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

getpost_ifset(array('id', 'name', 'activated' ,'id_support','type_user' ));



$HD_Form = new FormHandler("cc_support_component", "Support Component");


$HD_Form->no_debug();
$HD_Form -> FG_TABLE_DEFAULT_ORDER = " name";
$HD_Form -> FG_TABLE_DEFAULT_SENS = "DESC";


$HD_Form ->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form ->FG_LIST_ADDING_BUTTON_LINK1 = "CC_support_component.php?form_action=ask-add";
$HD_Form ->FG_LIST_ADDING_BUTTON_ALT1 = $HD_Form ->FG_LIST_ADDING_BUTTON_MSG1 = gettext("Add COMPONENT");
$HD_Form ->FG_LIST_ADDING_BUTTON_IMG1 = Images_Path ."/brick_add.png" ;


// Code Here for Deleting the Dependent Records
// Dependent Tables
$HD_Form -> FG_FK_DELETE_ALLOWED = true;
$HD_Form -> FG_FK_DELETE_CONFIRM = true;
$HD_Form -> FG_FK_WARNONLY = true;
$HD_Form -> FG_FK_TABLENAMES = array("cc_ticket");
$HD_Form -> FG_FK_EDITION_CLAUSE = array(" id_component ");

$HD_Form -> FG_FK_DELETE_MESSAGE = gettext("You have some tickets using this Support Component! Please comfirm that you really want to remove this Support Component? ");

$user_type = [[_("CUSTOMERS"), "0"], [_("AGENTS"), "1"], [_("CUSTOMERS AND AGENTS"),  "2"],];
$activated_list = getActivationList();

$HD_Form -> AddViewElement(gettext("ID"), "id");
$HD_Form -> AddViewElement(gettext("NAME"), "name");
$HD_Form -> AddViewElement(gettext("ACTIVATED"), "activated", true, 0, "", "list", $activated_list);
$HD_Form -> AddViewElement(gettext("USER TYPE"), "activated", true, 0, "", "list", $user_type);
$HD_Form -> AddViewElement(gettext("SUPPORT BOX"), "id_support", true, 15, "", "lie", "cc_support", "name", "id='%id'", "%1");

$HD_Form -> FieldViewElement ('id, name, activated,type_user, id_support ');

$HD_Form -> FG_ENABLE_EDIT_BUTTON = true;
$HD_Form -> FG_ENABLE_DELETE_BUTTON = true;
$HD_Form -> FG_ENABLE_ADD_BUTTON = true;


$HD_Form->AddEditElement(
    gettext("NAME"),
    "name",
    "",
    "size=30 maxlength=20",
    9,
    gettext("Insert the Support Box name ")
);

$HD_Form->AddEditRadio(
    gettext("ACTIVATED"),
    "activated",
    [[gettext("Yes"), "1"], [gettext("No"), "0"]],
    "1",
    "", "",
    gettext("Choose if you want to activate this component")
);

$HD_Form->AddEditSqlSelect(gettext("Support Box"), "id_support", "cc_support", "name,id");

$HD_Form->AddEditSelect(gettext("USER TYPE"), "type_user", $user_type, "", gettext("Define who can use this component."));

$HD_Form -> FG_INTRO_TEXT_EDITION= '';
$HD_Form -> FG_INTRO_TEXT_ADITION = '';

$HD_Form -> FG_LOCATION_AFTER_ADD = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";
$HD_Form -> FG_LOCATION_AFTER_EDIT = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";
$HD_Form -> FG_LOCATION_AFTER_DELETE = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";
