<?php

use A2billing\A2Billing;
use A2billing\A2bMailException;
use A2billing\Connection;
use A2billing\Mail;
use A2billing\Payments\Invoice;
use A2billing\ProcessHandler;

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

/***************************************************************************
 *            a2billing_invoice_cront.php
 *
 *  Purpose: To generate invoices and for each user.
 *  Copyright  2009  User : Belaid Arezqui
 *  ADD THIS SCRIPT IN A CRONTAB JOB
 *
 *  The sample above will run the script every day of each month at 6AM
    crontab -e
    0 6 * * * php /usr/local/a2billing/Cronjobs/a2billing_invoice_cront.php

    field	 allowed values
    -----	 --------------
    minute	 0-59
    hour		 0-23
    day of month	 1-31
    month	 1-12 (or names, see below)
    day of week	 0-7 (0 or 7 is Sun, or use names)

****************************************************************************/

set_time_limit(0);
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));

include (dirname(__FILE__) . "/../common/lib/admin.defines.php");

// CHECK IF THE CRONT PROCESS IS ALREADY RUNNING
$pH= new ProcessHandler("/var/run/a2billing/a2billing_batch_billing_pid.php");
if ($pH->isActive()) {
    die(); // Already running!
}

//Flag to show the debuging information
$verbose_level = 0;

$groupcard = 5000;
$oneday = 24 * 60 * 60;

$A2B = new A2Billing();
$cron_logfile = $A2B->config['log-files']['cront_invoice'] ?? "/tmp/a2billing_cront_invoice_log";

write_log ($cron_logfile, basename(__FILE__) . ' line:' . __LINE__ . "[#### CRONT BILLING BEGIN ####]");

try {
    $db = Connection::getConnection();
} catch (Throwable) {
    $db = null;
}

if (!$db) {
    echo "[Cannot connect to the database]\n";
    write_log ($cron_logfile, basename(__FILE__) . ' line:' . __LINE__ . "[Cannot connect to the database]");
    exit(1);
}

// CHECK COUNT OF CARD ON WHICH APPLY THE SERVICE
$nb_card = $db->table("cc_card")->count();
$nbpagemax = (ceil($nb_card / $groupcard));

if ($verbose_level >= 1) {
    echo "===> NB_CARD : $nb_card - NBPAGEMAX:$nbpagemax\n";
}

if ($nb_card <= 0) {
    if ($verbose_level >= 1) {
        echo "[No card to run the Invoice Billing Service]\n";
    }
    write_log ($cron_logfile, basename(__FILE__) . ' line:' . __LINE__ . "[No card to run the Invoice Billing service]");
    exit();
}

if ($verbose_level >= 1) {
    echo "[Invoice Billing Service analyze cards on which to apply billing]";
}
write_log ($cron_logfile, basename(__FILE__) . ' line:' . __LINE__ . "[Invoice Billing Service analyze cards on which to apply billing]");

