<?php

namespace A2billing\Payments;

use A2billing\Table;

trait Database
{
    protected string $table;

    protected function saveOrUpdate(array $values): bool
    {
        $table = new Table($this->table);
        $db = DbConnect();
        if ($this->id) {
            return $table->updateRow($db, $values, ["id" => $this->id]);
        } else {
            $id = null;
            $result = $table->addRow($db, $values, "id", $id);
            $this->id = $id;

            return $result;
        }
    }
}