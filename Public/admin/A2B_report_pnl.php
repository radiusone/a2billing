<?php

use A2billing\A2Billing;
use A2billing\Admin;
use A2billing\Forms\FormHandler;
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

$menu_section = 5;
require_once __DIR__ . "/../../common/lib/admin.defines.php";
/**
 * @var A2Billing $A2B
 * @var string|null $order
 * @var string|null $sens
 * @var numeric-string|null $current_page
 */
Admin::checkPageAccess(Admin::ACX_CALL_REPORT);

getpost_ifset([
    "enable_starttime_start", "starttime_start", "enable_starttime_end", "starttime_end", "enable_starttime_end_recent", "starttime_end_recent", "report_type", "group_id", "posted_search",
]);
/**
 * @var 'true'|null $enable_starttime_start
 * @var string|null $starttime_start
 * @var 'true'|null $enable_starttime_end
 * @var string|null $starttime_end
 * @var 'true'|null $enable_starttime_end_recent
 * @var string|null $starttime_end_recent
 * @var string|null $report_type
 * @var numeric-string|null $group_id
 * @var numeric-string|null $posted_search
 */
$conditions = ["1 = 1"];
// these need to be manually processed to build the temp table
if (isset($enable_starttime_start) || isset($enable_starttime_end)) {
    if (isset($enable_starttime_start) && isset($starttime_start)) {
        $starttime_start = (new DateTime($starttime_start))->format("Y-m-d H:i:s");
        $conditions[] = "starttime >= '$starttime_start'";
    }
    if (isset($enable_starttime_end) && isset($starttime_end)) {
        $starttime_end = (new DateTime($starttime_end))->format("Y-m-d H:i:s");
        $conditions[] = "starttime <= '$starttime_end'";
    }
}
$condition = implode(" AND ", $conditions);

$group_id ??= null;
if (empty($report_type)) {
    $report_type = 1;
}
$posted_search ??= 0;

// save conditions for later use
if ($posted_search === "1") {
    $_SESSION['pnl_condition'] = $condition;
    $_SESSION['group_id'] = "";
    $_SESSION['report_type'] = $report_type;
} else {
    if (!empty($_SESSION['pnl_condition'])) {
        $condition = $_SESSION['pnl_condition'];
    }
    if (!empty($_SESSION['report_type'])) {
        $report_type = $_SESSION['report_type'];
    }
}
if (isset($group_id)) {
    $_SESSION['group_id'] = $group_id;
} elseif (!empty($_SESSION['group_id'])) {
    $group_id = $_SESSION['group_id'];
}

$condition1 = str_replace('starttime','date',$condition);
$condition2 = str_replace('starttime','firstusedate',$condition);

$dnids = "";
// convert a string like "(23,45,68),(12,13,14)" to "select 23,45,68,2 union select 12,13,14,2"
$tollfree = $A2B->config["webui"]["report_pnl_toll_free"] ?? "";
if (preg_match_all("/\(([\d.]+, *[\d.]+, *[\d.]+)\)/", $tollfree, $matches)) {
    $dnids .= "union select " . implode(",2 union select ", $matches[1]) . ",2 ";
}
$payphones = $A2B->config["webui"]["report_pnl_pay_phones"] ?? "";
if (preg_match_all("/\(([\d.]+, *[\d.]+, *[\d.]+)\)/", $payphones, $matches)) {
    $dnids .= "union select " . implode(",1 union select ", $matches[1]) . ",1 ";
}

