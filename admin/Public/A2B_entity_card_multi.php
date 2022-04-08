<?php

use A2billing\A2Billing;
use A2billing\Forms\FormHandler;
use A2billing\Table;
use A2billing\Realtime;

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

require_once "../../common/lib/admin.defines.php";
include './form_data/FG_var_card.inc';
/**
 * @var FormHandler $HD_Form
 * @var A2Billing $A2B
 * @var SmartyBC $smarty
 * @var string $form_action
 * @var string $action
 * @var string $CC_help_generate_customer
 */

if (!has_rights(ACX_CUSTOMER)) {
    header("HTTP/1.0 401 Unauthorized");
    header("Location: PP_error.php?c=accessdenied");
    die();
}

getpost_ifset([
    'nb_to_create', 'creditlimit', 'cardnum', 'addcredit', 'choose_tariff', 'gen_id', 'cardnum', 'choose_simultaccess',
    'choose_currency', 'choose_typepaid', 'creditlimit', 'enableexpire', 'expirationdate', 'expiredays', 'runservice', 'sip', 'iax',
    'cardnumber_length', 'tag', 'id_group', 'discount', 'id_seria', 'id_didgroup', 'vat', 'id_country',
]);
/**
 * @var string $nb_to_create
 * @var string $creditlimit
 * @var string $cardnum
 * @var string $addcredit
 * @var string $choose_tariff
 * @var string $gen_id
 * @var string $cardnum
 * @var string $choose_simultaccess
 * @var string $choose_currency
 * @var string $choose_typepaid
 * @var string $creditlimit
 * @var string $enableexpire
 * @var string $expirationdate
 * @var string $expiredays
 * @var string $runservice
 * @var string $sip
 * @var string $iax
 * @var string $cardnumber_length
 * @var string $tag
 * @var string $id_group
 * @var string $discount
 * @var string $id_seria
 * @var string $id_didgroup
 * @var string $vat
 * @var string $id_country
 */

$id_group = (int)($id_group ?? 0);
$choose_tariff = (int)($choose_tariff ?? 0);
$addcredit = (int)($addcredit ?? 0);
$expiredays = (int)($expiredays ?? 0);
$expirationdate = empty($expirationdate) ? "zzzzzz" : $expirationdate;
try {
    $expirationdate = (new DateTime($expirationdate))->format("Y-m-d H:i:s");
} catch (\Exception $e) {
    $expirationdate = null;
}
$nb_to_create = (int)($nb_to_create ?? 0);
$instance_realtime = new Realtime();

$errors = [];

if ($action == "generate") {
    if ($id_group < 1) {
        $errors["id_group"] = _("Choose a GROUP for the customers");
    }
    if ($choose_tariff < 1) {
        $errors["choose_tariff"] =  _("Choose a CALL PLAN for the customers");
    }
    if ($addcredit < 0) {
        $errors["addcredit"] = _("Choose an initial BALANCE of at least 0 for the customers");
    }
    if ($expiredays < 0) {
        $errors["expiredays"] = _("Choose EXPIRATIONS DAYS of at least 0 for the customers");
    }
    if (!$expirationdate) {
        $errors["expirationdate"] = _("EXPIRATION DATE should be in the format YYYY-MM-DD HH:MM");
    }
    if ($nb_to_create < 1) {
        $errors["nb_to_create"] = _("Choose the number of customers that you want to generate");
    }
}

$_SESSION["IDfilter"] = 'NODEFINED';

