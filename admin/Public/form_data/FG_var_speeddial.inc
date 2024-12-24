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
    "cc_speeddial",
    _("Speed Dial"),
    "cc_speeddial.id",
    $form_action === "list"
        ? ["cc_card" => ["cc_speeddial.id_cc_card", "cc_card.id"]]
        : []
);

$HD_Form->FG_TABLE_DEFAULT_ORDER = "creationdate";
$HD_Form->FG_TABLE_DEFAULT_SENS = "DESC";

$HD_Form->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form->FG_LIST_ADDING_BUTTON_IMG1 = get_image_path("page_white_add.png") ;

$HD_Form->help_text = create_help(
    _("This section allows you to define the Speed dials for the customer.")
    . '<br>'
    . _("A Speed Dial will be entered on the IVR in order to make a shortcut to their preferred dialled phone number."),
    'ListSpeeddial'
);

$speeddial_list = range(0, 9);

$HD_Form->AddListValue(_("Speed dial"), "speeddial");
$HD_Form->AddListValue(_("Phone number"), "phone");
$HD_Form->AddListValue(_("Name"), "name");
$HD_Form->AddListValue(_("Account"), "id_cc_card", "display_customer_id_link");
$HD_Form->AddListValue(_("Date"), "cc_speeddial.creationdate");
$HD_Form->FieldViewElement (["speeddial", "phone", "name", "id_cc_card", "cc_speeddial.creationdate"]);

$HD_Form->FG_ENABLE_EDIT_BUTTON = true;
$HD_Form->FG_ENABLE_DELETE_BUTTON = true;
$HD_Form->FG_ENABLE_ADD_BUTTON = true;

$HD_Form->AddEditSelect(_("SPEED DIAL"), "speeddial", $speeddial_list);

$HD_Form->AddEditElement(
    _("NAME"),
    "name",
    _("Enter the name or label that will identify this speed dial"),
    ["maxlength" => 50],
    [Validator::class, "min3Chars"],
    _("Insert the name")
);


$HD_Form->AddEditElement(
    _("PHONE"),
    "phone",
    _("Enter the phone number for the speed dial."),
    ["maxlength" => 50],
    [Validator::class, "numeric"],
    _("Insert the phone")
);

$HD_Form->AddEditPopup(
    _("CARD ID"),
    "id_cc_card",
    "A2B_entity_card.php?popup_select=1&",
    _("Define the card number ID to use."),
    [],
    [Validator::class, "numeric"],
    _("Insert the card number ID")
);

$HD_Form->FG_FILTER_ENABLE = true;
$HD_Form->FG_FILTER_COLUMN = "username";
$HD_Form->FG_FILTER_LABEL = _("Account Number");

$HD_Form->FG_INTRO_TEXT_ADITION = '';

$HD_Form->FG_LOCATION_AFTER_DELETE = $HD_Form->FG_LOCATION_AFTER_EDIT = $HD_Form->FG_LOCATION_AFTER_ADD = "?id=";
