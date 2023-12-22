<?php

use A2billing\A2Billing;
use A2billing\Admin;
use A2billing\Forms\FormHandler;

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
Admin::checkPageAccess(Admin::ACX_ADMINISTRATOR);

getpost_ifset([
    'id',
    'tariffgroup',
    'posted',
    'cancelsearch_callplanlcr',
    'deleteselected',
]);
/**
 * @var string $id
 * @var string $tariffgroup
 * @var string $posted
 * @var string $cancelsearch_callplanlcr
 * @var string $deleteselected
 */

$form_action = $form_action ?? "list";

if ($form_action === "list" && $deleteselected !== 'true') {
    $HD_Form = new FormHandler("cc_ratecard LEFT JOIN cc_prefix ON prefix=cc_ratecard.destination", "Rates", 'cc_ratecard.id');
} else {
    $HD_Form = new FormHandler("cc_ratecard", "Rates");
}

if ($cancelsearch_callplanlcr) {
    $_SESSION['def_ratecard_tariffgroup'] = '';
}

$rateinitial_field = "rateinitial";
$use_lcr_callplan = false;

if (isset($posted) && !$popup_select) {

    if (!empty($tariffgroup)) {
        $HD_Form->FG_QUERY_TABLE_NAME = "cc_callplan_lcr";
        $use_lcr_callplan = true;
        $HD_Form->FG_QUERY_GROUPBY_COLUMNS = ["dialprefix"];

        $tariffgroup = explode('-:-', $tariffgroup);
        $FG_TOP_FILTER_NAME = _("EXPORTED LCR CALL PLAN") . ' : <b>' . str_replace("-:-", "", $tariffgroup[1]) . '</b>';
        $FG_TOP_FILTER_VALUE = $tariffgroup[0];

        $HD_Form->FG_QUERY_WHERE_CLAUSE = "tariffgroup_id= '$FG_TOP_FILTER_VALUE'";
        $HD_Form->list_query_conditions["tariffgroup_id"] = $FG_TOP_FILTER_VALUE;

        $rateinitial_field = "MIN(rateinitial) as rateinitial";

        $_SESSION['def_ratecard_tariffgroup'] = $HD_Form->FG_QUERY_WHERE_CLAUSE;
        $_SESSION['def_ratecard_tariffgroup2'] = $HD_Form->list_query_conditions;

    } else {
        $_SESSION['def_ratecard_tariffgroup'] = '';
    }
} elseif (!empty($_SESSION['def_ratecard_tariffgroup']) && (empty($form_action) || $form_action == "list")) {
    $HD_Form = new FormHandler("cc_callplan_lcr", "Rates");
    $use_lcr_callplan = true;
    $HD_Form->FG_QUERY_GROUPBY_COLUMNS = ["dialprefix"];

    $HD_Form->FG_QUERY_WHERE_CLAUSE = $_SESSION['def_ratecard_tariffgroup'];
    $HD_Form->list_query_conditions = $_SESSION["def_ratecard_tariffgroup2"];
    $rateinitial_field = "MIN(rateinitial) as rateinitial";
}

$HD_Form->no_debug();
$HD_Form->FG_TABLE_DEFAULT_ORDER = " dialprefix";
$HD_Form->FG_TABLE_DEFAULT_SENS = "ASC";

$HD_Form->search_session_key = 'entity_ratecard_selection';

$musiconhold_list = getMusicOnHold_List($A2B);
$yesno = getYesNoList();