$now = new DateTimeImmutable();
for ($page = 0; $page < $nbpagemax; $page++) {
    if ($verbose_level >= 1) {
        echo "$page <= $nbpagemax \n";
    }
    $resmax = $db->table("cc_card")
        ->select("id", "vat", "invoiceday", "typepaid", "credit")
        ->limit($page)->offset($page * $groupcard)
        ->get();

    if (count($resmax)) {
        $numrow = count($resmax);
        if ($verbose_level >= 2) {
            print_r($resmax[0]);
        }
    } else {
        if ($verbose_level >= 1) {
            echo "\n[No card to run the Invoice Billing Service]\n";
        }
        write_log ($cron_logfile, basename(__FILE__) . ' line:' . __LINE__ . "[No card to run the Invoice Billing service]");
        exit();
    }

    foreach ($resmax as $Customer) {
        $invoiceday = (is_numeric($Customer['invoiceday']) && $Customer['invoiceday'] >= 1 && $Customer['invoiceday'] <= 28) ? $Customer['invoiceday'] : 1;
        if ($verbose_level >= 1) {
            echo "\n Invoiceday = $invoiceday  -  Invoiceday db = " . $Customer['invoiceday'];
        }

        // the value of invoiceday is between 1..28, dont make sense to bill customer on 29, 30, 31
        if ($now->format("j") != $invoiceday) {
            if ($verbose_level >= 1) {
                echo "\n We dont create an invoice today for this customer : " . $Customer['invoiceday'];
            }
            continue;
        }

        //find the last billing
        $card_id = $Customer['id'];
        $date_now = date("Y-m-d");
        if (empty ($Customer['vat']) || !is_numeric($Customer['vat'])) {
            $vat = 0;
        } else {
            $vat = $Customer['vat'];
        }

        // FIND THE LAST BILLING
        $billing_table = $db->table('cc_billing_customer')
            ->select('id', 'date', 'id_invoice')
            ->where("id_card", $card_id)
            ->orderBy("date", "desc");

        $call_table = $db->table('cc_call')
            ->selectRaw('COALESCE(SUM(sessionbill), 0) AS sessionbill')
            ->where("card_id", $card_id);

        $table_charge = $db->table("cc_charge")
            ->where("id_cc_card", $card_id);

        $desc_billing = "";
        $desc_billing_postpaid = "";
        $start_date = null;
        $lastbilling_invoice = null;
        $result = $billing_table->first();
        if ($result) {
            if ($verbose_level >= 1) {
                echo "\n Find the last billing -> Id card : " . $result["id"];
            }

            $call_table->where("stoptime", ">=", $result["date"]);
            $table_charge->where("creationdate", ">=", $result["date"]);
            $desc_billing = "Calls cost between the " . $result["date"] . " and " . $date_now;
            $desc_billing_postpaid = "Amount for period between the " .date("Y-m-d", strtotime($result["date"])). " and " . $date_now;
            $start_date = $result["date"];
            $lastbilling_invoice = $result["id_invoice"];
        } else {
            $desc_billing = "Calls cost before the " . $date_now;
            $desc_billing_postpaid = "Amount for period before the " . $date_now;
        }

        // RETRIEVE THE LAST POSTPAID AMOUNT -SUM OF ALL INVOICE ITEMS UNPAID FOR A POSTPAID USER
        $lastpostpaid_amount = $db->table("cc_billing_customer")
            ->selectRaw("SUM(items.total_price) as total")
            ->leftJoin("cc_invoice", "cc_billing_customer.id_invoice", "cc_invoice.id")
            ->joinSub(
                $db->table("cc_invoice_item")->select("id_invoice")->selectRaw("SUM(price) AS total_price")->where("type_ext", "POSTPAID")->groupBy("id_invoice"),
                "items",
                "items.id_invoice",
                "cc_invoice.id"
            )
            ->where(["cc_billing_customer.id_card" => $card_id, "cc_invoice.paid_status" => 0])
            ->value("total") ?: 0;

        // INSERT CUSTOMER BILLING
        $values = ["id_card" => $card_id];
        if (!empty ($start_date)) {
            $values["start_date"] = $start_date;
        }
        $id_billing = $db->table("cc_billing_customer")->insertGetId($values);
        if ($verbose_level >= 2) {
            echo "\n Add billing -> Id card : " . json_encode($values);
        }

        $amount_calls = $call_table->where("stoptime", "<", $now)
            ->value("sessionbill");

        // COMMON BEHAVIOUR FOR PREPAID AND POSTPAID -> GENERATE A RECEIPT FOR THE CALLS OF THE LAST PERIOD
        if (!is_null($amount_calls)) {
            $amount_calls = ceil($amount_calls * 100) / 100;
            /// create receipt
            $title = gettext("SUMMARY OF CALLS");
            $description = gettext("Summary of the calls charged since the last billing");
            $values = ["id_card" => $card_id, "title" => $title, "description" => $description, "status" => 1];
            $id_receipt = $db->table("cc_receipt")->insertGetId($values);
            if ($verbose_level >= 2) {
                echo "\n Add Receipt for the call of the last period :> " . json_encode($values);
            }

            if (!empty ($id_receipt) && is_numeric($id_receipt)) {
                $description = $desc_billing;
                $values = ["id_receipt" => $id_receipt, "price" => $amount_calls, "description" => $description, "id_ext" => $id_billing, "type_ext" => "CALLS"];
                $db->table("cc_receipt_item")->insert($values);
                if ($verbose_level >= 2)
                    echo "\n Add Receipt Items for the call of the last period :> " . json_encode($values);
            }
        }

        // GENERATE RECEIPT FOR CHARGE ALREADY PAID
        $result = $table_charge->clone()->where("creationdate", "<", $now)
            ->where("charged_status", 1)
            ->get();
        if (count($result)) {
            $title = gettext("SUMMARY OF CHARGE");
            $description = gettext("Summary of the paid charges since the last billing.");
            $values = ["id_card" => $card_id, "title" => $title, "description" => $description, "status" => 1];
            $id_receipt = $db->table("cc_receipt")->insertGetId($values);
            if ($verbose_level >= 2) {
                echo "\n Add Receipt for the charges already paid :> " . json_encode($values);
            }

            if (!empty ($id_receipt) && is_numeric($id_receipt)) {
                foreach ($result as $charge) {
                    $description = gettext("CHARGE :") . $charge['description'];
                    $amount = $charge['amount'];
                    $values = ["date" => $charge["creationdate"], "id_receipt" => $id_receipt, "price" => $amount, "description" => $description, "id_ext" => $charge["id"], "type_ext" => "CHARGE"];
                    $db->table("cc_receipt_item")->insert($values);
                    if ($verbose_level >= 2) {
                        echo "\n Add Receipt Items for the charges already paid :> " . json_encode($values);
                    }
                }
            }
        }
        $total =0;
        $total_vat =0;
        // GENERATE INVOICE FOR CHARGE NOT YET CHARGED
        $result = $table_charge->where(["charged_status" => 0, "invoiced_status" => 0])->get();
        $last_invoice = null;
        if (count($result)) {
            $reference = Invoice::generateReference();
            $title = gettext("BILLING");
            $description = gettext("Invoice for the unpaid charges since the last billing.") . " " . $desc_billing_postpaid;
            $invoice_title = $title;
            $invoice_reference =$reference;
            $invoice_description = $description;
            $values = ["id_card" => $card_id, "title" => $title, "reference" => $reference, "description" => $description, "status" => 1, "paid_status" => 0];
            $id_invoice = $db->table("cc_invoice")->insertGetId($values);
            if ($verbose_level >= 2) {
                echo "\n Add Invoice for the unpaid charges :> " . json_encode($values);
            }

            if (!empty ($id_invoice) && is_numeric($id_invoice)) {
                $last_invoice = $id_invoice;
                foreach ($result as $charge) {
                    $description = gettext("CHARGE :") . $charge['description'];
                    $amount = $charge['amount'];
                    $total = $total + $amount;
                    $total_vat =$total_vat + round($amount *(1+($vat/100)),2);
                    $values = ["date" => $charge["creationdate"], "id_invoice" => $id_invoice, "price" => $amount, "vat" => $vat, "description" => $description, "id_ext" => $charge["id"], "type_ext" => "CHARGE"];
                    $db->table("cc_invoice_item")->insert($values);
                    if ($verbose_level >= 2) {
                        echo "\n Add Invoice Items for the unpaid charges :> " . json_encode($values);
                    }
                }
            }
        }

        // POSTPAID BILLING
        if ($Customer['typepaid'] == 1 && is_numeric($Customer['credit']) && ($Customer['credit']+$lastpostpaid_amount) < 0) {
            // GENERATE AN INVOICE TO COMPLETE THE BALANCE
            if (!empty($last_invoice)) {
                $id_invoice = $last_invoice;
            } else {
                $reference = Invoice::generateReference();
                $title = gettext("BILLING");
                $description = gettext("Invoice for POSTPAID");
                $invoice_title = $title;
                $invoice_reference =$reference;
                $invoice_description = $description;
                $values = ["id_card" => $card_id, "title" => $title, "reference" => $reference, "description" => $description, "status" => 1, "paid_status" => 0];
                $id_invoice = $db->table("cc_invoice")->insertGetId($values);
                if ($verbose_level >= 2) {
                    echo "\n Add Invoice :> " . json_encode($values);
                }
            }
            if (!empty ($id_invoice) && is_numeric($id_invoice)) {
                $last_invoice = $id_invoice;
                $description = $desc_billing_postpaid;
                $amount = abs($Customer['credit']+$lastpostpaid_amount);
                $total = $total + $amount;
                $total_vat =$total_vat + round($amount *(1+($vat/100)),2);
                $values = ["id_invoice" => $id_invoice, "price" => $amount, "vat" => $vat, "description" => $description, "id_ext" => $id_billing, "type_ext" => "POSTPAID"];
                $db->table("cc_invoice_item")->insert($values);
                if ($verbose_level >= 2) {
                    echo "\n Add Invoice Item :> " . json_encode($values);
                }
            }
        }

        if (!empty($last_invoice)) {
            $billing_table->where("id", $id_billing)->update(["id_invoice" => $last_invoice]);
        }

        // Send a mail for invoice to pay
        if (!empty($last_invoice)) {
            $total = round($total,2);
            try {
                $mail = new Mail(Mail::$TYPE_INVOICE_TO_PAY, $card_id);
                $mail->replaceInEmail(Mail::$INVOICE_REFERENCE_KEY, $invoice_reference);
                $mail->replaceInEmail(Mail::$INVOICE_TITLE_KEY, $invoice_title);
                $mail->replaceInEmail(Mail::$INVOICE_DESCRIPTION_KEY, $invoice_description);
                $mail->replaceInEmail(Mail::$INVOICE_TOTAL_KEY, $total);
                $mail->replaceInEmail(Mail::$INVOICE_TOTAL_VAT_KEY, $total_vat);
                $mail->send();
                if ($verbose_level >= 2)
                    echo "\n Email sent for invoice to pay, card id :> ".$card_id;
            } catch (A2bMailException $e) {
                $error_msg = $e->getMessage();
                if ($verbose_level >= 1)
                    echo "Sent mail error : ".$error_msg;
            }
        }

        if ($verbose_level >= 2)
            echo "\n Go to next Customer";

    } // END foreach($resmax as $Customer)
}

if ($verbose_level >= 1) {
    echo "------- CRONT BILLING END ------- \n";
}

write_log($cron_logfile, basename(__FILE__) . ' line:' . __LINE__ . "------- CRONT BILLING END -------");