if (!isset($group_id) && $report_type == 1) {
    $QUERY = <<< SQL
SELECT
    id, name, call_count, time_minutes, toll_free_buy_cost, pay_phone_buy_cost, orig_only, credits, orig_total,
    toll_free_sell_cost, pay_phone_sell_cost, term_only, charges, term_total, first_use, discount, net_revenue, 
    net_revenue - orig_total AS profit, (net_revenue - orig_total) / net_revenue * 100 AS margin
FROM (
    SELECT
        main_id AS id, name, call_count, time_minutes, toll_free_buy_cost, pay_phone_buy_cost, orig_only,
        credits,orig_cost+credits AS orig_total, toll_free_sell_cost, pay_phone_sell_cost, term_only, charges,
        term_cost + charges AS term_total, first_use, discount, ((term_cost + charges)) * (1 - discount / 100) AS net_revenue
    FROM (
        SELECT
            t1.id_group as main_id, cg.name, call_count, time_minutes, toll_free_buy_cost, pay_phone_buy_cost,
            orig_cost - toll_free_buy_cost - pay_phone_buy_cost AS orig_only,orig_cost, COALESCE(credits, 0) AS credits,
            0 AS total, toll_free_sell_cost, pay_phone_sell_cost, term_cost, COALESCE(charges, 0) AS charges, discount,
            term_cost - toll_free_sell_cost - pay_phone_sell_cost AS term_only, first_use
        FROM (
            SELECT
                id_group, COUNT(*) AS call_count, SUM(sessiontime) DIV 60 AS time_minutes,
                SUM(CASE WHEN toll_free = 0 THEN 0 ELSE real_sessiontime / 60 * tf_cost END) AS toll_free_buy_cost,
                SUM(CASE WHEN pay_phone = 0 THEN 0 ELSE real_sessiontime / 60 * tf_cost END) AS pay_phone_buy_cost,
                SUM(buycost) AS orig_cost,
                SUM(CASE WHEN toll_free = 0 THEN 0 ELSE real_sessiontime / 60 * tf_sell_cost END) AS toll_free_sell_cost,
                SUM(CASE WHEN pay_phone = 0 THEN 0 ELSE real_sessiontime / 60 * tf_sell_cost END) AS pay_phone_sell_cost,
                SUM(sessionbill) AS term_cost,
                SUM(discount * sessionbill) / SUM(sessionbill) AS discount
            FROM (
                SELECT
                    cc.id_group, cdr.sessiontime, cdr.dnid, cdr.real_sessiontime, sessionbill, buycost,
                    cc.discount, COALESCE(tf.cost, 0) AS tf_cost, COALESCE(tf.sell_cost, 0) AS tf_sell_cost,
                    CASE WHEN tf.dnid_type IS NULL THEN 0 WHEN tf.dnid_type = 1 THEN 1 ELSE 0 END AS toll_free,
                    CASE WHEN tf.dnid_type IS NULL THEN 0 WHEN tf.dnid_type = 2 THEN 1 ELSE 0 END as pay_phone
                FROM cc_call cdr
                LEFT JOIN cc_card cc ON cdr.card_id = cc.id
                LEFT JOIN (
                    SELECT 'dnid' AS dnid, 0.1 AS sell_cost, 0.1 AS cost,0 AS dnid_type $dnids
                ) AS tf ON tf.dnid = SUBSTRING(cdr.dnid, 1, LENGTH(tf.dnid))
                WHERE sessiontime > 0 AND $condition
                ORDER BY cdr.starttime DESC
            ) AS a
            GROUP BY id_group
        ) as t1
        LEFT JOIN cc_card_group AS cg ON cg.id = id_group
        LEFT JOIN (
            SELECT cc.id_group, SUM(cr.credit) AS credits
            FROM cc_logrefill cr
            LEFT JOIN cc_card cc ON cc.id = cr.card_id
            WHERE refill_type = 1 AND $condition1
            GROUP BY id_group
        ) AS t2 ON t1.id_group = t2.id_group
        LEFT JOIN (
            SELECT cc.id_group, SUM(cr.credit) * -1 AS charges
            FROM cc_logrefill cr
            LEFT JOIN cc_card cc ON cc.id = cr.card_id
            WHERE refill_type = 2 AND $condition1
            GROUP BY id_group
        ) AS t3 ON t1.id_group = t3.id_group
        LEFT JOIN (
            SELECT id_group, COUNT(*) AS first_use
            FROM cc_card
            WHERE $condition2
            GROUP BY id_group
        ) AS t4 ON t1.id_group = t4.id_group
     ) AS result
) AS final
SQL;
} elseif (!isset($group_id) && $report_type == 2) {
    $QUERY = <<< SQL
SELECT
    id, name, call_count, time_minutes, toll_free_buy_cost, pay_phone_buy_cost, orig_only, credits, orig_total,
    toll_free_sell_cost, pay_phone_sell_cost, term_only, charges, term_total, first_use, discount, net_revenue, 
    net_revenue - orig_total AS profit, (net_revenue - orig_total) / net_revenue * 100 AS margin
FROM (
    SELECT
        main_id AS id, name, call_count, time_minutes, toll_free_buy_cost, pay_phone_buy_cost, orig_only,
        credits,orig_cost+credits AS orig_total, toll_free_sell_cost, pay_phone_sell_cost, term_only, charges,
        term_cost + charges AS term_total, first_use, discount, ((term_cost + charges)) * (1 - discount / 100) AS net_revenue
    FROM (
        SELECT
            t1.id_tariffgroup as main_id, cg.tariffgroupname as name, call_count, time_minutes, toll_free_buy_cost,
            orig_cost - toll_free_buy_cost - pay_phone_buy_cost AS orig_only,orig_cost, COALESCE(credits, 0) AS credits,
            0 AS total, toll_free_sell_cost, pay_phone_sell_cost, term_cost, COALESCE(charges, 0) AS charges, discount,
            term_cost - toll_free_sell_cost - pay_phone_sell_cost AS term_only, first_use, pay_phone_buy_cost
        FROM (
            SELECT
                id_tariffgroup, COUNT(*) AS call_count, SUM(sessiontime) DIV 60 AS time_minutes,
                SUM(CASE WHEN toll_free = 0 THEN 0 ELSE real_sessiontime / 60 * tf_cost END) AS toll_free_buy_cost,
                SUM(CASE WHEN pay_phone = 0 THEN 0 ELSE real_sessiontime / 60 * tf_cost END) AS pay_phone_buy_cost,
                SUM(buycost) AS orig_cost,
                SUM(CASE WHEN toll_free = 0 THEN 0 ELSE real_sessiontime / 60 * tf_sell_cost END) AS toll_free_sell_cost,
                SUM(CASE WHEN pay_phone = 0 THEN 0 ELSE real_sessiontime / 60 * tf_sell_cost END) AS pay_phone_sell_cost,
                SUM(sessionbill) AS term_cost,
                SUM(discount * sessionbill) / SUM(sessionbill) AS discount
            FROM (
                SELECT
                    cdr.id_tariffgroup, cdr.sessiontime, cdr.dnid, cdr.real_sessiontime, sessionbill, buycost,
                    cc.discount, COALESCE(tf.cost, 0) AS tf_cost, COALESCE(tf.sell_cost, 0) AS tf_sell_cost,
                    CASE WHEN tf.dnid_type IS NULL THEN 0 WHEN tf.dnid_type = 1 THEN 1 ELSE 0 END AS toll_free,
                    CASE WHEN tf.dnid_type IS NULL THEN 0 WHEN tf.dnid_type = 2 THEN 1 ELSE 0 END as pay_phone
                FROM cc_call cdr
                LEFT JOIN cc_card cc ON cdr.card_id = cc.id
                LEFT JOIN (
                    SELECT 'dnid' AS dnid, 0.1 AS sell_cost, 0.1 AS cost,0 AS dnid_type $dnids
                ) AS tf ON tf.dnid = SUBSTRING(cdr.dnid, 1, LENGTH(tf.dnid))
                WHERE sessiontime > 0 AND $condition
                ORDER BY cdr.starttime DESC
            ) AS a
            GROUP BY id_tariffgroup
        ) as t1
        LEFT JOIN cc_tariffgroup AS cg ON cg.id = id_tariffgroup
        LEFT JOIN (
            SELECT
                cc.tariff AS id_tariffgroup, SUM(CASE WHEN refill_type = 1 THEN cr.credit ELSE 0 END) AS credits,
                SUM(CASE WHEN refill_type = 2 THEN cr.credit ELSE 0 END) * -1 AS charges
            FROM cc_logrefill cr
            LEFT JOIN cc_card cc ON cc.id = cr.card_id
        ) AS t2 ON t1.id_tariffgroup = t2.id_tariffgroup
        LEFT JOIN (
            SELECT tariff AS id_tariffgroup, COUNT(*) AS first_use
            FROM cc_card WHERE $condition2
             GROUP BY tariff
        ) AS t4 ON t1.id_tariffgroup = t4.id_tariffgroup
    ) AS result
) AS final
SQL;
} elseif (isset($group_id)) {
    if ($report_type == 1) {
        $q_where="AND cc.id_group = " . (int)$group_id;
    } elseif ($report_type == 2) {
        $q_where="AND cc.tariff = " . (int)$group_id;
    } else {
        $q_where = "";
    }
    $QUERY = <<< SQL
SELECT
    id, name, call_count, time_minutes, toll_free_buy_cost, pay_phone_buy_cost, orig_only, credits, orig_total,
    toll_free_sell_cost, pay_phone_sell_cost, term_only, charges, term_total, first_use, discount, net_revenue, 
    net_revenue - orig_total AS profit, (net_revenue - orig_total) / net_revenue * 100 AS margin
FROM (
    SELECT
        main_id AS id, name, call_count, time_minutes, toll_free_buy_cost, pay_phone_buy_cost, orig_only,
        credits,orig_cost+credits AS orig_total, toll_free_sell_cost, pay_phone_sell_cost, term_only, charges,
        term_cost + charges AS term_total, first_use, discount, ((term_cost + charges)) * (1 - discount / 100) AS net_revenue
    FROM (
        SELECT
            t1.destination as main_id, cg.destination as name, call_count, time_minutes, toll_free_buy_cost, pay_phone_buy_cost,
            orig_cost - toll_free_buy_cost - pay_phone_buy_cost AS orig_only,orig_cost, COALESCE(credits, 0) AS credits,
            0 AS total, toll_free_sell_cost, pay_phone_sell_cost, term_cost, COALESCE(charges, 0) AS charges, discount,
            term_cost - toll_free_sell_cost - pay_phone_sell_cost AS term_only, first_use
        FROM (
            SELECT
                destination, COUNT(*) AS call_count, SUM(sessiontime) DIV 60 AS time_minutes,
                SUM(CASE WHEN toll_free = 0 THEN 0 ELSE real_sessiontime / 60 * tf_cost END) AS toll_free_buy_cost,
                SUM(CASE WHEN pay_phone = 0 THEN 0 ELSE real_sessiontime / 60 * tf_cost END) AS pay_phone_buy_cost,
                SUM(buycost) AS orig_cost,
                SUM(CASE WHEN toll_free = 0 THEN 0 ELSE real_sessiontime / 60 * tf_sell_cost END) AS toll_free_sell_cost,
                SUM(CASE WHEN pay_phone = 0 THEN 0 ELSE real_sessiontime / 60 * tf_sell_cost END) AS pay_phone_sell_cost,
                SUM(sessionbill) AS term_cost,
                SUM(discount * sessionbill) / SUM(sessionbill) AS discount
            FROM (
                SELECT
                    cdr.destination, cdr.sessiontime, cdr.dnid, cdr.real_sessiontime, sessionbill, buycost,
                    cc.discount, COALESCE(tf.cost, 0) AS tf_cost, COALESCE(tf.sell_cost, 0) AS tf_sell_cost,
                    CASE WHEN tf.dnid_type IS NULL THEN 0 WHEN tf.dnid_type = 1 THEN 1 ELSE 0 END AS toll_free,
                    CASE WHEN tf.dnid_type IS NULL THEN 0 WHEN tf.dnid_type = 2 THEN 1 ELSE 0 END as pay_phone
                FROM cc_call cdr
                LEFT JOIN cc_card cc ON cdr.card_id = cc.id
                LEFT JOIN (
                    SELECT 'dnid' AS dnid, 0.1 AS sell_cost, 0.1 AS cost,0 AS dnid_type $dnids
                ) AS tf ON tf.dnid = SUBSTRING(cdr.dnid, 1, LENGTH(tf.dnid))
                WHERE sessiontime > 0 AND $condition $q_where
                ORDER BY cdr.starttime DESC
            ) AS a
            GROUP BY destination
        ) as t1
        LEFT JOIN cc_prefix AS cg ON cg.prefix = t1.destination
        NATURAL JOIN (SELECT '0' AS credits, '0' AS charges, '0' AS first_use) AS t2
    ) AS result
) AS final
SQL;
} else {
    die;
}

