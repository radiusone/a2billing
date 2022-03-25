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

getpost_ifset(array('id', 'didgroupname'));

$HD_Form = new FormHandler("cc_outbound_cid_group", "Outbound CID Group");

$HD_Form->no_debug();
$HD_Form -> FG_TABLE_DEFAULT_ORDER = "group_name";
$HD_Form -> FG_TABLE_DEFAULT_SENS = "DESC";

$HD_Form ->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form ->FG_LIST_ADDING_BUTTON_LINK1 = "A2B_entity_outbound_cidgroup.php?atmenu=cidgroup&form_action=ask-add&section=".$_SESSION["menu_section"];
$HD_Form ->FG_LIST_ADDING_BUTTON_ALT1 = $HD_Form ->FG_LIST_ADDING_BUTTON_MSG1 = gettext("Add CallerID Group");
$HD_Form ->FG_LIST_ADDING_BUTTON_IMG1 = Images_Path ."/server_connect.png" ;

// Code Here for Deleting the Dependent Records
// Dependent Tables
$HD_Form -> FG_FK_DELETE_ALLOWED = true;
$HD_Form -> FG_FK_DELETE_CONFIRM = true;

$HD_Form->FG_FK_DELETE_OR_UPDATE = true;

$HD_Form -> FG_FK_WARNONLY = true;
$HD_Form -> FG_FK_TABLENAMES = array("cc_outbound_cid_list");
$HD_Form -> FG_FK_EDITION_CLAUSE = array(" outbound_cid_group ");

$HD_Form -> FG_FK_DELETE_MESSAGE = gettext("You have some CID using this CID Group! Please comfirm that you really want to remove this CID Group ? ");

$HD_Form -> AddViewElement(gettext("ID"), "id");
$HD_Form -> AddViewElement(gettext("DIDGROUP NAME"), "group_name");
$HD_Form -> AddViewElement(gettext("CREATION DATE"), "creationdate", true, 30, "display_dateformat");

// added a parameter to append  FG_TABLE_ID  ( by default ) or disable 0.
$HD_Form -> FieldViewElement ('id, group_name, creationdate');

$HD_Form -> FG_ENABLE_EDIT_BUTTON = true;
$HD_Form -> FG_ENABLE_DELETE_BUTTON = true;
$HD_Form -> FG_ENABLE_ADD_BUTTON = true;

$HD_Form -> AddEditElement (gettext("CIDGROUPNAME"),
    "group_name",
    '$value',
    "",
    "",
    "INPUT",
    "size=30 maxlength=70",
    9, gettext("Insert the CID Group Name "));

$HD_Form -> FieldEditElement ('group_name');


$HD_Form -> FG_INTRO_TEXT_ADITION = '';


$HD_Form -> FG_LOCATION_AFTER_ADD = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?atmenu=document&stitle=Document&wh=AC&id=";
$HD_Form -> FG_LOCATION_AFTER_EDIT = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?atmenu=document&stitle=Document&wh=AC&id=";
$HD_Form -> FG_LOCATION_AFTER_DELETE = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?atmenu=document&stitle=Document&wh=AC&id=";
