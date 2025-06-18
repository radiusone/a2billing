#!/usr/bin/php -q
<?php

use A2billing\A2Billing;
use A2billing\A2bMailException;
use A2billing\Mail;
use A2billing\Payments\Invoice;
use A2billing\ProcessHandler;
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
} else {
    $pH->activate();
}

$verbose_level = 1;

$groupcard = 5000;

$A2B = new A2Billing($idconfig);
$logfile_cront_subfee = $A2B->config['log-files']['cront_subscriptionfee'] ?? "/tmp/a2billing_cront_subfee_log";

write_log($logfile_cront_subfee, basename(__FILE__) . ' line:' . __LINE__ . "[#### BATCH BEGIN ####]");

if (!$A2B->DbConnect()) {
    echo "[Cannot connect to the database]\n";
    write_log($logfile_cront_subfee, basename(__FILE__) . ' line:' . __LINE__ . "[Cannot connect to the database]");
    exit;
}

$instance_table = new Table(
    "cc_card c",
    [
        "c.id AS card_id", "ss.id AS service_id", "cs.id AS card_subscription_id", "ss.label", "ss.fee", "ss.emailreport",
        "cs.startdate", "cs.paid_status", "cs.last_run", "cs.next_billing_date", "cs.limit_pay_date", "cs.product_name",
    ],
    [
        "cc_card_subscription cs" => ["INNER", "c.id", "cs.id_cc_card"],
        "cc_subscription_service ss" => ["INNER", "cs.id_subscription_fee", "ss.id"],
    ]
);
/*
    Pay_Status :
        0 : First USE
        1 : Billed
        2 : Paid
        3 : UnPaid
*/
$condition = [
    "ss.status" => 1,
    "cs.startdate" => ["<", "CURRENT_TIMESTAMP"],
    ["SUB", ["cs.stopdate" => [[null], [">", "CURRENT_TIMESTAMP"]]]],
    "ss.startdate" => ["<", "CURRENT_TIMESTAMP"],
    ["SUB", ["ss.stopdate" => [[null], [">", "CURRENT_TIMESTAMP"]]]],
    "cs.paid_status" => ["!=", 3]
];
$nb_card = $instance_table->countRows($condition);

$nbpagemax = (ceil($nb_card / $groupcard));
if ($verbose_level >= 1)
    echo "===> NB_CARD : $nb_card - NBPAGEMAX:$nbpagemax\n";

if (!($nb_card > 0)) {
    if ($verbose_level >= 1)
        echo "[No card to run the Subscription service]\n";
    write_log($logfile_cront_subfee, basename(__FILE__) . ' line:' . __LINE__ . "[No card to run the Subscription Feeservice]");
    exit ();
}

$billdaybefor_anniversary = $A2B->config['global']['subscription_bill_days_before_anniversary'];

$service_array = array();

