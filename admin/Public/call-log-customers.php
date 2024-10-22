<?php

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

$menu_section = 5;
require_once "../../common/lib/admin.defines.php";
/**
 * @var Smarty $smarty
 */

Admin::checkPageAccess(Admin::ACX_CALL_REPORT);

global $letter;

getpost_ifset (['current_page', 'order', 'sens', 'download', 'file', 'nodisplay']);
/**
 * @var string $current_page
 * @var string $download
 * @var string $file
 * @var string $nodisplay
 */
$current_page = (int)($current_page ?? 0);
$nodisplay = (bool)($nodisplay ?? 0);

if (($download ?? "") === "file" && !empty($file)) {

    $value_de = base64_decode($file);
    if (str_contains($file, '/') || $value_de === false || str_contains($value_de, '..')) {
        exit;
    }

    $dl_full = MONITOR_PATH . "/" . $value_de;

    if (!is_readable($dl_full)) {
        echo _("ERROR: Cannot download file $dl_full, it does not exist.");
        exit ();
    }

    header("Content-Type: application/octet-stream");
    header("Content-Disposition: attachment; filename=$value_de");
    header("Content-Length: " . filesize($dl_full));
    header("Accept-Ranges: bytes");
    header("Pragma: no-cache");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-transfer-encoding: binary");

    readfile($dl_full);
    exit ();
}

$HD_Form = new FormHandler(
    "cc_call LEFT OUTER JOIN cc_trunk ON cc_call.id_trunk = cc_trunk.id_trunk LEFT OUTER JOIN cc_ratecard ON cc_call.id_ratecard = cc_ratecard.id LEFT OUTER JOIN cc_card ON cc_call.card_id = cc_card.id",
    gettext("CDR"),
    "cc_call.id"
);
$HD_Form->init();

$currencies_list = array_map(fn ($v) => array_reverse($v), getCurrenciesList());
$dialstatus_list = getDialStatusList();
$dialstatus_list_r = array_map(fn ($v) => array_reverse($v), $dialstatus_list);
$yesno = getYesNoList();
$calltype_list = [
    [0, _("STANDARD")],
    [1, _("SIP/IAX")],
    [2, _("DIDCALL")],
    [3, _("DID_VOIP")],
    [4, _("CALLBACK")],
    [5, _("PREDICT")],
    [6, _("AUTO DIALER")],
    [7, _("DID-ALEG")],
];

$HD_Form->no_debug();
$HD_Form->FG_QUERY_COLUMN_LIST = 'cc_call.starttime, cc_call.src, cc_call.dnid, cc_call.calledstation, cc_call.destination AS dest, cc_ratecard.buyrate, cc_ratecard.rateinitial, cc_call.sessiontime, cc_call.card_id, cc_trunk.trunkcode, cc_call.terminatecauseid, cc_call.sipiax, cc_call.buycost, cc_call.sessionbill, CASE WHEN cc_call.sessionbill != 0 THEN ((cc_call.sessionbill - cc_call.buycost) / cc_call.sessionbill) * 100 ELSE NULL END AS margin, CASE WHEN cc_call.buycost != 0 THEN ((cc_call.sessionbill - cc_call.buycost) / cc_call.buycost) * 100 ELSE NULL END AS markup, cc_call.id, cc_trunk.id_provider, cc_trunk.id_trunk AS trunk_id';

$DBHandle = DbConnect();

$HD_Form->AddViewElement(_("Date"), "cc_call.starttime", true, 19);
$HD_Form->AddViewElement(_("Caller ID"), "src", true, 0, "display_phone_number");
$HD_Form->AddViewElement(_("DNID"), "dnid", true, 0, "display_phone_number");
$HD_Form->AddViewElement(_("Phone Number"), "calledstation", true, 0, "display_phone_number");
$HD_Form->AddViewElement(_("Destination"), "cc_call.destination", true, 15, "", "lie", "cc_prefix", "destination,prefix", "prefix='%id'", "%1");
$HD_Form->AddViewElement(_("Buy Rate"), "buyrate", true, 30, "display_2bill");
$HD_Form->AddViewElement(_("Sell Rate"), "rateinitial", true, 30, "display_2bill");
$HD_Form->AddViewElement(_("Duration"), "sessiontime", true, 30, "display_minute");
$HD_Form->AddViewElement(_("Account"), "card_id", true, 0, "display_customer_id_link");
$HD_Form->AddViewElement(_("Trunk"), "trunkcode");
$HD_Form->AddViewElement(_("Disposition"), "terminatecauseid", true, "", null, "list", $dialstatus_list);
$HD_Form->AddViewElement(_("CallType"), "sipiax", true, 0, "", "list", $calltype_list);
$HD_Form->AddViewElement(_("Buy"), "buycost", true, 30, "display_2bill");
$HD_Form->AddViewElement(_("Sell"), "sessionbill", true, 30, "display_2bill");
$HD_Form->AddViewElement(_("Margin"), "CASE WHEN cc_call.sessionbill != 0 THEN ((cc_call.sessionbill - cc_call.buycost) / cc_call.sessionbill) * 100 ELSE NULL END AS margin", true, 30, "display_2dec_percentage");
$HD_Form->AddViewElement(_("Markup"), "CASE WHEN cc_call.buycost != 0 THEN ((cc_call.sessionbill - cc_call.buycost) / cc_call.buycost) * 100 ELSE NULL END AS markup", true, 30, "display_2dec_percentage");

