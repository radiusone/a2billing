<?php

use A2billing\Admin;
use A2billing\Customer;
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
 * @var Smarty $smarty
 */

Admin::checkPageAccess(Admin::ACX_CALL_REPORT);

global $letter;

getpost_ifset (["download", "file"]);
/**
 * @var string $download
 * @var string $file
 */

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
    "cc_call",
    gettext("CDR"),
    "cc_call.id",
    [
        "cc_trunk" => ["LEFT OUTER", "cc_call.id_trunk", "cc_trunk.id_trunk"],
        "cc_ratecard" => ["LEFT OUTER", "cc_call.id_ratecard", "cc_ratecard.id"],
        "cc_card" => ["LEFT OUTER", "cc_call.card_id", "cc_card.id"],
    ]
);
$HD_Form->init();

$dialstatus_list = getDialStatusList();
$calltype_list = [
    _("STANDARD"),
    _("SIP/IAX"),
    _("DIDCALL"),
    _("DID_VOIP"),
    _("CALLBACK"),
    _("PREDICT"),
    _("AUTO DIALER"),
    _("DID-ALEG"),
];

$HD_Form->list_query_columns = [
    'cc_call.starttime',
    'cc_call.src',
    'cc_call.dnid',
    'cc_call.calledstation',
    'cc_call.destination AS dest',
    'cc_ratecard.buyrate',
    'cc_ratecard.rateinitial',
    'cc_call.sessiontime',
    'cc_call.card_id',
    'cc_trunk.trunkcode',
    'cc_call.terminatecauseid',
    'cc_call.sipiax',
    'cc_call.buycost',
    'cc_call.sessionbill',
    'CASE WHEN cc_call.sessionbill != 0 THEN ((cc_call.sessionbill - cc_call.buycost) / cc_call.sessionbill) * 100 ELSE NULL END AS margin',
    'CASE WHEN cc_call.buycost != 0 THEN ((cc_call.sessionbill - cc_call.buycost) / cc_call.buycost) * 100 ELSE NULL END AS markup',
    'cc_call.id',
    'cc_trunk.id_provider',
    'cc_trunk.id_trunk AS trunk_id'
];

$DBHandle = DbConnect();

$HD_Form->AddListValue(_("Date"), "cc_call.starttime");
$HD_Form->AddListValue(_("Caller ID"), "src", "format_phone_number");
$HD_Form->AddListValue(_("DNID"), "dnid", "format_phone_number");
$HD_Form->AddListValue(_("Called number"), "calledstation", "format_phone_number");
$HD_Form->AddListSqlMapping(_("Destination"), "cc_call.destination", new Table("cc_prefix", ["prefix", "destination"]));
$HD_Form->AddListValue(_("Buy rate"), "buyrate", "get_money_precise");
$HD_Form->AddListValue(_("Sell rate"), "rateinitial", "get_money_precise");
$HD_Form->AddListValue(_("Duration"), "sessiontime", "get_minute");
$HD_Form->AddListValue(_("Account"), "card_id", [Customer::class, "getUsername"]);
$HD_Form->AddListValue(_("Trunk"), "trunkcode");
$HD_Form->AddListMapping(_("Disposition"), "terminatecauseid", $dialstatus_list);
$HD_Form->AddListMapping(_("Call type"), "sipiax", $calltype_list);
$HD_Form->AddListValue(_("Buy"), "buycost", "get_money_precise");
$HD_Form->AddListValue(_("Sell"), "sessionbill", "get_money_precise");
$HD_Form->AddListValue(_("Margin"), "CASE WHEN cc_call.sessionbill != 0 THEN ((cc_call.sessionbill - cc_call.buycost) / cc_call.sessionbill) * 100 ELSE NULL END AS margin", "get_percent");
$HD_Form->AddListValue(_("Markup"), "CASE WHEN cc_call.buycost != 0 THEN ((cc_call.sessionbill - cc_call.buycost) / cc_call.buycost) * 100 ELSE NULL END AS markup", "get_percent");

$HD_Form->FG_ENABLE_DELETE_BUTTON = true;
$HD_Form->FG_DELETE_BUTTON_LINK = "A2B_entity_call.php?form_action=ask-delete&id=";

if (LINK_AUDIO_FILE) {
    // TODO: figure out how this works, move it into this file with custom button
    $HD_Form->AddListValue(_("Audio"), "uniqueid", "get_monitorfile_link", [], false);
    $HD_Form->list_query_columns[] = 'cc_call.uniqueid';
}

$HD_Form->FG_LIST_VIEW_PAGE_SIZE = 25;

$HD_Form->CV_TITLE_TEXT = _("Call Logs");

$HD_Form->list_query_order_columns = ["cc_call.starttime"];
$HD_Form->list_query_order_direction = "DESC";

// EXPORT
$HD_Form->FG_EXPORT_CSV = true;
$HD_Form->FG_EXPORT_XML = true;
$HD_Form->export_session_key = "pr_export_entity_call";
/************************/

$HD_Form->search_form_enabled = true;
$HD_Form->search_session_key = 'call_log_selection';
$HD_Form->search_form_title = gettext('Define specific criteria to search for call records');

$HD_Form->AddSearchDateInput(_("Date"), "cc_call.starttime");
$HD_Form->AddSearchPopupInput(_("Enter the customer ID"), "card_id", "A2B_entity_card.php");
$HD_Form->AddSearchPopupInput(_("Enter the customer number"), "username", "A2B_entity_card.php", 2);
$HD_Form->AddSearchPopupInput(_("Call Plan"), "id_tariffgroup", "A2B_entity_tariffgroup.php", 2);
$HD_Form->AddSearchPopupInput(_("Provider"), "id_provider", "A2B_entity_provider.php", 2);
$HD_Form->AddSearchPopupInput(_("Trunk"), "cc_call.id_trunk", "A2B_entity_trunk.php", 2);
$HD_Form->AddSearchPopupInput(_("Rate"), "id_ratecard", "A2B_entity_def_ratecard.php", 2);

$HD_Form->AddSearchTextInput(_("Called Number"), "calledstation");
$HD_Form->AddSearchTextInput(_("Source Number"), "src");
$HD_Form->AddSearchTextInput(abbr(_("DNID"), _("???")), "dnid");

$HD_Form->AddSearchSelectInput(_("Disposition"), "terminatecauseid", $dialstatus_list);
$HD_Form->AddSearchSelectInput(_("Call type"), "sipiax", $calltype_list);
/** TODO: find some way to intercept display of records to apply these options
$HD_Form->FG_FILTER_SEARCH_FORM_SELECT_INPUTS[] = [_("Currency"), false, "choose_currency", $currencies_list];
$HD_Form->FG_FILTER_SEARCH_FORM_SELECT_INPUTS[] = [_("Time unit"), false, "choose_timeunit", [["min", _("Minutes")], ["sec", _("Seconds")]]];
 */
$HD_Form->search_delete_enabled = false;

$form_action ??= "list";
$HD_Form->prepare_list_subselection('list');
if (empty($HD_Form->list_query_conditions)) {
    $date = (new DateTime("-1 day"))->format("Y-m-d H:i:s");
    $HD_Form->list_query_conditions["cc_call.starttime"] = [">=", $date];
    $HD_Form->list_query_conditions["terminatecauseid"] = 1;
}

$list = $HD_Form->perform_action($form_action);

require_once __DIR__ . "/../templates/main.php";

$HD_Form->create_search_form();

$HD_Form->create_toppage($form_action);
$HD_Form->create_form("list", $list);

require_once __DIR__ . "/modules/call_graph.php";
require_once __DIR__ . "/../templates/footer.php";

