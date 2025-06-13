<?php

namespace A2billing\Payments;

use A2billing\Table;

trait Database
{
    protected string $table;

    protected function saveOrUpdate(array $values): bool
    {
        $table = new Table($this->table);
        if ($this->id) {
            return $table->updateRow($values, ["id" => $this->id]);
        } else {
            $id = null;
            $result = $table->addRow($values, "id", $id);
            $this->id = $id;

            return $result;
        }
    }
}