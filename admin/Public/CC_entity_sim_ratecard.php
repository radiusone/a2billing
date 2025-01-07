<?php

use A2billing\A2Billing;
use A2billing\Admin;
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
/**
 * @var A2Billing $A2B
 * @var Smarty $smarty
 */

Admin::checkPageAccess(Admin::ACX_RATECARD);

getpost_ifset(['posted', 'tariffplan', 'balance', 'id_cc_card', 'called' , 'accountcode']);
/**
 * @var string $posted
 * @var string $tariffplan
 * @var string $balance
 * @var string $id_cc_card
 * @var string $called
 * @var string $accountcode
 */

$DBHandle  = DbConnect();
$error_msg = "";

if ($called && ($id_cc_card > 0 || $accountcode > 0)) {
    if ($accountcode > 0) {
        $list_tariff_card = (new Table("cc_card", "username, id"))->getRow($DBHandle, ["username" => $accountcode]);
        if ($list_tariff_card) {
            $id_cc_card = $list_tariff_card["id"] ?? 0;
        }
    }

    if (!empty($called) && is_numeric($called)) {
        $num = 0;
        $card = (new Table("cc_card", "username, tariff, credit"))->getRow($DBHandle, ["id" => $id_cc_card]);
        if (empty($card)) {
            $error_msg = '<span style="color:red; font-weight: bold">' . _("Card lookup error") . '</span>';
        } else {
            $A2B->cardnumber = $card["username"];

            if ($A2B->callingcard_ivr_authenticate_light($error_msg, (int)$balance)) {
                $RateEngine = $A2B->rateEngine();

                // LOOKUP RATE : FIND A RATE FOR THIS DESTINATION
                $A2B->agiconfig['accountcode'] = $A2B->cardnumber;
                $A2B->agiconfig['use_dnid'] = 1;
                $A2B->agiconfig['say_timetocall'] = 0;
                $A2B->dnid = $A2B->destination = $called;

                if ($A2B->removeinterprefix) {
                    $A2B->destination = $A2B->apply_rules($A2B->destination);
                }

                $resfindrate = $RateEngine->rate_engine_findrates($A2B->destination, (int)$card["tariff"]);

                // IF FIND RATE
                if ($resfindrate) {
                    $res_all_calcultimeout = $RateEngine->rate_engine_all_calcultimeout($A2B->credit);
                } else {
                    $error_msg = '<span style="color:red; font-weight: bold">' . _("No matching rate found") . '</span>';
                }
            }
        }
    }
}

require_once __DIR__ . "/../templates/main.php";

echo create_help(
    _('Please select an account, then enter the number you wish to call and press the "SIMULATE" button.')
);
?>
<form method="post" name="simulator">
    <div class="row mb-3">
        <div class="col">
            <label class="form-label" for="called"><?= _("Number to call") ?></label>
            <input type="text" name="called" id="called" class="form-control" value="<?= $called ?? "" ?>" required="required" pattern="[0-9]+"/>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col">
            <label class="form-label" for="balance"><?= _("Initial credit") ?></label>
            <input type="text" name="balance" id="balance" class="form-control" value="<?= $balance ?? 0 ?>" required="required" pattern="[0-9]+"/>
            <small class="form-text"><?= _("Leave at 0 to use card balance") ?></small>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-5">
            <label class="form-label" for="accountcode"><?= _("Card number") ?></label>
            <input type="text" name="accountcode" id="accountcode" class="form-control" value="<?= $accountcode ?? "" ?>" pattern="[0-9]*"/>
        </div>
        <div class="col-1">
            <?= _("OR") ?>
        </div>
        <div class="col-6">
            <label class="form-label" for="id_cc_card"><?= _("Card ID") ?></label>
            <div class="input-group">
                <input type="text" name="id_cc_card" id="id_cc_card" class="form-control" value="<?= $id_cc_card ?? "" ?>" pattern="[0-9]*"/>
                <a href="A2B_entity_card.php" data-window-name="destinationPopup" data-form-name="simulator" data-field-name="id_cc_card" data-popup-options="width=750,height=450,top=50,left=100,scrollbars=1" class="btn btn-primary popup_trigger" aria-label="open a popup to select an item">
                    <svg class="mx-auto" width="16" height="16"><use xlink:href="#popup"></use></svg>
                </a>
            </div>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col">
            <button type="submit" class="btn btn-primary"><?= _("Submit") ?></button>
        </div>
    </div>
