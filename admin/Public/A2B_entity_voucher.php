<?php

use A2billing\Admin;
use A2billing\Forms\FormHandler;
use A2billing\Table;

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of A2Billing (http://www.a2billing.net/)
 *
 * A2Billing, Commercial Open Source Telecom Billing platform,
 * powered by Star2billing S.L. <http://www.star2billing.com/>
 *
 * @copyright   Copyright © 2004-2015 - Star2billing S.L.
 * @copyright   Copyright © 2022 RadiusOne Inc.
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
require_once __DIR__ . "/form_data/FG_var_voucher.inc";
/**
 * @var FormHandler $HD_Form
 * @var numeric-string $popup_select
 * @var array $used_list
 * @var array $actived_list
 */

Admin::checkPageAccess(Admin::ACX_BILLING);

$HD_Form->init();

/********************************* BATCH UPDATE ***********************************/
getpost_ifset(["action", "check", "type"]);
/**
 * @var string|null $action
 * @var array|null $check
 * @var array<string,numeric-string>|null $type
 */
$action ??= "";
$check ??= [];

// CHECK IF REQUEST OF BATCH UPDATE
if ($action === "batchupdate" && is_array($check)) {
    $HD_Form->prepare_list_subselection("list");

    $uf = [];
    getpost_ifset(
        ["upd_tag", "upd_currency", "upd_credit", "upd_activated", "upd_used", "upd_credittype"],
        $uf
    );
    $update_fields = [];
    foreach ($uf as $k => $v) {
        $k = substr($k, 4);
        $update_fields[$k] = $v;
    }

    $updates = [];
    foreach (array_keys($check) as $ch) {
        // remove "upd_"
        $col = substr($ch, 4);
        $val = $update_fields[$col] ?? null;
        if (is_null($val)) {
            continue;
        }
        if (($type[$ch] ?? 1) == 1) {
            $updates[$col] = $val;
        } elseif ($type[$ch] == 2) {
            $updates[$col] = ["`$col` + ?", $val];
        } elseif ($type[$ch] == 3) {
            $updates[$col] = ["`$col` - ?", $val];
        }
    }

    if (!(new Table("cc_voucher"))->updateRow($HD_Form->DBHandle, $updates, $HD_Form->list_query_conditions)) {
        $update_msg = _('Could not perform the batch update!');
    } else {
        $update_msg = _('The batch update has been successfully perform!');
    }
}
/********************************* END BATCH UPDATE ***********************************/

if ($action === "generate") {
    $gen = [];
    getpost_ifset(["count", "length", "credit", "currency", "expirationdate", "tag"], $gen);
    $table = new Table("cc_voucher");
    $count = $gen["count"] ?? 0;
    $length = $gen["length"] ?? 0;
    unset($gen["count"], $gen["length"]);
    for ($i = 0; $i < $count; $i++) {
        $gen["voucher"] = generate_unique_value("cc_voucher", $length, "voucher");
        $gen["usedcardnumber"] = "";
        $gen["activated"] = "t";
        if (isset($gen["expirationdate"])) {
            $gen["expirationdate"] = str_replace('T', ' ', $gen["expirationdate"]);
        }
        $table->addRow($HD_Form->DBHandle, $gen);
    }
}

$form_action ??= "list";
$list = $HD_Form->perform_action($form_action);

require_once __DIR__ . "/../templates/main.php";

if ($form_action === "list" && !$popup_select) {
?>
<div class="row justify-content-center">
    <div class="col-auto">
        <button
                class="btn btn-sm <?= empty($_SESSION[$HD_Form->search_session_key]) ? "btn-outline-primary" : "btn-primary" ?>"
                data-bs-toggle="modal"
                data-bs-target="#searchModal"
                title="<?= _("Search Vouchers") ?> <?= empty($_SESSION[$HD_Form->search_session_key]) ? "" : "(" . _("search activated") . ")" ?>"
        >
            <?= _("Search Vouchers") ?>
        </button>
    </div>
    <div class="col-auto">
        <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#batchUpdateModal">
            <?= _("Batch Update") ?>
        </button>
    </div>
    <div class="col-auto">
        <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#generateVoucherModal">
            <?= _("Generate Vouchers") ?>
        </button>
    </div>
</div>

    <?php $HD_Form->create_search_form(true, false) ?>


<div class="modal" id="batchUpdateModal" aria-labelledby="modal-title-udpate" aria-hidden="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-title-update"><?= _("Batch Update") ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="container-fluid form-striped" name="updateForm" id="updateForm" action="" method="post">
                    <input type="hidden" name="action" value="batchupdate"/>
                    <?= $HD_Form->csrf_inputs() ?>

                    <div class="row mb-1">
                        <div class="col">
                            <?= sprintf(_("%d vouchers selected!"), $HD_Form->FG_LIST_VIEW_ROW_COUNT) ?>
                            <?= _("Use the options below to batch update the selected vouchers.") ?>
                        </div>
                    </div>


                    <div class="row mb-1">
                        <div class="col-4">
                            <input name="check[upd_used]" type="checkbox" value="on" aria-label="check to enable updates to this field" <?php if (!empty($check["upd_used"])): ?> checked="checked"<?php endif ?> class="form-check-input"/>
                            <label class="form-label form-label-sm" for="upd_used">
                                <?= _("Used") ?>
                            </label>
                        </div>
                        <div class="col">
                            <select name="upd_used" id="upd_used" class="form-select form-select-sm">
                                <?php foreach ($used_list as $v): ?>
                                    <option value="<?= $v[1] ?>" <?php if (($update_fields["status"] ?? "") == $v[1]): ?>selected="selected"<?php endif ?>>
                                        <?= $v[0] ?>
                                    </option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>


                    <div class="row mb-1">
                        <div class="col-4">
                            <input name="check[upd_activated]" type="checkbox" value="on" aria-label="check to enable updates to this field" <?php if (!empty($check["upd_activated"])): ?> checked="checked"<?php endif ?> class="form-check-input"/>
                            <label class="form-label form-label-sm" for="upd_activated">
                                <?= _("Activated") ?>
                            </label>
                        </div>
                        <div class="col">
                            <select name="upd_activated" id="upd_activated" class="form-select form-select-sm">
                                <?php foreach ($actived_list as $v): ?>
                                    <option value="<?= $v[1] ?>" <?php if (($update_fields["activated"] ?? "") == $v[1]): ?>selected="selected"<?php endif ?>>
                                        <?= $v[0] ?>
                                    </option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>


                    <div class="row mb-1">
                        <div class="col-4">
                            <input name="check[upd_credit]" id="check[upd_credit]" type="checkbox" value="on" aria-label="check to enable updates to this field" <?php if (!empty($check["upd_credit"])): ?> checked="checked" <?php endif ?> class="form-check-input"/>
                            <label class="form-label form-label-sm" for="upd_credit">
                                <?= _("Credit") ?>
                            </label>
                        </div>
                        <div class="col-auto">
                            <input type="number" name="upd_credit" id="upd_credit" min="-100" max="100" value="<?= $update_fields["credit"] ?? 0 ?>" class="form-control form-control-sm"/>
                        </div>
                        <div class="col-auto">
                            <div class="form-check form-check-inline">
                                <input type="radio" name="type[upd_credit]" id="type_upd_credit_1" value="1" <?php if (($type["upd_credit"] ?? 1) == 1): ?>checked="checked"<?php endif ?> class="form-check-input"/>
                                <label class="form-check-label form-check-label-sm" for="type_upd_credit_1"><abbr title="<?= _("Equals") ?>">=</abbr></label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input type="radio" name="type[upd_credit]" id="type_upd_credit_2" value="2" <?php if(($type["upd_credit"] ?? 1) == 2): ?>checked="checked"<?php endif ?> class="form-check-input"/>
                                <label class="form-check-label form-check-label-sm" for="type_upd_credit_2"><abbr title="<?= _("Add") ?>">+</abbr></label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input type="radio" name="type[upd_credit]" id="type_upd_credit_3" value="3" <?php if(($type["upd_credit"] ?? 1) == 3): ?>checked="checked"<?php endif ?> class="form-check-input"/>
                                <label class="form-check-label form-check-label-sm" for="type_upd_credit_3"><abbr title="<?= _("Subtract") ?>">-</abbr></label>
                            </div>
                        </div>
                    </div>


                    <div class="row mb-1">
                        <div class="col-4">
                            <input name="check[upd_currency]" type="checkbox" value="on" aria-label="check to enable updates to this field" <?php if (!empty($check["upd_currency"])): ?> checked="checked"<?php endif ?> class="form-check-input"/>
                            <label class="form-label form-label-sm" for="upd_currency">
                                <?= _("Currency") ?>
                            </label>
                        </div>
                        <div class="col">
                            <select name="upd_currency" id="upd_currency" class="form-select form-select-sm">
                                <?php foreach (getCurrencyValuesList() as $k=>$v): ?>
                                    <option value="<?= $k ?>" <?php if (($update_fields["currency"] ?? "") === $k): ?>selected="selected"<?php endif ?>><?= $v["name"] ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>


                    <div class="row mb-1">
                        <div class="col-4">
                            <input name="check[upd_tag]" type="checkbox" value="on" aria-label="check to enable updates to this field" <?php if (!empty($check["upd_tag"])): ?> checked="checked"<?php endif ?> class="form-check-input"/>
                            <label class="form-label form-label-sm" for="upd_tag">
                                <?= _("Tag") ?>
                            </label>
                        </div>
                        <div class="col">
                            <input type="text" name="upd_tag" id="upd_tag" value="<?= $update_fields["tag"] ?? "" ?>" class="form-control form-control-sm">
                        </div>
                    </div>


                </form> <!-- .container-fluid -->
            </div> <!-- .modal-body -->
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= _("Close") ?></button>
                <button type="submit" form="updateForm" class="btn btn-primary"><?= _("Batch Update Cards") ?></button>
            </div>
        </div> <!-- .modal-content -->
    </div> <!-- .modal-dialog -->
</div> <!-- .modal -->
<!-- ** ** ** ** ** Part for the Update ** ** ** ** ** -->


<div class="modal" id="generateVoucherModal" aria-labelledby="modal-title-generate" aria-hidden="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-title-generate"><?= _("Generate Vouchers") ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="container-fluid form-striped" name="generateForm" id="generateForm" action="" method="post">
                    <input type="hidden" name="action" value="generate"/>
                    <?= $HD_Form->csrf_inputs() ?>

                    <div class="row mb-1">
                        <div class="col">
                            <?= _("Bulk generate a batch of vouchers, defining such properties as credit and currency etc.") ?>
                        </div>
                    </div>


                    <div class="row mb-1">
                        <div class="col-4">
                            <label class="form-label form-label-sm" for="gen_count">
                                <?= _("Count") ?>
                            </label>
                        </div>
                        <div class="col">
                            <select name="count" id="gen_count" class="form-select form-select-sm" required="required">
                                <?php foreach ([5, 10, 50, 100, 200, 500] as $v): ?>
                                    <option><?= $v ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>


                    <div class="row mb-1">
                        <div class="col-4">
                            <label class="form-label form-label-sm" for="gen_length">
                                <?= _("Voucher Length") ?>
                            </label>
                        </div>
                        <div class="col">
                            <input type="number" name="length" id="gen_length" value="<?= LEN_VOUCHER ?>" min="8" max="20" class="form-control form-control-sm" required="required"/>
                        </div>
                    </div>


                    <div class="row mb-1">
                        <div class="col-4">
                            <label class="form-label form-label-sm" for="gen_credit">
                                <?= _("Credit") ?>
                            </label>
                        </div>
                        <div class="col">
                            <input type="number" name="credit" id="gen_credit" min="1" max="1000" class="form-control form-control-sm" required="required"/>
                        </div>
                    </div>


                    <div class="row mb-1">
                        <div class="col-4">
                            <label class="form-label form-label-sm" for="gen_currency">
                                <?= _("Currency") ?>
                            </label>
                        </div>
                        <div class="col">
                            <select name="currency" id="gen_currency" class="form-select form-select-sm">
                                <?php foreach (getCurrenciesList() as $k=>$v): ?>
                                    <option value="<?= $k ?>"><?= $v ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>


                    <div class="row mb-1">
                        <div class="col-4">
                            <label class="form-label form-label-sm" for="gen_expirationdate">
                                <?= _("Expiration Date") ?>
                            </label>
                        </div>
                        <div class="col">
                            <input type="datetime-local" name="expirationdate" id="gen_expirationdate" class="form-control form-control-sm">
                        </div>
                    </div>


                    <div class="row mb-1">
                        <div class="col-4">
                            <label class="form-label form-label-sm" for="gen_tag">
                                <?= _("Tag") ?>
                            </label>
                        </div>
                        <div class="col">
                            <input type="text" name="tag" id="gen_tag" class="form-control form-control-sm">
                        </div>
                    </div>


                </form> <!-- .container-fluid -->
            </div> <!-- .modal-body -->
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= _("Close") ?></button>
                <button type="submit" form="generateForm" class="btn btn-primary"><?= _("Generate Vouchers") ?></button>
            </div>
        </div> <!-- .modal-content -->
    </div> <!-- .modal-dialog -->
</div> <!-- .modal -->

<?php
} // END if ($form_action == "list")

$HD_Form->create_toppage($form_action);
echo $update_msg ?? "";

$HD_Form->create_form($form_action, $list);
$HD_Form->setup_export();

require_once __DIR__ . "/../templates/footer.php";
