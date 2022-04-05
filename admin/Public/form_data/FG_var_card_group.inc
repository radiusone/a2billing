<?php

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

if (! has_rights (ACX_ADMINISTRATOR)) {
    Header ("HTTP/1.0 401 Unauthorized");
    Header ("Location: PP_error.php?c=accessdenied");
    die();
}


getpost_ifset(array('id', 'name ','id_agent','users_perms', 'description'));


$HD_Form = new FormHandler("cc_card_group", "Customer Group");

$HD_Form->no_debug();


$HD_Form -> FG_TABLE_DEFAULT_ORDER = "name";
$HD_Form -> FG_TABLE_DEFAULT_SENS = "DESC";

// Code Here for Deleting the Dependent Records
// Dependent Tables
$HD_Form -> FG_FK_DELETE_ALLOWED = true;
$HD_Form -> FG_FK_DELETE_CONFIRM = true;
$HD_Form -> FG_FK_WARNONLY = true;
$HD_Form -> FG_FK_TABLENAMES = array("cc_card");
$HD_Form -> FG_FK_EDITION_CLAUSE = array(" id_group ");

$HD_Form -> FG_FK_DELETE_MESSAGE = gettext("You have some cards using this Customers Group! Please comfirm that you really want to remove this Customers Group ? ");


$HD_Form ->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form ->FG_LIST_ADDING_BUTTON_LINK1 = "A2B_entity_card_group.php?form_action=ask-add&section=".$_SESSION["menu_section"];
$HD_Form ->FG_LIST_ADDING_BUTTON_ALT1 = $HD_Form ->FG_LIST_ADDING_BUTTON_MSG1 = gettext("Add Group");
$HD_Form ->FG_LIST_ADDING_BUTTON_IMG1 = Images_Path ."/group_add.png" ;



$HD_Form -> AddViewElement(gettext("NAME"), "name");
$HD_Form -> AddViewElement(gettext("DESCRIPTION"), "description", true, 150);
$HD_Form -> FieldViewElement ( 'name, description');

$HD_Form -> FG_ENABLE_ADD_BUTTON = true;
$HD_Form -> FG_ENABLE_EDIT_BUTTON = true;
$HD_Form -> FG_ENABLE_DELETE_BUTTON = true;
$HD_Form -> FG_DELETION_FORBIDDEN_ID = array(1);

$right_list = array();
$right_list["1"] = array( gettext("PASSWORD"), "2");
$right_list["2"] = array( gettext("SIP IAX"), "4");
$right_list["3"] = array( gettext("CALL HISTORY"), "8");
$right_list["4"] = array( gettext("PAYMENT HISTORY"), "16");
$right_list["5"] = array( gettext("VOUCHER"), "32");
$right_list["6"] = array( gettext("INVOICES"), "64");
$right_list["7"] = array( gettext("DID"), "128");
$right_list["8"] = array( gettext("SPEED DIAL"), "256");
$right_list["9"] = array( gettext("RATECARD"), "512");
$right_list["10"] = array( gettext("SIMULATOR"), "1024");
$right_list["12"] = array( gettext("CALL BACK"), "2048");
//$right_list["13"] = array( gettext("WEB PHONE"), "4096");
$right_list["14"] = array( gettext("CALLER_ID"), "8192");
$right_list["15"] = array( gettext("SUPPORT"), "16384");
$right_list["16"] = array( gettext("NOTIFICATION"), "32768");
$right_list["17"] = array( gettext("AUTODIALER"), "65536");
$right_list["18"] = array( gettext("EDIT PERSONAL INFO"), "131072");
$right_list["19"] = array( gettext("SEE RECORDING"), "262144");

$len_right_list = count($right_list);


$HD_Form->AddEditElement(
    gettext("NAME"),
    "name",
    "",
    "size=30 maxlength=40",
    0,
    gettext("Insert the provider name")
);

$HD_Form->AddEditTextarea(
    "description",
    gettext("DESCRIPTION"),
    "cols='50' rows='4'",
    gettext("Insert the description")
);

$HD_Form->AddEditSqlSelect(
    "id_agent",
    gettext("AGENT"),
    "cc_agent",
    "login,id,firstname,lastname",
    "",
    "",
    '<option value="-1" selected="selected">' . gettext("NONE") . '</option>',
    "",
    "%1 - %3 %4 (%2)"
);

$HD_Form->AddEditSelect(
    "users_perms",
    $right_list,
    gettext("PERMISSION USER INTERFACE"),
    gettext("To select several rights in the above ListBox, hold the CTRL key down while clicking the items"),
    "",
    "size='$len_right_list' multiple='multiple'"
);

$HD_Form -> FieldEditElement ('name, description, id_agent, users_perms');






$HD_Form -> FG_INTRO_TEXT_ADITION = gettext("Add $HD_Form->FG_INSTANCE_NAME.");





$HD_Form -> FG_LOCATION_AFTER_ADD = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";
$HD_Form -> FG_LOCATION_AFTER_EDIT = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";
$HD_Form -> FG_LOCATION_AFTER_DELETE = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";

