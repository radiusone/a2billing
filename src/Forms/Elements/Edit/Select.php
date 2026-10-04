<?php

namespace A2billing\Forms\Elements\Edit;

use A2billing\Forms\Elements\EditElement;

/**
 * @template TValue of int|string|float|array
 * @extends EditElement<TValue>
 */
class Select extends EditElement
{
    /** @var bool $multi */
    private bool $multi;

    /**
     * @param string $label The label text
     * @param string $name The form input name
     * @param array $options An array of options to build the select element with
     * @param string|int $default When adding (not editing) the value of the selected item
     * @param string $help_text Text to display below the form input
     * @param array<string,mixed> $attributes HTML attributes for the input
     * @param string $error A message to show if validation fails
     * @param array<string,mixed> $first_option array containing a value and label (k/v) for the first options in the list
     * @param (callable(string):(string|true))|null $validator A validation method that returns true or an error message
     * @return void
     */
    public function __construct(
        string $label,
        string $name,
        public iterable $options,
        string|int $default = "",
        string $help_text = "",
        array $attributes = [],
        string $error = "",
        public array $first_option = [],
        mixed $validator = null,
    )
    {
        $this->label = $label;
        $this->name = $name;
        $this->default = $default;
        $this->help_text = $help_text;
        $this->attributes = $attributes;
        $this->error = $error;
        $this->validator = $validator;
        $this->multi = array_key_exists("multiple", $this->attributes);
        $this->type = "LIST";
    }

    protected function renderElement(): string
    {
        $name = htmlspecialchars($this->name);
        if ($this->multi) {
            $name .= "[]";
        }
        $this->attributes["class"] ??= "";
        $this->attributes["class"] .= " form-select";
        $attributes = $this->attributeString();
        $options = "";
        foreach ($this->first_option as $k => $v) {
            $options = sprintf(
                "<option value=\"%s\">%s</option>",
                htmlspecialchars($k),
                htmlspecialchars($v)
            );
        }
        foreach($this->options as $k => $v) {
            $sel = "";
            if (is_null($this->value)) {
                if ("$this->default" === "$k") {
                    $sel = "selected=\"selected\"";
                }
            } elseif ($this->multi) {
                if (is_array($this->value)) {
                    $check = array_sum($this->value);
                } else {
                    $check = $this->value;
                }
                if (intval($k) & intval($check)) {
                    $sel = "selected=\"selected\"";
                }
            } elseif ("$this->value" === "$k") {
                $sel = "selected=\"selected\"";
            }
            $options .= sprintf(
                "<option value=\"%s\" %s>%s</option>",
                htmlspecialchars($k),
                $sel,
                htmlspecialchars($v)
            );
        }

        return <<< HTML
        <select name="$name" $attributes>
            $options
        </select>
        HTML;

    }
}
