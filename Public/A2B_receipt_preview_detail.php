<?php

use A2billing\Customer;
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

getpost_ifset(["page"]);
/**
 * @var numeric-string|null $page
 */

$page = intval($page ?? 1);

/**
 * These functions basically just fake the methods in A2billing\Receipt because there isn't an actual receipt
 *
 * @param string|null $startdate
 * @param int $begin
 * @param int $nb
 * @return list<ReceiptItem>
 */
function loadDetailledItems(?string $startdate = null, int $begin = 0, int $nb = 0): array
{
    $result = [];
    $card_id = $_SESSION["card_id"];

    $call_table = new Table("cc_call", ["starttime AS itemdate", "sessiontime", "calledstation", "sessionbill"]);
    $call_clause = ["card_id" => $card_id];
    if(!empty($startdate)) {
        $call_clause["stoptime"] = [">=", $startdate];
    }
    $return_calls = $call_table->getRows($call_clause);
    foreach ($return_calls as $call) {
        $result[] = ReceiptItem::create(
            null,
            sprintf(_("Call to %s for %s"), format_phone_number($call['calledstation']), get_timespan($call["sessiontime"])),
            floatval($call["sessionbill"]),
            $call["itemdate"],
        );
    }

    $charge_table = new Table("cc_charge", ["description", "creationdate AS itemdate", "amount"]);
    $clause_charge = ["id_cc_card" => $card_id, "charged_status" => 1];
    if(!empty($startdate)) {
        $clause_charge["itemdate"] = [">=", $startdate];
    }
    $return_charges = $charge_table->getRows($clause_charge);
    foreach ($return_charges as $charge) {
        $result[] = ReceiptItem::create(
            null,
            sprintf(_("Charge: %s"), $charge['description']),
            $charge['itemdate'],
            floatval($charge['amount']),
            'CHARGE'
        );
    }
    //sort r�sult by date
    usort($result, fn (ReceiptItem $a, ReceiptItem $b) => $a->getDate() <=> $b->getDate());

    return array_slice($result, $begin, $nb);
}

function nbDetailledItems(?string $startdate): int
{
    $card_id = $_SESSION["card_id"];
    $call_table = new Table("cc_call");
    $call_clause = ["card_id" => $card_id];
    if(!empty($startdate)) {
        $call_clause["stoptime"] = [">=", $startdate];
    }
    $i = $call_table->countRows($call_clause);

    $charge_table = new Table("cc_charge");
    $clause_charge = ["id_cc_card" => $card_id, "charged_status" => 1];
    if(!empty($startdate)) {
        $clause_charge["creationdate"] = [">=", $startdate];
    }
    $i += $charge_table->countRows($clause_charge);

    return $i;
}

function SumDetailledItems(?string $startdate): int
{
    $card_id = $_SESSION["card_id"];
    $call_table = new Table("cc_call", ["SUM(sessionbill)"]);
    $call_clause = ["card_id" => $card_id];
    if(!empty($startdate)) {
        $call_clause["stoptime"] = [">=", $startdate];
    }
    $i = intval($call_table->getValue($call_clause) ?? 0);

    $charge_table = new Table("cc_charge", ["SUM(amount)"]);
    $clause_charge = ["id_cc_card" => $card_id, "charged_status" => 1];
    if(!empty($startdate)) {
        $clause_charge["creationdate"] = [">=", $startdate];
    }
    $i += intval($charge_table->getValue($clause_charge) ?? 0);

    return $i;
}

$billing_table = new Table('cc_billing_customer', ['date']);
$start_date = $billing_table->getValue(["id_card" => $_SESSION["card_id"]], ["date"], "desc");

$nbitems = nbDetailledItems($start_date);
$nb_by_page = 100;
$nb_page = ceil($nbitems / $nb_by_page);
$items = loadDetailledItems($start_date,(($page - 1) * $nb_by_page), $nb_by_page);
$totalprice = SumDetailledItems($start_date);

require_once __DIR__ . "/templates/main.php";

