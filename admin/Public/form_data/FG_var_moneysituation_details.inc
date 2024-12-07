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

getpost_ifset(["id", "type"]);
/**
 * @var numeric-string|null $id
 * @var string|null $type
 */

$type ??= "refill";

if ($type === "payment"){
	$HD_Form = new FormHandler("cc_logpayment", _("Payment"));
    $HD_Form->list_query_conditions["card_id"] = $id;
	$HD_Form -> FG_TABLE_DEFAULT_SENS = "DESC";

	$HD_Form->AddListValue(_("Date"), "date");
	$HD_Form->AddListValue(_("Amount"), "payment");
	$HD_Form->FieldViewElement (["date", "payment"]);
} else {
    $join = "(SELECT id_invoice, SUM(price * (1 + (vat / 100))) AS total_price FROM cc_invoice_item GROUP BY id_invoice) AS items";
	$HD_Form = new FormHandler(
        "cc_invoice",
        _("Invoice"),
        "cc_invoice.id",
        [
            "$join" => ["items.id_invoice", "cc_invoice.id"]
        ]
    );
	$HD_Form -> FG_TABLE_DEFAULT_SENS = "DESC";
    $HD_Form->list_query_conditions["cc_invoice.id_card"] = $id;

	$HD_Form->AddListValue(_("Date"), "date");
	$HD_Form->AddListValue(_("Amount (incl VAT)"), "total_price", "display_money");
	$HD_Form-> FieldViewElement (["date", "total_price"]);
}
