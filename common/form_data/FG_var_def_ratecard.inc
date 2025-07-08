<?php

use A2billing\A2Billing;
use A2billing\Agent;
use A2billing\Forms\FormHandler;
use A2billing\Forms\Validator;
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

/**
 * @var A2Billing $A2B
 * @var string $form_action
 * @var string $popup_select
 * @var string $popup_formname
 * @var string $popup_fieldname
 */

getpost_ifset(["tariffgroup", "posted", "cancelsearch_callplanlcr", "deleteselected", "letter"]);
/**
 * @var numeric-string $tariffgroup
 * @var string $posted
 * @var string $cancelsearch_callplanlcr
 * @var string $deleteselected
 * @var string $letter
 */

$form_action ??= "list";

if (is_admin()) {
    $HD_Form = new FormHandler(
        "cc_ratecard",
        _("Rates"),
        "id",
        $form_action === "list" && !isset($deleteselected)
            ? ["cc_prefix" => ["prefix", "cc_ratecard.destination"]]
            : []
    );
    $use_lcr_callplan = false;
} elseif (is_agent()) {
    $HD_Form = new FormHandler(
        "cc_callplan_lcr",
        _("Rates"),
        "id",
        ["cc_agent_tariffgroup" => ["cc_callplan_lcr.tariffgroup_id", "cc_agent_tariffgroup.id_tariffgroup"]]
    );
    $HD_Form->list_query_conditions["cc_agent_tariffgroup.id_agent"] = Agent::id();
    $HD_Form->list_query_group_columns = ["dialprefix"];
    $use_lcr_callplan = true;
} else {
    $HD_Form = new FormHandler("cc_callplan_lcr", _("Rates"));
    $HD_Form->list_query_conditions["tariffgroup_id"] = $_SESSION["tariff"];
    $HD_Form ->list_query_group_columns = ["dialprefix"];
    if (isset($letter)) {
        $HD_Form->list_query_conditions["LOWER(SUBSTRING(`destination`, 1, 1))"] = strtolower($letter);
    }
    $use_lcr_callplan = true;
}

if (!empty($cancelsearch_callplanlcr) || (isset($posted) && empty($tariffgroup))) {
    $_SESSION["def_ratecard_tariffgroup"] = "";
}

if ((!empty($tariffgroup) || !empty($_SESSION['def_ratecard_tariffgroup'])) && $form_action === "list") {
    // "Export call plan with LCR" is active, whatever that means
    // It certainly isn't "exporting" anything
    $HD_Form->FG_QUERY_TABLE_NAME = "cc_callplan_lcr";
    $HD_Form->query_table_joins = [];
    $use_lcr_callplan = true;
    $HD_Form->list_query_group_columns = ["dialprefix"];
    $tariffgroup = (int)($tariffgroup ?? $_SESSION["def_ratecard_tariffgroup"]);
    $HD_Form->list_query_conditions["tariffgroup_id"] = $tariffgroup;
    $_SESSION['def_ratecard_tariffgroup'] = $tariffgroup;
}

if ($A2B->config["webui"]["hide_expired_rates"]) {
    $HD_Form->list_query_conditions[] = ["SUB", ["startdate" => [[null], ["<", "CURRENT_TIMESTAMP"]]], "OR"];
    $HD_Form->list_query_conditions[] = ["SUB", ["stopdate" => [[null], [">", "CURRENT_TIMESTAMP"]]], "OR"];
}

$HD_Form->list_query_order_columns = ["dialprefix"];
$HD_Form->search_session_key = "entity_ratecard_selection";

$HD_Form->add_help_text = $HD_Form->edit_help_text = create_help(
    _("Please fill in the fields below to set up the rate for each destination.")
);
$HD_Form->list_help_text = create_help(
    is_customer()
        ? _("View rates")
        : _("Please select a ratecard and click on search to browse the different rates/dialing prefix of the selected ratecard.")
);

