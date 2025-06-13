<?php

use A2billing\A2Billing;
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
 * @var A2Billing $A2B
 */

$HD_Form = new FormHandler(
    "cc_callerid",
    _("Caller ID"),
    "cc_callerid.id",
    ["cc_card" => ["cc_card.id", "cc_callerid.id_cc_card"], "cc_card_group" => ["cc_card.id_group", "cc_card_group.id"]]
);
if (is_customer()) {
    $HD_Form->list_query_conditions["cc_callerid.id_cc_card"] = $_SESSION["card_id"];
} elseif (is_agent()) {
    $HD_Form->list_query_conditions["cc_card_group.id_agent"] = $_SESSION["agent_id"];
}

if (is_customer()) {
    $cid_count = (new Table("cc_callerid"))
        ->countRows($HD_Form->DBHandle, ["id_cc_card" => $_SESSION["card_id"]]);
    if ($cid_count < $A2B->config["webcustomerui"]['limit_callerid']) {
        $HD_Form->AddListTopButton();
        $HD_Form->FG_ENABLE_ADD_BUTTON = true;
    } else {
        $HD_Form->AddListTopButton("javascript:alert(" . htmlspecialchars(json_encode(_("You are not allowed to add more CallerID."))) . ")");
    }
    $HD_Form->FG_ENABLE_DELETE_BUTTON = true;
    $HD_Form->add_help_text = _("You can add your home phone or cellphone number below to allow you to call in, be automatically recognized, and use your telephony services without having to enter your PIN.");
    $HD_Form->AddListValue(_("CALLERID"), "cid")
        ->AddListMapping(_("ACTIVATED"), "cc_callerid.activated", getActivationTrueFalseList());
} else {
    $HD_Form->AddListTopButton();

    if (is_admin()) {
        $HD_Form->AddListActionButton(
            "A2B_entity_payment.php?form_action=ask-add&card_id=|col4|",
            _('ADD PAYMENT'),
            "",
            "cash",
            "text-success"
        );
    }
    $HD_Form->AddListActionButton(
        "A2B_entity_logrefill.php?form_action=ask-add&card_id=|col4|",
        _("ADD REFILL"),
        "",
        "cash-stack",
        "text-success"
    );

    $HD_Form->help_text = create_help(
        _("Set the caller ID so that the customer calling in is authenticated on the basis of the callerID rather than with the account number")
    );

    $HD_Form->AddListValue(_("CALLERID"), "cid")
        ->AddListValue(_("ACCOUNT NUMBER"), "id_cc_card", [Customer::class, "getUsername"])
        ->AddListValue(_("CUSTOMER NAME"), "id_cc_card", [Customer::class, "getName"])
        ->AddListMapping(_("ACTIVATED"), "cc_callerid.activated", getActivationTrueFalseList())
        ->AddListHiddenValue("id_cc_card");

    $HD_Form->FG_ENABLE_ADD_BUTTON = true;
    $HD_Form->FG_ENABLE_EDIT_BUTTON = true;
    $HD_Form->FG_ENABLE_DELETE_BUTTON = true;
}

$HD_Form->AddEditElement(
    _("CALLERID"),
    "cid",
    "",
    ["maxlength" => 30],
    [Validator::class, "phoneNumber"],
    _("Insert the CallerID")
);

$HD_Form->AddEditRadio(
    _("ACTIVATED"),
    "activated",
    getActivationTrueFalseList(),
    "t",
    "",
    [],
    _("Choose if you want to activate this caller ID")
);

if (is_customer()) {
    $HD_Form->FG_ADD_QUERY_HIDDEN_INPUTS["id_cc_card"] = $_SESSION["card_id"];
    $HD_Form->FG_EDIT_QUERY_HIDDEN_INPUTS["id_cc_card"] = $_SESSION["card_id"];
} else {
    $HD_Form->AddEditPopup(
        _("CARD ID"),
        "id_cc_card",
        "A2B_entity_card.php?popup_select=1&",
        _("Define the card number ID to use."),
        [],
        [Validator::class, "numeric"],
        _("Insert the card number ID")
    );

    // Set the filter variables
    $HD_Form->FG_FILTER_ENABLE = true;
    $HD_Form->FG_FILTER_COLUMN = 'cid';
    $HD_Form->FG_FILTER_LABEL = _('CALLERID');
}
