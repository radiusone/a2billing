<?php

use A2billing\A2Billing;
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

$menu_section = 6;
require_once __DIR__ . "/../../common/lib/admin.defines.php";
require_once __DIR__ . "/../../common/form_data/FG_var_def_ratecard.inc";
/**
 * @var A2Billing $A2B
 * @var FormHandler $HD_Form
 * @var string $popup_select
 * @var string $popup_formname
 * @var string $popup_fieldname
 */

Admin::checkPageAccess(Admin::ACX_RATECARD);

getpost_ifset(["package"]);
/**
 * @var string $package
 */
/********************************* BATCH UPDATE ***********************************/
$bu = [];
getpost_ifset([
    'batchupdate',
    'upd_id_trunk',
    'upd_idtariffplan',
    'upd_id_outbound_cidgroup',
    'upd_tag',
    'check',
    'mode',
    'type',
], $bu);

$update_fields = [
    "upd_buyrate" => _("Buy Rate"),
    "upd_buyrateinitblock" => _("Buy Min Duration"),
    "upd_buyrateincrement" => _("Buy Billing Block"),
    "upd_rateinitial" => _("Sell Rate"),
    "upd_initblock" => _("Sell Min Duration"),
    "upd_billingblock" => _("Sell Billing Block"),
    "upd_connectcharge" => _("Connect Charge"),
    "upd_disconnectcharge" => _("Disconnect Charge"),
    "upd_rounding_calltime" => _("Duration Rounding"),
    "upd_rounding_threshold" => _("Rounding Threshold"),
    "upd_additional_block_charge" => _("Add Block Charge"),
    "upd_additional_block_charge_time" => _("Add Block Charge Time"),
];
getpost_ifset(array_keys($update_fields), $bu);

$charges_abc = [];
if (ADVANCED_MODE) {
    $charges_abc = [
        "upd_stepchargea" => _("Initial Charge A"),
        "upd_chargea" => _("Rate A"),
        "upd_timechargea" => _("Duration A"),
        "upd_stepchargeb" => _("Initial Charge B"),
        "upd_chargeb" => _("Rate B"),
        "upd_timechargeb" => _("Duration B"),
        "upd_stepchargec" => _("Initial Charge C"),
        "upd_chargec" => _("Rate C"),
        "upd_timechargec" => _("Duration C"),
        "upd_announce_time_correction" => _("Announce Time Correction"),
    ];
    getpost_ifset(array_keys($charges_abc), $bu);
}

/***********************************************************************************/
$HD_Form->init();

// CHECK IF REQUEST OF BATCH UPDATE
if (($bu["batchupdate"] ?? false) && is_array($bu["check"])) {
    // get the checkboxes that are checked
    $selected_updates = array_keys($bu["check"]);

    $HD_Form->prepare_list_subselection('list');

    $sql_sets = [];
    $sql_params = [];
    $values = [];

    foreach ($selected_updates as $ind_field) {
        if (!array_key_exists($ind_field, $bu)) {
            continue;
        }
        $col = (new Table())->quote_identifier(substr($ind_field,4));
        $val = $bu[$ind_field];
        $mode = $bu["mode"][$ind_field] ?? "1";
        $type = $bu["type"][$ind_field] ?? "1";

        // Standard update mode
        if ($mode === "1") {
            $values[$col] = $type[$ind_field] ?? $val;
            // Mode 2 - Equal - Add - Substract
        } elseif ($mode === "2" && $type === "1") {
            $values[$col] = $val;
        } elseif ($mode === "2") {
            if ($type === "3") {
                $val = -$val;
            }
            if (str_ends_with($val, "%")) {
                $values[$col] = ["ROUND($col + ($col * (? / 100)), 4)", str_replace("%", "", $val)];
            } else {
                $values[$col] = ["$col + ?", $val];
            }
        }
    }

    $result = (new Table("cc_ratecard"))->updateRow($values, $HD_Form->list_query_conditions);
    if ($result === false) {
        $update_msg = "<div class='alert alert-danger'>" . _("Could not perform the batch update") . "</div>";
    } else {
        $update_msg = "<div class='alert alert-success'>" . _("The batch update has been successfully performed") . "</div>";
    }
}
/********************************* END BATCH UPDATE ***********************************/
$form_action ??= "list";