$HD_Form->AddListValue(_("Destination"), $use_lcr_callplan ? "destination" : "cc_prefix.destination", null, [], false)
    ->AddListValue(abbr(_("Prefix"), _("Dialing Prefix")), "dialprefix");
if (is_admin()) {
    $HD_Form->AddListValue(abbr(_("Buy"), _("Buy Rate")), "buyrate", "get_money_precise");
}
$HD_Form->AddListValue(abbr(_("Sell"), _("Sell Rate")), $use_lcr_callplan ? "MIN(rateinitial)" : "rateinitial", "get_money_precise");

if (!$popup_select && is_admin()) {
    $HD_Form->AddListValue(_("Start"), "startdate")
        ->AddListValue(_("Stop"), "stopdate")
        ->AddListValue(abbr(_("Init."), _("Initial Billing Block")), "initblock")
        ->AddListValue(abbr(_("Conn."), _("Connect Charge")), "connectcharge", "get_money")
        ->AddListSqlMapping(_("Trunk"), "id_trunk", new Table("cc_trunk", ["id_trunk", "trunkcode"]))
        ->AddListSqlMapping(_("Rate Card"), "idtariffplan", new Table("cc_tariffplan", ["id", "tariffname"]));
    $HD_Form->AddListTopButton();
    $HD_Form->FG_ENABLE_EDIT_BUTTON = true;
    $HD_Form->FG_ENABLE_DELETE_BUTTON = true;
    $HD_Form->FG_ENABLE_ADD_BUTTON = true;
} elseif (!$popup_select && is_agent()) {
    $HD_Form->AddListValue(_("Start"), "startdate");
    $HD_Form->AddListValue(_("Stop"), "stopdate");
    $HD_Form->AddListValue(abbr(_("Conn."), _("Connect Charge")), "connectcharge", "get_money");
}

if ($popup_select) {
    $HD_Form->FG_LIST_VIEW_PAGE_SIZE = 7;
    $HD_Form->AddListSelectActionButton("javascript:sendRateToPackage('|param|')");
} else {
    $HD_Form->AddListFilter("dialprefix", _("Dial Prefix"));
    $HD_Form->search_form_enabled = true;
    $HD_Form->search_delete_enabled = is_admin();
}

// Search form
if (!is_customer()) {
    $HD_Form->AddSearchDateInput(_("Start Date"), "startdate");
}
if (is_admin()) {
    $HD_Form->AddSearchTextInput(_("Tag"), 'tag');
}
$HD_Form->AddSearchTextInput(_("Dialing Prefix"), 'dialprefix');
if (is_admin()) {
    $HD_Form->AddSearchComparisonInput(_("Buy Rate"), 'buyrate1', 'buyrate2', 'buyrate');
}
$HD_Form->AddSearchComparisonInput(_("Initial Rate"), 'rateinitial1', 'rateinitial2', 'rateinitial');
if (is_admin()) {
    $HD_Form->AddSearchSqlSelectInput(_("Trunk"), "id_trunk", new Table("cc_trunk", ["trunkcode", "id_trunk"]));
    $HD_Form->AddSearchSqlSelectInput(_("Rate Card"), "idtariffplan", new Table("cc_tariffplan", ["tariffname", "id"]));
} elseif (is_agent()) {
    $HD_Form->AddSearchSqlSelectInput(
        _("Call Plan"),
        "tariffgroup_id",
        new Table(
            "cc_tariffgroup",
            ["id", "tariffgroupname"],
            ["cc_agent_tariffgroup" => ["cc_tariffgroup.id", "cc_agent_tariffgroup.id_tariffgroup"]]
        ),
        ["id_agent" => Agent::id()]
    );
}
$HD_Form->AddEditSqlSelect(_("Rate Card"), "idtariffplan", new Table("cc_tariffplan", ["tariffname", "id"]));

