<?php

use A2billing\Customer;
use A2billing\Forms\FormBO;
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
 * @var string $form_action
 * @var string $popup_select
 * @var string $popup_fieldname
 * @var string $popup_formname
 */

getpost_ifset(['id', 'did', 'fixrate']);
/**
 * @var string $id
 * @var string $did
 * @var string $fixrate
 */

$HD_Form = new FormHandler("cc_did", "DID");

$HD_Form->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form->FG_LIST_ADDING_BUTTON_LINK1 = "A2B_entity_did.php?form_action=ask-add";
$HD_Form->FG_LIST_ADDING_BUTTON_ALT1 = $HD_Form->FG_LIST_ADDING_BUTTON_MSG1 = _("Add DID");
$HD_Form->FG_LIST_ADDING_BUTTON_IMG1 = get_image_path("telephone_add.png") ;

$HD_Form->list_query_order_columns = ["did"];
$HD_Form->list_query_order_direction = "DESC";
$HD_Form->FG_ADDITIONAL_FUNCTION_AFTER_ADD = [FormBO::class, "add_did_use"];
$HD_Form->FG_ADDITIONAL_FUNCTION_AFTER_DELETE = [FormBO::class, "did_use_delete"];
$HD_Form->FG_ADDITIONAL_FUNCTION_BEFORE_DELETE = [FormBO::class, "is_did_in_use"];

$HD_Form->list_help_text = create_help(_("DID number list with destinations."));
$HD_Form->add_help_text = $HD_Form->edit_help_text = create_help(_("DID can be assigned to a customer to re-route calls to a SIP/IAX client or a PSTN number. The Priority sets the order in which the calls are to be routed to allow for failover or follow-me."));

$yesno = getYesNoList();

$HD_Form->AddListValue(_("DID"), "did", "format_phone_number");
$HD_Form->AddListValue(_("Customer"), "iduser", [Customer::class, "getName"]);
$HD_Form->AddListMapping(_("Billing"), "billingtype", getBillingTypeShortList());
$HD_Form->AddListValue(_("Start date"), "startingdate");
$HD_Form->AddListSqlMapping(_("DID group"), "id_cc_didgroup", new Table("cc_didgroup", ["id", "didgroupname"]));
$HD_Form->AddListSqlMapping(_("Country"), "id_cc_country", new Table("cc_country", ["id", "countryname"]));
$HD_Form->AddListMapping(_("Active"), "activated", $yesno);
$HD_Form->AddListValue(abbr(_("Min"), _("Minutes Used")), "secondusedreal", "get_minute");
$HD_Form->AddListMapping(abbr(_("Res"), _("Reserved")), "reserved", $yesno);
$HD_Form->AddListValue(abbr(_("MR"), _("Monthly Rate")), "fixrate");
$HD_Form->AddListValue(abbr(_("SR"), _("Sell Rate")), "selling_rate");

$HD_Form->FG_ENABLE_ADD_BUTTON = true;
$HD_Form->FG_ENABLE_EDIT_BUTTON = true;
$HD_Form->FG_ENABLE_DELETE_BUTTON = true;
$HD_Form->FG_SPLITABLE_FIELDS = ["did"];

$did_attr = ["maxlength" => 60];
if ($form_action === "purchase") {
    $did_attr["value"] = $did;
}
$HD_Form->AddEditElement(
    _("DID"),
    "did",
    $form_action === "ask-add"
        ? _("Define the DIDs. You can define a range of DID. <br>80412340210-80412340218 would add all DID's between the range, whereas DIDs separated by a comma e.g. 80412340210,80412340212,80412340214 would only add the individual DID listed.")
        : _("Define the DIDs"),
    $did_attr,
    [Validator::class, "min1Char"],
    _("Insert the DID")
);


$HD_Form->AddEditSelect(_("BILLING"), "billingtype", getBillingTypeList());

$start_attr = ["maxlength" => 40];
if ($form_action === "ask-add") {
    $start_attr["value"] = (new DateTime())->format("Y-m-d H:i:s");
}
$HD_Form->AddEditElement(
    _("START DATE"),
    "startingdate",
    _("Format YYYY-MM-DD HH:MM:SS. For instance, '2004-12-31 00:00:00'"),
    $start_attr,
    [Validator::class, "dateTime"],
    _("Insert the starting date")
);

