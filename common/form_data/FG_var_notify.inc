<?php

use A2billing\A2Billing;
use A2billing\Forms\FormHandler;
use A2billing\Forms\Validator;

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
 * @var A2Billing $A2B
 */

getpost_ifset(["id", "email_notification", "credit_notification", "notify_email", "message"]);
/**
 * @var numeric-string|null $id
 * @var string|null $email_notification
 * @var string|null $credit_notification
 * @var string|null $notify_email
 * @var string|null $message
 */

$HD_Form = new FormHandler("cc_card", "Card");
$HD_Form->list_query_conditions = $HD_Form->update_query_conditions = ["id" => Customer::id()];

$HD_Form->FG_ENABLE_EDIT_BUTTON = true;
$HD_Form->help_text = _("Notification settings.") . '<br>' . _("You can update your notification settings here.");

$limits_notify = getLimitNotify_List($A2B);

$HD_Form->AddEditSelect(
    _("LIMIT CREDIT NOTIFICATION"),
    "credit_notification",
    $limits_notify,
    "",
    _("currency : ") . strtoupper(BASE_CURRENCY),
    [],
    gettext("Insert the number of credit when the customer receive a notification"),
    ""
);

$HD_Form->AddEditRadio(
    _("PERMITTED NOTIFICATIONS BY MAIL"),
    "notify_email",
    getYesNoList(),
    "0",
    _("Enable Email Notification"),
    [],
    _("Choose if you want to enable the notification by email for this card")
);

$HD_Form->AddEditElement(
    _("EMAIL NOTIFICATION"),
    "email_notification",
    "",
    ["maxlength" => 70],
    [Validator::class, "email"],
    _("Insert the email to notify the customers"),
    "",
    "NO"
);

$HD_Form->add_message_intro = "";
$HD_Form->add_message_result = _("Your record has been updated.");

$HD_Form->FG_LOCATION_AFTER_EDIT = "A2B_notification.php?form_action=ask-edit&id=$_SESSION[card_id]&message=success";
