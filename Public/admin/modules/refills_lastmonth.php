<?php

use A2billing\Admin;
use A2billing\Connection;
use Illuminate\Database\Query\Builder;

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

    $qb = Connection::getConnection("cc_logrefill")
        ->when(
            $view_type === "month",
            fn (Builder $q) => $q->selectRaw("CONCAT(CAST(date AS VARCHAR(8)), '01') AS period"),
            fn (Builder $q) => $q->selectRaw("CAST(date AS VARCHAR(10)) AS period"),
        )
        ->where("date", ">", $checkdate)
        ->wherePast("date")
        ->orderBy("period")
        ->groupBy("period");

    switch ($type) {
        case "refills_count":
            $qb->selectRaw("COUNT(*) AS agg");
            break;
        case "refills_amount":
            $qb->selectRaw("SUM(credit) AS agg");
            $format = "money";
            break;
        default:
            die();
    }
    $result = $qb->get() ?: [["period" => 0, "agg" => 0]];
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
    <strong><?= _("Report by") ?>:</strong>&nbsp;<label for="view_refill_day"><?= _("Days") ?></label>&nbsp;<input id="view_refill_day" type="radio" class="period_graph" name="view_refill" value="day" checked="checked" data-graph="#refills_graph"/>&nbsp;<label for="view_refill_month"><?= _("Months") ?></label>&nbsp;<input id="view_refill_month" type="radio" class="period_graph" name="view_refill" value="month" data-graph="#refills_graph"/>
</div>
<div class="card-text">
    <strong><?= _("Report Type") ?>:</strong>&nbsp;<label for="refills_count"><?= _("Refill Count") ?></label>&nbsp;<input id="refills_count" type="radio" name="mode_refill" class="update_graph" value="count" checked="checked" data-graph="#refills_graph" data-uri="modules/refills_lastmonth.php"/><label for="refills_amount"><?= _("Refill Amount") ?></label>&nbsp;<input id="refills_amount" type="radio" name="mode_refill" class="update_graph" value="amount" data-graph="#refills_graph" data-uri="modules/refills_lastmonth.php"/>
</div>
<div id="refills_graph" class="dashgraph"></div>
