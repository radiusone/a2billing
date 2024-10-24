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
$menu_section = 1;
require_once "../../common/lib/admin.defines.php";
require('./form_data/FG_var_card.inc');
/**
 * @var A2Billing $A2B
 * @var Smarty $smarty
 * @var FormHandler $HD_Form
 * @var string $CC_help_list_customer
 * @var string $CC_help_create_customer
 * @var array $cardstatus_list
 * @var array $language_list
 * @var string $cardnumber_length
 */

Admin::checkPageAccess(Admin::ACX_CUSTOMER);

$HD_Form->init();

/********************************* BATCH UPDATE ***********************************/
getpost_ifset(['popup_select', 'popup_formname', 'popup_fieldname', 'upd_inuse', 'upd_status', 'upd_language',
              'upd_tariff', 'upd_credit', 'upd_credittype', 'upd_simultaccess', 'upd_currency', 'upd_typepaid',
              'upd_creditlimit', 'upd_enableexpire', 'upd_expirationdate', 'upd_expiredays', 'upd_runservice',
              'upd_runservice', 'batchupdate', 'check', 'type', 'mode', 'addcredit', 'cardnumber','description',
              'upd_id_group','upd_discount','upd_refill_type','upd_description','upd_id_seria', 'upd_vat',
              'upd_country']);

/**
 * @var string $popup_select
 * @var string $popup_formname
 * @var string $popup_fieldname
 * @var string $upd_inuse
 * @var string $upd_status
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
 * @var string $upd_runservice
 * @var string $batchupdate
 * @var array $check
 * @var array $type
 * @var array $mode
 * @var string $addcredit
 * @var string $cardnumber
 * @var string $description
 * @var string $upd_id_group
 * @var string $upd_discount
 * @var string $upd_refill_type
 * @var string $upd_description
 * @var string $upd_id_seria
 * @var string $upd_vat
 * @var string $upd_country
 */

// CHECK IF REQUEST OF BATCH UPDATE
if ($batchupdate == 1 && is_array($check)) {
    $HD_Form->prepare_list_subselection('list');

    if (!empty($upd_expirationdate)) {
        // html datetime input sends as 2022-02-21T13:40
        $upd_expirationdate = str_replace("T", " ", $upd_expirationdate);
    }
    if (isset($check['upd_credit']) && strlen($upd_credit) > 0) {
        //set to refill
        $refill_sql = "INSERT INTO cc_logrefill (credit, card_id, description, refill_type) ";
        $refill_params = [];
        switch ($type["upd_credit"]) {
            case 1: $refill_sql .= "SELECT (? - credit), a.id, ?, ? FROM $HD_Form->FG_QUERY_TABLE_NAME AS a"; break;
            case 2: $refill_sql .= "SELECT ?, a.id, ?, ? FROM $HD_Form->FG_QUERY_TABLE_NAME AS a"; break;
            default: $refill_sql .= "SELECT -?, a.id, ?, ? FROM $HD_Form->FG_QUERY_TABLE_NAME AS a"; break;
        }
        $refill_params = [$upd_credit, $upd_description, $upd_refill_type];
        $where = (new Table())->processWhereClauseArray($HD_Form->list_query_conditions, $refill_params) ?: "1=1";
        $refill_sql .= " WHERE $where";
        if ($type["upd_credit"] == 1) {
            $refill_sql .= " AND ? <> credit";
            $refill_params[] = $upd_credit;
        }
    }

    // Array ( [upd_simultaccess] => on [upd_currency] => on )
    $i = 0;
    $update_sql = "UPDATE $HD_Form->FG_QUERY_TABLE_NAME SET";
    $update_params = [];
    foreach ($check as $ind_field => $ind_val) {
        $myfield = (new Table())->quote_identifier(substr($ind_field,4));
        if ($i !== 0) {
            $update_sql .= ',';
        }
        $val = $$ind_field;

        // Standard update mode
        if (($mode[$ind_field] ?? 1) == 1) {
            $update_sql .= " $myfield = ?";
            if (!isset($type[$ind_field])) {
                $update_params[] = $val;
            } else {
                $update_params[] = $type[$ind_field];
            }
        // Mode 2 - Equal - Add - Subtract
        } elseif ($mode[$ind_field] == 2) {
            if (($type[$ind_field] ?? 1) == 1) {
                $update_sql .= " $myfield = ?";
            } elseif ($type[$ind_field] == 2) {
                $update_sql .= " $myfield = $myfield + ?";
            } elseif ($type[$ind_field] == 3) {
                $update_sql .= " $myfield = $myfield - ?";
            }
            $update_params[] = $val;
        }
        $i++;
    }

    $where = (new Table())->processWhereClauseArray($HD_Form->list_query_conditions, $update_params) ?: "1=1";
    $update_sql .= "WHERE $where";
    $update_msg_error = _('Could not perform the batch update!');
    $update_msg = "";

    if (!$HD_Form->DBHandle->Execute("begin")) {
        $update_msg = $update_msg_error;
    } else {
        if (isset($refill_sql, $refill_params) && ($upd_refill_type >= 0)) {
            if (!$HD_Form->DBHandle->Execute($refill_sql, $refill_params)) {
                $update_msg = _('Could not perform refill log for the batch update!');
            }
        }
        if (!$HD_Form->DBHandle->Execute($update_sql, $update_params)) {
            $update_msg = $update_msg_error;
        }
        if (!$HD_Form->DBHandle->Execute("commit")) { // this looks like an error to me?
            $update_msg = _('The batch update has been successfully perform!');
        }
    }
}

