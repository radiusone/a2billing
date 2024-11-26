<?php

use A2billing\Admin;
use A2billing\Forms\FormHandler;
use A2billing\Mail;

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

/**
 * @var string $form_action
 * @var string $popup_select
 */


$HD_Form = new FormHandler("cc_templatemail", _("Mail Template"));

$HD_Form->FG_EDIT_BUTTON_LINK = "?form_action=ask-edit&id=";
$HD_Form->FG_DELETE_BUTTON_LINK = "?form_action=ask-delete&id=";

$typemail_list = [
    "reminder" => [_("Reminder"), Mail::$TYPE_REMINDER],
    "signup" => [_("Signup"),  Mail::$TYPE_SIGNUP],
    "forgetpassword" => [_("Forget password"), Mail::$TYPE_FORGETPASSWORD],
    "signupconfirmed" => [_("Signup confirmed"), Mail::$TYPE_SIGNUPCONFIRM],
    "payment" => [_("Payment Confirmation"), Mail::$TYPE_PAYMENT],
    "epaymentverify" => [_("EPayment Warning"), Mail::$TYPE_EPAYMENTVERIFY],
    "did_paid" => [_("Did Payment confirmation"), Mail::$TYPE_DID_PAID],
    "did_unpaid" => [_("Did not payment warning"), Mail::$TYPE_DID_UNPAID],
    "did_released" => [_("Did released warning"), Mail::$TYPE_DID_RELEASED],
    "template1" => [_("template1"), "template1"],
    "template2" => [_("template2"), "template2"],
    "template3" => [_("template3"), "template3"],
    "template4" => [_("template4"), "template4"],
    "template5" => [_("template5"), "template5"],
    "template6" => [_("template6"), "template6"],
    "template7" => [_("template7"), "template7"],
    "template8" => [_("template8"), "template8"],
    "template9" => [_("template9"), "template9"],
    "new_ticket" => [_("New Ticket"), Mail::$TYPE_TICKET_NEW],
    "modify_ticket" => [_("Modify Ticket"), Mail::$TYPE_TICKET_MODIFY],
    "invoice_to_pay" => [_("Invoice to Pay"), Mail::$TYPE_INVOICE_TO_PAY],
    "subscription_paid" => [_("Subscription Paid"), Mail::$TYPE_SUBSCRIPTION_PAID],
    "subscription_unpaid" => [_("Subscription Unpaid"), Mail::$TYPE_SUBSCRIPTION_UNPAID],
    "subscription_disable_card" => [_("Subscription Disable Card"), Mail::$TYPE_SUBSCRIPTION_DISABLE_CARD],
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

$HD_Form->AddViewElement(_("ID MAIL"), "id");
$HD_Form->AddViewElement(_("MAILTYPE"), "mailtype", false, 0, "", "list", $typemail_list);
$HD_Form->AddViewElement(_("FROMEMAIL"), "fromemail");
$HD_Form->AddViewElement(_("SUBJECT"), "subject");
$HD_Form->FieldViewElement ('id, mailtype, fromemail, subject');

if ((int)$popup_select >= 1) {
    $HD_Form->AddListActionButton(
        "javascript:sendValue('|col0|')",
        '<span style="color:red">&lt;' . _("SELECT TEXT") . '&gt;</span>',
        ""
    );
} else {
    $HD_Form->FG_LIST_ADDING_BUTTON1 = true;
    $HD_Form->FG_LIST_ADDING_BUTTON_LINK1 = "A2B_entity_mailtemplate.php?form_action=ask-add";
    $HD_Form->FG_LIST_ADDING_BUTTON_ALT1 = $HD_Form->FG_LIST_ADDING_BUTTON_MSG1 = _("Add MAIL TEMPLATE");
    $HD_Form->FG_LIST_ADDING_BUTTON_IMG1 = get_image_path("email_add.png") ;

    $HD_Form->FG_ENABLE_ADD_BUTTON = true;
    $HD_Form->FG_ENABLE_EDIT_BUTTON = true;
    $HD_Form->FG_ENABLE_DELETE_BUTTON = true;

    // not sure why this was specified but disabled?
//    $HD_Form->AddListActionButton(
//        "A2B_entity_translate.php?form_action=ask-add&amp;id=|param|&amp;mailtype=|col0|",
//        _("Translate"),
//        ""
//    );
}

$HD_Form->search_form_enabled = true;
$HD_Form->AddSearchSqlSelectInput(_("Select language"), "cc_iso639", "code,name", "", "name", "ASC", "id_language");

$HD_Form->AddEditSelect(_("MAIL TYPE"), "mailtype", $typemail_list);

$HD_Form->AddEditSqlSelect(
    _("LANGUAGE"),
    "id_language",
    "cc_iso639",
    "name,code",
    "",
    "",
    [],
    "%1",
    "",
    "",
    _("Insert the mail id language")
);


$HD_Form->AddEditElement(
    _("FROM EMAIL"),
    "fromemail",
    "",
    "size=30 maxlength=60",
    1,
    _("Insert the user fromemail")
);

$HD_Form->AddEditElement(
    _("FROM NAME"),
    "fromname",
    "",
    "size=30 maxlength=60",
    null,
    _("Insert the user fromname")
);

$HD_Form->AddEditElement(
    _("SUBJECT"),
    "subject",
    "",
    "size=30 maxlength=80",
    null,
    _("Insert the user subject")
);

$HD_Form->AddEditTextarea(
    _("MESSAGE TEXT"),
    "messagetext",
    $message_text_bottom,
    "cols='70' rows='10'",
    null,
    _("Insert the user subject")
);

$HD_Form->FG_INTRO_TEXT_ADITION = _("Add a new mail template");
$HD_Form->FG_LOCATION_AFTER_ADD = "?id=";
$HD_Form->FG_LOCATION_AFTER_EDIT = "?id=";