//
if ($popup_select) {
    $HD_Form->AddViewElement(_("ID"), "id");
}
$HD_Form->AddViewElement(_("Destination"), "cc_ratecard.destination", false, 15);
$HD_Form->AddViewElement("<acronym title=\"Dialing Prefix\">" . _("Prefix") . "</acronym>", "dialprefix");
$HD_Form->AddViewElement("<acronym title=\"Buy Rate\">" . _("Buy") . "</acronym>", "buyrate");
$HD_Form->AddViewElement("<acronym title=\"Sell Rate\">" . _("Sell") . "</acronym>", "rateinitial");
if (!$popup_select) {
    $HD_Form->AddViewElement(_("Start"), "startdate", true, 19, "display_dateformat");
    $HD_Form->AddViewElement(_("Stop"), "stopdate", true, 19, "display_dateformat");
    $HD_Form->AddViewElement("<acronym title=\"Initial Billing Block\">" . _("Init.") . "</acronym>", "initblock");
    $HD_Form->AddViewElement("<acronym title=\"Connect Charge\">" . _("Conn.") . "</acronym>", "connectcharge");
    $HD_Form->AddViewElement(_("Trunk"), "id_trunk", true, 15, "", "lie", "cc_trunk", "trunkcode", "id_trunk='%id'", "%1");
    $HD_Form->AddViewElement(_("Rate Card"), "idtariffplan", true, 15, "", "lie", "cc_tariffplan", "tariffname", "id='%id'", "%1");
    if ($use_lcr_callplan) {
        $HD_Form->FieldViewElement('destination, dialprefix, buyrate, ' . $rateinitial_field . ', startdate, stopdate, initblock, connectcharge, id_trunk , idtariffplan ');
    } else {
        $HD_Form->FieldViewElement('cc_prefix.destination, cc_ratecard.dialprefix, cc_ratecard.buyrate, cc_ratecard.rateinitial, cc_ratecard.startdate, cc_ratecard.stopdate, cc_ratecard.initblock, cc_ratecard.connectcharge, cc_ratecard.id_trunk , cc_ratecard.idtariffplan ');
    }
    $HD_Form->FG_LIST_ADDING_BUTTON1 = true;
    $HD_Form->FG_LIST_ADDING_BUTTON_LINK1 = "A2B_entity_def_ratecard.php?form_action=ask-add";
    $HD_Form->FG_LIST_ADDING_BUTTON_MSG1 = _("Add Rate");
    $HD_Form->FG_LIST_ADDING_BUTTON_IMG1 = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAM+SURBVBgZBcHLT1xVAMDh333MkxmYAYZOwbGGPqRaHq0gpIBGba3GR2KibjQmsHDXsHDjP2DSmBgTU7swUUk0ajemK1HapjYIoYRqi4DoUAsNjwIyw9wZ7tzHOff4fZpSCgAAgJHJ4UHgPNADZAATKAG3VKAufTb49c8AAACaUgqAkclhExgF3nwtdyaSjmWw7BpsD4R0EGxxfXNMSCGvCiHf/vLlHyoAmlKKkcnhEHCju/HJ/hdzz2E5EVw8li2NsIqwawco4RHDZtOdYHZjdk744pnv3rpS0gGA0VMNx/oHmrspSA9f1xBCpyrC2IQpuBG2nRhrpRCPJp6mO/t4h+f63wMYxbMPBoCP3zn2qjEv99mkSjUQVJwEBTeE5UB+vUxpf59IehetLk9fYxvXF2dav7k1etfoHT756bnm3hOaEWNTF6CaOCgT3N4yqDo6i+sVgmiRyKG/cWvz7ARFzKLkRENOv72yVG8CPbnaFu7YG+xEdZ4wDhMgWN32cJwqdVmFVT/OcrAHriIlIuR3XM48dgrfFe0m0BA3a1i1N9h2bZLxVva8JMViQF3GoSltsyO7sNy7RFSZ8n+7FPbiJGJJfE+kTKWUpinwXAtFDjMkGZv20WIJNpcFuqqlOVMlWR7EWvdxmMX37oNSCCE0U4qgYHlWS4ORIhntZG3HxPFDhKMRok0x7v27izOTIhOeIROdJ+JZlJ0yY1O/IEVQMoUvfl8pPGg5Es9x7eEkqfgRwkYO37FRRopwIk2tO0FbdomnjvfxSP1RbixcYXp+AqNa8XTfExd/XLopDiUymPY6pd0p0mkXU7iENEVENzAr1+hq60Tqks6DZ5GaT1/7aXTPyepfvXJ53HP9n8YXb/JsSxd1Rg3pREBdWFIbdkiGXIqVLUJagtePnwfggxe+4HBTB0BIB/Bd/91f83fm/lz5i3NtPbSmTA7EFY1GmQbdplgusrAxxYWrQwBcGB/i3vYcgKMppQB46fPnk8IXl4Uvz77XP2QisygVR9M1Fv75ltXiFKc7BjiaPUn+4R9Mzf3G2v3SJ5pSCgAAgP6Pet+QQr4vZdAeyCANAJSMatnTveoBIAqUgUvTFzc+/B+ww5qo63KzbgAAAABJRU5ErkJggg==";
    $HD_Form -> FieldExportElement (explode(",", $A2B->config['webui']['rate_export_field_list']));
    $HD_Form -> FG_EXPORT_CSV = true;
    $HD_Form -> FG_EXPORT_XML = true;
    $HD_Form -> FG_EXPORT_SESSION_VAR = "pr_export_entity_rates";
    $HD_Form -> FG_ENABLE_EDIT_BUTTON = true;
    $HD_Form -> FG_ENABLE_DELETE_BUTTON = true;
    $HD_Form -> FG_ENABLE_ADD_BUTTON = true;
} else {
    $HD_Form->FG_LIST_VIEW_PAGE_SIZE = 7;
    $HD_Form->CV_FOLLOWPARAMETERS = "&popup_select=" . $popup_select . "&popup_formname=" . $popup_formname . "&popup_fieldname=" . $popup_fieldname;

    $HD_Form->FG_OTHER_BUTTON1 = true;
    $HD_Form->FG_OTHER_BUTTON1_ALT = '<font color="red">&lt;select&gt;</font>';
    $HD_Form->FG_OTHER_BUTTON1_IMG = '';

    if ($popup_select == 1) {
        $HD_Form->FG_OTHER_BUTTON1_LINK = "javascript:sendValue('|param|');";
    } elseif ($popup_select == 2) {
        $HD_Form->FG_OTHER_BUTTON1_LINK = "javascript:sendValue('|col0|');";
    }
    if ($use_lcr_callplan) {
        $HD_Form->FieldViewElement('id, destination, dialprefix, buyrate, ' . $rateinitial_field);
    } else {
        $HD_Form->FieldViewElement('id, cc_prefix.destination, cc_ratecard.dialprefix, cc_ratecard.buyrate, ' . $rateinitial_field);
    }
}

