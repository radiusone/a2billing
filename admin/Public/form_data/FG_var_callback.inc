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

getpost_ifset(array('id', 'uniqueid', 'entry_time', 'status', 'server_ip', 'num_attempt', 'last_attempt_time', 'manager_result', 'agi_result', 'callback_time', 'channel', 'exten', 'context', 'priority', 'application', 'data', 'timeout', 'callerid', 'variable', 'account', 'async', 'actionid'));


$HD_Form = new FormHandler("cc_callback_spool", gettext("Callback"));

$HD_Form->no_debug();
$HD_Form -> FG_TABLE_DEFAULT_ORDER = "id";
$HD_Form -> FG_TABLE_DEFAULT_SENS  = "DESC";



$HD_Form ->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form ->FG_LIST_ADDING_BUTTON_LINK1 = "A2B_entity_callback.php?form_action=ask-add";
$HD_Form ->FG_LIST_ADDING_BUTTON_ALT1 = $HD_Form ->FG_LIST_ADDING_BUTTON_MSG1 = gettext("Add CALLBACK");
$HD_Form ->FG_LIST_ADDING_BUTTON_IMG1 = Images_Path ."/lightning_add.png" ;



$actived_list = getActivationTrueFalseList();
$status_list = ["PENDING" => [_("PENDING"), "PENDING"], "SENT" => [_("SENT"), "SENT"], "ERROR" => [_("ERROR"), "ERROR"],];
$yesno = getYesNoList();


$HD_Form -> AddViewElement(gettext("ID"), "id");
$HD_Form -> AddViewElement(gettext("UNIQUEID"), "uniqueid");
$HD_Form -> AddViewElement(gettext("ENTRY TIME"), "entry_time", true, 19);
$HD_Form -> AddViewElement(gettext("STATUS"), "status");
$HD_Form -> AddViewElement(gettext("SERVER IP"), "server_ip");
$HD_Form -> AddViewElement("<acronym title=\"" . gettext("NUMBER OF ATTEMPT") . "\">NA</acronym>", "num_attempt");
$HD_Form -> AddViewElement("<acronym title=\"" . gettext("AGI RESULT") . "\">AR</acronym>", "agi_result");
$HD_Form -> AddViewElement(gettext("CALLBACK TIME"), "callback_time", true, 19);
$HD_Form -> AddViewElement(gettext("LAST_ATTEMPT"), "last_attempt_time", true, 19);
$HD_Form -> AddViewElement(gettext("CHANNEL"), "channel");
$HD_Form -> AddViewElement(gettext("CONTEXT"), "context");
$HD_Form -> AddViewElement(gettext("SERVER"), "id_server", true, 15, "", "lie", "server_manager", "server_ip", "id='%id'", "%1");


$HD_Form -> FieldViewElement ('id, uniqueid, entry_time, status, server_ip, num_attempt, manager_result, callback_time, last_attempt_time, channel, context, id_server');

$HD_Form -> FG_LIST_VIEW_PAGE_SIZE = 10;

$HD_Form -> FG_ENABLE_ADD_BUTTON = true;
$HD_Form -> FG_ENABLE_EDIT_BUTTON = true;
$HD_Form -> FG_ENABLE_DELETE_BUTTON = true;

$HD_Form->AddEditSqlSelect(gettext("SERVER GROUP"), "id_server_group", "cc_server_group", "name,id");

$HD_Form->AddEditElement(
    gettext("UNIQUEID"),
    "uniqueid",
    gettext("This is an identifier to cross check the callback's request.") . gettext("(useful if you have requests coming from an other platform through the API))"),
    "size=20 maxlength=30",
    3,
    gettext("Insert the uniqueid")
);


if ($form_action=="ask-add") {
	$begin_date = date("Y");
	$begin_date_plus = date("Y") + 10;
	$end_date = date("-m-d H:i:s");
	$comp_date = "value='".$begin_date.$end_date."'";
	$comp_date_plus = "value='".$begin_date_plus.$end_date."'";

	$def_ipserver = " value='localhost' ";
	$def_nbattempt = " value='0' ";
}

$HD_Form->AddEditSelect(gettext("STATUS"), "status", $status_list);

$HD_Form->AddEditElement(
    gettext("IP SERVER"),
    "server_ip",
    "",
    "size=20 maxlength=30 $def_ipserver",
    11,
    gettext("Insert the Ip Server")
);


$HD_Form->AddEditElement(
    gettext("NUMBER ATTEMPTS"),
    "num_attempt",
    "",
    "size=5 maxlength=10 $def_nbattempt",
    4,
    gettext("Insert the number of attempts")
);


