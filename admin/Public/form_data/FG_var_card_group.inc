<?php

use A2billing\Admin;
use A2billing\Customer;
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

$HD_Form = new FormHandler("cc_card_group", "Customer Group");

$HD_Form->FG_TABLE_DEFAULT_ORDER = "name";

// Code Here for Deleting the Dependent Records
// Dependent Tables
$HD_Form->FG_FK_DELETE_ALLOWED = true;
$HD_Form->FG_FK_DELETE_CONFIRM = true;
$HD_Form->FG_FK_WARNONLY = true;
$HD_Form->FG_FK_TABLENAMES = ["cc_card"];
$HD_Form->FG_FK_EDITION_CLAUSE = ["id_group"];

$HD_Form->FG_FK_DELETE_MESSAGE = _("You have some cards using this Customers Group! Please comfirm that you really want to remove this Customers Group ? ");

$HD_Form->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form->FG_LIST_ADDING_BUTTON_LINK1 = "A2B_entity_card_group.php?form_action=ask-add";
$HD_Form->FG_LIST_ADDING_BUTTON_ALT1 = $HD_Form->FG_LIST_ADDING_BUTTON_MSG1 = _("Add Group");
$HD_Form->FG_LIST_ADDING_BUTTON_IMG1 = get_image_path("group_add.png");

$HD_Form->AddViewElement(_("NAME"), "name");
$HD_Form->AddViewElement(_("DESCRIPTION"), "description", true, 150);
$HD_Form->FieldViewElement ('name, description');

$HD_Form->FG_ENABLE_ADD_BUTTON = true;
$HD_Form->FG_ENABLE_EDIT_BUTTON = true;
$HD_Form->FG_ENABLE_DELETE_BUTTON = true;
$HD_Form->FG_DELETION_FORBIDDEN_ID = [1];

$right_list = [
    [ _("PASSWORD"), Customer::ACX_PASSWORD],
    [ _("SIP IAX"), Customer::ACX_SIP_IAX],
    [ _("CALL HISTORY"), Customer::ACX_CALL_HISTORY],
    [ _("PAYMENT HISTORY"), Customer::ACX_PAYMENT_HISTORY],
    [ _("VOUCHER"), Customer::ACX_VOUCHER],
    [ _("INVOICES"), Customer::ACX_INVOICES],
    [ _("DID"), Customer::ACX_DID],
    [ _("SPEED DIAL"), Customer::ACX_SPEED_DIAL],
    [ _("RATECARD"), Customer::ACX_RATECARD],
    [ _("SIMULATOR"), Customer::ACX_SIMULATOR],
    [ _("CALL BACK"), Customer::ACX_CALL_BACK],
    //[ _("WEB PHONE"), Customer::ACX_WEB_PHONE],
    [ _("CALLER_ID"), Customer::ACX_CALLER_ID],
    [ _("SUPPORT"), Customer::ACX_SUPPORT],
    [ _("NOTIFICATION"), Customer::ACX_NOTIFICATION],
    [ _("AUTODIALER"), Customer::ACX_AUTODIALER],
    [ _("EDIT PERSONAL INFO"), Customer::ACX_PERSONALINFO],
    [ _("SEE RECORDING"), Customer::ACX_SEERECORDING],
];

$len_right_list = count($right_list);

$HD_Form->AddEditElement(
    _("NAME"),
    "name",
    "",
    "size=30 maxlength=40",
    0,
    _("Insert the provider name")
);

$HD_Form->AddEditTextarea(
    _("DESCRIPTION"),
    "description",
    "",
    "cols='50' rows='4'",
    null,
    _("Insert the description")
);

$HD_Form->AddEditSqlSelect(
    _("AGENT"),
    "id_agent",
    "cc_agent",
    "login,id,firstname,lastname",
    "",
    "",
    [-1, _("NONE")],
    "%1 - %3 %4 (%2)"
);

$HD_Form->AddEditSelect(
    _("PERMISSION USER INTERFACE"),
    "users_perms",
    $right_list,
    "",
    _("To select several rights in the above ListBox, hold the CTRL key down while clicking the items"),
    "size='$len_right_list' multiple='multiple'"
);

$HD_Form->FG_LOCATION_AFTER_ADD = "?id=";
$HD_Form->FG_LOCATION_AFTER_EDIT = "?id=";
$HD_Form->FG_LOCATION_AFTER_DELETE = "?id=";
