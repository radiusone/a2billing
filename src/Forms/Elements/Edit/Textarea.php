<?php

namespace A2billing\Forms\Elements\Edit;

use A2billing\Forms\Elements\Edit\Traits\Splittable;
use A2billing\Forms\Elements\EditElement;

class Textarea extends EditElement
{
    use Splittable;

    /**
     * @param string $label The label text
     * @param string $column The database column, used to create the form input name
     * @param string $help_text Text to display below the form input
     * @param array $attributes HTML attributes for the input
     * @param string $error A message to show if validation fails
     * @param string $default When adding (not editing) the value of the input
     * @param (callable(string):(string|true))|null $validator A validation method that returns true or an error message
     * @param bool $splittable If true, value is "splittable" during create or edit (values like e.g. 12-14 or 15;16;17)
     */
    public function __construct(
        string $label,
        string $column,
        string $help_text = "",
        array $attributes = [],
        string $error = "",
        string $default = "",
        mixed $validator = null,
        public bool $splittable = false,
    )
    {
        $this->column = $column;
        $this->label = $label;
        $this->name = $this->sanitizeColumn();
        $this->help_text = $help_text;
        $this->attributes = $attributes;
        $this->error = $error;
        $this->default = $default;
        $this->validator = $validator;
    }

    protected function attributeString(): string
    {
        $this->attributes["class"] ??= "";
        $this->attributes["class"] .= " form-control";

        return parent::attributeString();
    }

    protected function renderElement(): string
    {
        $this->value ??= $this->default;
        $value = htmlspecialchars("$this->value");
        $attributes = $this->attributeString();

        return <<< HTML
        <textarea name="$this->name" $attributes>$value</textarea>
        HTML;
    }
}