$list = $HD_Form->perform_action($form_action);

$list_tariffname = (new Table("cc_tariffplan", ["id", "tariffname"]))->getRows([], ["tariffname"]);
$list_trunk = (new Table("cc_trunk", ["id_trunk", "trunkcode", "providerip"]))->getRows([], ["trunkcode"]);
$list_cid_group = (new Table("cc_outbound_cid_group", ["id", "group_name"]))->getRows([], ["group_name"]);
$list_tariffgroup = (new Table("cc_tariffgroup", ["id", "tariffgroupname AS name"]))->getRows([], ["tariffgroupname"]);

require_once __DIR__ . "/templates/main.php";

// DISPLAY THE UPDATE MESSAGE
echo $update_msg ?? "";

/********************************* BATCH UPDATE ***********************************/
// if $_SESSION['def_ratecard_tariffgroup'] is filled, disable batch update for LCR export
if ($form_action === "list" && !$popup_select): ?>
<div class="row justify-content-center">
    <div class="col-auto">
        <button
            class="btn btn-sm <?= empty($_SESSION[$HD_Form->search_session_key]) ? "btn-outline-primary" : "btn-primary" ?>"
            data-bs-toggle="modal"
            data-bs-target="#searchModal"
            title="<?= _("Search Rates") ?> <?= empty($_SESSION[$HD_Form->search_session_key]) ? "" : "(" . _("search activated") . ")" ?>"
        >
            <?= _("Search Rates") ?>
        </button>
    </div>
    <?php if (empty($_SESSION['def_ratecard_tariffgroup'])): ?>
    <div class="col-auto">
        <button class="btn btn-outline-primary btn-sm"
            data-bs-toggle="modal"
            data-bs-target="#batchUpdateModal"
        >
            <?= _("Batch Update") ?>
        </button>
    </div>
    <?php endif ?>
    <div class="col-auto">
        <button
            class="btn btn-sm <?= empty($_SESSION["def_ratecard_tariffgroup"]) ? "btn-outline-primary" : "btn-primary btn-search-active" ?>"
            data-bs-toggle="modal"
            data-bs-target="#exportModal"
        >
            <?= _("Export Call Plan with LCR") ?>
        </button>
    </div>
</div>

