<?php

use A2billing\Admin;
use A2billing\Forms\FormHandler;
use A2billing\Forms\Validator;

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

$form_action ??= "list";
$HD_Form = new FormHandler(
    "cc_receipt",
    _("Receipt"),
    "cc_receipt.id",
    $form_action === "list"
        ? ["cc_receipt_item" => ["cc_receipt_item.id_receipt", "cc_receipt.id"]]
        : []
);

$HD_Form->FG_QUERY_ORDERBY_COLUMNS = ["date"];
$HD_Form->FG_TABLE_DEFAULT_SENS = "DESC";
$HD_Form->FG_QUERY_GROUPBY_COLUMNS = ["cc_receipt.id"];

$HD_Form->FG_FK_DELETE_ALLOWED = true;
$HD_Form->FG_FK_DELETE_CONFIRM = true;
$HD_Form->FG_FK_WARNONLY = true;
$HD_Form->FG_FK_TABLENAMES = ["cc_receipt_item"];
$HD_Form->FG_FK_EDITION_CLAUSE = ["id_receipt"];

$HD_Form->FG_ENABLE_INFO_BUTTON = true;
$HD_Form->FG_INFO_BUTTON_LINK = "A2B_receipt_view.php?id=";

$HD_Form->AddListActionButton(
    "A2B_receipt_edit.php?id=",
    _("Edit receipt items"),
    get_image_path("edit.png"),
    "",
    "status",
    0 // open
);
$HD_Form->AddListActionButton(
    "",
    _("Close this receipt"),
    get_image_path("lock.png"),
    "lock",
    "status",
    0 // open
);
$HD_Form->FG_EDIT_BUTTON_CONDITION = '"|col4|" == "OPEN"';

$HD_Form->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form->FG_LIST_ADDING_BUTTON_IMG1 = get_image_path("money.png") ;

$HD_Form->AddListValue(_("ID"), "cc_receipt.id");
$HD_Form->AddListValue(_("Account"), "id_card", "display_customer_id_link");
$HD_Form->AddListValue(_("Date"), "cc_receipt.date");
$HD_Form->AddListValue(_("Title"), "title");
$HD_Form->AddListMapping(_("Status"), "status", getInvoiceStatusList());
$HD_Form->AddListValue(_("Amount (incl VAT)"), "total_price", "display_money");
$HD_Form->FieldViewElement([
    "cc_receipt.id",
    "id_card",
    "cc_receipt.date",
    "title",
    "cc_receipt.status",
    "SUM(price) AS total_price",
]);

$HD_Form->FG_ENABLE_ADD_BUTTON = true;
$HD_Form->FG_ENABLE_EDIT_BUTTON = true;
$HD_Form->FG_ENABLE_DELETE_BUTTON = true;

$HD_Form->AddEditPopup(
    _("CUSTOMER ID"),
    "id_card",
    "A2B_entity_card.php?popup_select=1&",
    _("Select the Customer ID"),
    "",
    [Validator::class, "numeric"],
    _("Insert the Customer ID")
);

$comp_date = $form_action === "ask-add" ? date("Y-m-d H:i:s") : "";

$HD_Form->AddEditElement(
    _("INVOICE DATE"),
    "date",
    "",
    "size=40 READONLY maxlength=40  value='$comp_date'",
    10,
    _("Insert the current date")
);

$HD_Form->AddEditElement(
    _("TITLE"),
    "title",
    "",
    "size=30 maxlength=50",
    0, // [Validator::class, "min3Chars"]
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
$HD_Form->FG_FILTER_COLUMN = "username";
$HD_Form->FG_FILTER_LABEL = _("Account number");

$HD_Form->FG_INTRO_TEXT_ADITION = "";

$HD_Form->FG_LOCATION_AFTER_ADD = "A2B_receipt_edit.php?id=";
$HD_Form->FG_LOCATION_AFTER_EDIT = "?id=";
$HD_Form->FG_LOCATION_AFTER_DELETE = "?id=";