// Search form
$HD_Form->search_form_enabled = true;
$HD_Form->search_date_enabled = true;
$HD_Form->search_date_text = _("Start Date");
$HD_Form->search_date_column = 'startdate';


$HD_Form->AddSearchTextInput(_("Tag"), 'tag', 'tagtype');
$HD_Form->AddSearchTextInput(_("Dialing Prefix"), 'dialprefix', 'dialprefixtype');
$HD_Form->AddSearchComparisonInput(_("Buy Rate"), 'buyrate1', 'buyrate1type', 'buyrate2', 'buyrate2type', 'buyrate');
$HD_Form->AddSearchComparisonInput(_("Initial Rate"), 'rateinitial1', 'rateinitial1type', 'rateinitial2', 'rateinitial2type', 'rateinitial');

$HD_Form->AddSearchSqlSelectInput('Trunk', "cc_trunk", "id_trunk, trunkcode, providerip", "", "trunkcode", "ASC", "id_trunk");
$HD_Form->AddSearchSqlSelectInput('Rate Card', "cc_tariffplan", "id, tariffname", "", "tariffname", "ASC", "idtariffplan");

$HD_Form->AddEditSqlSelect(_("Rate Card"), "idtariffplan", "cc_tariffplan", "tariffname,id");

$HD_Form->AddEditElement(
    _("Dialing Prefix"),
    "dialprefix",
    _("A) Add destination prefixes, ie '441' for UK Landlines.<br>B) Use 'defaultprefix' to setup a rate for all destinations where a specific rate is not provided.<br>C) if you ADD a rate, NOT an EDIT, you can define a range of prefixes. '32484-32487' adds all prefixes between 32484 and 32487. '32484,32386,32488' would add only the individual prefixes listed.<br>D) Asterisk extensions style + POSIX regex syntaxes are supported. '_447[7-9]XXXXXXXX' matches 12-digit UK mobiles. '_X{0,3}(112|999|911)' matches any 0-3 digits followed an emergency number. '_' can be used to add length, and raise the priority."),
    "size=50 maxlength=80",
    9,
    _("Insert the dialing prefix, such as for UK '44' or for US '1' ")
);

