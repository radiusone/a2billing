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


getpost_ifset(array('id', 'card_id', 'date', 'credit','description,added_invoice'));

if (!isset($form_action) || $form_action=="list") {
	$HD_Form = new FormHandler("cc_logrefill LEFT JOIN cc_card ON cc_card.id=card_id", "Refill");
} else {
	$HD_Form = new FormHandler("cc_logrefill", "Refill");
}

$HD_Form -> FG_QUERY_PRIMARY_KEY = 'cc_logrefill.id';

$HD_Form->no_debug();
$HD_Form -> FG_TABLE_DEFAULT_SENS = "DESC";

if (has_rights (ACX_MODIFY_REFILLS)) {
	$HD_Form ->FG_LIST_ADDING_BUTTON1 = true;
	$HD_Form ->FG_LIST_ADDING_BUTTON_LINK1 = "A2B_entity_logrefill.php?form_action=ask-add&section=".$_SESSION["menu_section"];
	$HD_Form ->FG_LIST_ADDING_BUTTON_ALT1 = $HD_Form ->FG_LIST_ADDING_BUTTON_MSG1 = gettext("Add Refill");
	$HD_Form ->FG_LIST_ADDING_BUTTON_IMG1 = Images_Path ."/time_add.png" ;
}

$list_refill_type = getRefillType_List();

$HD_Form -> AddViewElement(gettext("ID"), "cc_logrefill.id");
$HD_Form -> AddViewElement(gettext("ACCOUNT"), "username", true, 30, "display_customer_id_link");
$HD_Form -> AddViewElement(gettext("REFILL DATE"), "cc_logrefill.date", true, 30, "display_dateformat");
$HD_Form -> AddViewElement(gettext("REFILL AMOUNT"), "credit", true, 0, "display_money");
$HD_Form -> AddViewElement(gettext("REFILL TYPE"), "refill_type", true, 0, "", "list", $list_refill_type);

$HD_Form -> FieldViewElement ('cc_logrefill.id, cc_logrefill.card_id, cc_logrefill.date, cc_logrefill.credit , cc_logrefill.refill_type');

if (!($popup_select>=1))	 $HD_Form -> FG_FILTER_SEARCH_FORM = true;
$HD_Form -> FG_FILTER_SEARCH_SESSION_NAME = 'refill_cust_selection';
$HD_Form -> FG_FILTER_SEARCH_TOP_TEXT = gettext('Define specific criteria to search for refills created.');
$HD_Form -> FG_FILTER_SEARCH_1_TIME = true;
$HD_Form -> FG_FILTER_SEARCH_1_TIME_TEXT = gettext('DATE');
$HD_Form -> FG_FILTER_SEARCH_1_TIME_FIELD = 'date';


$HD_Form -> AddSearchTextInput(gettext("ACCOUNT NUMBER"), 'username','usernametype');
$HD_Form -> AddSearchTextInput(gettext("LASTNAME"),'lastname','lastnametype');
$HD_Form -> AddSearchTextInput(gettext("FIRSTNAME"),'firstname','firstnametype');
$HD_Form -> FG_FILTER_SEARCH_DELETE_ALL =false;

$HD_Form -> FG_ENABLE_INFO_BUTTON = true;
$HD_Form -> FG_INFO_BUTTON_LINK = "A2B_refill_info.php?id=";
$HD_Form -> FG_FILTER_ENABLE = true;
$HD_Form -> FG_FILTER_COLUMN = 'cardnumber';
$HD_Form -> FG_FILTER_LABEL = 'Account number';

if (isset($filterprefix)  &&  (strlen($filterprefix)>0)) {
	if (strlen($HD_Form -> FG_QUERY_WHERE_CLAUSE)>0) $HD_Form -> FG_QUERY_WHERE_CLAUSE.=" AND ";
	$HD_Form -> FG_QUERY_WHERE_CLAUSE.="username like '$filterprefix%'";
}

$HD_Form -> FG_ENABLE_ADD_BUTTON = true;
if (has_rights (ACX_MODIFY_REFILLS)) {

	$HD_Form -> FG_ENABLE_EDIT_BUTTON = true;
	$HD_Form -> FG_ENABLE_DELETE_BUTTON = true;

	if ($form_action=="ask-add" && !empty($card_id)) {
		$id_val = "value='$card_id'";
	}

    $HD_Form->AddEditPopup(
        "card_id",
        gettext("CUSTOMER ID"),
        "A2B_entity_card.php?popup_select=1&",
        gettext("Select the Customer ID"),
        gettext("Insert the Customer ID"),
        $id_val
    );

	if ($form_action=="ask-add") {
		$begin_date = date("Y");
		$end_date = date("-m-d H:i:s");
		$comp_date=$begin_date.$end_date;
		$comp_date="value='$comp_date'";
	}

    $HD_Form->AddEditElement(
        gettext("REFILL DATE"),
        "date",
        "",
        "size=40 maxlength=40 $comp_date",
        10,
        gettext("Insert the current date")
    );

    $HD_Form->AddEditElement(
        gettext("REFILL AMOUNT"),
        "credit",
        gettext("Enter the amount in the currency base : ") . BASE_CURRENCY,
        "size=60 maxlength=10",
        12,
        gettext("Insert the credit amount")
    );

    $HD_Form->AddEditTextarea(
        "description",
        gettext("DESCRIPTION"),
        "cols='50' rows='4'",
        gettext("Insert the description")
    );

    $HD_Form->AddEditSelect(
        "refill_type",
        $list_refill_type,
        gettext("REFILL TYPE"),
        gettext("Define type for payment and refill,if created.")
    );

	if ($form_action!="ask-edit" && $form_action!="edit") {
        $HD_Form->AddEditRadio(
            "added_invoice",
            [[gettext("Yes"), "1"], [gettext("No"), "0"]],
            gettext("CREATE ASSOCIATE INVOICE"),
            "1",
            "",
            gettext("Choose if you want to create an invoice with this refill account")
        );
	}


	$HD_Form -> FieldEditElement ('card_id, date, credit, description, refill_type, added_invoice');



	

	$HD_Form -> FG_INTRO_TEXT_ADITION = '';
	

		
	$HD_Form -> FG_ADDITIONAL_FUNCTION_AFTER_ADD = 'processing_refill_add';

	$HD_Form -> FG_LOCATION_AFTER_ADD = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?atmenu=document&stitle=Document&wh=AC&id=";
	$HD_Form -> FG_LOCATION_AFTER_EDIT = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?atmenu=document&stitle=Document&wh=AC&id=";
	$HD_Form -> FG_LOCATION_AFTER_DELETE = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?atmenu=document&stitle=Document&wh=AC&id=";

}

