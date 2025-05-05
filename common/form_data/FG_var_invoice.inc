<?php

use A2billing\Admin;
use A2billing\Customer;
use A2billing\Forms\FormBO;
use A2billing\Forms\FormHandler;
use A2billing\Forms\Validator;
use A2billing\Table;

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
$join_key = "(SELECT id_invoice, SUM(ROUND(price * (1 + vat / 100), 2)) AS total_price FROM cc_invoice_item GROUP BY id_invoice) AS items";

$HD_Form = new FormHandler(
    "cc_invoice",
    "Invoice",
    "cc_invoice.id",
    $form_action !== "list"
        ? []
        : [$join_key => ["cc_invoice.id", "items.id_invoice"]]
);

$HD_Form->list_query_order_direction = "DESC";

$HD_Form->FG_FK_DELETE_ALLOWED = true;
$HD_Form->FG_FK_DELETE_CONFIRM = true;
$HD_Form->FG_FK_WARNONLY = true;
$HD_Form->FG_FK_TABLENAMES = ["cc_invoice_item"];
$HD_Form->FG_FK_EDITION_CLAUSE = ["id_invoice"];

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
    "A2B_entity_invoice.php?action=lock&id=",
    _("Close this invoice"),
    get_image_path("lock.png"),
    "",
    "lock",
    "status",
    0 // open
);

$HD_Form->AddListActionButton(
    "A2B_invoice_manage_payment.php?id=",
    _("Manage payments"),
    get_image_path("money.png"),
    "",
    "",
    "status",
    1 // closed
);

$HD_Form->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form->FG_LIST_ADDING_BUTTON_IMG1 = get_image_path("money.png") ;

$HD_Form->help_text = create_help(
    _("Invoice history - The section below allows you to see and create invoices against a customer. Only the closed invoice can be seen on the customer interface")
);

$status_list = getInvoiceStatusList();
$paid_status_list = getInvoicePaidStatusList();
$typepaid_list = getPaidTypeList();
$invoiceday_list = getInvoiceDay_List();

$HD_Form->AddListValue(_("ID"), "cc_invoice.id");
$HD_Form->AddListValue(_("REFERENCE"), "reference");
$HD_Form->AddListValue(_("ACCOUNT"), "id_card", [Customer::class, "getUsername"]);
$HD_Form->AddListValue(_("DATE"), "cc_invoice.date");
$HD_Form->AddListValue(_("TITLE"), "title");
$HD_Form->AddListMapping(_("PAID STATUS"), "paid_status", $paid_status_list);
$HD_Form->AddListMapping(_("STATUS"), "status", $status_list);
$HD_Form->AddListValue(_("AMOUNT INCL VAT"), "total_price", "get_money");

if (!$popup_select) {
    $HD_Form->search_form_enabled = true;
    $HD_Form->search_delete_enabled = false;

    $HD_Form->search_form_title = _('Define specific criteria to search for invoice created.');

    $HD_Form->AddSearchDateInput(_("CREATION DATE"), "date");

    $HD_Form->AddSearchTextInput(_("FIRSTNAME"), 'firstname');
    $HD_Form->AddSearchTextInput(_("LASTNAME"), 'lastname');
    $HD_Form->AddSearchTextInput(_("REFERENCE"), 'reference');
    $HD_Form->AddSearchTextInput(_("TITLE"), 'title');

    $HD_Form->AddSearchComparisonInput(_("AMOUNT INCL VAT"), 'total_price1', 'total_price2', 'total_price');

    $HD_Form->AddSearchSelectInput(_("PAYMENT TYPE"), "typepaid", $typepaid_list);
    $HD_Form->AddSearchSqlSelectInput(_("CALL PLAN"), "tariff", new Table("cc_tariffgroup", ["id", "tariffgroupname"]), "tariffgroupname");
    $HD_Form->AddSearchSelectInput(_("INVOICE DAY"), "invoiceday", $invoiceday_list);
    $HD_Form->AddSearchSelectInput(_("PAID STATUS"), "paid_status", $paid_status_list);
}

$HD_Form->AddEditPopup(
    _("CUSTOMER ID"),
    "id_card",
    "A2B_entity_card.php?popup_select=1&",
    _("Select the Customer ID"),
    [],
    [Validator::class, "numeric"],
    _("Insert the Customer ID")
);

$date_attr = ["readonly" => "readonly", "maxlength" => 40];
if ($form_action === "ask-add") {
    $date_attr["value"] = (new DateTime())->format("Y-m-d H:i:s");
}
$HD_Form->AddEditElement(
    _("INVOICE DATE"),
    "date",
    "",
    $date_attr,
    [Validator::class, "dateTime"],
    _("Insert the current date")
);


$HD_Form->AddEditElement(
    _("TITLE"),
    "title",
    "",
    ["maxlength" => 50],
    [Validator::class, "min3Chars"],
    _("Insert the title of invoice, if you need one")
);

$HD_Form->AddEditTextarea(
    _("DESCRIPTION"),
    "description",
    "",
    ["rows" => 4],
    _("Insert the description")
);

$HD_Form->FG_FILTER_ENABLE = true;
$HD_Form->FG_FILTER_COLUMN = 'username';
$HD_Form->FG_FILTER_LABEL = _("Account Number");

$HD_Form->FG_INTRO_TEXT_ADITION = '';

$HD_Form->FG_ADDITIONAL_FUNCTION_AFTER_ADD = [FormBO::class, "create_invoice_reference"];

$HD_Form->FG_LOCATION_AFTER_ADD = "A2B_invoice_edit.php?id=";
$HD_Form->FG_LOCATION_AFTER_EDIT = "A2B_entity_invoice.php?form_action=list";
$HD_Form->FG_LOCATION_AFTER_DELETE = "A2B_entity_invoice.php?form_action=list";
