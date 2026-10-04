<?php

namespace A2billing\Forms\Elements\Edit;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

class SqlSelect extends Select
{
    /**
     * @param string $label The label text
     * @param string $name The form input name
     * @param Builder $builder The table to check
     * @param string|int $default When adding (not editing), the value of the selected item
     * @param array $first_option array containing a value and label (k/v) for the first options in the list
     * @param string $help_text Text to display below the form input
     * @param array<string,mixed> $attributes HTML attributes for the input
     * @param string $error_text A message to show if validation fails
     * @param (callable(array):array{0:string, 1:array-key})|null $callback the callback is passed the DB row array,
     * indices 0 and 1 of the return are used to create the option label and value, otherwise
     * first two indices of the database array are used
     * @param (callable(string):(string|true))|null $validator A validation method that returns true or an error message
     * @return void
     */
    public function __construct(
        string $label,
        string $name,
        Builder $builder,
        string|int $default = "",
        array $first_option = [],
        string $help_text = "",
        array $attributes = [],
        string $error_text = "",
        mixed $callback = null,
        mixed $validator = null,
    )
    {
        $options = $builder->get()
            ->when(
                $callback,
                fn (Collection $rows) => $rows->map(fn (array $row) => $callback($row))
            )
            ->mapWithKeys(fn ($v) => [$v[1] => $v[0]]);

        parent::__construct(
            $label,
            $name,
            $options,
            $default,
            $help_text,
            $attributes,
            $error_text,
            $first_option,
            $validator,
        );
    }
}