$exp_attr = ["maxlength" => 40];
if ($form_action === "ask-add") {
    $exp_attr["value"] = (new DateTime("+10 years"))->format("Y-m-d H:i:s");
}
$HD_Form->AddEditElement(
    _("EXPIRY DATE"),
    "expirationdate",
    _("Format YYYY-MM-DD HH:MM:SS. For instance, '2004-12-31 00:00:00'"),
    $exp_attr,
    [Validator::class, "dateTime"],
    _("Insert the expiration date")
);

$HD_Form->AddEditSqlSelect(
    _("DIDGROUP"),
    "id_cc_didgroup",
    new Table("cc_didgroup", ["id", "didgroupname"])
);

$HD_Form->AddEditSqlSelect(
    _("COUNTRY"),
    "id_cc_country",
    new Table("cc_country", ["id", "countryname"])
);

$HD_Form->AddEditRadio(
    gettext("ACTIVATED"),
    "activated",
    getYesNoList(),
    "1",
    "",
    [],
    gettext("Choose if you want to activate this card")
);

$rate_attr = ["maxlength" => 20];
if ($form_action === "purchase") {
    $rate_attr["value"] = $fixrate;
} elseif ($form_action === "ask-add") {
    $rate_attr["value"] = 0;
}
$HD_Form->AddEditElement(
    _("MONTHLY RATE"),
    "fixrate",
    _("currency : ") . strtoupper(BASE_CURRENCY),
    $rate_attr,
    [Validator::class, "number"],
    _("Insert the monthly rate"),
    _("Define the renting cost for the customers.")
);


$HD_Form->AddEditElement(
    _("CONNECT CHARGE"),
    "connection_charge",
    _("Apply a connection charge to connect DID together"),
    $rate_attr,
    [Validator::class, "number"],
    _("Insert the connect charge"),
    _("DID On Net Charges"),
    "NO"
);
   // On Net Charges is where one customer calls another customer without any external carrier being involved in the call

$HD_Form->AddEditElement(
    _("SELLING RATE"),
    "selling_rate",
    _("The retail rate; or the cost per minute to apply to the customer to connect DID together, e.g. 0.02"),
    $rate_attr,
    [Validator::class, "number"],
    _("Insert the rate")
);

$HD_Form->AddEditTextarea(
    _("TIME INTERVAL"),
    "aleg_timeinterval",
    _("The format is \"start_time;day_of_week;day_of_month;month;time_interval_type\" e.g. 7:00-19:00|mon-fri|*|*|p for peak hours or *|*|24|dec|o for off peak during Xmas day and so on. The last character indicates time interval type where \"p\" means PEAK and \"o\" OFF-PEAK. More definition one per line are supported. If there are PEAK and OFF-PEAK time interval definitions overlaps, than the OFF-PEAK is used."),
    ["rows" => 4],
    _("Insert time definition for peak and off peak time intervals."),
    "",
    null,
    _("Peak / Off Peak Time definition"),
);

$HD_Form->AddEditElement(
    _("CARRIER CONNECTION CHARGE"),
    "aleg_carrier_connect_charge",
    _("The connection charge can be negative or positive"),
    $rate_attr,
    [Validator::class, "number"],
    _("Insert a rate"),
    _("DID Ingress Charges Peak")
);


$HD_Form->AddEditElement(
    _("CARRIER COST PER MINUTE"),
    "aleg_carrier_cost_min",
    _("The cost can be negative for Non geographic calls where there is an out-payment"),
    $rate_attr,
    [Validator::class, "number"],
    _("Insert the connect cost"),
    "",
    "NO"
);


$HD_Form->AddEditElement(
    _("CARRIER MIN DURATION"),
    "aleg_carrier_initblock",
    _("Set the minimum duration charged by the carrier. (i.e. 30 secs)"),
    ["maxlength" => 20],
    [Validator::class, "numeric"],
    _("Insert the buyrate init block"),
    "",
    "NO"
);


$HD_Form->AddEditElement(
    _("CARRIER BILLING BLOCK"),
    "aleg_carrier_increment",
    _("Set the billing increment, in seconds (billing block), that the carrier applies. (ie 30 secs)"),
    ["maxlength" => 20],
    [Validator::class, "numeric"],
    _("Insert the billing block"),
    "",
    "NO"
);


$HD_Form->AddEditElement(
    _("RETAIL CONNECTION CHARGE"),
    "aleg_retail_connect_charge",
    _("The connection charge can be negative or positive"),
    $rate_attr,
    [Validator::class, "number"],
    _("Insert a rate")
);


