<?php

namespace A2billing\Forms\Elements\Search;

use A2billing\Forms\Elements\SearchElement;

class Text extends SearchElement
{
    public function __construct(string $column, string $label)
    {
        $this->column = $column;
        $this->label = $label;
        $this->attributes = [
            "type" => "text",
            "class" => "form-control form-control-sm",
        ];
    }

    protected function renderInputGroupItem(string $name, string $value): string
    {
        if ($value < 1 || $value > 4) {
            $value = "3";
        }
        $sel1 = $value === "1" ? 'selected="selected"' : "";
        $label1 = htmlspecialchars(_("Exact"));
        $sel2 = $value === "2" ? 'selected="selected"' : "";
        $label2 = htmlspecialchars(_("Begins with"));
        $sel3 = $value === "3" ? 'selected="selected"' : "";
        $label3 = htmlspecialchars(_("Contains"));
        $sel4 = $value === "4" ? 'selected="selected"' : "";
        $label4 = htmlspecialchars(_("Ends with"));
        $name = htmlspecialchars($name);
        $label = htmlspecialchars(_("select a search type for the previous input"));

        return <<< HTML

        <select name="$name" id="$name" class="form-select form-select-sm" aria-label="$label">
            <option value="1" $sel1>$label1</option>
            <option value="2" $sel2>$label2</option>
            <option value="3" $sel3>$label3</option>
            <option value="4" $sel4>$label4</option>
        </select>

        HTML;
    }

    protected function renderInput(string $name, string $value): string
    {
        $attr = array_map(
            fn ($k, $v) => sprintf('%s="%s"', htmlspecialchars($k), htmlspecialchars($v)),
            array_keys($this->attributes),
            $this->attributes,
        );

        return sprintf(
            '<input name="%s" id="%s" value="%s" %s/>',
            htmlspecialchars($name),
            htmlspecialchars($name),
            htmlspecialchars($value),
            implode(" ", $attr),
        );
    }

    protected function renderElement(array $data): string
    {
        $html = '<div class="col">';
        $col = $this->sanitizeColumn();
        $op_name = "{$col}_type";
        $op_value = $data[$op_name] ?? "";
        $col_name = $col;
        $col_value = $data[$col_name] ?? "";
        $html .= sprintf(
            '<div class="input-group"><div>%s</div>%s</div>',
            $this->renderInputGroupItem($op_name, $op_value),
            $this->renderInput($col_name, $col_value),
        );
        $html .= '</div>';

        return $html;
    }
}
