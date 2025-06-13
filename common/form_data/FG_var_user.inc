<?php

use A2billing\Admin;
use A2billing\Forms\FormHandler;
use A2billing\Forms\Validator;

/**
 * @var string $form_action
 */

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

getpost_ifset(['groupID', 'popup_select']);
/**
 * @var numeric-string $groupID
 * @var string $popup_select
 */

if ($groupID !== "0" && $groupID !== "1") {
    header("HTTP/1.0 400 Bad Request");
	die();
}
$subadmin = $groupID > 0;

$HD_Form = new FormHandler(
    "cc_ui_authen",
    $subadmin ? _("Sub-administrator") : _("Administrator"),
    "userid"
);

$HD_Form->list_query_order_columns = ["login"];

$HD_Form->AddListTopButton("A2B_entity_user.php?form_action=ask-add&amp;groupID=$groupID", null, "person-fill");

$HD_Form->add_help_text = create_help(_("Add administrator."));
$HD_Form->list_help_text = create_help(_("Administrators - this shows a list of all the Administrators who have access to the Administrator interface."));

$HD_Form->list_query_conditions["groupid"] = $groupID;

$HD_Form->AddListValue(_("User ID"), "userid");
$HD_Form->AddListValue(_("Login"), "login");
$HD_Form->AddListValue(_("Name"), "name");

if ($popup_select) {
    $HD_Form->AddListSelectActionButton("javascript:sendValue('|param|')");
    exit;
}

$HD_Form->FG_ENABLE_INFO_BUTTON = true;

if (!Admin::allowed(Admin::ACX_MODIFY_ADMINS)) {
    exit;
}

$HD_Form->FG_ENABLE_ADD_BUTTON = true;
$HD_Form->FG_INFO_BUTTON_LINK = "A2B_info_admin.php?groupID=$groupID&id=";
$HD_Form->FG_ENABLE_DELETE_BUTTON = true;
$HD_Form->FG_ENABLE_EDIT_BUTTON = true;

$HD_Form->AddEditElement(
    _("LOGIN"),
    "login",
    "",
    ["maxlength" => 12],
    [Validator::class, "min4Chars"],
    _("Insert the username")
);

$HD_Form->AddEditElement(
    _("PASSWORD"),
    "pwd_encoded",
    $form_action === "ask-edit" ? _("Enter a new password or leave blank to keep the old one") : _("Enter a new password"),
    ["maxlength" => 32],
    $form_action === "ask-edit" ? [Validator::class, "min8CharsOptional"] : [Validator::class, "min8Chars"]
);

if ($subadmin) {
    $rights_list = [
        Admin::ACX_CUSTOMER => _("CUSTOMERS"),
        Admin::ACX_BILLING => _("BILLING"),
        Admin::ACX_RATECARD => _("RATECARD"),
        Admin::ACX_TRUNK => _("TRUNK"),
        Admin::ACX_CALL_REPORT => _("CALL REPORT"),
        Admin::ACX_CRONT_SERVICE => _("CRONT SERVICE"),
        Admin::ACX_ADMINISTRATOR =>  _("AGENT / ADMIN"),
        Admin::ACX_MAINTENANCE => _("MAINTENANCE"),
        Admin::ACX_MAIL => _("MAIL"),
        Admin::ACX_DID => _("DID"),
        Admin::ACX_CALLBACK => _("CALL BACK"),
        Admin::ACX_OUTBOUNDCID => _("OUTBOUND CID"),
        Admin::ACX_PACKAGEOFFER => _("PACKAGE OFFER"),
        Admin::ACX_INVOICING => _("INVOICES"),
        Admin::ACX_SUPPORT => _("SUPPORT"),
        Admin::ACX_DASHBOARD => _("DASHBOARD"),
        Admin::ACX_ACXSETTING => _("SYSTEM SETTINGS"),
        Admin::ACX_MODIFY_REFILLS => _("EDIT/DELETE REFILLS"),
        Admin::ACX_MODIFY_PAYMENTS => _("EDIT/DELETE PAYMENTS"),
        Admin::ACX_MODIFY_CUSTOMERS => _("EDIT/DELETE CUSTOMERS"),
        Admin::ACX_DELETE_NOTIFICATIONS => _("DELETE NOTIFICATIONS"),
        Admin::ACX_DELETE_CDR => _("DELETE CDR"),
        Admin::ACX_MODIFY_ADMINS => _("EDIT/DELETE ADMIN"),
        Admin::ACX_MODIFY_AGENTS => _("EDIT/DELETE AGENT"),
    ];

    $len_right_list = count($rights_list);

    $HD_Form->AddEditSelect(
        _("PERMISSION"),
        "perms",
        $rights_list,
        "",
        _("To select several rights in the above ListBox, hold the Control/⌘ key down while clicking the items"),
        ["size" => $len_right_list, "multiple" => "multiple"]
    );
} else {
    // 32 bits max permission value
    $HD_Form->FG_ADD_QUERY_HIDDEN_INPUTS["perms"] = pow(2, 32) - 1;
    $HD_Form->FG_EDIT_QUERY_HIDDEN_INPUTS["perms"] = pow(2, 32) - 1;
}

$HD_Form->AddEditElement(
    _("NAME"),
    "name",
    "",
    ["maxlength" => 50],
    null,
    _("Insert the name")
);

$HD_Form->AddEditElement(
    _("ADDRESS"),
    "direction",
    "",
    ["maxlength" => 250],
    null,
    _("Insert the address")
);

$HD_Form->AddEditElement(
    _("CITY"),
    "city",
    "",
    ["maxlength" => 30],
    null,
    _("Insert the country")
);

$HD_Form->AddEditElement(
    _("ZIPCODE"),
    "zipcode",
    "",
    ["maxlength" => 7],
    null,
    _("Insert the zip code")
);

$HD_Form->AddEditElement(
    _("STATE"),
    "state",
    "",
    ["maxlength" => 30],
    null,
    _("Insert the state")
);

$HD_Form->AddEditElement(
    _("COUNTRY"),
    "country",
    "",
    ["maxlength" => 30],
    null,
    _("Insert the country")
);

$HD_Form->AddEditElement(
    _("PHONE"),
    "phone",
    "",
    ["maxlength" => 20],
    null,
    _("Insert the telephone number")
);

$HD_Form->AddEditElement(
    _("FAX"),
    "fax",
    "",
    ["maxlength" => 20],
    null,
    _("Insert the fax number")
);

$HD_Form->AddEditElement(
    _("EMAIL"),
    "email",
    "",
    ["maxlength" => 70],
    null,
    _("Insert the email"));

$HD_Form->FG_ADD_QUERY_HIDDEN_INPUTS["groupID"] = $groupID;
$HD_Form->FG_EDIT_QUERY_HIDDEN_INPUTS["groupID"] = $groupID;

$HD_Form->FG_INTRO_TEXT_ADITION = "";

$HD_Form->FG_LOCATION_AFTER_ADD = "?groupID=$groupID&form_action=ask-edit&id=";
$HD_Form->FG_LOCATION_AFTER_EDIT = "?groupID=$groupID&form_action=list";
$HD_Form->FG_LOCATION_AFTER_DELETE = "?groupID=$groupID&form_action=list";
