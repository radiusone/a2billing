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

getpost_ifset(array('id', 'name', 'path', 'creationdate'));


$HD_Form = new FormHandler("cc_backup", "restore");

$HD_Form->no_debug();
$HD_Form -> FG_TABLE_DEFAULT_ORDER = " name";
$HD_Form -> FG_EDIT_QUERY_CONDITION = " id='%id' ";
$HD_Form -> FG_TABLE_DEFAULT_SENS = "DESC";

$HD_Form -> AddViewElement(gettext("ID"), "id");
$HD_Form -> AddViewElement(gettext("NAME"), "name");
$HD_Form -> AddViewElement(gettext("FILE"), "path");
$HD_Form -> AddViewElement(gettext("CREATIONDATE"), "creationdate", true, 19, "display_dateformat");


$HD_Form -> FieldViewElement ('id, name, path, creationdate');

$HD_Form -> FG_ENABLE_ADD_BUTTON = true;
$HD_Form -> FG_ENABLE_EDIT_BUTTON = true;
$HD_Form -> FG_ENABLE_DELETE_BUTTON = true;
// Restore and Download
$HD_Form -> FG_OTHER_BUTTON1 = true;
$HD_Form -> FG_OTHER_BUTTON2 = true;

$HD_Form->AddEditElement(
    gettext("Name "),
    "name",
    gettext("Name of the backup"),
    "size=50 maxlength=50",
    null,
    gettext("Insert the name of backup")
);

$HD_Form->AddEditElement(
    gettext("PATH"),
    "path",
    gettext("The backup file name"),
    "size=100 maxlength=200 readonly",
    null,
    gettext("The backup file")
);



$HD_Form -> FieldEditElement ('name, path, creationdate');

//$HD_Form -> FG_QUERY_EDITION_HIDDEN_FIELDS = "path";
//$HD_Form -> FG_QUERY_EDITION_HIDDEN_VALUE  = "$path";


if ($form_action=="ask-restore") {
    $HD_Form->FG_ADD_QUERY_HIDDEN_INPUTS["form_action"] = "restore";
}


$HD_Form -> FG_INTRO_TEXT_EDITION= '';


$HD_Form -> FG_OTHER_BUTTON1_LINK = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?form_action=restore&id=";
$HD_Form -> FG_OTHER_BUTTON1_IMG = Images_Path . "/icon-restore.gif";
$HD_Form -> FG_OTHER_BUTTON1_ALT = gettext("Restore");

$HD_Form -> FG_OTHER_BUTTON2_LINK = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?form_action=download&id=";
$HD_Form -> FG_OTHER_BUTTON2_IMG = Images_Path . "/dl.gif";
$HD_Form -> FG_OTHER_BUTTON2_ALT = gettext("Download");




$HD_Form -> FG_LOCATION_AFTER_ADD = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";
$HD_Form -> FG_LOCATION_AFTER_EDIT = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";