$HD_Form->FG_ENABLE_DELETE_BUTTON = true;
$HD_Form->FG_DELETE_BUTTON_LINK = "A2B_entity_call.php?form_action=ask-delete&id=";

if (LINK_AUDIO_FILE) {
    // TODO: figure out how this works, move it into this file with custom button
    $HD_Form->AddViewElement("", "uniqueid", false, 30, "display_monitorfile_link");
    $HD_Form->FG_QUERY_COLUMN_LIST .= ', cc_call.uniqueid';
}

$HD_Form->FG_LIST_VIEW_PAGE_SIZE = 25;

$HD_Form->CV_TITLE_TEXT = _("Call Logs");

$HD_Form->FG_TABLE_DEFAULT_ORDER = "cc_call.starttime";
$HD_Form->FG_TABLE_DEFAULT_SENS = "DESC";

// EXPORT
$HD_Form->FG_EXPORT_CSV = true;
$HD_Form->FG_EXPORT_XML = true;
$HD_Form->export_session_key = "pr_export_entity_call";
/************************/

$HD_Form->search_form_enabled = true;
$HD_Form->search_session_key = 'call_log_selection';
$HD_Form->search_form_title = gettext('Define specific criteria to search for call records');
$HD_Form->search_date_enabled = true;
$HD_Form->search_date_text = _('DATE');
$HD_Form->search_date_column = "cc_call.starttime";

$HD_Form->AddSearchPopupInput("card_id", _("Enter the customer ID"), "A2B_entity_card.php");
$HD_Form->AddSearchPopupInput("username", _("Enter the customer number"), "A2B_entity_card.php", 2);
$HD_Form->AddSearchPopupInput("id_tariffgroup", _("Call Plan"), "A2B_entity_tariffgroup.php", 2);
$HD_Form->AddSearchPopupInput("id_provider", _("Provider"), "A2B_entity_provider.php", 2);
$HD_Form->AddSearchPopupInput("cc_call.id_trunk", _("Trunk"), "A2B_entity_trunk.php", 2);
$HD_Form->AddSearchPopupInput("id_ratecard", _("Rate"), "A2B_entity_def_ratecard.php", 2);

$HD_Form->AddSearchTextInput(_("Phone number"), "destination", "dsttype");
$HD_Form->AddSearchTextInput(_("Caller ID"), "src", "srctype");
$HD_Form->AddSearchTextInput(_("DNID"), "dnid", "dnidtype");

$HD_Form->AddSearchSelectInput(_("Disposition"), "terminatecauseid", $dialstatus_list_r);
$HD_Form->AddSearchSelectInput(_("Call type"), "sipiax", $calltype_list);
/** TODO: find some way to intercept display of records to apply these options
$HD_Form->FG_FILTER_SEARCH_FORM_SELECT_INPUTS[] = [_("Currency"), false, "choose_currency", $currencies_list];
$HD_Form->FG_FILTER_SEARCH_FORM_SELECT_INPUTS[] = [_("Time unit"), false, "choose_timeunit", [["min", _("Minutes")], ["sec", _("Seconds")]]];
 */
$HD_Form->search_delete_enabled = false;

$form_action = $form_action ?? "list";
$HD_Form->prepare_list_subselection('list');
if (empty($HD_Form->FG_QUERY_WHERE_CLAUSE)) {
    $date = (new DateTime("-1 day"))->format("Y-m-d H:i:s");
    $HD_Form->FG_QUERY_WHERE_CLAUSE = "cc_call.starttime >= '$date' AND terminatecauseid = 1";
    $HD_Form->list_query_conditions["cc_call.starttime"] = [">=", $date];
    $HD_Form->list_query_conditions["terminatecauseid"] = 1;
}

$list = $HD_Form->perform_action($form_action);

$smarty->display("main.tpl");
$HD_Form->create_search_form();

$HD_Form->create_toppage($form_action);
$HD_Form->create_form("list", $list);

