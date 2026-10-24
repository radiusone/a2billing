<?php

namespace A2billing\Forms\Elements;

/**
 * @template-covariant TValue
 */
abstract class EditElement
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

    public function validate(mixed &$value = null): bool
    {
        $this->validation_err = true;
        $this->valid = true;
        if ($this->validator) {
            $result = ($this->validator)($value);
            if ($result !== true) {
                $this->validation_err = $result;
                $this->valid = false;
                return false;
            }
        }

        return true;
    }

    /**
     * Parses the attributes array into an HTML-safe string
     *
     * The `id` attribute will be set to the same as the `name`
     * attribute if it has not been provided. The `value` attribute
     * for form elements will not be added.
     */
    protected function attributeString(): string
    {
        $this->attributes["class"] ??= "";
        if (!$this->valid) {
            $this->attributes["class"] .= " is-invalid";
        }
        $this->attributes["id"] ??= $this->name ?? "";

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

    public function isDisabled(): bool
    {
        return array_key_exists("disabled", $this->attributes);
    }

    public function render(int|string $i, mixed $value): string
    {
        $this->index = $i;
        $this->value = $value;
        $label = htmlspecialchars($this->label);
        $el = $this->renderElement();
        $err = $help = "";
        if (!$this->valid) {
            $err = sprintf(
                "<div class=\"form-text invalid-feedback\">%s%s%s</div>",
                htmlspecialchars($this->error),
                $this->error ? " - " : "",
                htmlspecialchars($this->validation_err)
            );
        }
        if ($this->help_text) {
            $help = sprintf(
                "<div class=\"form-text\">%s</div>",
                htmlspecialchars($this->help_text),
            );
        }
        $for = "";
        if ($this->attributes["id"] ?? "") {
            $for = htmlspecialchars($this->attributes["id"]);
        }
        $label_id = htmlspecialchars("item{$i}_label");

        return <<< HTML
        <div class="row mb-3">
            <label id="$label_id" for="$for" class="col-3 col-form-label">
                $label
            </label>
            <div class="col">
                $el
                $err
                $help
            </div>
        </div>
        HTML;
    }

    abstract protected function renderElement(): string;
}
