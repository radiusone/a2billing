<?php

use A2billing\A2Billing;
use A2billing\Agent;
use A2billing\Forms\FormHandler;
use A2billing\Realtime;
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

$menu_section = 1;
require_once __DIR__ . "/../../common/lib/agent.defines.php";
require_once __DIR__ . "/../../common/form_data/FG_var_card.inc";
/**
 * @var FormHandler $HD_Form
 * @var A2Billing $A2B
 * @var string $form_action
 * @var string $action
 */

Agent::checkPageAccess(Agent::ACX_GENERATE_CUSTOMER);

getpost_ifset(['nb_to_create', 'creditlimit', 'choose_tariff', 'choose_simultaccess',
    'choose_currency', 'choose_typepaid', 'enableexpire', 'expirationdate', 'expiredays', 'runservice', 'sip', 'iax',
    'cardnumber_length', 'tag', 'id_group', 'discount']);
/**
 * @var numeric-string|null $nb_to_create
 * @var numeric-string|null $creditlimit
 * @var numeric-string|null $choose_tariff
 * @var numeric-string|null $choose_simultaccess
 * @var numeric-string|null $choose_currency
 * @var numeric-string|null $choose_typepaid
 * @var numeric-string|null $enableexpire
 * @var string $expirationdate
 * @var numeric-string|null $expiredays
 * @var numeric-string|null $runservice
 * @var numeric-string|null $sip
 * @var numeric-string|null $iax
 * @var numeric-string|null $cardnumber_length
 * @var string $tag
 * @var numeric-string|null $id_group
 * @var numeric-string|null $discount
 */

$id_group = (int)($id_group ?? 0);
$choose_tariff = (int)($choose_tariff ?? 0);
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

if ($action === "generate") {
    if ($id_group < 1) {
        $errors["id_group"] = _("Choose a GROUP for the customers");
    }
    if ($choose_tariff < 1) {
        $errors["choose_tariff"] =  _("Choose a CALL PLAN for the customers");
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
        [$accountnumber, $useralias] = gen_card_with_alias($cardnumber_length);
        $passui_secret = generate_random_value("#####XXXXXXXXXX#####");

        (new Table("cc_card"))->addRow([
            "username" => $accountnumber, "useralias" => $useralias, "tariff" => $choose_tariff, "lastname" => $gen_id,
            "simultaccess" => $choose_simultaccess, "currency" => $choose_currency, "typepaid" => $choose_typepaid,
            "creditlimit" => $creditlimit, "enableexpire" => $enableexpire, "expirationdate" => $expirationdate,
            "expiredays" => $expiredays, "uipass" => $passui_secret, "runservice" => $runservice, "tag" => $tag,
            "id_group" => $id_group, "discount" => $discount, "sip_buddy" => $sip_buddy, "iax_buddy" => $iax_buddy
        ], "id", $id_cc_card);

        if (!empty($sip) || !empty($iax)) {
            $instance_realtime->insert_voip_config((bool)$sip, (bool)$iax, $id_cc_card, $accountnumber, $passui_secret);
        }
    }

    if (!empty($sip)) {
        $instance_realtime->create_trunk_config_file();
    }
    if (!empty($iax)) {
        $instance_realtime->create_trunk_config_file("iax");
    }
}

$HD_Form->list_query_conditions["lastname"] = $_SESSION["IDfilter"];
// END GENERATE CARDS

$HD_Form->init();

$form_action ??= "list";
$list = $HD_Form->perform_action($form_action);

require_once __DIR__ . "/templates/main.php";

$HD_Form->list_help_text = create_help(
    _("Bulk create customers in a single step. <br> Set the properties of the batch such as initial credit, account type and currency, then click on the GENERATE CUSTOMERS button to create the batch.")
);
$HD_Form->create_toppage($form_action);

$list_tariff = (new Table("cc_tariffgroup", ["id", "tariffgroupname"], ["cc_agent_tariffgroup" => ["cc_agent_tariffgroup.id_tariffgroup", "cc_tariffgroup.id"]]))
    ->getRows(["cc_agent_tariffgroup.id_agent" => $_SESSION["agent_id"]], ["tariffgroupname"]);
$list_group = (new Table("cc_card_group", ["id", "name"]))
    ->getRows(["id_agent" => $_SESSION["agent_id"]]);

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
                    <?php foreach (getCurrencyValuesList() as $id => $val): ?>
                        <option value="<?= $id ?>" <?= ("$choose_currency" ?? "") === $id ? "selected='selected'" : "" ?>><?= $val ?></option>
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
                        value="<?= empty($errors["expiredays"]) ? $expiredays : "0" ?>"
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
        <div class="row my-4 justify-content-end">
            <div class="col-auto">
                <button type="submit" class="btn btn-primary"><?= _("Generate Customers") ?></button>
            </div>
        </div>
    </form>

<?php
$HD_Form->create_form($form_action, $list) ;
$HD_Form->setup_export();

require_once __DIR__ . "/templates/footer.php";