$id = $id ?? 0;
$form_action = $form_action ?? "list"; //ask-add
$list = $HD_Form->perform_action($form_action);

// #### HEADER SECTION
$smarty->display('main.tpl');
?>

<script>
function sendValue(selvalue, othervalue) {
    const formname = <?= json_encode($popup_formname ?? "") ?>;
    const fieldname = <?= json_encode($popup_fieldname ?? "") ?>;
    $(`form[name='${formname}'] [name='${fieldname}']`, window.opener.document).val(selvalue);
    if (othervalue) {
        $(`form[name=${formname}] [name=accountcode]`, window.opener.document).val(othervalue);
    }
    window.close();
}
</script>
<script src="javascript/card.js"></script>

<?php if ($form_action === "list" && !$popup_select):

    $result = $HD_Form->DBHandle->CacheExecute(300, "SELECT id, tariffgroupname FROM cc_tariffgroup ORDER BY tariffgroupname");
    $list_tariff = $result ? $result->GetAll() : [];

    $result = $HD_Form->DBHandle->CacheExecute(300, "SELECT id, name FROM cc_card_group ORDER BY name");
    $list_group = $result ? $result->GetAll() : [];

    $result = $HD_Form->DBHandle->CacheExecute(300, "SELECT id, login FROM cc_agent ORDER BY login");
    $list_agent = $result ? $result->GetAll() : [];

    $result = $HD_Form->DBHandle->CacheExecute(300, "SELECT id, name FROM cc_card_seria ORDER BY name");
    $list_seria = $result ? $result->GetAll() : [];

    $list_refill_type = getRefillType_List();
    $list_refill_type["-1"] = ["NO REFILL", "-1"];

    $result = $HD_Form->DBHandle->CacheExecute(300, "SELECT countrycode, countryname FROM cc_country ORDER BY countryname");
    $list_country = $result ? $result->GetAll() : [];

    echo $CC_help_list_customer;
?>

<div class="row justify-content-center">
    <div class="col-auto">
        <button
            class="btn btn-sm <?= empty($_SESSION[$HD_Form->search_session_key]) ? "btn-outline-primary" : "btn-primary" ?>"
            data-bs-toggle="modal"
            data-bs-target="#searchModal"
            title="<?= _("Search Customers") ?> <?= empty($_SESSION[$HD_Form->search_session_key]) ? "" : "(" . _("search activated") . ")" ?>"
        >
            <?= _("Search Customers") ?>
        </button>
    </div>
    <div class="col-auto">
        <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#batchUpdateModal">
            <?= _("Batch Update") ?>
        </button>
    </div>
