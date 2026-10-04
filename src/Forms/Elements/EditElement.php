<?php

namespace A2billing\Forms\Elements;

use ArrayAccess;

/**
 * @template-covariant TValue
 */
abstract class EditElement implements ArrayAccess
{
    /** @var string The label text */
    public string $label;
    /** @var string The form input name */
    public string $name;
    /** @var array HTML attributes for the input */
    public array $attributes = [];
    /** @var mixed When adding (not editing), the value of the selected item */
    public mixed $default = null;
    /** @var string Text to display below the form input */
    public string $help_text = "";
    /** @var string A message to show if validation fails */
    public string $error = "";
    /** @var (callable(TValue):true|string)|null $validator A validation method that returns true or an error message */
    public mixed $validator = null;

    /** @var TValue The current value of the input */
    public mixed $value = null;
    /** @var bool Whether the input has passed validation */
    public bool $valid = true;
    /** @var true|string The error message returned by the validation method */
    public true|string $validation_err = true;
    /** @var int|string The index of the input in FormHander::$FG_EDIT_FORM_ELEMENTS */
    public int|string $index = 0;
    /** @var string The type, for backward compatibility during testing */
    public string $type = "";

    public function validate(): bool
    {
        if (!$this->validator) {
            $this->valid = true;
        } else {
            $result = ($this->validator)($this->value);
            if ($result === true) {
                $this->valid = true;
            } else {
                $this->valid = false;
                $this->validation_err = $result;
            }
        }

        return $this->valid;
    }

    /**
     * Parses the attributes array into an HTML-safe string
     *
     * The `id` attribute will be set to the same as the `name`
     * attribute if it has not been provided. The `value` attribute
     * for form elements will not be included.
     */
    protected function attributeString(): string
    {
        $this->attributes["class"] ??= "";
        if (!$this->valid) {
            $this->attributes["class"] .= " is-invalid";
        }
        $this->attributes["id"] ??= $this->name;

        $return = "";
        foreach ($this->attributes as $attribute => $value) {
            $attribute = preg_replace(
                "/[^a-z0-9_.[\]-]/",
                "",
                strtolower($attribute)
            );
            $value = htmlspecialchars(trim("$value"));
            $return .= "$attribute=\"$value\" ";
        }

        return trim($return);
    }

    public function render(int|string $i, mixed $value): string
    {
        $this->index = $i;
        $this->value = $value;
        $el = $this->renderElement();
        $err = $help = "";
        if (!$this->valid) {
            $err = sprintf(
                "<div class=\"form-text invalid-feedback\">%s%s%s</div>",
                $this->error,
                $this->error ? " - " : "",
                $this->validation_err
            );
        }
        if ($this->help_text) {
            $help = sprintf("<div class=\"form-text\">%s</div>", $this->help_text);
        }

        return <<< HTML
        <div class="row mb-3">
            <label id="item{$i}_label" for="{$this->attributes["id"]}" class="col-3 col-form-label">
                $this->label
            </label>
            <div class="col">
                $el
                $err
                $help
            </div>
        </div>
        HTML;
    }

    public function offsetExists(mixed $offset): bool
    {
        return property_exists($this, $offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return property_exists($this, $offset)
            ? $this->$offset
            : null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (property_exists($this, $offset)) {
            $this->$offset = $value;
        }
    }

    public function offsetUnset(mixed $offset): void
    {
    }

    abstract protected function renderElement(): string;
}
