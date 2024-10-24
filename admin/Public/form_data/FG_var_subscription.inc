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

getpost_ifset(array('id', 'label', 'fee', 'status', 'emailreport'));

$HD_Form = new FormHandler("cc_subscription_service", "Subscription Service");

$HD_Form->no_debug();
$HD_Form -> FG_TABLE_DEFAULT_ORDER = "id";
$HD_Form -> FG_TABLE_DEFAULT_SENS = "DESC";


$HD_Form ->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form ->FG_LIST_ADDING_BUTTON_LINK1 = "A2B_entity_subscription.php?form_action=ask-add";
$HD_Form ->FG_LIST_ADDING_BUTTON_ALT1 = $HD_Form ->FG_LIST_ADDING_BUTTON_MSG1 = gettext("Add SUBSCRIPTION SERVICE");
$HD_Form ->FG_LIST_ADDING_BUTTON_IMG1 = Images_Path ."/script.png" ;


$actived_list = getActivationList();

$daynumber_list = array();
for ($k=1;$k<=20;$k++)
        $daynumber_list["$k"]  = array( "$k", "$k");





$HD_Form -> AddViewElement(gettext("LABEL"), "label");
$HD_Form -> AddViewElement(gettext("FEE"), "fee");
$HD_Form -> AddViewElement(gettext("STATUS"), "status", true, 0, "", "list", $actived_list);
$HD_Form -> AddViewElement(gettext("DATE START"), "startdate");
$HD_Form -> AddViewElement(gettext("DATE STOP"), "stopdate");
$HD_Form -> AddViewElement(gettext("NBRUN"), "numberofrun");
$HD_Form -> AddViewElement(gettext("DATE LASTRUN"), "datelastrun", true, 16);
$HD_Form -> AddViewElement(gettext("ACC PERFORM"), "totalcardperform");
$HD_Form -> AddViewElement(gettext("TOTAL CREDIT"), "totalcredit");


// added a parameter to append  FG_TABLE_ID  ( by default ) or disable 0.
$HD_Form -> FieldViewElement ('label, fee, status,startdate,stopdate, numberofrun, datelastrun, totalcardperform, totalcredit');

$HD_Form -> FG_ENABLE_EDIT_BUTTON = true;
$HD_Form -> FG_ENABLE_DELETE_BUTTON = true;
$HD_Form -> FG_ENABLE_ADD_BUTTON = true;
// View Details
// $HD_Form -> FG_OTHER_BUTTON1 = true;
// |param| will be replace by id .
// you don't need that, if you don't supply any |param| string id will be append after FG_OTHER_BUTTON1_LINK.

//$HD_Form -> FG_OTHER_BUTTON1_LINK = "javascript:;\" onClick=\"window.open('CC_entity_subscription_details.php?displayfooter=0&popup_select=1&displayheader=0&id=|param|','','scrollbars=yes,resizable=yes,width=600,height=350')\"";
//$HD_Form -> FG_OTHER_BUTTON1_IMG = Images_Path . "/icon-viewdetails.gif";

$comp_zero = $comp_date_plus='';
if (isset($form_action) && $form_action=="ask-add") {
	$begin_date = date("Y");
	$end_date = date("-m-d H:i:s");
	$comp_date = "value='".$begin_date.$end_date."'";
	//set passed id if any (used if called from customer screen, id is passed to pr-epopluate value
	$idd = "value='$id'";
	//Max value Mysql Timestamp can store is '2038-01-19 03:14:07' UTC
	$comp_date_plus = "value='2038-01-01 00:00:00'";
	$comp_zero = "value='0'";
}

$HD_Form->AddEditElement(
    gettext("LABEL"),
    "label",
    "",
    "size=35 maxlength=30",
    3,
    gettext("Insert the service label")
);

$HD_Form->AddEditElement(
    gettext("FEE"),
    "fee",
    gettext("Set the fee amount that the user have to pay per month."),
    "size=30 maxlength=30",
    12,
    gettext("Insert the fee amount.")
);

$HD_Form->AddEditSelect(gettext("STATUS"), "status", $actived_list);

$HD_Form->AddEditElement(
    gettext("START DATE"),
    "startdate",
    gettext("please use the format YYYY-MM-DD HH:MM:SS. For instance, '2004-12-31 00:00:00'"),
    "size=40 maxlength=40  $comp_date",
    10,
    gettext("Insert the start date"),
    "",
    "NO-NULL",
    
);

$HD_Form->AddEditElement(
    gettext("STOP DATE"),
    "stopdate",
    gettext("please use the format YYYY-MM-DD HH:MM:SS. For instance, '2004-12-31 00:00:00'"),
    "size=40 maxlength=40  $comp_date_plus",
    10,
    gettext("Insert the stop date"),
    "",
    "NO-NULL",
    
);

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

$HD_Form -> FG_LOCATION_AFTER_ADD = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";
$HD_Form -> FG_LOCATION_AFTER_EDIT = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";
$HD_Form -> FG_LOCATION_AFTER_DELETE = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";