if ($nb_to_create > 0 && $action === "generate" && count($errors) === 0) {
    $_SESSION["IDfilter"] = $gen_id = time();
    $sip_buddy = !empty($sip) ? 1 : 0;
    $iax_buddy = !empty($iax) ? 1 : 0;
    $creditlimit = (int)($creditlimit ?? 0);

    for ($k = 0; $k < $nb_to_create; $k++) {
        [$accountnumber, $useralias] = gen_card_with_alias("cc_card", $cardnumber_length);
        $passui_secret = MDP_NUMERIC(5) . MDP_STRING(10) . MDP_NUMERIC(5);

        $datecol = $dateval = "";
        if (DB_TYPE === "mysql") {
            $datecol = ", creationdate";
            $dateval = ", NOW()";
        }

        $HD_Form->DBHandle->enableLastInsertID();
        $result = $HD_Form->DBHandle->Execute(
            "INSERT INTO cc_card (
                 username, useralias, credit, tariff, activated, lastname, firstname, email, address, city, state, country, 
                 zipcode, phone, simultaccess, currency, typepaid, creditlimit, enableexpire, expirationdate, expiredays, 
                 uipass, runservice, tag,id_group, discount, id_seria, id_didgroup, sip_buddy, iax_buddy, vat $datecol
             )
            VALUES (?, ?, ?, ?, 't', ?, '', '', '', '', '', ?, '', '', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? $dateval)",
            [
                $accountnumber, $useralias, $addcredit, $choose_tariff, $gen_id, $id_country, $choose_simultaccess,
                $choose_currency, $choose_typepaid, $creditlimit, $enableexpire, $expirationdate, $expiredays, $passui_secret,
                $runservice, $tag, $id_group, $discount, $id_seria, $id_didgroup, $sip_buddy, $iax_buddy, $vat,
            ]
        );
        $id_cc_card = $HD_Form->DBHandle->Insert_ID();

        //create refill for each cards
        if ($addcredit > 0) {
            $HD_Form->DBHandle->Execute(
                "INSERT INTO cc_logrefill (credit, card_id, description) VALUES (?, ?, ?)",
                [$addcredit, $id_cc_card, _("CREATION CARD REFILL")]
            );
        }

        if ($sip || $iax) {
            $instance_realtime->insert_voip_config($sip, $iax, $id_cc_card, $accountnumber, $passui_secret);
        }
    }

    if ($sip) {
        $instance_realtime->create_trunk_config_file();
    }
    if (isset ($iax)) {
        $instance_realtime->create_trunk_config_file('iax');
    }
}

$HD_Form->FG_QUERY_WHERE_CLAUSE = " lastname='$_SESSION[IDfilter]'";

// END GENERATE CARDS

$HD_Form->init();

if (!empty($id)) {
    $HD_Form->FG_EDIT_QUERY_CONDITION = str_replace("%id", "$id", $HD_Form->FG_EDIT_QUERY_CONDITION);
}

$form_action = $form_action ?? "list";
$action = $action ?? $form_action;

$list = $HD_Form->perform_action($form_action);

// #### HEADER SECTION
$smarty->display('main.tpl');

// #### HELP SECTION
echo $CC_help_generate_customer;

$result = $HD_Form->DBHandle->CacheExecute(300, "SELECT id, tariffgroupname AS name FROM cc_tariffgroup ORDER BY tariffgroupname");
$list_tariff = $result ? $result->GetAll() : [];

$result = $HD_Form->DBHandle->CacheExecute(300, "SELECT id, name FROM cc_card_group ORDER BY name");
$list_group = $result ? $result->GetAll() : [];

$result = $HD_Form->DBHandle->CacheExecute(300, "SELECT id, login AS name FROM cc_agent ORDER BY login");
$list_agent = $result ? $result->GetAll() : [];

$result = $HD_Form->DBHandle->CacheExecute(300, "SELECT id, name FROM cc_card_seria ORDER BY name");
$list_seria = $result ? $result->GetAll() : [];

$result = $HD_Form->DBHandle->CacheExecute(300, "SELECT id, didgroupname AS name FROM cc_didgroup ORDER BY didgroupname");
$list_didgroup = $result ? $result->GetAll() : [];

$result = $HD_Form->DBHandle->CacheExecute(300, "SELECT countrycode AS id, countryname AS name FROM cc_country ORDER BY countryname");
$list_country = $result ? $result->GetAll() : [];

// FORM FOR THE GENERATION
?>
<?php if (count($errors) > 0 ): ?>
<div class="row pb-3 text-danger">
    <?= _("Errors were found in the input:") ?><br/>
    <?php foreach ($errors as $err): ?>
        <?= $err ?><br/>
    <?php endforeach ?>
</div>
<?php endif ?>

