<?php

use A2billing\Admin;
use A2billing\Forms\FormHandler;

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

Admin::checkPageAccess(Admin::ACX_CALL_REPORT);

global $letter;

getpost_ifset (["download", "file"]);
/**
 * @var string $download
 * @var string $file
 */

$HD_Form = new FormHandler(
    "cc_call",
    gettext("CDR"),
    "cc_call.id",
    [
        "cc_ratecard" => ["LEFT OUTER", "cc_call.id_ratecard", "cc_ratecard.id"],
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

$HD_Form->AddListValue(_("DNID"), "dnid", "format_phone_number");
$HD_Form->AddListValue(_("Count"), "COUNT(cc_call.*)");
$HD_Form->AddListValue(_("Avg buy"), "AVG(buyrate)", "get_money_precise");
$HD_Form->AddListValue(_("Avg sell"), "AVG(rateinitial)", "get_money_precise");
$HD_Form->AddListValue(_("Duration"), "SUM(sessiontime)", "get_minute");
$HD_Form->AddListValue(_("Buy"), "SUM(buycost)", "get_money_precise");
$HD_Form->AddListValue(_("Sell"), "SUM(sessionbill)", "get_money_precise");

$HD_Form->FG_LIST_VIEW_PAGE_SIZE = 25;

$HD_Form->CV_TITLE_TEXT = _("DNID Report");

$HD_Form->list_query_order_columns = ["dnid"];
$HD_Form->list_query_group_columns = ["dnid"];

// EXPORT
$HD_Form->FG_EXPORT_CSV = true;
$HD_Form->FG_EXPORT_XML = true;
$HD_Form->export_session_key = "pr_export_dnid";
/************************/

$HD_Form->search_form_enabled = true;
$HD_Form->search_session_key = "dnid_selection";
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
$HD_Form->AddSearchTextInput(_("DNID"), "dnid");

$HD_Form->AddSearchSelectInput(_("Disposition"), "terminatecauseid", $dialstatus_list);
$HD_Form->AddSearchSelectInput(_("Call type"), "sipiax", $calltype_list);

$HD_Form->search_delete_enabled = false;

$form_action ??= "list";
$HD_Form->prepare_list_subselection('list');
if (empty($HD_Form->list_query_conditions)) {
    $date = (new DateTime("-1 day"))->format("Y-m-d H:i:s");
    $HD_Form->list_query_conditions["cc_call.starttime"] = [">=", $date];
}

$list = $HD_Form->perform_action($form_action);

require_once __DIR__ . "/templates/main.php";

$HD_Form->create_search_form();
$HD_Form->create_toppage($form_action);
$HD_Form->create_form("list", $list);

require_once __DIR__ . "/../../common/page_modules/call_graph.php";
require_once __DIR__ . "/templates/footer.php";
