<?php

namespace A2billing\Forms\Elements\Search;


class Comparison extends Text
{
    protected function renderInputGroupItem(string $name, string $value): string
    {
        $sel1 = $value === "1" ? 'selected="selected"' : "";
        $label1 = htmlspecialchars(_("equal to"));
        $sel2 = $value === "2" ? 'selected="selected"' : "";
        $label2 = htmlspecialchars(_("less than or equal to"));
        $sel3 = $value === "3" ? 'selected="selected"' : "";
        $label3 = htmlspecialchars(_("less than"));
        $sel4 = $value === "4" ? 'selected="selected"' : "";
        $label4 = htmlspecialchars(_("greater than"));
        $sel5 = $value === "5" ? 'selected="selected"' : "";
        $label5 = htmlspecialchars(_("greater than or equal to"));
        $sel6 = $value === "6" ? 'selected="selected"' : "";
        $label6 = htmlspecialchars(_("not equal to"));
        $name = htmlspecialchars($name);
        $label = htmlspecialchars(_("select an operator to apply to the next input"));

        return <<< HTML

            <select name="$name" id="$name" class="form-select form-select-sm" aria-label="$label">
                <option value="0"></option>
                <option value="4" $sel4 aria-label="$label4">&gt;</option>
                <option value="5" $sel5 aria-label="$label5">&gt;=</option>
                <option value="1" $sel1 aria-label="$label1">=</option>
                <option value="2" $sel2 aria-label="$label2">&lt;=</option>
                <option value="3" $sel3 aria-label="$label3">&lt;</option>
                <option value="6" $sel6 aria-label="$label6">!=</option>
            </select>

        HTML;
    }

    protected function renderElement(array $data): string
    {
        $html = '<div class="col"><div class="row align-items-center">';
        $col = $this->sanitizeColumn();
        foreach ([0, 1] as $i) {
            $op_name = "{$col}_{$i}_type";
            $op_value = $data[$op_name] ?? ($i === 0 ? "1" : "0");
            $col_name = "{$col}_$i";
            $col_value = $data[$col_name] ?? "";
            $html .= sprintf(
                '<div class="col"><div class="input-group"><div>%s</div>%s</div></div>',
                $this->renderInputGroupItem($op_name, $op_value),
                $this->renderInput($col_name, $col_value),
            );
            if ($i === 0) {
                // separator
                $html .= sprintf(
                    '<div class="col-auto text-center">%s</div>',
                    htmlspecialchars(_("AND")),
                );
            }
        }
        $html .= '</div></div>';

        return $html;
    }
}
