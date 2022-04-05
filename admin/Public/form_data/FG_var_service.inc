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


getpost_ifset(array('id', 'name', 'amount', 'period', 'rule', 'daynumber', 'stopmode', 'maxnumbercycle', 'status', 'emailreport'));


$HD_Form = new FormHandler("cc_service", "Service");

$HD_Form->no_debug();
$HD_Form -> FG_TABLE_DEFAULT_ORDER = "id";
$HD_Form -> FG_TABLE_DEFAULT_SENS = "DESC";


$HD_Form ->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form ->FG_LIST_ADDING_BUTTON_LINK1 = "A2B_entity_service.php?form_action=ask-add&section=".$_SESSION["menu_section"];
$HD_Form ->FG_LIST_ADDING_BUTTON_ALT1 = $HD_Form ->FG_LIST_ADDING_BUTTON_MSG1 = gettext("Add RECURRING SERVICE");
$HD_Form ->FG_LIST_ADDING_BUTTON_IMG1 = Images_Path ."/cog_add.png" ;



// TODO Integrate a generic LIST to Framework
$period_list = array();
$period_list["1"]  = array( "Daily", "1");
for ($k=2;$k<=60;$k++)
        $period_list["$k"]  = array( "every $k days", "$k");

$actived_list = getActivationList();

$rule_list = array();
$rule_list["3"]  = array( gettext("Apply Service every Y days"), "3");
$rule_list["1"]  = array( gettext("Apply service if card not used in last X days"), "1");
$rule_list["2"]  = array( gettext("Apply service if card has been used in the last X Days"), "2");
$rule_list["0"]  = array( gettext("No rule"), "0");

$daynumber_list = array();
for ($k=1;$k<=20;$k++)
        $daynumber_list["$k"]  = array( "$k", "$k");

$stopmode_list = array();
$stopmode_list["0"]  = array( gettext("Never"), "0");
$stopmode_list["1"]  = array( gettext("Account balance below zero"), "1");
$stopmode_list["2"]  = array( gettext("Max number of cycle reach"), "2");

$operatemode_list = array();
$operatemode_list["0"] = array(gettext("Allow be charged into negative balance"),"0");
$operatemode_list["1"] = array(gettext("Do partial charge but never go into negative balance"),"1");

$usegroup_list = array();
$usegroup_list["0"] = array(gettext("List of groups below"),"0");
$usegroup_list["1"] = array(gettext("Any group"),"1");

$HD_Form -> AddViewElement(gettext("NAME"), "name");
$HD_Form -> AddViewElement(gettext("AMOUNT"), "amount");
$HD_Form -> AddViewElement(gettext("PERIOD"), "period");
$HD_Form -> AddViewElement(gettext("STATUS"), "status", true, 0, "", "list", $actived_list);
$HD_Form -> AddViewElement(gettext("NBRUN"), "numberofrun");
$HD_Form -> AddViewElement(gettext("DATELASTRUN"), "datelastrun", true, 16);
$HD_Form -> AddViewElement(gettext("CARDPERFORM"), "totalcardperform");
$HD_Form -> AddViewElement(gettext("TOTALCREDIT"), "totalcredit");


// added a parameter to append  FG_TABLE_ID  ( by default ) or disable 0.
$HD_Form -> FieldViewElement ('name, amount, period, status, numberofrun, datelastrun, totalcardperform, totalcredit');

$HD_Form -> FG_ENABLE_ADD_BUTTON = true;
$HD_Form -> FG_ENABLE_EDIT_BUTTON = true;
$HD_Form -> FG_ENABLE_DELETE_BUTTON = true;
// View Details
$HD_Form -> FG_OTHER_BUTTON1 = true;
// |param| will be replace by id .
// you don't need that, if you don't supply any |param| string id will be append after FG_OTHER_BUTTON1_LINK.

$HD_Form -> FG_OTHER_BUTTON1_LINK = "javascript:;\" onClick=\"window.open('CC_entity_service_details.php?displayfooter=0&popup_select=1&displayheader=0&id=|param|','','scrollbars=yes,resizable=yes,width=600,height=350')\"";
$HD_Form -> FG_OTHER_BUTTON1_IMG = Images_Path . "/icon-viewdetails.gif";
$HD_Form -> FG_OTHER_BUTTON1_ALT = gettext("VIEW DETAILS");


