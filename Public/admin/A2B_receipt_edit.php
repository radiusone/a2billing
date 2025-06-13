<?php

use A2billing\Admin;
use A2billing\Customer;
use A2billing\Forms\Validator;
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

$menu_section = 11;
require_once __DIR__ . "/../../common/lib/admin.defines.php";

Admin::checkPageAccess(Admin::ACX_INVOICING);

getpost_ifset(["date", "id", "action", "price", "description", "vat", "idc"]);
/**
 * @var numeric-string $id
 * @var string|null $action
 * @var string $date
 * @var string $price
 * @var string $vat
 * @var string $description
 * @var numeric-string|null $idc
 */
if (empty($id)) {
    header("Location: A2B_entity_invoice.php");
}

$action ??= "";
$error_msg = "";
$receipt = new Receipt((int)$id);
$DBHandle = DbConnect();

switch ($action) {
    case "add":
    case "update":
        if (!Validator::dateTime($date)) {
            $error_msg .= _("Date inserted is invalid, it must respect a date format YYYY-MM-DD HH:MM:SS (time is optional).");
        }
        if (!Validator::number($price)) {
            $error_msg .= _("Amount inserted is invalid, it must be a number. Check the format.");
        }
        if ($error_msg) {
            break;
        }
        if ($action === "add") {
            $receipt->insertReceiptItem($description, $price);
        } elseif (!empty($idc)) {
            $item = new ReceiptItem($idc, $description, $date, $price, null);
            $item->save();
        }
        header("Location: A2B_receipt_edit.php?id=$id");
        break;

    case "edit":
        if (!empty($idc)) {
            $item = new ReceiptItem($idc);
            $description = $item->description;
            $price = $item->price;
            $date = $item->getDate();
            break;
        }
        header("Location: A2B_receipt_edit.php?id=$id");
        break;

    case "delete":
        if (!empty($idc)) {
            $table = new Table("cc_receipt_item");
            $table->deleteRow($DBHandle, ["id" => $idc]);
        }
        header("Location: A2B_receipt_edit.php?id=$id");
        break;
}

$total = 0;

require_once __DIR__ . "/templates/main.php";

?>
<div class="row mb-3">
    <div class="col-8">
        <div class="row">
            <div class="col-4 fw-bold"><?= _("Receipt:") ?></div><div class="col"><?= $receipt->title ?></div>
        </div>
        <div class="row">
            <div class="col-4 fw-bold"><?= _("For:") ?></div><div class="col"><?= Customer::getInfoLink($receipt->card) ?></div>
        </div>
        <div class="row">
            <div class="col-4 fw-bold"><?= _("Description:") ?></div>
            <div class="col"><?= $receipt->description ?></div>
        </div>
    </div>
    <div class="col-4">
        <div class="row">
            <div class="col-4 fw-bold"><?= _("Date:") ?></div><div class="col"><?= $receipt->date ?></div>
        </div>
    </div>
</div>

<table class="table table-sm table-striped">
    <thead>
    <tr>
        <td></td>
        <th><?= _("Date") ?></th>
        <th><?= _("Description") ?></th>
        <th><?= _("Price") ?></th>
        <td></td>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($receipt->items as $item): ?>
        <?php $total += ($rndprice = round($item->price, 2, PHP_ROUND_HALF_UP)); ?>
        <tr>
            <td></td>
            <td><?= $item->getDate() ?></td>
            <td><?= $item->description ?></td>
            <td><?= get_money($rndprice) ?></td>
            <td>
                <a href="?action=edit&id=<?= $id ?>&idc=<?= $item->id ?>"><span class="bi bi-16 bi-pencil-fill" aria-label="<?= _("Edit") ?>"></span></a>
                <a href="?action=delete&id=<?= $id ?>&idc=<?= $item->id ?>"><span class="bi bi-16 bi-x-circle-fill text-danger" aria-label="<?= _("Delete") ?>"></span></a>
            </td>
        </tr>
    <?php endforeach ?>
    </tbody>
    <tfoot class="table-group-divider">
    <tr>
        <th scope="row"><?= _("Totals") ?></th>
        <td colspan="2"></td>
        <td><?= get_money($total) ?></td>
        <td></td>
    </tr>
    </tfoot>
</table>

<form method="post" class="w-50">
    <div class="row mb-3">
        <div class="col h4">
            <?= _("Add receipt item") ?>
        </div>
    </div>
    <?php if (!empty($error_msg)): ?>
        <div class="alert alert-danger">
            <?= $error_msg ?>
        </div>
    <?php endif ?>
    <div class="row mb-3">
        <label class="col-4 col-form-label" for="date"><?= _("Date") ?></label>
        <div class="col">
            <input type="date" name="date" id="date" value="<?= $date ?? (new DateTime())->format("Y-m-d") ?>" class="form-control form-control-sm"/>
        </div>
    </div>
    <div class="row mb-3">
        <label class="col-4 col-form-label" for="price"><?= _("Amount") ?></label>
        <div class="col">
            <input type="text" name="price" id="price" value="<?= $price ?? "" ?>" class="form-control form-control-sm" pattern="[0-9]*([.][0-9]+)?"/>
        </div>
    </div>
    <div class="row mb-3">
        <label class="col-4 col-form-label" for="description"><?= _("Description") ?></label>
        <div class="col">
            <textarea name="description" id="description" class="form-control form-control-sm"><?= $description ?? "" ?></textarea>
        </div>
    </div>
    <div class="row">
        <div class="col-auto ms-auto">
            <input type="hidden" name="action" value="<?= empty($idc) ? "add" : "update" ?>"/>
            <input type="hidden" name="idc" value="<?= $idc ?? "" ?>"/>
            <button type="submit" class="btn btn-primary btn-sm"><?= empty($idc) ? _("Add") : _("Update") ?></button>
        </div>
    </div>
</form>
