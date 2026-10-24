<?php

namespace A2billing\Forms\Elements\Search;

use A2billing\Forms\Elements\SearchElement;
use DateTime;

class DateRelative extends SearchElement
{
    protected array $options = [];

    public function __construct(
        string         $column,
        string         $label,
        public bool    $start = false,
        protected bool $months = true,
    )
    {
        if ($this->months) {
            for ($i = 1; $i <= 12; $i++) {
                $val = (new DateTime("$i months ago"))->format("Y-m-d");
                $text = sprintf(ngettext("%d month ago", "%d months ago", $i), $i);
                $options[$val] = $text;
            }
        } else {
            $options = [
                (new DateTime("1 hour ago"))->format("Y-m-d H:i") => sprintf(ngettext("%d hour ago", "%d hours ago", 1), 1),
                (new DateTime("6 hours ago"))->format("Y-m-d H:i") => sprintf(ngettext("%d hour ago", "%d hours ago", 6), 6),
                (new DateTime("1 day ago"))->format("Y-m-d H:00") => sprintf(ngettext("%d day ago", "%d days ago", 1), 1),
                (new DateTime("1 week ago"))->format("Y-m-d H:00") => sprintf(ngettext("%d week ago", "%d weeks ago", 1), 1),
                (new DateTime("1 month ago"))->format("Y-m-d") => sprintf(ngettext("%d month ago", "%d months ago", 1), 1),
                (new DateTime("2 months ago"))->format("Y-m-d") => sprintf(ngettext("%d month ago", "%d months ago", 2), 2),
                (new DateTime("3 months ago"))->format("Y-m-d") => sprintf(ngettext("%d month ago", "%d months ago", 3), 3),
                (new DateTime("6 months ago"))->format("Y-m-d") => sprintf(ngettext("%d month ago", "%d months ago", 6), 6),
                (new DateTime("1 year ago"))->format("Y-m-d") => sprintf(ngettext("%d year ago", "%d years ago", 1), 1),
            ];
        }

        $this->column = $column;
        $this->label = $label;
        $this->options = $options;
    }

    protected function renderElement(array $data): string
    {
        $col = $this->sanitizeColumn();
        $name = $col . "_" . ($this->start ? "start" : "end") . "_relative";

        $en_name = "enable_$name";
        $en_value = $data[$en_name] ?? "";
        $en_checked = $en_value ? ' checked="checked"' : "";
        $en_label = _("enable the relative date search");

        $val = $data[$name] ?? $this->default;
        $options = implode(
            " ",
            array_map(
                fn ($value, $content) => sprintf(
                    '<option value="%s" %s>%s</option>',
                    htmlspecialchars($value),
                    "$val" === "$value" ? 'selected="selected"' : "",
                    htmlspecialchars($content)
                ),
                array_keys($this->options),
                array_values($this->options),
            )
        );
        $group_text = $this->start ? _("Since") : _("Before");
        $op_name = htmlspecialchars($name . "_type");
        $op_val = $this->start ? 5 : 3; // start gte end lt
        $name = htmlspecialchars($name);

        return <<< HTML
        <div class="col">
            <div class="input-group">
                <div class="input-group-text">
                    <input
                        type="checkbox"
                        name="$en_name" 
                        id="$en_name"
                        class="form-check-input m-0 date-input-enabler relative-date"
                        value="true"
                        aria-label="$en_label"
                        $en_checked
                    >&nbsp;<label for="$name" class="form-label form-label-sm m-0">$group_text</label>

                </div>
                <select name="$name" id="$name" class="form-select form-select-sm">
                    $options
                </select>
                <input type="hidden" name="$op_name" value="$op_val"/>
            </div>
        </div>
        HTML;
    }
}
