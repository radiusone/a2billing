#!/usr/bin/php -q
<?php

use A2billing\A2Billing;
use A2billing\A2bMailException;
use A2billing\Connection;
use A2billing\Mail;
use A2billing\Payments\Invoice;
use A2billing\ProcessHandler;
use Illuminate\Database\Query\Builder;

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
 *            a2billing_bill_diduse.php
 *
 *  Usage : this script will browse all the DID that are reserve and check if the customer need to pay for it
 *	bill them or warn them per email to know if they want to pay in order to keep their DIDs
 *
 *  ADD THIS SCRIPT IN A CRONTAB JOB
 *
    crontab -e
    0 2 * * * php /usr/local/a2billing/Cronjobs/a2billing_bill_diduse.php

    field	 allowed values
    -----	 --------------
    minute	 		0-59
    hour		 	0-23
    day of month	1-31
    month	 		1-12 (or names, see below)
    day of week	 	0-7 (0 or 7 is Sun, or use names)

****************************************************************************/

set_time_limit(0);
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));

include (dirname(__FILE__) . "/../common/lib/admin.defines.php");

// CHECK IF THE CRONT PROCESS IS ALREADY RUNNING
$pH= new ProcessHandler("/var/run/a2billing/a2billing_bill_diduse_pid.php");
if ($pH->isActive()) {
    die(); // Already running!
}

$verbose_level = 0;

$groupcard = 5000;

$A2B = new A2Billing();

$logfile_cront_billdid = $A2B->config['log-files']['cront_bill_diduse'] ?? "/tmp/a2billing_cront_billdid_log";

write_log($logfile_cront_billdid, basename(__FILE__) . ' line:' . __LINE__ . "[#### BATCH DIDUSE BEGIN ####]");

try {
    $db = Connection::getConnection();
} catch (Throwable) {
    $db = null;
}

if (!$db) {
    echo "[Cannot connect to the database]\n";
    write_log($logfile_cront_billdid, basename(__FILE__) . ' line:' . __LINE__ . "[Cannot connect to the database]");
    exit;
}

// CHECK THE CARD WITH DID'S

$result = $db->table("cc_did_use")
    ->select(["id_did", "reservationdate", "month_payed", "fixrate", "cc_card.id", "credit", "email", "did", "typepaid", "creditlimit", "reminded"])
    ->join("cc_card", "cc_card.id", "id_cc_card")
    ->join("cc_did", "id_did", "cc_did.id")
    ->where(
        fn (Builder $q) => $q->whereNull("releasedate")
            ->orWhere("releasedate", "<", "1984-01-01")
    )
    ->where("cc_did_use.activated", 1)
    ->where("cc_did.billingtype", "!=", 3)
    ->orderBy("cc_card.id")
    ->get();

if ($verbose_level >= 1) {
    print_r($result);
}

if (!is_array($result)) {
    if ($verbose_level >= 1) {
        echo "[No DID in use to run the DIDBilling recurring service]\n";
    }
    write_log($logfile_cront_billdid, basename(__FILE__) . ' line:' . __LINE__ . "[No DID in use to run the DIDBilling recurring service]");
    exit();
}

$oneday = 60 * 60 * 24;
// count day that user have to recharge his account
$daytopay = $A2B->config['global']['didbilling_daytopay'];
if ($verbose_level >= 1) {
    echo "daytopay=$daytopay \n";
}

