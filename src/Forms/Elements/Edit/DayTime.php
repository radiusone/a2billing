<?php

namespace A2billing\Forms\Elements\Edit;

use A2billing\Forms\Elements\EditElement;
use DateTime;

class DayTime extends EditElement
{
    /**
     * Add a split day/time field, used in common/form_data/FG_var_def_ratecard.inc
     */
    public function __construct(
        string $label,
        string $name,
        string $help_text = "",
        string $default = "",
        string $error = "",
    )
    {
        $this->label = $label;
        $this->name = $name;
        $this->help_text = $help_text;
        $this->default = $default;
        $this->error = $error;
        $this->type = "DAYTIME";
    }

    /**
     * @throws \Exception
     */
    protected function renderElement(): string
    {
        $value = intval(is_null($this->value) ? $this->default : $this->value);
        $name = htmlspecialchars($this->name);

        $day = intdiv($value, 1440);
        $time = (new DateTime("@" . ($value % 1440) * 60))->format("H:i");

        $day_label = htmlspecialchars(_("Day"));
        $this->attributes["class"] = " form-select";
        $day_attributes = $this->attributeString();

        $time_label = htmlspecialchars(_("Time"));
        $this->attributes["class"] = str_replace("form-select", "form-control", $this->attributes["class"]);
        $time_attributes = $this->attributeString();

        $days = array_map(
            fn ($v, $k) => sprintf(
                "<option value=\"%s\" %s>%s</option>",
                htmlspecialchars($k),
                ($day === $k ? "selected=\"selected\"" : ""),
                htmlspecialchars($v)
            ),
            [_("Monday"), _("Tuesday"), _("Wednesday"), _("Thursday"), _("Friday"), _("Saturday"), _("Sunday")]
        );
        $options = implode("", $days);

        return <<< HTML
        <div class="daytime row">
            <div class="col-6">
                <label for="{$name}_day">$day_label</label>
                <select id="{$name}_day" $day_attributes>
                    $options
                </select>
            </div>
            <div class="col-6">
                <label for="{$name}_time">$time_label</label>
                <input type="time" id="{$name}_time" value="$time" $time_attributes/>
                <input type="hidden" name="$name" id="$name" value="$value"/>
            </div>
        </div>
        HTML;
    }
}
