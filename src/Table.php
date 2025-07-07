<?php

namespace A2billing;

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

use ADOConnection;
use Profiler_Console as Console;

/**
 * This file is part of A2Billing (http://www.a2billing.net/)
 *
 * A2Billing, Commercial Open Source Telecom Billing platform,
 * powered by Star2billing S.L. <http://www.star2billing.com/>
 *
 * @copyright   Copyright © 2004-2015 - Star2billing S.L.
 * @copyright   Copyright © 2022-2025 RadiusOne Inc.
 * @author	  Belaid Arezqui <areski@gmail.com>
 * @license	 http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @package	 A2Billing
 *
 * Software License Agreement (GNU Affero General Public License)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *
**/

/**
* Class Table used to abstract Database queries and processing
*
* @category   Database
* @package    Table
* @author     Arezqui Belaid <areski _atl_ gmail com>
* @author     Steve Dommett <steve@st4vs.net>
* @copyright  2004-2015 A2Billing
* @license    http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
* @version    CVS: $Id:$
* @since      File available since Release 1.0
*/

class Table
{
    public array $fields = [];
    public ?string $table = null;
    public array $joins = [];
    public string $errstr = '';
    public array $FK_TABLES = [];
    public ?array $FK_EDITION_CLAUSE = null;
    // FALSE if you want to delete the dependent Records, TRUE if you want to update
    // Dependent Records to -1
    public bool $FK_DELETE = true;
    public int $FK_ID_VALUE = 0;

    public string $db_type = 'mysql';

    protected static ?ADOConnection $connection = null;

    /**
     * @param string|null $table the table we're working with
     * @param array|string $list_fields when selecting, what fields will be selected
     * @param array $joins tables to join to the query; see Table::processJoinedTables() for usage
     */
    public function __construct(string $table = null, $list_fields = [], array $joins = [], ADOConnection $db = null)
    {
        $this->table = $table;
        if (is_string($list_fields)) {
            $list_fields = explode(",", $list_fields);
            array_walk($list_fields, "trim");
            $list_fields = array_filter($list_fields);
        }
        if (empty($list_fields) || !is_array($list_fields)) {
            $list_fields = ["*"];
        }
        $this->fields = $list_fields;
        $this->joins = $joins;
        if (defined("DB_TYPE") && DB_TYPE === 'postgres') {
            $this->db_type = "postgres";
        }
        self::$connection = $db;
    }

    public static function getConnection(): ADOConnection
    {
        self::$connection ??= Connection::GetDBHandler();

        return self::$connection;
    }

    /**
     * Configure the table for deleting records using (fake) foreign keys
     *
     * @param array $fk_Tables tables that refer back to the current table
     * @param array $fk_Fields fields in the foreign tables that need updating/deleting
     * @param int|null $id_Value the value to check for in foreign tables when updating/deleting
     * @param bool $fk_delete whether to delete or update (with -1) foreign tables
     * @return void
     */
    public function setDeleteFk(array $fk_Tables = [], array $fk_Fields = [], int $id_Value = null, bool $fk_delete = true)
    {
        if (count($fk_Tables) === count($fk_Fields)) {
            $this->FK_TABLES         = $fk_Tables;
            $this->FK_EDITION_CLAUSE = $fk_Fields;
            $this->FK_DELETE         = $fk_delete;
            $this->FK_ID_VALUE       = $id_Value;
        }
    }

    public function quote_identifier(?string $identifier): ?string
    {
        if (is_null($identifier) || preg_match("/^(\\w+\\.)?\\*$/", $identifier)) {
            return $identifier;
        }

        $q = $this->db_type === "mysql" ? "`" : "\"";
        $identifier = trim($identifier);

        $alias = "";
        if (preg_match("/^(.+?) +AS +[`\"]?(\\w+)[`\"]?$/i", $identifier, $matches)) {
            $identifier = $matches[1];
            $alias = " AS $q$matches[2]$q";
        }

        if ($this->isSqlFunction($identifier) || is_numeric($identifier)) {
            // something like a function call
            return trim("$identifier $alias");
        }

        $distinct = "";
        if (str_starts_with(strtoupper($identifier), "DISTINCT ")) {
            $identifier = trim(substr($identifier, 8));
            $distinct = "DISTINCT ";
        }

        if (str_starts_with($identifier, $q) && str_ends_with($identifier, $q)) {
            // there is plenty of room for abuse here, but assume already quoted values are ok
            return trim("$distinct $identifier $alias");
        }

        $identifier = str_replace($q, "", $identifier);
        if (str_contains($identifier, ".")) {
            $identifier = implode("$q.$q", explode(".", $identifier));
        }

        return trim("$distinct $q$identifier$q $alias");
    }