$HD_Form->AddEditElement(
    _("Dialing Prefix"),
    "dialprefix",
    _("A) Add destination prefixes, ie '441' for UK Landlines.<br>B) Use 'defaultprefix' to setup a rate for all destinations where a specific rate is not provided.<br>C) if you ADD a rate, NOT an EDIT, you can define a range of prefixes. '32484-32487' adds all prefixes between 32484 and 32487. '32484,32386,32488' would add only the individual prefixes listed.<br>D) Asterisk extensions style + POSIX regex syntaxes are supported. '_447[7-9]XXXXXXXX' matches 12-digit UK mobiles. '_X{0,3}(112|999|911)' matches any 0-3 digits followed an emergency number. '_' can be used to add length, and raise the priority."),
    ["maxlength" => 80],
    [Validator::class, "asteriskExtension"],
    _("Insert the dialing prefix, such as for UK '44' or for US '1' ")
);

$HD_Form->AddEditPopup(
    _("Destination Prefix"),
    "destination",
    "A2B_entity_prefix.php",
    _("Select the prefix destination corresponding to your prefix rate.") . '<br>' . _("This is the label that appears against the Call Detail Record."),
    [],
    [Validator::class, "min1Char"]
);

$HD_Form->AddEditElement(
    "Buy Rate",
    "buyrate",
    _("Set the carrier cost, the price you pay for minutes."),
    ["maxlength" => 20],
    [Validator::class, "number"],
    _("Insert the buy rate"),
    _("Set the carrier cost for this destination.")
);


$HD_Form->AddEditElement(
    _("Buy Min Duration"),
    "buyrateinitblock",
    _("Set the minimum duration charged by the carrier. (i.e. 30 secs)"),
    ["maxlength" => 20],
    [Validator::class, "numeric"],
    _("Insert the buyrate init block"),
    "",
    "NO"
);


$HD_Form->AddEditElement(
    _("Buy Billing Block"),
    "buyrateincrement",
    _("Set the billing increment, in seconds (billing block), that the carrier applies. (ie 30 secs)"),
    ["maxlength" => 20],
    [Validator::class, "numeric"],
    _("Insert the billing block"),
    "",
    "NO"
);


$HD_Form->AddEditElement(
    _("Sell Rate"),
    "rateinitial",
    _("The retail rate; or the cost per minute to apply to the customer, e.g. 0.02"),
    ["maxlength" => 20],
    [Validator::class, "number"],
    _("Insert the initial rate"),
    _("Define the selling cost for customers.")
);


$HD_Form->AddEditElement(
    _("Sell Min Duration"),
    "initblock",
    _("Set the minimum duration to charge the customer (ie 60 seconds)"),
    ["maxlength" => 20],
    [Validator::class, "numeric"],
    _("Insert the init block"),
    "",
    "NO"
);


$HD_Form->AddEditElement(
    _("Sell Billing Block"),
    "billingblock",
    _("Set the billing increment in seconds to bill your customer."),
    ["maxlength" => 20],
    [Validator::class, "numeric"],
    _("Insert the billing block"),
    "",
    "NO"
);


$HD_Form->AddEditElement(
    _("Connect Charge"),
    "connectcharge",
    _("Apply a connection charge"),
    ["maxlength" => 20],
    [Validator::class, "number"],
    _("Insert the connect charge"),
    "",
    "NO"
);


$HD_Form->AddEditElement(
    _("Disconnect Charge"),
    "disconnectcharge",
    _("Apply a disconnection charge"),
    ["maxlength" => 20],
    [Validator::class, "number"],
    _("Insert the disconnect charge"),
    "",
    "NO"
);


$HD_Form->AddEditElement(
    _("Disconnect Charge Threshold"),
    "disconnectcharge_after",
    _("Apply the disconnection charge if the call duration is greater than this amount of seconds. If 0, it will always apply."),
    ["maxlength" => 20],
    [Validator::class, "number"],
    _("Insert the disconnect charge threshold"),
    "",
    "NO"
);


$HD_Form->AddEditElement(
    _("Minimum Cost"),
    "minimal_cost",
    _("Apply a minimum charge for the call."),
    ["maxlength" => 20],
    [Validator::class, "number"],
    _("Insert the Minimum call cost"),
    "",
    "NO"
);


