<?php

use A2billing\Admin;
use A2billing\Forms\FormHandler;
use A2billing\Forms\Validator;
use A2billing\Mail;
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

Admin::checkPageAccess(Admin::ACX_ADMINISTRATOR);

getpost_ifset(["from_id"]);
/**
 * @var string $form_action
 * @var numeric-string $popup_select
 * @var numeric-string $from_id
 */

$HD_Form = new FormHandler("cc_templatemail", _("Mail Template"));

$HD_Form->FG_EDIT_BUTTON_LINK = "?form_action=ask-edit&id=";
$HD_Form->FG_DELETE_BUTTON_LINK = "?form_action=ask-delete&id=";

$HD_Form->list_help_text = "";
$HD_Form->help_text = create_help(
    _("Configure the mail template below.")
    . '<br/>'
    . _("A Reminder email can be sent (see a2billing.conf) to customers having low credit, a confirmation mail can be sent to customers after their signup, etc...")
);

$typemail_list = [
    Mail::$TYPE_REMINDER => _("Reminder"),
    Mail::$TYPE_SIGNUP => _("Signup"),
    Mail::$TYPE_FORGETPASSWORD => _("Forget password"),
    Mail::$TYPE_SIGNUPCONFIRM => _("Signup confirmed"),
    Mail::$TYPE_PAYMENT => _("Payment Confirmation"),
    Mail::$TYPE_EPAYMENTVERIFY => _("EPayment Warning"),
    Mail::$TYPE_DID_PAID => _("Did Payment confirmation"),
    Mail::$TYPE_DID_UNPAID => _("Did not payment warning"),
    Mail::$TYPE_DID_RELEASED => _("Did released warning"),
    "template1" => sprintf(_("Template %d"), 1),
    "template2" => sprintf(_("Template %d"), 2),
    "template3" => sprintf(_("Template %d"), 3),
    "template4" => sprintf(_("Template %d"), 4),
    "template5" => sprintf(_("Template %d"), 5),
    "template6" => sprintf(_("Template %d"), 6),
    "template7" => sprintf(_("Template %d"), 7),
    "template8" => sprintf(_("Template %d"), 8),
    "template9" => sprintf(_("Template %d"), 9),
    Mail::$TYPE_TICKET_NEW => _("New Ticket"),
    Mail::$TYPE_TICKET_MODIFY => _("Modify Ticket"),
    Mail::$TYPE_INVOICE_TO_PAY => _("Invoice to Pay"),
    Mail::$TYPE_SUBSCRIPTION_PAID => _("Subscription Paid"),
    Mail::$TYPE_SUBSCRIPTION_UNPAID => _("Subscription Unpaid"),
    Mail::$TYPE_SUBSCRIPTION_DISABLE_CARD => _("Subscription Disable Card"),
];
$message_text_bottom =
    _("The followings tags will be replaced in the message by the value in the database.") . '<br/>'
    . '<u>' . _('Tags used for EPayment Warning email') . ': </u><br/>'
    . '<b>$time$</b> : ' . _('time of payment') . '<br/>'
    . '<b>$paymentgateway$</b>: ' . _('display payment gateway') . '<br/>'
    . '<b>$itemAmount$</b>: ' . _('amount of payment') . '<br/>'
    . '<u>'._('Tags used for Payment email') . ': </u><br/>'
    . '<b>$itemName$</b>: ' . _('name of item paid') . '<br/>'
    . '<b>$itemID$</b> : ' . _('id of customer') . '<br/>'
    . '<b>$paymentMethod$</b> : ' . _('methode of payment') . '<br/>'
    . '<b>$paymentStatus$</b> : ' . _('status of payment') . '<br/>'
    . '<b>$itemAmount$</b>: ' . _('amount of payment') . '<br/>'
    . '<u>' . _('Tags used for Signup') . ': </u><br/> '
    . '<b>$loginkey$</b>: ' . _('key created to validate the signup') . '<br/>'
    . '<u>' . _('Tag used for Did Payment confirmation email') . ': </u><br/>'
    . '<b>$balance_remaining$</b>: ' . _('balance after payment') . '<br/>'
    . '<u>' . _('Tag used for Did not payment warning email') . ': </u><br/>'
    . '<b>$days_remaining$</b>: ' . _('number of day before release of the did') . '<br/>'
    . '<b>$invoice_ref$</b>: ' . _('invoice reference to pay for the did') . '<br/>'
    . '<u>' . _('Tag used for DID') . ': </u><br/>'
    . '<b>$did$</b>: ' . _('did number') . '<br/>'
    . '<b>$did_cost$</b>: ' . _('cost of the did') . '<br/>'
    . '<u>' . _('Tag used for Support Ticket') . ': </u><br/>'
    . '<b>$ticket_id$</b>: ' . _('Ticket Id') . '<br/>'
    . '<b>$ticket_owner$</b>: ' . _('Ticket Owner') . '<br/>'
    . '<b>$ticket_priority$</b>: ' . _('Ticket Priority') . '<br/>'
    . '<b>$ticket_status$</b>: ' . _('Ticket Status') . '<br/>'
    . '<b>$ticket_title$</b>: ' . _('Ticket Title') . '<br/>'
    . '<b>$ticket_description$</b>: ' . _('Ticket Description') . '<br/>'
    . '<b>$comment_creator$</b>: ' . _('Comment Creator') . '<br/>'
    . '<b>$comment_description$</b>: ' . _('Comment Description') . '<br/>'
    . '<u>' . _('Tags used in all emails') . ': </u><br/>'
    . '<b>$email$</b>: ' . _('email of the customer') . '<br/>'
    . '<b>$firstname$</b>: ' . _('firstname of the customer') . '<br/>'
    . '<b>$lastname$</b>: ' . _('lastname of the customer') . '<br/>'
    . '<b>$credit$</b>: ' . _('credit of the customer in the system currency') . '<br/>'
    . '<b>$creditcurrency$</b>: ' . _('credit of the customer in the own currency') . '<br/>'
    . '<b>$currency$</b>: ' . _('currency of the customer') . '<br/>'
    . '<b>$cardnumber$</b>: ' . _('card number of the customer') . '<br/>'
    . '<b>$password$</b>: ' . _('password of the customer') . '<br/>'
    . '<b>$login$</b>: ' . _('login of the customer') . '<br/>'
    . '<b>$credit_notification$</b>: ' . _('credit notification of the customer') . '<br/>'
    . '<b>$base_currency$</b>: ' . _('base currency of system') . '<br/>';

