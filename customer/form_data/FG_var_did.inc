<?php

use A2billing\Customer;
use A2billing\Forms\FormHandler;
use A2billing\Notification;
use A2billing\NotificationsDAO;

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

if (! has_rights (Customer::ACX_ACCESS)) {
    Header ("HTTP/1.0 401 Unauthorized");
    Header ("Location: PP_error.php?c=accessdenied");
    die();
}

getpost_ifset(array('id', 'billingtype', 'did', 'startingdate', 'expirationdate', 'id_cc_didgroup', 'id_cc_country', 'activated', 'fixrate', 'id_trunk', 'choose_country', 'choose_did', 'assign', 'countrycode', 'arecode', 'phonenumber', 'voip_call', 'destination', 'choose_did_rate', 'new_did_page', 'confirm_buy_did', 'action_release'));


$HD_Form = new FormHandler("cc_did_destination", "destination");

$HD_Form->no_debug();
$HD_Form -> FG_TABLE_DEFAULT_ORDER = "did";
$HD_Form -> FG_TABLE_DEFAULT_SENS = "DESC";

$HD_Form -> FG_ENABLE_EDIT_BUTTON = true;
$HD_Form -> FG_ENABLE_DELETE_BUTTON = true;
$HD_Form -> FG_ENABLE_ADD_BUTTON = true;


$actived_list = getActivationList();
$billingtype_list = getBillingTypeList();

// -- billtype: 0 = fix per month + dialoutrate, 1= fix per month, 2 = dialoutrate, 3 = free
$billingtype_list_short = getBillingTypeShortList();

$priority_list = array();
for ($k=1;$k<=5;$k++) {
    $priority_list["$k"]  = array( "Priority : $k", "$k");
}
$actived_list = array();
$actived_list["1"] = array( gettext("Active"), "1");
$actived_list["0"]  = array( gettext("Inactive"), "0");

$validated_list = array();
$validated_list["0"] = array( gettext("Pending"), "0");
$validated_list["1"] = array( gettext("Validated"), "1");

$HD_Form -> AddViewElement(gettext("DESTINATION"), "destination");
$HD_Form -> AddViewElement(gettext("DID"), "id_cc_did", true, 15, "", "lie", "cc_did", "did", "id='%id'", "%1");
$HD_Form -> AddViewElement(gettext("ACTIVATED"), "cc_did_destination.activated", true, 0, "", "list", $actived_list);
$HD_Form -> AddViewElement(gettext("PRIORITY"), "priority", true, 0, "", "list", $priority_list);
$HD_Form -> AddViewElement(gettext("USED MINUTE"), "cc_did_destination.secondusedreal", true, 30, "display_minute");
$HD_Form -> AddViewElement(gettext("COUNTRY"), "id_cc_country", true, 15, "", "lie", "cc_country", "countryname", "id='%id'", "%1");
$HD_Form -> AddViewElement(gettext("VALIDATED"), "validated", true, 0, "", "list", $validated_list);


if (isset($form_action) && ($form_action=='ask-edit' || $form_action=='edit')) {
	$HD_Form -> FG_QUERY_TABLE_NAME = "cc_did_destination";
	$country_field = '';
	$validated = 1;

	if ($voip_call == 1) {
	    // It's a Voip Call
	    $DBHandle_max = DbConnect();
	    $numrow = 0;
	    $resmax = $DBHandle_max -> Execute("SELECT * FROM cc_did_destination WHERE id='$id' AND destination='$destination' AND validated=1");

	    if ( $resmax )
		    $numrow = $resmax -> RecordCount( );
        $validated = ($numrow == 1) ? 1 : 0;

	    if ($validated)
	        NotificationsDAO::AddNotification("did_destination_edited_cust", Notification::$LOW, Notification::$CUST, $_SESSION['card_id'], Notification::$LINK_DID_DESTINATION, $id);

	    //$HD_Form->_processed['destination'] = intval($destination);

	} else {
	    // It's not a Voip call, check if we don't try to inject
	    $destination = (intval($destination) > 0) ? $destination : 'no valid';
	    //echo "Validity check ($destination)";
	}

} else {
	$HD_Form -> FG_QUERY_TABLE_NAME = "cc_did_destination, cc_did";
	$HD_Form -> FG_QUERY_WHERE_CLAUSE = " id_cc_card='".$_SESSION["card_id"]."' AND cc_did_destination.id_cc_did=cc_did.id";
    $HD_Form->list_query_conditions = ["id_cc_card" => $_SESSION["card_id"], "cc_did_destination.id_cc_did" => "`cc_did`.`id`"];
    $HD_Form->update_query_conditions = ["id_cc_card" => $_SESSION["card_id"], "cc_did_destination.id_cc_did" => "`cc_did`.`id`"];
	$country_field = ' id_cc_country,';
	$validated = 0;
}

$HD_Form -> FG_QUERY_COLUMN_LIST = 'destination, id_cc_did, cc_did_destination.activated, priority, cc_did_destination.secondusedreal,'. $country_field .'  cc_did_destination.validated, cc_did_destination.id ';

$HD_Form -> FG_ENABLE_EDIT_BUTTON = true;
$HD_Form -> FG_ENABLE_DELETE_BUTTON = true;
$HD_Form -> FG_VIEW_TABLE_WITDH = '80%';

if (isset($form_action) && ($form_action=='ask-edit')) {

    $HD_Form->AddEditElement(
        gettext("DESTINATION"),
        "destination",
        gettext("Enter here the phonenumber you want to call or the SIP/IAX/H323 peer to reach (ie: 347894999 or SIP/jeremy@182.212.1.45). To call SIP/IAX/H323 peer, you need to enable the voip_call below (voip_call = Yes) "),
        "size=50 maxlength=120",
        0,
        gettext("Insert the Destination")
    );
}

$HD_Form->AddEditRadio(
    gettext("ACTIVATED"),
    "activated",
    [[gettext("Yes"), "1"], [gettext("No"), "0"]],
    "1",
    "", "",
    gettext("Choose if you want to activate this card")
);

$HD_Form->AddEditSelect(gettext("PRIORITY"), "priority", $priority_list);

$HD_Form->AddEditRadio(
    gettext("VOIP CALL"),
    "voip_call",
    [[gettext("Yes"), "1"], [gettext("No"), "0"]],
    "1",
    "", "",
    gettext("Choose if you want to not use the trunk and let the asterisk call directly the destination (ie, Destination : SIP/jeremy@182.212.1.45)")
);

if (!isset($form_action) && ($form_action!=='ask-edit')) {
    $HD_Form->FG_EDIT_QUERY_HIDDEN_INPUTS["destination"] = $destination;
    $HD_Form->FG_EDIT_QUERY_HIDDEN_INPUTS["validated"] = $validated;
}

$HD_Form -> FG_INTRO_TEXT_EDITION= '';
$HD_Form -> FG_INTRO_TEXT_ADITION = '';

$HD_Form -> FG_LOCATION_AFTER_ADD = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";
$HD_Form -> FG_LOCATION_AFTER_EDIT = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";
$HD_Form -> FG_LOCATION_AFTER_DELETE = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";