    public function isSqlFunction(string $value): bool
    {
        $value = strtolower($value);
        return str_starts_with($value, "now()")
            || str_starts_with($value, "current_timestamp")
            || str_starts_with($value, "current_time")
            || str_starts_with($value, "current_date")
            || preg_match("/^\(\s*select\s/", $value)
            || preg_match(
                "/(date|cast|if|count|coalesce|sum|avg|left|right|concat|replace|substr(ing)?|lower|upper|min|max|hour|minute|second|rand)\\s*\\(/",
                $value
            )
            || preg_match("/^case (when)?.*? end( as \w+)?$/", $value);
    }

    public function begin(): bool
    {
        return $this->getConnection()->BeginTrans();
    }

    public function end(): bool
    {
        return $this->getConnection()->CommitTrans();
    }

    public function abort(): bool
    {
        return $this->getConnection()->CommitTrans(false);
    }

    /**
     * Fetch one or more rows with a proper parameterized statement
     *
     * @param array $conditions values to match placeholders in $where
     * @param array $order the column(s) to order by
     * @param string $direction either "asc" or "desc"
     * @param array $group the column(s) to group by
     * @param int $limit query limit
     * @param int $offset
     * @return array
     */
    public function getRows(array $conditions = [], array $order = [], string $direction = "ASC", array $group = [], int $limit = 0, int $offset = 0): array
    {
        $db = $this->getConnection();
        $fields = implode(",", array_map([self::class, "quote_identifier"], $this->fields));
        $table = str_contains($this->table, " JOIN ") ? $this->table : $this->quote_identifier($this->table);
        $table .= " " . $this->processJoinedTables();
        $where = $this->processWhereClauseArray($conditions, $params);
        $direction = strtoupper($direction) === "ASC" ? "ASC" : "DESC";
        $orderings = array_filter($order);
        if (!empty($orderings)) {
            array_walk($orderings, fn (&$v) => $v = $this->quote_identifier($v) . " $direction");
            $order_sql = "ORDER BY " . implode(",", $orderings);
        } else {
            $order_sql = "";
        }
        $group = array_filter($group);
        if (!empty($group)) {
            array_walk($group, fn (&$v) => $v = $this->quote_identifier($v));
            $group_sql = "GROUP BY " . implode(",", $group);
        } else {
            $group_sql = "";
        }
        $limit_sql = $limit ? "LIMIT $limit" : "";
        $offset_sql = $offset ? "OFFSET $offset" : "";

        $query = "SELECT $fields FROM $table WHERE $where $group_sql $order_sql $limit_sql $offset_sql";
        class_exists(Console::class) && Console::logQuery($query);
        $result = $db->GetArray($query, $params) ?: [];
        class_exists(Console::class) && Console::logQuery($query);

        return $result;
    }

    /**
     * Get a single row
     *
     * @param array $conditions
     * @param array $order
     * @param string $direction
     * @param array $group
     * @return array
     */
    public function getRow(array $conditions = [], array $order = [], string $direction = "ASC", array $group = []): array
    {
        $data = $this->getRows($conditions, $order, $direction, $group, 1);

        return $data[0] ?? [];
    }

