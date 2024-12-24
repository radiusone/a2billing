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

getpost_ifset(["card_id"]);
/**
 * @var string $card_id
 */
$form_action ??= "list";

$HD_Form = new FormHandler("cc_logpayment", _("Payment"));

$HD_Form->FG_TABLE_DEFAULT_SENS = "DESC";

if (has_rights (Admin::ACX_MODIFY_PAYMENTS)) {
	$HD_Form->FG_LIST_ADDING_BUTTON1 = true;
	$HD_Form->FG_LIST_ADDING_BUTTON_IMG1 = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAJ0SURBVDjLlZPdT9JRGMe5qFu2Lrt1a63LWv9ATRdN5xvLsnLRipzZpIVpigjyIs3XAOUHgopoWkggP5QXSRJwJQmtm/IlAWtt3XXTfubS+nZ+P1eby6ldPGdn5+zzfb7Pc57DA8DbL9rjrYxuVsXf7W5fuC2mYawpE7QRJZpDDfz/EngYVTN9qR4EPvlgXjCiKVCPWvou/0ACxDJjSbIwDefqMPxrEzC87IDUW4Pq8Vv8PQVaX7Qw5qQRgY9ePP0wDMeSFfWTUkxmPeiI61DlFOP6SAV/VwFtRMFQCwb4CdwW10IbVcK+aMHgohmPlwdBZ11oCctx1X5p/R8B9Uzzuum1ntj1Iv1tGRtb3zH2dgSa2eZtOOOCMizD5cGyzR0lGBNdx1TP5T96E4+4WttiWg6mYr3Ifk1DF1PBmxmHYlrGZkbFUDku2oSHOAFjolOuIpZ65rs5+MmKg9hWcJlZWB1UbsOhRjYz5r/MoSn4AKWWQg0nwFoyzndhijRobGWIq3XgPQU1sa2LqjCRHoc81IBK9w0OnvscRWQtBGFfEc4b8o7wNDMKOwnY3lDwZZ+h1idB/zsThpf6CezkstVN3yNwHFMrNGqCVRvlA2UQ6POkud1nTvE0EcVR1gU7JNSCnrPrWLRtw+RM7BKBXnJDP9eOYqogVNAj0Av0uTk7mtjov2+1p2yQ0hIYXnXCs+qEzF+HC9YSyIiIsK84XWTKP5tvPHdi11GupSXHW8JNW+FMAHdclSCCKDEX/iKdDgotRY17jTu31LhvHybT5RGPin5K3NWs1c0yW+lp0umc/T7b383NUdHJa44rSfJU+Qf54n/iNzi8zBtL0z1zAAAAAElFTkSuQmCC";
}

$HD_Form->help_text = create_help(
    _("Payment history - The section below allows you to add payments against a customer. Note that this does not change the balance on the account. Click on 'create associate refill' when you create a payment to top-up an account."),
    'ViewPayments'
);

$list_refill_type = getRefillType_List();

$HD_Form->AddListValue(_("ID"), "id");
$HD_Form->AddListValue(_("Account"), "card_id","display_customer_id_link");
$HD_Form->AddListValue(_("Date"), "date");
$HD_Form->AddListValue(_("Amount"), "payment", "display_money");
$HD_Form->AddListMapping(_("Type"), "payment_type", $list_refill_type);
$HD_Form->AddListValue(_("Refill"), "id_logrefill", "display_refill_link");
$HD_Form->FieldViewElement([
    "id",
    "card_id",
    "date",
    "payment",
    "payment_type",
    "id_logrefill",
]);

$HD_Form->FG_ENABLE_INFO_BUTTON = true;
$HD_Form->FG_INFO_BUTTON_LINK = "A2B_info_payment.php?id=";

if (!$popup_select) {
	$HD_Form->search_form_enabled = true;
    $HD_Form->search_session_key = "payment_cust_selection";
    $HD_Form->search_delete_enabled = false;
    $HD_Form->search_form_title = _("Define specific criteria to search for payments created.");

    $HD_Form->AddSearchDateInput(_("Date"), "date");
    $HD_Form->AddSearchTextInput(_("Account number"), "username");
    $HD_Form->AddSearchTextInput(_("Last name"), "lastname");
    $HD_Form->AddSearchTextInput(_("First name"), "firstname");
}

$HD_Form->FG_FILTER_ENABLE = true;
$HD_Form->FG_FILTER_COLUMN = "username";
$HD_Form->FG_FILTER_LABEL = "account number";

if (has_rights (Admin::ACX_MODIFY_PAYMENTS)) {
	$HD_Form->FG_ENABLE_ADD_BUTTON = true;
	$HD_Form->FG_ENABLE_EDIT_BUTTON = true;
	$HD_Form->FG_ENABLE_DELETE_BUTTON = true;

    $id_val = ($form_action === "ask-add" && !empty($card_id)) ? $card_id : "";

    $HD_Form->AddEditPopup(
        _("CUSTOMER ID"),
        "card_id",
        "A2B_entity_card.php?popup_select=1&",
        _("Select the Customer ID"),
        "value='$id_val'",
        [Validator::class, "numeric"],
        _("Insert the Customer ID")
    );

    $comp_date = ($form_action === "ask-add") ? "value='" . (new DateTime())->format("Y-m-d H:i:s") . "'" : "";

    $HD_Form->AddEditElement(
        _("PAYMENT DATE"),
        "date",
        "",
        "size=40 maxlength=40 $comp_date",
        [Validator::class, "dateTime"],
        _("Insert the current date")
    );

    $HD_Form->AddEditElement(
        _("PAYMENT AMOUNT"),
        "payment",
        _("Enter the amount in the currency base : ") . BASE_CURRENCY,
        "size=60 maxlength=10",
        [Validator::class, "number"],
        _("Insert the payment amount")
    );

    $HD_Form->AddEditTextarea(
        _("DESCRIPTION"),
        "description",
        "",
        "cols='50' rows='4'",
        null,
        _("Insert the description")
    );

	if ($form_action !== "ask-edit" && $form_action !== "edit") {
        $HD_Form->AddEditRadio(
            _("CREATE ASSOCIATE REFILL"),
            "added_refill",
            getYesNoList(),
            "0",
            _("Refill card account"), "",
            _("Choose if you want to create a refill with this payment account")
        );

        $HD_Form->AddEditRadio(
            _("CREATE AGENT COMMISSION "),
            "added_commission",
            getYesNoList(),
            "0",
            _("Commission for agent"),
            "",
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

	$HD_Form->FG_INTRO_TEXT_ADITION = "";
	$HD_Form->FG_ADDITIONAL_FUNCTION_AFTER_ADD = "create_refill_after_payment";

	$HD_Form->FG_LOCATION_AFTER_ADD = "?id=";
	$HD_Form->FG_LOCATION_AFTER_EDIT = "?id=";
	$HD_Form->FG_LOCATION_AFTER_DELETE = "?id=";
}
