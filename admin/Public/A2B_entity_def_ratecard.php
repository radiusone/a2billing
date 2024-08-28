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

$menu_section = 6;
require_once "../../common/lib/admin.defines.php";
include './form_data/FG_var_def_ratecard.inc';
/**
 * @var A2Billing $A2B
 * @var Smarty $smarty
 * @var FormHandler $HD_Form
 * @var string $CC_help_rate
 * @var string $CC_help_def_ratecard
 * @var string $order
 * @var string $sens
 * @var string $current_page
 */

Admin::checkPageAccess(Admin::ACX_RATECARD);

getpost_ifset([
    'package',
    'popup_select',
    'popup_formname',
    'popup_fieldname',
    'filterprefix',
]);
/**
 * @var string $package
 * @var string $popup_select
 * @var string $popup_formname
 * @var string $popup_fieldname
 * @var string $filterprefix
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
getpost_ifset(array_keys($update_fields, $bu));

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
if ($bu["batchupdate"] && is_array($bu["check"])) {
    // get the checkboxes that are checked
    $selected_updates = array_keys($bu["check"]);

    $HD_Form->prepare_list_subselection('list');

    $sql_sets = [];
    $sql_params = [];

    foreach ($selected_updates as $ind_field) {
        if (!array_key_exists($ind_field, $bu)) {
            continue;
        }
        $col = str_replace("upd_", "", $ind_field);
        $val = $bu[$ind_field];
        $mode = $bu["mode"][$ind_field] ?? "1";
        $type = $bu["type"][$ind_field] ?? "1";

        // Standard update mode
        if ($mode === "1") {
            $sql_sets[] = "$col = ?";
            $sql_params[] = $type[$ind_field] ?? $val;
            // Mode 2 - Equal - Add - Substract
        } elseif ($mode === "2" && $type === "1") {
            $sql_sets[] = "$col = ?";
            $sql_params[] = $val;
        } elseif ($mode === "2") {
            if ($type === "3") {
                $val = "-$val";
            }
            if (str_ends_with($val, "%")) {
                $sql_sets[] = "$col = ROUND($col + ($col * (? / 100)), 4)";
            } else {
                $sql_sets[] = "$col = $col + ?";
            }
            $sql_params[] = str_replace("%", "", $val);
        }
    }

    $updates = implode(", ", $sql_sets);
    $where = (new Table())->processWhereClauseArray($HD_Form->list_query_conditions, $sql_params) ?: "1=1";
    $result = $HD_Form->DBHandle->Execute("UPDATE cc_ratecard SET $updates $where", $sql_params);
    if ($result === false) {
        $update_msg = "<div class='alert alert-danger'>" . _("Could not perform the batch update") . "</div>";
    } else {
        $update_msg = "<div class='alert alert-success'>" . _("The batch update has been successfully performed") . "</div>";
    }

}
/********************************* END BATCH UPDATE ***********************************/

$form_action = $form_action ?? "list"; //ask-add


if (!empty($tariffgroup) && substr_count($tariffgroup, "-:-") === 2) {
    [$mytariffgroup_id, $mytariffgroupname, $mytariffgrouplcrtype] = explode('-:-', $tariffgroup);
    $_SESSION["mytariffgroup_id"] = $mytariffgroup_id;
    $_SESSION["mytariffgroupname"] = $mytariffgroupname;
    $_SESSION["tariffgrouplcrtype"] = $mytariffgrouplcrtype;
} else {
    $mytariffgroup_id = $_SESSION["mytariffgroup_id"];
    $mytariffgroupname = $_SESSION["mytariffgroupname"];
    $mytariffgrouplcrtype = $_SESSION["tariffgrouplcrtype"];
}

if ($form_action === "list" && $HD_Form->search_form_enabled && $_POST['posted_search'] == 1 && is_numeric($mytariffgroup_id)) {
    if (!empty ($HD_Form->FG_QUERY_WHERE_CLAUSE)) {
        $HD_Form->FG_QUERY_WHERE_CLAUSE .= ' AND ';
    }

    $HD_Form->FG_QUERY_WHERE_CLAUSE .= "idtariffplan='$mytariffgroup_id'";
    $HD_Form->list_query_conditions["idtariffplan"] = $mytariffgroup_id;
}

$list = $HD_Form->perform_action($form_action);

$list_tariffname = $HD_Form->DBHandle->GetAll("SELECT id, tariffname FROM cc_tariffplan ORDER BY tariffname") ?: [];
$list_trunk = $HD_Form->DBHandle->GetAll("SELECT id_trunk, trunkcode, providerip FROM cc_trunk ORDER BY trunkcode") ?: [];
$list_cid_group = $HD_Form->DBHandle->GetAll("SELECT id, group_name FROM cc_outbound_cid_group ORDER BY group_name") ?: [];
$list_tariffgroup = $HD_Form->DBHandle->GetAll("SELECT id, tariffgroupname, lcrtype FROM cc_tariffgroup ORDER BY tariffgroupname") ?: [];

// #### HEADER SECTION
$smarty->display('main.tpl');

