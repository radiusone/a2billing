<?php

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

$HD_Form = new FormHandler("cc_did", "did");

$HD_Form->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form->FG_LIST_ADDING_BUTTON_LINK1 = "A2B_entity_did.php?form_action=ask-add";
$HD_Form->FG_LIST_ADDING_BUTTON_ALT1 = $HD_Form->FG_LIST_ADDING_BUTTON_MSG1 = _("Add DID");
$HD_Form->FG_LIST_ADDING_BUTTON_IMG1 = Images_Path ."/telephone_add.png" ;

$HD_Form->FG_TABLE_DEFAULT_ORDER = "did";
$HD_Form->FG_TABLE_DEFAULT_SENS = "DESC";
$HD_Form->FG_ADDITIONAL_FUNCTION_AFTER_ADD = 'add_did_use';
$HD_Form->FG_ADDITIONAL_FUNCTION_AFTER_DELETE = 'did_use_delete';
$HD_Form->FG_ADDITIONAL_FUNCTION_BEFORE_DELETE = 'is_did_in_use';

$billingtype_list = getBillingTypeList();
// -- billtype: 0 = fix per month + dialoutrate, 1= fix per month, 2 = dialoutrate, 3 = free
$billingtype_list_short = getBillingTypeShortList();
$yesno = getYesNoList();

$HD_Form->AddViewElement(_("DID"), "did", true, 0, "display_did");
$HD_Form->AddViewElement(_("Customer"), "iduser", true, 0, "display_customer_name_id_link");
$HD_Form->AddViewElement(_("Billing"), "billingtype", true, 0, "", "list", $billingtype_list_short);
$HD_Form->AddViewElement(_("Start Date"), "startingdate", true, 19, "display_dateformat");
$HD_Form->AddViewElement(_("DID Group"), "id_cc_didgroup", true, 15, "", "lie", "cc_didgroup", "didgroupname", "id='%id'", "%1");
$HD_Form->AddViewElement(_("Country"), "id_cc_country", true, 15, "", "lie", "cc_country", "countryname", "id='%id'", "%1");
$HD_Form->AddViewElement(_("Active"), "activated", true, 0, "", "list", $yesno);
$HD_Form->AddViewElement("<acronym title=\"" . _("Minutes Used") . "\">" . _("Min") . "</acronym>", "secondusedreal", true, 30, "display_minute");
$HD_Form->AddViewElement("<acronym title=\"" . _("Reserved") . "\">" . _("Res") . "</acronym>", "reserved", true, 0, "", "list", $yesno);
$HD_Form->AddViewElement("<acronym title=\"" . _("Monthly Rate") . "\">" . _("MR") . "</acronym>", "fixrate");
$HD_Form->AddViewElement("<acronym title=\"" . _("Sell Rate") . "\">" . _("SR") . "</acronym>", "selling_rate");

$HD_Form->FieldViewElement ('did, iduser, billingtype, startingdate, id_cc_didgroup, id_cc_country, activated, secondusedreal, reserved, fixrate, selling_rate');

$HD_Form->FG_ENABLE_ADD_BUTTON = true;
$HD_Form->FG_ENABLE_EDIT_BUTTON = true;
$HD_Form->FG_ENABLE_DELETE_BUTTON = true;
$HD_Form->FG_SPLITABLE_FIELDS[] = 'did';

$comp_date = $comp_date_plus = $didval = $fixval = "";
if ($form_action === "ask-add" || $form_action === "purchase") {
    $date = new DateTime();
    $comp_date = sprintf('value="%s"', $date->format("Y-m-d H:i:s"));
    $comp_date_plus = sprintf('value="%s"', $date->modify("+ 10 years")->format("Y-m-d H:i:s"));
    if ($form_action === "purchase") {
        $didval = "value='$did'";
        $fixval = "value='$fixrate'";
    } else {
        $fixval = "value='0'";
    }
}

$HD_Form->AddEditElement(
    _("DID"),
    "did",
    _("Define the DID's. If you ADD a new DID, NOT an EDIT, you can define a range of DID. <br>80412340210-80412340218 would add all DID's between the range, whereas DIDs separated by a comma e.g. 80412340210,80412340212,80412340214 would only add the individual DID listed."),
    "maxlength=60 $didval",
    9,
    _("Insert the DID")
);


$HD_Form->AddEditSelect("billingtype", $billingtype_list, _("BILLING"));

$HD_Form->AddEditElement(
    _("START DATE"),
    "startingdate",
    _("Format YYYY-MM-DD HH:MM:SS. For instance, '2004-12-31 00:00:00'"),
    "maxlength=40  $comp_date",
    10,
    _("Insert the starting date"),
    "",
    "",
    "res_display_dateformat"
);