    /**
     * Get all the values of a given column, optionally indexed by another column.
     *
     * @param array $conditions
     * @param string $column column name or empty string to use the first column of the result set
     * @param string $index column name or empty string to use the second column of the result set when available
     * @return array
     */
    public function getColumn(array $conditions = [], string $column = "", string $index = ""): array
    {
        $order = $column !== "" ? [$column] : [];
        $data = $this->getRows($conditions, $order);
        if (count($data) === 0) {
            return $data;
        }
        $columns = array_keys($data[0]);
        // todo: remove this when no more numeric keys
        $columns = array_values(array_filter(
            array_keys($data[0]),
            fn ($v) => !is_numeric($v)
        ));

        if ($column === "") {
            $column = $columns[0];
            // not great but can't do a DB sort if we don't know the column name
            usort($data, fn (array $a, array $b) => $a[$column] <=> $b[$column]);
        }
        if ($index === "" && count($columns) <= 1) {
            return array_column($data, $column);
        }

        if ($index === "") {
            $index = $columns[1];
        }

        return array_combine(
            array_column($data, $index),
            array_column($data, $column)
        );
    }

    /**
     * Gets the first value of the first row in the result set
     *
     * @param array $conditions
     * @param array $order
     * @param string $direction
     * @param array $group
     * @return mixed|null
     */
    public function getValue(array $conditions = [], array $order = [], string $direction = "ASC", array $group = [])
    {
        $data = $this->getRow($conditions, $order, $direction, $group);

        return $data[0] ?? null;
    }

    /**
     * Count matching rows
     *
     * @param array $conditions
     * @param array $groupby
     * @return int
     */
    public function countRows(array $conditions = [], array $groupby = []): int
    {
        $old_fields = $this->fields;
        $this->fields = ["COUNT(*)"];
        if (count($groupby)) {
            $data = $this->getRows($conditions, [], "ASC", $groupby);

            return count($data);
        }
        $data = $this->getRow($conditions);
        $this->fields = $old_fields;

        return $data[0] ?? 0;
    }

    /**
     * Add a row with a proper parameterized statement
     *
     * @param array<string,mixed> $values
     * @param string $pk_column
     * @param null $id
     * @return bool
     */
    public function addRow(array $values, string $pk_column = "id", &$id = null): bool
    {
        return $this->addRows([$values], $pk_column, $id) === 1;
    }

    /**
     * Add multiple rows with proper paramaterized statements
     * This assumes that all rows are identically structured
     * including things like function calls, subqueries, etc.
     *
     * @param array<array<string,mixed>> $rows
     * @param string $pk_column
     * @param null $id the primary key of the last inserted row
     * @param bool $ignore ignore errors during the insert
     * @return int
     */
    public function addRows(array $rows, string $pk_column = "id", &$id = null, bool $ignore = false): int
    {
        $db = $this->getConnection();
        $values = $rows[0];
        $fields = implode(
            ",",
            array_map([self::class, "quote_identifier"], array_keys($values))
        );

        $table = str_contains($this->table, " JOIN ") ? $this->table : $this->quote_identifier($this->table);
        $value_callback = function ($v) use (&$parameters): string {
            // temporary workaround while there are still things like "now()" in value lists
            if (is_null($v)) {
                $v = "NULL";
            } elseif (is_numeric(trim("$v")) && str_starts_with(trim("$v"), "0")) {
                $v = "'$v'";
            } elseif ($this->quote_identifier($v) !== trim("$v")) {
                $parameters[] = trim("$v");
                $v = "?";
            }

            return trim("$v");
        };

        $counter = 0;
        foreach ($rows as $values) {
            $parameters = [];
            $placeholders = implode(",", array_map($value_callback, $values));
            $query = "INSERT INTO $table ($fields) VALUES ($placeholders)";
            class_exists(Console::class) && Console::logQuery($query);
            $result = $db->Execute($query, $parameters);
            class_exists(Console::class) && Console::logQuery($query);
            if ($result === false && !$ignore) {
                $id = $db->Insert_ID($this->table, $pk_column);
                return $counter;
            }
            $counter++;
        }
        $id = $db->Insert_ID($this->table, $pk_column);

        return $counter;
    }

