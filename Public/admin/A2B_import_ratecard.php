<?php

use A2billing\Admin;
use A2billing\Logger;
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

getpost_ifset(["search_sources", "task", "uploadedfile_name", "tariffplan", "trunk", "currencytype"]);
/**
 * @var string $search_sources
 * @var string $task
 * @var string $uploadedfile_name
 * @var string $tariffplan
 * @var string $trunk
 * @var string $currencytype
 */

$task ??= "";
$search_sources ??= "nochange";
$fieldtoimport = "";
$field_names = [
    "dialprefix",
    "rateinitial",
];
if ($search_sources !== "nochange") {
    $field_names = array_merge($field_names, explode("|", $search_sources));
}
$field_names = array_merge($field_names, ["idtariffplan", "id_trunk", "destination"]);

$nb_imported = 0;
$import_error = "";
$import_time = 0;
$DBHandle = DbConnect();
$the_file = "";
$assoc_csv = [];
$prefix_values = [];

if ($task) {
    $start_time = microtime(true);
    if (!empty($_FILES["the_file"])) {
        $errortext = validate_upload($_FILES["the_file"]["tmp_name"] ?? "", $_FILES["the_file"]["type"] ?? "");
        if ($errortext) {
            echo $errortext;
            exit;
        }
        $the_file = tempnam(sys_get_temp_dir(), "cc_card");
        if (!move_uploaded_file($_FILES["the_file"]["tmp_name"], $the_file)) {
            echo sprintf(_("File Save Failed, FILE=%s"), $the_file);
            exit;
        }
    } else {
        $the_file = $uploadedfile_name;
    }

    $insert_data = [];

    $file_data = file($the_file, FILE_IGNORE_NEW_LINES);
    foreach ($file_data as $line) {
        $line = trim($line);
        if (str_starts_with($line, "#")) {
            continue;
        }
        $values = str_getcsv($line, ",", "\"", "");

        // remove destination
        $dialprefix = $values[0];
        $destination = $values[1];
        unset($values[1]);
        $prefix_values[] = ["prefix" => $dialprefix, "destination" => $destination];

        $values = array_merge($values, [$tariffplan, $trunk, $dialprefix]);
        if (count($values) !== count($field_names)) {
            continue;
        }
        $assoc_csv = array_combine($field_names, $values);

        if ($currencytype === "cent") {
            $currency_cols = [
                "rateinitial",
                "buyrate",
                "connectcharge",
                "disconnectcharge",
                "stepchargea",
                "chargea",
                "stepchargeb",
                "chargeb",
                "stepchargec",
                "chargec",
                "additional_block_charge",
                "minimal_cost",
            ];
            foreach ($currency_cols as $col) {
                if (array_key_exists($col, $assoc_csv)) {
                    $assoc_csv[$col] = $assoc_csv[$col] / 100;
                }
            }
        }
        if (!array_key_exists("stopdate", $assoc_csv)) {
            $assoc_csv["stopdate"] = (new DateTime('+10 years'))->format("Y-m-d H:i:s");
        }

        $insert_data[] = $assoc_csv;
        if ($task === "preview") {
            break;
        }
    }

    if ($task === "upload") {
        $result = (new Table("cc_ratecard"))->addRows($insert_data);
        if (!$result) {
            $import_error = $DBHandle->ErrorMsg();
        } else {
            $nb_imported = count($insert_data);
            Logger::insertLog(Admin::id(), 2, "RATES IMPORTED", $nb_imported . " New RATES Imported Successfully", '', $_SERVER['REMOTE_ADDR'], $_SERVER['REQUEST_URI']);
            (new Table("cc_prefix"))->addRows($prefix_values, "", $id, true);
        }
    }
    $stop_time = microtime(true);
    $import_time = $stop_time - $start_time;
} else {
    $my_max_file_size = (int)MY_MAX_FILE_SIZE_IMPORT;

    // GET CALLPLAN LIST
    $where = [
        ["SUB", ["startingdate" => [[null], ["<", "CURRENT_TIMESTAMP"]]], "OR"],
        ["SUB", ["expirationdate" => [[null], [">", "CURRENT_TIMESTAMP"]]], "OR"],
    ];
    $list_tariffname = (new Table("cc_tariffplan", ["tariffname", "id"]))->getColumn($where);

    // GET TRUNK LIST
    $list_trunk = (new Table("cc_trunk", ["trunkcode", "id_trunk"]))->getColumn(["status" => 1]);

    echo create_help(
        _("This section is a utility to import ratecards from a CSV file.")
        . "<br/>"
        . _('Define the ratecard name, the trunk to use and the fields that you wish to include from your csv files. Finally, select the csv files and click on the "Import Ratecard" button.')
    );
}

