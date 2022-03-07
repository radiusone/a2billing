<?php

use A2billing\Table;

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

require_once __DIR__ . "/../../lib/admin.defines.php";

if (!has_rights(ACX_DASHBOARD)) {
    header("HTTP/1.0 401 Unauthorized");
    header("Location: PP_error.php?c=accessdenied");
    die();
}

$QUERY_COUNT_CALL_ALL = "select terminatecauseid, COUNT(*) from cc_call WHERE starttime >= DATE(NOW()) GROUP BY terminatecauseid";
$QUERY_COUNT_CALL_BILL = "SELECT SUM(sessiontime), SUM(sessionbill), SUM(buycost) FROM cc_call WHERE starttime >= DATE(NOW())";

$DBHandle = DbConnect();
$table = new Table('cc_call', '*');
$result = $table->SQLExec($DBHandle, $QUERY_COUNT_CALL_ALL);

$count_total = 0;
$counts = [];
foreach ($result as $row) {
    $count_total += $row[1];
    $counts[$row[0]] = $row[1];
    // 1 = answered, 2= no answer, 3 = cancelled, 4 = congested, 5 = busy, 6 = chanunavil
}

$result = $table->SQLExec($DBHandle, $QUERY_COUNT_CALL_BILL);
$call_times = $result[0][0];
$call_sell = a2b_round($result[0][1]);
$call_buy = a2b_round($result[0][2]);
$call_profit = $call_sell - $call_buy;
$curr = $A2B->config["global"]["base_currency"];
?>

<div class="card-text">
    <?= _("Total Calls") ?>&nbsp;:&nbsp;<?= $count_total ?>
    <?= _("Answered") ?>&nbsp;:&nbsp;<?= $counts[1] ?? 0 ?>
    <?= _("Busy") ?>&nbsp;:&nbsp;<?= $counts[5] ?? 0 ?>
    <?= _("Unanswered") ?>&nbsp;:&nbsp;<?= $counts[2] ?? 0 ?>
    <?= _("Cancelled") ?>&nbsp;:&nbsp;<?= $counts[3] ?? 0 ?>
    <?= _("Congestion") ?>&nbsp;:&nbsp;<?= $counts[4] ?? 0 ?>
    <?= _("Unavailable") ?>&nbsp;:&nbsp;<?= $counts[6] ?? 0 ?>
</div>
<div class="card-text">
    <?= _("Sell") ?>&nbsp;:&nbsp;<?= $call_sell ?? 0 ?>&nbsp;<?= $curr ?>
    <?= _("Cost") ?>&nbsp;:&nbsp;<?= $call_buy ?? 0 ?>&nbsp;<?= $curr ?>
    <?= _("Profit") ?>&nbsp;:&nbsp;<?= $call_profit ?? 0 ?>&nbsp;<?= $curr ?>
    <?= _("Duration") ?>&nbsp;:&nbsp;<?= $call_times ?? 0 ?>&nbsp;<?= _("sec") ?>
</div>
