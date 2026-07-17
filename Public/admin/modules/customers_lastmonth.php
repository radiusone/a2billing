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
    $data = [];

    $checkdate = $view_type === "month"
        ? (new DateTime('midnight first day of this month -6 months 15 days'))
        : (new DateTime('midnight -10 days'));

    switch ($type) {
        case "card_creation":
            $period_column = "creationdate";
            break;
        case "card_expiration":
            $period_column = "expirationdate";
            break;
        case "card_firstuse":
            $period_column = "firstusedate";
            break;
        default:
            die();
    }

    $result = Connection::getConnection("cc_card")
        ->selectRaw("COUNT(*) AS agg")
        ->when(
            $view_type === "month",
            fn (Builder $q) => $q->selectRaw("CONCAT(CAST($period_column AS VARCHAR(8)), '01') AS period"),
            fn (Builder $q) => $q->selectRaw("CAST($period_column AS VARCHAR(10)) AS period")
        )
        ->where($period_column, ">=", $checkdate)
        ->wherePast($period_column)
        ->orderBy("period")
        ->groupBy("period")
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
        "format" => "",
    ];
    header("Content-Type: application/json");
    echo json_encode($response);
    die();
}
?>
<div class="card-text">
    <strong><?= _("Report by") ?>:</strong>&nbsp;<label for="view_customer_day"><?= _("Days") ?></label>&nbsp;<input id="view_customer_day" type="radio" class="period_graph" name="view_cust" checked="checked" value="day" data-graph="#cust_graph">&nbsp;<label for="view_customer_month"><?= _("Months") ?></label>&nbsp;<input id="view_customer_month" type="radio" class="period_graph" name="view_cust" value="month" data-graph="#cust_graph">
</div>
<div class="card-text">
    <strong><?= _("Report Type") ?>:</strong>&nbsp;<label for="card_creation"><?= _("Creation") ?></label>&nbsp;<input id="card_creation" type="radio" class="update_graph" name="mode_cust" value="CreationDate" checked="checked" data-graph="#cust_graph" data-uri="modules/customers_lastmonth.php">&nbsp;<label for="card_expiration"><?= _("Expiration") ?></label>&nbsp;<input id="card_expiration" type="radio" class="update_graph" name="mode_cust" value="ExpirationDate" data-graph="#cust_graph" data-uri="modules/customers_lastmonth.php"><label for="card_firstuse"><?= _("First Use") ?></label>&nbsp;<input id="card_firstuse" type="radio" class="update_graph" name="mode_cust" value="FirstUse" data-graph="#cust_graph" data-uri="modules/customers_lastmonth.php">
</div>
<div id="cust_graph" class="dashgraph"></div>