if (ADVANCED_MODE) {

    $HD_Form->AddEditElement(
        _("Initial Charge A"),
        "stepchargea",
        _("When entering in the cycle 'A', define the amount to charge for the entrance."),
        ["maxlength" => 20],
        [Validator::class, "number"],
        _("Insert the stepcharge A"),
        _("Below we have the section to create progressive rates. Progressive rate aims to bill the customer at various rates according the duration of the call. For instance you can define that you can to bill the customer 0.33dollars for the first 2 minutes and then you want to bill him 0.45dollars for the rest of the call."),
        "NO"
    );


    $HD_Form->AddEditElement(
        _("Rate A"),
        "chargea",
        _("When entering in the cycle 'A', define the rate to apply."),
        ["maxlength" => 20],
        [Validator::class, "number"],
        _("Insert the charge A"),
        "",
        "NO"
    );


    $HD_Form->AddEditElement(
        _("Duration A"),
        "timechargea",
        _("Define the duration of the cycle 'A'."),
        ["maxlength" => 20],
        [Validator::class, "numeric"],
        _("Insert the time charge A"),
        "",
        "NO"
    );


    $HD_Form->AddEditElement(
        _("Billing Block A"),
        "billingblocka",
        _("Define the duration of each billing block to apply the rate 'CHARGE A'."),
        ["maxlength" => 20],
        [Validator::class, "numeric"],
        _("Insert the billing block A"),
        "",
        "NO"
    );


    $HD_Form->AddEditElement(
        _("Initial Charge B"),
        "stepchargeb",
        _("When entering in the cycle 'B', define the amount to charge for the entrance."),
        ["maxlength" => 20],
        [Validator::class, "number"],
        _("Insert the stepcharge B"),
        "",
        "NO"
    );


    $HD_Form->AddEditElement(
        _("Rate B"),
        "chargeb",
        _("When entering in the cycle 'B', define the rate to apply."),
        ["maxlength" => 20],
        [Validator::class, "number"],
        _("Insert the charge B"),
        "",
        "NO"
    );


    $HD_Form->AddEditElement(
        _("Duration B"),
        "timechargeb",
        _("Define the duration of the cycle 'B'."),
        ["maxlength" => 20],
        [Validator::class, "numeric"],
        _("Insert the time charge B"),
        "",
        "NO"
    );


    $HD_Form->AddEditElement(
        _("Billing Block B"),
        "billingblockb",
        _("Define the duration of each billing block to apply the rate 'CHARGE B'."),
        ["maxlength" => 20],
        [Validator::class, "numeric"],
        _("Insert the billing block B"),
        "",
        "NO"
    );


    $HD_Form->AddEditElement(
        _("Initial Charge C"),
        "stepchargec",
        _("When entering in the cycle 'C', define the amount to charge for the entrance."),
        ["maxlength" => 20],
        [Validator::class, "number"],
        "Insert the stepcharge C",
        "",
        "NO"
    );


    $HD_Form->AddEditElement(
        _("Rate C"),
        "chargec",
        _("When entering in the cycle 'C', define the rate to apply."),
        ["maxlength" => 20],
        [Validator::class, "number"],
        _("Insert the charge C"),
        "",
        "NO"
    );


    $HD_Form->AddEditElement(
        _("Duration C"),
        "timechargec",
        _("Define the duration of the cycle 'C'."),
        ["maxlength" => 20],
        [Validator::class, "numeric"],
        _("Insert the time charge C"),
        "",
        "NO"
    );


    $HD_Form->AddEditElement(
        _("Billing Block C"),
        "billingblockc",
        _("Define the duration of each billing block to apply the rate 'CHARGE C'."),
        ["maxlength" => 20],
        [Validator::class, "numeric"],
        _("Insert the billing block C"),
        "",
        "NO"
    );


    $HD_Form->AddEditElement(
        _("Announce Time Correction"),
        "announce_time_correction",
        _("Define the multiplier on announce time for clients."),
        ["maxlength" => 20],
        [Validator::class, "number"],
        _("insert corection for announce time"),
        "",
        "NO"
    );

}//ENDif (ADVANCED_MODE)

