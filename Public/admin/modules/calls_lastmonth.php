<?php

use A2billing\Admin;
use A2billing\Connection;
use Illuminate\Database\Query\Builder;

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

require_once __DIR__ . "/../../../common/lib/admin.defines.php";

Admin::checkPageAccess(Admin::ACX_DASHBOARD);

getpost_ifset(["type", "view_type"]);
/**
 * @var string $type
 * @var string $view_type
 */

if (!empty($type) && !empty($view_type)) {
    $format = "";
    $data = [];

    $checkdate = $view_type === "month"
        ? (new DateTime('midnight first day of this month -6 months 15 days'))
        : (new DateTime('midnight -10 days'));

    $qb = Connection::getConnection("cc_call")
        ->when(
            $view_type === "month",
            fn (Builder $q) => $q->selectRaw("CONCAT(CAST(starttime AS VARCHAR(8)), '01') AS period"),
            fn (Builder $q) => $q->selectRaw("CAST(starttime AS VARCHAR(10)) AS period")
        )
        ->where("starttime", ">=", $checkdate)
        ->wherePast("starttime")
        ->orderBy("period")
        ->groupBy("period");

    switch ($type) {
        case "call_answer":
            $agg_column = "COUNT(*)";
            $qb->where("terminatecauseid", 1);
            break;
        case "call_incomplet":
            $agg_column = "COUNT(*)";
            $qb->where("terminatecauseid", "!=", 1);
            break;
        case "call_times":
            $agg_column = "SUM(sessiontime)";
            $format = "time";
            break;
        case "call_sell":
            $agg_column = "SUM(sessionbill)";
            $format = "money";
            break;
        case "call_buy":
            $agg_column = "SUM(buycost)";
            $format = "money";
            break;
        case "call_profit":
            $agg_column = "SUM(sessionbill) - SUM(buycost)";
            $format = "money";
            break;
        default:
            die();
    }

    $result = $qb->selectRaw("$agg_column AS agg")
        ->get() ?: [["period" => 0, "agg" => 0]];

    foreach ($result as $row) {
        $period = DateTime::createFromFormat("Y-m-d", $row["period"]);
        $data[] = [
            $period ? intval($period->format("U")) * 1000 : 0,
            floatval($row["agg"]),
        ];
    }
    $response = [
        "max" => floatval(max(array_column($data, 1))),
        "data" => $data,
        "format" => $format,
    ];
    header("Content-Type: application/json");
    echo json_encode($response);
    die();
}
?>
<div class="card-text">
    <strong><?= _("Report by") ?>:</strong>&nbsp;<label for="view_call_day"><?= _("Days") ?></label>&nbsp;<input id="view_call_day" type="radio" class="period_graph" name="view_call" checked="checked" value="day" data-graph="#call_graph"/>&nbsp;<label for="view_call_month"><?= _("Months") ?></label>&nbsp;<input id="view_call_month" type="radio" class="period_graph" name="view_call" value="month" data-graph="#call_graph"/>
</div>
<div class="card-text">
    <strong><?= _("Report Type") ?>:</strong>&nbsp;<label for="call_answer"><?= _("Answered") ?></label>&nbsp;<input id="call_answer" type="radio" class="update_graph" name="mode_call" value="answered" checked="checked" data-graph="#call_graph" data-uri="modules/calls_lastmonth.php"/>&nbsp;<label for="call_incomplet"><?= _("Incomplete") ?></label>&nbsp;<input id="call_incomplet" type="radio" class="update_graph" name="mode_call" value="incomplet" data-graph="#call_graph" data-uri="modules/calls_lastmonth.php"/>&nbsp;<label for="call_times"><?= _("Duration") ?></label>&nbsp;<input id="call_times" type="radio" class="update_graph" name="mode_call" value="times" data-graph="#call_graph" data-uri="modules/calls_lastmonth.php"/>
    <label for="call_sell"><?= _("Sell") ?></label>&nbsp;<input id="call_sell" type="radio" class="update_graph" name="mode_call" value="sell" data-graph="#call_graph" data-uri="modules/calls_lastmonth.php"/>&nbsp;<label for="call_buy"><?= _("Cost") ?></label>&nbsp;<input id="call_buy" type="radio" class="update_graph" name="mode_call" value="buy" data-graph="#call_graph" data-uri="modules/calls_lastmonth.php"/>&nbsp;<label for="call_profit"><?= _("Profit") ?></label>&nbsp;<input id="call_profit" type="radio" class="update_graph" name="mode_call" value="profit" data-graph="#call_graph" data-uri="modules/calls_lastmonth.php"/>
</div>
<div id="call_graph" class="dashgraph"></div>