$HD_Form->AddEditElement(
    gettext("NAME"),
    "name",
    "",
    "size=35 maxlength=30",
    3,
    gettext("Insert the service name")
);

$HD_Form->AddEditElement(
    gettext("AMOUNT CREDIT"),
    "amount",
    gettext("Set the amount of credit to remove from the card. 0 will remove all credit from the card use -1 for DID recurrent service."),
    "size=30 maxlength=30",
    12,
    gettext("Insert the number of credit to remove from the cards.")
);

$HD_Form->AddEditSelect(
    "rule",
    $rule_list,
    gettext("RULE"),
    gettext("where X - day number (X) in days <br>	and Y = number set in period field")
);

$HD_Form->AddEditElement(
    gettext("DAY NUMBER (X)"),
    "daynumber",
    gettext("Set the number of days to apply to the rule above."),
    "size=20 maxlength=3",
    4,
    gettext("Insert the daynumber"),
    "",
    "NO"
);

$HD_Form->AddEditSelect("period", $period_list, gettext("PERIOD (Y)"), gettext("Interval to apply service"));

$HD_Form->AddEditSelect("stopmode", $stopmode_list, gettext("STOP MODE"));

$HD_Form->AddEditElement(
    gettext("MAX NUMBER OF CYCLES BY CARD"),
    "maxnumbercycle",
    gettext("Set the maximum number of times that the service should be applied."),
    "size=20 maxlength=3",
    4,
    gettext("Insert the max number of cycle"),
    "",
    "NO-NULL"
);

$HD_Form->AddEditSelect("status", $actived_list, gettext("STATUS"));

$HD_Form->AddEditElement(
    gettext("EMAIL TO SEND REPORT"),
    "emailreport",
    "",
    "size=30 maxlength=30",
    1,
    gettext("Insert the email to send the report"),
    "",
    "NO-NULL"
);

$HD_Form->AddEditSqlSelect(
    "dialplan",
    gettext("CALL PLAN"),
    "cc_tariffgroup",
    "tariffgroupname,id",
    "",
    "",
    '<option value="0" selected="selected">--' . gettext("ANY") . '--</option>'
);

$HD_Form->AddEditSelect(
    "operate_mode",
    $operatemode_list,
    gettext("OPERATE MODE"),
    gettext("If you want to charge only part of sum in case if current balance less then amount, select second option. Will be charged only part of amount, cause balance to be 0.0")
);

$HD_Form->AddEditSelect(
    "use_group",
    $usegroup_list,
    gettext("SKIP GROUP"),
    gettext("Select first option(default) to manualy select groups or select second option to use service for any group, discarding list below")
);

$DBHandle_max  = DbConnect();
$numrow = 0;
$resmax = $DBHandle_max -> Execute("SELECT * FROM cc_card_group");
if ( $resmax )
	$numrow = $resmax -> RecordCount( );

if ($numrow > 0) {
    $HD_Form->AddEditSqlSelect(
        "service",
        gettext("CARD GROUP"),
        "cards_services",
        "name,id",
        "",
        "",
        "",
        "",
        "%1 : (%2)",
        "size='10' multiple='multiple'",
        [
            "name" => "id_card_group",
            "tables" => "cc_card_group LEFT JOIN cc_cardgroup_service ON cc_card_group.id=cc_cardgroup_service.id_card_group",
            "columns" => "DISTINCT cc_card_group.name, cc_card_group.id",
            "where" => "cc_cardgroup_service.id_service = %id",
            "format" => "%2 - (%1)",
        ]
    );
}

$HD_Form->FieldEditElement('name, amount, rule, daynumber, period, stopmode, maxnumbercycle, status, emailreport, dialplan, operate_mode, use_group');

if (DB_TYPE == "mysql") {
    $HD_Form->FG_ADD_QUERY_HIDDEN_INPUTS["datecreate"] = "now()";
}

$HD_Form->FG_LOCATION_AFTER_ADD = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL) . "?atmenu=document&id=";
$HD_Form->FG_LOCATION_AFTER_EDIT = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL) . "?atmenu=document&id=";
$HD_Form->FG_LOCATION_AFTER_DELETE = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL) . "?atmenu=document&id=";