$HD_Form->AddListValue(_("ID"), "id");
$HD_Form->AddListMapping(_("Type"), "mailtype", $typemail_list);
$HD_Form->AddListValue(_("From email"), "fromemail");
$HD_Form->AddListValue(_("Subject"), "subject");

if ((int)$popup_select >= 1) {
    $HD_Form->AddListSelectActionButton("javascript:getMailData('|param|')", _("SELECT TEXT"));
} else {
    $HD_Form->FG_LIST_ADDING_BUTTON1 = true;
    $HD_Form->FG_LIST_ADDING_BUTTON_LINK1 = "A2B_entity_mailtemplate.php?form_action=ask-add";
    $HD_Form->FG_LIST_ADDING_BUTTON_ALT1 = $HD_Form->FG_LIST_ADDING_BUTTON_MSG1 = _("Add MAIL TEMPLATE");
    $HD_Form->FG_LIST_ADDING_BUTTON_IMG1 = get_image_path("email_add.png") ;

    $HD_Form->FG_ENABLE_ADD_BUTTON = true;
    $HD_Form->FG_ENABLE_EDIT_BUTTON = true;
    $HD_Form->FG_ENABLE_DELETE_BUTTON = true;

    $HD_Form->AddListActionButton(
        "A2B_entity_mailtemplate.php?form_action=ask-add&amp;from_id=|param|",
        _("Copy")
    );
}

$HD_Form->search_form_enabled = true;
$HD_Form->AddSearchSqlSelectInput(_("Select language"), "id_language", new Table("cc_iso639", ["code", "name"]),  "name");

$source = [];
if ($form_action === "ask-add" && !empty($from_id)) {
    $source = (new Table("cc_templatemail"))->getRow($HD_Form->DBHandle, ["id" => $from_id]);
}
$HD_Form->AddEditSelect(
    _("MAIL TYPE"),
    "mailtype",
    $typemail_list,
    $source["mailtype"] ?? ""
);

$HD_Form->AddEditSqlSelect(
    _("LANGUAGE"),
    "id_language",
    new Table("cc_iso639", ["code", "name"]),
    [],
    $source["id_language"] ?? "en",
    [],
    "",
    [],
    _("Insert the mail id language")
);


$HD_Form->AddEditElement(
    _("FROM EMAIL"),
    "fromemail",
    "",
    array_merge(["maxlength" => 60], $source ? ["value" => $source["fromemail"]] : []),
    [Validator::class, "email"],
    _("Insert the user fromemail")
);

$HD_Form->AddEditElement(
    _("FROM NAME"),
    "fromname",
    "",
    array_merge(["maxlength" => 60], $source ? ["value" => $source["fromname"]] : []),
    null,
    _("Insert the user fromname")
);

$HD_Form->AddEditElement(
    _("SUBJECT"),
    "subject",
    "",
    array_merge(["maxlength" => 80], $source ? ["value" => $source["subject"]] : []),
    null,
    _("Insert the user subject")
);

$HD_Form->AddEditTextarea(
    _("MESSAGE TEXT"),
    "messagetext",
    $message_text_bottom,
    ["rows" => 10],
    _("Insert the user subject"),
    $source["messagetext"] ?? ""
);

$HD_Form->FG_INTRO_TEXT_ADITION = _("Add a new mail template");

if ($source) {
    $HD_Form->FG_INTRO_TEXT_ADITION = _("Copy an existing mail template");
    $HD_Form->FG_ADD_PAGE_SAVE_BUTTON_TEXT = _("Save Copy");
}
