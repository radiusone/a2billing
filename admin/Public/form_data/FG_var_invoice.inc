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
	$list_query = "cc_invoice LEFT JOIN cc_card ON cc_card.id=id_card ";
	$list_query .= "LEFT JOIN (SELECT st1.id_invoice, TRUNCATE(SUM(st1.price*(1+(st1.vat/100))),2) as total_price " .
	               "FROM cc_invoice_item AS st1 GROUP BY st1.id_invoice ) as items ON items.id_invoice = cc_invoice.id ";
	$HD_Form = new FormHandler($list_query, "Invoice");
} else {
	$HD_Form = new FormHandler("cc_invoice", "Invoice");
}

$HD_Form -> FG_QUERY_PRIMARY_KEY = 'cc_invoice.id';
$HD_Form->no_debug();
$HD_Form -> FG_TABLE_DEFAULT_SENS = "DESC";

$HD_Form -> FG_FK_DELETE_ALLOWED = true;
$HD_Form -> FG_FK_DELETE_CONFIRM = true;
$HD_Form -> FG_FK_WARNONLY = true;
$HD_Form -> FG_FK_TABLENAMES = array("cc_invoice_item");
$HD_Form -> FG_FK_EDITION_CLAUSE = array(" id_invoice ");

$HD_Form -> FG_ENABLE_INFO_BUTTON = true;
$HD_Form -> FG_INFO_BUTTON_LINK = "A2B_invoice_view.php?id=";

$HD_Form -> FG_OTHER_BUTTON1_LINK = "A2B_invoice_edit.php?id=";
$HD_Form -> FG_OTHER_BUTTON1_IMG = Images_Path."/page_white_gear.png";
$HD_Form -> FG_OTHER_BUTTON1 = true;
$HD_Form -> FG_OTHER_BUTTON1_CONDITION = '|col6| == "'.gettext('OPEN').'"';
$HD_Form -> FG_OTHER_BUTTON1_ALT = gettext("Edit invoice items");
$HD_Form -> FG_OTHER_BUTTON2_LINK = "javascript:;";
$HD_Form -> FG_OTHER_BUTTON2_IMG = Images_Path."/lock.png";
$HD_Form -> FG_OTHER_BUTTON2 = true;
$HD_Form -> FG_OTHER_BUTTON2_HTML_CLASS = "lock";
$HD_Form -> FG_OTHER_BUTTON2_HTML_ID = "|col0|";
$HD_Form -> FG_OTHER_BUTTON2_CONDITION = '|col6| == "'.gettext('OPEN').'"';
$HD_Form -> FG_OTHER_BUTTON2_ALT = gettext("Close this invoice");
$HD_Form -> FG_OTHER_BUTTON3_LINK = "A2B_invoice_manage_payment.php?id=";
$HD_Form -> FG_OTHER_BUTTON3_IMG = Images_Path."/money.png";
$HD_Form -> FG_OTHER_BUTTON3 = true;
$HD_Form -> FG_OTHER_BUTTON3_CONDITION = '|col6| == "'.gettext('CLOSE').'"';
$HD_Form -> FG_OTHER_BUTTON3_ALT = gettext("Manage payments");
$HD_Form -> FG_EDIT_BUTTON_CONDITION = '|col6| == "'.gettext('OPEN').'"';
$HD_Form -> FG_DELETE_BUTTON_CONDITION = '|col6| == "'.gettext('OPEN').'"';
$HD_Form ->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form ->FG_LIST_ADDING_BUTTON_LINK1 = "A2B_entity_invoice.php?form_action=ask-add&section=".$_SESSION["menu_section"];
$HD_Form ->FG_LIST_ADDING_BUTTON_ALT1 = $HD_Form ->FG_LIST_ADDING_BUTTON_MSG1 = gettext("Add Invoice");
$HD_Form ->FG_LIST_ADDING_BUTTON_IMG1 = Images_Path ."/money.png" ;


$status_list = getInvoiceStatusList();
$status_list_r = array_map("array_reverse", $status_list);
$paid_status_list = getInvoicePaidStatusList();
$paid_status_list_r = array_map("array_reverse", $paid_status_list);
$typepaid_list_r = array_map("array_reverse", getPaidTypeList());
$invoiceday_list = getInvoiceDay_List();