$db = DbConnect();
$db->Execute("SET autocommit = 0");
$db->Execute("CREATE TEMPORARY TABLE pnl_report AS $QUERY");

function linktonext_1($value) {
    $handle = DbConnect();
    $inst_table = new Table("cc_card_group", "id");
    $id = $inst_table->getValue($handle, ["name" => $value]) ?? 0;
    return $id ? "<a href=\"?group_id=$id&report_type=1\">$value</a>" : $value;
}

function linktonext_2($value) {
    $handle = DbConnect();
    $inst_table = new Table("cc_tariffgroup", "id");
    $id = $inst_table->getValue ($handle, ["tariffgroupname" => $value]) ?? 0;
    return $id ? "<a href=\"?group_id=$id&report_type=2\">$value</a>" : $value;
}

$HD_Form = new FormHandler("pnl_report", "PNL Report");

$HD_Form->init();

if (!isset($group_id)) {
    if ($report_type == 1) {
        $HD_Form->AddListValue(gettext("Group"), "name", "linktonext_1");
    } elseif ($report_type == 2) {
        $HD_Form->AddListValue(gettext("Callplan"), "name", "linktonext_2");
    }
} else {
    $HD_Form->AddListValue(gettext("Country"), "name");
}
$HD_Form->AddListValue(gettext("CallCount"), "call_count");
$HD_Form->AddListValue(gettext("Minutes"), "time_minutes");
$HD_Form->AddListValue(gettext("Toll Free Cost"), "toll_free_buy_cost", "get_money");
$HD_Form->AddListValue(gettext("Pay Phone Cost"), "pay_phone_buy_cost", "get_money");
$HD_Form->AddListValue(gettext("Origination Cost"), "orig_only", "get_money");
$HD_Form->AddListValue(gettext("Credits"), "credits", "get_money");
$HD_Form->AddListValue(gettext("Total Cost"), "orig_total", "get_money");
$HD_Form->AddListValue(gettext("Toll Free Revenu"), "toll_free_sell_cost", "get_money");
$HD_Form->AddListValue(gettext("Pay Phone Revenu"), "pay_phone_sell_cost", "get_money");
$HD_Form->AddListValue(gettext("Termination Revenu"), "term_only", "get_money");
$HD_Form->AddListValue(gettext("Extra Charges"), "charges", "get_money");
$HD_Form->AddListValue(gettext("Total Revenue"), "term_total", "get_money");
$HD_Form->AddListValue(gettext("First Use"), "first_use");
$HD_Form->AddListValue(gettext("Avg Discount"), "discount", "get_percent");
$HD_Form->AddListValue(gettext("Net Revenue"), "net_revenue", "get_money");
$HD_Form->AddListValue(gettext("Margin"), "margin", "get_percent");
$HD_Form->AddListValue(gettext("Total Profit"), "profit", "get_money");
$HD_Form->list_query_order_columns = ["name"];

