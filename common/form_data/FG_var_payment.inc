<?php

use A2billing\Admin;
use A2billing\Agent;
use A2billing\Customer;
use A2billing\Forms\FormBO;
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
/**
 * @var numeric-string $popup_select
 * @var string $form_action
 */

getpost_ifset(["card_id"]);
/**
 * @var string $card_id
 */
$form_action ??= "list";

if (is_admin()) {
    $HD_Form = new FormHandler("cc_logpayment", _("Payment"));
} else {
    $HD_Form = new FormHandler(
        "cc_logpayment",
        _("Payment"),
        "cc_logpayment.id",
        ["cc_card" => ["cc_card.id", "cc_logpayment.card_id"], "cc_card_group" => ["cc_card.id_group", "cc_card_group.id"]]
    );
    $HD_Form->list_query_conditions["cc_card_group.id_agent"] = Agent::id();
}
$HD_Form->list_query_order_direction = "DESC";

if (Admin::allowed(Admin::ACX_MODIFY_PAYMENTS)) {
	$HD_Form->AddListTopButton(null, null, "cash-stack text-success");
}

$HD_Form->help_text = create_help(
    is_admin()
        ? _("Payment history - The section below allows you to add payments against a customer. Note that this does not change the balance on the account. Click on 'create associate refill' when you create a payment to top-up an account.")
        : _("Payment history - The section below allows you to add payments against a customer. Note that this does not change the balance on the card. Click refill under customer list to top-up a card.")
);

$list_refill_type = getRefillType_List();

$HD_Form->AddListValue(_("ID"), "cc_logpayment.id");
$HD_Form->AddListValue(_("Account"), "cc_card.username", [Customer::class, "getLinkFromUsername"]);
$HD_Form->AddListValue(_("Date"), "date");
$HD_Form->AddListValue(_("Amount"), "payment", "get_money");
$HD_Form->AddListMapping(_("Type"), "payment_type", $list_refill_type);
$HD_Form->AddListValue(_("Refill"), "id_logrefill", "get_refill_link");

if (!$popup_select) {
    $HD_Form->FG_ENABLE_INFO_BUTTON = true;
    $HD_Form->FG_INFO_BUTTON_LINK = "A2B_info_payment.php?id=";
	$HD_Form->search_form_enabled = true;
    $HD_Form->search_session_key = "payment_cust_selection";
    $HD_Form->search_delete_enabled = false;
    $HD_Form->search_form_title = _("Define specific criteria to search for payments created.");

    $HD_Form->AddSearchDateInput(_("Date"), "date");
    $HD_Form->AddSearchTextInput(_("Account number"), "username");
    $HD_Form->AddSearchTextInput(_("Last name"), "lastname");
    $HD_Form->AddSearchTextInput(_("First name"), "firstname");
} else {
    $HD_Form->AddListSelectActionButton("javascript:sendValue('|param|')");
}

$HD_Form->AddListFilter("username", _("Account number"));

if (Admin::allowed(Admin::ACX_MODIFY_PAYMENTS) && !$popup_select) {
	$HD_Form->FG_ENABLE_ADD_BUTTON = true;
	$HD_Form->FG_ENABLE_EDIT_BUTTON = true;
	$HD_Form->FG_ENABLE_DELETE_BUTTON = true;

    $HD_Form->AddEditPopup(
        _("CUSTOMER ID"),
        "card_id",
        "A2B_entity_card.php",
        _("Select the Customer ID"),
        $form_action === "ask-add" && !empty($card_id) ? ["value" => $card_id] : [],
        [Validator::class, "numeric"],
        _("Insert the Customer ID")
    );

    $date_attr = ["maxlength" => 40];
    if ($form_action === "ask-add") {
        $date_attr["value"] = (new DateTime())->format("Y-m-d H:i:s");
    }
    $HD_Form->AddEditElement(
        _("PAYMENT DATE"),
        "date",
        "",
        $date_attr,
        [Validator::class, "dateTime"],
        _("Insert the current date")
    );

    $HD_Form->AddEditElement(
        _("PAYMENT AMOUNT"),
        "payment",
        _("Enter the amount in the currency base : ") . BASE_CURRENCY,
        ["maxlength" => 10],
        [Validator::class, "number"],
        _("Insert the payment amount")
    );

    $HD_Form->AddEditTextarea(
        _("DESCRIPTION"),
        "description",
        "",
        ["rows" => 4],
        _("Insert the description")
    );

	if ($form_action !== "ask-edit" && $form_action !== "edit") {
        $HD_Form->AddEditRadio(
            _("CREATE ASSOCIATE REFILL"),
            "added_refill",
            getYesNoList(),
            "0",
            _("Refill card account"),
            [],
            _("Choose if you want to create a refill with this payment account")
        );

        $HD_Form->AddEditRadio(
            _("CREATE AGENT COMMISSION "),
            "added_commission",
            getYesNoList(),
            "0",
            _("Commission for agent"),
            [],
            _("Choose if you want to create a commission to an agent, if this customer is managed by one")
        );
	}

    $HD_Form->AddEditSelect(
        _("PAYMENT TYPE"),
        "payment_type",
        $list_refill_type,
        "",
        _("Define type for payment and refill,if created.")
    );

	$HD_Form->add_message_intro = "";
	$HD_Form->FG_ADDITIONAL_FUNCTION_AFTER_ADD = [FormBO::class, "create_refill_after_payment"];
}
