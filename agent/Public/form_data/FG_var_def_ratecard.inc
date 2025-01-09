<?php

use A2billing\Agent;
use A2billing\Forms\FormHandler;
use A2billing\Table;

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of A2Billing (http://www.a2billing.net/)
 *
 * A2Billing, Commercial Open Source Telecom Billing platform,
 * powered by Star2billing S.L. <http://www.star2billing.com/>
 *
 * @copyright   Copyright (C) 2004-2012 - Star2billing S.L.
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

if (! has_rights (Agent::ACX_ACCESS)) {
    Header ("HTTP/1.0 401 Unauthorized");
    Header ("Location: PP_error.php?c=accessdenied");
    die();
}


getpost_ifset(array('id', 'idtariffplan', 'dialprefix', 'destination', 'buyrate', 'buyrateinitblock',
'buyrateincrement', 'rateinitial', 'initblock', 'billingblock', 'connectcharge', 'disconnectcharge','disconnectcharge_after',
'stepchargea', 'chargea', 'timechargea', 'billingblocka', 'stepchargeb', 'chargeb', 'timechargeb',
'billingblockb', 'stepchargec', 'chargec', 'timechargec', 'billingblockc', 'startdate', 'stopdate',
'starttime', 'endtime', 'id_trunk', 'musiconhold','tariffplan', 'tariffgroup','posted','id_outbound_cidgroup','rounding_calltime', 'rounding_threshold',
'additional_block_charge', 'additional_block_charge_time', 'additional_grace','minimal_cost', 'idtariffgroup'));

// put all the destination name to lowercase
if ($_POST["destination"]) {
	$_POST["destination"] = strtolower ($_POST["destination"]);
}
$HD_Form = new FormHandler("cc_callplan_lcr LEFT JOIN cc_agent_tariffgroup ON tariffgroup_id=cc_agent_tariffgroup.id_tariffgroup", "Rates");


$HD_Form->list_query_conditions["cc_agent_tariffgroup.id_agent"] = $_SESSION["agent_id"];
$HD_Form ->list_query_order_columns = ["dialprefix"];

$HD_Form -> search_session_key = 'entity_ratecard_selection';

$HD_Form -> list_query_group_columns = ["dialprefix"];

$musiconhold_list = getMusicOnHold_List($A2B);
$yesno = getYesNoList();

if ($popup_select) $HD_Form -> AddListValue(gettext("ID"), "id");
$HD_Form -> AddListValue(gettext("DESTINATION"), "destination");
$HD_Form -> AddListValue(abbr(_("PREFIX"), _("DIALING PREFIX")), "dialprefix");
$HD_Form -> AddListValue(abbr(_("SR"), _("SELLING RATE")), "rateinitial", "get_money_precise");
if (!$popup_select) $HD_Form -> AddListValue(gettext("START-DATE"), "startdate");
if (!$popup_select) $HD_Form -> AddListValue(gettext("STOP-DATE"), "stopdate");
if (!$popup_select) $HD_Form -> AddListValue(abbr(_("CC"), _("CONNECT CHARGE")), "connectcharge");

if (!$popup_select) {
	$HD_Form -> FieldViewElement ('destination, dialprefix, MIN(rateinitial) as rateinitial, startdate, stopdate, connectcharge');
} else {
	$HD_Form -> FieldViewElement ('id, destination, dialprefix, MIN(rateinitial) as rateinitial');
}

if ($popup_select) {
	$HD_Form -> FG_LIST_VIEW_PAGE_SIZE = 7;
    $HD_Form->AddListSelectActionButton("javascript:sendValue('|param|')");
} else {
    $export_cols = $A2B->config['webui']['rate_export_field_list'];
    if (str_contains($HD_Form->FG_QUERY_TABLE_NAME, "cc_callplan_lcr")) {
        $export_cols = str_replace("destination", "cc_prefix.destination", $export_cols);
    }
	$HD_Form -> FG_EXPORT_FIELD_LIST = explode(",", $export_cols);
	$HD_Form -> FG_EXPORT_CSV = false;
	$HD_Form -> FG_EXPORT_XML = false;
	$HD_Form -> export_session_key = "pr_export_entity_rates";
}

// Search form
$HD_Form -> search_form_enabled = true;

$HD_Form->AddSearchDateInput(_("START DATE"), "startdate");
$HD_Form -> AddSearchTextInput(gettext("PREFIX"), 'dialprefix');
$HD_Form -> AddSearchComparisonInput(gettext("RATE INITIAL"), 'rateinitial1', 'rateinitial2', 'rateinitial');

$HD_Form -> AddSearchSqlSelectInput(
    'SELECT CALLPLAN',
    "tariffgroup_id",
    new Table(
        "cc_tariffgroup",
        ["cc_tariffgroup.id", "cc_tariffgroup.tariffgroupname"],
        ["cc_agent_tariffgroup" => ["cc_tariffgroup.id", "cc_agent_tariffgroup.id_tariffgroup"]]
    ),
    "tariffgroupname",
    "ASC",
    ["cc_agent_tariffgroup.id_agent" => $_SESSION['agent_id']]
);


$HD_Form -> FG_INTRO_TEXT_ADITION = $HD_Form -> FG_INTRO_TEXT_EDITION = gettext("Rates must be entered in the currency base : ").strtoupper(BASE_CURRENCY);



$HD_Form -> FG_FILTER_ENABLE = true;
$HD_Form -> FG_FILTER_COLUMN = 'dialprefix';
$HD_Form -> FG_FILTER_LABEL = 'dialprefix';
