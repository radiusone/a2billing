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

getpost_ifset(array('id', 'amount', 'id_cc_card', 'chargetype', 'description', 'id_did', 'username'));


if (($form_action=='ask-edit') || ($form_action=='ask-add') || ($form_action=='add') || ($form_action=='edit') || ($form_action=='ask-delete') || ($form_action=='delete')){
	if ($wantinclude==1) $HD_Form_c = new FormHandler("cc_charge", "charge");
	else $HD_Form_c = new FormHandler("cc_charge", "charge");
}else {
	if ($wantinclude==1) $HD_Form_c = new FormHandler("cc_charge, cc_card", "charge", "cc_charge.id");
	else $HD_Form_c = new FormHandler("cc_charge, cc_card", "charge", "cc_charge.id");
}

$HD_Form_c->no_debug();
if ($wantinclude==1) $HD_Form_c -> FG_TABLE_DEFAULT_ORDER = $order;
else $HD_Form_c -> FG_TABLE_DEFAULT_ORDER = "id_cc_card";

$HD_Form_c -> FG_TABLE_DEFAULT_SENS = "DESC";
$HD_Form_c -> FG_QUERY_WHERE_CLAUSE = " cc_card.id=id_cc_card ";

$HD_Form_c -> FG_LIST_ADDING_BUTTON1 = true;
$HD_Form_c -> FG_LIST_ADDING_BUTTON_LINK1 = "A2B_entity_charge.php?form_action=ask-add";
$HD_Form_c -> FG_LIST_ADDING_BUTTON_ALT1 = $HD_Form_c -> FG_LIST_ADDING_BUTTON_MSG1 = gettext("Add a Charge");
$HD_Form_c -> FG_LIST_ADDING_BUTTON_IMG1 = Images_Path ."/add.png" ;

// -- chargetype : 1 - connection charge for DID setup, 2 - Montly charge for DID use, 3 - Subscription fee, 4 - Extra Charge, etc...
$chargetype_list = array();
$chargetype_list["1"] = array( gettext("Connection charge for DID setup"), "1");
$chargetype_list["2"] = array( gettext("Monthly Charge for DID use"), "2");
$chargetype_list["3"] = array( gettext("Subscription fee"), "3");
$chargetype_list["4"] = array( gettext("Extra charge"), "4");




$HD_Form_c -> AddViewElement(gettext("ACCOUNT HOLDER"), "id_cc_card", true, 25, "", "lie", "cc_card", "lastname, firstname", "id='%id'", "%1 %2");
$HD_Form_c -> AddViewElement(gettext("ACCOUNT NUMBER"), "username", true, 30, "display_customer_link");
$HD_Form_c -> AddViewElement(gettext("DATE"), "cc_charge.creationdate", true, 16, "display_dateformat");
$HD_Form_c -> AddViewElement(gettext("AMOUNT"), "amount");
$HD_Form_c -> AddViewElement(gettext("CHARGE TYPE"), "chargetype", true, 30, "", "list", $chargetype_list);
$HD_Form_c -> AddViewElement(gettext("DESCRIPTION"), "description");

// added a parameter to append  FG_TABLE_ID  ( by default ) or disable 0.
$HD_Form_c -> FieldViewElement ('id_cc_card, username, cc_charge.creationdate, amount, chargetype, description');

$HD_Form_c -> FG_ENABLE_EDIT_BUTTON = true;
$HD_Form_c -> FG_ENABLE_DELETE_BUTTON = true;
$HD_Form_c -> FG_ENABLE_ADD_BUTTON = true;

// TODO integrate in Framework
if ($form_action=='ask-add'){
	$DBHandle_max  = DbConnect();
	if ($username) {
		$resmax = $DBHandle_max -> Execute("SELECT id FROM cc_card where username='$username'");
		$numrow = 0;
		if ($resmax)
			$numrow = $resmas -> RecordCount( );
		if ($numrow!=0) {
			$db_row [] = $resmax -> fetchRow();
			$val_default = "value='".$db_row[0][0]."'";
		}
	}
	if ($id_did){
		$resmax = $DBHandle_max -> Execute("SELECT fixrate FROM cc_did where id='$id_did'");
		$numrow = 0;
		if ($resmax)
			$numrow = $resmax -> RecordCount( );
		if ($numrow!=0){
			$db_row2 [] = $resmax -> fetchRow();
			$val_default_fixrate = "value='".$db_row2[0][0]."'";
		}
	}
}

$HD_Form_c->AddEditPopup(
    "id_cc_card",
    gettext("ID CARD"),
    "A2B_entity_card.php?popup_select=1&",
    gettext("Set the Customer ID"),
    gettext("Insert the Customer ID")
);

$HD_Form_c->AddEditElement(
    gettext("AMOUNT"),
    "amount",
    "",
    "size=20  maxlength=20 $val_default_fixrate",
    12,
    gettext("Insert the amount")
);

$HD_Form_c->AddEditSelect("chargetype", $chargetype_list, gettext("CHARGE TYPE"));

$HD_Form_c->AddEditTextarea(
    "description",
    gettext("DESCRIPTION"),
    "cols='50' rows='4'",
    gettext("Insert the description")
);

$HD_Form_c -> FieldEditElement ('id_cc_card, amount, chargetype, description');

// Set the filter variables

$HD_Form_c -> FG_FILTER_ENABLE = true;
$HD_Form_c -> FG_FILTER_COLUMN = 'username';
$HD_Form_c -> FG_FILTER_LABEL = 'account number';

$HD_Form_c -> FG_INTRO_TEXT_ADITION = '';

$HD_Form_c -> FG_LOCATION_AFTER_ADD = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";
$HD_Form_c -> FG_LOCATION_AFTER_EDIT = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";
$HD_Form_c -> FG_LOCATION_AFTER_DELETE = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";