$HD_Form->AddSearchDateInput(_("Date"), "starttime");
$HD_Form->AddSearchRelativeDateInput(_("Date"), "starttime", true, false);
$HD_Form->AddSearchSelectInput(_("Report type"), "report_type", [1 => _("Card Group"), 2 => _("Call Plan")], false);
$HD_Form->search_session_key = 'pnl_selection';

$HD_Form->CV_NO_FIELDS  = gettext("NO INFO!");

// Code here for adding the fields in the Export File
$HD_Form->FG_EXPORT_FIELD_LIST = $HD_Form->list_query_columns;
$HD_Form->FG_EXPORT_CSV = true;
$HD_Form->FG_EXPORT_XML = true;
$HD_Form->export_session_key = "pr_export_pnl_report";
// todo: this may be broken for now; export should match $QUERY which should be in a temp table
$HD_Form->setup_export(["*"], "pnl_report");

$form_action ??= "list";
$list = $HD_Form->perform_action($form_action);

require_once __DIR__ . "/../templates/main.php";

$HD_Form->create_search_form();
$HD_Form->create_toppage ($form_action);
$HD_Form->create_form($form_action, $list) ;

// create the totals row
$totals_query = <<<SQL
SELECT
    SUM(call_count) AS call_count, SUM(time_minutes) AS time_minutes, SUM(toll_free_buy_cost) AS toll_free_buy_cost,
    SUM(pay_phone_buy_cost) AS pay_phone_buy_cost, SUM(orig_only) AS orig_only, SUM(credits) AS credits,
    SUM(orig_total) AS orig_total, SUM(toll_free_sell_cost) AS toll_free_sell_cost, SUM(pay_phone_sell_cost) AS pay_phone_sell_cost,
    SUM(term_only) AS term_only, SUM(charges) AS charges, SUM(term_total) AS term_total, SUM(first_use) AS first_use,
    (1 - SUM(net_revenue) / SUM(term_total)) * 100 AS average_discount, SUM(net_revenue) AS net_revenue,
    SUM(profit) / SUM(net_revenue) * 100 AS margin, SUM(profit) AS profit
