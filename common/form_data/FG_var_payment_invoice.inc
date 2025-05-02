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

/**
 * @var numeric-string $popup_select
 * @var string $popup_formname
 * @var string $popup_fieldname
 */

getpost_ifset(["card", "invoice"]);
/**
 * @var string $card
 * @var string $invoice
 */

$HD_Form = new FormHandler(
    "cc_logpayment",
    _("Payment"),
    "id",
    ["cc_invoice_payment" => ["cc_logpayment.id", "cc_invoice_payment.id_payment"]]
);

$HD_Form->list_query_order_columns = ["date"];
$HD_Form->list_query_order_direction = "DESC";
if (!empty($card)) {
    // only include this user's payments
    $HD_Form->list_query_conditions["card_id"] = $card;
}
if (!empty($invoice)) {
    // don't include ones already applied
    $HD_Form->list_query_conditions["id_invoice"] = null;
}

$HD_Form->AddListValue(_("Date"), "date");
$HD_Form->AddListValue(_("Amount"), "payment", "get_money");
$HD_Form->AddListValue(_("Description"), "description");
$HD_Form->AddListMapping(_("Type"), "payment_type", getRefillType_List());

$HD_Form->AddListSelectActionButton(
    "javascript:gotoAddPage('|param|')",
    _("ADD")
);

$HD_Form->CV_FOLLOWPARAMETERS = array_filter(["invoice" => $invoice ?? ""]);
