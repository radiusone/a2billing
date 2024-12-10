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

$form_action ??= "list";

$HD_Form = new FormHandler("cc_subscription_service", _("Subscription Service"));

$HD_Form->FG_TABLE_DEFAULT_SENS = "DESC";

$HD_Form->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form->FG_LIST_ADDING_BUTTON_IMG1 = get_image_path("script.png") ;

$actived_list = getActivationList();

$HD_Form->AddListValue(_("Label"), "label");
$HD_Form->AddListValue(_("Fee"), "fee", "display_money");
$HD_Form->AddListMapping(_("Status"), "status", $actived_list);
$HD_Form->AddListValue(_("Start date"), "startdate");
$HD_Form->AddListValue(_("Stop date"), "stopdate");
$HD_Form->AddListValue(_("Runs"), "numberofrun");
$HD_Form->AddListValue(_("Last run"), "datelastrun");
$HD_Form->AddListValue(_("Cards affected"), "totalcardperform");
$HD_Form->AddListValue(_("Total credit"), "totalcredit", "display_money");
$HD_Form->FieldViewElement([
    "label",
    "fee",
    "status",
    "startdate",
    "stopdate",
    "numberofrun",
    "datelastrun",
    "totalcardperform",
    "totalcredit",
]);

$HD_Form->FG_ENABLE_EDIT_BUTTON = true;
$HD_Form->FG_ENABLE_DELETE_BUTTON = true;
$HD_Form->FG_ENABLE_ADD_BUTTON = true;

$comp_date = $comp_date_plus = '';
if ($form_action === "ask-add") {
	$end_date = date("Y-m-d H:i:s");
	$comp_date = "value='$end_date'";
	//Max value Mysql Timestamp can store is '2038-01-19 03:14:07' UTC
	$comp_date_plus = "value='2038-01-01 00:00:00'";
}

$HD_Form->AddEditElement(
    _("LABEL"),
    "label",
    "",
    "size=35 maxlength=30",
    3, // [Validator::class, "min4Chars"],
    _("Insert the service label")
);

$HD_Form->AddEditElement(
    _("FEE"),
    "fee",
    _("Set the fee amount that the user have to pay per month."),
    "size=30 maxlength=30",
    12, // [Validator::class, "number"],
    _("Insert the fee amount.")
);

$HD_Form->AddEditSelect(_("STATUS"), "status", $actived_list);

$HD_Form->AddEditElement(
    _("START DATE"),
    "startdate",
    _("please use the format YYYY-MM-DD HH:MM:SS. For instance, '2004-12-31 00:00:00'"),
    "size=40 maxlength=40  $comp_date",
    10, // [Validator::class, "dateTime"],
    _("Insert the start date"),
    "",
    "NO-NULL",
    
);

$HD_Form->AddEditElement(
    _("STOP DATE"),
    "stopdate",
    _("please use the format YYYY-MM-DD HH:MM:SS. For instance, '2004-12-31 00:00:00'"),
    "size=40 maxlength=40  $comp_date_plus",
    10, // [Validator::class, "dateTime"],
    _("Insert the stop date"),
    "",
    "NO-NULL",
    
);

$HD_Form->AddEditElement(
    _("EMAIL TO SEND REPORT"),
    "emailreport",
    "",
    "size=30 maxlength=30",
    1, // [Validator::class, "email"],
    _("Insert the email to send the report"),
    "",
    "NO-NULL"
);

$HD_Form->FG_LOCATION_AFTER_ADD = "?id=";
$HD_Form->FG_LOCATION_AFTER_EDIT = "?id=";
$HD_Form->FG_LOCATION_AFTER_DELETE = "?id=";
