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

$menu_section = 10;
require_once __DIR__ . "/../../common/lib/admin.defines.php";
require_once __DIR__ . "/form_data/FG_var_moneysituation.inc";
/**
 * @var FormHandler $HD_Form
 */

Admin::checkPageAccess(Admin::ACX_BILLING);

$HD_Form->init();

$form_action ??= "list";
$list = $HD_Form->perform_action($form_action);

require_once __DIR__ . "/../templates/main.php";

$HD_Form->create_toppage($form_action);
$HD_Form->create_form($form_action, $list);

if (count($list) > 0) {
    $nb_month = 5;
    $checkdate = (new DateTime("first day of $nb_month months ago"))->format("Y-m-d");
    $QUERY_INVOICE_ENOUGH_PAID = <<< SQL
        SELECT EXTRACT(MONTH FROM sub.invoice_date) AS mo, SUM(sub.has_paid) AS ct
        FROM (
            SELECT cc_invoice.date AS invoice_date, 
                IF (COALESCE(SUM(cc_invoice_item.price * (1 + (cc_invoice_item.vat / 100))), 0) <= COALESCE(SUM(cc_logpayment.payment), 0),
                    1,
                    0
                ) AS has_paid
            FROM cc_invoice 
                LEFT JOIN cc_invoice_item ON cc_invoice_item.id_invoice = cc_invoice.id 
                LEFT JOIN cc_invoice_payment ON cc_invoice_payment.id_invoice = cc_invoice.id
                LEFT JOIN cc_logpayment ON cc_invoice_payment.id_payment = cc_logpayment.id
            WHERE cc_invoice.date >= ? AND cc_invoice.date <= CURRENT_TIMESTAMP
            GROUP BY cc_invoice.id
        ) AS sub
        GROUP BY EXTRACT(MONTH FROM sub.invoice_date)
        ORDER BY sub.invoice_date DESC
        SQL;
    $result_invoice_enough_paid = $HD_Form->DBHandle->GetArray($QUERY_INVOICE_ENOUGH_PAID, [$checkdate]) ?: [];

    $QUERY_INVOICE_COUNT = <<< SQL
        SELECT EXTRACT(MONTH FROM cc_invoice.date) AS mo,
            COUNT(*) AS total_ct,
            SUM(CASE paid_status WHEN 0 THEN 1 ELSE 0 END) AS unpaid_ct,
            SUM(CASE paid_status WHEN 1 THEN 1 ELSE 0 END) AS paid_ct
        FROM cc_invoice
        WHERE cc_invoice.date >= ?
            AND cc_invoice.date <= CURRENT_TIMESTAMP
        GROUP BY EXTRACT(MONTH FROM cc_invoice.date)
        ORDER BY cc_invoice.date DESC
        SQL;
    $result_invoice_count = $HD_Form->DBHandle->GetArray($QUERY_INVOICE_COUNT, [$checkdate]) ?: [];

    $table_data = [];
    for ($i = 0; $i <= $nb_month; $i++) {
        $dt = new DateTime("$i months ago");
        $mo = (int)$dt->format("m");
        $ct_row = array_values(
            array_filter($result_invoice_count, fn ($v) => (int)$v["mo"] === $mo)
        );
        $table_data[] = [
            $dt->format("F"),
            $ct_row[0]["total_ct"] ?? 0,
            array_values(
                array_filter($result_invoice_enough_paid, fn ($v) => (int)$v["mo"] === $mo)
            )[0]["ct"] ?? 0,
            $ct_row[0]["paid_ct"] ?? 0,
            $ct_row[0]["unpaid_ct"] ?? 0,
        ];
    }
?>
<div class="d-flex justify-content-end">
    <table class="table table-sm w-50" style="table-layout:fixed">
        <thead>
            <tr>
                <td></td>
                <th scope="col"><?= _("Invoices") ?></th>
                <th scope="col"><?= abbr(_("Enough"), _("Invoices with enough payment")) ?></th>
                <th scope="col"><?= _("Paid") ?></th>
                <th scope="col"><?= _("Unpaid") ?></th>
            </tr>
        </thead>
        <tbody class="table-group-divider">
    <?php foreach ($table_data as $row): ?>
            <tr>
                <th scope="row"><?= $row[0] ?></th>
                <td><?= $row[1] ?></td>
                <td class="table-success"><?= $row[2] ?></td>
                <td class="table-secondary"><?= $row[3] ?></td>
                <td class="table-danger"><?= $row[4] ?></td>
            </tr>
    <?php endforeach ?>
        </tbody>
    </table>
</div>
<?php
}
require_once __DIR__ . "/../templates/footer.php";
