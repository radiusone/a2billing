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

/**
 * @var numeric-string $popup_select
 * @var string $form_action
 */
Admin::checkPageAccess(Admin::ACX_ADMINISTRATOR);

$form_action ??= "list";

$HD_Form = new FormHandler(
    "cc_logpayment_agent", 
    _("Agent Payment"),
    "cc_logpayment_agent.id",
    $form_action === "list"
        ? [
            "cc_agent" => ["cc_agent.id", "agent_id"],
            "cc_logrefill_agent" => ["cc_agent.id", "cc_logrefill_agent.agent_id"],
        ]
        : []
);

$HD_Form->FG_QUERY_ORDERBY_COLUMNS = ["date"];
$HD_Form->FG_TABLE_DEFAULT_SENS = "DESC";

$HD_Form->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form->FG_LIST_ADDING_BUTTON_LINK1 = "A2B_entity_payment_agent.php?form_action=ask-add";
$HD_Form->FG_LIST_ADDING_BUTTON_ALT1 = $HD_Form ->FG_LIST_ADDING_BUTTON_MSG1 = _("Add Payment");
$HD_Form->FG_LIST_ADDING_BUTTON_IMG1 = get_image_path("money_add.png") ;

$HD_Form->help_text = create_help(_("Agent Payment history - The section below allows you to add payments against an agent. Note that this does not change the balance on the account. Click on 'create associate refill' when you create  a payment to top-up an account."));

$list_refill_type = getRefillType_List();
$HD_Form->AddListValue(_("ID"), "cc_logpayment.id");
$HD_Form->AddListValue(_("Agent"), "cc_logpayment_agent.agent_id", has_rights(Admin::ACX_ADMINISTRATOR) ? "get_linktoagent" : "get_nameofagent");
$HD_Form->AddListValue(_("Date"), "cc_logpayment_agent.date");
$HD_Form->AddListValue(_("Amount"), "payment", "get_money");
$HD_Form->AddListMapping(_("Type"), "payment_type", $list_refill_type);
$HD_Form->AddListValue(_("Refill"), "cc_logrefill_agent.id AS refill_id", "get_agent_refill_link");
$HD_Form->FieldViewElement([
    "cc_logpayment_agent.id",
    "cc_logpayment_agent.agent_id",
    "cc_logpayment_agent.date",
    "payment",
    "payment_type",
    "cc_logrefill_agent.id AS refill_id",
]);

$HD_Form->FG_ENABLE_INFO_BUTTON = true;
$HD_Form->FG_INFO_BUTTON_LINK = "A2B_info_payment.php?type=agent&id=";
$HD_Form->FG_ENABLE_ADD_BUTTON = true;

$HD_Form->search_form_enabled = !$popup_select;
$HD_Form->search_session_key = "payment_agent_selection";
$HD_Form->search_form_title = _("Define specific criteria to search for payments created.");
$HD_Form->search_delete_enabled = false;
$HD_Form->AddSearchDateInput(_("DATE"), "date");
$HD_Form->AddSearchTextInput(_("LOGIN"), "login");
$HD_Form->AddSearchTextInput(_("LASTNAME"), "lastname");
$HD_Form->AddSearchTextInput(_("FIRSTNAME"), "firstname");

if (has_rights (Admin::ACX_MODIFY_PAYMENTS)) {
	$HD_Form->FG_ENABLE_EDIT_BUTTON = true;
	$HD_Form->FG_ENABLE_DELETE_BUTTON = true;

    $HD_Form->AddEditPopup(
        _("AGENT ID"),
        "agent_id",
        "A2B_entity_agent.php?popup_select=1&",
        _("Select the Agent ID"),
        $form_action === "ask-add" && !empty($agent_id) ? ["value" => $agent_id] : [],
        [Validator::class, "numeric"],
        _("Insert the AGENT ID ")
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
        null,
        _("Insert the description")
    );

	if ($form_action!="ask-edit" && $form_action!="edit") {
        $HD_Form->AddEditRadio(
            _("CREATE ASSOCIATE REFILL"),
            "added_refill",
            getYesNoList(),
            "0",
            _("Refill card account"),
            [],
            _("Choose if you want to create a refill with this payment account")
        );
	}

    $HD_Form->AddEditSelect(
        _("REFILL TYPE"),
        "payment_type",
        $list_refill_type,
        "",
        _("Define type for payment and refill,if created.")
    );

	$HD_Form->FG_INTRO_TEXT_ADITION = "";
		
	$HD_Form->FG_ADDITIONAL_FUNCTION_AFTER_ADD = "create_agent_refill";

	$HD_Form->FG_LOCATION_AFTER_ADD = "?id=";
	$HD_Form->FG_LOCATION_AFTER_EDIT = "?id=";
	$HD_Form->FG_LOCATION_AFTER_DELETE = "?id=";
}
