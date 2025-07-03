<?php

use A2billing\Admin;
use A2billing\Logger;
use A2billing\Table;

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of A2Billing (http://www.a2billing.net/)
 *
 * A2Billing, Commercial Open Source Telecom Billing platform,
 * powered by Star2billing S.L. <http://www.star2billing.com/>
 *
 * @copyright   Copyright © 2004-2015 - Star2billing S.L.
 * @copyright   Copyright © 2022-2025 RadiusOne Inc.
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

require_once __DIR__ . "/../../common/lib/admin.defines.php";

Admin::checkPageAccess(Admin::ACX_CALL_REPORT | Admin::ACX_CUSTOMER);

getpost_ifset(["export_session", "export_type"]);

$export_session ??= "export_data";
$export_type ??= "csv";

if (!is_array($_SESSION[$export_session])) {
    echo gettext("ERROR CSV EXPORT");
} else {
    [$columns, $table, $conditions, $group, $order, $direction] = $_SESSION[$export_session];

    $date = (new DateTime())->format("Y-m-d");
    $myfileName = "dump $date.$export_type";

    $export_data = (new Table($table, $columns))
        ->getRows($conditions, $order, $direction, $group);

    if (empty($export_data)) {
        $db = DbConnect();
        if ($err = $db->ErrorMsg()) {
            $export_data = [["error" => $err]];
        }
    }

    // while DB is still returning numeric indices (should be close to done with that)
    foreach ($export_data as &$row) {
        $row = array_filter($row, fn($k) => !is_numeric($k), ARRAY_FILTER_USE_KEY);
    }
    unset($row);

    if ($export_type === "csv") {
        header("Content-Type: text/csv");
        header("Content-Disposition: attachment;filename=$myfileName");
        $out = fopen("php://output", "w");
        foreach ($export_data as $line) {
            fputcsv($out, $line, ",", "\"", "");
        }
    } else {
        header("Content-Type: application/xml");
        header("Content-Disposition: attachment;filename=$myfileName");
        $dom = new DOMDocument();
        $data = $dom->createElement("data");
        $head = $dom->createElement("header");
        foreach (array_keys($export_data[0]) as $col) {
            $column = $dom->createElement("column");
            $column->setAttribute("name", $col);
            $head->appendChild($column);
        }
        $data->appendChild($head);
        $records = $dom->createElement("records");
        foreach ($export_data as $line) {
            $row = $dom->createElement("row");
            foreach ($line as $col => $value) {
                $column = $dom->createElement("column", $value);
                $column->setAttribute("name", $col);
                $row->appendChild($column);
            }
            $records->appendChild($row);
        }
        $data->appendChild($records);
        $dom->appendChild($data);
        $dom->formatOutput = true;
        echo $dom->saveXML();
    }

    Logger::insertLog($_SESSION["admin_id"], 2, "FILE EXPORTED", "A File is exported by User, File Name= " . $myfileName, '', $_SERVER['REMOTE_ADDR'], $_SERVER['REQUEST_URI'], '');
    die();
}
