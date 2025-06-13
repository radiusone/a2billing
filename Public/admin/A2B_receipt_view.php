<?php

use A2billing\Admin;
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

$menu_section = 11;
require_once __DIR__ . "/../../common/lib/admin.defines.php";

Admin::checkPageAccess(Admin::ACX_INVOICING);

getpost_ifset(["id", "curr"]);
/**
 * @var numeric-string $id
 * @var string $curr
 * @var numeric-string $popup_select
 */

$receipt = new Receipt($id ?? 0);
if (empty($receipt->card)) {
    header("Location: A2B_entity_receipt.php?form_action=list");
}
$DBHandle  = DbConnect();
$card = (new Table("cc_card", "*", ["cc_country" => ["country", "countrycode"]]))
    ->getRow(["cc_card.id" => $receipt->card]);

if (empty($card)) {
    echo "Customer doesn't exist or is not correctly defined for this receipt !";
    die();
}

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

$curr = strtoupper($curr ?? BASE_CURRENCY);
$total = 0;

require_once __DIR__ . "/templates/main.php";
?>

<?php if (!$popup_select): ?>
<div class="row mb-3">
    <?php if (strtoupper(BASE_CURRENCY) !== strtoupper($card["currency"])): ?>
    <form class="col-4" method="get">
        <div class="row">
            <label class="col-4 col-form-label" for="curr"><?= _("Currency") ?></label>
            <div class="col">
                <input type="hidden" name="id" value="<?= $id ?>"/>
                <select name="curr" id="curr" class="form-select" onchange="this.form.submit()">
                    <option value="<?= BASE_CURRENCY ?>"><?= _("System Currency") ?></option>
                    <option value="<?= $card["currency"] ?>" <?php if($curr === $card["currency"]): ?>selected="selected"<?php endif ?>><?= _("Customer Currency") ?></option>
                </select>
            </div>
        </div>
    </form>
    <?php endif ?>
    <div class="col-auto ms-auto">
        <a href="?id=<?= $id ?>&curr=<?= $curr ?>&popup_select=1" target="_blank">
            <span class="bi bi-16 bi-printer" aria-label="<?= _("Print") ?>" title="<?= _("Print") ?>"></span>
        </a>
    </div>
</div>
<?php else: ?>
<div class="row d-print-none">
    <div class="col-auto ms-auto">
        <a href="javascript:window.print()">
            <span class="bi bi-16 bi-printer" aria-label="<?= _("Print") ?>" title="<?= _("Print") ?>"></span>
        </a>
    </div>
</div>
<?php endif ?>

<div class="mx-auto position-relative invoice-wrapper" style="width: 210mm; height: 297mm">
    <div class="row mb-3 justify-content-between">
        <div class="col-5 align-self-top">
            <div class="h4 mb-auto text-uppercase"><?= _("Receipt") ?></div>
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
            <div class="tel"><?= $receipt_conf["tel"] ?></div>
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