// BROWSE THROUGH THE CARD TO APPLY THE DID USAGE
$last_idcard = null;
$new_card = true;
$now = new DateTimeImmutable();
foreach ($result as $mydids) {

    if ($last_idcard != $mydids["id"]) {
        $new_card = true;
        $last_idcard = $mydids["id"];
    } else {
        $new_card = false;
    }

    // mail variable for user notification
    $user_mail_adrr = '';
    $mail_user = false;

    if ($verbose_level >= 1) {
        print_r($mydids);
        echo "------>>>  ID DID = " . $mydids["id_did"] . " - MONTHLY RATE = " . $mydids["fixrate"] . "ID CARD = " . $mydids["id"] . " -BALANCE =" . $mydids["credit"] . " \n";
    }

    $day_remaining = 0;
    // $mydids["reservationdate"] -> reservationdate
    $diff_reservation_daytopay = (strtotime($mydids["reservationdate"])) - (intval($daytopay) * $oneday); // diff : reservationdate - daytopay : ie reserved 15Sept - day to pay 5 :> 10 days of diff
    // $timestamp_datetopay : 10 Septembre
    $timestamp_datetopay = mktime(date('H', $diff_reservation_daytopay), date("i", $diff_reservation_daytopay), date("s", $diff_reservation_daytopay), date("m", $diff_reservation_daytopay) + $mydids["month_payed"], date("d", $diff_reservation_daytopay), date("Y", $diff_reservation_daytopay));

    $day_remaining = time() - $timestamp_datetopay;

    if ($verbose_level >= 1) {
        echo "Time now :" . time() . " - timestamp_datetopay=$timestamp_datetopay\n";
        echo "day_remaining=$day_remaining <=" . (intval($daytopay) * $oneday) . "\n";
    }

    if ($day_remaining >= 0) {
        if ($day_remaining <= (intval($daytopay) * $oneday)) {

            //type of user prepaid
            if ($mydids['reminded'] == 0) {
                // THE USER HAVE TO PAY FOR HIS DID NOW

                if (($mydids['credit'] + $mydids['typepaid'] * $mydids['creditlimit']) >= $mydids['fixrate']) {

                    // USER HAVE ENOUGH CREDIT TO PAY FOR THE DID
                    $db->table("cc_card")->where("id", $mydids["id"])->decrement("credit", $mydids["fixrate"]);
                    $db->table("cc_did_use")->where("id_did", $mydids["id_did"])->increment("month_payed");
                    $db->table("cc_charge")->insert(["id_cc_card" => $mydids["id"], "amount" => $mydids["fixrate"], "description" => $mydids["did"], "chargetype" => 2, "id_cc_did" => $mydids["id_did"], "charged_status" => 1]);

                    $mail_user = true;
                    $mail = new Mail(Mail::$TYPE_DID_PAID,$mydids["id"] );
                    $mail -> replaceInEmail(Mail::$BALANCE_REMAINING_KEY,$mydids["credit"] - $mydids["fixrate"]);
                    $mail -> replaceInEmail(Mail::$DID_NUMBER_KEY,$mydids["did"]);
                    $mail -> replaceInEmail(Mail::$DID_COST_KEY,$mydids["fixrate"]);

                } else {
                    // USER DONT HAVE ENOUGH CREDIT TO PAY FOR THE DID - WE WILL WARN HIM

                    $reference = Invoice::generateReference();

                    //CREATE INVOICE If a new card then just an invoice item in the last invoice
                    if ($new_card) {
                        $date = date("Y-m-d h:i:s");
                        $card_id = $last_idcard;
                        $title = gettext("DID INVOICE REMINDER");
                        $description = "Your credit was not enough to pay yours DID numbers automatically.\n";
                        $description .= "You have " . date("d", $day_remaining) . " days to pay this invoice (REF: $reference ) or the DID will be automatically released \n\n";
                        $values = ["date" => $date, "id_card" => $card_id, "title" => $title, "reference" => $reference, "description" => $description, "status" => 1, "paid_status" => 0];
                        if ($verbose_level >= 1) {
                            echo "INSERT INVOICE : " . json_encode($values) . "\n";
                        }
                        $last_invoice = $db->table("cc_invoice")->insertGetId($values);

                        $description = "DID number (" . $mydids["did"] . ")";
                        $amount = $mydids["fixrate"];
                        $vat = 0;
                        $values = ["date" => $date, "id_invoice" => $last_invoice, "price" => $amount, "vat" => $vat, "description" => $description, "id_ext" => $mydids["id_did"], "type_ext" => "DID"];
                        if ($verbose_level >= 1) {
                            echo "INSERT INVOICE ITEM : " . json_encode($values) . "\n";
                        }
                        $db->table("cc_invoice_item")->insert($values);
                    }

                    $mail_user = true;
                    $mail = new Mail(Mail::$TYPE_DID_UNPAID, $mydids["id"]);
                    $mail -> replaceInEmail(Mail::$DAY_REMAINING_KEY,date("d", $day_remaining));
                    $mail -> replaceInEmail(Mail::$INVOICE_REF_KEY,$reference);
                    $mail -> replaceInEmail(Mail::$DID_NUMBER_KEY,$mydids["did"]);
                    $mail -> replaceInEmail(Mail::$DID_COST_KEY,$mydids["fixrate"]);
                    $mail -> replaceInEmail(Mail::$BALANCE_REMAINING_KEY, $mydids["credit"]);

                    //insert charge
                    $db->table("cc_charge")->insert(["id_cc_card" => $mydids["id"], "amount" => $mydids["fixrate"], "description" => $mydids["did"], "chargetype" => 2, "id_cc_did" => $mydids["id_did"], "invoiced_status" => 1]);
                    $db->table("cc_did_use")->where("id_did", $mydids["id_did"])->update(["reminded" => 1]);
                }
            }

        } else {
            // RELEASE THE DID
            $db->table("cc_did")->where("id", $mydids["id_did"])->update(["id_user" => 0, "reserved" => 0]);
            $db->table("cc_did_use")->where("id_did", $mydids["id_did"])->where("activated", 1)->update(["releasedate" => $now]);
            $db->table("cc_did_use")->insert(["activated" => 0, "id_did" => $mydids["id_did"]]);
            $db->table("cc_did_destination")->where("id_cc_did", $mydids["id_did"])->delete();

            $mail_user = true;
            $mail = new Mail(Mail::$TYPE_DID_RELEASED,$mydids["id"] );
            $mail -> replaceInEmail(Mail::$DID_NUMBER_KEY,$mydids["did"]);
            $mail -> replaceInEmail(Mail::$DID_COST_KEY,$mydids["fixrate"]);
            $mail -> replaceInEmail(Mail::$BALANCE_REMAINING_KEY, $mydids["credit"]);
        }
    }

    $user_mail_adrr = $mydids["email"];
    $user_card_id = $mydids["id"];

    if (!is_null($mail )&& $mail_user && strlen($user_mail_adrr) > 5) {
        try {
            $mail -> send($user_mail_adrr);
        } catch (A2bMailException $e) {
            if ($verbose_level >= 1)
                echo "[Sent mail failed : $e]";
            write_log($logfile_cront_billdid, basename(__FILE__) . ' line:' . __LINE__ . "[Sent mail failed : $e]");
        }
    }

}
write_log($logfile_cront_billdid, basename(__FILE__) . ' line:' . __LINE__ . "[Service DIDUSE finish]");

if ($verbose_level >= 1)
    echo "#### END RECURRING SERVICES \n";

write_log($logfile_cront_billdid, basename(__FILE__) . ' line:' . __LINE__ . "[#### BATCH DIDUSE  PROCESS END ####]");
