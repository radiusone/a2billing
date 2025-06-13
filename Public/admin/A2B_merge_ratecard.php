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

set_time_limit(0);

Admin::checkPageAccess(Admin::ACX_RATECARD);

$HD_Form = new FormHandler('cc_ratecard', 'Rate Card');
$HD_Form->init();
$HD_Form->search_session_key = 'entity_ratecard_selection';

getpost_ifset(["posted" ,"idtariffplan", "ratecard_source", "ratecard_destination", "selected_cols", "posted_search"]);
/**
 * @var numeric-string|null $posted
 * @var numeric-string|null $idtariffplan
 * @var numeric-string|null $ratecard_source
 * @var numeric-string|null $ratecard_destination
 * @var string[]|null $selected_cols
 * @var string|null $posted_search
 */

$msgs = [];
$err = false;

if (!empty($posted_search) && empty($idtariffplan)) {
    $err = true;
    $msgs[] = _("No Source ratecard selected !");
}

if (!empty($posted)) {
    $fieldtomerge = [];
    $ratecard_src_val = $idtariffplan ?? null;
    if (!is_numeric($ratecard_src_val)) {
        $err = true;
        $msgs[] = _("No Source ratecard selected !");
    }

    $ratecard_des_val = $ratecard_destination ?? null;
    if (!is_numeric($ratecard_des_val)) {
        $err = true;
        $msgs[] = _("No Destination ratecard selected !");
    }

    if ($ratecard_des_val === $ratecard_src_val) {
        $err = true;
        $msgs[] = _("Source Ratecard Should be different from Destination ratecard!");
    }

    $fieldtomerge = $selected_cols ?? [];
    if (count($fieldtomerge) < 1) {
        $err = true;
        $msgs[] = _("Select fields to update");
    }

    if (!$err) {
        $condition = [];
        $count = 0;
        $fieldtomerge[] = "dialprefix";

        if (!empty($_SESSION['search_ratecard'])) {
            $condition = json_decode($_SESSION['search_ratecard']);
        }

        $instance_table = new Table("cc_ratecard", ["id"]);

        $source_result = (new Table("cc_ratecard", $fieldtomerge))->getRows(
            $HD_Form->DBHandle,
            $condition,
            ["dialprefix", "id"]
        );

        foreach ($source_result as $source_rate) {
            $check = $instance_table->getValue(
                $HD_Form->DBHandle,
                ["idtariffplan" => $ratecard_des_val, "dialprefix" => $source_rate["dialprefix"], "is_merged" => 0],
                ["dialprefix", "id"]
            );
            if ($check) {
                // so not actually "merging" but updating dest from source only if the dialprefix already exists
                $count++;
                $source_rate["is_merged"] = 1;
                $instance_table->updateRow($HD_Form->DBHandle, $source_rate, ["id" => $check]);
            }
        }
        $instance_table->updateRow($HD_Form->DBHandle, ["is_merged" => 0]);

        if($count > 0) {
            $msgs[] = sprintf(_("Ratecard is successfully merged, %d records updated."), $count);
        } else {
            $err = true;
            $msgs[] = _("Ratecard is not merged, please try again with different search criteria.");
        }
    }
    $_SESSION['search_ratecard'] = "";
}

$HD_Form->search_form_enabled = true;
$HD_Form->AddSearchDateInput(_("Start date"), "startdate");
$HD_Form->AddSearchTextInput(gettext("TAG"), 'tag');
$HD_Form->AddSearchTextInput(gettext("DESTINATION"), 'destination');
$HD_Form->AddSearchTextInput(gettext("PREFIX"), 'dialprefix');
$HD_Form->AddSearchComparisonInput(gettext("BUYRATE"), 'buyrate1', 'buyrate2', 'buyrate');
$HD_Form->AddSearchComparisonInput(gettext("RATE INITIAL"), 'rateinitial1', 'rateinitial2', 'rateinitial');
$HD_Form->prepare_list_subselection('list');
$HD_Form->AddSearchSqlSelectInput('SELECT TRUNK', "id_trunk", new Table("cc_trunk", ["id_trunk, trunkcode"]), "trunkcode");
$HD_Form->AddSearchSqlSelectInput("Source Ratecard", "idtariffplan", new Table("cc_tariffplan", ["id", "tariffname"]), "tariffname");
$HD_Form->prepare_list_subselection($form_action = "list");
$list = $HD_Form->perform_action($form_action);
$_SESSION['search_ratecard'] = json_encode($HD_Form->list_query_conditions);

$list_tariffname = (new Table("cc_tariffplan", "id, tariffname"))
    ->getRows ($HD_Form->DBHandle, [], ["tariffname"]);

