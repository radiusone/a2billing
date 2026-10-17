<?php

namespace A2billing\Forms\Elements\List;

use Illuminate\Database\Query\Builder;

class SqlMapping extends Mapping
{
    public function __construct(string $column, string $header, Builder $table, string $href = "", bool $sortable = true)
    {
        // todo: numeric keys in result
        $options = $table->get()->mapWithKeys(fn ($row) => [$row[0] => $row[1]]);
        parent::__construct($column, $header, $options, $sortable);
        $this->href = $href;
    }
}
