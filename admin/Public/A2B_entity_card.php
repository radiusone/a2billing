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
require_once __DIR__ . "/../../common/lib/admin.defines.php";
require_once __DIR__ . "/form_data/FG_var_card.inc";
/**
 * @var A2Billing $A2B
 * @var FormHandler $HD_Form
 * @var array $cardstatus_list
 * @var array $language_list
 * @var string $cardnumber_length
 * @var string $popup_select
 * @var string $popup_formname
 * @var string $popup_fieldname
 */

Admin::checkPageAccess(Admin::ACX_CUSTOMER);

$HD_Form->init();

/********************************* BATCH UPDATE ***********************************/
getpost_ifset(['batchupdate', 'check', 'type', 'mode']);

/**
 * @var string $batchupdate
 * @var array $check
 * @var array $type
 * @var array $mode
 */
$batchupdate ??= 0;
$check ??= [];
$type ??= [];
$mode ??= [];

// CHECK IF REQUEST OF BATCH UPDATE
if ($batchupdate == 1 && count($check)) {
    $HD_Form->prepare_list_subselection('list');

    $uf = [];
    getpost_ifset(
        [
            "upd_inuse", "udp_status", "upd_language", "upd_tarrif", "upd_credit", "upd_simultaccess", "upd_currency",
            "upd_creditlimit", "upd_enableexpire", "upd_expirationdate", "upd_expiredays", "upd_runservice", "upd_vat",
            "upd_id_group", "upd_discount", "upd_refill_type", "upd_description", "upd_id_seria", "upd_country",
        ],
        $uf
    );
    $update_fields = [];
    foreach ($uf as $k => $v) {
        $k = substr($k, 4);
        $update_fields[$k] = $v;
    }

    if (!empty($update_fields["expirationdate"])) {
        // html datetime input sends as 2022-02-21T13:40
        $update_fields["expirationdate"] = str_replace("T", " ", $update_fields["expirationdate"]);
    }
    if (isset($check["upd_credit"]) && strlen($update_fields["credit"] ?? "") > 0) {
        // we will be updating card credit, prepare the refill query
        $current_cards = (new Table("cc_card", ["id", "credit"]))
            ->getRows($HD_Form->DBHandle, $HD_Form->list_query_conditions);
        $refill_cards = [];
        foreach ($current_cards as $v) {
            switch ($type["upd_credit"]) {
                // set value
                case 1:
                default:
                    if ((float)$update_fields["credit"] === (float)$v["credit"]) {
                        // no refill entries if the credit doesn't change
                        continue(2);
                    }
                    $credit = (float)$update_fields["credit"] - $v["credit"];
                    break;
                // add
                case 2:
                    $credit = (float)$update_fields["credit"];
                    break;
                // subtract
                case 3:
                    $credit = (float)$update_fields["credit"] * -1;
                    break;
            }
            $refill_cards[] = [
                "credit" => $credit,
                "card_id" => $v["id"],
                "description" => $update_fields["description"],
                "refill_type" => $update_fields["refill_type"],
            ];
        }
    }

    $updates = [];
    foreach (array_keys($check) as $ch) {
        // remove "upd_"
        $col = substr($ch, 4);
        $val = $update_fields[$col] ?? null;
        if (is_null($val)) {
            continue;
        }
        if (($mode[$ch] ?? 1) == 1) {
            // Standard update mode
            $updates[$col] = $val;
        } elseif ($mode[$ch] == 2) {
            // Mode 2 - Equal - Add - Subtract
            if (($type[$ch] ?? 1) == 1) {
                $updates[$col] = $val;
            } elseif ($type[$ch] == 2) {
                $updates[$col] = ["`$col` + ?", $val];
            } elseif ($type[$ch] == 3) {
                $updates[$col] = ["`$col` - ?", $val];
            }
        }
    }

    if (!(new Table("cc_card"))->updateRow($HD_Form->DBHandle, $updates, $HD_Form->list_query_conditions)) {
        $update_msg = _('Could not perform the batch update!');
    } else {
        $update_msg = _('The batch update has been successfully perform!');
        if (!empty($refill_cards)) {
            if (!(new Table("cc_logrefill"))->addRows($HD_Form->DBHandle, $refill_cards)) {
                $update_msg = _('Could not perform refill log for the batch update!');
            }
        }
    }
}

$id = $id ?? 0;
$form_action ??= "list";
$list = $HD_Form->perform_action($form_action);

require_once __DIR__ . "/../templates/main.php";
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