<form name="theForm" action="" method="POST">
    <?= $HD_Form->csrf_inputs() ?>
    <input type="hidden" name="action" value="generate"/>
    <div class="row pb-3">
        <label class="col-4 col-form-label" for="cardnumber_length">
            <?= _("Length of card number :") ?>
        </label>
        <div class="col-8">
            <select name="cardnumber_length" id="cardnumber_length" class="form-select">
                <?php foreach ($A2B->cardnumber_range as $value): ?>
                <option value="<?= $value ?>"><?= sprintf(_("%d digits"), $value) ?></option>
                <?php endforeach ?>
            </select>
        </div>
    </div>
    <div class="row pb-3">
        <label class="col-4 col-form-label" for="nb_to_create">
            <?= _("Number of customers to create") ?>
        </label>
        <div class="col-8">
            <input type="number" name="nb_to_create" id="nb_to_create" class="form-control" value="<?= $nb_to_create ?? "" ?>" min="1" max="999"/>
        </div>
    </div>
    <div class="row pb-3">
        <label class="col-4 col-form-label" for="choose_tariff">
            <?= _("Call plan") ?>
        </label>
        <div class="col-8">
            <select name="choose_tariff" id="choose_tariff" class="form-select <?= empty($errors["choose_tariff"]) ? "" : "is-invalid" ?>">
                <option value=""><?= _("Choose a call plan") ?></option>
                <?php foreach ($list_tariff as $plan): ?>
                <option value="<?= $plan["id"] ?>" <?= ($plan["id"] === "$choose_tariff" ?? "") ? 'selected="selected"' : "" ?>><?= $plan["name"] ?></option>
                <?php endforeach ?>
            </select>
        </div>
    </div>
    <div class="row pb-3">
        <label class="col-4 col-form-label" for="addcredit">
            <?= _("Initial amount of credit") ?>
        </label>
        <div class="col-8">
            <input
                type="number"
                name="addcredit"
                id="addcredit"
                class="form-control <?= empty($errors["addcredit"]) ? "" : "is-invalid" ?>"
                value="<?= empty($errors["addcredit"]) ? $addcredit : "" ?>"
                min="0"
                max="999"
            />
        </div>
    </div>
    <div class="row pb-3">
        <label class="col-4 col-form-label" for="choose_simultaccess">
            <?= _("Simultaneous access") ?>
        </label>
        <div class="col-8">
            <select name="choose_simultaccess" id="choose_simultaccess" class="form-select">
                <option value="0" <?= ("$choose_simultaccess" ?? "0") === "0" ? "selected='selected'" : "" ?>><?= _("Individual access") ?></option>
                <option value="1" <?= ("$choose_simultaccess" ?? "0") === "1" ? "selected='selected'" : "" ?>><?= _("Simultaneous access") ?></option>
            </select>
        </div>
    </div>
    <div class="row pb-3">
        <label class="col-4 col-form-label" for="choose_currency">
            <?= _("Currency") ?>
        </label>
        <div class="col-8">
            <select name="choose_currency" id="choose_currency" class="form-select <?= empty($errors["choose_currency"]) ? "" : "is-invalid" ?>">
                <?php foreach (get_currencies() as $id => $val): ?>
                <option value="<?= $id ?>" <?= ("$choose_currency" ?? "") === $id ? "selected='selected'" : "" ?>><?= $val["name"] ?> (<?= $val["value"] ?>)</option>
                <?php endforeach ?>
            </select>
        </div>
    </div>
    <div class="row pb-3">
        <label class="col-4 col-form-label" for="choose_typepaid">
            <?= _("Card type") ?>
        </label>
        <div class="col-8">
            <select name="choose_typepaid" id="choose_typepaid" class="form-select">
                <option value="0" <?= ("$choose_typepaid" ?? "0") === "0" ? "selected='selected'" : "" ?>><?= _("Prepaid") ?></option>
                <option value="1" <?= ("$choose_typepaid" ?? "0") === "1" ? "selected='selected'" : "" ?>><?= _("Postpaid") ?></option>
            </select>
        </div>
    </div>
    <div class="row pb-3">
        <label class="col-4 col-form-label" for="creditlimit">
            <?= _("Postpaid credit limit") ?>
        </label>
        <div class="col-8">
            <input type="number" name="creditlimit" id="creditlimit" class="form-control" min="0" max="999" value="<?= ($creditlimit ?? 0) * 1 ?>"/>
        </div>
    </div>
    <div class="row pb-3">
        <label class="col-4 col-form-label" for="enableexpire">
            <?= _("Expiration") ?>
        </label>
        <div class="col-8">
            <select name="enableexpire" id="enableexpire" class="form-select">
                <option value="0" <?= ("$enableexpire" ?? "0") === "0" ? "selected='selected'" : "" ?>><?= _("No expiration") ?></option>
                <option value="1" <?= ("$enableexpire" ?? "0") === "1" ? "selected='selected'" : "" ?>><?= _("Expires on date") ?></option>
                <option value="2" <?= ("$enableexpire" ?? "0") === "2" ? "selected='selected'" : "" ?>><?= _("Expires in days since first use") ?></option>
                <option value="3" <?= ("$enableexpire" ?? "0") === "3" ? "selected='selected'" : "" ?>><?= _("Expires in days since creation") ?></option>
            </select>
        </div>
    </div>
    <div class="row pb-3">
        <label class="col-4 col-form-label" for="expirationdate">
            <?= _("Expiry date") ?>
        </label>
        <div class="col-8">
            <input
                type="datetime-local"
                name="expirationdate"
                id="expirationdate"
                class="form-control <?= empty($errors["expirationdate"]) ? "" : "is-invalid" ?>"
                value="<?= empty($errors["expirationdate"]) ? $expirationdate : (new DateTime("now +10 years"))->format("Y-m-d\\TH:i") ?>"
            />
        </div>
    </div>
    <div class="row pb-3">
        <label class="col-4 col-form-label" for="expiredays">
            <?= _("Expiry days") ?>
        </label>
        <div class="col-8">
            <input
                type="number"
                name="expiredays"
                id="expiredays"
                class="form-control <?= empty($errors["expiredays"]) ? "" : "is-invalid" ?>"
                value="<?= empty($errors["expiredays"]) ? $addcredit : "0" ?>"
                min="0"
                max="999"
            />
        </div>
    </div>
    <div class="row pb-3">
        <label class="col-4 col-form-label" for="runservice">
            <?= _("Run service?") ?>
        </label>
        <div class="col-8">
            <select name="runservice" id="runservice" class="form-select">
                <option value="0" <?= ("$runservice" ?? "0") === "0" ? "selected='selected'" : "" ?>><?= _("No") ?></option>
                <option value="1" <?= ("$runservice" ?? "0") === "1" ? "selected='selected'" : "" ?>><?= _("Yes") ?></option>
            </select>
        </div>
    </div>
    <div class="row pb-3">
        <label class="col-4 col-form-label" for="sip">
            <?= _("Create SIP/IAX peers?") ?>
        </label>
        <div class="col-8 d-flex align-items-center">
            <div class="form-check form-check-inline">
                <input type="checkbox" name="sip" id="sip" value="1" class="form-check-input" <?= empty($sip) ? "" : "checked='checked'" ?>/>
                <label class="form-check-label" for="sip"><?= _("SIP") ?></label>
            </div>
            <div class="form-check form-check-inline">
                <input type="checkbox" name="iax" id="iax" value="1" class="form-check-input" <?= empty($iax) ? "" : "checked='checked'" ?>/>
                <label class="form-check-label" for="iax"><?= _("IAX") ?></label>
            </div>
        </div>
    </div>
    <div class="row pb-3">
        <label class="col-4 col-form-label" for="tag">
            <?= _("Tag") ?>
        </label>
        <div class="col-8">
            <input type="text" name="tag" id="tag" class="form-control" value="<?= $tag ?? "" ?>" maxlength="40"/>
        </div>
    </div>
    <div class="row pb-3">
        <label class="col-4 col-form-label" for="id_group">
            <?= _("Customer group") ?>
        </label>
        <div class="col-8">
            <select name="id_group" id="id_group" class="form-select <?= empty($errors["id_group"]) ? "" : "is-invalid" ?>">
                <option value=""><?= _("Choose a group") ?></option>
                <?php foreach ($list_group as $group): ?>
                    <option value="<?= $group["id"] ?>" <?= ($group["id"] === "$id_group" ?? "") ? 'selected="selected"' : "" ?>><?= $group["name"] ?></option>
                <?php endforeach ?>
            </select>
        </div>
    </div>
    <div class="row pb-3">
        <label class="col-4 col-form-label" for="discount">
            <?= _("Discount") ?>
        </label>
        <div class="col-8">
            <select name="discount" id="discount" class="form-select">
                <option value="0"><?= _("No discount") ?></option>
                <?php for ($i = 1; $i < 100; $i++): ?>
                    <option value="<?= $i ?>"<?= ($i === "$discount" ?? "0") ? 'selected="selected"' : "" ?>><?= $i ?>%</option>
                <?php endfor ?>
            </select>
        </div>
    </div>
    <div class="row pb-3">
        <label class="col-4 col-form-label" for="id_seria">
            <?= _("Card series") ?>
        </label>
        <div class="col-8">
            <select name="id_seria" id="id_seria" class="form-select">
                <option value=""><?= _("Choose a series") ?></option>
                <?php foreach ($list_seria as $group): ?>
                    <option value="<?= $group["id"] ?>" <?= ($group["id"] === "$id_seria" ?? "") ? 'selected="selected"' : "" ?>><?= $group["name"] ?></option>
                <?php endforeach ?>
            </select>
        </div>
    </div>
    <div class="row pb-3">
        <label class="col-4 col-form-label" for="id_didgroup">
            <?= _("DID group") ?>
        </label>
        <div class="col-8">
            <select name="id_didgroup" id="id_didgroup" class="form-select <?= empty($errors["id_didgroup"]) ? "" : "is-invalid" ?>">
                <option value=""><?= _("Choose a DID group") ?></option>
                <?php foreach ($list_didgroup as $group): ?>
                    <option value="<?= $group["id"] ?>" <?= ($group["id"] === "$id_didgroup" ?? "") ? 'selected="selected"' : "" ?>><?= $group["name"] ?></option>
                <?php endforeach ?>
            </select>
        </div>
    </div>
    <div class="row pb-3">
        <label class="col-4 col-form-label" for="vat">
            <?= _("VAT/GST") ?>
        </label>
        <div class="col-8">
            <input type="number" name="vat" id="vat" class="form-control" min="0" max="99" value="<?= ($vat ?? 0) * 1 ?>"/>
        </div>
    </div>
    <div class="row pb-3">
        <label class="col-4 col-form-label" for="id_country">
            <?= _("Country") ?>
        </label>
        <div class="col-8">
            <select name="id_country" id="id_country" class="form-select <?= empty($errors["id_country"]) ? "" : "is-invalid" ?>">
                <option value="0"><?= _("Choose a country") ?></option>
                <?php foreach ($list_country as $country): ?>
                    <option value="<?= $country["id"] ?>" <?= ($country["id"] === $id_country ?? "") ? 'selected="selected"' : "" ?>><?= $country["name"] ?></option>
                <?php endforeach ?>
            </select>
        </div>
    </div>
    <div class="row my-4 justify-content-end">
        <div class="col-auto">
            <button type="submit" class="btn btn-primary"><?= _("Generate Customers") ?></button>
        </div>
    </div>