</form>

<?php

if (!empty($RateEngine->ratecard_obj)) {
    $arr_ratecard= [
        "tariffgroupname" => _("Call plan"), "tariffname" => _("Ratecard"), "tp_trunkcode" => _("Trunk"),
        "dialprefix" => _("Dial prefix"), "lcrtype" => _("LCR type"), "rateinitial" => _("Sell rate"),
        "initblock" => _("Sell rate minimum"), "billingblock" => _("Sell rate increment"),
        "buyrate" => _("Buy rate"), "buyrateinitblock" => _("Buy rate minimum"),
        "buyrateincrement" => _("Buy rate increment"), "connectcharge" => _("Connection charge"),
        "disconnectcharge" => _("Disconnect charge"), "disconnectcharge_after" => _("Time before disconnect charge"),
    ];
    if (!empty($A2B->config['webui']['advanced_mode'])) {
        $arr_ratecard = array_merge(
            $arr_ratecard,
            [
                // don't know what these are so can't make a label for them
                "stepchargea" => null, "chargea" => null, "timechargea" => null, "billingblocka" => null,
                "stepchargeb" => null, "chargeb" => null, "timechargeb" => null, "billingblockb" => null,
                "stepchargec" => null, "chargec" => null, "timechargec" => null, "billingblockc" => null,
            ]
        );
    }
?>
<div class="row mb-3">
    <div class="col">
        <table class="table table-striped caption-top">
            <caption>
                <?php if (count($RateEngine->ratecard_obj) > 1): ?>
                <?= sprintf(_("Simulator found %d rates for your destination"), count($RateEngine->ratecard_obj)) ?>
                <?php else: ?>
                <?= _("Simulator found a rate for your destination") ?>
                <?php endif ?>
            </caption>
            <?php foreach ($RateEngine->ratecard_obj as $i => $ratecard): ?>
            <tbody class="mb-3">
            <?php if (count($RateEngine->ratecard_obj) > 1): ?>
                <tr class="table-info">
                    <th colspan="2"><?= sprintf(_("Rate #%d"), $i + 1) ?></th>
                </tr>
            <?php endif ?>
                <tr>
                    <th scope="row"><?= _("MAX DURATION FOR THE CALL") ?></th>
                    <td><?= get_timespan($ratecard["timeout"]) ?></td>
                </tr>
            <?php if ($ratecard["freetime_include_in_timeout"]): ?>
                <tr>
                    <th scope="row"><?= _("FREE TIME INCLUDED IN THE DURATION") ?></th>
                    <td><?= get_timespan($ratecard["freetime_include_in_timeout"]) ?></td>
                </tr>
            <?php endif ?>
            <?php if ((int)$A2B->agiconfig["cheat_on_announcement_time"] === 1): ?>
                <tr>
                    <th scope="row"><?= _("TIME ANNOUCEMENT FOR THE CALL") ?></th>
                    <td><?= get_timespan($ratecard["time_without_rules"]) ?></td>
                </tr>
            <?php endif ?>
            <?php if ($ratecard["announce_time_correction"] > 0): ?>
                <tr>
                    <th scope="row"><?= _("Announce correction") ?></th>
                    <td>x<?= $ratecard["announce_time_correction"] ?></td>
                </tr>
            <?php endif ?>
                <tr>
                    <th scope="row"><?= _("Destination") ?></th>
                    <td><?= (new Table("cc_prefix", "destination"))->getRow($DBHandle, ["prefix" => $ratecard["destination"]])["destination"] ?? "" ?></td>
                </tr>
            <?php foreach ($arr_ratecard as $col => $label): ?>
                <tr>
                    <th scope="row"><?= $label ?? $col ?></th>
                    <td><?= $ratecard[$col] ?></td>
                </tr>
            <?php endforeach ?>
            </tbody>
            <?php endforeach ?>
        </table>
    </div>
</div>

<?php
} else {
    echo '<span style="color:red; font-weight: bold">' . $error_msg . '</span>';
}
require_once __DIR__ . "/../templates/footer.php";
