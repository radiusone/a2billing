<?php

use A2billing\Admin;
use A2billing\Forms\FormHandler;
use A2billing\Forms\Validator;

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

$HD_Form = new FormHandler("cc_alarm", _("Alarms"));

$HD_Form->FG_TABLE_DEFAULT_SENS = "DESC";

$HD_Form ->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form ->FG_LIST_ADDING_BUTTON_LINK1 = "A2B_entity_alarm.php?form_action=ask-add";
$HD_Form ->FG_LIST_ADDING_BUTTON_ALT1 = $HD_Form ->FG_LIST_ADDING_BUTTON_MSG1 = _("Add Alarm");
$HD_Form ->FG_LIST_ADDING_BUTTON_IMG1 = get_image_path("bell_add.png") ;

$HD_Form->add_help_text = create_help(
    _("Utility to apply a scheduled monitor on trunks.<br>") .
    _("For example if you want to monitor ASR (answer seize ratio) or ALOC (average length of call) everyday on each single trunk, it can be defined here, the different parameters below will define the rules to apply the alarm."),
    'EditAlarm'
);
$HD_Form->help_text = create_help(_("Alarms that monitors trunks at timed intervals."), 'ListAlarm');

$period_list = [1 => _("Hourly"), 2 => _("Daily"), 3 => _("Weekly"), 4 => _("Monthly")];
$actived_list = getActivationList();
$type_list = [
    1 => _("Apply alarm on ALOC too low or too high"),
    2 => _("Apply alarm on ASR too low or too high"),
    3 => _("Apply alarm on CIC too many"),
];
$daynumber_list = getInvoiceDay_List();

$HD_Form->AddListValue(_("NAME"), "name");
$HD_Form->AddListMapping(_("PERIOD"), "periode", $period_list);
$HD_Form->AddListMapping(_("TYPE"), "type",$type_list);
$HD_Form->AddListValue(_("MAX"), "maxvalue");
$HD_Form->AddListValue(_("MIN"), "minvalue");
$HD_Form->AddListMapping(_("STATUS"), "status", $actived_list);
$HD_Form->AddListValue(_("TRUNK"), "id_trunk");
$HD_Form->AddListValue(_("CREATION DATE"), "datecreate");
$HD_Form->FieldViewElement (["name", "periode", "type", "maxvalue", "minvalue", "status", "id_trunk", "datecreate"]);

$HD_Form->FG_ENABLE_EDIT_BUTTON = true;
$HD_Form->FG_ENABLE_DELETE_BUTTON = true;
$HD_Form->FG_ENABLE_ADD_BUTTON = true;
$HD_Form->FG_ENABLE_INFO_BUTTON = true;
$HD_Form->FG_INFO_BUTTON_LINK = "javascript:window.open('CC_entity_alarm_details.php?popup_select=2&displayfooter=0&displayheader=0&id=|param|','','scrollbars=yes,resizable=yes,width=600,height=350')";

$HD_Form->AddEditElement(
    _("NAME"),
    "name",
    "",
    "",
    [Validator::class, "min4Chars"],
    _("Insert the alarm name")
);

$HD_Form->AddEditSelect(_("PERIOD"), "periode", $period_list);

$HD_Form->AddEditSelect(
    _("TYPE"), "type",
    $type_list,
    "",
    _("ALOC (average length of call) ; ASR (answer seize ratio) ; CIC (Consecutive Incomplete Calls)")
);

$HD_Form->AddEditElement(
    _("MAX VALUE"),
    "`maxvalue`",
    _("Input the maximum value (between 0 and 1 for ASR ; > 0 for ALOC in sec ; > 0 for CIC)"),
    "",
    null,
    _("Insert the maximum value in")
);

$HD_Form->AddEditElement(
    _("MIN VALUE"),
    "minvalue",
    _("Input the minimum value (between 0 and 1 for ASR ; >= 0 for ALOC in sec ; not used by CIC) or leave it empty")
);

$HD_Form->AddEditPopup(
    _("TRUNK"),
    "id_trunk",
    "A2B_entity_trunk.php?popup_select=1&",
    _("Set if you want to apply the alarm on a specific trunk or leave empty for all trunk.")
);

$HD_Form->AddEditSelect(_("STATUS"), "status", $actived_list);

$HD_Form->AddEditElement(
    _("EMAIL TO SEND ALARM"),
    "emailreport",
    "",
    "maxlength=40",
    [Validator::class, "email"],
    _("Insert the email to send the alarm")
);

$HD_Form->FG_INTRO_TEXT_ADITION = _("Add an alarm below");

$HD_Form->FG_LOCATION_AFTER_ADD = "?id=";
$HD_Form->FG_LOCATION_AFTER_EDIT = "?id=";
$HD_Form->FG_LOCATION_AFTER_DELETE = "?id=";
