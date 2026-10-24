<?php

namespace A2billing\Forms\Elements;

abstract class SearchElement
{
    public string $column;
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
            htmlspecialchars($this->label)
        );
    }

    /**
     * @param string[]|string|null $col
     * @return ($col is array ? array : string)
     */
    public function sanitizeColumn(array|string|null $col = null): array|string
    {
        return str_replace(".", "^^", $col ?? $this->column);
    }

    /**
     * @param string[]|string|null $col
     * @return ($col is array ? array : string)
     */
    public function normalizeColumn(array|string|null $col = null): array|string
    {
        return str_replace("^^", ".", $col ?? $this->column);
    }

    abstract protected function renderElement(array $data): string;
}
