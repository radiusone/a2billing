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

getpost_ifset(array('name','path','creationdate'));

$HD_Form = new FormHandler("cc_backup", "Backup");

$HD_Form->no_debug();

if ($form_action == 'add'){
    $backup_file = $path;

    if (substr($backup_file,-3)=='.gz'){
        // WE NEED TO GZIP
        $backup_file = substr($backup_file,0,-3);
        $do_gzip=1;
    }
    // Make the backup stuff here and redirect to success page
    // mysqldump -all --databases mya2billing -ua2billinguser -pa2billing > /tmp/test.sql
    // pg_dump -c -d -U a2billinguser -h localhost -f /tmp/test.sql mya2billing
    if (DB_TYPE != 'postgres') {
        $run_backup=MYSQLDUMP." -all --databases ".DBNAME." -u'".USER."' -p'".PASS."' > '{$backup_file}'";
    } else {
        $env_var="PGPASSWORD='".PASS."'";
        putenv($env_var);
        $run_backup=PG_DUMP." -c -d -U ".USER." -h ".HOST." -f '{$backup_file}' ".DBNAME;
    }
    if ($FG_DEBUG == 1 ) echo $run_backup."<br>";
    exec($run_backup,$output,$error);

    if ($do_gzip){
        // Compress file
        $run_gzip = GZIP_EXE." '$backup_file'";
        if ($FG_DEBUG == 1 ) echo $run_gzip."<br>";
        exec($run_gzip,$output,$error_zip);
    }

    if($error==0 && $error_zip==0 ) {
        $HD_Form -> FG_LOCATION_AFTER_ADD = 'A2B_entity_restore.php?id';
    }
    elseif($error!=0) {
        $HD_Form -> FG_TEXT_ADITION_CONFIRMATION = gettext("An error occur when the system tried to backup of the database.")."<br>".gettext("Please check yours system settings for the backup section ");
    }
    else {
        $HD_Form -> FG_TEXT_ADITION_CONFIRMATION = gettext("An error occur when the system tried to compress the backup realized.")."<br>".gettext("Please check yours system settings for the backup section ");
    }
}

$HD_Form -> FG_ENABLE_EDIT_BUTTON = false;
$HD_Form -> FG_ENABLE_DELETE_BUTTON = false;
$HD_Form -> FG_ENABLE_ADD_BUTTON = true;

$default_path = "value='".BACKUP_PATH."/db_". DBNAME . "-" . date('YmdHis') . ".sql.gz'";

$HD_Form->AddEditElement(
    gettext("Name"),
    "name",
    gettext("Name for the backup"),
    "size=50 maxlength=50",
    null,
    gettext("Insert the name of backup")
);

$HD_Form->AddEditElement(
    gettext("PATH"),
    "path",
    gettext("Path for the backup file or leave the default path set in a2billing.conf"),
    "size=100 maxlength=200 $default_path",
    null,
    gettext("Insert the patch for the backup file or leave the default path")
);


$HD_Form -> FieldEditElement ('name, path, creationdate');






$HD_Form -> FG_LOCATION_AFTER_EDIT = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";
$HD_Form -> FG_LOCATION_AFTER_DELETE = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";