$table = new Table(
    "cc_call",
    [
        "DATE(cc_call.starttime) AS day",
        "SUM(cc_call.sessiontime) AS calltime",
        "COUNT(*) AS nbcall",
        "SUM(cc_call.buycost + 0) AS buy",
        "SUM(cc_call.sessionbill + 0) AS sell",
        "SUM(CASE WHEN sessionbill != 0 THEN ((sessionbill - buycost) / sessionbill) * 100 ELSE 0 END) AS margin",
        "SUM(CASE WHEN buycost != 0 THEN ((sessionbill - buycost) / buycost) * 100 ELSE 0 END) AS markup",
        "SUM(CASE WHEN cc_call.sessiontime > 0 THEN 1 ELSE 0 END) AS success_calls",
    ]
);
$list_total_day = $table->getRows($DBHandle, $HD_Form->list_query_conditions, ["day"], "ASC", ["day"]);

if (count($list_total_day)):
    $mmax = max(array_column($list_total_day, "calltime"));
    $totalcall = array_sum(array_column($list_total_day, "nbcall"));
    $totalminutes = array_sum(array_column($list_total_day, "calltime"));
    $totalsell = array_sum(array_column($list_total_day, "sell"));
    $totalbuycost = array_sum(array_column($list_total_day, "buy"));
    $totalsuccess = array_sum(array_column($list_total_day, "success_calls"));
    $widthbar = 0;

    $total_tmc = ($resulttype ?? "min") === "min"
        ? sprintf("%02d:%02d", ($totalminutes / $totalcall) / 60, ($totalminutes / $totalcall) % 60)
        : intval($totalminutes / $totalcall);

    $totalminutes = sprintf("%02d:%02d", $totalminutes / 60, $totalminutes % 60);
?>

<table class="table table-striped caption-top">
    <caption><?= _("Traffic Summary") ?></caption>
    <thead>
        <tr>
            <th><?= _( "Date" ) ?></th>
            <th><abbr title="<?= _( "Call duration" ) ?>"><?= _( "Time" ) ?></abbr></th>
            <th style="width: 10vw"></th>
            <th><?= _( "Calls" ) ?></th>
            <th><abbr title="<?= _( "Average call length" ) ?>"><?= _( "Avg" ) ?></abbr></th>
            <th><abbr title="<?= _( "Answer sieze ratio" ) ?>"><?= _( "ASR" ) ?></abbr></th>
            <th><?= _( "Sell" ) ?></th>
            <th><?= _( "Buy" ) ?></th>
            <th><?= _( "Profit" ) ?></th>
            <th><?= _( "Margin" ) ?></th>
            <th><?= _( "Markup" ) ?></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($list_total_day as $data): ?>
        <tr>
            <td><?= $data["day"] ?></td>
            <td><?= get_minute($data["calltime"]) ?></td>
            <td aria-hidden="true">
                <div style="width: <?= $mmax ? ($data["calltime"] / $mmax) * 100 : 0 ?>%; background: darkred">&nbsp;</div>
            </td>
            <td><?= $data["nbcall"] ?></td>
            <td><?= get_minute(intval($data ["calltime"] / $data ["nbcall"])) ?></td>
            <td><?= get_2dec_percentage($data["success_calls"] * 100 / ($data["nbcall"]) ) ?></td>
            <td><?= get_2bill($data["sell"]) ?></td>
            <td><?= get_2bill($data["buy"] ) ?></td>
            <td><?= get_2bill($data["sell"] - $data["buy"]) ?></td>
            <td><?= get_2dec_percentage($data["margin"]) ?></td>
            <td><?= get_2dec_percentage($data["markup"]) ?></td>
        </tr>
    <?php endforeach ?>
    </tbody>
    <tfoot>
        <tr>
            <th scope="row"><?= _( "TOTAL" ) ?></th>
            <td colspan="2"><?= $totalminutes ?></td>
            <td><?= $totalcall ?></td>
            <td><?= $total_tmc ?></td>
            <td><?= get_2dec_percentage($totalsuccess * 100 / $totalcall) ?></td>
            <td><?= get_2bill($totalsell) ?></td>
            <td><?= get_2bill($totalbuycost) ?></td>
            <td><?= get_2bill($totalsell - $totalbuycost) ?></td>
            <td><?= $totalsell ? get_2dec_percentage((($totalsell - $totalbuycost) / $totalsell) * 100) : _("n/a") ?></td>
            <td><?= $totalbuycost ? get_2dec_percentage((($totalsell - $totalbuycost) / $totalbuycost) * 100) : _("n/a")?></td>
        </tr>
    </tfoot>
</table>
<?php endif ?>

<?php $smarty->display("footer.tpl") ?>

