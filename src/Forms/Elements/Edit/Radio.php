<?php

namespace A2billing\Forms\Elements\Edit;

use A2billing\Forms\Elements\EditElement;

class Radio extends EditElement
{
    /**
     * @param string $label The label text
     * @param string $name The form input name
     * @param array $options An array of labels (indexed by value) to build radio buttons
     * @param string $default When adding (not editing), the value of the selected item
     * @param string $help_text Text to display below the form input
     * @param array<string,mixed> $attributes HTML attributes for the inputs
     * @param string $error A message to show if validation fails
     * @return void
     */
    public function __construct(
        string $label,
        string $name,
        public array $options,
        mixed $default,
        string $help_text,
        array $attributes,
        string $error,
    )
    {
        $this->label = $label;
        $this->name = $name;
        $this->default = $default;
        $this->help_text = $help_text;
        $this->attributes = $attributes;
        $this->error = $error;

    }

    protected function renderElement(): string
    {
        $invalid = $this->valid ? "" : "is-invalid";
        $options = "";
        foreach ($this->options as $value => $label) {
            $check = "";
            if (
                (is_null($this->value) && "$this->default" === "$value")
                || (!is_null($this->value) && "$this->value" === "$value")
            ) {
                $check = 'checked="checked"';
            }
            $value = htmlspecialchars("$value");
            $label = htmlspecialchars("$label");
            $options .= <<< HTML
            <div class="form-check">
                <input id="{$this->name}_$value" class="form-check-input $invalid" type="radio" name="$this->name" value="$value" $check/>
            </div>
            <label for="{$this->name}_$value" class="form-check-label">$label</label>
            HTML;
        }

        return $options;
    }
}
