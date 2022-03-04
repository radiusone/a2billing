<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of A2Billing (http://www.a2billing.net/)
 *
 * A2Billing, Commercial Open Source Telecom Billing platform,
 * powered by Star2billing S.L. <http://www.star2billing.com/>
 *
 * @copyright   Copyright (C) 2004-2015 - Star2billing S.L.
 * @author      Belaid Arezqui <areski@gmail.com>
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

use A2billing\Table;

require_once __DIR__ . "/../../lib/admin.defines.php";

if (!has_rights(ACX_DASHBOARD)) {
    header("HTTP/1.0 401 Unauthorized");
    header("Location: PP_error.php?c=accessdenied");
    die();
}

getpost_ifset(["type", "view_type"]);
/**
 * @var string $type
 * @var string $view_type
 */

if (!empty($type) && !empty($view_type)) {
    $format = "";
    $max = 0;
    $data = [];

    $ck_dt = $view_type === "month" ? $checkdate_month : $checkdate_day;
    $dt_fmt = $view_type === "month" ? "%Y-%m-01" : "%Y-%m-%d";
    switch ($type) {
        case "card_creation":
            $query = "SELECT UNIX_TIMESTAMP(DATE_FORMAT(creationdate,'$dt_fmt')) * 1000 AS period, COUNT(*) FROM cc_card WHERE creationdate >= TIMESTAMP('$ck_dt') AND creationdate <= CURRENT_TIMESTAMP GROUP BY period ORDER BY period";
            break;
        case "card_expiration":
            $query = "SELECT UNIX_TIMESTAMP(DATE_FORMAT(expirationdate,'$dt_fmt')) * 1000 AS period, COUNT(*) FROM cc_card WHERE expirationdate >= TIMESTAMP('$ck_dt') AND expirationdate <= CURRENT_TIMESTAMP GROUP BY period ORDER BY period";
            break;
        case "card_firstuse":
            $query = "SELECT UNIX_TIMESTAMP(DATE_FORMAT(firstusedate,'$dt_fmt')) * 1000 AS period, COUNT(*) FROM cc_card WHERE firstusedate >= TIMESTAMP('$ck_dt') AND firstusedate <= CURRENT_TIMESTAMP GROUP BY period ORDER BY period";
            break;
        default:
            die();
    }


    $result = (new Table())->SQLExec(DbConnect(), $query);
    if (is_array($result_graph)) {
        foreach ($result as $row) {
            $max = max($max, $row[1]);
            $data[] = [$row[0], floatval($row[1])];
        }
    }
    $response = ["max" => floatval($max), "data" => $data , "format" => $format];
    header("Content-Type: application/json");
    echo json_encode($response);
    die();
}
?>
<div class="card-text">
    <strong><?= _("Report by") ?>:</strong>&nbsp;<label for="view_customer_day"><?= _("Days") ?></label>&nbsp;<input id="view_customer_day" type="radio" class="period_graph" name="view_cust" value="day" data-graph="#cust_graph"><label for="view_customer_month"><?= _("Months") ?></label>&nbsp;<input id="view_customer_month" type="radio" class="period_graph" name="view_cust" value="month" data-graph="#cust_graph">
</div>
<div class="card-text">
    <strong><?= _("Report Type") ?>:</strong>&nbsp;<label for="card_creation"><?= _("Creation") ?></label>&nbsp;<input id="card_creation" type="radio" class="update_graph" name="mode_cust" value="CreationDate" checked="checked" data-graph="#cust_graph" data-url="modules/customers_lastmonth.php">&nbsp;<label for="card_expiration"><?= _("Expiration") ?></label>&nbsp;<input id="card_expiration" type="radio" class="update_graph" name="mode_cust" value="ExpirationDate" data-graph="#cust_graph" data-url="modules/customers_lastmonth.php"><label for="card_firstuse"><?= _("First Use") ?></label>&nbsp;<input id="card_firstuse" type="radio" class="update_graph" name="mode_cust" value="FirstUse" data-graph="#cust_graph" data-url="modules/customers_lastmonth.php">
</div>
<div id="cust_graph" class="dashgraph"></div>
