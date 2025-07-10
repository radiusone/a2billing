<?php

use A2billing\Admin;
use A2billing\Forms\FormHandler;
use A2billing\Forms\Validator;
use A2billing\Table;

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

/**
 * @var string $form_action
 */

$HD_Form = new FormHandler("cc_callback_spool", _("Callback"));
$HD_Form->list_query_order_columns = ["entry_time"];
$HD_Form->list_query_order_direction  = "DESC";

$HD_Form->AddListTopButton(null, null, "telephone-outbound-fill");

$HD_Form->help_text = create_help(
    _("Callback will offer you an easy way to connect any phone to our Asterisk platform. Browse here the pending and completed callbacks. You will see that different parameters determine the callback, the way to reach the user, the time when we need to call him, the result of the last attempts, etc...")
);

$HD_Form->AddListValue(_("ID"), "id");
$HD_Form->AddListValue(_("UNIQUEID"), "uniqueid");
$HD_Form->AddListValue(_("ENTRY TIME"), "entry_time");
$HD_Form->AddListValue(_("STATUS"), "status");
$HD_Form->AddListValue(_("SERVER IP"), "server_ip");
$HD_Form->AddListValue(abbr(_("NA"),_("NUMBER OF ATTEMPT")), "num_attempt");
$HD_Form->AddListValue(abbr(_("AR"), _("AGI RESULT")), "manager_result"); // not sure why this isn't agi_result?
$HD_Form->AddListValue(_("CALLBACK TIME"), "callback_time");
$HD_Form->AddListValue(_("LAST_ATTEMPT"), "last_attempt_time");
$HD_Form->AddListValue(_("CHANNEL"), "channel");
$HD_Form->AddListValue(_("CONTEXT"), "context");
$HD_Form->AddListSqlMapping(_("SERVER"), "id_server", new Table("cc_server_manager", ["id", "server_ip"]));

$HD_Form->FG_ENABLE_ADD_BUTTON = true;
$HD_Form->FG_ENABLE_EDIT_BUTTON = true;
$HD_Form->FG_ENABLE_DELETE_BUTTON = true;

$HD_Form->AddEditSqlSelect(
    _("SERVER GROUP"),
    "id_server_group",
    new Table("cc_server_group", ["name", "id"])
);

$HD_Form->AddEditElement(
    _("UNIQUEID"),
    "uniqueid",
    _("This is an identifier to cross check the callback's request.")
        . _("(useful if you have requests coming from an other platform through the API))"),
    ["maxlength" => 30],
    [Validator::class, "min4Chars"],
    _("Insert the uniqueid")
);


$HD_Form->AddEditSelect(
    _("STATUS"),
    "status",
    [
        "PENDING" => _("PENDING"),
        "SENT" => _("SENT"),
        "ERROR" => _("ERROR"),
    ]
);

$server_attr = ["maxlength" => 30];
if (($form_action ?? "") === "ask-add") {
    $server_attr["value"] = "localhost";
}
$HD_Form->AddEditElement(
    _("IP SERVER"),
    "server_ip",
    "",
    $server_attr,
    [Validator::class, "min2Chars"],
    _("Insert the Ip Server")
);

$num_attr = ["maxlength" => 10];
if (($form_action ?? "") === "ask-add") {
    $server_attr["value"] = 0;
}
$HD_Form->AddEditElement(
    _("NUMBER ATTEMPTS"),
    "num_attempt",
    "",
    $num_attr,
    [Validator::class, "numeric"],
    _("Insert the number of attempts")
);


$HD_Form->AddEditElement(
    _("MANAGER RESULT"),
    "manager_result",
    "",
    ["maxlength" => 80],
    [Validator::class, "min2Chars"],
    _("Insert the manager result"),
    "",
    "NO"
);


$HD_Form->AddEditElement(
    _("AGI RESULT"),
    "agi_result",
    "",
    ["maxlength" => 80],
    [Validator::class, "min2Chars"],
    _("Insert the agi result"),
    "",
    "NO"
);