</div>

<?php $HD_Form->create_search_form(true, false) ?>

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
                    <?= $HD_Form->csrf_inputs() ?>


                    <div class="row mb-1">
                        <div class="col">
                            <?= $HD_Form->FG_LIST_VIEW_ROW_COUNT ?> <?= _("cards selected!") ?>
                            <?= _("Use the options below to batch update the selected cards.") ?>
                        </div>
                    </div>


                    <div class="row mb-1">
                        <div class="col-4">
                            <input name="check[upd_inuse]" type="checkbox" value="on" aria-label="check to enable updates to this field" <?php if ($check["upd_inuse"] === "on"): ?> checked="checked"<?php endif ?> class="form-check-input"/>
                            <label class="form-label form-label-sm" for="upd_inuse">
                                <?= _("In use") ?>
                            </label>
                        </div>
                        <div class="col">
                            <input type="number" name="upd_inuse" id="upd_inuse" min="0" max="1" value="<?= $upd_inuse ?? 0 ?>" class="form-control form-control-sm">
                        </div>
                    </div>


                    <div class="row mb-1">
                        <div class="col-4">
                            <input name="check[upd_status]" type="checkbox" value="on" aria-label="check to enable updates to this field" <?php if ($check["upd_status"] === "on"): ?> checked="checked"<?php endif ?> class="form-check-input"/>
                            <label class="form-label form-label-sm" for="upd_status">
                                <?= _("Status") ?>
                            </label>
                        </div>
                        <div class="col">
                            <select name="upd_status" id="upd_status" class="form-select form-select-sm">
                                <?php foreach ($cardstatus_list as $v): ?>
                                <option value="<?= $v[1] ?>" <?php if ($upd_status == $v[1]): ?>selected="selected"<?php endif ?>><?= $v[0] ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>


                    <div class="row mb-1">
                        <div class="col-4">
                            <input name="check[upd_language]" type="checkbox" value="on" aria-label="check to enable updates to this field" <?php if ($check["upd_language"] === "on"): ?> checked="checked"<?php endif ?> class="form-check-input"/>
                            <label class="form-label form-label-sm" for="upd_language">
                                <?= _("Language") ?>
                            </label>
                        </div>
                        <div class="col">
                            <select name="upd_language" id="upd_language" class="form-select form-select-sm">
                                <?php foreach ($language_list as $v): ?>
                                    <option value="<?= $v[1] ?>" <?php if ($upd_language == $v[1]): ?>selected="selected"<?php endif ?>><?= $v[0] ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>


                    <div class="row mb-1">
                        <div class="col-4">
                            <input name="check[upd_tariff]" type="checkbox" value="on" aria-label="check to enable updates to this field" <?php if ($check["upd_tariff"] === "on"): ?> checked="checked"<?php endif ?> class="form-check-input"/>
                            <label class="form-label form-label-sm" for="upd_tariff">
                                <?= _("Tariff") ?>
                            </label>
                        </div>
                        <div class="col">
                            <select name="upd_tariff" id="upd_tariff" class="form-select form-select-sm">
                                <?php foreach ($list_tariff as $v): ?>
                                    <option value="<?= $v[0] ?>" <?php if ($upd_tariff == $v[0]): ?>selected="selected"<?php endif ?>><?= $v[1] ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>


                    <div class="row mb-1">
                        <div class="col-4">
                            <input name="check[upd_credit]" id="check[upd_credit]" type="checkbox" value="on" aria-label="check to enable updates to this field" <?php if ($check["upd_credit"] === "on"): ?> checked="checked" <?php endif ?> class="form-check-input"/>
                            <input name="mode[upd_credit]" type="hidden" value="2"/>
                            <label class="form-label form-label-sm" for="upd_credit">
                                <?= _("Credit") ?>
                            </label>
                        </div>
                        <div class="col-auto">
                            <input type="number" name="upd_credit" id="upd_credit" min="-100" max="100" value="<?= $upd_credit ?? 0 ?>" class="form-control form-control-sm"/>
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
                            <label class="form-label form-label-sm ps-3" for="upd_refill_type">
                                <?= _("Refill") ?>
                            </label>
                        </div>
                        <div class="col">
                            <select name="upd_refill_type" id="upd_refill_type" class="form-select form-select-sm">
                                <?php foreach ($list_refill_type as $v): ?>
                                <option value="<?= $v[1] ?>" <?php if ($upd_refill_type == $v[1]): ?>selected="selected"<?php endif?>><?= $v[0] ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-1">
                        <div class="col-4">
                            <label class="form-label form-label-sm ps-3" for="upd_description">
                                <?= _("Description") ?>
                            </label>
                        </div>
                        <div class="col">
                            <input type="text" name="upd_description" id="upd_description" value="<?= $upd_description ?? "" ?>" class="form-control form-control-sm"/>
                        </div>
                    </div>


                    <div class="row mb-1">
                        <div class="col-4">
                            <input name="check[upd_simultaccess]" type="checkbox" value="on" aria-label="check to enable updates to this field" <?php if ($check["upd_simultaccess"] === "on"): ?> checked="checked" <?php endif ?> class="form-check-input"/>
                            <label class="form-label form-label-sm" for="upd_simultaccess">
                                <?= _("Access") ?>
                            </label>
                        </div>
                        <div class="col">
                            <select name="upd_simultaccess" id="upd_simultaccess" class="form-select form-select-sm">
                                <option value="0" <?php if (($upd_simultaccess ?? 0) == 0): ?>selected="selected"<?php endif?>><?= _("Individual Access") ?></option>
                                <option value="1" <?php if (($upd_simultaccess ?? 0) == 1): ?>selected="selected"<?php endif?>><?= _("Simultaneous Access") ?></option>
                            </select>
                        </div>
                    </div>


                    <div class="row mb-1">
                        <div class="col-4">
                            <input name="check[upd_currency]" type="checkbox" value="on" aria-label="check to enable updates to this field" <?php if ($check["upd_currency"] === "on"): ?> checked="checked"<?php endif ?> class="form-check-input"/>
                            <label class="form-label form-label-sm" for="upd_currency">
                                <?= _("Currency") ?>
                            </label>
                        </div>
                        <div class="col">
                            <select name="upd_currency" id="upd_currency" class="form-select form-select-sm">
                                <?php foreach (get_currencies() as $k=>$v): ?>
                                    <option value="<?= $k ?>" <?php if ($upd_currency === $k): ?>selected="selected"<?php endif ?>><?= $v["name"] ?> (<?= $v["value"] ?>)</option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>


                    <div class="row mb-1">
                        <div class="col-4">
                            <input name="check[upd_creditlimit]" type="checkbox" value="on" aria-label="check to enable updates to this field" <?php if ($check["upd_creditlimit"] === "on"): ?> checked="checked" <?php endif ?> class="form-check-input"/>
                            <input name="mode[upd_creditlimit]" type="hidden" value="2"/>
                            <label class="form-label form-label-sm" for="upd_creditlimit">
                                <?= _("Credit Limit") ?>
                            </label>
                        </div>
                        <div class="col-auto">
                            <input type="number" name="upd_creditlimit" id="upd_creditlimit" min="0" max="1000" value="<?= $upd_creditlimit ?? 0 ?>" class="form-control form-control-sm"/>
                        </div>
                        <div class="col-auto">
                            <div class="form-check form-check-inline">
                                <input type="radio" name="type[upd_creditlimit]" id="type_upd_creditlimit_1" value="1" <?php if(($type["upd_creditlimit"] ?? 1) == 1): ?>checked="checked"<?php endif ?> class="form-check-input"/>
                                <label class="form-check-label form-check-label-sm" for="type_upd_creditlimit_1"><abbr title="<?= _("Equals") ?>">=</abbr></label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input type="radio" name="type[upd_creditlimit]" id="type_upd_creditlimit_2" value="2" <?php if(($type["upd_creditlimit"] ?? 1) == 2): ?>checked="checked"<?php endif ?> class="form-check-input"/>
                                <label class="form-check-label form-check-label-sm" for="type_upd_creditlimit_2"><abbr title="<?= _("Add") ?>">+</abbr></label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input type="radio" name="type[upd_creditlimit]" id="type_upd_creditlimit_3" value="3" <?php if(($type["upd_creditlimit"] ?? 1) == 3): ?>checked="checked"<?php endif ?> class="form-check-input"/>
                                <label class="form-check-label form-check-label-sm" for="type_upd_creditlimit_3"><abbr title="<?= _("Subtract") ?>">-</abbr></label>
                            </div>
                        </div>
                    </div>


                    <div class="row mb-1">
                        <div class="col-4">
                            <input name="check[upd_enableexpire]" type="checkbox" value="on" aria-label="check to enable updates to this field" <?php if ($check["upd_enableexpire"] === "on"): ?> checked="checked" <?php endif ?> class="form-check-input"/>
                            <label class="form-label form-label-sm" for="upd_enableexpire">
                                <?= _("Enable Expire") ?>
                            </label>
                        </div>
                        <div class="col">
                            <select name="upd_enableexpire" id="upd_enableexpire" class="form-select form-select-sm">
                                <option value="0" <?php if (($upd_enableexpire ?? 0) == 0): ?>selected="selected"<?php endif?>><?= _("No Expiry") ?></option>
                                <option value="1" <?php if (($upd_enableexpire ?? 0) == 1): ?>selected="selected"<?php endif?>><?= _("Expire Date") ?></option>
                                <option value="2" <?php if (($upd_enableexpire ?? 0) == 2): ?>selected="selected"<?php endif?>><?= _("Expire Days Since First Use") ?></option>
                                <option value="3" <?php if (($upd_enableexpire ?? 0) == 3): ?>selected="selected"<?php endif?>><?= _("Expire Days Since Creation") ?></option>
                            </select>
                        </div>
                    </div>


                    <div class="row mb-1">
                        <div class="col-4">
                            <input name="check[upd_expirationdate]" type="checkbox" value="on" aria-label="check to enable updates to this field" <?php if ($check["upd_expirationdate"] === "on"): ?> checked="checked" <?php endif ?> class="form-check-input"/>
                            <label class="form-label form-label-sm" for="upd_expirationdate">
                                <?= _("Expiry Date") ?>
                            </label>
                        </div>
                        <div class="col">
                            <input type="datetime-local" name="upd_expirationdate" id="upd_expirationdate" value="<?= $upd_expirationdate ?? (new DateTime("now + 10 years"))->format("Y-m-d\TH:i") ?>" class="form-control form-control-sm"/>
                        </div>
                    </div>


                    <div class="row mb-1">
                        <div class="col-4">
                            <input name="check[upd_expiredays]" type="checkbox" value="on" aria-label="check to enable updates to this field" <?php if ($check["upd_expiredays"] === "on"): ?> checked="checked" <?php endif ?> class="form-check-input"/>
                            <label class="form-label form-label-sm" for="upd_expiredays">
                                <?= _("Expiration Days") ?>
                            </label>
                        </div>
                        <div class="col">
                            <input type="number" name="upd_expiredays" id="upd_expiredays" min="1" max="3650" value="<?= $upd_expiredays ?? 0 ?>" class="form-control form-control-sm"/>
                        </div>
                    </div>


                    <div class="row mb-1">
                        <div class="col-4">
                            <input name="check[upd_runservice]" type="checkbox" value="on" aria-label="check to enable updates to this field" <?php if ($check["upd_runservice"] === "on"): ?> checked="checked" <?php endif ?> class="form-check-input"/>
                            <input name="mode[upd_runservice]" type="hidden" value="2"/>
                            <label class="form-label form-label-sm" for="upd_runservice">
                                <?= _("Run Service") ?>
                            </label>
                        </div>
                        <div class="col-auto">
                            <input type="number" name="upd_runservice" id="upd_runservice" min="0" max="1000" value="<?= $upd_runservice ?? 0 ?>" class="form-control form-control-sm"/>
                        </div>
                        <div class="col-auto">
                            <div class="form-check form-check-inline">
                                <input type="radio" name="type[upd_runservice]" id="type_upd_runservice_1" value="1" <?php if(($type["upd_runservice"] ?? 1) == 1): ?>checked="checked"<?php endif ?> class="form-check-input"/>
                                <label class="form-check-label form-check-label-sm" for="type_upd_runservice_1"><?= _("Yes") ?></label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input type="radio" name="type[upd_runservice]" id="type_upd_runservice_0" value="0" <?php if(($type["upd_runservice"] ?? 1) == 0): ?>checked="checked"<?php endif ?> class="form-check-input"/>
                                <label class="form-check-label form-check-label-sm" for="type_upd_runservice_0"><?= _("No") ?></label>
                            </div>
                        </div>
                    </div>


                    <div class="row mb-1">
                        <div class="col-4">
                            <input name="check[upd_id_group]" type="checkbox" value="on" aria-label="check to enable updates to this field" <?php if ($check["upd_id_group"] === "on"): ?> checked="checked"<?php endif ?> class="form-check-input"/>
                            <label class="form-label form-label-sm" for="upd_id_group">
                                <?= _("Group this batch belongs to") ?>
                            </label>
                        </div>
                        <div class="col">
                            <select name="upd_id_group" id="upd_id_group" class="form-select form-select-sm">
                                <?php foreach ($list_group as $v): ?>
                                    <option value="<?= $v[0] ?>" <?php if ($upd_id_group == $v[0]): ?>selected="selected"<?php endif ?>><?= $v[1] ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>


                    <div class="row mb-1">
                        <div class="col-4">
                            <input name="check[upd_discount]" type="checkbox" value="on" aria-label="check to enable updates to this field" <?php if ($check["upd_discount"] === "on"): ?> checked="checked" <?php endif ?> class="form-check-input"/>
                            <label class="form-label form-label-sm" for="upd_discount">
                                <?= _("Discount") ?>
                            </label>
                        </div>
                        <div class="col input-group">
                            <input type="number" name="upd_discount" id="upd_discount" min="0" max="99" value="<?= $upd_discount ?? 0 ?>" class="form-control form-control-sm"/>
                            <span class="input-group-text">%</span>
                        </div>
                    </div>


                    <div class="row mb-1">
                        <div class="col-4">
                            <input name="check[upd_id_seria]" type="checkbox" value="on" aria-label="check to enable updates to this field" <?php if ($check["upd_id_seria"] === "on"): ?> checked="checked"<?php endif ?> class="form-check-input"/>
                            <label class="form-label form-label-sm" for="upd_id_seria">
                                <?= _("Move to Card Series") ?>
                            </label>
                        </div>
                        <div class="col">
                            <select name="upd_id_seria" id="upd_id_seria" class="form-select form-select-sm">
                                <?php foreach ($list_seria as $v): ?>
                                    <option value="<?= $v[0] ?>" <?php if ($upd_id_seria === $v[0]): ?>selected="selected"<?php endif ?>><?= $v[1] ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>


                    <div class="row mb-1">
                        <div class="col-4">
                            <input name="check[upd_vat]" type="checkbox" value="on" aria-label="check to enable updates to this field" <?php if ($check["upd_vat"] === "on"): ?> checked="checked" <?php endif ?> class="form-check-input"/>
                            <label class="form-label form-label-sm" for="upd_vat">
                                <?= _("VAT") ?>
                            </label>
                        </div>
                        <div class="col input-group">
                            <input type="number" name="upd_vat" id="upd_vat" min="0" max="99" value="<?= $upd_vat ?? 0 ?>" class="form-control form-control-sm"/>
                            <span class="input-group-text">%</span>
                        </div>
                    </div>


                    <div class="row mb-1">
                        <div class="col-4">
                            <input name="check[upd_country]" type="checkbox" value="on" aria-label="check to enable updates to this field" <?php if ($check["upd_country"] === "on"): ?> checked="checked"<?php endif ?> class="form-check-input"/>
                            <label class="form-label form-label-sm" for="upd_country">
                                <?= _("Country") ?>
                            </label>
                        </div>
                        <div class="col">
                            <select name="upd_country" id="upd_country" class="form-select form-select-sm">
                                <?php foreach ($list_country as $v): ?>
                                    <option value="<?= $v[0] ?>" <?php if (($upd_country ?? $A2B->config["global"]["base_country"] ?? "") === $v[0]): ?>selected="selected"<?php endif ?>><?= $v[1] ?></option>
                                <?php endforeach ?>
                            </select>
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