</form>

<?php
// #### TOP SECTION PAGE

$HD_Form->FG_FILTER_ENABLE = false;
$HD_Form->FG_ENABLE_ADD_BUTTON = false;
$HD_Form->FG_ENABLE_INFO_BUTTON = false;
$HD_Form->FG_LIST_ADDING_BUTTON1 = false;
$HD_Form->FG_LIST_ADDING_BUTTON2 = false;

$HD_Form->create_toppage ($form_action);

$HD_Form->create_form($form_action, $list) ;

$_SESSION[$HD_Form->FG_EXPORT_SESSION_VAR]= "SELECT ".implode(",", $HD_Form->FG_EXPORT_FIELD_LIST)." FROM $HD_Form->FG_QUERY_TABLE_NAME";
if (strlen($HD_Form->FG_QUERY_WHERE_CLAUSE)>1) {
    $_SESSION[$HD_Form->FG_EXPORT_SESSION_VAR] .= " WHERE $HD_Form->FG_QUERY_WHERE_CLAUSE ";
}
if (!empty($HD_Form->FG_QUERY_ORDERBY_COLUMNS) && !empty($HD_Form->FG_QUERY_DIRECTION)) {
    $ord = implode(",", $HD_Form->FG_QUERY_ORDERBY_COLUMNS);
    $_SESSION[$HD_Form->FG_EXPORT_SESSION_VAR] .= " ORDER BY $ord $HD_Form->FG_QUERY_DIRECTION";
}

// #### FOOTER SECTION
$smarty->display('footer.tpl');