    /**
     * Add rows using INSERT ... SELECT with paramaterized statements
     *
     * @param Table $source
     * @param array $conditions
     * @return int
     */
    public function addRowsFromSelect(Table $source, array $conditions): int
    {
        $db = $this->getConnection();
        $table = $this->quote_identifier($this->table);
        $source_fields = implode(",", array_map([self::class, "quote_identifier"], $source->fields));
        $source_table = $this->quote_identifier($source->table);
        $where = $this->processWhereClauseArray($conditions, $params);
        $query = "INSERT INTO $table SELECT $source_fields FROM $source_table WHERE $where";
        class_exists(Console::class) && Console::logQuery($query);
        $result = $db->Execute($query, $params);
        class_exists(Console::class) && Console::logQuery($query);

        return $result ? $db->Affected_Rows() : 0;
    }

    /**
     * Update a row with a proper parameterized statement
     *
     * @param array $values values indexed by column name
     * @param array $conditions values indexed by column name (will be joined with AND)
     * @return bool
     */
    public function updateRow(array $values, array $conditions = []): bool
    {
        $db = $this->getConnection();
        $value_callback = function ($v) use (&$parameters): string {
            if (is_array($v)) {
                // this allows updates like ["usage" => ["usage + ?", 1]]
                $parameters[] = $v[1];
                $v = $v[0];
            } elseif (is_null($v)) {
                $v = "NULL";
            } elseif (is_numeric(trim("$v")) && str_starts_with(trim("$v"), "0")) {
                $v = "'$v'";
            } elseif ($this->quote_identifier($v) !== trim("$v")) {
                // temporary workaround while there are still things like "now()" in value lists
                $parameters[] = trim("$v");
                $v = "?";
            }

            return trim("$v");
        };

        $parameters = [];
        $table = str_contains($this->table, " JOIN ") ? $this->table : $this->quote_identifier($this->table);
        $updates = array_kv($values, [$this, "quote_identifier"], $value_callback);
        $where = $this->processWhereClauseArray($conditions, $parameters);

        $query = "UPDATE $table SET $updates WHERE $where";
        class_exists(Console::class) && Console::logQuery($query);
        $result = $db->Execute($query, $parameters);
        class_exists(Console::class) && Console::logQuery($query);

        return $result !== false;
    }

    /**
     * Delete a row with a proper parameterized statement
     *
     * @param array $conditions values indexed by column name
     * @param int $limit
     * @return bool
     */
    public function deleteRow(array $conditions = [], int $limit = 0): bool
    {
        $db = $this->getConnection();
        // temporary until proper foreign keys are set up
        foreach ($this->FK_TABLES as $i=>$table) {
            $table = $this->quote_identifier($table);
            $local_key = $this->quote_identifier($this->FK_EDITION_CLAUSE[$i]);
            $foreign_key = $this->FK_ID_VALUE;
            if ($this->FK_DELETE === true) {
                $query = "DELETE FROM $table WHERE $local_key = ?";
            } else {
                $query = "UPDATE $table SET $local_key = -1 WHERE $local_key = ?";
            }
            $db->Execute($query, [$foreign_key]);
        }

        $table = str_contains($this->table, " JOIN ") ? $this->table : $this->quote_identifier($this->table);

        $where = $this->processWhereClauseArray($conditions, $params);
        $query = "DELETE FROM $table WHERE $where";
        if ($limit) {
            $query .= " LIMIT $limit";
        }
        class_exists(Console::class) && Console::logQuery($query);
        $result = $db->Execute($query, $params);
        class_exists(Console::class) && Console::logQuery($query);

        return $result !== false;
    }