//$filter_amount_without_vat ='SUM(cc_invoice_item.price)';
// $filter_amount_with_vat ='SUM(cc_invoice_item.price*(1+(cc_invoice_item.vat/100)))';
$filter_amount_with_vat = "total_price";
$list_refill_type=getRefillType_List();
$HD_Form -> AddViewElement(gettext("ID"), "cc_invoice.id");
$HD_Form -> AddViewElement(gettext("REFERENCE"), "cc_invoice.reference");
$HD_Form -> AddViewElement(gettext("ACCOUNT"), "username", true, 30, "display_customer_link");
$HD_Form -> AddViewElement(gettext("DATE"), "date", true, 30, "display_dateformat");
$HD_Form -> AddViewElement(gettext("TITLE"), "title");
$HD_Form -> AddViewElement(gettext("PAID STATUS"), "status", true, 0, "", "list", $paid_status_list);
$HD_Form -> AddViewElement(gettext("STATUS"), "cc_invoice_status", true, 0, "", "list", $status_list);
$HD_Form -> AddViewElement(gettext("AMOUNT INCL VAT"), "total_price", true, 0, "display_2bill");
$HD_Form -> FieldViewElement ("cc_invoice.id, cc_invoice.reference, username, cc_invoice.date,title, cc_invoice.paid_status, cc_invoice.status as cc_invoice_status,$filter_amount_with_vat");

if (!($popup_select>=1)) $HD_Form -> search_form_enabled = true;
if (!($popup_select>=1)) $HD_Form -> search_delete_enabled = false;

$HD_Form -> search_form_title = gettext('Define specific criteria to search for invoice created.');
$HD_Form -> search_date_enabled = true;
$HD_Form -> search_date_text = gettext('CREATION DATE');

$HD_Form -> AddSearchTextInput(gettext("FIRSTNAME"),'firstname','firstnametype');
$HD_Form -> AddSearchTextInput(gettext("LASTNAME"),'lastname','lastnametype');
$HD_Form -> AddSearchTextInput(gettext("REFERENCE"), 'reference','referencetype');
$HD_Form -> AddSearchTextInput(gettext("TITLE"), 'title','titletype');

$HD_Form -> AddSearchComparisonInput(gettext("AMOUNT INCL VAT"),'total_price1','total_price1type','total_price2','total_price2type','total_price');

$HD_Form -> AddSearchSelectInput(gettext("PAYMENT TYPE"), "typepaid", $typepaid_list_r);
$HD_Form -> AddSearchSqlSelectInput(gettext("CALL PLAN"), "cc_tariffgroup", "id, tariffgroupname", "", "tariffgroupname", "ASC", "tariff");
$HD_Form -> AddSearchSelectInput(gettext("INVOICE DAY"), "invoiceday", $invoiceday_list);
$HD_Form -> AddSearchSelectInput(gettext("PAID STATUS"), "paid_status", $paid_status_list_r);


$HD_Form -> FG_ENABLE_EDIT_BUTTON = true;
$HD_Form -> FG_ENABLE_DELETE_BUTTON = true;
$HD_Form -> FG_ENABLE_ADD_BUTTON = true;

$HD_Form->AddEditPopup(
    "id_card",
    gettext("CUSTOMER ID"),
    "A2B_entity_card.php?popup_select=1&",
    gettext("Select the Customer ID"),
    gettext("Insert the Customer ID")
);

if ($form_action=="ask-add"){
	$begin_date = date("Y");
	$end_date = date("-m-d H:i:s");
	$comp_date=$begin_date.$end_date;
	$comp_date="value='$comp_date'";
}

$HD_Form->AddEditElement(
    gettext("INVOICE DATE"),
    "date",
    "",
    "size=40 READONLY maxlength=40  $comp_date",
    10,
    gettext("Insert the current date")
);


$HD_Form->AddEditElement(
    gettext("TITLE"),
    "title",
    "",
    "size=30 maxlength=50",
    0,
    gettext("Insert the title of invoice, if you need one")
);

$HD_Form->AddEditTextarea(
    "description",
    gettext("DESCRIPTION"),
    "cols='50' rows='4'",
    gettext("Insert the description")
);

$HD_Form -> FieldEditElement ('id_card, date,title, description');



$HD_Form -> FG_FILTER_ENABLE = true;
$HD_Form -> FG_FILTER_COLUMN = 'cardnumber';
$HD_Form -> FG_FILTER_LABEL = 'Account number';

if (isset($filterprefix)  &&  (strlen($filterprefix)>0)){
	if (strlen($HD_Form -> FG_QUERY_WHERE_CLAUSE)>0) $HD_Form -> FG_QUERY_WHERE_CLAUSE.=" AND ";
	$HD_Form -> FG_QUERY_WHERE_CLAUSE.="username like '$filterprefix%'";
}





$HD_Form -> FG_INTRO_TEXT_ADITION = '';



$HD_Form -> FG_ADDITIONAL_FUNCTION_AFTER_ADD = 'create_invoice_reference';

$HD_Form -> FG_LOCATION_AFTER_ADD = "A2B_invoice_edit.php?id=";
$HD_Form -> FG_LOCATION_AFTER_EDIT = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?atmenu=document&stitle=Document&wh=AC&id=";
$HD_Form -> FG_LOCATION_AFTER_DELETE = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?atmenu=document&stitle=Document&wh=AC&id=";



