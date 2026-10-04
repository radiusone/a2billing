<?php

namespace A2billing\Forms\Elements\Edit;

use A2billing\Forms\Elements\EditElement;

class Textarea extends EditElement
{
    /**
     * @param string $label The label text
     * @param string $name The form input name
     * @param string $help_text Text to display below the form input
     * @param array $attributes HTML attributes for the input
     * @param string $error A message to show if validation fails
     * @param string $default When adding (not editing) the value of the input
     * @param (callable(string):(string|true))|null $validator A validation method that returns true or an error message
     * @return void
     */
    public function __construct(
        string $label,
        string $name,
        string $help_text = "",
        array $attributes = [],
        string $error = "",
        string $default = "",
        mixed $validator = null,
    )
    {
        $this->label = $label;
        $this->name = $name;
        $this->help_text = $help_text;
        $this->attributes = $attributes;
        $this->error = $error;
        $this->default = $default;
        $this->validator = $validator;
        $this->type = "TEXTAREA";
    }

    protected function attributeString(): string
    {
        $this->attributes["class"] ??= "";
        $this->attributes["class"] .= " form-control";

        return parent::attributeString();
    }

    protected function renderElement(): string
    {
        if (is_null($this->value)) {
            $this->value = $this->default;
        }
        $value = htmlspecialchars("$this->value");
        $attributes = $this->attributeString();

        return <<< HTML
        <textarea name="$this->name" $attributes>$value</textarea>
        HTML;
    }
}