    /**
     * Takes an array of data and process it to create an SQL query condition
     * Example input array/output string:
     *  - ["mycol" => "value"] `mycol` = ?
     *  - ["mycol" => "value", "mycol2" => "value2"] `mycol` = ? AND `mycol2` = ?
     *  - [["SUB", ["mycol" => "value", "mycol2" => [">", "value2"]], "OR"]] (`mycol` = ? OR `mycol2` > ?)
     *  - [["SUB", ["mycol" => [["value"], ["value2"]], "OR"] (`mycol` = ? OR `mycol` = ?)
     *    (note even plain values must be in an array of arrays; an array of strings is interpreted as operator/value)
     *  - ["mycol" => "value", ["SUB", ["mycol2" => "value2", "mycol3" => "value3"], "OR"]] `mycol` = ? AND (`mycol2` = ? OR `mycol3` = ?)
     *
     * @param array $where the array of data
     * @param array|null $params parameters for use with the database execution
     * @return string the query clause with placeholders
     */
    public function processWhereClauseArray(array $where, ?array &$params, string $operator = "AND"): string
    {
        $params ??= [];
        if (count($where) === 0) {
            return "1=1";
        }
        $query_clauses = [];
        foreach ($where as $col => $data) {
            if (is_numeric($col) && is_array($data) && count($data) > 1 && $data[0] === "SUB") {
                $clauses = $data[1];
                $suboperator = $data[2] ?? "AND";
                if (is_array($clauses) && array_filter($clauses, fn($v) => is_array($v[0] ?? null))) {
                    // applying multiple conditions to the same column
                    $subquery_clauses = [];
                    foreach ($clauses as $subcol => $subclause) {
                        foreach ($subclause as $subcondition) {
                            $subquery_clauses[] = $this->processConditionClauseArray($subcol, $subcondition, $params);
                        }
                    }
                    $query_clauses[] = "(" . implode($suboperator, $subquery_clauses) . ")";
                } else {
                    // a subclause with multiple different columns
                    $query_clauses[] = "(" . $this->processWhereClauseArray($clauses, $params, $suboperator) . ")";
                }
            } else {
                // just a plain column/value pair
                $query_clauses[] = $this->processConditionClauseArray($col, $data, $params);
            }
        }

        return implode(" $operator ", $query_clauses);
    }

    /**
     * Process a single array for use as part of a WHERE clause
     * Example input parameters/output string:
     *  - $col="mycol",$condition="value" `mycol` = ?
     *  - $col="mycol",$condition=["<", "value"] `mycol` < ?
     *  - $col="mycol",$condition=["!=", null] `mycol` IS NOT NULL
     *  - $col="mycol",$condition=["IN", ["value1", "value2"]] `mycol` IN (?, ?)
     *  - $col="mycol",$condition=["CASE", [3 => "value1", "x" => "value2"]] CASE `mycol` WHEN 3 THEN ? WHEN "x" THEN ? END
     *  - $col="mycol",$condition=["CASE", [3 => "`col2`", "else" => "value2"]] CASE `mycol` WHEN 3 THEN `col2` ELSE ? END
     *  - $col="mycol",$condition=[">", ["othercol + ?", 12]] `mycol` > othercol + ?
     *  - $col="mycol",$condition=["=", ["othercol"]] `mycol` = othercol
     *
     * @param string $col the column name
     * @param mixed $condition either a value or an array with operator and value
     * @param array $params query parameters for the prepared statement
     * @return string the query with placeholders
     */
    private function processConditionClauseArray(string $col, $condition, array &$params): string
    {
        $col = $this->quote_identifier($col);
        if (is_array($condition) && count($condition) === 1) {
            $condition = array_shift($condition);
        }
        $operator = is_array($condition) ? $condition[0] : "=";
        $value = is_array($condition) ? $condition[1] : $condition;
        if ($operator === "IN") {
            $value = is_array($value) ? $value : [$value];
            $placeholder = sprintf(
                "(%s)",
                implode(",", array_fill(0, count($value), "?"))
            );
            $params = array_merge($params, $value);
        } elseif ($operator === "BETWEEN" && is_array($value) && count($value) === 2) {
            if ($this->quote_identifier($value[0]) !== "$value[0]") {
                $params[] = $value[0];
                $placeholder = "?";
            } else {
                $placeholder = $value[0];
            }
            $placeholder .= " AND ";
            if ($this->quote_identifier($value[1]) !== "$value[1]") {
                $params[] = $value[1];
                $placeholder .= "?";
            } else {
                $placeholder .= $value[1];
            }
        } elseif ($operator === "CASE") {
            $conditions = "";
            $else = "";
            $else_param = "";
            foreach ($value as $k => $v) {
                if (strtolower($k) === "else") {
                    if (is_bool($v)) {
                        $else = $v ? "TRUE" : "FALSE";
                    } elseif ($this->quote_identifier("$v") !== "$v") {
                        $else_param = $v;
                        $else = "?";
                    } else {
                        $else = $v;
                    }
                    continue;
                }
                if (is_bool($v)) {
                    $v = $v ? "TRUE" : "FALSE";
                } elseif ($this->quote_identifier("$v") !== "$v") {
                    $params[] = $v;
                    $v = "?";
                }
                if (!is_numeric($k)) {
                    $k = "\"$k\"";
                }
                $conditions .= "WHEN $k THEN $v ";
            }
            if ($else) {
                $conditions .= "ELSE $else";
                if ($else_param) {
                    $params[] = $else_param;
                }
            }

            return " CASE $col $conditions END ";
        } elseif (is_null($value)) {
            if ($operator === "=") {
                $operator = "IS";
            } elseif ($operator === "!=" || $operator === "<>") {
                $operator = "IS NOT";
            }
            $placeholder = "NULL";
        } elseif (is_array($value) && str_contains($value[0], "?") && count($value) > 1) {
            $placeholder = $value[0];
            $params = array_merge($params, array_slice($value, 1));
        } elseif (is_array($value)) {
            $placeholder = $value[0];
        } elseif ($this->quote_identifier("$value") === trim("$value")) {
            // something like a column name passed as RHS
            $placeholder = trim("$value");
        } else {
            $placeholder = "?";
            $params[] = trim("$value");
        }

        return " $col $operator $placeholder ";
    }

