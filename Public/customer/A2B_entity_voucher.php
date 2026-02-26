<?php

use A2billing\Customer;
use A2billing\Forms\FormHandler;
use A2billing\Table;

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
 */

require_once __DIR__ . "/../../common/lib/customer.defines.php";
require_once __DIR__ . "/../../common/form_data/FG_var_voucher.inc";
/**
 * @var FormHandler $HD_Form
 */

Customer::checkPageAccess(Customer::ACX_VOUCHER);

getpost_ifset(["voucher"]);
/**
 * @var numeric-string|null $voucher
 */
$HD_Form -> init();
$currencies_list = get_currencies();
$success = "";
$error = "";

if (!empty($voucher)) {
    $result = (new Table("cc_voucher", ["currency", "credit"]))
        ->getRow(["expirationdate" =>  [">=", "CURRENT_TIMESTAMP"], "available" => 1, "voucher" => $voucher]);

    if ($result) {
        $credit = convert_currency($result["credit"], $result["currency"], BASE_CURRENCY);
        (new Table("cc_voucher"))
            ->updateRow(["available" => 0, "usedcardnumber" => Customer::card(), "usedate" => "CURRENT_TIMESTAMP"], ["voucher" => $voucher]);
        (new Table("cc_card"))
            ->updateRow(["credit" => ["credit + ?", $credit]], ["username" => Customer::card()]);
        $success = sprintf(_("The voucher %s has been processed; we added %s to your account"), $voucher, get_money($credit));
    } else {
        sleep(2);
        $error = _("Invalid voucher");
    }
}

$form_action ??= "list";
$list = $HD_Form->perform_action($form_action);

require_once __DIR__ . "/templates/main.php";

$HD_Form->create_toppage ($form_action);
?>

<?php if ($success): ?>
    <div class="row mb-3"><div class="col"><p class="alert alert-success"><?= $success ?></p></div></div>
<?php endif ?>
<?php if ($error): ?>
    <div class="row mb-3"><div class="col"><p class="alert alert-danger"><?= $error ?></p></div></div>
<?php endif ?>
<form class="row mb-3">
    <label for="voucher" class="col-2 col-form-label-sm"><?= _("Voucher") ?></label>
    <div class="col-8">
        <input type="text" name="voucher" id="voucher" class="form-control form-control-sm w-100" value="<?= $voucher ?? "" ?>" pattern="[0-9]{8,20}"/>
    </div>
    <div class="col-2">
        <button type="submit" class="btn btn-primary btn-sm"><?= _("Use Voucher") ?></button>
    </div>
</form>

<?php
$HD_Form->create_form($form_action, $list);

require_once __DIR__ . "/templates/footer.php";