$HD_Form->AddEditElement(
    _("EXPIRY DATE"),
    "expirationdate",
    _("Format YYYY-MM-DD HH:MM:SS. For instance, '2004-12-31 00:00:00'"),
    "maxlength=40  $comp_date_plus",
    10,
    _("Insert the expiration date"),
    "",
    "",
    "res_display_dateformat"
);

$HD_Form->AddEditSqlSelect("id_cc_didgroup", _("DIDGROUP"), "cc_didgroup", "didgroupname,id");

$HD_Form->AddEditSqlSelect("id_cc_country", _("COUNTRY"), "cc_country", "countryname,id");

$HD_Form->AddEditRadio(
    "activated",
    $yesno,
    gettext("ACTIVATED"),
    "1",
    "",
    gettext("Choose if you want to activate this card")
);

$HD_Form->AddEditElement(
    _("MONTHLY RATE"),
    "fixrate",
    _("currency : ") . strtoupper(BASE_CURRENCY),
    "maxlength=20 $fixval",
    12,
    _("Insert the monthly rate"),
    _("Define the renting cost for the customers.")
);


$HD_Form->AddEditElement(
    _("CONNECT CHARGE"),
    "connection_charge",
    _("Apply a connection charge to connect DID together"),
    "maxlength=20 $fixval",
    12,
    _("Insert the connect charge"),
    _("DID On Net Charges"),
    "NO"
);
   // On Net Charges is where one customer calls another customer without any external carrier being involved in the call

$HD_Form->AddEditElement(
    _("SELLING RATE"),
    "selling_rate",
    _("The retail rate; or the cost per minute to apply to the customer to connect DID together, e.g. 0.02"),
    "maxlength=20 $fixval",
    12,
    _("Insert the rate"),
    ""
);

$HD_Form->AddEditTextarea(
    "aleg_timeinterval",
    _("TIME INTERVAL"),
    "rows='4'",
    _("Insert time definition for peak and off peak time intervals."),
    _("Peak / Off Peak Time definition"),
    null,
    _("The format is \"start_time;day_of_week;day_of_month;month;time_interval_type\" e.g. 7:00-19:00|mon-fri|*|*|p for peak hours or *|*|24|dec|o for off peak during Xmas day and so on. The last character indicates time interval type where \"p\" means PEAK and \"o\" OFF-PEAK. More definition one per line are supported. If there are PEAK and OFF-PEAK time interval definitions overlaps, than the OFF-PEAK is used."),
);

$HD_Form->AddEditElement(
    _("CARRIER CONNECTION CHARGE"),
    "aleg_carrier_connect_charge",
    _("The connection charge can be negative or positive"),
    "maxlength=20 $fixval",
    12,
    _("Insert a rate"),
    _("DID Ingress Charges Peak")
);


$HD_Form->AddEditElement(
    _("CARRIER COST PER MINUTE"),
    "aleg_carrier_cost_min",
    _("The cost can be negative for Non geographic calls where there is an out-payment"),
    "maxlength=20 $fixval",
    12,
    _("Insert the connect cost"),
    "",
    "NO"
);


$HD_Form->AddEditElement(
    _("CARRIER MIN DURATION"),
    "aleg_carrier_initblock",
    _("Set the minimum duration charged by the carrier. (i.e. 30 secs)"),
    "maxlength=20",
    4,
    _("Insert the buyrate init block"),
    "",
    "NO"
);


$HD_Form->AddEditElement(
    _("CARRIER BILLING BLOCK"),
    "aleg_carrier_increment",
    _("Set the billing increment, in seconds (billing block), that the carrier applies. (ie 30 secs)"),
    "maxlength=20",
    4,
    _("Insert the billing block"),
    "",
    "NO"
);


$HD_Form->AddEditElement(
    _("RETAIL CONNECTION CHARGE"),
    "aleg_retail_connect_charge",
    _("The connection charge can be negative or positive"),
    "maxlength=20 $fixval",
    12,
    _("Insert a rate")
);


$HD_Form->AddEditElement(
    _("RETAIL COST PER MINUTE"),
    "aleg_retail_cost_min",
    _("The cost can be negative where there is an out payment, or more usually, positive"),
    "maxlength=20 $fixval",
    12,
    _("Insert the connect cost"),
    "",
    "NO"
);


$HD_Form->AddEditElement(
    _("RETAIL MIN DURATION"),
    "aleg_retail_initblock",
    _("Set the minimum duration to charge the customer (ie 60 seconds)"),
    "maxlength=20",
    4,
    _("Insert the init block"),
    "",
    "NO"
);