<?php if (!USE_REALTIME && isset($_SESSION["is_sip_iax_change"]) && $_SESSION["is_sip_iax_change"]): ?>
<div class="modal show" aria-labelledby="modal-title-sip" aria-hidden="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <span id="modal-title-sip"><?= _("Changes detected on SIP/IAX Friends") ?></span>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= _("Close") ?></button>
                <?php  if (!empty($_SESSION["is_sip_changed"])): ?>
                    <a class="btn btn-primary" href="CC_generate_friend_file.php?voip_type=sipfriend">
                        <?= _("Generate additional_a2billing_sip.conf") ?>
                    </a>
                <?php endif ?>
                <?php if (!empty($_SESSION["is_iax_changed"])): ?>
                    <a class="btn btn-primary" href="CC_generate_friend_file.php?voip_type=iaxfriend">
                        <?= _("Generate additional_a2billing_iax.conf") ?>
                    </a>
                <?php endif ?>
            </div>
        </div>
    </div>
</div>
<?php endif; // endif is_sip_iax_change
endif; // ($form_action === "list" && !$popup_select)

if (!$popup_select){
    echo $CC_help_create_customer;
}
if (!empty($update_msg)) {
    echo $update_msg;
}

$HD_Form->create_toppage ($form_action);

if (!$popup_select && $form_action === "ask-add"):?>
<div class="row pb-3">
    <div class="col">
        <form action="?form_action=ask-add" method="post" name="cardform">
            <?= $HD_Form->csrf_inputs() ?>
            <label for="cardnumber_length"><?= _("Change the account number length") ?></label>
            <select name="cardnumber_length" id="cardnumber_length" onchange="this.form.submit()">
                <?php foreach ($A2B->cardnumber_range as $v): ?>
                <option value="<?= $v ?>" <?php if ($v == $cardnumber_length): ?>selected="selected"<?php endif ?>><?= sprintf(_("%s digits"), $v) ?></option>
                <?php endforeach ?>
            </select>
        </form>
    </div>
</div>

<?php endif;

if ($form_action === "ask-edit") {
    echo get_login_button($id);
}

$HD_Form->create_form($form_action, $list);
$HD_Form->setup_export();
?>

<script>
function toggleUpdateField(el) {
    // convert check[foo] into foo
    let elname = el.getAttribute("name").slice(6, -1);
    $(`[name='${elname}']`).closest('.row').find("[name]:not([name^='check'])").attr("disabled", !el.checked);
}
$("#batchUpdateModal input[type='checkbox'][name^='check']")
    .each((i, el) => toggleUpdateField(el))
    .on('change', ev => toggleUpdateField(ev.target));
// special case
$("#check[upd_credit]").on("change", ev => $("#upd_refill_type, #upd_description").attr("disabled", !ev.target.checked));
</script>

<?php $smarty->display('footer.tpl');
