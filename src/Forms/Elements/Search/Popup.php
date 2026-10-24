<?php

namespace A2billing\Forms\Elements\Search;

class Popup extends Text
{
    public function __construct(
        string $column,
        string $label,
        protected string $href,
        protected string $select
    )
    {
        parent::__construct($column, $label);
    }

    protected function renderInputGroupItem(string $name = "", string $value = ""): string
    {
        $col = $this->sanitizeColumn();
        $href = htmlspecialchars($this->href);
        $name = htmlspecialchars($col);
        $select = htmlspecialchars($this->select);
        $label = htmlspecialchars(_("open a popup to select an item"));

        return <<< HTML

        <a
            href="$href"
            data-field-name="$name"
            data-window-name="{$name}_popup"
            data-popup-options="width=750,height=450,top=50,left=100,scrollbars=1"
            data-select="$select"
            class="btn btn-primary popup_trigger"
            aria-label="$label"
        ><span class="bi bi-box-arrow-up-right fw-bolder fs-6"></span></a>

        HTML;
    }

    protected function renderElement(array $data): string
    {
        $html = '<div class="col">';
        $col_name = $this->sanitizeColumn();
        $col_value = $data[$col_name] ?? "";
        $html .= sprintf(
            '<div class="input-group">%s%s</div>',
            $this->renderInput($col_name, $col_value),
            $this->renderInputGroupItem(),
        );
        $html .= '</div>';

        return $html;
    }

}
