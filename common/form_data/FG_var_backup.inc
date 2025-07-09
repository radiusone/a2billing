<?php

use A2billing\A2Billing;
use A2billing\Forms\FormHandler;
use A2billing\Table;

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

/**
 * @var A2Billing $A2B
 */

getpost_ifset(["path", "id"]);
/**
 * @var string $path
 * @var numeric-string|null $id
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
    if (DB_TYPE !== "postgres") {
        $run_backup = sprintf(
            "%s -al --databases %s -u %s -p%s > %s",
            escapeshellcmd($A2B->config['backup']['mysqldump']),
            escapeshellarg(DBNAME),
            escapeshellarg(USER),
            escapeshellarg(PASS),
            escapeshellarg($backup_file)
        );
    } else {
        putenv("PGPASSWORD='".PASS."'");
        $run_backup = sprintf(
            "%s -c -d -U %s -h %s -f %s %s",
            escapeshellcmd($A2B->config['backup']['pg_dump']),
            escapeshellarg(USER),
            escapeshellarg(HOST),
            escapeshellarg($backup_file),
            escapeshellarg(DBNAME)
        );
    }
    exec($run_backup,$output,$error);

    if ($do_gzip) {
        // Compress file
        $run_gzip = sprintf("%s %s", escapeshellcmd($A2B->config['backup']['gzip_exe']), escapeshellarg($backup_file));
        exec($run_gzip,$output,$error_zip);
    }

    $HD_Form->add_message_result = "";
    if ($error !== 0) {
        $HD_Form->add_message_result .= _("An error occur when the system tried to backup of the database.") . "<br>" . _("Please check yours system settings for the backup section ");
    }
    if ($error_zip !== 0) {
        $HD_Form->add_message_result .= _("An error occur when the system tried to compress the backup realized.") . "<br>" . _("Please check yours system settings for the backup section ");
    }
} elseif ($form_action === "delete") {
    $path = (new Table("cc_backup", ["path"]))->getValue(["id" => $id]);
    if (!is_readable($path) || !unlink($path)) {
        echo _("Error deleting file");
    }
} elseif ($form_action === "restore") {
    $path = (new Table("cc_backup", ["path"]))->getValue(["id" => $id]);

    if (str_ends_with($path, ".gz")) {
        // WE NEED TO GZIP
        $output = escapeshellcmd($A2B->config['backup']['gzip_exe']) . " -c " . escapeshellarg($path) . " | ";
    } else {
        $output = "cat " . escapeshellarg($path) . " | ";
    }

    $user = escapeshellarg(USER);
    $pass = escapeshellarg(PASS);
    $db = escapeshellarg(DBNAME);
    $host = escapeshellarg(HOST);
    if (DB_TYPE !== "postgres") {
        $run_restore = $output . escapeshellcmd($A2B->config['backup']['mysql']) . " -D $db -u $user -p$pass -h $host";
    } else {
        putenv("PGPASSWORD=$pass");
        $run_restore = $output . escapeshellcmd($A2B->config['backup']['psql']) . " -d $db -U $user -h $host";
    }

    exec($run_restore);
} elseif ($form_action === "download") {
    $path = (new Table("cc_backup", ["path"]))->getValue(["id" => $id]);
    if (is_readable($path)) {
        $filename = basename($path);
        $len = filesize($path);
        header("Content-Type: application/stream");
        header("Content-Length: " . filesize($path));
        header("Content-Disposition: attachment; filename=\"" . $filename . "\"");
        readfile($path);
    } else {
        http_response_code(file_exists($path) ? 403 : 404);
    }
    exit;
} elseif ($form_action === "upload") {
    $uploaddir = $A2B->config['backup']['backup_path'] . "/";
    $uploadfile = $uploaddir . basename($_FILES['databasebackup']['name']);

    if (move_uploaded_file($_FILES['databasebackup']['tmp_name'], $uploadfile)) {
        (new Table("cc_backup"))
            ->addRow(["name" => "Custom" . date("Ymd-His"), "path" => $uploadfile]);
    } else {
        $error_upload = _("Error uploading file");
    }
}

$HD_Form->list_query_order_columns = ["name"];

$HD_Form->help_text = create_help(
    _("This section will allow you to backup an existing database context. The Backup process will export the whole database, so you can restore it later.")
);

$HD_Form->AddListValue(_("ID"), "id");
$HD_Form->AddListValue(_("Name"), "name");
$HD_Form->AddListValue(_("File"), "path");
$HD_Form->AddListValue(_("Creation date"), "creationdate");

$HD_Form->AddListActionButton(
    "?form_action=restore&id=",
    _("Restore")
);
$HD_Form->AddListActionButton(
    "?form_action=download&id=",
    _("Download"),
    "",
    "cloud-arrow-down"
);

$HD_Form->FG_ENABLE_EDIT_BUTTON = true;
$HD_Form->FG_ENABLE_DELETE_BUTTON = true;
$HD_Form->FG_ENABLE_ADD_BUTTON = true;
$HD_Form->AddListTopButton();

$HD_Form->AddEditElement(
    _("Name"),
    "name",
    _("Name for the backup"),
    ["maxlength" => 50],
    null,
    _("Insert the name of backup")
);


$path_attr = ["maxlength" => 200];
if ($form_action === "ask-add") {
    $path_attr["value"] = $A2B->config['backup']['backup_path'] . "/db_" . DBNAME . "-" . date('YmdHis') . ".sql.gz";
}
$HD_Form->AddEditElement(
    _("PATH"),
    "path",
    $form_action === "ask-add"
        ? _("Path for the backup file or leave the default path set in a2billing.conf")
        : _("The backup file name"),
    $path_attr,
    null,
    $form_action === "ask-add"
        ? _("Insert the path for the backup file or leave the default path")
        : _("The backup file")
);