require_once __DIR__ . "/templates/main.php";
$HD_Form->create_search_form();
?>

<?php if ($msgs): ?>
<div class="row my-3" role="alert">
    <div class="col">
        <p class="alert alert-<?= $err ? "danger" : "success"?>"><?= implode("<br/>", $msgs) ?></p>
    </div>
</div>
<?php endif ?>

<?php if ($posted_search && $err === false): ?>
<div class="row my-3" role="alert">
    <div class="col">
        <?php if (count($list)): ?>
        <p class="alert alert-info">
            <?= sprintf(_("%d matching rates found. Select destination ratecard and fields to update."), count($list)) ?>
        </p>
        <?php else: ?>
        <p class="alert alert-danger">
            <?= _("No matching rates found, try again with different search criteria") ?>
        </p>
        <?php endif ?>
    </div>
</div>
<?php if (count($list)): ?>
<form method="post">
    <?= $HD_Form->csrf_inputs() ?>
    <input type="hidden" name="posted" value="1"/>
    <div class="row mb-3">
        <label class="col-4 col-form-label">
            <?= _("Destination Ratecard") ?>
        </label>
        <div class="col-4">
            <select name="ratecard_destination" class="form-select">
                <?php foreach ($list_tariffname as $rc): ?><option value="<?= $rc["id"] ?>"><?= htmlspecialchars($rc["tariffname"]) ?></option><?php endforeach ?>
            </select>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-5">
            <select class="form-select csv-import" id="unselected_cols" multiple="multiple" size="10" aria-labelledby="unselected_label">
                <optgroup id="unselected_label" label="<?= _("Unselected fields…") ?>">
                    <option value="buyrate">buyrate - <?= _("Buying rate") ?></option>
                    <option value="buyrateinitblock">buyrateinitblock - <?= _("Buying min duration") ?></option>
                    <option value="buyrateincrement">buyrateincrement - <?= _("Buying billing block") ?></option>

                    <option value="initblock">initblock - <?= _("Selling min duration") ?></option>
                    <option value="billingblock">billingblock - <?= _("Selling billing block") ?></option>

                    <option value="connectcharge">connectcharge - <?= _("Connect charge") ?></option>
                    <option value="disconnectcharge">disconnectcharge - <?= _("Disconnect charge") ?></option>
                    <option value="disconnectcharge_after">disconnectcharge_after - <?= _("Disconnect charge threshold (seconds)") ?></option>

                    <option value="minimal_cost">minimal_cost - <?= _("Minimum call cost") ?></option>

                    <?php if (ADVANCED_MODE): ?>
                        <option value="stepchargea">stepchargea - <?= _("Step charge A") ?></option>
                        <option value="chargea">chargea - <?= _("Charge A") ?></option>
                        <option value="timechargea">timechargea - <?= _("Time charge A") ?></option>
                        <option value="billingblocka">billingblocka - <?= _("Billing block A") ?></option>

                        <option value="stepchargeb">stepchargeb - <?= _("Step charge B") ?></option>
                        <option value="chargeb">chargeb - <?= _("Charge B") ?></option>
                        <option value="timechargeb">timechargeb - <?= _("Time charge B") ?></option>
                        <option value="billingblockb">billingblockb - <?= _("Billing block B") ?></option>

                        <option value="stepchargec">stepchargec - <?= _("Step charge C") ?></option>
                        <option value="chargec">chargec - <?= _("Charge C") ?></option>
                        <option value="timechargec">timechargec - <?= _("Time charge C") ?></option>
                        <option value="billingblockc">billingblockc - <?= _("Billing block C") ?></option>
                    <?php endif ?>

                    <option value="startdate">startdate - <?= _("Start date (Y-m-d H:m:s)") ?></option>
                    <option value="stopdate">stopdate - <?= _("Stop date (Y-m-d H:m:s)") ?></option>
                    <option value="additional_grace">additional_grace - <?= _("Additional grace time (seconds)") ?></option>
                    <option value="starttime">starttime - <?= _("Start time (0 = 12:00:00 Monday)") ?></option>
                    <option value="endtime">endtime - <?= _("End time (10079 = 23:59:59 on Sunday)") ?></option>
                    <option value="tag">tag - <?= _("Tag") ?></option>
                    <option value="rounding_calltime">rounding_calltime - <?= _("Rounding calltime") ?></option>
                    <option value="rounding_threshold">rounding_threshold - <?= _("Rounding threshold") ?></option>
                    <option value="additional_block_charge">additional_block_charge - <?= _("Additional block charge") ?></option>
                    <option value="additional_block_charge_time">additional_block_charge_time - <?= _("Additional block charge time") ?></option>
                    <?php if (ADVANCED_MODE): ?>
                        <option value="announce_time_correction">announce_time_correction - <?= _("Announce time correction") ?></option>
                    <?php endif ?>
                </optgroup>
            </select>
        </div>
        <div class="col-1 d-flex">
            <div class="align-self-center">
                <button type="button" class="btn btn-sm" id="add_col">
                    <img alt="<?= _("add column to selected list") ?>" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABYAAAAWCAYAAADEtGw7AAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsNAAALDQHtB8AsAAAAB3RJTUUH1QMJAgsoYUq3OQAABKNJREFUOMuVldmPFFUUh797a5neqnt2nBDaARRicMaxJ5OIMS4YVARf1PigxAcNLm88SPwHTHg2xPgwiQmJQSVgjD5ABCMGWRRoMpnRgWEfcJjuWXut6q6qe32YBQaQyEnOS9063/ndyq/OEdwnMh8PWKDbQHWgdQw0oF20GrdUMd/N3np/f/89a8V9oOuBt0D3IEgjrDgAqlZFh9eBAaGCfRn91TEgvLOBuAdwNYjtGNbbiXis8aHWpFzWEmFta4moFXJpOsHVvGJyYkJNF1xXBe4xS5c/69bfnACChQbmHdCHQezCTm7qWRXl1T6HrhWa9kYDQzYBcx9jphRwebxdHsh68aODMy/VXbFiSL356eN63wEgWKJ4XumuRDL58it9KbklY9PsGAgh8OqaEyMBAE+tMYnac2UVL+T4cJVvj0zr/NTsBTucfr+L/ccBdZtivQPb2bQxk2LzkxaWKSi5CoBiFTZ0RwA4MujSlZaYhgA0fY9G8YNmsfuwWlMthR+gGQDKxpzaM+uF0bDzidXJyKaMhZRQ9xWer6j5iraUQbrNxolKljeb/DJYI2Yr/EBTDzTNCUmlbjI64XdOhCsGljF8xch8dNoCtjuO8+yLPXHRlICar/F8TW0+g1DT7MzdItogWN5icuJ8Hckc3PMVtlHnck7ZFS/wjaBw1ATagJ5kMiUbIwGFsiRUS80yVQyZLIZszKRosATtKYMtfXH2/FbEr9cYyjVSdGOUSEjF7LqiSKcl0IGQ6VZH4NYV/8yAqyJ3Zb5s8ftwbbFZe8rg3RdSjJaauTQZYaLSAGYchNGBYadNIIZsiMcsn9mKYmOvQ8+qBv5PNDuSHa/ZfLIHxmZACAGyISoUzqIrip5FrqBJt5s8SKRbIREBIeZNDgiUlKBdVK2aq0S5Ukjy/Z8PxOXoecgV5sACDaHnCR3UTLQaB64XqmplKKLs/QN+OquX/PFKQ6Dgnadh6zO3oIeHYOePc+eWhLJbAx3mpaqMS0sV88AAtWllGRpTavxAzWdI2dMUXXjusaXQkxcUX/wcYoqQiBlimyGiPqVMvJFGfe2m2c3eela9ty+sFbZaYbSpybGXTCaloXe1zYcvRRafnbpQZ/evHikLUtbC/KijvJlKNMwdShmT00Y2m2VLRt4Yo3ul0EFvus0k2RAQtwPiVkDCDljTIRed8veox3dHC5jCX3zHFD7Xb05Rq8wc7BI/7AbyCxYIDVX9vFQSj1wb4/nuVQlpW7fccWPCZXjUBuDAqQIxW7Ew6up+wMhoWZWLhTMtavhrJDnANQCy2SybM9bUlE6fLrjmumI16HRiFs2OiW1JhICLYzUujtUQAmxTYBkwWfAZvFxgfKJ4ui04uzMts6eASSA0FlT19vbqds5NT+nOswXXiE0U/M5SNbAjZiDiURNTSqQArUOmZioMXa2qc6Ol8uxs8WBL+NeX89A84Pf399+9QbZt2yaB+CBvbAhk4nWFtU5K0eFEjShAyQ09Faq8iTcSCXOH1srDx4EcMPufG2TBCEC5i/0H837nyaJIpzHstCjjCJRs1EFNqsp4o752M2VMTgMlwAXU7XvvXznGRXgBgp7QAAAAAElFTkSuQmCC"/>
                </button>
                <button type="button" class="btn btn-sm" id="remove_col">
                    <img alt="<?= _("remove column from selected list") ?>" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABYAAAAWCAYAAADEtGw7AAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsNAAALDQHtB8AsAAAAB3RJTUUH1QMJAgwPiwGUlQAABLJJREFUOMuVlUtsVFUYx3/nnHtv53Fnpg8KYsNYQFHB1trKwsQganwAuvARF8adwejODXHnRhPXxhgXTUxMNBqD0R1EXZig1SgUGyAgT0t5TGfaaWfuzNw7c+85x8WUIoomfMm3O9/v/M/3OJ/gf2z8jRkX7CCY9VibAQvYEGtKrqmXR/myMzk5edNY8T/Qh4CXwI4hKCLcLACm3cLqOWBGmGT/uP34J0D/8wJxE+BmEG+i3Jf9bKb3tjV5uW4gxd1rAtKu5lzV58+yYaFSMdVaGJok/Mm1jXdH7ec/A8m1C5x/QO8A8QFeftfYpjS7t+cY2WBZ26tQsg/oJmMpSDhfWisPTEfZQ8eWnuyEYsNx8+Jb99n9B4DkBsUrSj/w8/mnnt5ekM+Me/TnFEIIEm0pLXUAuK3Pw1HdsGakmTrZ4osfqra8uHzG09VXR/hqCjB/U2z34eV2PTFeYM8DLq4jCEKDtZagpbmnmAHg8kK7q0YIwLL9rjRx0i8++d5saQX6NSwzQEN11R55SKie9+7fnE/tGneREjqxIewYgpZhbLOPn1b0uJKgpak2EjqxIYotncTS70uaHYeLlXi4ojfMrOPkBTX++mEXeDOXy+14fCwr+nxox5awYwGY2OKTTSsAWpHhfCmi3tJEsaW94lFs8FSH8/PGa0ZJrJLaIQcYBMby+YLsTSXUmxIAP614cEsOfwWaaMuh43WClgYsFtDacqmqOVbqpR5mCPClYXlbXRSLDrAeIYtrcoKw033+moLLjpE82ZRarcCFUkSfr+jzFcZCrC1aW+qJS+VcinoIOA4ItR7lFR0gg+zJZtyY5aahN6vYOVq4AQpw11D6poO0E7jzdnh7PwghQPakhSG32hX1yGW+ZlGOoi/ncCu2dWi1SVamzkgJNsS0W/PNNBdqeX6/nOXn0+aWwPt/7YIFFnQUCZu0HawpAXO1ltmoRZpaG975GvY9C49uvR786Y/w2dTNwXECroRG2Aary9I0S9I19TIwQ7tqXGXxlAUsH36r+eXMdeWvPAyP3AuNqOvt2GC0xmiNqzSeoxGdReMQne61s1fV7omULnF/02KfK2Sd9IBv8N0YT8acmOuwLi8YGugW8sFNEIYRYRhSSHXIeTE5LybrxnSiJsFSpZGOZz8aUsdn1PT0NM+My0tXGN0obDJRHHTI9yRkvQRPJPxxuc3t/YrBgoOjYLbUohaE+F73TNZNcETM3NVF2s2lgyPim0+A8rXya2Va7weBuHP2CjtHN/nSc693xoHfaqu/1aVKSCGzkiILnTjh9MWGadRrRwbMyU+RzAOhApienmbPuLu4aIuHa6Gzrd5KhnMZl/6cg+dKhICzV9qcvdJGCPAcgatgoRZz7HyNUqV+eDA5+l5RTv8GLAB6dQomJibsWk5VF+3w0VqoMpVaPBy0Ei/lJCKbdnCkRAqwVrO41OT4ny1z6mLQWF6uHxzQJz5agZaBeHJy8t8bZO/evRLIHuOFxxLpP29wt0kp1ufSKg0QhDoy2pQdotMpPf/d3fL7KWAeWP7PDbJiBmiM8NXBcjz8S10UiyivKBrkBEb22qQtTbPUa2evFtRCFQiAEDB/33t/AfcTTUjqjZgNAAAAAElFTkSuQmCC"/>
                </button>
            </div>
        </div>
        <div class="col-5">
            <select class="form-select csv-import" name="selected_cols[]" id="selected_cols" multiple="multiple" size="10" aria-labelledby="selected_label">
                <optgroup id="selected_label" label="<?= _("Selected fields…") ?>">
                    <option value="" disabled="disabled">&nbsp;</option>
                </optgroup>
            </select>
        </div>
        <div class="row justify-content-end pt-3 mt-3 bg-transparent">
            <div class="col text-end">
                <button type="submit" class="btn btn-primary"><?= _("Merge") ?></button>
            </div>
        </div>
    </div>
</form>
<?php endif ?>
<?php endif ?>

<?php
require_once __DIR__ . "/templates/footer.php";
