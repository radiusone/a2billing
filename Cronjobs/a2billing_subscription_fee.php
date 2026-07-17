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
 *            a2billing_subscription_fee.php
 *
 *  Purpose: manage the monthly services subscription
 *  Fri Feb 27 14:17:10 2007
 *  Copyright  2007  User : Areski
 *  ADD THIS SCRIPT IN A CRONTAB JOB
 *
    crontab -e
    0 6 * * * php /usr/local/a2billing/Cronjobs/a2billing_subscription_fee.php

    field	 allowed values
    -----	 --------------
    minute	 		0-59
    hour		 	0-23
    day of month	1-31
    month	 		1-12 (or names, see below)
    day of week	 	0-7 (0 or 7 is Sun, or use names)

    The sample above will run the script every 21 of each month at 10AM

****************************************************************************/

set_time_limit(0);
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));

include (dirname(__FILE__) . "/../common/lib/admin.defines.php");

// CHECK IF THE CRONT PROCESS IS ALREADY RUNNING
$pH= new ProcessHandler("/var/run/a2billing/a2billing_subscription_fee_pid.php");
if ($pH->isActive()) {
    die(); // Already running!
}

$verbose_level = 1;

$groupcard = 5000;

$A2B = new A2Billing();
$logfile_cront_subfee = $A2B->config['log-files']['cront_subscriptionfee'] ?? "/tmp/a2billing_cront_subfee_log";

write_log($logfile_cront_subfee, basename(__FILE__) . ' line:' . __LINE__ . "[#### BATCH BEGIN ####]");

try {
    $db = Connection::getConnection();
} catch (Throwable) {
    $db = null;
}

if (!$db) {
    echo "[Cannot connect to the database]\n";
    write_log($logfile_cront_subfee, basename(__FILE__) . ' line:' . __LINE__ . "[Cannot connect to the database]");
    exit(1);
}

$instance_table = $db->table("cc_card", "c")
    ->select(
        "c.id AS card_id", "ss.id AS service_id", "cs.id AS card_subscription_id", "ss.label", "ss.fee", "ss.emailreport",
        "cs.startdate", "cs.paid_status", "cs.last_run", "cs.next_billing_date", "cs.limit_pay_date", "cs.product_name",
    )
    ->join("cc_card_subscription cs", "c.id", "cs.id_cc_card")
    ->join("cc_subscription_service ss", "cs.id_subscription_fee", "ss.id");
/*
    Pay_Status :
        0 : First USE
        1 : Billed
        2 : Paid
        3 : UnPaid
*/
$now = new DateTimeImmutable();

$instance_table
    ->where("ss.status", 1)
    ->where("cs.startdate", "<", $now)
    ->where(
        fn (Builder $q) => $q->whereNull("cs.stopdate")->orWhere("cs.stopdate", ">", $now)
    )
    ->where("ss.startdate", "<", $now)
    ->where(
        fn (Builder $q) => $q->whereNull("ss.stopdate")->orWhere("ss.stopdate", ">", $now)
    )
    ->where("cs.paid_status", "!=", 3);
$nb_card = $instance_table->count();

$nbpagemax = (ceil($nb_card / $groupcard));
if ($verbose_level >= 1) {
    echo "===> NB_CARD : $nb_card - NBPAGEMAX:$nbpagemax\n";
}

if (!($nb_card > 0)) {
    if ($verbose_level >= 1) {
        echo "[No card to run the Subscription service]\n";
    }
    write_log($logfile_cront_subfee, basename(__FILE__) . ' line:' . __LINE__ . "[No card to run the Subscription Feeservice]");
    exit();
}

$billdaybefor_anniversary = $A2B->config['global']['subscription_bill_days_before_anniversary'];
$now = new DateTimeImmutable();
$interval = new DateInterval("P{$billdaybefor_anniversary}D");
$limite_pay_date = $now->add($interval);

$service_array = [];

