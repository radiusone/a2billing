<?php

namespace A2billing;

use PHPMailer\PHPMailer\Exception;

/**
 * This file is part of A2Billing (http://www.a2billing.net/)
 *
 * A2Billing, Commercial Open Source Telecom Billing platform,
 * powered by Star2billing S.L. <http://www.star2billing.com/>
 *
 * @copyright   Copyright © 2004-2015 - Star2billing S.L.
 * @copyright   Copyright © 2022-2025 RadiusOne Inc.
 * @author      Belaid Rachid <rachid.belaid@gmail.com>
 * @license     http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @package     A2Billing
 * @contributor Belaid Arezqui <areski@gmail.com>
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

class Mail
{
    private $id_card;
    private $message = '';
    private $title = '';
    private $from_email = '';
    private $from_name = '';
    private $to_email = '';

    //mail type
    public static string $TYPE_PAYMENT = 'payment';
    public static string $TYPE_REMINDER = 'reminder';
    public static string $TYPE_SIGNUP = 'signup';
    public static string $TYPE_FORGETPASSWORD = 'forgetpassword';
    public static string $TYPE_SIGNUPCONFIRM = 'signupconfirmed';
    public static string $TYPE_EPAYMENTVERIFY = 'epaymentverify';
    public static string $TYPE_REMINDERCALL = 'reminder';
    public static string $TYPE_SUBSCRIPTION_PAID = 'subscription_paid';
    public static string $TYPE_SUBSCRIPTION_UNPAID = 'subscription_unpaid';
    public static string $TYPE_SUBSCRIPTION_DISABLE_CARD = 'subscription_disable_card';

    public static string $TYPE_DID_PAID = 'did_paid';
    public static string $TYPE_DID_UNPAID = 'did_unpaid';
    public static string $TYPE_DID_RELEASED = 'did_released';
    public static string $TYPE_TICKET_NEW = 'new_ticket';
    public static string $TYPE_TICKET_MODIFY = 'modify_ticket';
    public static string $TYPE_INVOICE_TO_PAY = 'invoice_to_pay';

    //Used by mail type = invoice_to_pay
    public static string $INVOICE_TITLE_KEY = '$invoice_title$';
    public static string $INVOICE_REFERENCE_KEY = '$invoice_reference$';
    public static string $INVOICE_DESCRIPTION_KEY = '$invoice_description$';
    public static string $INVOICE_TOTAL_KEY = '$invoice_total$';
    public static string $INVOICE_TOTAL_VAT_KEY = '$invoice_total_vat$';

    //Used by mail type = new_ticket AND modify_ticket
    public static string $TICKET_NUMBER_KEY = '$ticket_id$';
    public static string $TICKET_OWNER_KEY = '$ticket_owner$';
    public static string $TICKET_PRIORITY_KEY = '$ticket_priority$';
    public static string $TICKET_STATUS_KEY = '$ticket_status$';
    public static string $TICKET_TITLE_KEY = '$ticket_title$';
    public static string $TICKET_DESCRIPTION_KEY = '$ticket_description$';

    //Used by mail type = modify_ticket
    public static string $TICKET_COMMENT_CREATOR_KEY = '$comment_creator$';
    public static string $TICKET_COMMENT_DESCRIPTION_KEY = '$comment_description$';

    //Used by mail type = did_paid
    public static string $BALANCE_REMAINING_KEY = '$balance_remaining$';

    //Used by mail type = subscription_paid OR subscription_unpaid
    public static string $SUBSCRIPTION_LABEL = '$subscription_label$';
    public static string $SUBSCRIPTION_ID = '$subscription_id$';
    public static string $SUBSCRIPTION_FEE = '$subscription_fee$';

    //Used by mail type = did_paid OR did_unpaid OR did_released
    public static string $DID_NUMBER_KEY = '$did$';
    public static string $DID_COST_KEY = '$did_cost$';

    //Used by mail type = did_unpaid  & subscription_unpaid
    public static string $DAY_REMAINING_KEY = '$days_remaining$';
    public static string $INVOICE_REF_KEY = '$invoice_ref$';

    //Used by mail type = epaymentverify
    public static string $TIME_KEY = '$time$';
    public static string $PAYMENTGATEWAY_KEY = '$paymentgateway$';

    //Used by mail type = payment
    public static string $ITEM_NAME_KEY = '$itemName$';
    public static string $ITEM_ID_KEY = '$itemID$';
    public static string $PAYMENT_METHOD_KEY = '$paymentMethod$';
    public static string $PAYMENT_STATUS_KEY = '$paymentStatus$';

    //used by type = payment and type = epaymentverify
    public static string $ITEM_AMOUNT_KEY = '$itemAmount$';

    //used in all mail
    public static string $CUSTOMER_EMAIL_KEY = '$email$';
    public static string $CUSTOMER_FIRSTNAME_KEY = '$firstname$';
    public static string $CUSTOMER_LASTNAME_KEY = '$lastname$';
    public static string $CUSTOMER_CREDIT_BASE_CURRENCY_KEY = '$credit$';
    public static string $CUSTOMER_CREDIT_IN_OWN_CURRENCY_KEY = '$creditcurrency$';
    public static string $CUSTOMER_CURRENCY = '$currency$';
    public static string $CUSTOMER_CARDNUMBER_KEY = '$cardnumber$';
    public static string $CUSTOMER_PASSWORD_KEY = '$password$';
    public static string $CUSTOMER_LOGIN = '$login$';
    public static string $CUSTOMER_LOGINKEY = '$loginkey$';
    public static string $CUSTOMER_CREDIT_NOTIFICATION = '$credit_notification$';

    //used in all mail
    public static string $SYSTEM_CURRENCY = '$base_currency$';

    /**
     * @throws A2bMailException
     */
    public function __construct($type, $id_card = null, $lg = null, $msg = null, $title = null)
    {
        if (!empty($type)) {
            $tmpl_table = new Table("cc_templatemail", "*");
            $tmpl_clause = ["mailtype" => $type];
            $order = null;
            $order_field = "";
            if (!empty ($lg)) {
                $tmpl_clause["id_language"] = ["IN", [$lg, 'en']];
                $order_field = 'id_language';
                if (strcasecmp($lg, 'en') < 0) {
                    $order = 'ASC';
                } else {
                    $order = 'DESC';
                }
            } elseif (is_numeric($id_card)) {
                $card_table = new Table("cc_card", ["*", "CASE WHEN typepaid = 1 AND creditlimit IS NOT NULL THEN credit + creditlimit ELSE credit END AS real_credit"]);
                $card_clause = ["id" => $id_card];
                $result_card = $card_table->getRow($card_clause);
                if ($result_card) {
                    $card = $result_card;
                    $language = $card['language'];
                }
                if (!empty ($language)) {
                    $tmpl_clause["id_language"] = ["IN", [$lg, 'en']];
                    $order_field = 'id_language';
                    if (strcasecmp($language, 'en') < 0) {
                        $order = 'ASC';
                    } else {
                        $order = 'DESC';
                    }
                }
            }
            $result_tmpl = $tmpl_table->getRow($tmpl_clause, [$order_field], $order);
            if ($result_tmpl) {
                $mail_tmpl = $result_tmpl;
                $this->message = $mail_tmpl['messagetext'];
                $this->title = $mail_tmpl['subject'];
                $this->from_email = $mail_tmpl['fromemail'];
                $this->from_name = $mail_tmpl['fromname'];
            } else {
                throw new A2bMailException("Template Type '$type' cannot be found into the database!");
            }
        } elseif (!empty($msg) || !empty($title)) {
            $this->message = $msg;
            $this->title = $title;
        } else {
            throw new A2bMailException("Error : no Type defined and neither message or subject is provided!");
        }

        if (!empty($this->message) || !empty($this->title)) {
            if (is_numeric($id_card)) {
                $this->id_card = $id_card;
                if (!isset($card)) {
                    $card_table = new Table("cc_card", ["*", "CASE WHEN typepaid = 1 AND creditlimit IS NOT NULL THEN credit + creditlimit ELSE credit END AS real_credit"]);
                    $card_clause = ["id" => $id_card];
                    $result_card = $card_table->getRow($card_clause);
                    if ($result_card) {
                        $card = $result_card;
                    } else {
                        return;
                    }
                }
                $credit = $card['real_credit'];
                $credit = round($credit, 3);
                $currency = $card['currency'];
                $currencies_list = get_currencies();
                if (!isset ($currencies_list[strtoupper($currency)]["value"]) || !is_numeric($currencies_list[strtoupper($currency)]["value"])) {
                    $mycur = 1;
                } else {
                    $mycur = $currencies_list[strtoupper($currency)]["value"];
                }
                $credit_currency = $credit / $mycur;
                $credit_currency = round($credit_currency, 3);
                $this->to_email = $card['email'];
                $this->replaceInEmail(self::$CUSTOMER_CARDNUMBER_KEY, $card['username']);
                $this->replaceInEmail(self::$CUSTOMER_EMAIL_KEY, $card['email']);
                $this->replaceInEmail(self::$CUSTOMER_FIRSTNAME_KEY, $card['firstname']);
                $this->replaceInEmail(self::$CUSTOMER_LASTNAME_KEY, $card['lastname']);
                $this->replaceInEmail(self::$CUSTOMER_LOGIN, $card['useralias']);
                $this->replaceInEmail(self::$CUSTOMER_LOGINKEY, $card['loginkey']);
                $this->replaceInEmail(self::$CUSTOMER_PASSWORD_KEY, $card['uipass']);
                $this->replaceInEmail(self::$CUSTOMER_CREDIT_IN_OWN_CURRENCY_KEY, $credit_currency);
                $this->replaceInEmail(self::$CUSTOMER_CREDIT_BASE_CURRENCY_KEY, $credit);
                $this->replaceInEmail(self::$CUSTOMER_CURRENCY, $currency);
                $this->replaceInEmail(self::$CUSTOMER_CREDIT_NOTIFICATION, $card['credit_notification']);

            }
            $this->replaceInEmail(self::$SYSTEM_CURRENCY, BASE_CURRENCY);
        }
    }

    public function replaceInEmail($key, $val)
    {
        $this->message = str_replace($key, $val, $this->message);
        $this->title = str_replace($key, $val, $this->title);
    }

    public function getIdCard()
    {
        return $this->id_card;
    }

    public function getFromEmail()
    {
        return $this->from_email;
    }

    public function getToEmail()
    {
        return $this->to_email;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function AddToMessage($msg)
    {
        $this->message = $this->message . $msg;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getFromName()
    {
        return $this->from_name;
    }

    public function setFromEmail($from_email)
    {
        $this->from_email = $from_email;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function setToEmail($to_email)
    {
        $this->to_email = $to_email;
    }

    public function setFromName($from_name)
    {
        $this->from_name = $from_name;
    }

    /**
     * @throws A2bMailException
     */
    public function send($to_email = null)
    {
        if (!empty ($to_email)) {
            $this->to_email = $to_email;
        }
        try {
            a2b_mail($this->to_email, $this->title, $this->message, $this->from_email, $this->from_name);
        } catch (Exception $e) {
            throw new A2bMailException("Error sent mail : ".$e->getMessage()."\n");
        }
    }
}
