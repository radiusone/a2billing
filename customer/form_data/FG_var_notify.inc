<?php

use A2billing\Customer;
use A2billing\Forms\FormHandler;
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

if (! has_rights (Customer::ACX_ACCESS)) {
    Header ("HTTP/1.0 401 Unauthorized");
    Header ("Location: PP_error.php?c=accessdenied");
    die();
}



getpost_ifset(array('id', 'email_notification','credit_notification','notify_email', 'message'));


$HD_Form = new FormHandler("cc_card", "Card");
$HD_Form->no_debug();
$HD_Form -> FG_TABLE_DEFAULT_SENS = "ASC";
$HD_Form->update_query_conditions = ["id" => $_SESSION["card_id"]];
$HD_Form -> FG_INTRO_TEXT_EDITION = '';

$HD_Form -> FG_ENABLE_EDIT_BUTTON= true;
$QUERY = "SELECT status, currency FROM cc_card  WHERE username = '".$_SESSION["pr_login"]."' AND uipass = '".$_SESSION["pr_password"]."'";
$DBHandle  = DbConnect();
$numrow = 0;
$resmax = $DBHandle -> Execute($QUERY);
if ($resmax)
	$numrow = $resmax -> RecordCount();

if ($numrow == 0) exit();
$customer_info =$resmax -> fetchRow();



$currencies_list = get_currencies();

if (!isset($currencies_list[strtoupper($customer_info [1])]["value"]) || !is_numeric($currencies_list[strtoupper($customer_info [1])]["value"])) $mycur = 1;
else $mycur = $currencies_list[strtoupper($customer_info [1])]["value"];


$limits_notify = getLimitNotify_List($A2B);
array_unshift($limits_notify, [gettext("NOT DEFINED"), -1]);

$HD_Form->AddEditSelect(
    gettext("LIMIT CREDIT NOTIFICATION"),
    "credit_notification",
    $limits_notify,
    "",
    gettext("currency : ") . strtoupper(BASE_CURRENCY),
    "",
    gettext("Insert the number of credit when the customer receive a notification"),
    ""
);

$HD_Form->AddEditRadio(
    gettext("PERMITTED NOTIFICATIONS BY MAIL"),
    "notify_email",
    [["Yes", "1"], ["No", "0"]],
    "0",
    gettext("Enable Email Notification"), "",
    gettext("Choose if you want to enable the notification by email for this card")
);

$HD_Form->AddEditElement(
    gettext("EMAIL NOTIFICATION"),
    "email_notification",
    "",
    "size=30 maxlength=70",
    1,
    gettext("Insert the email to notify the customers"),
    "",
    "NO"
);

$HD_Form -> FG_INTRO_TEXT_ADITION = '';
$HD_Form -> FG_TEXT_ADITION_CONFIRMATION = gettext("Your record has been updated.")."<br>";

$HD_Form -> FG_LOCATION_AFTER_EDIT = "A2B_notification.php?message=success";
