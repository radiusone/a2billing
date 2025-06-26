<?php

use A2billing\Customer;
use A2billing\Payments\Receipt;
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

getpost_ifset(["id"]);
/**
 * @var numeric-string $id
 * @var numeric-string $popup_select
 */

if (empty($id)) {
    header("Location: A2B_entity_receipt.php");
}
$receipt = new Receipt($id);
if ($receipt->getCard() != $_SESSION["card_id"]) {
    header("HTTP/1.0 401 Unauthorized");
    header("Location: PP_error.php?c=accessdenied");
    die();
}
//load customer
$card = (new Table("cc_card", "*", ["cc_country" => ["country", "countrycode"]]))
    ->getRow(["cc_card.id" => $_SESSION["card_id"]]);

if (empty($card)) {
    echo "Customer doesn't exist or is not correctly defined for this receipt !";
    die();
}

require_once __DIR__ . "/templates/main.php";
//Load receipt conf
$table = new Table(
    "cc_config",
    ["config_key", "config_value"],
    ["cc_config_group" => ["cc_config.config_group_id", "cc_config_group.id"]]
);
$receipt_conf = $table->getColumn(
    "config_value",
    "config_key",
    ["group_title" => "invoice"]
);

//Currencies check
$curr = $card['currency'];
$total = 0;

?>
    <div class="mx-auto position-relative invoice-wrapper" style="width: 210mm; height: 297mm">
        <div class="row mb-3 justify-content-between">
            <div class="col-5 align-self-top">
                <div class="h4 mb-auto text-uppercase">
                    <?= _("Receipt") ?>
                <?php if (!$popup_select): ?>
                    <a href="" class="popup_trigger me-2" data-uri-extra="&id=<?= $id ?>&curr=<?= $curr ?>&popup_select=1" aria-label="<?= _("Print") ?>"><span class="bi bi-16 bi-printer-fill" aria-hidden="true"></span></a>
                    <a href="A2B_receipt_detail.php" class="popup_trigger" data-uri-extra="&id=<?= $id ?>" aria-label="<?= _("Details") ?>"><span class="bi bi-16 bi-search text-info" aria-hidden="true"></span></a>
                <?php else: ?>
                    <a href="javascript:window.print()" class="d-print-none"><span class="bi bi-16 bi-printer-fill" aria-hidden="true"></span></a>
                <?php endif ?>
                </div>
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
                <div class="company-name"><?= $receipt_conf["company_name"] ?></div>
                <div class="address"><span class="street"><?= $receipt_conf["address"] ?></span></div>
                <div class="zipcode-city">
                    <span class="city"><?= $receipt_conf["city"] ?></span>
                    <span class="state"><?= $receipt_conf["state"] ?></span>
                    <span class="zipcode"><?= $receipt_conf["zipcode"] ?></span>
                </div>
                <div class="country"><?= $receipt_conf["country"] ?></div>
                <div class="tel"><?= $receipt_conf["phone"] ?></div>
                <div class="email"><?= $receipt_conf["email"] ?></div>
                <div class="web"><?= $receipt_conf["web"] ?></div>
                <div class="vat-number"><?= sprintf(_("VAT no. %s"), $receipt_conf["vat"]) ?></div>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-4">
                <strong><?= _("Date") ?></strong>
                <div><?= $receipt->getDate() ?></div>
            </div>
            <?php if ($receipt_conf["display_account"]): ?>
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
                <?php $total += ($rndprice = round($item->price, 2, PHP_ROUND_HALF_UP)) ?>
                <tr>
                    <td></td>
                    <td><?= $item->getDate() ?></td>
                    <td class="description"><?= $item->description ?></td>
                    <td>
                        <?= get_money(convert_currency($rndprice, BASE_CURRENCY, $curr), null, $curr) ?>
                    </td>
                </tr>
            <?php endforeach ?>
            </tbody>
            <tfoot class="table-group-divider">
            <tr>
                <th scope="row"><?= _("Total") ?></th>
                <td colspan="2"></td>
                <td><?= get_money(convert_currency($total, BASE_CURRENCY, $curr), null, $curr) ?></td>
            </tr>
            </tfoot>
        </table>
        <div class="row mb-3 additional-information">
            <div class="col invoice-description">
                <?= $receipt->description ?>
            </div>
        </div>
        <div class="row footer position-absolute bottom-0 w-100">
            <div class="col small text-center">
                <?= $receipt_conf["company_name"] ?>
                <span aria-hidden="true"> | </span>
                <?= $receipt_conf["address"] ?>
                <?= $receipt_conf["city"] ?>
                <?= $receipt_conf["zipcode"] ?>
                <?= $receipt_conf["country"] ?>
                <span aria-hidden="true"> | </span>
                <?= sprintf(_("VAT no. %s"), $receipt_conf["vat"]) ?>
            </div>
        </div>
    </div>

<?php

require_once __DIR__ . "/templates/footer.php";