$HD_Form->AddEditElement(
    gettext("MANAGER RESULT"),
    "manager_result",
    "",
    "size=50 maxlength=80",
    11,
    gettext("Insert the manager result"),
    "",
    "NO"
);


$HD_Form->AddEditElement(
    gettext("AGI RESULT"),
    "agi_result",
    "",
    "size=50 maxlength=80",
    11,
    gettext("Insert the agi result"),
    "",
    "NO"
);


$HD_Form->AddEditElement(
    gettext("CALLBACK DATE"),
    "callback_time",
    gettext("please use the format YYYY-MM-DD HH:MM:SS. For instance, '2004-12-31 00:00:00'"),
    "size=20 maxlength=20  $comp_date",
    10,
    "Insert the callback time",
    "",
    "NO-NULL",
    
);


$HD_Form->AddEditElement(
    gettext("CHANNEL"),
    "channel",
    gettext("Channel on which to originate the call (SIP/123@1.1.1.4)"),
    "size=60 maxlength=70",
    11,
    gettext("Insert the channel"),
    gettext("Define all the information requested by asterisk to generate the callback")
);


$HD_Form->AddEditElement(
    gettext("EXTENSION"),
    "exten",
    gettext("Extension to use on connect (must use Context & Priority with it)"),
    "size=15 maxlength=30",
    11,
    gettext("Insert the channel")
);

$HD_Form->AddEditElement(
    gettext("CONTEXT"),
    "context",
    gettext("Context to use on connect (must use Exten & Priority with it)"),
    "size=30 maxlength=50",
    11,
    gettext("Insert the context")
);


$HD_Form->AddEditElement(
    gettext("PRIORITY"),
    "priority",
    gettext("Priority to use on connect (must use Context & Exten with it)"),
    "size=10 maxlength=10",
    4,
    gettext("Insert the priority")
);


$HD_Form->AddEditElement(
    gettext("APPLICATION"),
    "application",
    gettext("Application to use on connect : meetme or other (use Data for parameters)"),
    "size=20 maxlength=30",
    11,
    gettext("Insert the application"),
    "",
    "NO"
);


$HD_Form->AddEditElement(
    gettext("DATA"),
    "data",
    gettext("Data if Application parameter is used"),
    "size=30 maxlength=30",
    11,
    gettext("Insert the data"),
    "",
    "NO"
);


$HD_Form->AddEditElement(
    gettext("TIMEOUT"),
    "timeout",
    gettext("Timeout for the connection to happen (30000)"),
    "size=10 maxlength=15",
    11,
    gettext("Insert the timeout"),
    "",
    "NO"
);


$HD_Form->AddEditElement(
    gettext("CALLERID"),
    "callerid",
    gettext("CallerID to use for the call"),
    "size=20 maxlength=30",
    11,
    gettext("Insert the callerid"),
    "",
    "NO"
);


$HD_Form->AddEditElement(
    gettext("variable"),
    "variable",
    gettext("Callback info for Asterisk (variable list to transfer to the channel) var1=23|var2=24|var3=25"),
    "size=80 maxlength=300",
    11,
    gettext("Insert the variable"),
    "",
    "NO"
);


$HD_Form->AddEditElement(
    gettext("account"),
    "account",
    gettext("Account code for the call for billing purpose"),
    "size=20 maxlength=30",
    11,
    gettext("Insert the account"),
    "",
    "NO"
);


$HD_Form->AddEditElement(
    gettext("async"),
    "async",
    gettext("For the origination to be asynchronous (allows multiple calls to be generated without waiting for a response)"),
    "size=10 maxlength=10",
    11,
    gettext("Insert the async"),
    "",
    "NO"
);


$HD_Form->AddEditElement(
    gettext("actionid"),
    "actionid",
    gettext("The request identifier. It allows you to identify the response to this request. You may use a number or a string. <br> Useful when you make several simultaneous requests. ActionID: ABC45678901234567890 "),
    "size=20 maxlength=30",
    11,
    gettext("Insert the actionid"),
    "",
    "NO"
);

// Set the filter variables
$HD_Form -> FG_FILTER_ENABLE = true;
$HD_Form -> FG_FILTER_COLUMN = 'uniqueid';
$HD_Form -> FG_FILTER_LABEL = gettext('uniqueid');

$HD_Form -> FG_INTRO_TEXT_ADITION = '';

$HD_Form -> FG_LOCATION_AFTER_ADD = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";
$HD_Form -> FG_LOCATION_AFTER_EDIT = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";
$HD_Form -> FG_LOCATION_AFTER_DELETE = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";
