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

require_once "../../common/lib/admin.defines.php";
require('./form_data/FG_var_card.inc');

if (!has_rights(ACX_CUSTOMER)) {
    header("HTTP/1.0 401 Unauthorized");
    header("Location: PP_error.php?c=accessdenied");
    die();
}

/** @var FormHandler $HD_Form */
$HD_Form->setDBHandler (DbConnect());
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

'upd_country'
 */
// CHECK IF REQUEST OF BATCH UPDATE
if ($batchupdate == 1 && is_array($check)) {
    $SQL_REFILL="";
    $HD_Form->prepare_list_subselection('list');

    if (!empty($upd_expirationdate)) {
        // html datetime input sends as 2022-02-21T13:40
        $upd_expirationdate = str_replace("T", " ", $upd_expirationdate) . ":00";
    }
    if (isset($check['upd_credit']) && strlen($upd_credit) > 0) {
        //set to refill
        $SQL_REFILL_CREDIT="";
        $SQL_REFILL_WHERE="";
        if ($type["upd_credit"] == 1) {//equal
            $SQL_REFILL_CREDIT = "($upd_credit - credit) ";
            $SQL_REFILL_WHERE = " AND $upd_credit <> credit ";//never write 0 refill
        } elseif ($type["upd_credit"] == 2) {//+-
            $SQL_REFILL_CREDIT = "($upd_credit) ";
        } else {
            $SQL_REFILL_CREDIT = "(-$upd_credit) ";
        }
        $SQL_REFILL="INSERT INTO cc_logrefill (credit, card_id, description, refill_type) SELECT $SQL_REFILL_CREDIT, a.id, '$upd_description', '$upd_refill_type' FROM $HD_Form->FG_TABLE_NAME AS a ";
        if (strlen($HD_Form->FG_TABLE_CLAUSE) > 1) {
            $SQL_REFILL .= " WHERE $HD_Form->FG_TABLE_CLAUSE $SQL_REFILL_WHERE";
        } elseif ($SQL_REFILL_WHERE && $type["upd_credit"] == 1) {
            $SQL_REFILL .= " WHERE $upd_credit <> credit ";
        }
    }

    // Array ( [upd_simultaccess] => on [upd_currency] => on )
    $i = 0;
    $SQL_UPDATE = '';
    foreach ($check as $ind_field => $ind_val) {
        $myfield = substr($ind_field,4);
        if ($i != 0) {
            $SQL_UPDATE.=',';
        }
        $val = $$ind_field;

        // Standard update mode
        if (($mode[$ind_field] ?? 1) == 1) {
            if (!isset($type[$ind_field])) {
                $SQL_UPDATE .= " $myfield='$val'";
            } else {
                $SQL_UPDATE .= " $myfield='$type[$ind_field]'";
            }
        // Mode 2 - Equal - Add - Subtract
        } elseif ($mode[$ind_field] == 2) {
            if (($type[$ind_field] ?? 1) == 1) {
                $SQL_UPDATE .= " $myfield='$val'";
            } elseif ($type[$ind_field] == 2) {
                $SQL_UPDATE .= " $myfield = $myfield + '$val'";
            } elseif ($type[$ind_field] == 3) {
                $SQL_UPDATE .= " $myfield = $myfield - '$val'";
            }
        }
        $i++;
    }

    $SQL_UPDATE = "UPDATE $HD_Form->FG_TABLE_NAME SET $SQL_UPDATE";
    if ($HD_Form->FG_TABLE_CLAUSE) {
        $SQL_UPDATE .= " WHERE $HD_Form->FG_TABLE_CLAUSE";
    }
    $update_msg_error = _('Could not perform the batch update!');
    $update_msg = "";

    if (!$HD_Form->DBHandle->Execute("begin")) {
        $update_msg = $update_msg_error;
    } else {
        if (isset($check['upd_credit']) && (strlen($upd_credit) > 0) && ($upd_refill_type >= 0)) {
            if (!$HD_Form->DBHandle->Execute($SQL_REFILL)) {
                $update_msg = _('Could not perform refill log for the batch update!');
            }
        }
        if (!$HD_Form->DBHandle->Execute($SQL_UPDATE)) {
            $update_msg = $update_msg_error;
        }
        if (!$HD_Form->DBHandle->Execute("commit")) { // this looks like an error to me?
            $update_msg = _('The batch update has been successfully perform!');
        }
    }
}

if (!empty($id)) {
    $HD_Form->FG_EDITION_CLAUSE = str_replace("%id", "$id", $HD_Form->FG_EDITION_CLAUSE);
}
$form_action = $form_action ?? "list"; //ask-add
$list = $HD_Form->perform_action($form_action);

// #### HEADER SECTION
$smarty->display('main.tpl');
?>

<script>
function sendValue(selvalue, othervalue) {
    var formname = <?= json_encode($popup_formname ?? "") ?>;
    var fieldname = <?= json_encode($popup_fieldname ?? "") ?>;
    $(`form[name='${formname}'] [name='${fieldname}']`, window.opener.document).val(selvalue);
    if (othervalue) {
        $(`form[name=${formname}] [name=accountcode]`, window.opener.document).val(othervalue);
    }
    window.close();
}
</script>
<script src="javascript/card.js"></script>

