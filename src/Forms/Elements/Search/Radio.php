<?php

namespace A2billing\Forms\Elements\Search;

use A2billing\Forms\Elements\SearchElement;

class Radio extends SearchElement
{
    /**
     * @param string $column
     * @param string $label
     * @param array $options
     * @param int|string|null $default
     * @param bool $local if true, the element will not be added to the query
     */
    public function __construct(
        string $column,
        string $label,
        protected array $options,
        int|string|null $default = "",
        bool $local = false,
    )
    {
        $this->column = $column;
        $this->label = $label;
        $this->default = "$default";
        $this->local = $local;
    }

    protected function renderElement(array $data): string
    {
        $col = $this->sanitizeColumn();
        $val = $data[$col] ?? $this->default;
        $h = <<< HTML

        <div class="form-check col-6 col-lg-3 mb-1">
            <input
                type="radio" 
                name="%s" 
                id="%s" 
                value="%s" 
                %s
            />&nbsp;<label class="form-check-label form-check-label-sm" for="%s">%s</label>
        </div>
        HTML;
        $options = implode(
            " ",
            array_map(
                fn ($k, $v) => sprintf(
                    $h,
                    htmlspecialchars($col),
                    htmlspecialchars("$col$val"),
                    htmlspecialchars($k),
                    "$val" === "$k" ? 'checked="checked"' : "",
                    htmlspecialchars("$col$val"),
                    htmlspecialchars($v),
                ),
                array_keys($this->options),
                array_values($this->options),
            )
        );

        return <<< HTML

        <div class="col">
            <div class="row">
                $options
            </div>
        </div>

        HTML;
    }
}