for ($page = 0; $page < $nbpagemax; $page++) {
    $result_subscriptions = $instance_table
        ->orderBy("c.id")
        ->limit($groupcard)
        ->offset($page * $groupcard)
        ->get();

    foreach ($result_subscriptions as $subscription) {
        $service_id = $subscription['service_id'];

        if (empty($service_array[$service_id])) {
            $service_array[$service_id] = ["totalcardperform" => 0, "totalcredit" => 0];
        }

        $action = "";

        switch ($subscription['paid_status']) {
            case 0:
                //firstuse : billed
                $action = "bill";
                $startdate = DateTimeImmutable::createFromFormat("Y-m-d H:i:s", $subscription["startdate"]);
                $billday = $startdate->format("j");
                $lastday_nextmonth = $startdate->modify("last day of next month")->format("j");
                if ($billday > $lastday_nextmonth) {
                    $next_bill_date = $startdate->modify("last day of next month");
                } else {
                    $billday -= 1;
                    $next_bill_date = $startdate->modify("first day of next month")->modify("+$billday days");
                }
                $next_bill_date = $next_bill_date->modify("-$billdaybefor_anniversary days");
                break;

            case 1:
                // billed : check if out of date -> unpaid
                $limit = DateTimeImmutable::createFromFormat("Y-m-d H:i:s", $subscription["limit_pay_date"]);

                if ($now > $limit) {
                    $action = "unpaid";
                }

                break;

            case 2:
                // paid : check if the system have to bill it again
                $next = DateTimeImmutable::createFromFormat("Y-m-d H:i:s", $subscription["next_billing_date"]);
                if ($now >= $next) {
                    $action = "bill";
                    $startdate = DateTimeImmutable::createFromFormat("Y-m-d H:i:s", $subscription["startdate"]);
                    $billday = $startdate->format("j");
                    $next_bill_date = DateTimeImmutable::createFromFormat("Y-m-d H:i:s", $subscription["next_billing_date"]);
                    $lastday_nextmonth = $next_bill_date->modify("last day of next month")->format("j");
                    if ($billday > $lastday_nextmonth) {
                        $next_bill_date = $next_bill_date->modify("last day of next month");
                    } else {
                        $billday -= 1;
                        $next_bill_date = $next_bill_date->modify("first day of next month")->modify("+$billday days");
                    }
                    $next_bill_date = $next_bill_date->modify("-$billdaybefor_anniversary days");
                }
                break;

            default:
                continue 2;
        }

        switch ($action) {

            case "bill" :
                //select card
                $result_card = $db->table("cc_card")
                    ->where("id", $subscription["card_id"])
                    ->first();

                if (!$result_card) {
                    break;
                }
                $card = $result_card;

                if (($card['credit'] + $card['typepaid'] * $card['creditlimit']) >= $subscription['fee']) {

                    // USER HAVE ENOUGH CREDIT TO PAY FOR THE DID
                    $service_array[$service_id]['totalcardperform']++;
                    $service_array[$service_id]['totalcredit']+= $subscription['fee'];

                    $db->table("cc_card")->where("id", $card["id"])->decrement("credit", $subscription["fee"]);
                    $db->table("cc_charge")->insert(
                        [
                            "id_cc_card" => $card["id"],
                            "amount" => $subscription["fee"],
                            "chargetype" => 3,
                            "id_cc_card_subscription" => $subscription["card_subscription_id"],
                            "charged_status" => 1,
                            "description" => $subscription["product_name"],
                        ]
                    );
                    $db->table("cc_card_subscription")->where("id", $subscription["card_subscription_id"])->update(["paid_status" => 2]);

                    $mail = new Mail(Mail::$TYPE_SUBSCRIPTION_PAID,$card['id'] );
                    $mail -> replaceInEmail(Mail::$SUBSCRIPTION_FEE,$subscription['fee']);
                    $mail -> replaceInEmail(Mail::$SUBSCRIPTION_ID,$subscription['id']);
                    $mail -> replaceInEmail(Mail::$SUBSCRIPTION_LABEL,$subscription['product_name']);
                    //update status to paid

                    try {
                        $mail -> send();
                    } catch (A2bMailException $e) {
                        if ($verbose_level >= 1)
                            echo "[Sent mail failed : $e]";
                        write_log($logfile_cront_subfee, basename(__FILE__) . ' line:' . __LINE__ . "[Sent mail failed : $e]");
                    }

                } else {

                    $reference = Invoice::generateReference();

                    //CREATE INVOICE If a new card then just an invoice item in the last invoice
                    $date = (new DateTimeImmutable())->format("Y-m-d h:i:s");
                    $card_id = $card['id'];
                    $title = gettext("SUBSCRIPTION INVOICE REMINDER");
                    $description = "Your credit was not enough to pay yours subscription automatically.\n";
                    $description .= "You have $billdaybefor_anniversary days to pay this invoice (REF: $reference ) or the account will be automatically disactived \n\n";
                    $instance_table = $db->table("cc_invoice");
                    $values = ["date" => $date, "id_card" => $card_id, "title"=> $title, "reference" => $reference, "description" => $description, "status" => 1, "paid_status" => 0];

                    if ($verbose_level >= 1) {
                        echo "INSERT INVOICE : " . json_encode($values) . "\n";
                    }
                    $id_invoice = $instance_table->insertGetId($values);

                    if (!empty ($id_invoice) && is_numeric($id_invoice)) {
                        $description = "Subscription (" . $subscription['product_name'] . ")";
                        $amount = $subscription['fee'];
                        $vat = 0;
                        $instance_table = $db->table("cc_invoice_item");
                        $values = [
                            "date" => $date,
                            "id_invoice" => $id_invoice,
                            "price" => $amount,
                            "vat" => $vat,
                            "description" => $description,
                            "id_ext" => $subscription["card_subscription_id"],
                            "type_ext" => "SUBSCR"
                        ];
                        if ($verbose_level >= 1) {
                            echo "INSERT INVOICE ITEM : " . json_encode($values) . "\n";
                        }
                        $instance_table->insert($values);
                    }

                    $mail = new Mail(Mail::$TYPE_SUBSCRIPTION_UNPAID, $card['id'] );
                    $mail -> replaceInEmail(Mail::$DAY_REMAINING_KEY, $billdaybefor_anniversary );
                    $mail -> replaceInEmail(Mail::$INVOICE_REF_KEY, $reference);
                    $mail -> replaceInEmail(Mail::$SUBSCRIPTION_FEE, $subscription['fee']);
                    $mail -> replaceInEmail(Mail::$SUBSCRIPTION_ID, $subscription['id']);
                    $mail -> replaceInEmail(Mail::$SUBSCRIPTION_LABEL, $subscription['product_name']);
                    //insert charge
                    $db->table("cc_charge")
                        ->insert(
                            [
                                "id_cc_card" => $card["id"],
                                "amount" => $subscription["fee"],
                                "chargetype" => 3,
                                "id_cc_card_subscription" => $subscription["card_subscription_id"],
                                "invoiced_status" => 1,
                                "description" => $subscription["product_name"],
                            ]
                        );
                    $db->table("cc_card_subscription")->where("id", $subscription["card_subscription_id"])->update(["paid_status" => 1]);

                    try {
                        $mail -> send();
                    } catch (A2bMailException $e) {
                        if ($verbose_level >= 1)
                            echo "[Sent mail failed : $e]";
                        write_log($logfile_cront_subfee, basename(__FILE__) . ' line:' . __LINE__ . "[Sent mail failed : $e]");
                    }
                }
                $db->table("cc_card_subscription")
                    ->where("id", $subscription["card_subscription_id"])
                    ->update([
                        "last_run" => $now,
                        "next_billing_date" => $next_bill_date,
                        "limit_pay_date" => $limite_pay_date,
                    ]
                );

                break;

            case "unpaid" :
                // block the card
                $db->table("cc_card")->where("id", $subscription["card_id"])->update(["status" => 8]);
                $db->table("cc_card_subscription")->where("id", $subscription["card_subscription_id"])->update(["paid_status" => 3]);

                $mail = new Mail(Mail::$TYPE_SUBSCRIPTION_DISABLE_CARD, $subscription('card_id'));
                $mail -> replaceInEmail(Mail::$SUBSCRIPTION_FEE, $subscription['fee']);
                $mail -> replaceInEmail(Mail::$SUBSCRIPTION_ID, $subscription['id']);
                $mail -> replaceInEmail(Mail::$SUBSCRIPTION_LABEL, $subscription['product_name']);
                try {
                    $mail -> send();
                } catch (A2bMailException $e) {
                    if ($verbose_level >= 1) {
                        echo "[Sent mail failed : $e]";
                    }
                    write_log($logfile_cront_subfee, basename(__FILE__) . ' line:' . __LINE__ . "[Sent mail failed : $e]");
                }
                break;
        }

    }

    sleep(10);
}

// UPDATE THE SERVICE
foreach ($service_array as $key => $value) {
    $db->table("cc_subscription_service")
        ->where("id", $key)
        ->incrementEach([
            "numberofrun" => 1,
            "totalcardperform" => $value["totalcardperform"],
            "totalcredit" => $value["totalcredit"],
        ], ["datelastrun" => $now]);
}

if ($verbose_level >= 1) {
    echo "#### END SUBSCRIPTION SERVICES \n";
}

write_log($logfile_cront_subfee, basename(__FILE__) . ' line:' . __LINE__ . "[#### BATCH PROCESS END ####]");