<div class="modal" id="searchModal" aria-labelledby="modal-title-search" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-title-search"><?= _("Search Rates") ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php $HD_Form->create_search_form() ?>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="batchUpdateModal" aria-labelledby="modal-title-udpate" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-title-update"><?= _("Batch Update") ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="container-fluid form-striped" name="updateForm" id="updateForm" action="" method="post">
                    <input type="hidden" name="batchupdate" value="1"/>
                    <input type="hidden" name="form_action" value="<?= $form_action ?>"/>
                    <input type="hidden" name="filterprefix" value="<?= $filterprefix ?? "" ?>"/>
                    <?= $HD_Form->csrf_inputs() ?>

                    <div class="row mb-1">
                        <div class="col">
                            <?= $HD_Form->FG_LIST_VIEW_ROW_COUNT ?> <?= _("rates selected!") ?>
                            <?= _("Use the options below to batch update the selected rates.") ?>
                        </div>
                    </div>

                    <div class="row mb-1">
                        <div class="col-4">
                            <input name="check[upd_id_trunk]" type="checkbox" value="on" aria-label="check to enable updates to this field" <?php if (!empty($bu["check"]["upd_id_trunk"])): ?> checked="checked"<?php endif ?> class="form-check-input"/>
                            <label class="form-label form-label-sm" for="upd_id_trunk">
                                <?= _("Trunk") ?>
                            </label>
                        </div>
                        <div class="col">
                            <select name="upd_id_trunk" id="upd_id_trunk" class="form-select form-select-sm">
                                <option value="-1"><?= _("Not Defined") ?></option>
                                <?php foreach ($list_trunk as $v): ?>
                                    <option value="<?= $v[0] ?>" <?php if (($bu["upd_id_trunk"] ?? "") == $v[0]): ?>selected="selected"<?php endif ?>><?= $v[1] ?> (<?= $v[2] ?>)</option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-1">
                        <div class="col-4">
                            <input name="check[upd_idtariffplan]" type="checkbox" value="on" aria-label="check to enable updates to this field" <?php if (!empty($bu["check"]["upd_idtariffplan"])): ?> checked="checked"<?php endif ?> class="form-check-input"/>
                            <label class="form-label form-label-sm" for="upd_idtariffplan">
                                <?= _("Ratecard") ?>
                            </label>
                        </div>
                        <div class="col">
                            <select name="upd_idtariffplan" id="upd_idtariffplan" class="form-select form-select-sm">
                                <?php foreach ($list_tariffname as $v): ?>
                                    <option value="<?= $v[0] ?>" <?php if (($bu["upd_idtariffplan"] ?? "") == $v[0]): ?>selected="selected"<?php endif ?>><?= $v[1] ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-1">
                        <div class="col-4">
                            <input name="check[upd_id_outbound_cidgroup]" type="checkbox" value="on" aria-label="check to enable updates to this field" <?php if (!empty($bu["check"]["upd_id_outbound_cidgroup"])): ?> checked="checked"<?php endif ?> class="form-check-input"/>
                            <label class="form-label form-label-sm" for="upd_id_outbound_cidgroup">
                                <?= _("CID Group") ?>
                            </label>
                        </div>
                        <div class="col">
                            <select name="upd_id_outbound_cidgroup" id="upd_id_outbound_cidgroup" class="form-select form-select-sm">
                                <?php foreach ($list_cid_group as $v): ?>
                                    <option value="<?= $v[0] ?>" <?php if (($bu["upd_id_outbound_cidgroup"] ?? "") == $v[0]): ?>selected="selected"<?php endif ?>><?= $v[1] ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>

                <?php foreach (array_merge($update_fields, $charges_abc) as $field => $label):?>
                    <div class="row mb-1">
                        <div class="col-4">
                            <input name="check[<?= $field ?>]" type="checkbox" value="on" aria-label="check to enable updates to this field" <?php if (!empty($bu["check"][$field])): ?> checked="checked"<?php endif ?> class="form-check-input"/>
                            <input type="hidden" name="mode[<?= $field ?>]" value="2"/>
                            <label class="form-label form-label-sm" for="<?= $field ?>">
                                <?= _($label) ?>
                            </label>
                        </div>
                        <div class="col-3">
                            <select name="type[<?= $field ?>]" id="type[<?= $field ?>]" aria-label="select the operation to perform with the entered value" class="form-select form-select-sm">
                                <option value="1" <?php if (($bu["type"][$field] ?? 1) == 1): ?>selected="selected"<?php endif ?>><?= _("Set equal to") ?></option>
                                <option value="2" <?php if (($bu["type"][$field] ?? 1) == 2): ?>selected="selected"<?php endif ?>><?= _("Add amount") ?></option>
                                <option value="3" <?php if (($bu["type"][$field] ?? 1) == 3): ?>selected="selected"<?php endif ?>><?= _("Subtract amount") ?></option>
                            </select>
                        </div>
                        <div class="col">
                            <input type="number" name="<?= $field ?>" id="<?= $field ?>" value="<?= $bu["field"] ?? 0 ?>" class="form-control form-control-sm"/>
                        </div>
                    </div>
                <?php endforeach ?>
                    
                    <div class="row mb-1">
                        <div class="col-4">
                            <input name="check[upd_tag]" type="checkbox" value="on" aria-label="check to enable updates to this field" <?php if (!empty($bu["check"]["upd_tag"])): ?> checked="checked" <?php endif ?> class="form-check-input"/>
                            <label class="form-label form-label-sm" for="upd_tag">
                                <?= _("Tag") ?>
                            </label>
                        </div>
                        <div class="col">
                            <input type="text" name="upd_tag" id="upd_tag" value="<?= $bu["upd_tag"] ?? "" ?>" class="form-control form-control-sm"/>
                        </div>
                    </div>
                </form> <!-- .container-fluid -->
            </div> <!-- .modal-body -->
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= _("Close") ?></button>
                <button type="submit" form="updateForm" class="btn btn-primary"><?= _("Batch Update Ratecard") ?></button>
            </div>
        </div> <!-- .modal-content -->
    </div> <!-- .modal-dialog -->
