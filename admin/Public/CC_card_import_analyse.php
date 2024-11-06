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
// Common includes
require_once "../../common/lib/admin.defines.php";
/**
 * @var Smarty $smarty
 */
set_time_limit(0);

Admin::checkPageAccess(Admin::ACX_CUSTOMER);

getpost_ifset(['search_sources', 'task', 'uploadedfile_name']);
/**
 * @var string $search_sources
 * @var string $task
 * @var string $uploadedfile_name
 */

$fieldtoimport = "";
$field_names = [
    "username",
    "useralias",
    "uipass",
    "credit",
    "lastname",
    "firstname",
    "status",
];
if ($search_sources !== "nochange") {
    $field_names = array_merge($field_names, explode("|", $search_sources));
}

$nb_imported = 0;
$import_time = 0;
$DBHandle = DbConnect();
$the_file = "";
$assoc_csv = [];

if ($task === "upload" || $task === "preview") {
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
        if (count($values) !== count($field_names)) {
            continue;
        }
        $assoc_csv = array_combine($field_names, $values);
        if (empty($assoc_csv["useralias"])) {
            $assoc_csv["useralias"] = $assoc_csv["username"];
        }
        if (empty($assoc_csv["id_group"]) || $assoc_csv["id_group"] < 1) {
            // default user group
            $assoc_csv["id_group"] = 1;
        }

        $insert_data[] = $assoc_csv;
        if ($task === "preview") {
            break;
        }
    }

    if ($task === "upload") {
        (new Table("cc_card"))->addRows($DBHandle, $insert_data);
        $nb_imported = count($insert_data);
        Logger::insertLog($_SESSION["admin_id"], 2, "CARDs IMPORTED", $nb_imported." New CARDS Imported Successfully", '', $_SERVER['REMOTE_ADDR'], $_SERVER['REQUEST_URI']);
    }
    $stop_time = microtime(true);
    $import_time = $stop_time - $start_time;
}

// #### HEADER SECTION
$smarty->display('main.tpl');
?>

<?php if ($task === "preview" && empty($assoc_csv)): ?>
<div class="row mb-3">
    <div class="col">
        <p class="text-danger"><?= _("No valid rows were found for import. Ensure the number of values in the CSV matches the number of fields selected for import.") ?></p>
    </div>
</div>
<div class="row mb-3">
    <div class="col">
        <a class="btn btn-danger" href="CC_card_import.php"><?= _("Return") ?></a>
    </div>
</div>

<?php elseif ($task === "preview"): ?>
<div class="row mb-3">
    <div class="col">
        <p><?= _("The first line of your import is previewed below, please check to ensure that every column is correct.") ?></p>
    </div>
</div>
<table class="table table-stripe">
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
            <input type="hidden" name="task" value="upload">
            <p><?= _("Confirm the data is correct, or press cancel to return to the previous page.") ?></p>
            <button class="btn btn-primary" type="submit"><?= _("Import") ?></button>
            <a class="btn btn-danger" href="CC_card_import.php"><?= _("Cancel") ?></a>
        </form>
    </div>
</div>

<?php elseif ($task === "upload"): ?>
<div class="row mb-3">
    <div class="col">
        <p><?= sprintf(_("Success, %d new cards have been imported in %0.4f seconds."), $nb_imported, $import_time) ?></p>
    </div>
</div>
<div class="row mb-3">
    <div class="col">
        <a class="btn btn-success" href="A2B_entity_card.php"><?= _("Continue") ?></a>
    </div>
</div>

<?php endif;

// #### Footer SECTION
$smarty->display('footer.tpl');
