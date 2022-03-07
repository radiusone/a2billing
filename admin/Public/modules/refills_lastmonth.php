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
        case "refills_count":
            $query = "SELECT UNIX_TIMESTAMP(DATE_FORMAT(date, ?)) * 1000 AS period, COUNT(*) FROM cc_logrefill WHERE date >= ? AND date <= CURRENT_TIMESTAMP GROUP BY period ORDER BY period";
            break;
        case "refills_amount":
            $query = "SELECT UNIX_TIMESTAMP(DATE_FORMAT(date, ?)) * 1000 AS period, SUM(credit) FROM cc_logrefill WHERE date >= ? AND date <= CURRENT_TIMESTAMP GROUP BY period ORDER BY period";
            $format = "money";
            break;
        default:
            die();
    }

    $result = DbConnect()->Execute($query, [$dt_fmt, $ck_dt]);
    while ($row = $result->FetchRow()) {
        $max = max($max, $row[1]);
        $data[] = [intval($row[0]), floatval($row[1])];
    }
    $response = ["max" => floatval($max), "data" => $data , "format" => $format];
    header("Content-Type: application/json");
    echo json_encode($response);
    die();
}
?>
<div class="card-text">
    <strong><?= _("Report by") ?>:</strong>&nbsp;<label for="view_refill_day"><?= _("Days") ?></label>&nbsp;<input id="view_refill_day" type="radio" class="period_graph" name="view_refill" value="day" checked="checked" data-graph="#refills_graph"/><label for="view_refill_month"><?= _("Months") ?></label>&nbsp;<input id="view_refill_month" type="radio" class="period_graph" name="view_refill" value="month" data-graph="#refills_graph"/>
</div>
<div class="card-text">
    <strong><?= _("Report Type") ?>:</strong>&nbsp;<label for="refills_count"><?= _("Refill Count") ?></label>&nbsp;<input id="refills_count" type="radio" name="mode_refill" class="update_graph" value="count" checked="checked" data-graph="#refills_graph" data-uri="modules/refills_lastmonth.php"/><label for="refills_amount"><?= _("Refill Amount") ?></label>&nbsp;<input id="refills_amount" type="radio" name="mode_refill" class="update_graph" value="amount" data-graph="#refills_graph" data-uri="modules/refills_lastmonth.php"/>
</div>
<div id="refills_graph" class="dashgraph"></div>
