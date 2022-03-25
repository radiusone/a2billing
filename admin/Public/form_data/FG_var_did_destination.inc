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

if (! has_rights (ACX_ADMINISTRATOR)) {
    Header ("HTTP/1.0 401 Unauthorized");
    Header ("Location: PP_error.php?c=accessdenied");
    die();
}


getpost_ifset(array('id', 'destination', 'id_cc_card', 'id_cc_did', 'activated', 'priority', 'id_trunk', 'voip_call'));

if ( (empty($form_action) || $form_action=="list")) {
	$HD_Form = new FormHandler("cc_did_destination LEFT JOIN cc_did ON cc_did.id = cc_did_destination.id_cc_did", "Did Destination");
} else {
	$HD_Form = new FormHandler("cc_did_destination");
}

$HD_Form->no_debug();
$HD_Form -> FG_TABLE_DEFAULT_ORDER = "destination";
$HD_Form -> FG_TABLE_DEFAULT_SENS = "DESC";

$HD_Form -> FG_QUERY_PRIMARY_KEY = 'cc_did_destination.id';

$HD_Form ->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form ->FG_LIST_ADDING_BUTTON_LINK1 = "A2B_entity_did_destination.php?form_action=ask-add&section=".$_SESSION["menu_section"];
$HD_Form ->FG_LIST_ADDING_BUTTON_ALT1 = $HD_Form ->FG_LIST_ADDING_BUTTON_MSG1 = gettext("Add DESTINATION");
$HD_Form ->FG_LIST_ADDING_BUTTON_IMG1 = Images_Path ."/world_add.png" ;


$actived_list = getActivationList();


$billingtype_list = getBillingTypeList();
// -- billtype: 0 = fix per month + dialoutrate, 1= fix per month, 2 = dialoutrate, 3 = free

$priority_list = array();
for ($k=1;$k<=5;$k++)
    $priority_list["$k"]  = array( "$k", "$k");

$validated_list = array();
$validated_list["0"] = array( gettext("Pending"), "0");
$validated_list["1"] = array( gettext("Validated"), "1");


$HD_Form -> AddViewElement(gettext("DESTINATION"), "destination");
$HD_Form -> AddViewElement(gettext("CREATION DATE"), "creationdate", true, 16, "display_dateformat");
$HD_Form -> AddViewElement(gettext("DID"), "did", true, 15);
$HD_Form -> AddViewElement(gettext("ACCOUNT NUMBER"), "id_cc_card", true, 0, "", "lie_link", "cc_card", "username,id", "id='%id'", "%1", "A2B_entity_card.php");
$HD_Form -> AddViewElement(gettext("ACTIVE"), "activated", true, 0, "", "list", $actived_list);
$HD_Form -> AddViewElement(gettext("VOIP"), "voip_call", true, 0, "", "list", $actived_list);
$HD_Form -> AddViewElement(gettext("PRIOR"), "priority", true, 0, "", "list", $priority_list);
$HD_Form -> AddViewElement(gettext("MINUTES USED"), "secondusedreal", true, 30, "display_minute");
$HD_Form -> AddViewElement(gettext("VALIDATED"), "validated", true, 0, "", "list", $validated_list);



// added a parameter to append  FG_TABLE_ID  ( by default ) or disable 0.
$HD_Form -> FieldViewElement ('destination, cc_did_destination.creationdate, cc_did.did, id_cc_card, cc_did_destination.activated, voip_call, priority, cc_did_destination.secondusedreal, validated');

$HD_Form -> FG_ENABLE_EDIT_BUTTON = true;
$HD_Form -> FG_ENABLE_DELETE_BUTTON = true;
$HD_Form -> FG_ENABLE_ADD_BUTTON = true;

// TODO integrate in Framework
if ($form_action=="ask-add"){
	$begin_date = date("Y");
	$begin_date_plus = date("Y") + 10;
	$end_date = date("-m-d H:i:s");
	$comp_date = "value='".$begin_date.$end_date."'";
	$comp_date_plus = "value='".$begin_date_plus.$end_date."'";
}



$HD_Form -> AddEditElement (gettext("DESTINATION"),
    "destination",
    '$value',
    gettext("Enter the phone number you wish to call, or the VoIP client to reach. (ie: 347894999 or SIP/jeremy@182.212.1.45). If the call is VoIP, the VoIP_Call must be set to yes."),
    "",
    "INPUT",
    "size=50 maxlength=120",
    0, gettext("Insert the DID"));

if ($form_action!='ask-edit' && $form_action!='edit') {

    $HD_Form->AddEditPopup(
        "id_cc_card",
        gettext("CUSTOMER ID"),
        "A2B_entity_card.php?popup_select=1&",
        gettext("Select the Customer ID"),
        gettext("Insert the Customer ID")
    );

    $HD_Form->AddEditSqlSelect("id_cc_did", gettext("DID"), "cc_did", "did,id");
}

$HD_Form->AddEditRadio(
    "activated",
    [[gettext("Yes"), "1"], [gettext("No"), "0"]],
    gettext("ACTIVATED"),
    "1",
    "",
    gettext("Choose if you want to activate this card")
);

$HD_Form->AddEditSelect("validated", $validated_list, gettext("VALIDATED"));

$HD_Form->AddEditSelect("priority", $priority_list, gettext("PRIORITY"));

$HD_Form->AddEditRadio(
    "voip_call",
    [[gettext("Yes"), "1"], [gettext("No"), "0"]],
    gettext("ACTIVATED"),
    "1",
    "",
    gettext("Choose if you want to not use the trunk and let the asterisk call directly the destination (ie, Destination : SIP/jeremy@182.212.1.45)")
);

if ($form_action!='ask-edit' && $form_action!='edit') {
	$HD_Form -> FieldEditElement ('destination, id_cc_card, id_cc_did, activated, validated, priority, voip_call');
} else {
	$HD_Form -> FieldEditElement ('destination, activated, validated, priority, voip_call');
}

// Set the filter variables
$HD_Form -> FG_FILTER_ENABLE = true;
$HD_Form -> FG_FILTER_COLUMN = 'did';
$HD_Form -> FG_FILTER_LABEL = 'did';

if (isset($filterprefix)  &&  (strlen($filterprefix)>0)) {
	if (strlen($HD_Form -> FG_QUERY_WHERE_CLAUSE)>0) $HD_Form -> FG_QUERY_WHERE_CLAUSE.=" AND ";
	$HD_Form -> FG_QUERY_WHERE_CLAUSE .= "cc_did.did like '$filterprefix%'";
}




$HD_Form -> FG_INTRO_TEXT_ADITION = '';



$HD_Form -> FG_ADDITIONAL_FUNCTION_AFTER_ADD = 'did_destination_add';
$HD_Form -> FG_ADDITIONAL_FUNCTION_AFTER_DELETE = 'did_destination_del';

$HD_Form -> FG_LOCATION_AFTER_ADD = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";
$HD_Form -> FG_LOCATION_AFTER_EDIT = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";
$HD_Form -> FG_LOCATION_AFTER_DELETE = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";