    /**
     * Process $this->joins into an SQL string
     *
     * Sample input/output:
     *         ["t2" => ["t1.col", "t2.col"]] gives "LEFT JOIN t2 ON (t1.col = t2.col)"
     *         ["t2" => ["INNER", ["t1.col", "<", "t2.col"]]] gives "INNER JOIN t2 ON (t1.col < t2.col)"
     *         ["t2" => [["t1.col", "=", "t2.col"], "t1.col2", "t2.col2"]] gives "LEFT JOIN t2 ON (t1.col = t2.col AND t1.col2 = t2.col2)"
     *
     * @return string
     */
    private function processJoinedTables(): string
    {
        $joins = [];
        foreach ($this->joins as $table => $conditions) {
            $type = "LEFT";
            $join_types = [
                "inner", "cross", "left", "right", "left outer", "right outer",
                "natural", "natural inner", "natural left", "natural right",
                "natural left outer", "natural right outer",
            ];
            if (is_string($conditions[0]) && in_array(strtolower($conditions[0]), $join_types)) {
                $type = strtoupper(array_shift($conditions));
            }
            $table = $this->quote_identifier($table);
            $condition_clauses = [];
            for ($i = 0; $i < count($conditions); $i++) {
                $condition = $conditions[$i];
                if (is_array($condition) && count($condition) >= 2 && count($condition) <= 3) {
                    $col1 = $this->quote_identifier($condition[0]);
                    $operator = count($condition) > 2 ? $condition[1] : "=";
                    $col2 = count($condition) > 2 ? $condition[2] : $condition[1];
                    // if it comes back from quote_identifer() unchanged, leave it alone
                    if (($quoted = $this->quote_identifier($col2)) !== $col2) {
                        $col2 = $quoted;
                    }
                } elseif (is_string($condition) && is_string($conditions[$i + 1] ?? null)) {
                    $col1 = $this->quote_identifier($condition);
                    $operator = "=";
                    $col2 = $conditions[$i + 1];
                    if (($quoted = $this->quote_identifier($col2)) !== $col2) {
                        $col2 = $quoted;
                    }
                    $i++;
                } else {
                    continue;
                }
                $condition_clauses[] = "$col1 $operator $col2";
            }
            $condition_string = implode(" AND ", $condition_clauses);
            $joins[] = "$type JOIN $table ON ($condition_string)";
        }

        return implode(" ", $joins);
    }
}
