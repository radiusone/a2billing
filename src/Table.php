<?php

namespace A2billing;

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

use ADOConnection;

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
    public string $errstr = '';
    public bool $debug_st = false;
    public int $debug_st_stop = 0;
    public string $start_message_debug = "<table width=\"100%\" align=\"right\" style=\"float : left;\"><tr><td>QUERY: \n";
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
     * @param string $liste_fields when selecting, what fields will be selected
     * @param array $fk_Tables when deleting, tables that refer back to the current table
     * @param array $fk_Fields when deleting, fields in the foreign tables that need updating/deleting
     * @param int|null $id_Value when deleting, the value to check for in foreign tables when updating/deleting
     * @param bool $fk_del_upd when deleting, whether to delete or update (with -1) foreign tables
     */
    public function __construct(string $table = null, string $liste_fields = "*", array $fk_Tables = [], array $fk_Fields = [], int $id_Value = null, bool $fk_del_upd = true)
    {
        $this->writelog = defined('WRITELOG_QUERY') && WRITELOG_QUERY;
        $this->table = $table;
        $this->fields = $liste_fields;
        if (DB_TYPE === 'postgres') {
            $this->db_type = "postgres";
        }

        if ((count($fk_Tables) == count($fk_Fields)) && (count($fk_Fields) > 0)) {
            $this->FK_TABLES         = $fk_Tables;
            $this->FK_EDITION_CLAUSE = $fk_Fields;
            $this->FK_DELETE         = $fk_del_upd;
            $this->FK_ID_VALUE       = $id_Value;
        }

        $this->query_handler = Query_trace::getInstance();

    }

    public function quote_identifier(string $identifier): string
    {
        $q = $this->db_type === "mysql" ? "`" : "\"";
        return $q . str_replace($q, "", $identifier) . $q;
    }

    /*
     * ExecuteQuery
     */
    public function ExecuteQuery(ADOConnection $DBHandle, string $QUERY, $cache = 0)
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
            $start = microtime(true);
            $res = $DBHandle->Execute($QUERY);
            $this->query_handler->queryCount += 1;
            $this->logQuery($QUERY, $start);
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

    public function SQLExec(ADOConnection $DBHandle, string $QUERY, $select = 1, $cache = 0)
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

    public function get_list(ADOConnection $DBHandle, string $clause = "", array $orderby = [], $sens = "ASC", $limite = 0, $current_record = 0, array $groupby = [], $cache = 0)
    {
        $sql = "SELECT $this->fields FROM $this->table";

        $sql_clause = "";
        if (!empty($clause)) {
            $sql_clause = " WHERE $clause";
        }

        $sql_orderby = "";
        $sens = strtoupper($sens);
        if ($sens !== "ASC" && $sens !== "DESC") {
            $sens = "ASC";
        }
        array_filter($orderby);
        if (count($orderby)) {
            foreach ($orderby as &$col) {
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
            $orderby = implode(",", $orderby);
            $sql_orderby = " ORDER BY $orderby $sens";
        }

        $sql_limit = "";
        if (is_numeric($limite) && $limite > 0 && is_numeric($current_record)) {
            $sql_limit = " LIMIT $limite OFFSET $current_record";
        }

        $sql_group = "";
        array_filter($groupby);
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

        $res = $this->ExecuteQuery($DBHandle, $QUERY, $cache);
        if (!$res) {
            return false;
        }

        $num = $res->RecordCount();
        if ($num == 0) {
            return 0;
        }

        $row = [];
        for ($i = 0; $i < $num; $i++) {
            $row[] = $res->fetchRow();
        }

        return($row);
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

    public function Add_table(ADOConnection $DBHandle, $value, string $func_fields = "", string $func_table = "", string $id_name = "", bool $subquery = false)
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

    public function Update_table(ADOConnection $DBHandle, string $param_update, string $clause, string $func_table = "")
    {

        if ($func_table !== "") {
            $this->table = $func_table;
        }

        $QUERY = "UPDATE " . $this->table . " SET " . trim($param_update) . " WHERE " . trim($clause);
        $res = $this->ExecuteQuery($DBHandle, $QUERY);

        return($res);
    }

    public function Delete_table(ADOConnection $DBHandle, string $clause, string $func_table = "")
    {

        if ($func_table !== "") {
            $this->table = $func_table;
        }

        $countFK = count($this->FK_TABLES);
        for ($i = 0; $i < $countFK; $i++) {
            if ($this->FK_DELETE === false) {
                $QUERY = "UPDATE " . $this->FK_TABLES[$i] . " SET ".
                            trim($this->FK_EDITION_CLAUSE[$i]) . " = -1 WHERE (" . trim($this->FK_EDITION_CLAUSE[$i]) . " = " . $this->FK_ID_VALUE . " )";
            } else {
                $QUERY = "DELETE FROM " . $this->FK_TABLES[$i].
                            " WHERE (" . trim($this->FK_EDITION_CLAUSE[$i]) . " = " . $this->FK_ID_VALUE . " )";
            }
            if ($this->debug_st) {
                echo "<br>$QUERY";
            }
            $DBHandle->Execute($QUERY);
        }

        $QUERY = "DELETE FROM " . $this->table . " WHERE (" . trim($clause) . ")";
        $res = $this->ExecuteQuery($DBHandle, $QUERY);

        return($res);
    }

    public function Delete_Selected(ADOConnection $DBHandle, string $clause = "")
    {
        $QUERY = 'DELETE FROM ' . $this->quote_identifier($this->table);
        if ($clause) {
            $QUERY .= "WHERE $clause";
        }

        return $this->ExecuteQuery($DBHandle, $QUERY);
    }

    public function logQuery(string $sql, float $start)
    {
        if (count($this->query_handler->queries) < 100) {
            $this->query_handler->queries[] = [
                'sql' => $sql,
                'time' => microtime(true) - $start
            ];
        }
    }
}
