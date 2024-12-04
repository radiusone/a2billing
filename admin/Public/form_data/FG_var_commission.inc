<?php

use A2billing\Admin;
use A2billing\Forms\FormHandler;
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
 * @var numeric-string|null $form_action
 */

Admin::checkPageAccess(Admin::ACX_ADMINISTRATOR);

getpost_ifset(["id_card"]);
/**
 * @var numeric-string|null $id_card
 */

$form_action ??= "list";

$HD_Form = new FormHandler("cc_agent_commission", _("Agent Commission"));

$HD_Form->FG_QUERY_ORDERBY_COLUMNS = ["date"];
$HD_Form ->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form ->FG_LIST_ADDING_BUTTON_IMG1 = get_image_path("money_add.png") ;

$com_list = getRefillType_List();

$HD_Form->AddListValue(_("Account"), "id_card", "display_customer_id_link");
$HD_Form->AddListValue(_("Agent"), "id_agent", "display_linktoagent");
$HD_Form->AddListValue(_("Payment date"), "date");
$HD_Form->AddListValue(_("Amount"), "amount", "display_money");
$HD_Form->AddListMapping(_("Status"), "paid_status", $com_list);
$HD_Form->AddListValue(_("Description"), "description");
$HD_Form->AddListSqlMapping(_("Link to payment"), "id_payment", new Table("cc_logpayment", ["id", "payment"]), [], "A2B_entity_payment.php?form_action=ask-edit&id=");
$HD_Form->FieldViewElement([
    "id_card",
    "id_agent",
    "date",
    "amount",
    "commission_type",
    "description",
    "id_payment",
]);

$HD_Form->FG_ENABLE_ADD_BUTTON = true;

$id_val = "";
if ($form_action === "ask-add" && !empty($id_card)) {
	$id_val = "value='$id_card'";
}

$HD_Form->AddEditPopup(
    _("CUSTOMER ID"),
    "id_card",
    "A2B_entity_card.php?popup_select=1&",
    _("Select the Customer ID"),
    "",
    4,
    _("Insert the Customer ID")
);

$HD_Form->AddEditPopup(
    _("AGENT ID"),
    "id_agent",
    "A2B_entity_agent.php?popup_select=1&",
    _("Select the Agent ID"),
    $id_val,
    4,
    _("Insert the AGENT ID ")
);

$comp_date = "";
if ($form_action === "ask-add") {
	$comp_date = date("Y-m-d H:i:s");
}

$HD_Form->AddEditElement(
    _("PAYMENT DATE"),
    "date",
    "",
    "size=40 READONLY maxlength=40 value='$comp_date'",
    10,
    _("Insert the current date")
);

$HD_Form->AddEditElement(
    _("COMMISSION AMOUNT"),
    "amount",
    _("Enter the amount in the currency base : ") . BASE_CURRENCY,
    "size=60 maxlength=10",
    12,
    _("Insert the commission amount")
);

$HD_Form->AddEditTextarea(
    _("DESCRIPTION"),
    "description",
    "",
    "cols='50' rows='4'",
    null,
    _("Insert the description")
);

$HD_Form->AddEditPopup(
    _("PAYMENT ID"),
    "id_payment",
    "A2B_entity_payment.php?popup_select=1&",
    _("Select the Payment ID"),
    $id_val,
    4,
    _("Insert the Payment ID ")
);

$HD_Form->AddEditSelect(
    _("COMMISSION TYPE"),
    "commission_type",
    $com_list,
    "",
    _("Define the type of this commission.")
);

$HD_Form->FG_ADDITIONAL_FUNCTION_AFTER_ADD = "processing_commission_add";
$HD_Form->FG_INTRO_TEXT_ADITION = "";

$HD_Form->FG_LOCATION_AFTER_ADD = "?id=";
$HD_Form->FG_LOCATION_AFTER_EDIT = "?id=";
$HD_Form->FG_LOCATION_AFTER_DELETE = "?id=";
