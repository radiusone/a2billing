<?php

namespace A2billing\Forms\Elements\Edit;

use A2billing\Forms\Elements\EditElement;

/**
 * @template TValue of int|string|float
 * @extends EditElement<TValue>
 */
class Input extends EditElement
{
    /**
     * @param string $label The label text
     * @param string $name The form input name
     * @param string $help_text Text to display below the form input
     * @param array<string,mixed> $attributes HTML attributes for the input
     * @param (callable(string):(string|true))|null $validator A validation method that returns true or an error message
     * @param string $error A message to show if validation fails
     * @param string $check_empty If set to "NO", empty values are not validated; if set to "NO-NULL" empty values are added to the SQL query as NULL
     * @param callable(string):string|null $callback A callback to run the value through before displaying it
     * @return void
     */
    public function __construct(
        string $label,
        string $name,
        string $help_text = "",
        array $attributes = [],
        mixed $validator = null,
        string $error = "",
        protected string $check_empty = "",
        protected mixed $callback = null, // only used in FG_var_config.inc to convert 0/1 to yes/no
    )
    {
        $this->label = $label;
        $this->name = $name;
        $this->help_text = $help_text;
        $this->attributes = $attributes;
        $this->validator = $validator;
        $this->error = $error;
        $this->type = "INPUT";
    }

    public function validate(): bool
    {
        if ($this->check_empty === "NO" && $this->value === "") {
            return true;
        }
        if ($this->check_empty === "NO-NULL" && $this->value === "") {
            $this->value = null;
            return true;
        }
        return parent::validate();
    }

    protected function attributeString(): string
    {
        $this->attributes["class"] ??= "";
        $this->attributes["class"] .= " form-control";

        return parent::attributeString();
    }

    protected function renderElement(): string
    {
        $value = $this->value;
        if ($this->callback) {
            $value = ($this->callback)($value);
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
