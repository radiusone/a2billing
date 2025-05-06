<?php

use A2billing\Admin;
use A2billing\Payments\Invoice;

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

$menu_section = 11;
require_once __DIR__ . "/../../common/lib/admin.defines.php";

Admin::checkPageAccess(Admin::ACX_INVOICING);

getpost_ifset(["id", "addpayment", "delpayment", "status"]);
/**
 * @var numeric-string $id
 * @var numeric-string $addpayment
 * @var numeric-string $delpayment
 * @var numeric-string $status
 */

if (empty($id)) {
    header("Location: A2B_entity_invoice.php");
}

$invoice = new Invoice($id);

if (is_numeric($addpayment ?? null)) {
    $invoice->addPayment($addpayment);
    header("Location: A2B_invoice_manage_payment.php?id=$id");
}

if (is_numeric($delpayment ?? null)) {
    $invoice->delPayment($delpayment);
    header("Location: A2B_invoice_manage_payment.php?id=$id");
}

if (is_numeric($status ?? null)) {
    $invoice->changeStatus($status);
    header("Location: A2B_invoice_manage_payment.php?id=$id");
}

$vat_array = $invoice->getTotalVat();

$payments = $invoice->loadPayments();
$payment_assigned = 0;
foreach ($payments as $payment) {
    $payment_assigned += round($payment["payment"], 2, PHP_ROUND_HALF_UP);
}

require_once __DIR__ . "/../templates/main.php";
?>
<div class="row mb-3">
    <div class="col-8">
        <?= _("Invoice:") ?> <?= $invoice->title ?>
    </div>
    <div class="col">
        <?= _("Reference:") ?> <?= $invoice->reference ?>
    </div>
    <div class="col-1">
        <button class="btn" id="imp_popupselect">
            <span class="bi bi-16 bi-printer" aria-label="<?= _("Print") ?>"></span>
        </button>
    </div>
</div>

<table class="table table-sm">
    <tr>
        <th scope="row"><?= _("Paid status") ?></th>
        <td class="<?= $invoice->paid_status === Invoice::PAIDSTATUS_UNPAID ? "text-danger" : "text-success" ?>">
            <?= $invoice->getPaidStatusDisplay() ?>
            <button class="btn btn-primary badge" id="changestatus"><?= _("Change status") ?></button>
        </td>
    </tr>
    <tr>
        <th scope="row"><?= _("Total excluding VAT") ?></th>
        <td><?= get_money($invoice->getTotalPrice()) ?></td>
    </tr>
    <tr>
        <th scope="row" rowspan="<?= count($vat_array) ?>"><?= _("Total VAT") ?></th>
        <?php foreach ($vat_array as $key => $val): ?><td><?= get_money($val) ?> @<?= get_percent((float)$key) ?></td><?php endforeach ?>
    </tr>
    <tr>
        <th scope="row"><?= _("Total including VAT") ?></th>
        <td><?= get_money($invoice->getTotalAmount()) ?></td>
    </tr>
    <tr>
        <th scope="row"><?= _("Total payments assigned") ?></th>
        <td><?= get_money($payment_assigned) ?></td>
    </tr>
</table>

<div class="row mb-3 justify-content-center flex-column text-center">
    <label for="payment" class="form-label"><?= _("Payments assigned") ?></label>
    <select id="payment" name="payment" size="5" class="form-select">
    <?php foreach ($payments as $payment): ?>
        <option value="<?php echo $payment["id"] ?>">
            <?= sprintf("%s&nbsp;&nbsp;%s&nbsp;&nbsp;(ID:&nbsp;%d)", substr($payment["date"],0,10), get_money($payment["payment"]), $payment["id"]) ?>
        </option>
    <?php endforeach ?>
    </select>
    <div>
        <button class="btn" id="addpayment"><span class="bi bi-16 bi-plus-circle-fill text-success" aria-label="<?= _("Add Payment") ?>" title="<?= _("Add Payment") ?>"></span></button>
        <button class="btn" id="delpayment"><span class="bi bi-16 bi-dash-circle-fill text-danger" aria-label="<?= _("Delete Payment") ?>" title="<?= _("Delete Payment") ?>"></span></button>
    </div>
</div>

<script>
    $(function() {
        let id = <?= json_encode($id) ?>;
        let card = <?= json_encode($invoice->getCard()) ?>;
        let status = <?= ($invoice->getPaidStatus() + 1) % 2 ?>; // converts 0 to 1 and 1 to 0
        let popup = "scrollbars=yes,resizable=yes,width=700,height=500";
        $("#addpayment").on("click", function() {
            window.open(`A2B_entity_payment_invoice.php?popup_select=1&invoice=${id}&card=${card}`, '', popup)
        });
        $("#delpayment").on("click", function() {
            let p = $("#payment").val();
            if (p) {
                self.location.href = `A2B_invoice_manage_payment.php?id=${id}&delpayment=${p}`;
            }
        });
        $("#changestatus").on('click', function() {
            self.location.href = `A2B_invoice_manage_payment.php?id=${id}&status=${status}`
        });
        $("#imp_popupselect").on('click', function() {
            window.open(`A2B_invoice_view.php?popup_select=1&id=${id}`, '', popup)
        })
    });
</script>

<?php
require_once __DIR__ . "/../templates/footer.php";
