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

Admin::checkPageAccess(Admin::ACX_ADMINISTRATOR);

$HD_Form = new FormHandler(
    "cc_card",
    _("Card"),
    "cc_card.id",
    [
        "cc_invoice" => ["cc_card.id", "cc_invoice.id_card"],
        "cc_invoice_item" => ["cc_invoice.id", "cc_invoice_item.id_invoice"],
        "cc_logpayment" => ["cc_card.id", "cc_logpayment.card_id"]
    ],
);

$HD_Form->FG_QUERY_GROUPBY_COLUMNS = ["cc_card.id"];
$HD_Form->FG_QUERY_ORDERBY_COLUMNS = ["cc_card.id"];

$HD_Form->help_text = create_help(
    _("This screen shows refills and payments made against each account, along with the current credit on each account. The initial amount of credit applied to the account is not included. The amount owing is calculated by subtracting payments from refills"),
    'MoneySituation'
);

$HD_Form->AddListValue(_("ID"), "cc_card.id");
$HD_Form->AddListValue(_("Card number"), "username", "display_customer_link");
$HD_Form->AddListValue(_("Alias"), "useralias");
$HD_Form->AddListValue(_("First name"), "firstname");
$HD_Form->AddListValue(_("Last name"), "lastname");
$HD_Form->AddListValue(_("Credit"), "credit", "display_money");
$HD_Form->AddListValue(_("Invoiced"), "invoiced", "display_money");
$HD_Form->AddListValue(_("Paid"), "paid", "display_money");
$HD_Form->AddListValue(_("To pay"), "to_pay", "sub_money", ["%6", "%7"]);

$HD_Form->FieldViewElement([
    "cc_card.id",
    "username",
    "useralias",
    "firstname",
    "lastname",
    "credit",
    "SUM(cc_invoice_item.price * (1 + (cc_invoice_item.vat / 100))) AS invoiced",
    "SUM(cc_logpayment.payment) AS paid",
    "cc_card.id",
]);

$HD_Form->AddListActionButton(
    "javascript:window.open('A2B_entity_moneysituation_details.php?type=invoice&form_action=list&displayheader=0&popup_select=1&id=|param|','','scrollbars=yes,resizable=yes,width=500,height=270')",
    _("INVOICE"),
    ""
);
$HD_Form->AddListActionButton(
    "javascript:window.open('A2B_entity_moneysituation_details.php?type=payment&form_action=list&displayheader=0&popup_select=1&id=|param|','','scrollbars=yes,resizable=yes,width=500,height=270')",
    _("PAYMENT"),
    ""
);

$HD_Form->FG_FILTER_ENABLE = true;
$HD_Form->FG_FILTER_COLUMN = "username";
$HD_Form->FG_FILTER_LABEL = "cardnumber";