<?php if ($form_action === "list" && !$popup_select) {
    // populate some lists for the batch update settings
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

    echo create_help(
        _("Customers are listed below by account number. Each row corresponds to one customer, along with information such as their call plan, credit remaining, etc.")
            . "<br/>"
            . _("The SIP and IAX buttons create SIP and IAX entries to allow direct VoIP connections to the Asterisk server without further authentication."),
        'ListCustomers'
    );
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

<div class="modal" id="batchUpdateModal" aria-labelledby="modal-title-udpate" aria-hidden="true" role="dialog">
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
                            <input name="check[upd_inuse]" type="checkbox" value="on" aria-label="check to enable updates to this field" <?php if (!empty($check["upd_inuse"])): ?> checked="checked"<?php endif ?> class="form-check-input"/>
                            <label class="form-label form-label-sm" for="upd_inuse">
                                <?= _("In use") ?>
                            </label>
                        </div>
                        <div class="col">
                            <input type="number" name="upd_inuse" id="upd_inuse" min="0" max="1" value="<?= $update_fields["inuse"] ?? 0 ?>" class="form-control form-control-sm">
                        </div>
                    </div>


                    <div class="row mb-1">
                        <div class="col-4">
                            <input name="check[upd_status]" type="checkbox" value="on" aria-label="check to enable updates to this field" <?php if (!empty($check["upd_status"])): ?> checked="checked"<?php endif ?> class="form-check-input"/>
                            <label class="form-label form-label-sm" for="upd_status">
                                <?= _("Status") ?>
                            </label>
                        </div>
                        <div class="col">
                            <select name="upd_status" id="upd_status" class="form-select form-select-sm">
                                <?php foreach ($cardstatus_list as $v): ?>
                                <option value="<?= $v[1] ?>" <?php if (($update_fields["status"] ?? "") == $v[1]): ?>selected="selected"<?php endif ?>><?= $v[0] ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>


                    <div class="row mb-1">
                        <div class="col-4">
                            <input name="check[upd_language]" type="checkbox" value="on" aria-label="check to enable updates to this field" <?php if (!empty($check["upd_language"])): ?> checked="checked"<?php endif ?> class="form-check-input"/>
                            <label class="form-label form-label-sm" for="upd_language">
                                <?= _("Language") ?>
                            </label>
                        </div>
                        <div class="col">
                            <select name="upd_language" id="upd_language" class="form-select form-select-sm">
                                <?php foreach ($language_list as $v): ?>
                                    <option value="<?= $v[1] ?>" <?php if (($update_fields["language"] ?? "") == $v[1]): ?>selected="selected"<?php endif ?>><?= $v[0] ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>


                    <div class="row mb-1">
                        <div class="col-4">
                            <input name="check[upd_tariff]" type="checkbox" value="on" aria-label="check to enable updates to this field" <?php if (!empty($check["upd_tariff"])): ?> checked="checked"<?php endif ?> class="form-check-input"/>
                            <label class="form-label form-label-sm" for="upd_tariff">
                                <?= _("Tariff") ?>
                            </label>
                        </div>
                        <div class="col">
                            <select name="upd_tariff" id="upd_tariff" class="form-select form-select-sm">
                                <?php foreach ($list_tariff as $v): ?>
                                    <option value="<?= $v[0] ?>" <?php if (($update_fields["tariff"] ?? "") == $v[0]): ?>selected="selected"<?php endif ?>><?= $v[1] ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>


                    <div class="row mb-1">
                        <div class="col-4">
                            <input name="check[upd_credit]" id="check[upd_credit]" type="checkbox" value="on" aria-label="check to enable updates to this field" <?php if (!empty($check["upd_credit"])): ?> checked="checked" <?php endif ?> class="form-check-input"/>
                            <input name="mode[upd_credit]" type="hidden" value="2"/>
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
                            <label class="form-label form-label-sm ps-3" for="upd_refill_type">
                                <?= _("Refill") ?>
                            </label>
                        </div>
                        <div class="col">
                            <select name="upd_refill_type" id="upd_refill_type" class="form-select form-select-sm">
                                <?php foreach ($list_refill_type as $v): ?>
                                <option value="<?= $v[1] ?>" <?php if (($update_fields["refill_type"] ?? "") == $v[1]): ?>selected="selected"<?php endif?>><?= $v[0] ?></option>
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
                            <input type="text" name="upd_description" id="upd_description" value="<?= $update_fields["description"] ?? "" ?>" class="form-control form-control-sm"/>
                        </div>
                    </div>


                    <div class="row mb-1">
                        <div class="col-4">
                            <input name="check[upd_simultaccess]" type="checkbox" value="on" aria-label="check to enable updates to this field" <?php if (!empty($check["upd_simultaccess"])): ?> checked="checked" <?php endif ?> class="form-check-input"/>
                            <label class="form-label form-label-sm" for="upd_simultaccess">
                                <?= _("Access") ?>
                            </label>
                        </div>
                        <div class="col">
                            <select name="upd_simultaccess" id="upd_simultaccess" class="form-select form-select-sm">
                                <option value="0" <?php if (($update_fields["simultaccess"] ?? 0) == 0): ?>selected="selected"<?php endif?>><?= _("Individual Access") ?></option>
                                <option value="1" <?php if (($update_fields["simultaccess"] ?? 0) == 1): ?>selected="selected"<?php endif?>><?= _("Simultaneous Access") ?></option>
                            </select>
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
                                <?php foreach (get_currencies() as $k=>$v): ?>
                                    <option value="<?= $k ?>" <?php if (($update_fields["currency"] ?? "") === $k): ?>selected="selected"<?php endif ?>><?= $v["name"] ?> (<?= $v["value"] ?>)</option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>


                    <div class="row mb-1">
                        <div class="col-4">
                            <input name="check[upd_creditlimit]" type="checkbox" value="on" aria-label="check to enable updates to this field" <?php if (!empty($check["upd_creditlimit"])): ?> checked="checked" <?php endif ?> class="form-check-input"/>
                            <input name="mode[upd_creditlimit]" type="hidden" value="2"/>
                            <label class="form-label form-label-sm" for="upd_creditlimit">
                                <?= _("Credit Limit") ?>
                            </label>
                        </div>
                        <div class="col-auto">
                            <input type="number" name="upd_creditlimit" id="upd_creditlimit" min="0" max="1000" value="<?= $update_fields["creditlimit"] ?? 0 ?>" class="form-control form-control-sm"/>
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
                            <input name="check[upd_enableexpire]" type="checkbox" value="on" aria-label="check to enable updates to this field" <?php if (!empty($check["upd_enableexpire"])): ?> checked="checked" <?php endif ?> class="form-check-input"/>
                            <label class="form-label form-label-sm" for="upd_enableexpire">
                                <?= _("Enable Expire") ?>
                            </label>
                        </div>
                        <div class="col">
                            <select name="upd_enableexpire" id="upd_enableexpire" class="form-select form-select-sm">
                                <option value="0" <?php if (($update_fields["enableexpire"] ?? 0) == 0): ?>selected="selected"<?php endif?>><?= _("No Expiry") ?></option>
                                <option value="1" <?php if (($update_fields["enableexpire"] ?? 0) == 1): ?>selected="selected"<?php endif?>><?= _("Expire Date") ?></option>
                                <option value="2" <?php if (($update_fields["enableexpire"] ?? 0) == 2): ?>selected="selected"<?php endif?>><?= _("Expire Days Since First Use") ?></option>
                                <option value="3" <?php if (($update_fields["enableexpire"] ?? 0) == 3): ?>selected="selected"<?php endif?>><?= _("Expire Days Since Creation") ?></option>
                            </select>
                        </div>
                    </div>


                    <div class="row mb-1">
                        <div class="col-4">
                            <input name="check[upd_expirationdate]" type="checkbox" value="on" aria-label="check to enable updates to this field" <?php if (!empty($check["upd_expirationdate"])): ?> checked="checked" <?php endif ?> class="form-check-input"/>
                            <label class="form-label form-label-sm" for="upd_expirationdate">
                                <?= _("Expiry Date") ?>
                            </label>
                        </div>
                        <div class="col">
                            <input type="datetime-local" name="upd_expirationdate" id="upd_expirationdate" value="<?= $update_fields["expirationdate"] ?? (new DateTime("now + 10 years"))->format("Y-m-d\TH:i") ?>" class="form-control form-control-sm"/>
                        </div>
                    </div>


                    <div class="row mb-1">
                        <div class="col-4">
                            <input name="check[upd_expiredays]" type="checkbox" value="on" aria-label="check to enable updates to this field" <?php if (!empty($check["upd_expiredays"])): ?> checked="checked" <?php endif ?> class="form-check-input"/>
                            <label class="form-label form-label-sm" for="upd_expiredays">
                                <?= _("Expiration Days") ?>
                            </label>
                        </div>
                        <div class="col">
                            <input type="number" name="upd_expiredays" id="upd_expiredays" min="1" max="3650" value="<?= $update_fields["expiredays"] ?? 0 ?>" class="form-control form-control-sm"/>
                        </div>
                    </div>


                    <div class="row mb-1">
                        <div class="col-4">
                            <input name="check[upd_runservice]" type="checkbox" value="on" aria-label="check to enable updates to this field" <?php if (!empty($check["upd_runservice"])): ?> checked="checked" <?php endif ?> class="form-check-input"/>
                            <input name="mode[upd_runservice]" type="hidden" value="2"/>
                            <label class="form-label form-label-sm" for="upd_runservice">
                                <?= _("Run Service") ?>
                            </label>
                        </div>
                        <div class="col-auto">
                            <input type="number" name="upd_runservice" id="upd_runservice" min="0" max="1000" value="<?= $update_fields["runservice"] ?? 0 ?>" class="form-control form-control-sm"/>
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
                            <input name="check[upd_id_group]" type="checkbox" value="on" aria-label="check to enable updates to this field" <?php if (!empty($check["upd_id_group"])): ?> checked="checked"<?php endif ?> class="form-check-input"/>
                            <label class="form-label form-label-sm" for="upd_id_group">
                                <?= _("Group this batch belongs to") ?>
                            </label>
                        </div>
                        <div class="col">
                            <select name="upd_id_group" id="upd_id_group" class="form-select form-select-sm">
                                <?php foreach ($list_group as $v): ?>
                                    <option value="<?= $v[0] ?>" <?php if (($update_fields["id_group"] ?? "") == $v[0]): ?>selected="selected"<?php endif ?>><?= $v[1] ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>


                    <div class="row mb-1">
                        <div class="col-4">
                            <input name="check[upd_discount]" type="checkbox" value="on" aria-label="check to enable updates to this field" <?php if (!empty($check["upd_discount"])): ?> checked="checked" <?php endif ?> class="form-check-input"/>
                            <label class="form-label form-label-sm" for="upd_discount">
                                <?= _("Discount") ?>
                            </label>
                        </div>
                        <div class="col input-group">
                            <input type="number" name="upd_discount" id="upd_discount" min="0" max="99" value="<?= $update_fields["discount"] ?? 0 ?>" class="form-control form-control-sm"/>
                            <span class="input-group-text">%</span>
                        </div>
                    </div>


                    <div class="row mb-1">
                        <div class="col-4">
                            <input name="check[upd_id_seria]" type="checkbox" value="on" aria-label="check to enable updates to this field" <?php if (!empty($check["upd_id_seria"])): ?> checked="checked"<?php endif ?> class="form-check-input"/>
                            <label class="form-label form-label-sm" for="upd_id_seria">
                                <?= _("Move to Card Series") ?>
                            </label>
                        </div>
                        <div class="col">
                            <select name="upd_id_seria" id="upd_id_seria" class="form-select form-select-sm">
                                <?php foreach ($list_seria as $v): ?>
                                    <option value="<?= $v[0] ?>" <?php if (($update_fields["id_seria"] ?? "") === $v[0]): ?>selected="selected"<?php endif ?>><?= $v[1] ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>


                    <div class="row mb-1">
                        <div class="col-4">
                            <input name="check[upd_vat]" type="checkbox" value="on" aria-label="check to enable updates to this field" <?php if (!empty($check["upd_vat"])): ?> checked="checked" <?php endif ?> class="form-check-input"/>
                            <label class="form-label form-label-sm" for="upd_vat">
                                <?= _("VAT") ?>
                            </label>
                        </div>
                        <div class="col input-group">
                            <input type="number" name="upd_vat" id="upd_vat" min="0" max="99" value="<?= $update_fields["vat"] ?? 0 ?>" class="form-control form-control-sm"/>
                            <span class="input-group-text">%</span>
                        </div>
                    </div>


                    <div class="row mb-1">
                        <div class="col-4">
                            <input name="check[upd_country]" type="checkbox" value="on" aria-label="check to enable updates to this field" <?php if (!empty($check["upd_country"])): ?> checked="checked"<?php endif ?> class="form-check-input"/>
                            <label class="form-label form-label-sm" for="upd_country">
                                <?= _("Country") ?>
                            </label>
                        </div>
                        <div class="col">
                            <select name="upd_country" id="upd_country" class="form-select form-select-sm">
                                <?php foreach ($list_country as $v): ?>
                                    <option value="<?= $v[0] ?>" <?php if (($update_fields["country"] ?? $A2B->config["global"]["base_country"] ?? "") === $v[0]): ?>selected="selected"<?php endif ?>><?= $v[1] ?></option>
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
<?php
    endif; // endif is_sip_iax_change
} elseif ($form_action !== "list" && !$popup_select) {
    echo create_help(
        _("Create and edit the properties of each customer. Click CONFIRM DATA at the bottom of the page to save changes."),
        'CreateCustomer'
    );
}

$HD_Form->create_toppage($form_action);
echo $update_msg ?? "";

if ($form_action === "ask-add"):?>
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
    .on("change", ev => toggleUpdateField(ev.target));
// special case
$("#check\\[upd_credit\\]")
    .each((i, el) => $("#upd_refill_type, #upd_description").attr("disabled", !el.checked))
    .on("change", ev => $("#upd_refill_type, #upd_description").attr("disabled", !ev.target.checked));
</script>

<?php require_once __DIR__ . "/../templates/footer.php";
