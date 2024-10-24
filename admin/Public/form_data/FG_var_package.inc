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

getpost_ifset(array('id', 'label', 'packagetype', 'billingtype','startday', 'freetimetocall'));

$HD_Form = new FormHandler("cc_package_offer", "OFFER PACKAGE");

$HD_Form->no_debug();
$HD_Form -> FG_TABLE_DEFAULT_ORDER = " label";
$HD_Form -> FG_TABLE_DEFAULT_SENS = "DESC";


$HD_Form ->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form ->FG_LIST_ADDING_BUTTON_LINK1 = "A2B_entity_package.php?form_action=ask-add";
$HD_Form ->FG_LIST_ADDING_BUTTON_ALT1 = $HD_Form ->FG_LIST_ADDING_BUTTON_MSG1 = gettext("Add Package");
$HD_Form ->FG_LIST_ADDING_BUTTON_IMG1 = Images_Path ."/package_add.png" ;




$packagetype_list = getPackagesTypeList();
$billingtype_list = [[_("Monthly"), 0], [_("Weekly"), 1]];

// If packagetype == NUMBER OF FREE CALL -> concat unit with "times"
// Else concat with "sec"
if ($A2B->config["database"]['dbtype'] == 'postgres') // Postgres doesn't like double quotes...
	$filter_unit = "CASE packagetype WHEN 1 THEN CONCAT( freetimetocall, ' times' ) WHEN 0 THEN 'ALL' ELSE CONCAT( freetimetocall, ' sec' ) END ";
else // And I don't know if mysql likes them single.
	$filter_unit = 'CASE packagetype WHEN 1 THEN CONCAT( freetimetocall, " times" ) WHEN 0 THEN "ALL" ELSE CONCAT( freetimetocall, " sec" ) END ';

$HD_Form -> AddViewElement(gettext("LABEL"), "label");
$HD_Form -> AddViewElement(gettext("CREATION DATE"), "creationdate", true, 19);
$HD_Form -> AddViewElement(gettext("PACKAGE TYPE"), "packagetype", true, 0, "", "list", $packagetype_list);
$HD_Form -> AddViewElement(gettext("BILLING TYPE"), "billingtype", true, 0, "", "list", $billingtype_list);
$HD_Form -> AddViewElement(gettext("STARTING DAY"), "startday");
$HD_Form -> AddViewElement(gettext("FREE"), "freetimetocall");

$HD_Form -> FieldViewElement ('label, creationdate, packagetype, billingtype, startday, '.$filter_unit);

$HD_Form -> FG_ENABLE_ADD_BUTTON = true;
$HD_Form -> FG_ENABLE_EDIT_BUTTON = true;
$HD_Form -> FG_ENABLE_DELETE_BUTTON = true;
$HD_Form -> FG_OTHER_BUTTON1 = true;
$HD_Form -> FG_OTHER_BUTTON1_ALT = gettext("Add rates");
$HD_Form -> FG_OTHER_BUTTON1_IMG = "";
$HD_Form -> FG_OTHER_BUTTON1_LINK = "A2B_package_manage_rates.php?id=|param|";
$HD_Form->AddEditElement(
    gettext("LABEL"),
    "label",
    "",
    "size=30 maxlength=30",
    0,
    gettext("Insert the package label")
);

$HD_Form->AddEditSelect(gettext("PACKAGE TYPE"), "packagetype", $packagetype_list);

$HD_Form->AddEditSelect(gettext("BILLING TYPE"), "billingtype", $billingtype_list);

if (isset($form_action) && $form_action=="ask-add") {
	$default_init = "value='1'";
}

$HD_Form->AddEditElement(
    gettext("STARTING DAY"),
    "startday",
    gettext("The value will be related to billing type ; if monthly accepted value are 1-31 ; if Weekly accepted value 1-7 (Monday to Sunday) "),
    "size=10 $default_init maxlength=5",
    4,
    gettext("Insert the start day")
);


$HD_Form->AddEditElement(
    gettext("FREE UNITS"),
    "freetimetocall",
    gettext("Insert the amount of free units, in seconds,for the FREE SECONDS mode. For NUMBER OF FREE CALLS insert the number of free calls"),
    "size=10 maxlength=10",
    4,
    gettext("Insert the amount of free time to call (seconds)")
);

$HD_Form -> FG_INTRO_TEXT_ADITION = '';

$HD_Form -> FG_LOCATION_AFTER_ADD = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";
$HD_Form -> FG_LOCATION_AFTER_EDIT = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";
$HD_Form -> FG_LOCATION_AFTER_DELETE = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";



