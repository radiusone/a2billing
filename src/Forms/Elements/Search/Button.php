<?php

namespace A2billing\Forms\Elements\Search;

use A2billing\Forms\Elements\SearchElement;

class Button extends SearchElement
{
    public function __construct(
        protected string $name,
        string $label,
        protected string $value = "1",
        protected string $class = "",
    )
    {
        $this->label = $label;
        $this->column = "";
    }

    public function render(array $data): string
    {
        return $this->renderElement($data);
    }

    protected function renderElement(array $data): string
    {
        $name = htmlspecialchars($this->name);
        $value = htmlspecialchars($this->value);
        $class = htmlspecialchars($this->class);
        $label = htmlspecialchars($this->label);

        return <<< HTML
        <button type="submit" name="$name" value="$value" class="btn $class">$label</button>
        HTML;

    }
}
