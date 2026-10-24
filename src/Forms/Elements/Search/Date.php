<?php

namespace A2billing\Forms\Elements\Search;

class Date extends Text
{
    public function __construct(string $column, string $label)
    {
        parent::__construct($column, $label);
        $this->attributes["type"] = "date";
    }

    protected function renderElement(array $data): string
    {
        $html = parent::renderElement($data);
        // add a "starts with" comparison operator
        $col = $this->sanitizeColumn();
        $html .= sprintf(
            '<input type="hidden" name="%s" value="2"/>',
            htmlspecialchars("{$col}_type"),
        );

        return $html;
    }
}
