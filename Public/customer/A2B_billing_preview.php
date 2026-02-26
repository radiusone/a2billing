<?php

use A2billing\Customer;
use A2billing\Payments\Invoice;
use A2billing\Payments\InvoiceItem;
use A2billing\Payments\Receipt;
use A2billing\Payments\ReceiptItem;
use A2billing\Table;

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of A2Billing (http://www.a2billing.net/)
 *
 * A2Billing, Commercial Open Source Telecom Billing platform,
 * powered by Star2billing S.L. <http://www.star2billing.com/>
 *
 * @copyright   Copyright © 2004-2015 - Star2billing S.L.
 * @copyright   Copyright © 2022-2025 RadiusOne Inc.
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

$menu_section = 5;
require_once __DIR__ . "/../common/lib/customer.defines.php";

Customer::checkPageAccess(Customer::ACX_INVOICES);

$card_id = Customer::id();

$card = (new Table("cc_card", ["*"], ["cc_country" => ["country", "countrycode"]]))
    ->getRow(["cc_card.id" => $card_id]);

$vat = $card["vat"];
$typepaid = $card["typepaid"];
$credit = $card["credit"];

//find the last billing
$now = (new DateTimeImmutable())->format("Y-m-d H:i:s");
$last_bill_date = (new Table('cc_billing_customer', ["date"]))
    ->getValue(["id_card" => $card_id], ["date"], "desc");
$clause_call_billing = ["card_id" => $card_id];
$clause_charge = ["id_cc_card" => $card_id];

if (!empty($last_bill_date)) {
    $clause_call_billing["stoptime"] = ["BETWEEN", [$last_bill_date, $now]];
    $clause_charge["creationdate"] = ["BETWEEN", [$last_bill_date, $now]];
    $desc_billing = sprintf(_("Cost of calls between %s and %s"), Customer::date($last_bill_date)->format("Y-m-d H:i:s"), Customer::date($now)->format("Y-m-d H:i:s"));
    $desc_billing_postpaid = sprintf(_("Amount for period between %s and %s"), Customer::date($now)->format("Y-m-d H:i:s"), Customer::date($last_bill_date)->format("Y-m-d H:i:s"));
} else {
    $clause_call_billing["stoptime"] = ["<", $now];
    $clause_charge["creationdate"] = ["<", $now];
    $desc_billing = sprintf(_("Cost of calls before %s"), Customer::date($now)->format("Y-m-d H:i:s"));
    $desc_billing_postpaid = "";
}
$calls_price = (new Table('cc_call', ['COALESCE(SUM(sessionbill),0)']))
    ->getValue($clause_call_billing);
$receipt = Receipt::create($card_id, _("Summary of the charge charged since the last billing."));
$invoice = Invoice::create($card_id, _("This invoice is for some charges unpaid since the last billing, and for the negative balance."));

// COMMON BEHAVIOUR FOR PREPAID AND POSTPAID ... GENERATE A RECEIPT FOR THE CALLS OF THE MONTH
if ($calls_price) {
    $receipt->items[] = ReceiptItem::create(null, $desc_billing, floatval($calls_price), $now, 'CALLS');
}

$table_charge = new Table("cc_charge", ["description", "creationdate", "amount", "charged_status", "invoiced_status"]);
$result =  $table_charge->getRows($clause_charge);
foreach ($result as $charge) {
    if ((int)$charge["charged_status"] === 1) {
        // GENERATE RECEIPT FOR CHARGE ALREADY CHARGED
        $receipt->items[] = ReceiptItem::create(
            null,
            gettext("CHARGE :") . $charge['description'],
            floatval($charge['amount']),
            $charge['creationdate'],
        );
    } elseif ((int)$charge["charged_status"] === 0 && (int)$charge["invoiced_status"] === 0) {
        // GENERATE RECEIPT FOR CHARGE NOT CHARGED YET
        $invoice->items[] = InvoiceItem::create(
            null,
            gettext("CHARGE :") . $charge['description'],
            $charge['creationdate'],
            floatval($charge['amount']),
            floatval($vat),
        );
    }
}
// behaviour postpaid
if ((int)$typepaid === 1 && $credit < 0) {
    //GENERATE AN INVOICE TO COMPLETE THE BALANCE
    $amount = abs($credit);
    $invoice->items[] = InvoiceItem::create(
        null,
        $desc_billing_postpaid,
        Customer::date($now)->format("Y-m-d H:i:s"),
        floatval($amount),
        floatval($vat),
        'POSTPAID'
    );
}

$table = new Table(
    "cc_config",
    ["config_value", "config_key"],
    ["cc_config_group" => ["cc_config.config_group_id", "cc_config_group.id"]]
);
$invoice_conf = $table->getColumn(["group_title" => "invoice"]);

require_once __DIR__ . "/templates/main.php";

$curr = $_SESSION['currency'];
?>
<?php if (count($receipt->items) > 0): ?>
<div class="row">
    <div class="col">
        <h4>
            <?= _("Preview Next Receipt") ?>
            <button type="button" class="btn receipt-preview-detail" aria-label="<?= _("View detailed charges") ?>">
                <span class="bi bi-16 bi-search text-info" aria-hidden="true"></span>
            </button>
        </h4>
    </div>
</div>

<div class="mx-auto position-relative invoice-wrapper" style="width: 210mm; height: 297mm">
    <div class="row mb-3 justify-content-between">
        <div class="col-5 align-self-center">
            <div class="h4 mb-auto text-uppercase"><?= _("Invoice") ?></div>
            <div class="company-name"><?= $card["company_name"] ?></div>
            <div class="fullname"><?= $card["firstname"]?> <?= $card["lastname"]?></div>
            <div class="address"><span class="street"><?= $card["address"] ?></span></div>
            <div class="zipcode-city">
                <span class="city"><?= $card["city"] ?></span>
                <span class="state"><?= $card["state"] ?></span>
                <span class="zipcode"><?= $card["zipcode"] ?></span>
            </div>
            <div class="country"><?= $card["countryname"] ?></div>
            <?php if ($card["vat_rn"]): ?>
                <div class="vat-number"><?= sprintf(_("VAT no. %s"), $card["vat_rn"]) ?></div>
            <?php endif ?>
        </div>
        <div class="col-5 align-self-center text-end">
            <div class="company-name"><?= $invoice_conf["company_name"] ?></div>
            <div class="address"><span class="street"><?= $invoice_conf["address"] ?></span></div>
            <div class="zipcode-city">
                <span class="city"><?= $invoice_conf["city"] ?></span>
                <span class="state"><?= $invoice_conf["state"] ?></span>
                <span class="zipcode"><?= $invoice_conf["zipcode"] ?></span>
            </div>
            <div class="country"><?= $invoice_conf["country"] ?></div>
            <div class="tel"><?= $invoice_conf["phone"] ?></div>
            <div class="email"><?= $invoice_conf["email"] ?></div>
            <div class="web"><?= $invoice_conf["web"] ?></div>
            <div class="vat-number"><?= sprintf(_("VAT no. %s"), $invoice_conf["vat"]) ?></div>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-4">
            <strong><?= _("Date") ?></strong>
            <div><?= $receipt->getDate() ?></div>
        </div>
        <?php if ($invoice_conf["display_account"]): ?>
            <div class="col-4">
                <strong><?= _("Client account") ?></strong>
                <div><?= $card["username"] ?></div>
            </div>
        <?php endif ?>
    </div>
    <table class="table table-sm mb-3 table-striped invoice-details">
        <thead>
        <tr>
            <td></td>
            <th><?= _("Date") ?></th>
            <th class="description"><?= _("Description") ?></th>
            <th><?= _("Price") ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($receipt->items as $item): ?>
            <tr>
                <td></td>
                <td><?= $item->getDate() ?></td>
                <td class="description"><?= $item->getDescription() ?></td>
                <td>
                    <?= get_money(convert_currency($item->getPrice(), BASE_CURRENCY, $curr), null, $curr) ?>
                </td>
            </tr>
        <?php endforeach ?>
        </tbody>
        <tfoot class="table-group-divider">
        <tr>
            <th scope="row"><?= _("Total") ?></th>
            <td colspan="2"></td>
            <td><?= get_money(convert_currency($receipt->getTotalPrice(), BASE_CURRENCY, $curr), null, $curr) ?></td>
        </tr>
        </tfoot>
    </table>
    <div class="row mb-3 additional-information">
        <div class="col invoice-description">
            <?= $receipt->description ?>
        </div>
    </div>
</div>
<?php endif ?>

<?php if (count($invoice->items) > 0): ?>
<div class="row">
    <div class="col">
        <h4><?= _("Preview Next Invoice") ?></h4>
    </div>
</div>
<div class="mx-auto position-relative invoice-wrapper" style="width: 210mm; height: 297mm">
    <table class="table table-sm mb-3 table-striped invoice-details">
        <thead>
        <tr>
            <td></td>
            <th><?= _("Date") ?></th>
            <th class="description"><?= _("Description") ?></th>
            <th><?= _("Price ex VAT") ?></th>
            <th><?= _("VAT") ?></th>
            <th><?= _("Total price") ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($invoice->items as $item): ?>
            <tr>
                <td></td>
                <td><?= $item->getDate() ?></td>
                <td class="description"><?= $item->description ?></td>
                <td>
                    <?= get_money(convert_currency($item->getPrice(), BASE_CURRENCY, $curr), null, $curr) ?>
                </td>
                <td><?= get_percent($item->getVatRate()) ?></td>
                <td>
                    <?= get_money(convert_currency($item->getTotalPrice(), BASE_CURRENCY, $curr), null, $curr) ?>
                </td>
            </tr>
        <?php endforeach ?>
        </tbody>
        <tfoot class="table-group-divider">
        <tr>
            <th scope="row"><?= _("Totals") ?></th>
            <td colspan="2"></td>
            <td><?= get_money(convert_currency($invoice->getTotalPrice(), BASE_CURRENCY, $curr), null, $curr) ?></td>
            <td>
                <?php foreach ($invoice->getTotalVat() as $per => $vatamt): ?>
                    <?= sprintf("VAT %s", get_percent((float)$per)) ?>
                    <?= get_money(convert_currency($vatamt, BASE_CURRENCY, $curr), null, $curr) ?><br/>
                <?php endforeach ?>
            </td>
            <td><?= get_money(convert_currency($invoice->getTotalAmount(), BASE_CURRENCY, $curr), null, $curr) ?></td>
        </tr>
        </tfoot>
    </table>
    <div class="row mb-3 additional-information">
        <div class="col invoice-description">
            <?= $invoice->description ?>
        </div>
    </div>
</div>
<?php endif ?>

<script>
    document.querySelector(".btn.receipt-preview-detail")
        .addEventListener("click", function () {
            window.open("A2B_receipt_preview_detail.php?popup_select=1", "previewdetail", "scrollbars=yes,resizable=yes,width=700,height=500");
        });
</script>

<?php
require_once __DIR__ . "/templates/footer.php";