$HD_Form->AddEditElement(
    _("RETAIL BILLING BLOCK"),
    "aleg_retail_increment",
    _("Set the billing increment in seconds to bill your customer."),
    "maxlength=20",
    4,
    _("Insert the billing block"),
    "",
    "NO"
);


$HD_Form->AddEditElement(
    _("CARRIER CONNECTION CHARGE OFF PEAK"),
    "aleg_carrier_connect_charge_offp",
    _("The connection charge can be negative or positive"),
    "maxlength=20 $fixval",
    12,
    _("Insert a rate"),
    _("DID Ingress Charges Off Peak")
);


$HD_Form->AddEditElement(
    _("CARRIER COST PER MINUTE OFF PEAK"),
    "aleg_carrier_cost_min_offp",
    _("The cost can be negative for Non geographic calls where there is an out-payment"),
    "maxlength=20 $fixval",
    12,
    _("Insert the connect cost"),
    "",
    "NO"
);


$HD_Form->AddEditElement(
    _("CARRIER MIN DURATION OFF PEAK"),
    "aleg_carrier_initblock_offp",
    _("Set the minimum duration charged by the carrier. (i.e. 30 secs)"),
    "maxlength=20",
    4,
    _("Insert the buyrate init block"),
    "",
    "NO"
);


$HD_Form->AddEditElement(
    _("CARRIER BILLING BLOCK OFF PEAK"),
    "aleg_carrier_increment_offp",
    _("Set the billing increment, in seconds (billing block), that the carrier applies. (ie 30 secs)"),
    "maxlength=20",
    4,
    _("Insert the billing block"),
    "",
    "NO"
);


$HD_Form->AddEditElement(
    _("RETAIL CONNECTION CHARGE OFF PEAK"),
    "aleg_retail_connect_charge_offp",
    _("The connection charge can be negative or positive"),
    "maxlength=20 $fixval",
    12,
    _("Insert a rate"),
    ""
);


$HD_Form->AddEditElement(
    _("RETAIL COST PER MINUTE OFF PEAK"),
    "aleg_retail_cost_min_offp",
    _("The cost can be negative where there is an out payment, or more usually, positive"),
    "maxlength=20 $fixval",
    12,
    _("Insert the connect cost"),
    "",
    "NO"
);


$HD_Form->AddEditElement(
    _("RETAIL MIN DURATION OFF PEAK"),
    "aleg_retail_initblock_offp",
    _("Set the minimum duration to charge the customer (ie 60 seconds)"),
    "maxlength=20",
    4,
    _("Insert the init block"),
    "",
    "NO"
);


$HD_Form->AddEditElement(
    _("RETAIL BILLING BLOCK OFF PEAK"),
    "aleg_retail_increment_offp",
    _("Set the billing increment in seconds to bill your customer."),
    "maxlength=20",
    4,
    _("Insert the billing block"),
    "",
    "NO"
);


$HD_Form->AddEditElement(
    _("MAX CONCURRENT CALLS"),
    "max_concurrent",
    _("Max concurrent calls (Feature not implemented)"),
    "size=5 maxlength=5",
    null,
    "",
    "",
    "NO"
);

$HD_Form->FieldEditElement('did, billingtype, startingdate, expirationdate, id_cc_didgroup, id_cc_country, activated, fixrate, connection_charge, selling_rate, aleg_timeinterval, aleg_carrier_connect_charge, aleg_carrier_cost_min, aleg_carrier_initblock, aleg_carrier_increment, aleg_retail_connect_charge, aleg_retail_cost_min, aleg_retail_initblock, aleg_retail_increment, aleg_carrier_connect_charge_offp, aleg_carrier_cost_min_offp, aleg_carrier_initblock_offp, aleg_carrier_increment_offp, aleg_retail_connect_charge_offp, aleg_retail_cost_min_offp, aleg_retail_initblock_offp, aleg_retail_increment_offp, max_concurrent');

// Set the filter variables
$HD_Form->FG_FILTER_ENABLE = true;
$HD_Form->FG_FILTER_COLUMN = 'did';
$HD_Form->FG_FILTER_LABEL = 'did';

$HD_Form->FG_INTRO_TEXT_EDITION= '';
$HD_Form->FG_INTRO_TEXT_ADITION = '';

$HD_Form->FG_LOCATION_AFTER_ADD = "?form_action=list&amp;id=";
$HD_Form->FG_LOCATION_AFTER_EDIT = "?form_action=list&amp;id=";
$HD_Form->FG_LOCATION_AFTER_DELETE = "?form_action=list&amp;id=";
