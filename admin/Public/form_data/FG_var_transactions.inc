<?php

use A2billing\Admin;
use A2billing\Forms\FormHandler;
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

Admin::checkPageAccess(Admin::ACX_ADMINISTRATOR);

getpost_ifset(["trans_type"]);
/**
 * @var string|null $trans_type
 */
$trans_type ??= "user";

$HD_Form = new FormHandler(
    $trans_type === "agent" ? "cc_payments_agent" : "cc_payments",
    _("Transactions"),
    $trans_type === "agent" ? "cc_payments_agent.id" : "cc_payments.id",
    $trans_type === "agent" ? ["cc_agent" => ["agent_id", "cc_agent.id"]] : ["cc_card" => ["customers_id", "cc_card.id"]]
);

$HD_Form->FG_QUERY_ORDERBY_COLUMNS = ["date_purchased"];
$HD_Form->FG_TABLE_DEFAULT_SENS = "DESC";

$HD_Form->AddListValue(_("ID"), "id");
$HD_Form->AddListValue(_("Date"), "date_purchased");
if ($trans_type === "agent") {
    $HD_Form->AddListValue(_("Login"), "cc_agent.login");
} else {
    $HD_Form->AddListValue(_("Card number"), "cc_card.username");
}
$HD_Form->AddListValue(_("Name"), "CONCAT(firstname, ' ', lastname)");
$HD_Form->AddListValue(_("Item name"), "item_name");
$HD_Form->AddListValue(_("Method"), "payment_method");
$HD_Form->AddListValue(_("CC number"), "cc_number");
$HD_Form->AddListSqlMapping(_("Status"), "orders_status", new Table("cc_payments_status", ["id", "status_name"]));
$HD_Form->AddListValue(_("Amount"), "orders_amount", "display_money");
$HD_Form->AddListValue(abbr(_("Cur"), _("Currency")), "currency");
$HD_Form->AddListValue(_("Value"), "currency_value", "display_2bill");

$HD_Form->FieldViewElement([
    "id",
    "date_purchased",
    $trans_type === "agent" ? "cc_agent.login" : "cc_card.username",
    "CONCAT(firstname, ' ', lastname)",
    "item_name",
    "payment_method",
    "cc_number",
    "orders_status",
    "orders_amount",
    "currency",
    "currency_value",
]);

$HD_Form->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form->FG_LIST_ADDING_BUTTON_LINK1 = "?trans_type=" . ($trans_type === "agent" ? "user" : "agent"); 
$HD_Form->FG_LIST_ADDING_BUTTON_MSG1 = $trans_type === "agent" ? _("Show user transactions") : _("Show agent transactions");

$HD_Form->FG_ENABLE_ADD_BUTTON = false;
$HD_Form->FG_ENABLE_EDIT_BUTTON = false;
$HD_Form->FG_ENABLE_DELETE_BUTTON = true;

$HD_Form->FG_FILTER_ENABLE = true;
$HD_Form->FG_FILTER_COLUMN = "id";
$HD_Form->FG_FILTER_LABEL = _("Transaction ID");

$HD_Form->FG_LOCATION_AFTER_DELETE = "?id=";
