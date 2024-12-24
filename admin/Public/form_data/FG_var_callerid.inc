<?php

use A2billing\Admin;
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

$HD_Form = new FormHandler("cc_callerid", "CallerID");

$HD_Form ->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form ->FG_LIST_ADDING_BUTTON_IMG1 = get_image_path("page_white_add.png") ;

$HD_Form->AddListActionButton(
    "A2B_entity_payment.php?form_action=ask-add&card_id=|col1|",
    _('ADD PAYMENT'),
    get_image_path("money.png")
);
$HD_Form->AddListActionButton(
    "A2B_entity_logrefill.php?form_action=ask-add&card_id=|col1|",
    _("ADD REFILL"),
    get_image_path("brick_add.png")
);

$HD_Form->help_text = create_help(
    _("Set the caller ID so that the customer calling in is authenticated on the basis of the callerID rather than with the account number"),
    'ListCallerID'
);

$HD_Form->AddListValue(_("CALLERID"), "cid");
$HD_Form->AddListSqlMapping(_("ACCOUNT NUMBER"), "id_cc_card", new Table("cc_card", ["id", "username"]), [], "A2B_entity_card.php?form_action=ask-edit&id=");
$HD_Form->AddListValue(_("CUSTOMER NAME"), "id_cc_card", "display_customer_name_id_link");
$HD_Form->AddListMapping(_("ACTIVATED"), "activated", getActivationTrueFalseList());
$HD_Form->FieldViewElement (["cid", "id_cc_card", "id_cc_card", "cc_callerid.activated", "cc_callerid.id"]);

$HD_Form->FG_ENABLE_ADD_BUTTON = true;
$HD_Form->FG_ENABLE_EDIT_BUTTON = true;
$HD_Form->FG_ENABLE_DELETE_BUTTON = true;

$HD_Form->AddEditElement(
    _("CALLERID"),
    "cid",
    "",
    "maxlength=30",
    [Validator::class, "phoneNumber"],
    _("Insert the CallerID")
);

$HD_Form->AddEditRadio(
    _("ACTIVATED"),
    "activated",
    getActivationTrueFalseList(),
    "t",
    "",
    "",
    _("Choose if you want to activate this card")
);

$HD_Form->AddEditPopup(
    _("CARD ID"),
    "id_cc_card",
    "A2B_entity_card.php?popup_select=1&",
    _("Define the card number ID to use."),
    "",
    [Validator::class, "numeric"],
    _("Insert the card number ID")
);

// Set the filter variables
$HD_Form->FG_FILTER_ENABLE = true;
$HD_Form->FG_FILTER_COLUMN = 'cid';
$HD_Form->FG_FILTER_LABEL = _('CALLERID');

$HD_Form->FG_LOCATION_AFTER_ADD = "?id=";
$HD_Form->FG_LOCATION_AFTER_EDIT = "?id=";
$HD_Form->FG_LOCATION_AFTER_DELETE = "?id=";