$HD_Form->AddEditPopup(
    _("Destination Prefix"),
    "destination",
    "A2B_entity_prefix.php?popup_select=2&",
    _("Select the prefix destination corresponding to your prefix rate.") . '<br>' . _("This is the label that appears against the Call Detail Record."),
    "",
    9,
    ""
);

$HD_Form->AddEditElement("Buy Rate",
    "buyrate",
    _("Set the carrier cost, the price you pay for minutes."),
    "size=30 maxlength=20",
    12,
    _("Insert the buy rate"),
    _("Set the carrier cost for this destination.")
);


$HD_Form->AddEditElement(
    _("Buy Min Duration"),
    "buyrateinitblock",
    _("Set the minimum duration charged by the carrier. (i.e. 30 secs)"),
    "size=30 maxlength=20",
    4,
    _("Insert the buyrate init block"),
    "",
    "NO"
);


$HD_Form->AddEditElement(
    _("Buy Billing Block"),
    "buyrateincrement",
    _("Set the billing increment, in seconds (billing block), that the carrier applies. (ie 30 secs)"),
    "size=30 maxlength=20",
    4,
    _("Insert the billing block"),
    "",
    "NO"
);


$HD_Form->AddEditElement(
    _("Sell Rate"),
    "rateinitial",
    _("The retail rate; or the cost per minute to apply to the customer, e.g. 0.02"),
    "size=30 maxlength=20",
    12,
    _("Insert the initial rate"),
    _("Define the selling cost for customers.")
);


$HD_Form->AddEditElement(
    _("Sell Min Duration"),
    "initblock",
    _("Set the minimum duration to charge the customer (ie 60 seconds)"),
    "size=30 maxlength=20",
    4,
    _("Insert the init block"),
    "",
    "NO"
);


$HD_Form->AddEditElement(
    _("Sell Billing Block"),
    "billingblock",
    _("Set the billing increment in seconds to bill your customer."),
    "size=30 maxlength=20",
    4,
    _("Insert the billing block"),
    "",
    "NO"
);


$HD_Form->AddEditElement(
    _("Connect Charge"),
    "connectcharge",
    _("Apply a connection charge"),
    "size=30 maxlength=20",
    12,
    _("Insert the connect charge"),
    "",
    "NO"
);


$HD_Form->AddEditElement(
    _("Disconnect Charge"),
    "disconnectcharge",
    _("Apply a disconnection charge"),
    "size=30 maxlength=20",
    12,
    _("Insert the disconnect charge"),
    "",
    "NO"
);


$HD_Form->AddEditElement(
    _("Disconnect Charge Threshold"),
    "disconnectcharge_after",
    _("Apply the disconnection charge if the call duration is greater than this amount of seconds. If 0, it will always apply."),
    "size=30 maxlength=20",
    12,
    _("Insert the disconnect charge threshold"),
    "",
    "NO"
);


$HD_Form->AddEditElement(
    _("Minimum Cost"),
    "minimal_cost",
    _("Apply a minimum charge for the call."),
    "size=30 maxlength=20",
    12,
    _("Insert the Minimum call cost"),
    "",
    "NO"
);


