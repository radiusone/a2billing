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

getpost_ifset(array('id', 'cid', 'outbound_cid_group', 'activated'));

$HD_Form = new FormHandler("cc_outbound_cid_list", "cid");

$HD_Form->no_debug();
$HD_Form -> FG_TABLE_DEFAULT_ORDER = "cid";
$HD_Form -> FG_TABLE_DEFAULT_SENS = "DESC";

$HD_Form ->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form ->FG_LIST_ADDING_BUTTON_LINK1 = "A2B_entity_outbound_cid.php?form_action=ask-add&atmenu=cidgroup&section=".$_SESSION["menu_section"];
$HD_Form ->FG_LIST_ADDING_BUTTON_ALT1 = $HD_Form ->FG_LIST_ADDING_BUTTON_MSG1 = gettext("Add CallerID");
$HD_Form ->FG_LIST_ADDING_BUTTON_IMG1 = Images_Path ."/server_connect.png" ;

$actived_list = getActivationList();

$HD_Form -> AddViewElement(gettext("CID"), "cid");
$HD_Form -> AddViewElement(gettext("CIDGROUP"), "outbound_cid_group", true, 15, "", "lie", "cc_outbound_cid_group", "group_name", "id='%id'", "%1");
$HD_Form -> AddViewElement(gettext("STATUS"), "activated", true, 30, "", "list", $actived_list);

$HD_Form -> FieldViewElement ('cid, outbound_cid_group, activated');

$HD_Form -> FG_ENABLE_ADD_BUTTON = true;
$HD_Form -> FG_ENABLE_EDIT_BUTTON = true;
$HD_Form -> FG_ENABLE_DELETE_BUTTON = true;
$HD_Form -> FG_SPLITABLE_FIELDS[] = 'cid';

// TODO integrate in Framework
if ($form_action=="ask-add") {
    $begin_date = date("Y");
    $begin_date_plus = date("Y") + 10;
    $end_date = date("-m-d H:i:s");
    $comp_date = "value='".$begin_date.$end_date."'";
    $comp_date_plus = "value='".$begin_date_plus.$end_date."'";
}

$HD_Form -> AddEditElement (gettext("CID"),
    "cid",
    '$value',
    gettext("Define the CallerID's. If you ADD a new CID, NOT an EDIT, you can define a range of CallerID. <br>80412340210-80412340218 would add all CID's between the range, whereas CIDs separated by a comma e.g. 80412340210,80412340212,80412340214 would only add the individual CID listed."),
    "",
    "TEXTAREA",  //CID Regular Expression
    "cols=50 rows=4",
    null, gettext("Insert the CID"));

$HD_Form->AddEditSqlSelect("outbound_cid_group", gettext("CIDGROUP"), "cc_outbound_cid_group", "group_name,id");

$HD_Form->AddEditRadio(
    "activated",
    [[gettext("Yes"), "1"], [gettext("No"), "0"]],
    gettext("ACTIVATED"),
    "1",
    "",
    gettext("Choose if you want to activate this CallerID")
);

$HD_Form -> FieldEditElement ('cid, outbound_cid_group, activated');

$HD_Form -> FG_INTRO_TEXT_EDITION = '';

$HD_Form -> FG_INTRO_TEXT_ADITION = '';


$HD_Form -> FG_LOCATION_AFTER_ADD = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?atmenu=document&stitle=Document&wh=AC&id=";
$HD_Form -> FG_LOCATION_AFTER_EDIT = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?atmenu=document&stitle=Document&wh=AC&id=";
$HD_Form -> FG_LOCATION_AFTER_DELETE = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?atmenu=document&stitle=Document&wh=AC&id=";