FROM pnl_report
SQL;

$res = $HD_Form->DBHandle->Execute($totals_query);
$row = $res ? $res->FetchRow() : false;
?>

<?php if ($row): ?>
    <div class="row pb-3">
        <div class="col table-responsive">
            <table class="table table-bordered table-striped table-hover caption-top">
                <thead>
                <tr>
                    <th><?= _('Total Calls') ?></th>
                    <th><?= _('Total Min') ?></th>
                    <th><?= _('Toll Free Cost') ?></th>
                    <th><?= _('PayPhone Cost') ?></th>
                    <th><?= _('Origination Cost') ?></th>
                    <th><?= _('Credits') ?></th>
                    <th><?= _('Total Cost') ?></th>
                    <th><?= _('Toll Free Revenue') ?></th>
                    <th><?= _('Pay Phone Revenue') ?></th>
                    <th><?= _('Termination Revenue') ?></th>
                    <th><?= _('Extra Charges') ?></th>
                    <th><?= _('Total Revenue') ?></th>
                    <th><?= _('First Use') ?></th>
                    <th><?= _('Average Discount') ?></th>
                    <th><?= _('Net Revenue') ?></th>
                    <th><?= _('Margin') ?></th>
                    <th><?= _('Total Profit') ?></th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td><?= $row["call_count"] ?></td>
                    <td><?= get_minute($row["time_minutes"]) ?></td>
                    <td><?= get_money($row["toll_free_buy_cost"]) ?></td>
                    <td><?= get_money($row["pay_phone_buy_cost"]) ?></td>
                    <td><?= get_money($row["orig_only"]) ?></td>
                    <td><?= get_money($row["credits"]) ?></td>
                    <td><?= get_money($row["orig_total"]) ?></td>
                    <td><?= get_money($row["toll_free_sell_cost"]) ?></td>
                    <td><?= get_money($row["pay_phone_sell_cost"]) ?></td>
                    <td><?= get_money($row["term_only"]) ?></td>
                    <td><?= get_money($row["charges"]) ?></td>
                    <td><?= get_money($row["term_total"]) ?></td>
                    <td><?= get_money($row["first_use"]) ?></td>
                    <td><?= get_money($row["average_discount"]) ?></td>
                    <td><?= get_money($row["net_revenue"]) ?></td>
                    <td><?= get_money($row["margin"]) ?></td>
                    <td><?= get_money($row["profit"]) ?></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
<?php endif ?>

<?php
require_once __DIR__ . "/../templates/footer.php";