</div> <!-- .modal -->

<div class="modal" id="exportModal" aria-labelledby="modal-title-export" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-title-export"><?= _("Export Call Plan with LCR") ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="container-fluid" name="exportForm" id="exportForm" action="" method="post">
                    <input type="hidden" name="posted" value="1"/>
                    <input type="hidden" name="current_page" value="0"/>
                    <?= $HD_Form->csrf_inputs() ?>
                    <div class="row">
                        <div class="col">
                            <?php if (!empty($_SESSION['def_ratecard_tariffgroup'])): ?>
                                <strong><?= sprintf(_("Current LCR call plan: %s"), $list_tariffgroup[$_SESSION['def_ratecard_tariffgroup']]["tariffgroupname"]) ?></strong><br/>
                            <?php endif ?>
                            <select name="tariffgroup" id="tariffgroup" aria-label="<?= _("Choose a call plan") ?>" class="form-select form-select-sm">
                                <option value=""><?= _("Choose a call plan") ?></option>
                                <?php foreach ($list_tariffgroup as $v): ?>
                                <option value="<?= $v["id"] ?>" <?php if ($_SESSION['def_ratecard_tariffgroup'] ?? 0 == $v["id"]): ?>selected="selected"<?php endif?>>
                                    <?= $v["tariffgroupname"] ?>
                                </option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                </form> <!-- .container-fluid -->
            </div> <!-- .modal-body -->
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= _("Close") ?></button>
                <?php if (!empty($_SESSION['def_ratecard_tariffgroup'])): ?>
                <a class="btn btn-secondary" href="?cancelsearch_callplanlcr=true"><?= _("Cancel Search") ?></a>
                <?php endif ?>
                <button type="submit" form="exportForm" class="btn btn-primary"><?= _("Search") ?></button>
            </div>
        </div> <!-- .modal-content -->
    </div> <!-- .modal-dialog -->
</div> <!-- .modal -->


<?php endif; // END if ($form_action == "list" && !$popup_select)

/********************************* BATCH ASSIGNED ***********************************/
if ($popup_select === "1"): // only triggered from A2B_info_package.php ?>
<div class="row justify-content-center">
    <div class="col-auto">
        <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#batchAssignModal">
            <?= _("Batch Assign to Package") ?>
        </button>
    </div>
</div>

