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

getpost_ifset(["popup_select"]);
$popup_select ??= "";

Admin::checkPageAccess(Admin::ACX_ADMINISTRATOR);

$form_action ??= "list";
$col6_open = sprintf("'|col6|' === '%s'", _("OPEN"));
$col6_closed = sprintf("'|col6|' === '%s'", _("CLOSE"));

if ($form_action === "list") {
	$list_query = "cc_invoice LEFT JOIN cc_card ON cc_card.id=id_card ";
	$list_query .= "LEFT JOIN (SELECT st1.id_invoice, TRUNCATE(SUM(st1.price*(1+(st1.vat/100))),2) as total_price " .
	               "FROM cc_invoice_item AS st1 GROUP BY st1.id_invoice ) as items ON items.id_invoice = cc_invoice.id ";
	$HD_Form = new FormHandler($list_query, "Invoice", 'cc_invoice.id');
} else {
	$HD_Form = new FormHandler("cc_invoice", "Invoice");
}

$HD_Form->FG_TABLE_DEFAULT_SENS = "DESC";

$HD_Form->FG_FK_DELETE_ALLOWED = true;
$HD_Form->FG_FK_DELETE_CONFIRM = true;
$HD_Form->FG_FK_WARNONLY = true;
$HD_Form->FG_FK_TABLENAMES = ["cc_invoice_item"];
$HD_Form->FG_FK_EDITION_CLAUSE = [" id_invoice "];

$HD_Form->FG_ENABLE_INFO_BUTTON = true;
$HD_Form->FG_INFO_BUTTON_LINK = "A2B_invoice_view.php?id=";

$HD_Form->FG_ENABLE_EDIT_BUTTON = true;
$HD_Form->FG_EDIT_BUTTON_CONDITION = $col6_open;

$HD_Form->FG_ENABLE_DELETE_BUTTON = true;
$HD_Form->FG_DELETE_BUTTON_CONDITION = $col6_open;

$HD_Form->FG_ENABLE_ADD_BUTTON = true;

$HD_Form->AddListActionButton(
    "A2B_invoice_edit.php?id=",
    _("Edit invoice items"),
    get_image_path("page_white_gear.png")
);

$HD_Form->AddListActionButton(
    "",
    _("Close this invoice"),
    get_image_path("lock.png"),
    "lock",
    "cc_invoice_status",
    _("OPEN")
);

$HD_Form->AddListActionButton(
    "A2B_invoice_manage_payment.php?id=",
    _("Manage payments"),
    get_image_path("money.png"),
    "",
    "cc_invoice_status",
    _("CLOSE")
);

$HD_Form->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form->FG_LIST_ADDING_BUTTON_LINK1 = "A2B_entity_invoice.php?form_action=ask-add";
$HD_Form->FG_LIST_ADDING_BUTTON_ALT1 = $HD_Form->FG_LIST_ADDING_BUTTON_MSG1 = _("Add Invoice");
$HD_Form->FG_LIST_ADDING_BUTTON_IMG1 = get_image_path("money.png") ;

$status_list = getInvoiceStatusList();
$paid_status_list = getInvoicePaidStatusList();
$paid_status_list_r = array_map("array_reverse", $paid_status_list);
$typepaid_list_r = array_map("array_reverse", getPaidTypeList());
$invoiceday_list = getInvoiceDay_List();

$filter_amount_with_vat = "total_price";
$HD_Form->AddViewElement(_("ID"), "cc_invoice.id");
$HD_Form->AddViewElement(_("REFERENCE"), "cc_invoice.reference");
$HD_Form->AddViewElement(_("ACCOUNT"), "username", true, 30, "display_customer_link");
$HD_Form->AddViewElement(_("DATE"), "date", true, 30);
$HD_Form->AddViewElement(_("TITLE"), "title");
$HD_Form->AddViewElement(_("PAID STATUS"), "status", true, 0, "", "list", $paid_status_list);
$HD_Form->AddViewElement(_("STATUS"), "cc_invoice_status", true, 0, "", "list", $status_list);
$HD_Form->AddViewElement(_("AMOUNT INCL VAT"), "total_price", true, 0, "display_2bill");
$HD_Form->FieldViewElement("cc_invoice.id, cc_invoice.reference, username, cc_invoice.date,title, cc_invoice.paid_status, cc_invoice.status as cc_invoice_status,$filter_amount_with_vat");

if (!$popup_select) {
    $HD_Form->search_form_enabled = true;
    $HD_Form->search_delete_enabled = false;

    $HD_Form->search_form_title = _('Define specific criteria to search for invoice created.');
    $HD_Form->search_date_enabled = true;
    $HD_Form->search_date_text = _('CREATION DATE');
    $HD_Form->search_date_column = "`date`";

    $HD_Form->AddSearchTextInput(_("FIRSTNAME"), 'firstname', 'firstnametype');
    $HD_Form->AddSearchTextInput(_("LASTNAME"), 'lastname', 'lastnametype');
    $HD_Form->AddSearchTextInput(_("REFERENCE"), 'reference', 'referencetype');
    $HD_Form->AddSearchTextInput(_("TITLE"), 'title', 'titletype');

    $HD_Form->AddSearchComparisonInput(_("AMOUNT INCL VAT"), 'total_price1', 'total_price1type', 'total_price2', 'total_price2type', 'total_price');

    $HD_Form->AddSearchSelectInput(_("PAYMENT TYPE"), "typepaid", $typepaid_list_r);
    $HD_Form->AddSearchSqlSelectInput(_("CALL PLAN"), "cc_tariffgroup", "id, tariffgroupname", "", "tariffgroupname", "ASC", "tariff");
    $HD_Form->AddSearchSelectInput(_("INVOICE DAY"), "invoiceday", $invoiceday_list);
    $HD_Form->AddSearchSelectInput(_("PAID STATUS"), "paid_status", $paid_status_list_r);
}

$HD_Form->AddEditPopup(
    _("CUSTOMER ID"),
    "id_card",
    "A2B_entity_card.php?popup_select=1&",
    _("Select the Customer ID"),
    "",
    4,
    _("Insert the Customer ID")
);

$comp_date = $form_action === "ask-add" ? sprintf("value='%s'", (new DateTime())->format("Y-m-d H:i:s")) : "";

$HD_Form->AddEditElement(
    _("INVOICE DATE"),
    "date",
    "",
    "size=40 READONLY maxlength=40 $comp_date",
    10,
    _("Insert the current date")
);


$HD_Form->AddEditElement(
    _("TITLE"),
    "title",
    "",
    "size=30 maxlength=50",
    0,
    _("Insert the title of invoice, if you need one")
);

$HD_Form->AddEditTextarea(
    _("DESCRIPTION"),
    "description",
    "",
    "cols='50' rows='4'",
    null,
    _("Insert the description")
);

$HD_Form->FG_FILTER_ENABLE = true;
$HD_Form->FG_FILTER_COLUMN = 'username';
$HD_Form->FG_FILTER_LABEL = _("Account Number");

$HD_Form->FG_INTRO_TEXT_ADITION = '';

$HD_Form->FG_ADDITIONAL_FUNCTION_AFTER_ADD = 'create_invoice_reference';

$HD_Form->FG_LOCATION_AFTER_ADD = "A2B_invoice_edit.php?id=";
$HD_Form->FG_LOCATION_AFTER_EDIT = "?id=";
$HD_Form->FG_LOCATION_AFTER_DELETE = "?id=";