$date_attr = ["maxlength" => 40];
if ($form_action === "ask-add") {
    $date_attr["value"] = (new DateTime())->format("Y-m-d H:i:s");
}
$HD_Form->AddEditElement(
    _("Start Date"),
    "startdate",
    _("Format YYYY-MM-DD HH:MM:SS. For instance, '2004-12-31 00:00:00'"),
    $date_attr,
    [Validator::class, "dateTime"],
    _("Insert the starting date"),
    _("Define the period when this rate table is active.")
);


if ($form_action === "ask-add") {
    $date_attr["value"] = (new DateTime("+10 years"))->format("Y-m-d H:i:s");
}
$HD_Form->AddEditElement(
    _("Stop Date"),
    "stopdate",
    _("Leave empty to apply this rate indefinitely. Format YYYY-MM-DD HH:MM:SS. For instance, '2004-12-31 00:00:00"),
    $date_attr,
    [Validator::class, "dateTime"],
    _("Insert the stop date"),
    "",
    "NO-NULL"
);

$HD_Form->AddEditDayTime(
    _("Start Time"),
    "starttime",
    _("Set the time and day of the week that the rate is first valid"),
    0
);

$HD_Form->AddEditDayTime(
    _("End Time"),
    "endtime",
    _("Set the time and the day of the week that the rate is no longer valid"),
    10079
);

$HD_Form->AddEditElement(
    _("Duration Rounding"),
    "rounding_calltime",
    _("Set the rounding calltime. All the selling rules will apply over this new calltime."),
    ["maxlength" => 40],
    null,
    _("Insert the rounding calltime")
);


$HD_Form->AddEditElement(
    _("Rounding Threshold"),
    "rounding_threshold",
    _("Set the rounding threshold"),
    ["maxlength" => 40],
    null,
    _("Insert the rounding threshold")
);


$HD_Form->AddEditElement(
    _("Additional Block Charge"),
    "additional_block_charge",
    _("Set the initial block charge"),
    ["maxlength" => 40],
    null,
    _("Insert the additional block charge")
);


$HD_Form->AddEditElement(
    _("Additional Block Charge Time"),
    "additional_block_charge_time",
    _("Set the initial block charge time"),
    ["maxlength" => 40],
    null,
    _("Insert the additional block charge time")
);


$HD_Form->AddEditElement(
    _("Additional Grace Time"),
    "additional_grace",
    _("Set the additional grace time in sec"),
    ["maxlength" => 40],
    null,
    _("Insert the additional grace time in sec")
);

$HD_Form->AddEditSqlSelect(
    _("Trunk"),
    "id_trunk",
    new Table("cc_trunk", ["trunkcode", "id_trunk"]),
    [],
    "",
    [-1, _("NOT DEFINED")],
    _("Set the trunk to use for this destination, or NOT DEFINED to use the rate card trunk.")
);

$HD_Form->AddEditSqlSelect(
    _("CID Group"),
    "id_outbound_cidgroup",
    new Table("cc_outbound_cid_group", ["group_name", "id"]),
    [],
    "",
    [-1 => _("NOT DEFINED")],
    _("Set the Outgoing CID Group to use for this destination.")
);

$HD_Form->AddEditElement(
    _("Tag"),
    "tag",
    _("Set a tag on this rate"),
    ["maxlength" => 40],
    [Validator::class, "min2Chars"],
    "",
    "",
    "NO"
);


if (ADVANCED_MODE) {
    $musiconhold_list = getMusicOnHold_List($A2B);
    $HD_Form->AddEditSelect(_("On-hold Music"), "musiconhold", $musiconhold_list);
}

$HD_Form->FG_SPLITABLE_FIELDS[] = 'dialprefix';

$HD_Form->FG_INTRO_TEXT_ADITION = $HD_Form->FG_INTRO_TEXT_EDITION = sprintf(_("Rates must be entered in the base currency (%s)"), (BASE_CURRENCY));
