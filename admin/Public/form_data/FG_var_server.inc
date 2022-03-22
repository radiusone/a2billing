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


getpost_ifset(array('id', 'id_group', 'server_ip', 'manager_host', 'manager_username', 'manager_secret'));


$HD_Form = new FormHandler("cc_server_manager", "Server");

$HD_Form->no_debug();
$HD_Form ->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form ->FG_LIST_ADDING_BUTTON_LINK1 = "A2B_entity_server.php?form_action=ask-add&section=".$_SESSION["menu_section"];
$HD_Form ->FG_LIST_ADDING_BUTTON_ALT1 = $HD_Form ->FG_LIST_ADDING_BUTTON_MSG1 = gettext("Add SERVER");
$HD_Form ->FG_LIST_ADDING_BUTTON_IMG1 = Images_Path ."/server_add.png" ;


$HD_Form -> AddViewElement(gettext("ID GROUP"), "id_group", true, 15);
$HD_Form -> AddViewElement(gettext("GROUP"), "id_group", true, 15, "", "lie", "cc_server_group", "name", "id='%id'", "%1");
$HD_Form -> AddViewElement(gettext("SERVER HOSTNAME"), "server_ip");
$HD_Form -> AddViewElement(gettext("MANAGER HOST"), "manager_host");
$HD_Form -> AddViewElement(gettext("MANAGER USERNAME"), "manager_username");
$HD_Form -> AddViewElement(gettext("LASTTIME USED"), "lasttime_used", true, 19, "display_dateformat");


$HD_Form -> FieldViewElement ('id_group, id_group, server_ip, manager_host, manager_username, lasttime_used');

$HD_Form -> FG_LIST_VIEW_PAGE_SIZE = 10;

$HD_Form -> FG_ENABLE_ADD_BUTTON = true;
$HD_Form -> FG_ENABLE_EDIT_BUTTON = true;
$HD_Form -> FG_ENABLE_DELETE_BUTTON = true;

$HD_Form -> AddEditElement (gettext("SERVER GROUP"),
    "id_group",
    '$value',
    "",
    "", "SELECT", "",
    null,
    "",
    "sql",
    "cc_server_group", "name, id", "", "", "%1", "");

$HD_Form -> AddEditElement (gettext("SERVER HOSTNAME"),
    "server_ip",
    '$value',
    "",
    "",
    "INPUT",
    "size=30 maxlength=40",
    3, gettext("Insert the server hostname"), "", "", "", "", "", "", "");

$HD_Form -> AddEditElement (gettext("MANAGER HOST"),
    "manager_host",
    '$value',
    "",
    "",
    "INPUT",
    "size=30 maxlength=40",
    9, gettext("Insert the manager host"), "", "", "", "", "", "", "");

$HD_Form -> AddEditElement (gettext("MANAGER USERNAME"),
    "manager_username",
    '$value',
    "",
    "",
    "INPUT",
    "size=30 maxlength=40",
    9, gettext("Insert the manager username"), "", "", "", "", "", "", "");

$HD_Form -> AddEditElement (gettext("MANAGER SECRET"),
    "manager_secret",
    '$value',
    "",
    "",
    "INPUT",
    "size=30 maxlength=40",
    null, gettext("Insert the manager secret"), "", "", "", "", "", "", "");

$HD_Form -> FieldEditElement ('id_group, server_ip, manager_host, manager_username, manager_secret');


// Set the filter variables
$HD_Form -> FG_FILTER_ENABLE = true;
$HD_Form -> FG_FILTER_COLUMN = 'id_group';
$HD_Form -> FG_FILTER_LABEL = 'Server ID Group';


if (isset($filterprefix)  &&  (strlen($filterprefix)>0)){
	if (strlen($HD_Form -> FG_QUERY_WHERE_CLAUSE)>0) $HD_Form -> FG_QUERY_WHERE_CLAUSE.=" AND ";
	$HD_Form -> FG_QUERY_WHERE_CLAUSE.=" id_group like '$filterprefix%'";
}

$HD_Form -> FG_INTRO_TEXT_ADITION = '';

$HD_Form -> FG_LOCATION_AFTER_ADD = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?atmenu=document&stitle=Document&wh=AC&id=";
$HD_Form -> FG_LOCATION_AFTER_EDIT = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?atmenu=document&stitle=Document&wh=AC&id=";
$HD_Form -> FG_LOCATION_AFTER_DELETE = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?atmenu=document&stitle=Document&wh=AC&id=";




