<?php

use A2billing\Admin;
use A2billing\Customer;
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
/**
 * @var string $form_action
 */
Admin::checkPageAccess(Admin::ACX_ADMINISTRATOR);

$form_action ??= "list";
$wantinclude ??= false;

getpost_ifset(["id", "id_did"]);
/**
 * @var numeric-string|null $id_did
 */

$HD_Form_c = new FormHandler(
    "cc_charge",
    _("Charge"),
    "cc_charge.id",
    ["cc_card" => ["cc_charge.id_cc_card", "cc_card.id"]]
);

if ($wantinclude && isset($order)) {
    $HD_Form_c->list_query_order_columns = [$order];
} else {
    $HD_Form_c->list_query_order_columns = ["id_cc_card"];
}
$HD_Form_c->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form_c->FG_LIST_ADDING_BUTTON_LINK1 = "A2B_entity_charge.php?form_action=ask-add";
$HD_Form_c->FG_LIST_ADDING_BUTTON_IMG1 = get_image_path("add.png") ;

$HD_Form_c->help_text = create_help(
    _("Extra charges allow the billing of one-off or re-occurring monthly charges. These may be used as setup or service charges, etc...")
    . _("Charges will appear to the user with the description you attach. Each charge that you create for a user will decrement his account.")
);

// -- chargetype : 1 - connection charge for DID setup, 2 - Montly charge for DID use, 3 - Subscription fee, 4 - Extra Charge, etc...
$chargetype_list = [
    1 => _("Connection charge for DID setup"),
    2 => _("Monthly Charge for DID use"),
    3 => _("Subscription fee"),
    4 => _("Extra charge"),
];

$HD_Form_c->AddListSqlMapping(_("Account holder"), "id_cc_card", new Table("cc_card", ["id", "CONCAT(lastname, ' ', firstname)"]));
$HD_Form_c->AddListValue(_("Account number"), "id_cc_card", [Customer::class, "getUsername"]);
$HD_Form_c->AddListValue(_("Date"), "cc_charge.creationdate");
$HD_Form_c->AddListValue(_("Amount"), "amount", "get_money");
$HD_Form_c->AddListMapping(_("Charge type"), "chargetype", $chargetype_list);
$HD_Form_c->AddListValue(_("Description"), "description");

$HD_Form_c->FG_ENABLE_EDIT_BUTTON = true;
$HD_Form_c->FG_ENABLE_DELETE_BUTTON = true;
$HD_Form_c->FG_ENABLE_ADD_BUTTON = true;

$HD_Form_c->AddEditPopup(
    _("ID CARD"),
    "id_cc_card",
    "A2B_entity_card.php?popup_select=1&",
    _("Set the Customer ID"),
    [],
    [Validator::class, "numeric"],
    _("Insert the Customer ID")
);

$amt_attr = ["maxlength" => 20];
if ($form_action === "ask-add" && !empty($id_did)) {
    $row = (new Table("cc_did", "fixrate"))->getRow(DbConnect(), ["id" => $id_did]);
    $amt_attr["value"] = $row["fixrate"];
}
$HD_Form_c->AddEditElement(
    _("AMOUNT"),
    "amount",
    "",
    $amt_attr,
    [Validator::class, "number"],
    _("Insert the amount")
);

$HD_Form_c->AddEditSelect(
    _("CHARGE TYPE"),
    "chargetype",
    $chargetype_list
);

$HD_Form_c->AddEditTextarea(
    _("DESCRIPTION"),
    "description",
    "",
    ["rows" => 4],
    null,
    _("Insert the description")
);

$HD_Form_c->FG_FILTER_ENABLE = true;
$HD_Form_c->FG_FILTER_COLUMN = "username";
$HD_Form_c->FG_FILTER_LABEL = _("Account number");

$HD_Form_c->FG_INTRO_TEXT_ADITION = "";

$HD_Form_c->FG_LOCATION_AFTER_ADD = "?id=";
$HD_Form_c->FG_LOCATION_AFTER_EDIT = "?id=";
$HD_Form_c->FG_LOCATION_AFTER_DELETE = "?id=";
