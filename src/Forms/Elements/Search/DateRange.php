<?php

namespace A2billing\Forms\Elements\Search;

use DateTime;

class DateRange extends Text
{
    private string $groupLabel;
    private string $groupText;

    public function __construct(string $column, string $label)
    {
        parent::__construct($column, $label);
        $this->attributes["type"] = "date";
    }

    protected function renderInputGroupItem(string $name, string $value): string
    {
        $name = htmlspecialchars($name);
        $checked = $value ? ' checked="checked"' : "";

        return <<< HTML

        <input
            type="checkbox"
            name="$name"
            id="$name"
            class="form-check-input m-0 date-input-enabler"
            value="true"
            aria-label="$this->groupLabel"
            $checked
        >&nbsp;<label for="$name" class="form-label form-label-sm m-0">$this->groupText</label>

        HTML;
    }

    protected function renderElement(array $data): string
    {
        $col = $this->sanitizeColumn();
        $html = '<div class="col"><div class="row align-items-center">';
        foreach (["start", "end"] as $i) {
            $this->groupLabel = htmlspecialchars(
                $i === "start"
                    ? _("enable the search start date")
                    : _("enable the search end date")
            );
            $this->groupText = htmlspecialchars($i === "start" ? _("From") : _("To"));

            $en_name = "enable_{$col}_$i";
            $en_value = $data[$en_name] ?? "";

            $op_name = "{$col}_{$i}_type";
            $op_value = $i === "start" ? "5" : "2"; // start >= end <=

            $col_name = "{$col}_$i";
            $default = $i === "start" ? "first day of this month" : "first day of next month";
            $col_value = $data[$col_name] ?? (new DateTime($default))->format("Y-m-d");

            $h = <<< HTML

            <div class="col">
                <div class="input-group">
                    <div class="input-group-text">
                        %s
                    </div>
                    %s
                </div>
                <input type="hidden" name="%s" value="%s"/>
            </div>

            HTML;

            $html .= sprintf(
                $h,
                $this->renderInputGroupItem($en_name, $en_value),
                $this->renderInput($col_name, $col_value),
                htmlspecialchars($op_name),
                htmlspecialchars($op_value),
            );
        }
        $html .= '</div></div>';

        return $html;
    }
}
