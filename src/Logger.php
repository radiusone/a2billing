<?php

namespace A2billing;

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of A2Billing (http://www.a2billing.net/)
 *
 * A2Billing, Commercial Open Source Telecom Billing platform,
 * powered by Star2billing S.L. <http://www.star2billing.com/>
 *
 * @copyright   Copyright © 2004-2015 - Star2billing S.L.
 * @copyright   Copyright © 2022 RadiusOne Inc.
 * @author      Belaid Arezqui <areski@gmail.com>
 * @author      Michael Newton <mnewton@goradiusone.com>
 * @license     http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @package     A2Billing
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

class Logger
{
    public bool $do_debug = false;

    public function insertLog($userID, $logLevel, $actionPerformed, $description, $tableName, $ipAddress, $pageName, $fields = '', $values = [], $agent = false)
    {
        $DB_Handle = DbConnect();
        $pageName = basename($pageName);
        $interName = explode('?', $pageName);
        $pageName = array_shift($interName);
        $description = str_replace("'", "", $description);
        if (is_array($fields)) {
            $pairs = [];
            foreach ($fields as $i => $field) {
                $pairs[] = $field . " = " . $values[$i] ?? "null";
            }
            $data = implode("|", $pairs);
        } else {
            $data = $fields;
        }

        $columns = ["iduser", "loglevel", "action", "description", "tablename", "pagename", "ipaddress", "data"];
        $params = [$userID, $logLevel, $actionPerformed, $description, $tableName, $pageName, $ipAddress, $data];
        if ($agent) {
            $columns[] = "agent";
            $params[] = 1;
        }
        $query = "INSERT INTO cc_system_log (" . implode(",", $columns) . ") VALUES (";
        $query .= implode(",", array_fill(0, count($columns), "?")) . ")";
        if ($this->do_debug) {
            echo $query;
        }

        $DB_Handle->Execute($query, $params);
    }
}
