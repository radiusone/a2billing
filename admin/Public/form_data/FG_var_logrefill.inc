<?php

use A2billing\Admin;
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

getpost_ifset(['id', 'card_id', 'date', 'credit', 'description', 'added_invoice']);
/**
 * @var string $form_action
 * @var string $popup_select
 * @var string $id
 * @var string $card_id
 * @var string $date
 * @var string $credit
 * @var string $description
 * @var string $added_invoice
 */
$form_action = $form_action ?? "list";

if ($form_action === "list") {
	$HD_Form = new FormHandler("cc_logrefill LEFT JOIN cc_card ON cc_card.id=card_id", "Refill", 'cc_logrefill.id');
} else {
	$HD_Form = new FormHandler("cc_logrefill", "Refill");
}

$HD_Form->no_debug();
$HD_Form -> FG_TABLE_DEFAULT_SENS = "DESC";

if (has_rights (Admin::ACX_MODIFY_REFILLS)) {
	$HD_Form->FG_LIST_ADDING_BUTTON1 = true;
	$HD_Form->FG_LIST_ADDING_BUTTON_LINK1 = "A2B_entity_logrefill.php?form_action=ask-add";
	$HD_Form ->FG_LIST_ADDING_BUTTON_MSG1 = gettext("Add Refill");
	$HD_Form->FG_LIST_ADDING_BUTTON_IMG1 = Images_Path ."/time_add.png" ;
}

$list_refill_type = getRefillType_List();

$HD_Form -> AddViewElement(gettext("ID"), "cc_logrefill.id");
$HD_Form -> AddViewElement(gettext("ACCOUNT"), "username", true, 30, "display_customer_link");
$HD_Form -> AddViewElement(gettext("REFILL DATE"), "cc_logrefill.date", true, 30);
$HD_Form -> AddViewElement(gettext("REFILL AMOUNT"), "cc_logrefill.credit", true, 0, "display_money");
$HD_Form -> AddViewElement(gettext("REFILL TYPE"), "refill_type", true, 0, "", "list", $list_refill_type);

$HD_Form -> FieldViewElement ('cc_logrefill.id, cc_logrefill.card_id, cc_logrefill.date, cc_logrefill.credit , cc_logrefill.refill_type');

if (!$popup_select) {
    $HD_Form->search_form_enabled = true;
    $HD_Form->search_session_key = 'refill_cust_selection';
    $HD_Form->search_form_title = gettext('Define specific criteria to search for refills created.');
    $HD_Form->search_date_enabled = true;
    $HD_Form->search_date_text = gettext('DATE');
    $HD_Form->search_date_column = 'date';

    $HD_Form->AddSearchTextInput(gettext("ACCOUNT NUMBER"), 'username', 'usernametype');
    $HD_Form->AddSearchTextInput(gettext("LASTNAME"), 'lastname', 'lastnametype');
    $HD_Form->AddSearchTextInput(gettext("FIRSTNAME"), 'firstname', 'firstnametype');
    $HD_Form->search_delete_enabled = false;

    $HD_Form -> FG_ENABLE_INFO_BUTTON = true;
    $HD_Form -> FG_INFO_BUTTON_LINK = "A2B_refill_info.php?id=";
    $HD_Form -> FG_ENABLE_ADD_BUTTON = true;
}

$HD_Form -> FG_FILTER_ENABLE = true;
$HD_Form -> FG_FILTER_COLUMN = 'username';
$HD_Form -> FG_FILTER_LABEL = 'Account number';

// what is the point of this, if editing or deleting a refill doesn't change the card balance?
if (has_rights (Admin::ACX_MODIFY_REFILLS)) {
	$HD_Form -> FG_ENABLE_EDIT_BUTTON = true;
	$HD_Form -> FG_ENABLE_DELETE_BUTTON = true;

    $id_val = $form_action === "ask-add" && !empty($card_id) ? "value='$card_id'" : "";

    $HD_Form->AddEditPopup(
        gettext("CUSTOMER ID"),
        "card_id",
        "A2B_entity_card.php?popup_select=1&",
        gettext("Select the Customer ID"),
        $id_val,
        4, gettext("Insert the Customer ID")
    );

    $comp_date = $form_action === "ask-add" ? "value='" . (new DateTime())->format("Y-m-d H:i:s") . "'" : "";
    $HD_Form->AddEditElement(
        gettext("REFILL DATE"),
        "date",
        "",
        "size=40 maxlength=40 $comp_date",
        10,
        gettext("Insert the current date")
    );

    $HD_Form->AddEditElement(
        gettext("REFILL AMOUNT"),
        "credit",
        gettext("Enter the amount in the currency base : ") . BASE_CURRENCY,
        "size=60 maxlength=10",
        12,
        gettext("Insert the credit amount")
    );

    $HD_Form->AddEditTextarea(
        gettext("DESCRIPTION"),
        "description",
        "",
        "cols='50' rows='4'", null, gettext("Insert the description")
    );

    $HD_Form->AddEditSelect(
        gettext("REFILL TYPE"),
        "refill_type",
        $list_refill_type,
        "", gettext("Define type for payment and refill,if created.")
    );

	if ($form_action !== "ask-edit" && $form_action !== "edit") {
        $HD_Form->AddEditRadio(
            gettext("CREATE ASSOCIATE INVOICE"),
            "added_invoice",
            [[gettext("Yes"), "1"], [gettext("No"), "0"]],
            "1",
            "", "",
            gettext("Choose if you want to create an invoice with this refill account")
        );
	}

	$HD_Form -> FG_INTRO_TEXT_ADITION = '';
	$HD_Form -> FG_ADDITIONAL_FUNCTION_AFTER_ADD = 'processing_refill_add';
	$HD_Form -> FG_LOCATION_AFTER_ADD = "?id=";
	$HD_Form -> FG_LOCATION_AFTER_EDIT = "?id=";
	$HD_Form -> FG_LOCATION_AFTER_DELETE = "?id=";
}

