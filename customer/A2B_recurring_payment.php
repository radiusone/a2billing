<?php

use A2billing\Payments\Invoice;
use A2billing\Table;

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of A2Billing (http://www.a2billing.net/)
 *
 * A2Billing, Commercial Open Source Telecom Billing platform,
 * powered by Star2billing S.L. <http://www.star2billing.com/>
 *
 * @copyright   Copyright © 2004-2015 - Star2billing S.L.
 * @copyright   Copyright © 2022-2025 RadiusOne Inc.
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

require_once __DIR__ . "/../common/lib/customer.defines.php";

getpost_ifset (array('id', 'key', 'payment_gross','payment_status', 'txn_type','payer_email'));

$log = "\nREQUEST RECURRING PAYMENT:\n";
$log .= "GET:\n";
foreach ($_GET as $vkey => $Value) {
    $log .= $vkey . " = " . $Value . "\n";
}
$log .= "POST:\n";
foreach ($_POST as $vkey => $Value) {
    $log .= $vkey . " = " . $Value . "\n";
}

$epayment_logfile = $A2B->config['log-files']['epayment'] ?? "/tmp/a2billing_epayment_log";

write_log($epayment_logfile, basename(__FILE__) . ' line:' . __LINE__ . "$log");

if ($payment_status != "Completed" || $txn_type != "subscr_payment") {
    die();
}

$req = 'cmd=_notify-validate';
foreach ($_POST as $vkey => $Value) {
    $req .= "&" . $vkey . "=" . urlencode($Value);
}

$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";

for ($i = 1; $i <= 3; $i++) {
    write_log($epayment_logfile, basename(__FILE__) . ' line:' . __LINE__ . "-OPENDING HTTP CONNECTION TO " . PAYPAL_VERIFY_URL);
    $fp = fsockopen(PAYPAL_VERIFY_URL, 443, $errno, $errstr, 30);
    if ($fp) {
        break;
    } else {
        write_log($epayment_logfile, basename(__FILE__) . ' line:' . __LINE__ . " -Try#" . $i . " Failed to open HTTP Connection : " . $errstr . ". Error Code: " . $errno);
        sleep(3);
    }
}

if (!$fp) {
    write_log($epayment_logfile, basename(__FILE__) . ' line:' . __LINE__ . "-Failed to open HTTP Connection: " . $errstr . ". Error Code: " . $errno);
    exit ();
} else {
    fputs($fp, $header . $req);
    $flag_ver = 0;
    while (!feof($fp)) {
        $res = fgets($fp, 1024);
        $gather_res .= $res;
        if (strcmp($res, "VERIFIED") == 0) {
            write_log($epayment_logfile, basename(__FILE__) . ' line:' . __LINE__ . "-PAYPAL Transaction Verification Status: Verified ");
            $flag_ver = 1;
        }
    }
    write_log("/var/log/a2billing/test.log", "\nREQUEST:\n$gather_res");
    if ($flag_ver == 0) {
        write_log($epayment_logfile, basename(__FILE__) . ' line:' . __LINE__ . "-PAYPAL Transaction Verification Status: Failed \nreq=$req\n$gather_res");
        $security_verify = false;
    }
}
fclose($fp);
$DBHandle = DbConnect();
$table_card = new Table("cc_card", "username,useralias,creationdate,vat,firstname,lastname");
$card_clause = ["id" => $id];
$result = $table_card->getRow($DBHandle, $card_clause);

if (!$result) {
    write_log($epayment_logfile, basename(__FILE__) . ' line:' . __LINE__ . "-PAYPAL Reccurring Payment Failed : card id( $id ) not found");
    die();
}

$card = $result;
$username = $card['username'];
$creationdate = strtotime($card['creationdate']);
$useralias = $card['useralias'];
$vat = $card['vat'];
$firstname = $card['firstname'];
$lastname = $card['lastname'];
$email = $card['email'];
$newkey = securitykey(EPAYMENT_TRANSACTION_KEY, $username . "^" . $id . "^" . $useralias . "^" . $creationdate);

if ($newkey == $key) {
    write_log($epayment_logfile, basename(__FILE__) . ' line:' . __LINE__ . "----------- Transaction Key Verified ------------");
} else {
    write_log($epayment_logfile, basename(__FILE__) . ' line:' . __LINE__ . "----NEW KEY =" . $newkey . " OLD KEY= " . $key . " ------- Transaction Key Verification Failed:" . $transaction_data[0][8] . "^" . $transactionID . "^" . $transaction_data[0][2] . "^" . $transaction_data[0][1] . " ------------\n");
    exit ();
}

$amount_paid = $payment_gross;
$amount_without_vat = $amount_paid / (1 + $vat / 100);
$nowDate = date("Y-m-d H:i:s");

$Query = "INSERT INTO cc_payments ( customers_id, customers_name, customers_email_address, item_name, payment_method,cc_number,orders_status, " .
" last_modified, date_purchased, orders_date_finished, orders_amount, currency, currency_value) values (" .
" '" . $id . "', '" . $firstname . " " . $lastname . "', '" . $email . "', 'RECURRING PAYMENT', 'PAYPAL' ," .
" '$payer_email','2', '" . $nowDate . "', '" . $nowDate . "', '" . $nowDate . "',  " . $amount_paid . ",  '" . BASE_CURRENCY . "', '1' )";
$result = $DBHandle->Execute($Query);

$instance_table = new Table("cc_card", "username, id");
$param_update = ["credit" => ["credit + ?", $amount_without_vat]];
$FG_EDITION_CLAUSE = ["id" => $id];
$instance_table->updateRow($DBHandle, $param_update, $FG_EDITION_CLAUSE);
write_log($epayment_logfile, basename(__FILE__) . ' line:' . __LINE__ . "-Recurring payment" . " Update_table cc_card : " . json_encode($param_update) . " - CLAUSE : " . json_encode($FG_EDITION_CLAUSE));

$instance_sub_table = new Table("cc_logrefill");
$values = ["date" => $nowDate, "credit" => $amount_without_vat, "card_id" => $id, "description" => _("Reccurring payment : automated refill")];
$instance_sub_table->addRow($DBHandle, $values, "id", $id_logrefill);
write_log($epayment_logfile, basename(__FILE__) . ' line:' . __LINE__ . "-Recurring payment" . " Add_table cc_logrefill : " . json_encode($values));

$instance_sub_table = new Table("cc_logpayment");
$values = ["date" => $nowDate, "payment" => $amount_paid, "card_id" => $id, "id_logrefill" => $id_logrefill, "description" => _("Reccurring payment : automated refill")];
$instance_sub_table->addRow($DBHandle, $values, "id", $id_payment);
write_log($epayment_logfile, basename(__FILE__) . ' line:' . __LINE__ . "-Recurring payment" . " Add_table cc_logpayment : " . json_encode($values));

//ADD an INVOICE
$reference = Invoice::generateReference();
$date = $nowDate;
$card_id = $id;
$title = gettext("CUSTOMER REFILL");
$description = gettext("Invoice for refill");
$instance_table = new Table("cc_invoice");
$values = ["date" => $date, "id_card" => $card_id, "title" => $title, "reference" => $reference, "description" => $description, "status" => 1, "paid_status" => 1];
$instance_table->addRow($DBHandle, $values, "id", $id_invoice);

//load vat of this card
if (!empty ($id_invoice) && is_numeric($id_invoice)) {
    $amount = $amount_without_vat;
    $description = gettext("Automated Refill : recurring payment");
    $instance_table = new Table("cc_invoice_item");
    $values = ["date" => $date, "id_invoice" => $id_invoice, "price" => $amount, "vat" => $VAT, "description" => $description];
    $instance_table->addRow($DBHandle, $values);
}

//link payment to this invoice
$table_payment_invoice = new Table("cc_invoice_payment");
$values = compact("id_invoice", $id_payment);
$table_payment_invoice->addRow($DBHandle, $values);

//END INVOICE
//Agent commision
// test if this card have a agent
$table_transaction = new Table();
$result_agent = $table_transaction->SQLExec($DBHandle, "SELECT cc_card_group.id_agent FROM cc_card LEFT JOIN cc_card_group ON cc_card_group.id = cc_card.id_group WHERE cc_card.id = $id");

if (is_array($result_agent) && !is_null($result_agent[0]['id_agent']) && $result_agent[0]['id_agent'] > 0) {
    //test if the agent exist and get its commission
    $id_agent = $result_agent[0]['id_agent'];
    $agent_table = new Table("cc_agent", "commission");
    $agent_clause = ["id" => $id_agent];
    $commission_amt = $agent_table->getValue($DBHandle, $agent_clause) ?? 0;

    if ($commission_amt > 0) {
        $commission = ceil(($amount_paid * ($commission_amt) / 100) * 100) / 100;
        $description_commission = gettext("AUTOMATICALY GENERATED COMMISSION!");
        $description_commission .= "\nID CARD : " . $id;
        $description_commission .= "\nID PAYMENT : " . $id_payment;
        $description_commission .= "\nPAYMENT AMOUNT: " . $amount_paid;
        $description_commission .= "\nCOMMISSION APPLIED: " . $commission_amt;
        $commission_table = new Table("cc_agent_commission");
        $values = ["id_payment" => $id_payment, "id_card" => $id, "amount" => $commission, "description" => $description_commission, "id_agent" => $id_agent];
        $commission_table->addRow($DBHandle, $values, "id", $id_commission);
    }
}