require_once __DIR__ . "/templates/main.php";
?>

<?php if ($task === "preview" && empty($assoc_csv)): ?>
<div class="row mb-3">
    <div class="col">
        <p class="text-danger"><?= _("No valid rows were found for import. Ensure the number of values in the CSV matches the number of fields selected for import.") ?></p>
    </div>
</div>
<div class="row mb-3">
    <div class="col">
        <a class="btn btn-danger" href="A2B_import_ratecard.php"><?= _("Return") ?></a>
    </div>
</div>

<?php elseif ($task === "preview"): ?>
<div class="row mb-3">
    <div class="col">
        <p><?= create_help(_('This is the second step of the import ratecard! <br>') .
                _('The first line of your csv files has been read and the values are displayed below according to the fields') .
                _('you decided to import on the ratecard! You can check the values and if there are correct,') .
                _('please select the same file and click on "Continue to Import the Ratecard" button...')); ?></p>
        <p><?= _("The first line of your import is previewed below, please check to ensure that every column is correct.") ?></p>
        <p><?= _("Note that some values have been added or changed as part of the import process") ?></p>
    </div>
</div>
<table class="table table-striped">
    <thead>
    <tr>
        <th scope="col"><?= _("FIELD") ?></th>
        <th scope="col"><?= _("VALUE") ?></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($assoc_csv as $key => $data): ?>
        <tr>
            <th scope="row"><?= _($key) ?></th>
            <td><?= htmlspecialchars($data) ?></td>
        </tr>
    <?php endforeach ?>
    </tbody>
</table>
<div class="row mb-3">
    <div class="col">
        <form method="post">
            <input type="hidden" name="search_sources" value="<?= $search_sources ?>"/>
            <input type="hidden" name="uploadedfile_name" value="<?= $the_file ?>"/>
            <input type="hidden" name="tariffplan" value="<?= $tariffplan ?>"/>
            <input type="hidden" name="trunk" value="<?= $trunk ?>"/>
            <input type="hidden" name="currencytype" value="<?= $currencytype ?>"/>
            <input type="hidden" name="task" value="upload">
            <p><?= _("Confirm the data is correct, or press cancel to return to the previous page.") ?></p>
            <button class="btn btn-primary" type="submit"><?= _("Import") ?></button>
            <a class="btn btn-danger" href="A2B_import_ratecard.php"><?= _("Cancel") ?></a>
        </form>
    </div>
</div>

<?php elseif ($task === "upload" && $import_error === ""): ?>
<div class="row mb-3">
    <div class="col">
        <p><?= create_help(_('Ratecard comfirmation page. <br>') . _('Import results, how many new rates have been imported, and the line numbers of the CSV files that generated errors.')) ?></p>
        <p><?= sprintf(_("Success, %d new rates have been imported in %0.4f seconds."), $nb_imported, $import_time) ?></p>
    </div>
</div>
<div class="row mb-3">
    <div class="col">
        <a class="btn btn-success" href="A2B_entity_def_ratecard.php"><?= _("Continue") ?></a>
    </div>
</div>

<?php elseif ($task === "upload"): ?>
<div class="row mb-3">
    <div class="col">
        <p><?= _("There were errors importing the rate cards.") ?></p>
        <p><?= $import_error ?></p>
    </div>
</div>
<div class="row mb-3">
    <div class="col">
        <a class="btn btn-danger" href="A2B_import_ratecard.php"><?= _("Continue") ?></a>
    </div>
</div>

