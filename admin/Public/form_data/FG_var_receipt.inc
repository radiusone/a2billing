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


getpost_ifset(array('id', 'id_card', 'date','title','description'));


if (!isset($form_action) || $form_action=="list") {
	$list_query = "cc_receipt LEFT JOIN cc_card ON cc_card.id=id_card";
	$list_query .= " LEFT JOIN ( SELECT st1.id_receipt, SUM(st1.price) as total_price FROM cc_receipt_item AS st1 GROUP BY st1.id_receipt) as items ON items.id_receipt = cc_receipt.id ";
	$HD_Form = new FormHandler($list_query, "Receipt");
} else {
	$HD_Form = new FormHandler("cc_receipt", "Receipt");
}

$HD_Form -> FG_QUERY_PRIMARY_KEY = 'cc_receipt.id';
$HD_Form->no_debug();
$HD_Form -> FG_TABLE_DEFAULT_SENS = "DESC";

$HD_Form -> FG_FK_DELETE_ALLOWED = true;
$HD_Form -> FG_FK_DELETE_CONFIRM = true;
$HD_Form -> FG_FK_WARNONLY = true;
$HD_Form -> FG_FK_TABLENAMES = array("cc_receipt_item");
$HD_Form -> FG_FK_EDITION_CLAUSE = array(" id_receipt ");

$HD_Form -> FG_ENABLE_INFO_BUTTON = true;
$HD_Form -> FG_INFO_BUTTON_LINK = "A2B_receipt_view.php?id=";

$HD_Form -> FG_OTHER_BUTTON1_LINK = "A2B_receipt_edit.php?id=";
$HD_Form -> FG_OTHER_BUTTON1_IMG = Images_Path."/page_white_gear.png";
$HD_Form -> FG_OTHER_BUTTON1 = true;
$HD_Form -> FG_OTHER_BUTTON1_CONDITION = '|col4| == "OPEN"';
$HD_Form -> FG_OTHER_BUTTON1_ALT = gettext("Edit receipt items");
$HD_Form -> FG_OTHER_BUTTON2_LINK = "javascript:;";
$HD_Form -> FG_OTHER_BUTTON2_IMG = Images_Path."/lock.png";
$HD_Form -> FG_OTHER_BUTTON2 = true;
$HD_Form -> FG_OTHER_BUTTON2_HTML_CLASS = "lock";
$HD_Form -> FG_OTHER_BUTTON2_HTML_ID = "|col0|";
$HD_Form -> FG_OTHER_BUTTON2_CONDITION = '|col4| == "OPEN"';
$HD_Form -> FG_OTHER_BUTTON2_ALT = gettext("Close this receipt");
$HD_Form -> FG_EDIT_BUTTON_CONDITION = '|col4| == "OPEN"';

$HD_Form ->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form ->FG_LIST_ADDING_BUTTON_LINK1 = "A2B_entity_receipt.php?form_action=ask-add&section=".$_SESSION["menu_section"];
$HD_Form ->FG_LIST_ADDING_BUTTON_ALT1 = $HD_Form ->FG_LIST_ADDING_BUTTON_MSG1 = gettext("Add Receipt");
$HD_Form ->FG_LIST_ADDING_BUTTON_IMG1 = Images_Path ."/money.png" ;


$status_list = getInvoiceStatusList();

$filter_amount_with_vat ='total_price';
$list_refill_type=getRefillType_List();
$HD_Form -> AddViewElement(gettext("ID"), "cc_receipt.id");
$HD_Form -> AddViewElement(gettext("ACCOUNT"), "username", true, "30", "display_customer_link");
$HD_Form -> AddViewElement(gettext("DATE"), "date", true, "30", "display_dateformat");
$HD_Form -> AddViewElement(gettext("TITLE"), "title");
$HD_Form -> AddViewElement(gettext("STATUS"), "status", true, "", null, "list", $status_list);
$HD_Form -> AddViewElement(gettext("AMOUNT INCL VAT"), "total_price", true, "", "display_2bill");
$HD_Form -> FieldViewElement ("cc_receipt.id, username, cc_receipt.date,title,cc_receipt.status,$filter_amount_with_vat");

$HD_Form -> FG_ENABLE_ADD_BUTTON = true;
$HD_Form -> FG_ENABLE_EDIT_BUTTON = true;
$HD_Form -> FG_ENABLE_DELETE_BUTTON = true;

$HD_Form -> AddEditElement (gettext("ID CUSTOMER"),
			   "id_card",
			   "$value", "POPUPVALUE", "size=30 maxlength=50",
			   "4",	gettext("Insert the Customer ID"),
			    "" , "", "", "", "",
				"A2B_entity_card.php?popup_select=1&", ", 'CardNumberSelection','width=550,height=330,top=20,left=100,scrollbars=1'" ,
				gettext("Select the Customer ID"));

if ($form_action=="ask-add"){
	$begin_date = date("Y");
	$end_date = date("-m-d H:i:s");
	$comp_date=$begin_date.$end_date;
	$comp_date="value='$comp_date'";
}

$HD_Form -> AddEditElement (gettext("INVOICE DATE"),
			   "date",
			   '$value',
			   "INPUT",
			   "size=40 READONLY maxlength=40  $comp_date",
			   "10",
			   gettext("Insert the current date"),
			   "" , "", "",	"", "", "", "", "" );


$HD_Form -> AddEditElement ( gettext("TITLE"),
				"title",	'$value',
				"INPUT",
				"size=30 maxlength=50",
				"0",
				gettext("Insert the title of invoice, if you need one"),
				"" , "", "",	"", "", "", "", "" );


$HD_Form -> AddEditElement (gettext("DESCRIPTION"),
				"description",
				'',
				"TEXTAREA",
				"cols=50 rows=4",
				"",
				gettext("Insert the description"),
				"" , "", "", "", "" , "", "", "");


$HD_Form -> FieldEditElement ('id_card, date,title, description');



$HD_Form -> FG_FILTER_ENABLE = true;
$HD_Form -> FG_FILTER_COLUMN = 'cardnumber';
$HD_Form -> FG_FILTER_LABEL = 'Account number';

if (isset($filterprefix)  &&  (strlen($filterprefix)>0)) {
	if (strlen($HD_Form -> FG_QUERY_WHERE_CLAUSE)>0) $HD_Form -> FG_QUERY_WHERE_CLAUSE.=" AND ";
	$HD_Form -> FG_QUERY_WHERE_CLAUSE.="username like '$filterprefix%'";
}




$HD_Form -> FG_INTRO_TEXT_ADITION = '';


$HD_Form -> FG_BUTTON_EDITION_BOTTOM_TEXT = $HD_Form -> FG_BUTTON_ADITION_BOTTOM_TEXT = gettext("Click 'Confirm Data' to continue.");


$HD_Form -> FG_LOCATION_AFTER_ADD = "A2B_receipt_edit.php?id=";
$HD_Form -> FG_LOCATION_AFTER_EDIT = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?atmenu=document&stitle=Document&wh=AC&id=";
$HD_Form -> FG_LOCATION_AFTER_DELETE = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?atmenu=document&stitle=Document&wh=AC&id=";



