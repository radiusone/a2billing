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

$form_action ??= "list";

$HD_Form = new FormHandler("cc_subscription_service", _("Subscription Service"));

$HD_Form->list_query_order_direction = "DESC";

$HD_Form->AddListTopButton(null, null, "box-seam");

$HD_Form->help_text = create_help(
    _("SUBSCRIPTION FEE - You can bill the user  in a monthly, weekly or any time period for being subscribed on your service. The fee amount is defined here and the period through the cront configuration.")
);

$actived_list = getActivationList();

$HD_Form->AddListValue(_("Label"), "label");
$HD_Form->AddListValue(_("Fee"), "fee", "get_money");
$HD_Form->AddListMapping(_("Status"), "status", $actived_list);
$HD_Form->AddListValue(_("Start date"), "startdate");
$HD_Form->AddListValue(_("Stop date"), "stopdate");
$HD_Form->AddListValue(_("Runs"), "numberofrun");
$HD_Form->AddListValue(_("Last run"), "datelastrun");
$HD_Form->AddListValue(_("Cards affected"), "totalcardperform");
$HD_Form->AddListValue(_("Total credit"), "totalcredit", "get_money");

$HD_Form->FG_ENABLE_EDIT_BUTTON = true;
$HD_Form->FG_ENABLE_DELETE_BUTTON = true;
$HD_Form->FG_ENABLE_ADD_BUTTON = true;

$HD_Form->AddEditElement(
    _("LABEL"),
    "label",
    "",
    ["maxlength" => 30],
    [Validator::class, "min4Chars"],
    _("Insert the service label")
);

$HD_Form->AddEditElement(
    _("FEE"),
    "fee",
    _("Set the fee amount that the user have to pay per month."),
    ["maxlength" => 30],
    [Validator::class, "number"],
    _("Insert the fee amount.")
);

$HD_Form->AddEditSelect(_("STATUS"), "status", $actived_list);

$date_attr = ["maxlength" => 40];
if ($form_action === "ask-add") {
    $date_attr["value"] = (new DateTime())->format("Y-m-d H:i:s");
}
$HD_Form->AddEditElement(
    _("START DATE"),
    "startdate",
    _("please use the format YYYY-MM-DD HH:MM:SS. For instance, '2004-12-31 00:00:00'"),
    $date_attr,
    [Validator::class, "dateTime"],
    _("Insert the start date"),
    "NO-NULL",
    
);

if ($form_action === "ask-add") {
    $date_attr["value"] = (new DateTime("+10 years"))->format("Y-m-d H:i:s");
}
$HD_Form->AddEditElement(
    _("STOP DATE"),
    "stopdate",
    _("please use the format YYYY-MM-DD HH:MM:SS. For instance, '2004-12-31 00:00:00'"),
    $date_attr,
    [Validator::class, "dateTime"],
    _("Insert the stop date"),
    "NO-NULL",
    
);

$HD_Form->AddEditElement(
    _("EMAIL TO SEND REPORT"),
    "emailreport",
    "",
    ["maxlength" => 30],
    [Validator::class, "email"],
    _("Insert the email to send the report"),
    "NO-NULL"
);
