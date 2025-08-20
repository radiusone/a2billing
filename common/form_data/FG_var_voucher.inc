<?php

use A2billing\A2Billing;
use A2billing\Customer;
use A2billing\Forms\FormHandler;
use A2billing\Forms\Validator;

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
 */

/**
 * @var A2Billing $A2B
 */

$HD_Form = new FormHandler("cc_voucher", _("Voucher"));

$currency_list = getCurrenciesList();
$actived_list = getActivationTrueFalseList();
$used_list = [_("NOT USED"), _("USED")];

if (is_customer()) {
    $HD_Form->list_query_conditions["usedcardnumber"] = Customer::card();
    $HD_Form->list_help_text = create_help(_("Enter your voucher number to top up your card."));
} else {
    $HD_Form->AddListTopButton(null, null, "postcard-heart");

    $HD_Form->list_help_text = create_help(
        _("Listed below are the vouchers created on the system,.<br/>")
        . _("Each row corresponds to a voucher and shows it's status, value and currency..")
        . _("Create a single voucher, defining such properties as credit, tag, currency etc, click confirm when finished. <br/> The customer applies voucher credits to their account via the customer interface or via an IVR menu.")
    );
}

$HD_Form->AddListValue(_("Voucher"), "voucher");
$HD_Form->AddListValue(_("Credit"), "credit", "get_money");
$HD_Form->AddListMapping(_("Currency"), "currency", $currency_list);
$HD_Form->AddListValue(_("Used date"), "usedate");
if (!is_customer()) {
    $HD_Form->AddListMapping(_("Activated"), "activated", $actived_list);
    $HD_Form->AddListMapping(_("Used"), "used", $used_list);
    $HD_Form->AddListValue(_("Account"), "usedcardnumber");
    $HD_Form->AddListValue(_("Created date"), "creationdate");
    $HD_Form->AddListValue(_("Tag"), "tag");
}

$form_action ??= "list";

// Set the filter variables
$HD_Form->AddListFilter("voucher", _("Voucher"));

// Search form
$HD_Form->search_form_enabled = !is_customer();
$HD_Form->search_form_title = _('Define specific criteria to search for vouchers created.');
$HD_Form->AddSearchDateInput(_("Creation date"), "creationdate");
$HD_Form->AddSearchTextInput(_("Voucher"), "voucher");
$HD_Form->AddSearchTextInput(_("Card number"), "usedcardnumber");
$HD_Form->AddSearchTextInput(_("Tag"), "tag");
$HD_Form->AddSearchComparisonInput(_("Credit"), "credit");

$HD_Form->AddSearchSelectInput(_("Status"), "activated", $actived_list);
$HD_Form->AddSearchSelectInput(_("Used"), "used", $used_list);
$HD_Form->AddSearchSelectInput(_("Currency"), "currency", $currency_list);

$HD_Form->FG_ENABLE_EDIT_BUTTON = !is_customer();
$HD_Form->FG_ENABLE_DELETE_BUTTON = !is_customer();
$HD_Form->FG_ENABLE_ADD_BUTTON = !is_customer();

$vouch_attr = ["readonly" => "readonly", "maxlength" => 40];
if ($form_action === "ask-add") {
    $vouch_attr["value"] = generate_unique_value($HD_Form->FG_QUERY_TABLE_NAME, $A2B->config['global']['len_voucher'] ?? 0, "voucher");
}
$HD_Form->AddEditElement(
    _("VOUCHER"),
    "voucher",
    "",
    $vouch_attr,
    [Validator::class, "numeric"],
    _("Insert the voucher")
);

$HD_Form->AddEditElement(
    _("AMOUNT"),
    "credit",
    "",
    ["maxlength" => 30],
    [Validator::class, "number"],
    _("Insert the number of credit")
);

$HD_Form->AddEditElement(
    _("TAG"),
    "tag",
    "",
    ["maxlength" => 30],
    [Validator::class, "min2Chars"],
    _("Insert the tag"),
    "NO-NULL"
);

$HD_Form->AddEditRadio(
    _("ACTIVATED"),
    "activated",
    getActivationTrueFalseList(),
    "t",
    "",
    [],
    _("Choose if you want to activate this card")
);

$HD_Form->AddEditSelect(_("CURRENCY"), "currency", $currency_list);

$date_attr = ["maxlength" => 40];
if ($form_action === "ask-add") {
    $date_attr["value"] = (new DateTime("+10 years"))->format("Y-m-d H:i:s");
}
$HD_Form->AddEditElement(
    _("EXPIRY DATE"),
    "expirationdate",
    _("Format YYYY-MM-DD HH:MM:SS. For instance, '2004-12-31 00:00:00'"),
    $date_attr,
    [Validator::class, "dateTime"],
    _("Insert the expiration date")
);

$HD_Form->edit_message_intro = _("Modify the properties of the selected voucher.");
$HD_Form->add_message_intro = "";