<?php else: ?>
<form class="container align-center" id="prefs" name="prefs" enctype="multipart/form-data" method="post" action="">
    <div class="row mb-3">
        <div class="col">
            <h5><?= _("New rates can be imported from a CSV file") ?></h5>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-6">
            <label class="form-label" for="tariffplan"><?= _("Choose the rate card to add rates to") ?></label>
            <select id="tariffplan" name="tariffplan" class="form-select" required="required">
                <option value=""><?= _("Choose a rate card") ?></option>
                <?php foreach ($list_tariffname as $id => $name): ?>
                <option value="<?= $id ?>"><?= htmlspecialchars($name) ?></option>
                <?php endforeach ?>
            </select>
        </div>
        <div class="col-6">
            <label class="form-label" for="trunk"><?= _("Choose the trunk to use for imported rates") ?></label>
            <select id="trunk" name="trunk" class="form-select">
                <option value="-1"><?= _("Use rate card default") ?></option>
                <?php foreach ($list_trunk as $id => $name): ?>
                <option value="<?= $id ?>"><?= htmlspecialchars($name) ?></option>
                <?php endforeach ?>
            </select>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col">
            <label class="form-label" for="bydefault"><?= _("These fields are mandatory") ?></label>
            <select class="form-select csv-import" id="bydefault" multiple="multiple" size="5" disabled="disabled">
                <option>dialprefix - <?= _("Dial prefix") ?></option>
                <option>destination - <?= _("Destination") ?></option>
                <option>rateinitial - <?= _("Selling rate") ?></option>
            </select>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col">
            <h6><?= _("Choose the additional fields to import from the CSV file") ?></h6>
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
        <div class="col-1 d-flex">
            <div class="align-self-center">
                <button type="button" class="btn btn-sm" id="move_col_up">
                    <img alt="<?= _("move selected item up") ?>" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABYAAAAWCAYAAADEtGw7AAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsNAAALDQHtB8AsAAAAB3RJTUUH1QMJAgsz6y9+1QAABKxJREFUOMt9ldtvVEUcxz8z59K9ne2NtlbCUi4KEahlGx5I1BgVFeFJjQ/GJw1G33jQ8A+Y4KvhwYcmJiQkGgKJ0SAkYEJCQA2wpAEtKVDutLvbbvd29pzdc86MD9sWyu2XTGYy+c1nvjPznRnBcyL79bgFug/UIFonQAPaQ6sZS1ULwxxujY2NPXWseA50O/AJ6BEEGYSVBEA1G+joLjAuVHgkq386C0SPTyCeAlwHYi+G9Wkqmeh6YUVaDvTG2LCiRtyKuFFKcaugmC0WVanieSr0zlq6/t2w/vkvIFycwHwMuhrEAez0zpG1cT7Y5rBllaa/y8CQ3UB7M+ZrIVMz/fJ4zk+euTz/bssTq66oj/dt1keOA+EyxQtKD6TS6ffe39Ypd2dtehwDIQRBqJiablL3IgZ6LFb22gghcP2IcxMNfjld0oW58jU7Kn2xhaPnAPWIYv0ttrNzR7aTXVstLFNQ8xRKafLzASPrk3SnTKamfa4/8BnotgHNtpfiBGGPOHhKvdyoRV+iGQfqRlvtxe3C6Nj/6rp0bGfWQkpoBYpGSzFTCtj+ikNnsq2h2zGJIriVbwLQCjU9KYnbMrlTDIaK0arxASZuGtmvLljAXsdx3nh7JCm6U9AMNI2WouErXtvskE4sOwp6HBMhYGq6SaTADxS20WIqr2zXDwMjrJwxgT5gJJ3ulF2xkKorF+wi2DHauaT08Vg7GEMpOHa+Su5+mqqXoEZKKsqbqiKTkcAgQmZWOAKvpSjXQwDe29b1TOhirF8ZI7uxn6Ibo+h2gJkEYQxi2BkJJJAdyYQVUHYVIHhnaxdO3AAgiDTjU+4y4B8XvaX26xvhm10Ll0IIkB1xAY5cTKj6FvmKpFCT+GHbhffnYd8hn7PXjGXgA3/G+f538INHbpp46F2BkiZoD9Vs5N04bhTjZhlmfwtZtQJuFeG/+zYDPfKJbTh2CVwf4jacnWyDQUPk+0KHTROtZoC7lYZaE4k4AJfvt4tGgnj2g3J6ol2bBlgS6l4TdFSQyp2RlqoWgHGaJWUZGvuR0mFEoDXqKVDLUCSsiIQVETMjbDNCtOaUiT/ZpW9Pm8McbuXU50eiZuUzK4p3dzv2MoVKQ0/MBmJLfas7a3gtvZTXfj9aKH/ejUf5k53GbMnI5XLszsp7DxheI3Q4mukzSXeEJO2QpBWSskPW9ks2r+5YAv9ztUKHESzlmCLg7vQcTXf+xBbx60GgsGjUyFCNH2o1sf72A94cXpuStvXQw/eKHhN3bF7stTg/6baXbrSltoKQyTt1Va9WLvaqiUNI8oBnAORyOXZlrbk5nblQ8cxN1UY45CQsehwT25IIAdcfNLl0o0G+HGIZAsuA2UrA5akKM8Xqhb7w0v6MzJ0HZoFoyaCjo6O6n6ulOT10qeIZiWIlGKo1QjtmhiIZNzGlRArQOmJu3uXKrYa6eqdWL5erJ3qjf39cgBaAYGxs7Ekn7dmzRwLJy3z0VihTHyqsTVKKQSduxAFqXuSrSBVM/MlYlD+5QZ46B+SB8jN/kEUjAPUtHD1RCIb+ropMBsPOiDqOQMkuHTalcme69O3pTmO2BNQAD1CP/nv/A0BcMay0FfABAAAAAElFTkSuQmCC"/>
                </button>
                <button type="button" class="btn btn-sm" id="move_col_down">
                    <img alt="<?= _("move selected item down") ?>" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABYAAAAWCAYAAADEtGw7AAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsNAAALDQHtB8AsAAAAB3RJTUUH1QMJAgwDgrfYvgAABNpJREFUOMt9lc9rXFUUxz/3vnff/P6RTNOalo6xVQvWxjqhSDciij+qXfgLFy5EkIrudKH+AQpu3KiIi4BQEBSpKIi0qKAo1mprSmxtNdra9FeSSTLJvJk37828d+91kWicqr27wz3ne773nO+5R3CVU3t2UoEdAjOMtVmwgA2xZlYZvz7KB73x8fH/jBVXAd0NPAZ2J4IqQuUAMN0OVl8AJoVJDtTsO98C+soE4j8At4J4Dkc9ns9ly9esK8oNlTTb1rXIKM2ZRp5zdcPC/LxpNMPQJOG3yrZfGbXvfQckfyVwrwC9FsSbeMU9O7dkuH9XgR2bLevLDo4cAFaKsdRKODu7Xh6ciHLfnFi6pxeKzSfNoy/dbA8cBJI+xqtM38wXi/fet6sk99Y8BgsOQgjixHB2pks71GwYVGyqeAghCCLN4dMd3v+qYeuLy795uvHUDj48DJh/MLYv4BX23F0r8cCtCuUKWqEBIE4MO6/PMZB3OTsT4Xc0QgjAsuuGDHEyKPZ/YW7stPTTWCaBtrPC9sfdwkm9esvWYnpPTSEl9GJDFBu6saGcd9lY8QAYKLicr3eJuoYotvQSy2BeEvRczs/HI/N68+QGTv/h1p45prD2sXwuXaxt9dDG0AxsX0NzaafPbgaaRK/5aGPZNqz56Uyq0FjMP1yPR464wBCws1gsyXI6wQ/kv6Q3kO/rMX5HEycGrS0XG5oTs2X8MEuLvDQsb/dFteoCwwhZXVcQhD1D2DN0epIoFqSUJecZOmXdz7idEMWGRiCZvJTjop9euXBdEM4wjleVQBaZymVVzHJgWPAtpUKGe8ZKrB/MshxAnPSXZmrO4ftzWXbfVOb5vVk2DqwOhRAgUxkBhb/f7UeKuaYklVI8eVeBW7d4PH57npFNJabm033AZ5oVnrq7yB03e9x2g8MbT4AQa9oVGOmCDTHdzlyQIdBp6pHguynD7htXcj54W4bTl/pr/uJeqI2s2Qd+WAEGCzqKhE26EmtmsfpCs2NodtNc9lO8/BF8eWolSDkwWr1i7FdBwx689il8fAyUhF6vC1bXpQlmpTJ+HZik2zDKsXiOBSxvfaY58pu52ufH/q8Nn/+kUY7GczWit2hcoqmynZ5x7h9L61luCSz2oVLOzVTyhryK8WTMzxd6bCgKNlX6ddyNLR8cjvjmVERexeRUTC8KaC3NtzPx9NubnJOTzsTEBHtr8uJlRq8TNhmrDrkUUwk5L8ETCb9e6rJx0GGotKblT37wOfprQFYl5FSCK2IuzCzSDZYO7RAf7wfqf3lrx3Reb7XE9dOXuWN0S156ag3o4NEmAthYURydCjg13aGYWelVL06YOt82bb/5Y8WcfhfJHBA6ABMTEzxQU4uLtnqsGbrb/U4yUsgqBgsunpIIAb9f7nL8TIe55QTlCJQDC82YE2ebzM77x4aS469W5cRRYAHQfxdvbGzMrueXxqIdOd4Mnex8Mx5pdRIv7SYil3FxpUQKsFazuBRw8lzH/HK+1V5e9g9V9M9vr4LWgXh8fPzfG2Tfvn0SyJ3gkTsTmX/YoLZLKYYLGScD0Ap1ZLSpu0RTaT33+Tb5xWFgDlj+3w2yegzQ3sGHh+rxyBFfVKs4XlW0KQiMLNukK00wW7bTMyVnoQG0gBAw/9x7fwLZ/05q+fGkRQAAAABJRU5ErkJggg=="/>
                </button>
            </div>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col">
            <?= _("Type of currency values in CSV") ?>:
            <div class="form-check form-check-inline">
                <input type="radio" class="form-check-input" id="currencyunit" name="currencytype" checked="checked" value="unit"/>
                <label class="form-check-label" for="currencyunit"><?= _("Unit") ?></label>
            </div>
            <div class="form-check form-check-inline">
                <input type="radio" class="form-check-input" id="currencycent" name="currencytype" value="cent"/>
                <label class="form-check-label" for="currencycent"><?= _("Cents") ?></label>
            </div>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col">
            <p>
                <?= _("Use the example below  to format the CSV file. Standard CSV format is used, as output from e.g. Microsoft Excel.") ?>
                <?= _("Fields are separated by comma <code>,</code>.") ?>
                <?= _("If a field contains a comma, surround it with quotes <code>\"</code>.") ?>
                <?= _("If a field contains a quote, surround it with quotes and double the inside quote.") ?>
                <?= _("A period <code>.</code> is used for decimal numbers.") ?>
                <?= _("Lines starting with a hash <code>#</code> are ignored.")?>
            </p>
            <p>
                <a href="importsamples.php?sample=RateCard_Complex" target="demoframe"><?php echo _("Complex Sample");?></a> -
                <a href="importsamples.php?sample=RateCard_Simple" target="demoframe"> <?php echo _("Simple Sample");?></a>
            </p>
            <iframe class="w-100" height="80" name="demoframe" src="importsamples.php?sample=RateCard_Simple"></iframe>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col">
            <label for="the_file" class="form-label">
                <?= sprintf(_("Select a file. The maximum file size is %d KB"), $my_max_file_size / 1024) ?>
            </label>
            <input type="hidden" name="MAX_FILE_SIZE" value="<?= $my_max_file_size ?>"/>
            <input type="hidden" name="task" value="preview"/>
            <input type="hidden" id="search_sources" name="search_sources" value="nochange"/>
            <input type="file" class="form-control" name="the_file" id="the_file" required="required"/>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <button type="submit" class="btn btn-primary" id="sendtoupload"><?= _("Import rate cards") ?></button>
        </div>
    </div>
</form>

<?php
endif;

require_once __DIR__ . "/templates/footer.php";
