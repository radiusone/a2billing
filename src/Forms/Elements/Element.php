<?php

namespace A2billing\Forms\Elements;

use A2billing\Connection;
use Illuminate\Database\Query\Expression;

abstract class Element
{
    /** @var string|Expression The database column associated with the element */
    public string|Expression $column;

    /**
     * This replaces dots in the column name to produce something suitable for an HTTP
     * form post. It also ensures that the column has a predictable alias, allowing it
     * to be found in the result set even if, e.g. the table name is removed from a
     * qualified name
     *
     * @param string[]|string|null $col
     * @return ($col is array ? array : string)
     */
    public function sanitizeColumn(array|string|null $col = null): array|string
    {
        if ($col) {
            return str_replace(".", "^^", $col);
        }

        $col = is_string($this->column) ? $this->column : $this->column->getValue(Connection::getConnection()->getQueryGrammar());
        if (str_contains($col, ".") && !str_contains($col, " AS ")) {
            $name = preg_replace("/(\w+)\.(\w+)/", "$1^^$2", $col);
            $col .= " AS $name";
            $this->column = is_string($this->column)
                ? $col
                : Connection::getConnection()->raw($col);
        } elseif (str_contains($col, " AS ")) {
            preg_match("/.* AS (\w+)/", $col, $m);
            $name = $m[1];
        } else {
            $name = $col;
        }

        return str_replace(".", "^^", $name);
    }

    /**
     * @param string[]|string|null $col
     * @return ($col is array ? array : string)
     */
    public function normalizeColumn(array|string|null $col = null): array|string
    {
        return str_replace("^^", ".", $col ?? $this->column);
    }
}