$HD_Form->AddEditElement(
    _("RETAIL COST PER MINUTE"),
    "aleg_retail_cost_min",
    _("The cost can be negative where there is an out payment, or more usually, positive"),
    $rate_attr,
    [Validator::class, "number"],
    _("Insert the connect cost"),
    "",
    "NO"
);


$HD_Form->AddEditElement(
    _("RETAIL MIN DURATION"),
    "aleg_retail_initblock",
    _("Set the minimum duration to charge the customer (ie 60 seconds)"),
    ["maxlength" => 20],
    [Validator::class, "numeric"],
    _("Insert the init block"),
    "",
    "NO"
);


$HD_Form->AddEditElement(
    _("RETAIL BILLING BLOCK"),
    "aleg_retail_increment",
    _("Set the billing increment in seconds to bill your customer."),
    ["maxlength" => 20],
    [Validator::class, "numeric"],
    _("Insert the billing block"),
    "",
    "NO"
);


$HD_Form->AddEditElement(
    _("CARRIER CONNECTION CHARGE OFF PEAK"),
    "aleg_carrier_connect_charge_offp",
    _("The connection charge can be negative or positive"),
    $rate_attr,
    [Validator::class, "number"],
    _("Insert a rate"),
    _("DID Ingress Charges Off Peak")
);


$HD_Form->AddEditElement(
    _("CARRIER COST PER MINUTE OFF PEAK"),
    "aleg_carrier_cost_min_offp",
    _("The cost can be negative for Non geographic calls where there is an out-payment"),
    $rate_attr,
    [Validator::class, "number"],
    _("Insert the connect cost"),
    "",
    "NO"
);


$HD_Form->AddEditElement(
    _("CARRIER MIN DURATION OFF PEAK"),
    "aleg_carrier_initblock_offp",
    _("Set the minimum duration charged by the carrier. (i.e. 30 secs)"),
    ["maxlength" => 20],
    [Validator::class, "numeric"],
    _("Insert the buyrate init block"),
    "",
    "NO"
);


$HD_Form->AddEditElement(
    _("CARRIER BILLING BLOCK OFF PEAK"),
    "aleg_carrier_increment_offp",
    _("Set the billing increment, in seconds (billing block), that the carrier applies. (ie 30 secs)"),
    ["maxlength" => 20],
    [Validator::class, "numeric"],
    _("Insert the billing block"),
    "",
    "NO"
);


$HD_Form->AddEditElement(
    _("RETAIL CONNECTION CHARGE OFF PEAK"),
    "aleg_retail_connect_charge_offp",
    _("The connection charge can be negative or positive"),
    $rate_attr,
    [Validator::class, "number"],
    _("Insert a rate")
);


$HD_Form->AddEditElement(
    _("RETAIL COST PER MINUTE OFF PEAK"),
    "aleg_retail_cost_min_offp",
    _("The cost can be negative where there is an out payment, or more usually, positive"),
    $rate_attr,
    [Validator::class, "number"],
    _("Insert the connect cost"),
    "",
    "NO"
);


$HD_Form->AddEditElement(
    _("RETAIL MIN DURATION OFF PEAK"),
    "aleg_retail_initblock_offp",
    _("Set the minimum duration to charge the customer (ie 60 seconds)"),
    ["maxlength" => 20],
    [Validator::class, "numeric"],
    _("Insert the init block"),
    "",
    "NO"
);


$HD_Form->AddEditElement(
    _("RETAIL BILLING BLOCK OFF PEAK"),
    "aleg_retail_increment_offp",
    _("Set the billing increment in seconds to bill your customer."),
    ["maxlength" => 20],
    [Validator::class, "numeric"],
    _("Insert the billing block"),
    "",
    "NO"
);


$HD_Form->AddEditElement(
    _("MAX CONCURRENT CALLS"),
    "max_concurrent",
    _("Max concurrent calls (Feature not implemented)"),
    ["maxlength" => 5],
    null,
    "",
    "",
    "NO"
);

// Set the filter variables
$HD_Form->FG_FILTER_ENABLE = true;
$HD_Form->FG_FILTER_COLUMN = "did";
$HD_Form->FG_FILTER_LABEL = "did";

$HD_Form->FG_INTRO_TEXT_EDITION= "";
$HD_Form->FG_INTRO_TEXT_ADITION = "";