<?php if ($form_action === "list" && !$popup_select):

    $instance_table_tariff = new Table("cc_tariffgroup", "id, tariffgroupname");
    $FG_TABLE_CLAUSE = "";
    $list_tariff = $instance_table_tariff->get_list($HD_Form->DBHandle, $FG_TABLE_CLAUSE, "tariffgroupname");
    $nb_tariff = count($list_tariff);

    $instance_table_group = new Table("cc_card_group"," id, name ");
    $list_group = $instance_table_group->get_list($HD_Form->DBHandle, $FG_TABLE_CLAUSE, "name");

    $instance_table_agent = new Table("cc_agent"," id, login ");
    $list_agent = $instance_table_agent->get_list($HD_Form->DBHandle, $FG_TABLE_CLAUSE, "login");

    $instance_table_seria = new Table("cc_card_seria"," id, name");
    $list_seria  = $instance_table_seria->get_list($HD_Form->DBHandle, $FG_TABLE_CLAUSE, "name");

    $list_refill_type = getRefillType_List();
    $list_refill_type["-1"] = ["NO REFILL", "-1"];

    $instance_table_country = new Table("cc_country", " countrycode, countryname ");
    $list_country = $instance_table_country->get_list($HD_Form->DBHandle, $FG_TABLE_CLAUSE, "countryname");

    echo $CC_help_list_customer;
?>

<div class="row justify-content-center">
    <div class="col-auto">
        <button
            class="btn btn-sm <?= empty($_SESSION[$HD_Form->FG_FILTER_SEARCH_SESSION_NAME]) ? "btn-outline-primary" : "btn-primary" ?>"
            data-bs-toggle="modal"
            data-bs-target="#searchModal"
            title="<?= _("Search Customers") ?> <?= empty($_SESSION[$HD_Form->FG_FILTER_SEARCH_SESSION_NAME]) ? "" : "(" . _("search activated") . ")" ?>"
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

<div class="modal" id="searchModal" aria-labelledby="modal-title-search" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-title-search"><?= _("Search Customers") ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php $HD_Form->create_search_form() ?>
            </div>
<!-- buttons are in the form
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" form="searchForm" class="btn btn-primary"><?= _("Search") ?></button>
            </div>
-->
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
                    <?= $HD_Form->csrf_inputs() ?>


                    <div class="row mb-1">
                        <div class="col">
                            <?= $HD_Form->FG_NB_RECORD ?> <?= _("cards selected!") ?>
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
                            <input name="check[upd_credit]" type="checkbox" value="on" aria-label="check to enable updates to this field" <?php if ($check["upd_credit"] === "on"): ?> checked="checked" <?php endif ?> class="form-check-input"/>
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
                                <?php foreach (get_currencies() as $v): ?>
                                    <option value="<?= $v[1] ?>" <?php if ($upd_currency == $v[1]): ?>selected="selected"<?php endif ?>><?= $v[0] ?></option>
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
                                <?= _("Move to Seria") ?> <!-- TODO: figure out WTF this means -->
                            </label>
                        </div>
                        <div class="col">
                            <select name="upd_id_seria" id="upd_id_seria" class="form-select form-select-sm">
                                <?php foreach ($list_seria as $v): ?>
                                    <option value="<?= $v[0] ?>" <?php if ($upd_id_seria == $v[0]): ?>selected="selected"<?php endif ?>><?= $v[1] ?></option>
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
                                    <option value="<?= $v[0] ?>" <?php if ($upd_country == $v[0]): ?>selected="selected"<?php endif ?>><?= $v[1] ?></option>
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
                    <a class="btn btn-primary" href="CC_generate_friend_file.php?atmenu=sipfriend">
                        <?= _("Generate additional_a2billing_sip.conf") ?>
                    </a>
                <?php endif ?>
                <?php if (!empty($_SESSION["is_iax_changed"])): ?>
                    <a class="btn btn-primary" href="CC_generate_friend_file.php?atmenu=iaxfriend">
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
<div class="row">
    <div class="col">
        <form action="?form_action=ask-add&section=1" method="post" name="cardform">
            <label for="cardnumber_length"><?= _("Change the account number length") ?></label>
            <select name="cardnumber_length_list" id="cardnumber_length" onchange="this.form.submit()">
                <?php foreach ($A2B->cardnumber_range as $v): ?>
                <option value="$v" <?php if ($v == $cardnumberlength_list): ?>selected="selected"<?php endif ?>><?= $v ?> <?= _("Digits") ?></option>
                <?php endforeach ?>
            </select>
        </form>
    </div>
</div>

<?php endif;

if ($form_action === "ask-edit") {
    echo get_login_button($HD_Form->DBHandle, $id);
}

$HD_Form->create_form($form_action, $list);

// Code for the Export Functionality
$_SESSION[$HD_Form->FG_EXPORT_SESSION_VAR] = "SELECT $HD_Form->FG_EXPORT_FIELD_LIST FROM $HD_Form->FG_TABLE_NAME";

if (strlen($HD_Form->FG_TABLE_CLAUSE)>1) {
    $_SESSION[$HD_Form->FG_EXPORT_SESSION_VAR] .= " WHERE $HD_Form->FG_TABLE_CLAUSE ";
}

if (!empty($HD_Form->FG_ORDER) && !empty($HD_Form->FG_SENS)) {
    $_SESSION[$HD_Form->FG_EXPORT_SESSION_VAR] .= " ORDER BY $HD_Form->FG_ORDER $HD_Form->FG_SENS";
}
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

</script>

<?php $smarty->display('footer.tpl');
