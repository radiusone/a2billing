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

    $checkdate_month = (new DateTime('midnight first day of this month -6 months 15 days'))->format("Y-m-d");
    $checkdate_day = (new DateTime('midnight -10 days'))->format("Y-m-d");

    $ck_dt = $view_type === "month" ? $checkdate_month : $checkdate_day;
    $dt_fmt = $view_type === "month" ? "%Y-%m-01" : "%Y-%m-%d";
    switch ($type) {
        case "call_answer":
            $query = "SELECT UNIX_TIMESTAMP(DATE_FORMAT(starttime,'$dt_fmt')) * 1000 AS period, COUNT(*) FROM cc_call WHERE starttime >= '$ck_dt' AND starttime <= CURRENT_TIMESTAMP AND terminatecauseid = 1 GROUP BY period ORDER BY period";
            break;
        case "call_incomplet":
            $query = "SELECT UNIX_TIMESTAMP(DATE_FORMAT(starttime,'$dt_fmt')) * 1000 AS period, COUNT(*) FROM cc_call WHERE starttime >= '$ck_dt' AND starttime <= CURRENT_TIMESTAMP AND terminatecauseid != 1 GROUP BY period ORDER BY period";
            break;
        case "call_times":
            $query = "SELECT UNIX_TIMESTAMP(DATE_FORMAT(starttime,'$dt_fmt')) * 1000 AS period, SUM(sessiontime) FROM cc_call WHERE starttime >= '$ck_dt' AND starttime <= CURRENT_TIMESTAMP GROUP BY period ORDER BY period";
            $format = "time";
            break;
        case "call_sell":
            $query = "SELECT UNIX_TIMESTAMP(DATE_FORMAT(starttime,'$dt_fmt')) * 1000 AS period, SUM(sessionbill) FROM cc_call WHERE starttime >= '$ck_dt' AND starttime <= CURRENT_TIMESTAMP GROUP BY period ORDER BY period";
            $format = "money";
            break;
        case "call_buy":
            $query = "SELECT UNIX_TIMESTAMP(DATE_FORMAT(starttime,'$dt_fmt')) * 1000 AS period, SUM(buycost) FROM cc_call WHERE starttime >= '$ck_dt' AND starttime <= CURRENT_TIMESTAMP GROUP BY period ORDER BY period";
            $format = "money";
            break;
        case "call_profit":
            $query = "SELECT UNIX_TIMESTAMP(DATE_FORMAT(starttime,'$dt_fmt')) * 1000 AS period, SUM(sessionbill) - SUM(buycost) FROM cc_call WHERE starttime >= '$ck_dt' AND starttime <= CURRENT_TIMESTAMP GROUP BY period ORDER BY period";
            $format = "money";
            break;
        default:
            die();
    }

    $result = (new Table())->SQLExec(DbConnect(), $query);
    if (is_array($result)) {
        foreach ($result as $row) {
            $max = max($max, $row[1]);
            $data[] = [intval($row[0]), floatval($row[1])];
        }
    }
    $response = ["max" => floatval($max), "data" => $data , "format" => $format];
    header("Content-Type: application/json");
    echo json_encode($response);
    die();
}
?>
<div class="card-text">
    <strong><?= _("Report by") ?>:</strong>&nbsp;<label for="view_call_day"><?= _("Days") ?></label>&nbsp;<input id="view_call_day" type="radio" class="period_graph" name="view_call" value="day" data-graph="#call_graph"/>&nbsp;<label for="view_call_month"><?= _("Months") ?></label>&nbsp;<input id="view_call_month" type="radio" class="period_graph" name="view_call" value="month" data-graph="#call_graph"/>
</div>
<div class="card-text">
    <strong><?= _("Report Type") ?>:</strong>&nbsp;<label for="call_answer"><?= _("Answered") ?></label>&nbsp;<input id="call_answer" type="radio" class="update_graph" name="mode_call" value="answered" data-graph="#call_graph" data-uri="modules/calls_lastmonth.php"/>&nbsp;<label for="call_incomplet"><?= _("Incomplete") ?></label>&nbsp;<input id="call_incomplet" type="radio" class="update_graph" name="mode_call" value="incomplet" data-graph="#call_graph" data-uri="modules/calls_lastmonth.php"/>&nbsp;<label for="call_times"><?= _("Duration") ?></label>&nbsp;<input id="call_times" type="radio" class="update_graph" name="mode_call" value="times" data-graph="#call_graph" data-uri="modules/calls_lastmonth.php"/>
    <label for="call_sell"><?= _("Sell") ?></label>&nbsp;<input id="call_sell" type="radio" class="update_graph" name="mode_call" value="sell" data-graph="#call_graph" data-uri="modules/calls_lastmonth.php"/>&nbsp;<label for="call_buy"><?= _("Cost") ?></label>&nbsp;<input id="call_buy" type="radio" class="update_graph" name="mode_call" value="buy" data-graph="#call_graph" data-uri="modules/calls_lastmonth.php"/>&nbsp;<label for="call_profit"><?= _("Profit") ?></label>&nbsp;<input id="call_profit" type="radio" class="update_graph" name="mode_call" value="profit" checked="checked" data-graph="#call_graph" data-uri="modules/calls_lastmonth.php"/>
</div>
<div id="call_graph" class="dashgraph"></div>
