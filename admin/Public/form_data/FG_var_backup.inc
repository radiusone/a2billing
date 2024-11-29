<?php

use A2billing\Admin;
use A2billing\Forms\FormHandler;

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of A2Billing (http://www.a2billing.net/)
 *
 * A2Billing, Commercial Open Source Telecom Billing platform,
 * powered by Star2billing S.L. <http://www.star2billing.com/>
 *
 * @copyright   Copyright (C) 2004-2012 - Star2billing S.L.
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

Admin::checkPageAccess(Admin::ACX_ADMINISTRATOR);

getpost_ifset(["path"]);
/**
 * @var string $path
 */
$HD_Form = new FormHandler("cc_backup", "Backup");

$form_action ??= "list";
if ($form_action === "add" && !empty($path)) {
    $backup_file = $path;
    $do_gzip = false;
    $error_zip = 0;

    if (str_ends_with($backup_file, ".gz")) {
        // WE NEED TO GZIP
        $backup_file = substr($backup_file,0,-3);
        $do_gzip = true;
    }

    // Make the backup stuff here and redirect to success page
    // mysqldump -all --databases mya2billing -ua2billinguser -pa2billing > /tmp/test.sql
    // pg_dump -c -d -U a2billinguser -h localhost -f /tmp/test.sql mya2billing
    if (DB_TYPE != 'postgres') {
        $run_backup = sprintf(
            "%s -al --databases %s -u %s -p%s > %s",
            escapeshellcmd(MYSQLDUMP),
            escapeshellarg(DBNAME),
            escapeshellarg(USER),
            escapeshellarg(PASS),
            escapeshellarg($backup_file)
        );
    } else {
        putenv("PGPASSWORD='".PASS."'");
        $run_backup = sprintf(
            "%s -c -d -U %s -h %s -f %s %s",
            escapeshellcmd(PG_DUMP),
            escapeshellarg(USER),
            escapeshellarg(HOST),
            escapeshellarg($backup_file),
            escapeshellarg(DBNAME)
        );
    }
    exec($run_backup,$output,$error);

    if ($do_gzip) {
        // Compress file
        $run_gzip = sprintf("%s %s", escapeshellcmd(GZIP_EXE), escapeshellarg($backup_file));
        exec($run_gzip,$output,$error_zip);
    }

    $HD_Form->FG_TEXT_ADITION_CONFIRMATION = "";
    if($error === 0 && $error_zip === 0) {
        $HD_Form->FG_LOCATION_AFTER_ADD = "A2B_entity_restore.php?id=";
    } else {
        if ($error !== 0) {
            $HD_Form->FG_TEXT_ADITION_CONFIRMATION .= _("An error occur when the system tried to backup of the database.") . "<br>" . _("Please check yours system settings for the backup section ");
        }
        if ($error_zip !== 0) {
            $HD_Form->FG_TEXT_ADITION_CONFIRMATION .= _("An error occur when the system tried to compress the backup realized.") . "<br>" . _("Please check yours system settings for the backup section ");
        }
    }
}

$HD_Form->FG_ENABLE_EDIT_BUTTON = false;
$HD_Form->FG_ENABLE_DELETE_BUTTON = false;
$HD_Form->FG_ENABLE_ADD_BUTTON = true;

$default_path = "value='" . BACKUP_PATH . "/db_" . DBNAME . "-" . date('YmdHis') . ".sql.gz'";

$HD_Form->AddEditElement(
    _("Name"),
    "name",
    _("Name for the backup"),
    "size=50 maxlength=50",
    null,
    _("Insert the name of backup")
);

$HD_Form->AddEditElement(
    _("PATH"),
    "path",
    _("Path for the backup file or leave the default path set in a2billing.conf"),
    "size=100 maxlength=200 $default_path",
    null,
    _("Insert the patch for the backup file or leave the default path")
);

$HD_Form->FG_LOCATION_AFTER_EDIT = "?id=";
$HD_Form->FG_LOCATION_AFTER_DELETE = "?id=";