// #### HELP SECTION
if (!$popup_select) {
    echo ($form_action === 'ask-add' || $form_action === 'ask-edit') ? $CC_help_rate : $CC_help_def_ratecard;
}

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
            title="<?= _("Search Customers") ?> <?= empty($_SESSION[$HD_Form->search_session_key]) ? "" : "(" . _("search activated") . ")" ?>"
        >
            <?= _("Search Rates") ?>
        </button>
    </div>
    <?php if (empty($_SESSION['def_ratecard_tariffgroup'])): ?>
    <div class="col-auto">
        <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#batchUpdateModal">
            <?= _("Batch Update") ?>
        </button>
    </div>
    <?php endif ?>
    <div class="col-auto">
        <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#exportModal">
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
    <?php if (empty($_SESSION['def_ratecard_tariffgroup'])): ?>
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
                    <input type="hidden" name="popup_select" value="<?= $popup_select?>"/>
                    <input type="hidden" name="popup_formname" value="<?= $popup_formname?>"/>
                    <input type="hidden" name="popup_fieldname" value="<?= $popup_fieldname?>"/>
                    <input type="hidden" name="form_action" value="<?= $form_action?>"/>
                    <input type="hidden" name="filterprefix" value="<?= $filterprefix?>"/>
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
                                    <option value="<?= $v[0] ?>" <?php if (($bu["upd_idtariffplan"]) == $v[0]): ?>selected="selected"<?php endif ?>><?= $v[1] ?></option>
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

    <?php endif // session check ?>
<div class="modal" id="exportModal" aria-labelledby="modal-title-export" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-title-export"><?= _("Export Call Plan with LCR") ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="container-fluid" name="exportForm" id="exportForm" action="?order=<?= $order ?>&amp;sens=<?= $sens ?>&amp;current_page=<?= $current_page ?>" method="post">
                    <input type="hidden" name="posted" value="1"/>
                    <input type="hidden" name="current_page" value="0"/>
                    <?= $HD_Form->csrf_inputs() ?>
                    <div class="row">
                        <div class="col">
                            <?php if (!empty($FG_TOP_FILTER_NAME)): ?>
                                <strong><?= $FG_TOP_FILTER_NAME ?></strong><br/>
                            <?php endif ?>
                            <select name="tariffgroup" id="tariffgroup" aria-label="<?= _("Choose a call plan") ?>" class="form-select form-select-sm">
                                <option value=""><?= _("Choose a call plan") ?></option>
                                <?php foreach ($list_tariffgroup as $v): ?>
                                <option value="<?= implode("-:-", $v) ?>" <?php if (($FG_TOP_FILTER_VALUE ?? null) == $v[0]): ?>selected="selected" <?php endif?>>
                                    <?= $v[1] ?>
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
if ($popup_select === "1"): // only triggered from A2B_package_manage_rates.php ?>
<div class="row justify-content-center">
    <div class="col-auto">
        <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#batchAssignModal">
            <?= _("Batch Assigned") ?>
        </button>
    </div>
</div>

<div class="modal" id="batchAssignModal" aria-labelledby="modal-title-assign" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-title-assign"><?= _("Batch Assign") ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="container-fluid form-striped" name="assignForm" id="assignForm" action="" method="post">
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
                <button id="sendopener" class="btn btn-primary"><?= _("Batch Assigned") ?></button>
            </div>
        </div> <!-- .modal-content -->
    </div> <!-- .modal-dialog -->
</div> <!-- .modal -->

<script>
$("#sendopener").on('click', function () {
    let id_trunk = "";
    let id_tariffplan = "";
    let tag = "";
    let prefix = "";
    const pack = '<?= $package ?>';

    if ($("#check[assign_id_trunk]:checked")) {
        id_trunk = $("#assign_id_trunk").val();
    }

    if ($("#check[assign_idtariffplan]:checked").length) {
        id_tariffplan = $("#assign_idtariffplan").val();
    }

    if ($("#check[assign_tag]:checked").length) {
        tag = $("#assign_tag").val();
    }

    if ($("#check[assign_prefix]:checked").length) {
        const rb_prefix = $("#rbPrefix").val();
        const assign_prefix = $("form[name=assignForm] *[name=assign_prefix]").val();
        prefix = `${assign_prefix}&rbPrefix=${rb_prefix}`;
    }
    window.opener.location.href = `A2B_package_manage_rates.php?id=${pack}&addbatchrate=true&id_trunk=${id_trunk}&id_tariffplan=${id_tariffplan}&tag=${tag}&prefix=${prefix}`;
});
</script>

<?php
$HD_Form->CV_FOLLOWPARAMETERS .= "&package=" . $package ?? "";
/********************************* END BATCH ASSIGNED ***********************************/
elseif ($popup_select === "2"):
?>
<script>
    function sendValue(selvalue) {
    const formname = <?= json_encode($popup_formname ?? "") ?>;
    const fieldname = <?= json_encode($popup_fieldname ?? "") ?>;
    $(`form[name='${formname}'] [name='${fieldname}']`, window.opener.document).val(selvalue);
    window.close();
    }
</script>
<?php
endif;

// #### TOP SECTION PAGE
$HD_Form -> create_toppage ($form_action);

$HD_Form -> create_form($form_action, $list);
$HD_Form->setup_export();

// #### FOOTER SECTION
$smarty->display('footer.tpl');
