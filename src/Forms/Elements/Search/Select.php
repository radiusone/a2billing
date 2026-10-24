<?php

namespace A2billing\Forms\Elements\Search;

use A2billing\Forms\Elements\SearchElement;

class Select extends SearchElement
{
    protected array $options;

    /**
     * @param string $column
     * @param string $label
     * @param iterable $options
     * @param int|string $default
     * @param bool $local if true, the element will not be added to the query
     */
    public function __construct(
        string $column,
        string $label,
        iterable $options,
        int|string $default = "",
        bool $local = false,
    )
    {
        $this->column = $column;
        $this->label = $label;
        $this->default = "$default";
        if (is_array($options)) {
            $this->options = array_map("strval", $options);
        } else {
            foreach ($options as $k => $v) {
                $this->options[$k] = "$v";
            }
        }
        $this->local = $local;
    }

    public function render(array $data): string
    {
        return $this->renderElement($data);
    }

    protected function renderElement(array $data): string
    {
        $col = $this->sanitizeColumn();
        $name = htmlspecialchars($col);
        $label = htmlspecialchars($this->label);
        $val = $data[$col] ?? $this->default;
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

        return <<< HTML

        <div class="col-4 mb-1">
            <label for="$name" class="form-label form-label-sm">$label</label>
            <select name="$name" id="$name" class="form-select form-select-sm">
                <option value=""></option>
                $options
            </select>
        </div>

        HTML;
    }
}
