<?php

namespace A2billing\Forms\Elements\List;

use A2billing\Forms\Elements\ListElement;
use Illuminate\Database\Query\Expression;

class Value extends ListElement
{
    /** @var callable|null  */
    protected mixed $callback = null;

    /** @var string[] $args */
    protected array $args = [];

    public function __construct(string|Expression $column, string $header, bool $sortable = true, callable|null $callback = null, array $args = [])
    {
        $this->column = $column;
        $this->sanitizeColumn();
        $this->header = $header;
        $this->sortable = $sortable;
        $this->callback = $callback;
        $this->args = array_filter($args, fn ($v) => !is_null($v));
    }

    public function renderCell(mixed &$value, array $row): string
    {
        $display = $value;
        if ($this->callback) {
            if (count($this->args) === 0) {
                $args = [$value ?? ""];
            } else {
                $args = [];
                foreach ($this->args as $arg) {
                    if (!is_string($arg)) {
                        $args[] = $arg;
                        continue;
                    }
                    if ($arg === "%X") {
                        $args[] = $value;
                    } elseif (str_starts_with($arg, "%")) {
                        $k = substr($arg, 1);
                        $args[] = $row[$k] ?? "";
                    } else {
                        $args[] = $arg;
                    }
                }
            }
            $display = ($this->callback)(...$args);
        }
        $value = $display;

        return sprintf("<td>%s</td>", $value);
    }
}
