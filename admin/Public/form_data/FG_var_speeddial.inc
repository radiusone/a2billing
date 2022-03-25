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


getpost_ifset(array('id', 'speeddial', 'id_cc_card', 'name', 'phone','popup_select','popup_formname','popup_fieldname'));

if (!isset($form_action) || $form_action=="list") {
	$HD_Form = new FormHandler("cc_speeddial LEFT JOIN cc_card ON cc_card.id = cc_speeddial.id_cc_card", "speed dial");
}else{
	$HD_Form = new FormHandler("cc_speeddial ", "speed dial");
}
$HD_Form->no_debug();
$HD_Form -> FG_TABLE_DEFAULT_ORDER = "cc_speeddial.creationdate";
$HD_Form -> FG_TABLE_DEFAULT_SENS = "DESC";
$HD_Form -> FG_QUERY_PRIMARY_KEY="cc_speeddial.id";

$HD_Form ->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form ->FG_LIST_ADDING_BUTTON_LINK1 = "A2B_entity_speeddial.php?form_action=ask-add&atmenu=speeddial&section=".$_SESSION["menu_section"];
$HD_Form ->FG_LIST_ADDING_BUTTON_ALT1 = $HD_Form ->FG_LIST_ADDING_BUTTON_MSG1 = gettext("Add Speed Dial");
$HD_Form ->FG_LIST_ADDING_BUTTON_IMG1 = Images_Path ."/page_white_add.png" ;


$speeddial_list = array();
for ($k=0;$k<=9;$k++)
	$speeddial_list["$k"] = array("$k", "$k");


$HD_Form -> AddViewElement(gettext("SPEED DIAL"), "cc_speeddial.speeddial");
$HD_Form -> AddViewElement(gettext("PHONE NUMBER"), "cc_speeddial.phone");
$HD_Form -> AddViewElement(gettext("NAME"), "cc_speeddial.name");
$HD_Form -> AddViewElement(gettext("ACCOUNT"), " cc_speeddial.id_cc_card", true, 30, "display_customer_id_link");
$HD_Form -> AddViewElement(gettext("DATE"), "cc_speeddial.creationdate", true, 16, "display_dateformat");


$HD_Form -> FieldViewElement ('cc_speeddial.speeddial, cc_speeddial.phone, cc_speeddial.name, cc_speeddial.id_cc_card, cc_speeddial.creationdate');

$HD_Form -> FG_ENABLE_EDIT_BUTTON = true;
$HD_Form -> FG_ENABLE_DELETE_BUTTON = true;
$HD_Form -> FG_ENABLE_ADD_BUTTON = true;

$HD_Form->AddEditSelect("speeddial", $speeddial_list, gettext("SPEED DIAL"));

$HD_Form -> AddEditElement (gettext("NAME"),
    "name",
    '$value',
    gettext("Enter the name or label that will identify this speed dial"),
    "",
    "INPUT",
    "size=40 maxlength=50",
    0, gettext("Insert the name"));


$HD_Form -> AddEditElement (gettext("PHONE"),
    "phone",
    '$value',
    gettext("Enter the phone number for the speed dial."),
    "",
    "INPUT",
    "size=30 maxlength=50",
    4, gettext("Insert the phone"));

$HD_Form->AddEditPopup(
    "id_cc_card",
    gettext("CARD ID"),
    "A2B_entity_card.php?popup_select=1&",
    gettext("Define the card number ID to use."),
    gettext("Insert the card number ID")
);

$HD_Form -> FieldEditElement ('cc_speeddial.speeddial, cc_speeddial.name, cc_speeddial.phone, cc_speeddial.id_cc_card');


$HD_Form -> FG_FILTER_ENABLE = true;
$HD_Form -> FG_FILTER_COLUMN = 'username';
$HD_Form -> FG_FILTER_LABEL = gettext('Account Number');


if (isset($filterprefix)  &&  (strlen($filterprefix)>0)){
	if (strlen($HD_Form -> FG_QUERY_WHERE_CLAUSE)>0) $HD_Form -> FG_QUERY_WHERE_CLAUSE.=" AND ";
	$HD_Form -> FG_QUERY_WHERE_CLAUSE.="username like '$filterprefix%'";
}

$HD_Form -> FG_INTRO_TEXT_ADITION = '';

$HD_Form -> FG_LOCATION_AFTER_DELETE = $HD_Form -> FG_LOCATION_AFTER_EDIT = $HD_Form -> FG_LOCATION_AFTER_ADD = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?atmenu=document&stitle=Document&wh=AC&id=";