$curr = $_SESSION['currency'];
$pagetotal = 0;
?>
<?php if ($nb_page > 1): ?>
<nav aria-label="<?= _("page navigation") ?>">
    <ul class="pagination justify-content-center">
        <li class="page-item <?= $page <= 1 ? "disabled" : "" ?>">
            <?php if ($page <= 1): ?>
                <span class="page-link" aria-hidden="true"><span class="bi bi-16 bi-skip-backward-fill"></span></span>
            <?php else: ?>
                <a class="page-link" href="?popup_select=1&page=1" aria-label="<?= _("First") ?>"><span class="bi bi-16 bi-skip-backward-fill" aria-hidden="true"></span></a>
            <?php endif ?>
        </li>
        <li class="page-item <?= $page === 1 ? "disabled" : "" ?>">
            <?php if ($page === 1): ?>
                <span class="page-link" aria-hidden="true"><span class="bi bi-16 bi-rewind-fill"></span></span>
            <?php else: ?>
                <a class="page-link" href="?popup_select=1&page=<?= $page - 1 ?>" aria-label="<?= _("Previous") ?>"><span class="bi bi-16 bi-rewind-fill" aria-hidden="true"></span></a>
            <?php endif ?>
        </li>
        <li class="page-item disabled"><span class="page-link"><?= sprintf(_("Page %d/%d"), $page, $nb_page) ?></span></li>
        <li class="page-item <?= $page >= $nb_page ? "disabled" : "" ?>">
            <?php if ($page >= $nb_page): ?>
                <span class="page-link" aria-hidden="true"><span class="bi bi-16 bi-fast-forward-fill"></span></span>
            <?php else: ?>
                <a class="page-link" href="?popup_select=1&page=<?= $page + 1 ?>" aria-label="<?= _("Next") ?>"><span class="bi bi-16 bi-fast-forward-fill" aria-hidden="true"></span></a>
            <?php endif ?>
        </li>
        <li class="page-item <?= $page >= $nb_page ? "disabled" : "" ?>">
            <?php if ($page >= $nb_page): ?>
                <span class="page-link" aria-hidden="true"><span class="bi bi-16 bi-skip-forward-fill"></span></span>
            <?php else: ?>
                <a class="page-link" href="?popup_select=1&page=<?= $nb_page ?>" aria-label="<?= _("Last") ?>"><span class="bi bi-16 bi-skip-forward-fill" aria-hidden="true"></span></a>
            <?php endif ?>
        </li>
    </ul>
</nav>
<?php endif ?>

<div class="row">
    <div class="col">
        <h4><?= _("Preview Next Receipt Detail") ?></h4>
    </div>
</div>

<div class="receipt-wrapper">
    <table class="table table-sm table-striped caption-top receipt-table">
        <caption>
            <strong><?= _("Client number") ?></strong> <?= $_SESSION["pr_login"] ?>
        </caption>
        <thead>
            <tr>
                <th scope="col"><?= _("Date") ?></th>
                <th scope="col"><?= _("Description") ?></th>
                <th scope="col"><?= _("Cost") ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($items as $item): ?>
            <tr>
                <td><?= $item->date ?></td>
                <td><?= $item->getDescription() ?></td>
                <td><?= get_money(convert_currency($pagetotal += $item->getPrice(), BASE_CURRENCY, $curr), null, $curr) ?></td>
            </tr>
        <?php endforeach ?>
        </tbody>
        <tfoot class="table-group-divider">
        <?php if ($nb_page > 1): ?>
            <tr>
                <th scope="row" colspan="2"><?= sprintf(_("Page %d total"), $page) ?></th>
                <td><?= get_money(convert_currency($pagetotal, BASE_CURRENCY, $curr), null, $curr) ?></td>
            </tr>
        <?php endif ?>
            <tr>
                <th scope="row" colspan="2"><?= _("Receipt total") ?></th>
                <td><?= get_money(convert_currency($totalprice, BASE_CURRENCY, $curr), null, $curr) ?></td>
            </tr>
        </tfoot>
    </table>
</div>
