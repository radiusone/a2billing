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

Admin::checkPageAccess(Admin::ACX_ADMINISTRATOR);

getpost_ifset(array('id', 'id_cc_card', 'id_subscription_fee', 'datestart', 'datestop', 'product_name'));

$HD_Form = new FormHandler("cc_card_subscription", "Subscriber");
$HD_Form->no_debug();

$HD_Form -> FG_TABLE_DEFAULT_ORDER = "id";
$HD_Form -> FG_TABLE_DEFAULT_SENS = "DESC";


$HD_Form ->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form ->FG_LIST_ADDING_BUTTON_LINK1 = "A2B_entity_subscriber.php?form_action=ask-add";
$HD_Form ->FG_LIST_ADDING_BUTTON_ALT1 = $HD_Form ->FG_LIST_ADDING_BUTTON_MSG1 = gettext("Add SUBSCRIBER");
$HD_Form ->FG_LIST_ADDING_BUTTON_IMG1 = Images_Path ."/text_signature.png" ;


$HD_Form -> AddViewElement(gettext("ACCOUNT NUMBER"), "id_cc_card", true, 15, "", "lie", "cc_card", "username", "id='%id'", "%1");
$HD_Form -> AddViewElement(gettext("SERVICE"), "id_subscription_fee", true, 15, "", "lie", "cc_subscription_service", "label", "id='%id'", "%1");
$HD_Form -> AddViewElement(gettext("DATE START"), "startdate");
$HD_Form -> AddViewElement(gettext("DATE STOP"), "stopdate");
$HD_Form -> AddViewElement(gettext("PRODUCT"), "product_name", true, 50);

// added a parameter to append  FG_TABLE_ID  ( by default ) or disable 0.
$HD_Form -> FieldViewElement ('id_cc_card, id_subscription_fee, startdate, stopdate, product_name');

$HD_Form -> FG_ENABLE_EDIT_BUTTON = true;
$HD_Form -> FG_ENABLE_DELETE_BUTTON = true;
$HD_Form -> FG_ENABLE_ADD_BUTTON = true;

$comp_zero = $comp_date_plus='';
if (isset($form_action) && $form_action=="ask-add") {
	$begin_date = date("Y");
	$end_date = date("-m-d H:i:s");
	$comp_date = "value='".$begin_date.$end_date."'";
	//set passed id if any (used if called from customer screen, id is passed to pr-epopluate value
	$idd = "value='$id'";
	//Max value Mysql Timestamp can store is '2038-01-19 03:14:07' UTC
	$comp_date_plus = "value='2038-01-01 00:00:00'";
	$comp_zero = "value='0'";
}

$HD_Form->AddEditPopup(
    "id_cc_card",
    gettext("CUSTOMER ID"),
    "A2B_entity_card.php?popup_select=1&",
    gettext("Select the Customer ID"),
    gettext("Insert the Customer ID"),
    $idd
);

$HD_Form->AddEditSqlSelect(
    "id_subscription_fee",
    gettext("ID SUBSCRIPTION SERVICE"),
    "cc_subscription_service",
    "label,id",
    "",
    "",
    '<option value="-1" selected="selected">' . gettext("NOT DEFINED") . '</option>'
);

$HD_Form->AddEditElement(
    gettext("START DATE"),
    "startdate",
    gettext("please use the format YYYY-MM-DD HH:MM:SS. For instance, '2004-12-31 00:00:00'"),
    "size=40 maxlength=40  $comp_date",
    10,
    gettext("Insert the start date"),
    "",
    "NO-NULL",
    "res_display_dateformat"
);

$HD_Form->AddEditElement(
    gettext("STOP DATE"),
    "stopdate",
    gettext("please use the format YYYY-MM-DD HH:MM:SS. For instance, '2004-12-31 00:00:00'"),
    "size=40 maxlength=40  $comp_date_plus",
    10,
    gettext("Insert the stop date"),
    "",
    "NO-NULL",
    "res_display_dateformat"
);

$HD_Form->AddEditTextarea(
    gettext("PRODUCT LABEL"),
    "product_name",
    "",
    "cols='50' rows='4'", null, gettext("Enter the name of the product wich is subscribed")
);

$HD_Form -> FieldEditElement ('id_cc_card, id_subscription_fee, startdate, stopdate, product_name');



$HD_Form -> FG_INTRO_TEXT_ADITION = gettext("Apply subscription to account");

$HD_Form -> FG_LOCATION_AFTER_ADD = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";
$HD_Form -> FG_LOCATION_AFTER_EDIT = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";
$HD_Form -> FG_LOCATION_AFTER_DELETE = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";


