<?php

namespace A2billing\Forms\Elements;

use Illuminate\Database\Query\Expression;

abstract class ListElement
{
    public string|Expression $column = "";
    public string $header = "";
    public bool $sortable = false;

    public function renderHeader(bool $current = false, array $sort_params = []): string
    {
        if (!$this->sortable) {
            return sprintf("<th>%s</th>", $this->header);
        }

        return sprintf(
            "<th><a class='sort %s' href='?%s'> %s </a></th>",
            $current ? $sort_params["sens"] : "",
            http_build_query($sort_params, arg_separator: "&amp;"),
            $this->header
        );
    }

    abstract public function renderCell(mixed &$value, array $row): string;
}
