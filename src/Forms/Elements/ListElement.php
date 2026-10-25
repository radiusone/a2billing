<?php

namespace A2billing\Forms\Elements;

use Illuminate\Database\Query\Expression;

abstract class ListElement extends Element
{
    public string $header = "";
    public bool $sortable = false;

    public function renderHeader(bool $current = false, array $sort_params = []): string
    {
        // the only special case where we expect HTML
        $header = preg_match("/<abbr title=\".+?\">.+?<\/abbr>/", $this->header)
            ? $this->header
            : htmlspecialchars($this->header);

        if (!$this->sortable) {
            return sprintf("<th>%s</th>", $header);
        }

        return sprintf(
            "<th><a class='sort %s' href='?%s'> %s </a></th>",
            $current ? $sort_params["sens"] : "",
            http_build_query($sort_params, arg_separator: "&amp;"),
            $header
        );
    }

    abstract public function renderCell(mixed &$value, array $row): string;
}
