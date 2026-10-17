<?php

namespace A2billing\Forms\Elements\List;

use A2billing\Forms\Elements\ListElement;
use ArrayAccess;

class Mapping extends ListElement
{
    protected string $href = "";

    public function __construct(string $column, string $header, protected array|ArrayAccess $options, bool $sortable = true)
    {
        $this->column = $column;
        $this->header = $header;
        $this->sortable = $sortable;
    }

    public function renderCell(mixed &$value, array $row): string
    {
        $display = $mapped_value = $this->options[$value] ?? _("n/a");
        if (str_ends_with($this->href, "=")) {
            $display = sprintf(
                "<a href=\"%s%s\">%s</a>",
                htmlspecialchars($this->href),
                urlencode($value),
                htmlspecialchars($mapped_value)
            );
        }
        $value = $mapped_value;

        return sprintf("<td>%s</td>", $display);
    }
}
