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

Admin::checkPageAccess(Admin::ACX_ADMINISTRATOR);

$HD_Form = new FormHandler("cc_card_group", "Customer Group");

$HD_Form->FG_TABLE_DEFAULT_ORDER = "name";

$HD_Form->FG_FK_DELETE_ALLOWED = true;
$HD_Form->FG_FK_DELETE_CONFIRM = true;
$HD_Form->FG_FK_WARNONLY = true;
$HD_Form->FG_FK_TABLENAMES = ["cc_card"];
$HD_Form->FG_FK_EDITION_CLAUSE = ["id_group"];

$HD_Form->FG_FK_DELETE_MESSAGE = _("You have some cards using this Customers Group! Please comfirm that you really want to remove this Customers Group ? ");

$HD_Form->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form->FG_LIST_ADDING_BUTTON_IMG1 = get_image_path("group_add.png");

$HD_Form->help_text = create_help(
    _("This page shows a group list.")
    . _("The Group field is used for grouping customers for quick search, batch update and reporting.")
);

$HD_Form->AddListValue(_("Name"), "name");
$HD_Form->AddListValue(_("Description"), "description", "substr", ["%1", "0", "150"]);

$HD_Form->FG_ENABLE_ADD_BUTTON = true;
$HD_Form->FG_ENABLE_EDIT_BUTTON = true;
$HD_Form->FG_ENABLE_DELETE_BUTTON = true;
$HD_Form->FG_DELETION_FORBIDDEN_ID = [1];

$right_list = [
    Customer::ACX_PASSWORD => _("PASSWORD"),
    Customer::ACX_SIP_IAX => _("SIP IAX"),
    Customer::ACX_CALL_HISTORY => _("CALL HISTORY"),
    Customer::ACX_PAYMENT_HISTORY => _("PAYMENT HISTORY"),
    Customer::ACX_VOUCHER => _("VOUCHER"),
    Customer::ACX_INVOICES => _("INVOICES"),
    Customer::ACX_DID => _("DID"),
    Customer::ACX_SPEED_DIAL => _("SPEED DIAL"),
    Customer::ACX_RATECARD => _("RATECARD"),
    Customer::ACX_SIMULATOR => _("SIMULATOR"),
    Customer::ACX_CALL_BACK => _("CALL BACK"),
    //Customer::ACX_WEB_PHONE => _("WEB PHONE"),
    Customer::ACX_CALLER_ID => _("CALLER_ID"),
    Customer::ACX_SUPPORT => _("SUPPORT"),
    Customer::ACX_NOTIFICATION => _("NOTIFICATION"),
    Customer::ACX_AUTODIALER => _("AUTODIALER"),
    Customer::ACX_PERSONALINFO => _("EDIT PERSONAL INFO"),
    Customer::ACX_SEERECORDING => _("SEE RECORDING"),
];

$len_right_list = count($right_list);

$HD_Form->AddEditElement(
    _("Name"),
    "name",
    "",
    ["maxlength" => 40],
    [Validator::class, "min3Chars"],
    _("Insert the provider name")
);

$HD_Form->AddEditTextarea(
    _("Description"),
    "description",
    "",
    ["rows" => 4],
    null,
    _("Insert the description")
);

$HD_Form->AddEditSqlSelect(
    _("Agent"),
    "id_agent",
    new Table("cc_agent", ["id", "CONCAT(login, ' - ', firstname, ' ', lastname)"]),
    [],
    "",
    [-1 => _("NONE")]
);

$HD_Form->AddEditSelect(
    _("Permissions"),
    "users_perms",
    $right_list,
    "",
    _("To select several rights in the above ListBox, hold the CTRL key down while clicking the items"),
    ["size" => $len_right_list, "multiple" => "multiple"]
);

$HD_Form->FG_LOCATION_AFTER_ADD = "?id=";
$HD_Form->FG_LOCATION_AFTER_EDIT = "?id=";
$HD_Form->FG_LOCATION_AFTER_DELETE = "?id=";
