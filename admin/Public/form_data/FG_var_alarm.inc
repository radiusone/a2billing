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

getpost_ifset(array('id', 'name', 'periode', 'type', 'maxvalue', 'minvalue', 'status', 'id_trunk', 'numberofrun', 'datelastrun', 'popup_select'));

$HD_Form = new FormHandler("cc_alarm", "Alarms");

$HD_Form->no_debug();
$HD_Form -> FG_TABLE_DEFAULT_ORDER = "id";
$HD_Form -> FG_TABLE_DEFAULT_SENS = "DESC";



$HD_Form ->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form ->FG_LIST_ADDING_BUTTON_LINK1 = "A2B_entity_alarm.php?form_action=ask-add";
$HD_Form ->FG_LIST_ADDING_BUTTON_ALT1 = $HD_Form ->FG_LIST_ADDING_BUTTON_MSG1 = gettext("Add Alarm");
$HD_Form ->FG_LIST_ADDING_BUTTON_IMG1 = Images_Path ."/bell_add.png" ;


// TODO Integrate a generic LIST to Framework
$period_list = [1 => ["Hourly", "1"], 2 => ["Daily", "2"], 3 => ["Weekly", "3"], 4 => ["Monthly", "4"]];


$actived_list = getActivationList() ;


$type_list = array();
$type_list["1"]  = array( gettext("Apply alarm on ALOC too low or too high"), "1");
$type_list["2"]  = array( gettext("Apply alarm on ASR too low or too high"), "2");
$type_list["3"]  = array( gettext("Apply alarm on CIC too many"), "3");


$daynumber_list = array();
for ($k=1;$k<=20;$k++) {
		$daynumber_list["$k"]  = array( "$k", "$k");
}
$HD_Form -> AddViewElement(gettext("NAME"), "name");
$HD_Form -> AddViewElement(gettext("PERIOD"), "periode", true, 0, "", "list", $period_list);
$HD_Form -> AddViewElement(gettext("TYPE"), "type", true, 0, "", "list", $type_list);
$HD_Form -> AddViewElement(gettext("MAX"), "maxvalue");
$HD_Form -> AddViewElement(gettext("MIN"), "minvalue");
$HD_Form -> AddViewElement(gettext("STATUS"), "status", true, 0, "", "list", $actived_list);
$HD_Form -> AddViewElement(gettext("TRUNK"), "id_trunk");
$HD_Form -> AddViewElement(gettext("CREATION DATE"), "datecreate", true, 22);




// added a parameter to append  FG_TABLE_ID  ( by default ) or disable 0.
$HD_Form -> FieldViewElement ('name, periode, type, `maxvalue`, minvalue, status, id_trunk, datecreate');

$HD_Form -> FG_ENABLE_EDIT_BUTTON = true;
$HD_Form -> FG_ENABLE_DELETE_BUTTON = true;
$HD_Form -> FG_ENABLE_ADD_BUTTON = true;
// View Details
$HD_Form -> FG_OTHER_BUTTON1 = true;
// |param| will be replace by id .
// you don't need that, if you don't supply any |param| string id will be append after FG_OTHER_BUTTON1_LINK.

$HD_Form -> FG_OTHER_BUTTON1_LINK = "javascript:;\" onClick=\"window.open('CC_entity_alarm_details.php?popup_select=2&displayfooter=0&displayheader=0&id=|param|','','scrollbars=yes,resizable=yes,width=600,height=350')\"";

$HD_Form -> FG_OTHER_BUTTON1_IMG = Images_Path . "/icon-viewdetails.gif";
$HD_Form -> FG_OTHER_BUTTON1_ALT = gettext("View details");


$HD_Form->AddEditElement(
    gettext("NAME"),
    "name",
    "",
    "",
    3,
    gettext("Insert the alarm name")
);

$HD_Form->AddEditSelect(gettext("PERIOD"), "periode", $period_list);

$HD_Form->AddEditSelect(
    gettext("TYPE"), "type",
    $type_list,
    "", gettext("ALOC (average length of call) ; ASR (answer seize ratio) ; CIC (Consecutive Incomplete Calls)")
);

$HD_Form->AddEditElement(
    gettext("MAX VALUE"),
    "`maxvalue`",
    gettext("Input the maximum value (between 0 and 1 for ASR ; > 0 for ALOC in sec ; > 0 for CIC)"),
    "",
    null,
    gettext("Insert the maximu value in")
);

$HD_Form->AddEditElement(
    gettext("MIN VALUE"),
    "minvalue",
    gettext("Input the minimum value (between 0 and 1 for ASR ; >= 0 for ALOC in sec ; not used by CIC) or leave it empty")
);

$HD_Form->AddEditPopup(
    gettext("TRUNK"),
    "id_trunk",
    "A2B_entity_trunk.php?popup_select=1&",
    gettext("Set if you want to apply the alarm on a specific trunk or leave empty for all trunk."),
    "",
    null,
    ""
);

$HD_Form->AddEditSelect(gettext("STATUS"), "status", $actived_list);

$HD_Form->AddEditElement(
    gettext("EMAIL TO SEND ALARM"),
    "emailreport",
    "",
    "size=40 maxlength=40",
    1,
    gettext("Insert the email to send the alarm")
);

$HD_Form -> FG_INTRO_TEXT_ADITION = gettext("Add an alarm below");

$HD_Form -> FG_LOCATION_AFTER_ADD = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";
$HD_Form -> FG_LOCATION_AFTER_EDIT = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";
$HD_Form -> FG_LOCATION_AFTER_DELETE = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";