if (ADVANCED_MODE) {

    $HD_Form->AddEditElement(
        _("Initial Charge A"),
        "stepchargea",
        _("When entering in the cycle 'A', define the amount to charge for the entrance."),
        "size=30 maxlength=20",
        12,
        _("Insert the stepcharge A"),
        _("Below we have the section to create progressive rates. Progressive rate aims to bill the customer at various rates according the duration of the call. For instance you can define that you can to bill the customer 0.33dollars for the first 2 minutes and then you want to bill him 0.45dollars for the rest of the call."),
        "NO"
    );


    $HD_Form->AddEditElement(
        _("Rate A"),
        "chargea",
        _("When entering in the cycle 'A', define the rate to apply."),
        "size=30 maxlength=20",
        12,
        _("Insert the charge A"),
        "",
        "NO"
    );


    $HD_Form->AddEditElement(
        _("Duration A"),
        "timechargea",
        _("Define the duration of the cycle 'A'."),
        "size=30 maxlength=20",
        4,
        _("Insert the time charge A"),
        "",
        "NO"
    );


    $HD_Form->AddEditElement(
        _("Billing Block A"),
        "billingblocka",
        _("Define the duration of each billing block to apply the rate 'CHARGE A'."),
        "size=30 maxlength=20",
        4,
        _("Insert the billing block A"),
        "",
        "NO"
    );


    $HD_Form->AddEditElement(
        _("Initial Charge B"),
        "stepchargeb",
        _("When entering in the cycle 'B', define the amount to charge for the entrance."),
        "size=30 maxlength=20",
        12,
        _("Insert the stepcharge B"),
        "",
        "NO"
    );


    $HD_Form->AddEditElement(
        _("Rate B"),
        "chargeb",
        _("When entering in the cycle 'B', define the rate to apply."),
        "size=30 maxlength=20",
        12,
        _("Insert the charge B"),
        "",
        "NO"
    );


    $HD_Form->AddEditElement(
        _("Duration B"),
        "timechargeb",
        _("Define the duration of the cycle 'B'."),
        "size=30 maxlength=20",
        4,
        _("Insert the time charge B"),
        "",
        "NO"
    );


    $HD_Form->AddEditElement(
        _("Billing Block B"),
        "billingblockb",
        _("Define the duration of each billing block to apply the rate 'CHARGE B'."),
        "size=30 maxlength=20",
        4,
        _("Insert the billing block B"),
        "",
        "NO"
    );


    $HD_Form->AddEditElement(
        _("Initial Charge C"),
        "stepchargec",
        _("When entering in the cycle 'C', define the amount to charge for the entrance."),
        "size=30 maxlength=20",
        12,
        "Insert the stepcharge C",
        "",
        "NO"
    );


    $HD_Form->AddEditElement(
        _("Rate C"),
        "chargec",
        _("When entering in the cycle 'C', define the rate to apply."),
        "size=30 maxlength=20",
        12,
        _("Insert the charge C"),
        "",
        "NO"
    );


    $HD_Form->AddEditElement(
        _("Duration C"),
        "timechargec",
        _("Define the duration of the cycle 'C'."),
        "size=30 maxlength=20",
        4,
        _("Insert the time charge C"),
        "",
        "NO"
    );


    $HD_Form->AddEditElement(
        _("Billing Block C"),
        "billingblockc",
        _("Define the duration of each billing block to apply the rate 'CHARGE C'."),
        "size=30 maxlength=20",
        4,
        _("Insert the billing block C"),
        "",
        "NO"
    );


    $HD_Form->AddEditElement(
        _("Announce Time Correction"),
        "announce_time_correction",
        _("Define the multiplier on announce time for clients."),
        "size=30 maxlength=20",
        12,
        _("insert corection for announce time"),
        "",
        "NO"
    );

}//ENDif (ADVANCED_MODE)

$comp_date = $comp_date_plus = "";
if ($form_action == "ask-add") {
    $comp_date = "value='" . (new DateTime())->format("Y-m-d H:i:s") . "'";
    $comp_date_plus = "value='" . (new DateTime("now +10 years"))->format("Y-m-d H:i:s") . "'";
}

$HD_Form->AddEditElement(
    _("Start Date"),
    "startdate",
    _("Format YYYY-MM-DD HH:MM:SS. For instance, '2004-12-31 00:00:00'"),
    "size=40 maxlength=40  $comp_date",
    10,
    _("Insert the starting date"),
    _("Define the period when this rate table is active."),
    "",
    "res_display_dateformat"
);


