<?php

namespace A2billing\Forms\Elements\Edit;

use A2billing\Forms\Elements\EditElement;

class Section extends EditElement
{
    public function __construct(string $label)
    {
        $this->label = $label;
    }

    public function render(int|string $i, mixed $value): string
    {
        return $this->renderElement();
    }

    protected function renderElement(): string
    {
        return <<< HTML
        <div class="row mb-3 border-bottom">
            <h4>$this->label</h4>
        </div>
        HTML;
    }
}