$date_attr = ["maxlength" => 20];
if (($form_action ?? "") === "ask-add") {
    $date_attr["value"] = date("Y-m-d H:i:s");
}
$HD_Form->AddEditElement(
    _("CALLBACK DATE"),
    "callback_time",
    _("please use the format YYYY-MM-DD HH:MM:SS. For instance, '2004-12-31 00:00:00'"),
    $date_attr,
    [Validator::class, "dateTime"],
    "Insert the callback time",
    "",
    "NO-NULL",
    
);

$HD_Form->AddEditSection(_("Asterisk Settings"));

$HD_Form->AddEditElement(
    _("CHANNEL"),
    "channel",
    _("Channel on which to originate the call (SIP/123@1.1.1.4)"),
    ["maxlength" => 70],
    [Validator::class, "min2Chars"],
    _("Insert the channel")
);


$HD_Form->AddEditElement(
    _("EXTENSION"),
    "exten",
    _("Extension to use on connect (must use Context & Priority with it)"),
    ["maxlength" => 30],
    [Validator::class, "min2Chars"],
    _("Insert the channel")
);

$HD_Form->AddEditElement(
    _("CONTEXT"),
    "context",
    _("Context to use on connect (must use Exten & Priority with it)"),
    ["maxlength" => 50],
    [Validator::class, "min2Chars"],
    _("Insert the context")
);


$HD_Form->AddEditElement(
    _("PRIORITY"),
    "priority",
    _("Priority to use on connect (must use Context & Exten with it)"),
    ["maxlength" => 10],
    [Validator::class, "numeric"],
    _("Insert the priority")
);


$HD_Form->AddEditElement(
    _("APPLICATION"),
    "application",
    _("Application to use on connect : meetme or other (use Data for parameters)"),
    ["maxlength" => 30],
    [Validator::class, "min2Chars"],
    _("Insert the application"),
    "",
    "NO"
);


$HD_Form->AddEditElement(
    _("DATA"),
    "data",
    _("Data if Application parameter is used"),
    ["maxlength" => 30],
    [Validator::class, "min2Chars"],
    _("Insert the data"),
    "",
    "NO"
);


$HD_Form->AddEditElement(
    _("TIMEOUT"),
    "timeout",
    _("Timeout for the connection to happen (30000)"),
    ["maxlength" => 15],
    [Validator::class, "min2Chars"],
    _("Insert the timeout"),
    "",
    "NO"
);


$HD_Form->AddEditElement(
    _("CALLERID"),
    "callerid",
    _("CallerID to use for the call"),
    ["maxlength" => 30],
    [Validator::class, "min2Chars"],
    _("Insert the callerid"),
    "",
    "NO"
);


$HD_Form->AddEditElement(
    _("variable"),
    "variable",
    _("Callback info for Asterisk (variable list to transfer to the channel) var1=23|var2=24|var3=25"),
    ["maxlength" => 300],
    [Validator::class, "min2Chars"],
    _("Insert the variable"),
    "",
    "NO"
);


$HD_Form->AddEditElement(
    _("account"),
    "account",
    _("Account code for the call for billing purpose"),
    ["maxlength" => 30],
    [Validator::class, "min2Chars"],
    _("Insert the account"),
    "",
    "NO"
);


$HD_Form->AddEditElement(
    _("async"),
    "async",
    _("For the origination to be asynchronous (allows multiple calls to be generated without waiting for a response)"),
    ["maxlength" => 10],
    [Validator::class, "min2Chars"],
    _("Insert the async"),
    "",
    "NO"
);


$HD_Form->AddEditElement(
    _("actionid"),
    "actionid",
    _("The request identifier. It allows you to identify the response to this request. You may use a number or a string. <br> Useful when you make several simultaneous requests. ActionID: ABC45678901234567890 "),
    ["maxlength" => 30],
    [Validator::class, "min2Chars"],
    _("Insert the actionid"),
    "",
    "NO"
);

// Set the filter variables
$HD_Form->AddListFilter("uniqueid", _("Unique ID"));

$HD_Form->add_message_intro = "";