$HD_Form->AddEditElement(
    _("Stop Date"),
    "stopdate",
    _("Leave empty to apply this rate indefinitely. Format YYYY-MM-DD HH:MM:SS. For instance, '2004-12-31 00:00:00"),
    "size=40 maxlength=40  $comp_date_plus",
    10,
    _("Insert the stop date"),
    "",
    "NO-NULL",
    "res_display_dateformat"
);

$comp_time = $comp_time_plus = "";
if ($form_action === "ask-add") {
    $comp_time = " value='0' ";
    $comp_time_plus = " value='10079' ";
}

$HD_Form->AddEditPopup(
    _("Start Time"),
    "starttime",
    "",
    _("Set the time and day of the week at the rate is first valid (ie Monday 00:00 is 0)"),
    $comp_time,
    4,
    _("Insert the start time"),
    true
);

$HD_Form->AddEditPopup(
    _("End Time"),
    "endtime",
    "",
    _("Set the time and the day of the week that the rate is no longer valid. (ie Sunday 23:59 is 10079)"),
    $comp_time_plus,
    4,
    _("Insert the end time"),
    true
);

$HD_Form->AddEditElement(
    _("Duration Rounding"),
    "rounding_calltime",
    _("Set the rounding calltime. All the selling rules will apply over this new calltime."),
    "size=40 maxlength=40",
    null,
    _("Insert the rounding calltime")
);


$HD_Form->AddEditElement(
    _("Rounding Threshold"),
    "rounding_threshold",
    _("Set the rounding threshold"),
    "size=40 maxlength=40",
    null,
    _("Insert the rounding threshold")
);


$HD_Form->AddEditElement(
    _("Additional Block Charge"),
    "additional_block_charge",
    _("Set the initial block charge"),
    "size=40 maxlength=40",
    null,
    _("Insert the additional block charge")
);


$HD_Form->AddEditElement(
    _("Additional Block Charge Time"),
    "additional_block_charge_time",
    _("Set the initial block charge time"),
    "size=40 maxlength=40",
    null,
    _("Insert the additional block charge time")
);


$HD_Form->AddEditElement(
    _("Additional Grace Time"),
    "additional_grace",
    _("Set the additional grace time in sec"),
    "size=40 maxlength=40",
    null,
    _("Insert the additional grace time in sec")
);

$HD_Form->AddEditSqlSelect(
    _("Trunk"),
    "id_trunk",
    "cc_trunk",
    "trunkcode,id_trunk",
    "",
    "",
    '<option value="-1" selected="selected">' . _("NOT DEFINED") . '</option>',
    "%1", _("Set the trunk to use for this destination, or NOT DEFINED to use the rate card trunk.")
);

$HD_Form->AddEditSqlSelect(
    _("CID Group"),
    "id_outbound_cidgroup",
    "cc_outbound_cid_group",
    "group_name,id",
    "",
    "",
    '<option value="-1" selected="selected">' . _("NOT DEFINED") . '</option>',
    "%1", _("Set the Outgoing CID Group to use for this destination, or NOT DEFINED to use the rate card trunk.")
);

$HD_Form->AddEditElement(
    _("Tag"),
    "tag",
    _("Set a tag on this rate"),
    "size=30 maxlength=40",
    11,
    "",
    "",
    "NO"
);


if (ADVANCED_MODE) {
    $HD_Form->AddEditSelect(_("On-hold Music"), "musiconhold", $musiconhold_list);
}

$HD_Form->FG_SPLITABLE_FIELDS[] = 'dialprefix';

$HD_Form->FG_INTRO_TEXT_ADITION = $HD_Form->FG_INTRO_TEXT_EDITION = sprintf(_("Rates must be entered in the base currency (%s)"), (BASE_CURRENCY));

$HD_Form->FG_FILTER_ENABLE = true;
$HD_Form->FG_FILTER_COLUMN = 'dialprefix';
$HD_Form->FG_FILTER_LABEL = _("Dial Prefix");

$HD_Form->FG_LOCATION_AFTER_ADD = "?id=";
$HD_Form->FG_LOCATION_AFTER_EDIT = "?id=";
$HD_Form->FG_LOCATION_AFTER_DELETE = "?id=";