for ($page = 0; $page < $nbpagemax; $page++) {
    $result_subscriptions = $instance_table->getRows($condition, ["c.id"], "ASC", [], $groupcard, $page * $groupcard);

    foreach ($result_subscriptions as $subscription) {
        $service_id = $subscription['service_id'];

        if (empty($service_array[$service_id])) $service_array[$service_id] = array("totalcardperform" => 0 , "totalcredit" => 0 );

        $action = "";

        switch ($subscription['paid_status']) {

            case 0:
                //firstuse : billed
                $action = "bill";
                $unix_startdate = strtotime($subscription['startdate']);
                $day_now = date("j");
                $last_run = date("Y-m-d");
                $day_startdate = date("j",$unix_startdate);
                $month_startdate = date("m",$unix_startdate);
                $year_startdate= date("Y",$unix_startdate);
                $lastday_of_startdate_month = lastDayOfMonth($month_startdate,$year_startdate,"j");

                $next_bill_date = strtotime("01-$month_startdate-$year_startdate + 1 month");
                $lastday_of_next_month= lastDayOfMonth(date("m",$next_bill_date),date("Y",$next_bill_date),"j");

                $limite_pay_date = date("Y-m-d",strtotime(" + $billdaybefor_anniversary day")) ;

                if ($day_startdate>$lastday_of_next_month) {
                    $next_limite_pay_date = date ("$lastday_of_next_month-m-Y" ,$next_bill_date);
                } else {
                    $next_limite_pay_date = date ("$day_startdate-m-Y" ,$next_bill_date);
                }

                $next_bill_date = date("Y-m-d",strtotime("$next_limite_pay_date - $billdaybefor_anniversary day")) ;
                break;

            case 1:
                // billed : check if out of date -> unpaid
                // date('m',strtotime($mycard['last_run']));
                $unix_limit = strtotime($subscription['limit_pay_date']);
                $unix_now = strtotime(date("d-m-Y"));

                if ($unix_now>$unix_limit) {
                    $action = "unpaid";
                }

                break;

            case 2:
                // paid : check if the system have to bill it again
                $unix_bill_time = strtotime($subscription['next_billing_date']);
                $unix_now = strtotime(date("d-m-Y"));
                if ($unix_now>=$unix_bill_time) {
                    $action = "bill";

                    $unix_startdate = strtotime($subscription['startdate']);
                    $last_run = date("Y-m-d");

                    $day_startdate = date("j",$unix_startdate);
                    $month_lastbill_date = date("m",$unix_bill_time);
                    $year_lastbill_date = date("Y",$unix_bill_time);
                    $lastday_of_next_billmonth = lastDayOfMonth($month_lastbill_date,$year_lastbill_date,"j");

                    $next_bill_date = strtotime("01-$month_lastbill_date-$year_lastbill_date + 1 month");
                    $lastday_of_next_month= lastDayOfMonth(date("m",$next_bill_date),date("Y",$next_bill_date),"j");

                    $limite_pay_date = date("Y-m-d",strtotime(" + $billdaybefor_anniversary day")) ;

                    if ($day_startdate>$lastday_of_next_month) {
                        $next_limite_pay_date = date ("$lastday_of_next_month-m-Y" ,$next_bill_date);
                    } else {
                        $next_limite_pay_date = date ("$day_startdate-m-Y" ,$next_bill_date);
                    }

                    $next_bill_date = date("Y-m-d",strtotime("$next_limite_pay_date - $billdaybefor_anniversary day")) ;

                }
                break;

            default:
                continue;
                break;
        }

        switch ($action) {

            case "bill" :
                //select card
                $table_card = new Table('cc_card', '*');
                $card_clause = ["id" => $subscription['card_id']];
                $result_card = $table_card -> getRow($card_clause);

                if (!$result_card)
                    break;
                else
                    $card = $result_card;

                if (($card['credit'] + $card['typepaid'] * $card['creditlimit']) >= $subscription['fee']) {

                    // USER HAVE ENOUGH CREDIT TO PAY FOR THE DID
                    $service_array[$service_id]['totalcardperform']++;
                    $service_array[$service_id]['totalcredit']+= $subscription['fee'];

                    (new Table("cc_card"))->updateRow(["credit" => ["credit - ?", $subscription["fee"]]], ["id" => $card["id"]]);
                    (new Table("cc_charge"))
                        ->addRow(
                            ["id_cc_card" => $card["id"], "amount" => $subscription["fee"], "chargetype" => 3, "id_cc_card_subscription" => $subscription["card_subscription_id"], "charged_status" => 1, "description" => $subscription["product_name"]]
                        );
                    (new Table("cc_card_subscription"))->updateRow(["paid_status" => 2], ["id" => $subscription["card_subscription_id"]]);

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
                    $date = date("Y-m-d h:i:s");
                    $card_id = $card['id'];
                    $title = gettext("SUBSCRIPTION INVOICE REMINDER");
                    $description = "Your credit was not enough to pay yours subscription automatically.\n";
                    $description .= "You have $billdaybefor_anniversary days to pay this invoice (REF: $reference ) or the account will be automatically disactived \n\n";
                    $instance_table = new Table("cc_invoice");
                    $values = ["date" => $date, "id_card" => $card_id, "title"=> $title, "reference" => $reference, "description" => $description, "status" => 1, "paid_status" => 0];

                    if ($verbose_level >= 1)
                        echo "INSERT INVOICE : " . json_encode($values) . "\n";
                    $instance_table->addRow($values, "id", $id_invoice);

                    if (!empty ($id_invoice) && is_numeric($id_invoice)) {
                        $description = "Subscription (" . $subscription['product_name'] . ")";
                        $amount = $subscription['fee'];
                        $vat = 0;
                        $instance_table = new Table("cc_invoice_item");
                        $values = ["date" => $date, "id_invoice", $id_invoice, "price" => $amount, "vat" => $vat, "description" => $description, "id_ext" => $subscription["card_subscription_id"], "type_ext" => "SUBSCR"];
                        if ($verbose_level >= 1)
                            echo "INSERT INVOICE ITEM : " . json_encode($values) . "\n";
                        $instance_table->addRow($values);
                    }

                    $mail = new Mail(Mail::$TYPE_SUBSCRIPTION_UNPAID, $card['id'] );
                    $mail -> replaceInEmail(Mail::$DAY_REMAINING_KEY, $billdaybefor_anniversary );
                    $mail -> replaceInEmail(Mail::$INVOICE_REF_KEY, $reference);
                    $mail -> replaceInEmail(Mail::$SUBSCRIPTION_FEE, $subscription['fee']);
                    $mail -> replaceInEmail(Mail::$SUBSCRIPTION_ID, $subscription['id']);
                    $mail -> replaceInEmail(Mail::$SUBSCRIPTION_LABEL, $subscription['product_name']);
                    //insert charge
                    (new Table("cc_charge"))
                        ->addRow(
                            ["id_cc_card" => $card["id"], "amount" => $subscription["fee"], "chargetype" => 3, "id_cc_card_subscription" => $subscription["card_subscription_id"], "invoiced_status" => 1, "description" => $subscription["product_name"]]
                        );
                    (new Table("cc_card_subscription"))->updateRow(["paid_status" => 1], ["id" => $subscription["card_subscription_id"]]);

                    try {
                        $mail -> send();
                    } catch (A2bMailException $e) {
                        if ($verbose_level >= 1)
                            echo "[Sent mail failed : $e]";
                        write_log($logfile_cront_subfee, basename(__FILE__) . ' line:' . __LINE__ . "[Sent mail failed : $e]");
                    }
                }
                (new Table("cc_card_subscription"))->updateRow(["last_run" => $last_run, "next_billing_date" => $next_bill_date, "limit_pay_date" => $limite_pay_date], ["id" => $subscription["card_subscription_id"]]);

                break;

            case "unpaid" :
                // block the card
                (new Table("cc_card"))->updateRow(["status" => 8], ["id" => $subscription["card_id"]]);
                (new Table("cc_card_subscription"))->updateRow(["paid_status" => 3], ["id" => $subscription["card_subscription_id"]]);

                $mail = new Mail(Mail::$TYPE_SUBSCRIPTION_DISABLE_CARD, $subscription('card_id'));
                $mail -> replaceInEmail(Mail::$SUBSCRIPTION_FEE, $subscription['fee']);
                $mail -> replaceInEmail(Mail::$SUBSCRIPTION_ID, $subscription['id']);
                $mail -> replaceInEmail(Mail::$SUBSCRIPTION_LABEL, $subscription['product_name']);
                try {
                    $mail -> send();
                } catch (A2bMailException $e) {
                    if ($verbose_level >= 1)
                        echo "[Sent mail failed : $e]";
                    write_log($logfile_cront_subfee, basename(__FILE__) . ' line:' . __LINE__ . "[Sent mail failed : $e]");
                }
                break;
        }

    }

    sleep(10);
}

// UPDATE THE SERVICE
foreach ($service_array as $key => $value) {
    (new Table("cc_subscription_service"))
        ->updateRow(
            ["datelastrun" => "CURRENT_TIMESTAMP", "numberofrun" => ["numberofrun + ?", 1], "totalcardperform" => ["totalcardperform + ?", $value["totalcardperform"]], "totalcredit" => ["totalcredit + ?", $value["totalcredit"]]],
            ["id" => $key]
        );
}

if ($verbose_level >= 1)
    echo "#### END SUBSCRIPTION SERVICES \n";

write_log($logfile_cront_subfee, basename(__FILE__) . ' line:' . __LINE__ . "[#### BATCH PROCESS END ####]");
