<?php

namespace A2billing\Forms\Elements\List;

use Illuminate\Database\Query\Expression;

class FilterInput
{
    public function __construct(public string|Expression $column, public string $label)
    {}

    public function render(mixed $value, int $index): string
    {
        $aria_label = htmlspecialchars(sprintf(_("Filter on %s"), $this->label));
        $label = htmlspecialchars($this->label);

        return <<< HTML
        <div class="col-auto">
            <input
                type="text"
                id="filterprefix$index"
                name="filterprefix$index"
                value="$value"
                class="form-control form-control-sm"
                placeholder="$label"
                aria-label="$aria_label"
            />
        </div>
        HTML;
    }
}