<div class="modal" id="batchAssignModal" aria-labelledby="modal-title-assign" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-title-assign"><?= _("Batch Assign Rates to Package") ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="container-fluid form-striped" name="assignForm" id="assignForm" action="" method="post">
                    <?= $HD_Form->csrf_inputs() ?>
                    <div class="row mb-1">
                        <div class="col">
                            <?= $HD_Form->FG_LIST_VIEW_ROW_COUNT ?> <?= _("rates selected!") ?>
                            <?= _("Use the options below to batch update the selected rates.") ?>
                        </div>
                    </div>

                    <div class="row mb-1">
                        <div class="col-4">
                            <input id="check[assign_id_trunk]" type="checkbox" value="on" aria-label="check to enable searching this field" class="form-check-input"/>
                            <label class="form-label form-label-sm" for="assign_id_trunk">
                                <?= _("Trunk") ?>
                            </label>
                        </div>
                        <div class="col">
                            <select id="assign_id_trunk" class="form-select form-select-sm">
                                <option value="-1"><?= _("Not Defined") ?></option>
                                <?php foreach ($list_trunk as $v): ?>
                                    <option value="<?= $v[0] ?>"><?= $v[1] ?> (<?= $v[2] ?>)</option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-1">
                        <div class="col-4">
                            <input id="check[assign_idtariffplan]" type="checkbox" value="on" aria-label="check to enable searching this field" class="form-check-input"/>
                            <label class="form-label form-label-sm" for="assign_idtariffplan">
                                <?= _("Ratecard") ?>
                            </label>
                        </div>
                        <div class="col">
                            <select id="assign_idtariffplan" class="form-select form-select-sm">
                                <?php foreach ($list_tariffname as $v): ?>
                                    <option value="<?= $v[0] ?>"><?= $v[1] ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-1">
                        <div class="col-4">
                            <input id="check[assign_tag]" type="checkbox" value="on" aria-label="check to enable searching this field" class="form-check-input"/>
                            <label class="form-label form-label-sm" for="assign_tag">
                                <?= _("Tag") ?>
                            </label>
                        </div>
                        <div class="col">
                            <input type="text" id="assign_tag" value="" class="form-control form-control-sm"/>
                        </div>
                    </div>

                    <div class="row mb-1">
                        <div class="col-4">
                            <input id="check[assign_prefix]" type="checkbox" value="on" aria-label="check to enable searching this field" class="form-check-input"/>
                            <label class="form-label form-label-sm" for="assign_prefix">
                                <?= _("Prefix") ?>
                            </label>
                        </div>
                        <div class="col-4">
                            <input type="text" id="assign_prefix" value="" class="form-control form-control-sm"/>
                        </div>
                        <div class="col">
                            <select name="rbPrefix" id="rbPrefix" aria-label="select a comparison to apply to the field" class="form-select form-select-sm">
                                <option value="1"><?= _("Exact") ?></option>
                                <option value="2"><?= _("Begins with") ?></option>
                                <option value="3"><?= _("Contains") ?></option>
                                <option value="4"><?= _("Ends with") ?></option>
                                <option value="5"><?= _("Expression") ?></option>
                            </select>
                        </div>
                    </div>
                </form> <!-- .container-fluid -->
            </div> <!-- .modal-body -->
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= _("Close") ?></button>
                <button id="batchassign" class="btn btn-primary"><?= _("Batch Assign") ?></button>
            </div>
        </div> <!-- .modal-content -->
    </div> <!-- .modal-dialog -->
</div> <!-- .modal -->

<script>
function sendRateToPackage(rate) {
    const pack = <?= (int)$package ?>;
    window.opener.location.href = `A2B_info_package.php?id=${pack}&addrate=${rate}`;
    window.close();
}

document.getElementById("batchassign").addEventListener('click', function () {
    const pack = <?= (int)$package ?>;
    let url = `A2B_info_package.php?id=${pack}&addbatchrate=true`;

    if (document.getElementById("check[assign_id_trunk]")?.checked) {
        const id_trunk = encodeURIComponent(document.getElementById("assign_id_trunk").value);
        url += `&id_trunk=${id_trunk}`;
    }

    if (document.getElementById("check[assign_idtariffplan]")?.checked) {
        const id_tariffplan = encodeURIComponent(document.getElementById("assign_idtariffplan").value);
        url += `&id_tariffplan=${id_tariffplan}`;
    }

    if (document.getElementById("check[assign_tag]")?.checked) {
        const tag = encodeURIComponent(document.getElementById("assign_tag").value);
        url += `&tag=${tag}`;
    }

    if (document.getElementById("check[assign_prefix]")?.checked) {
        const rb_prefix = encodeURIComponent(document.getElementById("rbPrefix").value);
        const assign_prefix = encodeURIComponent(document.getElementById("assign_prefix").value);
        url += `&prefix=${assign_prefix}&rbPrefix=${rb_prefix}`;
    }

    window.opener.location.href = url;
    window.close();
});
</script>

<?php
$HD_Form->CV_FOLLOWPARAMETERS = array_filter(["package" => $package ?? ""]);
/********************************* END BATCH ASSIGNED ***********************************/
endif;

// #### TOP SECTION PAGE
$HD_Form->create_toppage($form_action);
$HD_Form->create_form($form_action, $list);
$HD_Form->setup_export();

require_once __DIR__ . "/templates/footer.php";
