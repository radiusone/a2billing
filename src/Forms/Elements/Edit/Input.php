<?php

namespace A2billing\Forms\Elements\Edit;

use A2billing\Forms\Elements\Edit\Traits\Splittable;
use A2billing\Forms\Elements\EditElement;

/**
 * @template TValue of int|string|float
 * @extends EditElement<TValue>
 */
class Input extends EditElement
{
    use Splittable;

    public bool $check_empty = true;
    public bool $empty_is_null = false;

    /**
     * @param string $label The label text
     * @param string $column The database column, used to create the form input name
     * @param string $help_text Text to display below the form input
     * @param array<string,mixed> $attributes HTML attributes for the input
     * @param (callable(string):(string|true))|null $validator A validation method that returns true or an error message
     * @param string $error A message to show if validation fails
     * @param string $check_empty If set to "NO", empty values are not validated; if set to "NO-NULL" empty values are added to the SQL query as NULL
     * @param string|null $default When adding (not editing), the value of the input
     * @param (callable(string):string)|null $display_callback A callback to run the value through before displaying it
     * @param (callable(string):string)|null $save_callback A callback to run the value through before saving it to database
     * @param bool $splittable If true, value is "splittable" during create or edit (values like e.g. 12-14 or 15;16;17)
     */
    public function __construct(
        string $label,
        string $column,
        string $help_text = "",
        array $attributes = [],
        mixed $validator = null,
        string $error = "",
        string $check_empty = "",
        ?string $default = null,
        protected mixed $display_callback = null, // only used in FG_var_config.inc to convert 0/1 to yes/no
        public mixed $save_callback = null,
        public bool $splittable = false,
    )
    {
        $this->column = $column;
        $this->label = $label;
        $this->name = $this->sanitizeColumn();
        $this->help_text = $help_text;
        $this->attributes = $attributes;
        $this->validator = $validator;
        $this->error = $error;
        if ($check_empty === "NO") {
            $this->check_empty = false;
        }
        if ($check_empty === "NO-NULL") {
            $this->check_empty = false;
            $this->empty_is_null = true;
        }
        $this->default = $default;
    }

    public function validate(mixed &$value = null): bool
    {
        if ($this->empty_is_null === true && $value === "") {
            $this->value = null;
            return true;
        }
        if ($this->check_empty === false && $value === "") {
            return true;
        }
        return parent::validate($value);
    }

    protected function attributeString(): string
    {
        $this->attributes["class"] ??= "";
        $this->attributes["class"] .= " form-control";

        return parent::attributeString();
    }

    protected function renderElement(): string
    {
        if (array_key_exists("value", $this->attributes)) {
            $this->value = $this->attributes["value"];
            unset($this->attributes["value"]);
        } else {
            $this->value ??= $this->default ?? "";
        }
        $value = $this->value;
        if ($this->display_callback) {
            $value = ($this->display_callback)("$value");
        }
        $this->attributes["type"] ??= "text";
        $attributes = $this->attributeString();

        return sprintf(
            "<input name=\"%s\" value=\"%s\" %s/>",
            htmlspecialchars($this->name),
            htmlspecialchars("$value"),
            $attributes
        );
    }
}
