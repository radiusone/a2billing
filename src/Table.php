<?php

namespace A2billing;

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

use ADOConnection;
use Profiler_Console;

/**
 * This file is part of A2Billing (http://www.a2billing.net/)
 *
 * A2Billing, Commercial Open Source Telecom Billing platform,
 * powered by Star2billing S.L. <http://www.star2billing.com/>
 *
 * @copyright   Copyright © 2004-2015 - Star2billing S.L.
 * @copyright   Copyright © 2022 RadiusOne Inc.
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
    public ?string $fields = null;
    public ?string $table = null;
    public array $joins = [];
    public string $errstr = '';
    public bool $debug_st = false;
    public int $debug_st_stop = 0;
    public string $start_message_debug = "<table style=\"float : left;\"><tr><td>QUERY: \n";
    public string $end_message_debug = "\n</td></tr></table><br><br><br>";
    public float $alert_query_time = 0.1;
    public int $alert_query_long_time = 2;

    public bool $writelog = false;

    public array $FK_TABLES = [];
    public ?array $FK_EDITION_CLAUSE = null;
    // FALSE if you want to delete the dependent Records, TRUE if you want to update
    // Dependent Records to -1
    public bool $FK_DELETE = true;
    public int $FK_ID_VALUE = 0;

    public ?Query_trace $query_handler = null;
    public string $db_type = 'mysql';

    /**
     * @param string|null $table the table we're working with
     * @param string $list_fields when selecting, what fields will be selected
     * @param array $joins tables to join to the query; for example:
     *                      ["t2" => ["t1.col", "t2.col"]] gives "LEFT JOIN t2 ON (t1.col = t2.col)"
     *                      ["t2" => ["t1.col", "<", "t2.col", "INNER"]] gives "INNER JOIN t2 ON (t1.col < t2.col)"
     */
    public function __construct(string $table = null, string $list_fields = "*", array $joins = [])
    {
        $this->writelog = defined('WRITELOG_QUERY') && WRITELOG_QUERY;
        $this->table = $table;
        $this->fields = $list_fields;
        $this->joins = $joins;
        if (defined("DB_TYPE") && DB_TYPE === 'postgres') {
            $this->db_type = "postgres";
        }

        $this->query_handler = Query_trace::getInstance();

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

    public function quote_identifier(string $identifier): string
    {
        if (str_contains($identifier, "(")) {
            // something like a function call
            return $identifier;
        }

        $q = $this->db_type === "mysql" ? "`" : "\"";
        $identifier = trim($identifier);
        $distinct = "";
        if (str_starts_with($identifier, "DISTINCT ")) {
            $identifier = trim(substr($identifier, 8));
            $distinct = "DISTINCT ";
        }
        if (str_starts_with($identifier, $q) && str_ends_with($identifier, $q)) {
            // there is plenty of room for abuse here, but assume already quoted values are ok
            return "$distinct$identifier";
        }
        $identifier = str_replace($q, "", $identifier);
        if (str_contains($identifier, ".")) {
            $identifier = implode("$q.$q", explode(".", $identifier));
        }

        return "$distinct$q$identifier$q";
    }

    /*
     * ExecuteQuery
     */
    public function ExecuteQuery(ADOConnection $DBHandle, string $QUERY, int $cache = 0)
    {
        global $A2B;

        $time_start = microtime(true);

        if ($this->db_type === 'postgres') {
            // convert MySQLisms to be Postgres compatible
            $mytopg = new MytoPg(0); // debug level 0 logs only >30ms CPU hogs
            $mytopg->My_to_Pg($QUERY);
        }

        if ($this->debug_st) {
            echo $this->start_message_debug . $QUERY . $this->end_message_debug;
        }
        if ($cache > 0) {
            $res = $DBHandle->CacheExecute($cache, $QUERY);
        } else {
            Profiler_Console::logQuery($QUERY);
            $res = $DBHandle->Execute($QUERY);
            Profiler_Console::logQuery($QUERY);
        }

        if ($DBHandle->ErrorNo() != 0) {
            $this->errstr = $DBHandle->ErrorMsg();
            if ($this->debug_st) {
                echo $DBHandle->ErrorMsg();
            }
            if ($this->debug_st_stop) {
                exit;
            }
        }

        if ($this->writelog) {
            $time_end = microtime(true);
            $time = $time_end - $time_start;
            if ($time > $this->alert_query_time) {
                if ($time > $this->alert_query_long_time) {
                    $A2B->debug(A2Billing::WARN, "EXTRA_TOOLONG_DB_QUERY - RUNNING TIME = $time");
                }
                else {
                    $A2B->debug(A2Billing::WARN, "TOOLONG_DB_QUERY - RUNNING TIME = $time");
                }
            }
            $A2B->debug(A2Billing::DEBUG, "Running time=$time - QUERY=\n$QUERY\n");
        }

        return $res;
    }

    // If $select is not supplied then function check numrows
    // so expect a SELECT query.

    public function SQLExec(ADOConnection $DBHandle, string $QUERY, $select = 1, int $cache = 0)
    {
        $res = $this->ExecuteQuery($DBHandle, $QUERY, $cache);
        if (!$res) {
            return false;
        }

        if ($select) {
            $num = $res->RecordCount();
            if ($num === 0) {
                return false;
            }

            return $res->GetAll();
        }

        return true;
    }


    /**
     * Fetch one or more rows with a proper parameterized statement
     *
     * @param ADOConnection $db
     * @param array $conditions values to match placeholders in $where
     * @param string $order the column(s) to order by (use commas for multiple)
     * @param string $direction either "asc" or "desc"
     * @param int $limit query limit
     * @return array
     */
    public function getRows(ADOConnection $db, array $conditions = [], string $order = "", string $direction = "ASC", int $limit = 0): array
    {
        $table = str_contains($this->table, " JOIN ") ? $this->table : $this->quote_identifier($this->table);
        $table .= " " . $this->processJoinedTables();
        $where = $this->processWhereClauseArray($conditions, $params);
        $direction = strtoupper($direction) === "ASC" ? "ASC" : "DESC";
        $orderings = array_filter(explode(",", $order) ?: []);
        if (!empty($orderings)) {
            // escape column names, but watch for use of subqueries and don't escape them
            array_walk(
                $orderings,
                fn (&$v) => $v = (str_contains($v, "(") ? $v : $this->quote_identifier($v)) . " $direction"
            );
            $order_sql = "ORDER BY " . implode(",", $orderings);
        } else {
            $order_sql = "";
        }
        $limit_sql = $limit ? "LIMIT $limit" : "";

        $query = "SELECT $this->fields FROM $table WHERE $where $order_sql $limit_sql";

        return $db->GetArray($query, $params) ?: [];
    }

    /**
     * Get a single row
     *
     * @param ADOConnection $db
     * @param array $conditions
     * @return array
     */
    public function getRow(ADOConnection $db, array $conditions = []): array
    {
        $data = $this->getRows($db, $conditions, "", "", 1);

        return $data[0] ?? [];
    }

    public function get_list(ADOConnection $DBHandle, string $where = "", string $orderby = "", string $sens = "ASC", int $limite = 0, int $current_record = 0, array $groupby = [])
    {
        $sql = "SELECT $this->fields FROM $this->table";

        $sql_clause = "";
        if (!empty($where)) {
            $sql_clause = " WHERE $where";
        }

        $sql_orderby = "";
        $sens = strtoupper($sens);
        if ($sens !== "ASC" && $sens !== "DESC") {
            $sens = "ASC";
        }
        $order = explode(",", $orderby);
        if (is_array($order)) {
            $order = array_filter($order);
            array_walk(
                $order,
                fn (&$v) => $v = (str_contains($v, "(") ? $v : $this->quote_identifier($v)) . " $sens"
            );
            if (count($order)) {
                $orderby = implode(",", $order);
                $sql_orderby = " ORDER BY $orderby ";
            }
        }

        $sql_limit = "";
        if (is_numeric($limite) && $limite > 0 && is_numeric($current_record)) {
            $sql_limit = " LIMIT $limite OFFSET $current_record";
        }

        $sql_group = "";
        $groupby = array_filter($groupby);
        if (count($groupby)) {
            foreach($groupby as &$col) {
                if (str_contains($col, "(")) {
                    // don't want to parse function calls
                    continue;
                }
                $col = str_replace(
                    ".",
                    $this->quote_identifier("."),
                    $this->quote_identifier(trim($col))
                );
            }
            $sql_group = "GROUP BY " . implode(",", $groupby);
        }

        $QUERY = $sql . $sql_clause . $sql_group;

        if (!str_contains($QUERY, '%ORDER%')) {
            $QUERY .= $sql_orderby;
        } else {
            $QUERY = str_replace("%ORDER%", $sql_orderby, $QUERY);
        }

        if (!str_contains($QUERY, '%LIMIT%')) {
            $QUERY .= $sql_limit;
        } else {
            $QUERY = str_replace("%LIMIT%", $sql_limit, $QUERY);
        }

        $res = $this->ExecuteQuery($DBHandle, $QUERY);
        if (!$res) {
            return false;
        }

        $num = $res->RecordCount();
        if ($num == 0) {
            return [];
        }

        return $res->GetAll();
    }

    public function Table_count(ADOConnection $DBHandle, string $clause = "", string $compare = "", int $cache = 0)
    {
        $sql = "SELECT count(*) FROM $this->table";

        $sql_clause = '';
        if (!empty($clause)) {
            $sql_clause = empty($compare) ? " WHERE $clause" : " WHERE $clause = $compare";
        }

        $QUERY = $sql . $sql_clause;

        $res = $this->ExecuteQuery($DBHandle, $QUERY, $cache);
        if (!$res) {
            return false;
        }

        $row = $res->fetchRow();

        return $row[0];
    }

    /**
     * Add a row with a proper parameterized statement
     *
     * @param ADOConnection $db
     * @param array $values
     * @param array $fields
     * @param $id
     * @return bool
     */
    public function addRow(ADOConnection $db, array $fields, array $values, &$id = null): bool
    {
        array_walk($fields, fn (&$v) => $v = $this->quote_identifier($v));
        $this->fields = implode(",", $fields);

        $table = str_contains($this->table, " JOIN ") ? $this->table : $this->quote_identifier($this->table);
        $placeholders = implode(",", array_map(fn ($v) => $this->quote_identifier($v) === $v ? $v : "?", $values));

        $query = "INSERT INTO $table ($this->fields) VALUES ($placeholders)";

        $result = $db->Execute($query, $values);

        if ($result === false) {
            return false;
        }
        $id = $db->Insert_ID();

        return true;
    }

    public function Add_table(ADOConnection $DBHandle, string $value, string $func_fields = "", string $func_table = "", string $id_name = "", bool $subquery = false)
    {
        if ($func_fields !== "") {
            $this->fields = $func_fields;
        }

        if ($func_table !== "") {
            $this->table = $func_table;
        }
        if ($subquery) {
            $QUERY = "INSERT INTO " . $this->table . " (" . $this->fields . ") (" . trim($value) . ")";
        } else {
            $QUERY = "INSERT INTO " . $this->table . " (" . $this->fields . ") values (" . trim($value) . ")";
        }

        $res = $this->ExecuteQuery($DBHandle, $QUERY);
        if (!$res) {
            return false;
        }

        // Fix that , make PEAR complaint
        if ($id_name !== "") {
            $insertid = $DBHandle->Insert_ID();
            if ($this->db_type === "postgres") {
                if (!$insertid) {
                    return true;
                }
                $sql = "SELECT $id_name FROM $this->table WHERE oid = '$insertid'";
                $res = $DBHandle->Execute($sql);
                if (!$res) {
                    return false;
                }
                $row = $res->fetchRow();

                $insertid = $row[0];

            }
            if ($this->debug_st) {
                echo "\n <br> insert_id = $insertid";
            }

            return $insertid;
        }

        return true;
    }

    /**
     * Update a row with a proper parameterized statement
     *
     * @param ADOConnection $db
     * @param array $fields
     * @param array $values
     * @param array $conditions
     * @return bool
     */
    public function updateRow(ADOConnection $db, array $fields, array $values, array $conditions = []): bool
    {
        array_walk($fields, fn (&$v) => $v = $this->quote_identifier($v));
        $this->fields = implode(",", $fields);

        $table = str_contains($this->table, " JOIN ") ? $this->table : $this->quote_identifier($this->table);
        $placeholders = implode(",", array_map(fn ($v) => $this->quote_identifier($v) === $v ? $v : "?", $values));

        $where_params = array_filter(
            array_values($conditions),
            fn ($v) => $this->quote_identifier($v) !== $v
        );
        $where = count($conditions) > 0
            ? array_kv(
                $conditions,
                [$this, "quote_identifier"],
                fn ($v) => $this->quote_identifier($v) === $v ? $v : "?",
                " = ",
                " AND "
            )
            : "1=1";

        $query = "INSERT INTO $table ($this->fields) VALUES ($placeholders) WHERE $where";

        return $db->Execute($query, array_merge($values, $where_params)) !== false;
    }

    public function Update_table(ADOConnection $DBHandle, string $param_update, string $clause, string $func_table = "")
    {

        if ($func_table !== "") {
            $this->table = $func_table;
        }

        $QUERY = "UPDATE " . $this->table . " SET " . trim($param_update) . " WHERE " . trim($clause);
        $res = $this->ExecuteQuery($DBHandle, $QUERY);

        return($res);
    }

    /**
     * Delete a row with a proper parameterized statement
     *
     * @param ADOConnection $db
     * @param array $conditions values to match placeholders in $where
     * @return bool
     */
    public function deleteRow(ADOConnection $db, array $conditions = []): bool
    {
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

        $params = array_filter(
            array_values($conditions),
            fn ($v) => $this->quote_identifier($v) !== $v
        );
        $where = count($conditions) > 0
            ? array_kv(
                $conditions,
                [$this, "quote_identifier"],
                fn ($v) => $this->quote_identifier($v) === $v ? $v : "?",
                " = ",
                " AND "
            )
            : "1=1";
        $query = "DELETE FROM $table WHERE $where";

        return $db->Execute($query, $params) !== false;
    }

    public function Delete_table(ADOConnection $DBHandle, string $clause, string $func_table = "")
    {

        if ($func_table !== "") {
            $this->table = $func_table;
        }

        foreach ($this->FK_TABLES as $i => $table) {
            $table = $this->quote_identifier($table);
            if ($this->FK_DELETE === false) {
                $QUERY = "UPDATE $table SET {$this->FK_EDITION_CLAUSE[$i]} = -1 WHERE {$this->FK_EDITION_CLAUSE[$i]} = $this->FK_ID_VALUE";
            } else {
                $QUERY = "DELETE FROM $table WHERE {$this->FK_EDITION_CLAUSE[$i]} = $this->FK_ID_VALUE";
            }
            if ($this->debug_st) {
                echo "<br>$QUERY";
            }
            $DBHandle->Execute($QUERY);
        }

        $table = $this->quote_identifier($this->table);
        $query = "DELETE FROM $table WHERE ($clause)";

        return $this->ExecuteQuery($DBHandle, $query);
    }

    public function Delete_Selected(ADOConnection $DBHandle, string $clause = "")
    {
        $table = $this->quote_identifier($this->table);
        $query = "DELETE FROM  $table WHERE ($clause)";

        return $this->ExecuteQuery($DBHandle, $query);
    }

    /**
     * Takes an array of data and process it to create an SQL query condition
     *
     * @param array $where the array of data
     * @param array|null $params parameters for use with the database execution
     * @return string the query clause with placeholders
     */
    public function processWhereClauseArray(array $where, ?array &$params): string
    {
        $params = [];
        $query_clauses = [];
        foreach ($where as $col => $data) {
            $query = "";
            if (is_numeric($col) && is_array($data) && count($data) > 1 && $data[0] === "SUB") {
                $clauses = $data[1];
                $operator = $data[2] ?? "AND";
                if (is_array($clauses)) {
                    $subclauses = [];
                    foreach ($clauses as $subclause) {
                        foreach ($subclause as $subcol => $subcondition) {
                            $subclauses[] = $this->processConditionClauseArray($subcol, $subcondition, $params);
                        }
                    }
                    if (count($subclauses)) {
                        $query .= " ( ";
                        $query .= implode(" $operator ", $subclauses);
                        $query .= " ) ";
                    }
                }
                $query_clauses[] = $query;
                continue;
            }
            $query_clauses[] = $this->processConditionClauseArray($col, $data, $params);
        }

        return implode(" AND ", $query_clauses);
    }

    /**
     * Process a single array for use as part of a WHERE clause
     *
     * @param string $col the column name
     * @param mixed $condition either a value or an array with operator and value
     * @param array $params query parameters for the prepared statement
     * @return string the query with placeholders
     */
    private function processConditionClauseArray(string $col, $condition, array &$params): string
    {
        $col = $this->quote_identifier($col);
        $operator = is_array($condition) ? $condition[0] : "=";
        $value = is_array($condition) ? $condition[1] : $condition;
        if ($operator === "IN") {
            $value = is_array($value) ? $value : [$value];
            $placeholder = sprintf(
                "(%s)",
                implode(",", array_fill(0, count($value), "?"))
            );
            $params = array_merge($params, $value);
        } elseif ($this->quote_identifier("$value") === "$value") {
            // something like a column name passed as RHS
            $placeholder = $value;
        } else {
            $placeholder = "?";
            $params[] = $value;
        }

        return " $col $operator $placeholder ";
    }

    private function processJoinedTables(): string
    {
        $joins = [];
        foreach ($this->joins as $table => $conditions) {
            if (count($conditions) < 2 || count($conditions) > 4) {
                continue;
            }
            $type = count($conditions) === 4 ? $conditions[3] : "LEFT";
            $table = $this->quote_identifier($table);
            $col1 = $this->quote_identifier($conditions[0]);
            $operator = count($conditions) > 2 ? $conditions[1] : "=";
            $col2 = count($conditions) > 2 ? $conditions[2] : $conditions[1];
            $col2 = $this->quote_identifier($col2);
            $joins[] = "$type JOIN $table ON ($col1 $operator $col2)";
        }

        return implode(" ", $joins);
    }
}
