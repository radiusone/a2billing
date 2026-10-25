<?php

namespace A2billing\Forms\Elements;

abstract class SearchElement extends Element
{
    protected string $label = "";
    protected string $default = "";
    protected array $attributes = [];
    public bool $local = false;

    public function render(array $data): string
    {
        $html = '<div class="row py-1">';
        $html .= $this->renderLabel();
        $html .= $this->renderElement($data);
        $html .= '</div>';

        return $html;
    }

    protected function renderLabel(): string
    {
        return sprintf(
            '<label class="col-4 col-form-label col-form-label-sm" for="%s">%s</label>',
            $this->column,
            // the only special case where we expect HTML
            preg_match("/<abbr title=\".+?\">.+?<\/abbr>/", $this->label)
                ? $this->label
                : htmlspecialchars($this->label),
        );
    }

    abstract protected function renderElement(array $data): string;
}
