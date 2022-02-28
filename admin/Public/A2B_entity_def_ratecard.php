<?php

use A2billing\Forms\FormHandler;
use A2billing\Table;

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of A2Billing (http://www.a2billing.net/)
 *
 * A2Billing, Commercial Open Source Telecom Billing platform,
 * powered by Star2billing S.L. <http://www.star2billing.com/>
 *
 * @copyright   Copyright (C) 2004-2015 - Star2billing S.L.
 * @author      Belaid Arezqui <areski@gmail.com>
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

/** @var FormHandler $HD_Form */

include '../lib/admin.defines.php';
include '../lib/admin.module.access.php';
include './form_data/FG_var_def_ratecard.inc';
include '../lib/admin.smarty.php';

if (!has_rights(ACX_RATECARD)) {
    header("HTTP/1.0 401 Unauthorized");
    header("Location: PP_error.php?c=accessdenied");
    die();
}

getpost_ifset([
    'package',
    'popup_select',
    'popup_formname',
    'popup_fieldname',
    'posted',
    'Period',
    'frommonth',
    'fromstatsmonth',
    'tomonth',
    'tostatsmonth',
    'fromday',
    'fromstatsday_sday',
    'fromstatsmonth_sday',
    'today',
    'tostatsday_sday',
    'tostatsmonth_sday',
    'current_page',
    'removeallrate',
    'removetariffplan',
    'definecredit',
    'IDCust',
    'mytariff_id',
    'destination',
    'dialprefix',
    'buyrate1',
    'buyrate2',
    'buyrate1type',
    'buyrate2type',
    'rateinitial1',
    'rateinitial2',
    'rateinitial1type',
    'rateinitial2type',
    'id_trunk',
    "check",
    "type",
    "mode",
]);
/**
 * @var string $package
 * @var string $popup_select
 * @var string $popup_formname
 * @var string $popup_fieldname
 * @var string $posted
 * @var string $Period
 * @var string $frommonth
 * @var string $fromstatsmonth
 * @var string $tomonth
 * @var string $tostatsmonth
 * @var string $fromday
 * @var string $fromstatsday_sday
 * @var string $fromstatsmonth_sday
 * @var string $today
 * @var string $tostatsday_sday
 * @var string $tostatsmonth_sday
 * @var string $current_page
 * @var string $removeallrate
 * @var string $removetariffplan
 * @var string $definecredit
 * @var string $IDCust
 * @var string $mytariff_id
 * @var string $destination
 * @var string $dialprefix
 * @var string $buyrate1
 * @var string $buyrate2
 * @var string $buyrate1type
 * @var string $buyrate2type
 * @var string $rateinitial1
 * @var string $rateinitial2
 * @var string $rateinitial1type
 * @var string $rateinitial2type
 * @var string $id_trunk
 * @var array $check
 * @var array $type
 * @var array $mode

 */
/********************************* BATCH UPDATE ***********************************/
getpost_ifset([
    'batchupdate',
    'upd_id_trunk',
    'upd_idtariffplan',
    'upd_id_outbound_cidgroup',
    'upd_tag',
    'upd_inuse',
    'upd_activated',
    'upd_language',
    'upd_tariff',
    'upd_credit',
    'upd_credittype',
    'upd_simultaccess',
    'upd_currency',
    'upd_typepaid',
    'upd_creditlimit',
    'upd_enableexpire',
    'upd_expirationdate',
    'upd_expiredays',
    'upd_runservice',
    'filterprefix',
    'filterfield'
]);

/**
 * @var string $batchupdate
 * @var string $upd_id_trunk
 * @var string $upd_idtariffplan
 * @var string $upd_id_outbound_cidgroup
 * @var string $upd_tag
 * @var string $upd_inuse
 * @var string $upd_activated
 * @var string $upd_language
 * @var string $upd_tariff
 * @var string $upd_credit
 * @var string $upd_credittype
 * @var string $upd_simultaccess
 * @var string $upd_currency
 * @var string $upd_typepaid
 * @var string $upd_creditlimit
 * @var string $upd_enableexpire
 * @var string $upd_expirationdate
 * @var string $upd_expiredays
 * @var string $upd_runservice
 * @var string $filterprefix
 * @var string $filterfield
 */

$update_fields = [
    "upd_buyrate",
    "upd_buyrateinitblock",
    "upd_buyrateincrement",
    "upd_rateinitial",
    "upd_initblock",
    "upd_billingblock",
    "upd_connectcharge",
    "upd_disconnectcharge",
    "upd_rounding_calltime",
    "upd_rounding_threshold",
    "upd_additional_block_charge",
    "upd_additional_block_charge_time"
];
$update_fields_info = [
    "BUYING RATE",
    "BUYRATE MIN DURATION",
    "BUYRATE BILLING BLOCK",
    "SELLING RATE",
    "SELLRATE MIN DURATION",
    "SELLRATE BILLING BLOCK",
    "CONNECT CHARGE",
    "DISCONNECT CHARGE",
    "ROUNDING CALLTIME",
    "ROUNDING THRESHOLD",
    "ADDITIONAL BLOCK CHARGE",
    "ADDITIONAL BLOCK CHARGE TIME"
];
$charges_abc = [];
$charges_abc_info = [];
if (ADVANCED_MODE) {
    $charges_abc = [
        "upd_stepchargea",
        "upd_chargea",
        "upd_timechargea",
        "upd_stepchargeb",
        "upd_chargeb",
        "upd_timechargeb",
        "upd_stepchargec",
        "upd_chargec",
        "upd_timechargec",
        "upd_announce_time_correction"
    ];
    getpost_ifset($charges_abc);
    $charges_abc_info = [
        "ENTRANCE CHARGE A",
        "COST A",
        "TIME FOR A",
        "ENTRANCE CHARGE B",
        "COST B",
        "TIME FOR B",
        "ENTRANCE CHARGE C",
        "COST C",
        "TIME FOR C",
        "ANNOUNCE TIME CORRECTION"
    ];
}

getpost_ifset($update_fields);

/***********************************************************************************/

$HD_Form->setDBHandler(DbConnect());
$HD_Form->init();

// CHECK IF REQUEST OF BATCH UPDATE
if ($batchupdate == 1 && is_array($check)) {

    check_demo_mode();

    $HD_Form->prepare_list_subselection('list');

    // Array ( [upd_simultaccess] => on [upd_currency] => on )
    $i = 0;
    $SQL_UPDATE = '';
    $PREFIX_FIELD = 'cc_ratecard.';

    foreach ($check as $ind_field => $ind_val) {
        //echo "<br>::> $ind_field -";
        $myfield = substr($ind_field, 4);
        if ($i != 0) {
            $SQL_UPDATE .= ',';
        }
        $val = $$ind_field;

        // Standard update mode
        if (($mode["$ind_field"] ?? 1) == 1) {
            if (!isset($type["$ind_field"])) {
                $SQL_UPDATE .= " $PREFIX_FIELD$myfield = '$val'";
            } else {
                $SQL_UPDATE .= " $PREFIX_FIELD$myfield = '$type[$ind_field]'";
            }
            // Mode 2 - Equal - Add - Substract
        } elseif ($mode["$ind_field"] == 2) {
            if (($type["$ind_field"] ?? 1) == 1) {
                $SQL_UPDATE .= " $PREFIX_FIELD$myfield = '$val'";
            } elseif ($type["$ind_field"] == 2 && str_ends_with($val, "%")) {
                $val = substr($val, 0, -1);
                $SQL_UPDATE .= " $PREFIX_FIELD$myfield = ROUND($PREFIX_FIELD$myfield + (($PREFIX_FIELD$myfield * $val) / 100) + 0.00005, 4)";
            } elseif ($type["$ind_field"] == 2) {
                $SQL_UPDATE .= " $PREFIX_FIELD$myfield = $PREFIX_FIELD$myfield + '$val'";
            } elseif ($type["$ind_field"] == 3 && str_ends_with($val, "%")) {
                $val = substr($val, 0, -1);
                $SQL_UPDATE .= " $PREFIX_FIELD$myfield = ROUND($PREFIX_FIELD$myfield - (($PREFIX_FIELD$myfield * $val) / 100) + 0.00005, 4)";
            } elseif ($type["$ind_field"] == 3) {
                $SQL_UPDATE .= " $PREFIX_FIELD$myfield = $PREFIX_FIELD$myfield - '$val'";
            }
        }

        $i++;
    }

    $SQL_UPDATE = "UPDATE $HD_Form->FG_TABLE_NAME SET $SQL_UPDATE";
    if (strlen($HD_Form->FG_TABLE_CLAUSE) > 1) {
        $SQL_UPDATE .= ' WHERE ';
        $SQL_UPDATE .= $HD_Form->FG_TABLE_CLAUSE;
    }
    $instance_table = new Table();
    $res = $instance_table->ExecuteQuery($HD_Form->DBHandle, $SQL_UPDATE);
    if (!$res)
        $update_msg = "<center><font color=\"red\"><b>" . gettext("Could not perform the batch update") . "!</b></font></center>";
    else
        $update_msg = "<center><font color=\"green\"><b>" . gettext("The batch update has been successfully perform") . " !</b></font></center>";

}
/********************************* END BATCH UPDATE ***********************************/

if (!empty($id)) {
    $HD_Form->FG_EDITION_CLAUSE = str_replace("%id", $id, $HD_Form->FG_EDITION_CLAUSE);
}

$form_action = $form_action ?? "list"; //ask-add

if ($form_action !== "list") {
    check_demo_mode();
} elseif ($HD_Form->FG_FILTER_SEARCH_FORM && $_POST['posted_search'] == 1 && is_numeric($mytariffgroup_id)) {
    if (!empty ($HD_Form->FG_TABLE_CLAUSE)) {
        $HD_Form->FG_TABLE_CLAUSE .= ' AND ';
    }

    $HD_Form->FG_TABLE_CLAUSE .= "idtariffplan='$mytariff_id'";
}


if (substr_count($tariffgroup ?? "", "-:-") === 2) {
    [$mytariffgroup_id, $mytariffgroupname, $mytariffgrouplcrtype] = explode('/-:-/', $tariffgroup);
    $_SESSION["mytariffgroup_id"] = $mytariffgroup_id;
    $_SESSION["mytariffgroupname"] = $mytariffgroupname;
    $_SESSION["tariffgrouplcrtype"] = $mytariffgrouplcrtype;
} else {
    $mytariffgroup_id = $_SESSION["mytariffgroup_id"];
    $mytariffgroupname = $_SESSION["mytariffgroupname"];
    $mytariffgrouplcrtype = $_SESSION["tariffgrouplcrtype"];
}

$list = $HD_Form->perform_action($form_action);

$list_tariffname = (new Table("cc_tariffplan", "id, tariffname"))->get_list($HD_Form->DBHandle, "", "tariffname");
$list_trunk = (new Table("cc_trunk", "id_trunk, trunkcode, providerip"))->get_list($HD_Form->DBHandle, "", "trunkcode");
$list_cid_group = (new Table("cc_outbound_cid_group", "id, group_name"))->get_list($HD_Form->DBHandle, "", "group_name");
$list_tariffgroup = (new Table("cc_tariffgroup", "id, tariffgroupname, lcrtype"))->get_list($HD_Form->DBHandle, "", "tariffgroupname");

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
            class="btn btn-sm <?= empty($_SESSION[$HD_Form->FG_FILTER_SEARCH_SESSION_NAME]) ? "btn-outline-primary" : "btn-primary" ?>"
            data-bs-toggle="modal"
            data-bs-target="#searchModal"
            title="<?= _("Search Customers") ?> <?= empty($_SESSION[$HD_Form->FG_FILTER_SEARCH_SESSION_NAME]) ? "" : "(" . _("search activated") . ")" ?>"
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
                    <input type="hidden" name="atmenu" value="<?= $atmenu?>"/>
                    <input type="hidden" name="popup_select" value="<?= $popup_select?>"/>
                    <input type="hidden" name="popup_formname" value="<?= $popup_formname?>"/>
                    <input type="hidden" name="popup_fieldname" value="<?= $popup_fieldname?>"/>
                    <input type="hidden" name="form_action" value="<?= $form_action?>"/>
                    <input type="hidden" name="filterprefix" value="<?= $filterprefix?>"/>
                    <input type="hidden" name="filterfield" value="<?= $filterfield?>"/>
                    <?= $HD_Form->csrf_inputs() ?>


                    <div class="row mb-1">
                        <div class="col">
                            <?= $HD_Form->FG_NB_RECORD ?> <?= _("rates selected!") ?>
                            <?= _("Use the options below to batch update the selected rates.") ?>
                        </div>
                    </div>

                    <div class="row mb-1">
                        <div class="col-4">
                            <input name="check[upd_id_trunk]" type="checkbox" value="on" aria-label="check to enable updates to this field" <?php if ($check["upd_id_trunk"] === "on"): ?> checked="checked"<?php endif ?> class="form-check-input"/>
                            <label class="form-label form-label-sm" for="upd_id_trunk">
                                <?= _("Trunk") ?>
                            </label>
                        </div>
                        <div class="col">
                            <select name="upd_id_trunk" id="upd_id_trunk" class="form-select form-select-sm">
                                <option value="-1"><?= _("Not Defined") ?></option>
                                <?php foreach ($list_trunk as $v): ?>
                                    <option value="<?= $v[0] ?>" <?php if ($upd_id_trunk == $v[0]): ?>selected="selected"<?php endif ?>><?= $v[1] ?> (<?= $v[2] ?>)</option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-1">
                        <div class="col-4">
                            <input name="check[upd_idtariffplan]" type="checkbox" value="on" aria-label="check to enable updates to this field" <?php if ($check["upd_idtariffplan"] === "on"): ?> checked="checked"<?php endif ?> class="form-check-input"/>
                            <label class="form-label form-label-sm" for="upd_idtariffplan">
                                <?= _("Ratecard") ?>
                            </label>
                        </div>
                        <div class="col">
                            <select name="upd_idtariffplan" id="upd_idtariffplan" class="form-select form-select-sm">
                                <?php foreach ($list_tariffname as $v): ?>
                                    <option value="<?= $v[0] ?>" <?php if ($upd_idtariffplan == $v[0]): ?>selected="selected"<?php endif ?>><?= $v[1] ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-1">
                        <div class="col-4">
                            <input name="check[upd_id_outbound_cidgroup]" type="checkbox" value="on" aria-label="check to enable updates to this field" <?php if ($check["upd_id_outbound_cidgroup"] === "on"): ?> checked="checked"<?php endif ?> class="form-check-input"/>
                            <label class="form-label form-label-sm" for="upd_id_outbound_cidgroup">
                                <?= _("Ratecard") ?>
                            </label>
                        </div>
                        <div class="col">
                            <select name="upd_id_outbound_cidgroup" id="upd_id_outbound_cidgroup" class="form-select form-select-sm">
                                <?php foreach ($list_cid_group as $v): ?>
                                    <option value="<?= $v[0] ?>" <?php if ($upd_id_outbound_cidgroup == $v[0]): ?>selected="selected"<?php endif ?>><?= $v[1] ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>

                <?php foreach (array_merge($update_fields, $charges_abc) as $k => $v):?>
                    <div class="row mb-1">
                        <div class="col-4">
                            <input name="check[<?= $v ?>]" type="checkbox" value="on" aria-label="check to enable updates to this field" <?php if ($check[$v] === "on"): ?> checked="checked"<?php endif ?> class="form-check-input"/>
                            <input type="hidden" name="mode[<?= $v ?>]" value="2"/>
                            <label class="form-label form-label-sm" for="<?= $v ?>">
                                <?php if ($k > count($update_fields) + 1): ?>
                                <?= _($charges_abc_info[$k]) ?>
                                <?php else: ?>
                                <?= _($update_fields_info[$k]) ?>
                                <?php endif ?>
                            </label>
                        </div>
                        <div class="col-3">
                            <select name="type[<?= $v ?>]" id="type[<?= $v ?>]" aria-label="select the operation to perform with the entered value" class="form-select form-select-sm">
                                <option value="1" <?php if (($type[$v] ?? 1) == 1): ?>selected="selected"<?php endif ?>><?= _("Set equal to") ?></option>
                                <option value="2" <?php if (($type[$v] ?? 1) == 2): ?>selected="selected"<?php endif ?>><?= _("Add to") ?></option>
                                <option value="3" <?php if (($type[$v] ?? 1) == 3): ?>selected="selected"<?php endif ?>><?= _("Subtract from") ?></option>
                            </select>
                        </div>
                        <div class="col">
                            <input type="number" name="<?= $v ?>" id="<?= $v ?>" value="<?= $v ?? 0 ?>" class="form-control form-control-sm"/>
                        </div>
                    </div>
                <?php endforeach ?>
                    
                    <div class="row mb-1">
                        <div class="col-4">
                            <input name="check[upd_tag]" type="checkbox" value="on" aria-label="check to enable updates to this field" <?php if ($check["upd_tag"] === "on"): ?> checked="checked" <?php endif ?> class="form-check-input"/>
                            <label class="form-label form-label-sm" for="upd_tag">
                                <?= _("Tag") ?>
                            </label>
                        </div>
                        <div class="col">
                            <input type="text" name="upd_tag" id="upd_tag" value="<?= $upd_tag ?? "" ?>" class="form-control form-control-sm"/>
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
                <form class="container-fluid" name="exportForm" id="exportForm" action="?s=1&amp;t=0&amp;order=<?= $order ?>&amp;sens=<?= $sens ?>&amp;current_page=<?= $current_page ?>" method="post">
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


<?php endif; // END if ($form_action == "list")

/********************************* BATCH ASSIGNED ***********************************/
if ($popup_select): ?>
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
                    <input type="hidden" name="batchupdate" value="1"/>
                    <input type="hidden" name="atmenu" value="<?= $atmenu?>"/>
                    <input type="hidden" name="popup_select" value="<?= $popup_select?>"/>
                    <input type="hidden" name="popup_formname" value="<?= $popup_formname?>"/>
                    <input type="hidden" name="popup_fieldname" value="<?= $popup_fieldname?>"/>
                    <input type="hidden" name="form_action" value="<?= $form_action?>"/>
                    <input type="hidden" name="filterprefix" value="<?= $filterprefix?>"/>
                    <input type="hidden" name="filterfield" value="<?= $filterfield?>"/>
                    <input type="hidden" name="addbatchrate" value="1"/>
                    <?= $HD_Form->csrf_inputs() ?>


                    <div class="row mb-1">
                        <div class="col">
                            <?= $HD_Form->FG_NB_RECORD ?> <?= _("rates selected!") ?>
                            <?= _("Use the options below to batch update the selected rates.") ?>
                        </div>
                    </div>

                    <div class="row mb-1">
                        <div class="col-4">
                            <input id="check[assign_id_trunk]" type="checkbox" value="on" aria-label="check to enable updates to this field" <?php if ($check["assign_id_trunk"] === "on"): ?> checked="checked"<?php endif ?> class="form-check-input"/>
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
                            <input id="check[assign_idtariffplan]" type="checkbox" value="on" aria-label="check to enable updates to this field" <?php if ($check["assign_idtariffplan"] === "on"): ?> checked="checked"<?php endif ?> class="form-check-input"/>
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
                            <input id="check[assign_tag]" type="checkbox" value="on" aria-label="check to enable updates to this field" <?php if ($check["assign_tag"] === "on"): ?> checked="checked" <?php endif ?> class="form-check-input"/>
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
                            <input id="check[assign_prefix]" type="checkbox" value="on" aria-label="check to enable updates to this field" <?php if ($check["assign_prefix"] === "on"): ?> checked="checked" <?php endif ?> class="form-check-input"/>
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
                <button class="btn btn-primary" onclick="sendOpener()"><?= _("Batch Assigned") ?></button>
            </div>
        </div> <!-- .modal-content -->
    </div> <!-- .modal-dialog -->
</div> <!-- .modal -->

<script>
function sendOpener() {
    let id_trunk = "";
    let id_tariffplan = "";
    let tag = "";
    let prefix = "";
    let pack = '<?= $package ?>';

    if (document.getElementById("check[assign_id_trunk]").checked) {
        id_trunk = document.getElementById("assign_id_trunk").value;
    }

    if (document.getElementById("check[assign_idtariffplan]").checked) {
        id_tariffplan = document.getElementById("assign_idtariffplan").value;
    }

    if (document.getElementById("check[assign_tag]").checked) {
        tag = document.getElementById("assign_tag").value;
    }

    if (document.getElementById("check[assign_prefix]").checked) {
        let val = document.getElementById("rbPrefix").value;
        prefix = `${document.assignForm.assign_prefix.value}&rbPrefix=${val}`;
    }
    window.opener.location.href = `A2B_package_manage_rates.php?id=${pack}&addbatchrate=true&id_trunk=${id_trunk}&id_tariffplan=${id_tariffplan}&tag=${tag}&prefix=${prefix}`;
}
</script>

<?php
if (!empty($package) &&is_numeric($package)) {
    $HD_Form->CV_FOLLOWPARAMETERS .= "&package=$package";
}
endif; // is popup
/********************************* END BATCH ASSIGNED ***********************************/

?>

<?php

// #### TOP SECTION PAGE
$HD_Form -> create_toppage ($form_action);

$HD_Form -> create_form($form_action, $list) ;

// Code for the Export Functionality
$_SESSION[$HD_Form->FG_EXPORT_SESSION_VAR]= "SELECT $HD_Form->FG_EXPORT_FIELD_LIST FROM $HD_Form->FG_TABLE_NAME";
if (strlen($HD_Form->FG_TABLE_CLAUSE) > 1) {
    $_SESSION[$HD_Form->FG_EXPORT_SESSION_VAR] .= " WHERE $HD_Form->FG_TABLE_CLAUSE ";
}
if (!empty($HD_Form->SQL_GROUP)) {
    $_SESSION[$HD_Form->FG_EXPORT_SESSION_VAR] .= " $HD_Form->SQL_GROUP ";
}
if (!empty($HD_Form->FG_ORDER) && !empty($HD_Form->FG_SENS)) {
    $_SESSION[$HD_Form->FG_EXPORT_SESSION_VAR].= " ORDER BY $HD_Form->FG_ORDER $HD_Form->FG_SENS";
}
if (!str_contains($_SESSION[$HD_Form->FG_EXPORT_SESSION_VAR], 'cc_callplan_lcr')) {
    $_SESSION[$HD_Form->FG_EXPORT_SESSION_VAR] = str_replace('destination,', 'cc_prefix.destination,', $_SESSION[$HD_Form->FG_EXPORT_SESSION_VAR]);
}

// #### FOOTER SECTION
$smarty->display('footer.tpl');
