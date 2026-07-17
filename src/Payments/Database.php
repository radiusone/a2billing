<?php

namespace A2billing\Payments;

use A2billing\Connection;

trait Database
{
    protected string $table;

    protected function saveOrUpdate(array $values): bool
    {
        if ($this->id) {
            return Connection::getConnection($this->table)
                ->where("id", $this->id)
                ->update($values) > 0;
        } else {
            $this->id = Connection::getConnection($this->table)
                ->insertGetId($values, "id") ?: null;

            return !is_null($this->id);
        }
    }
}