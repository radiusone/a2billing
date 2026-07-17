#!/usr/bin/php -q
<?php

use A2billing\A2Billing;
use A2billing\A2bMailException;
use A2billing\Connection;
use A2billing\Mail;
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
 *            a2billing_batch_process_alt.php
 *
 *  Copyright  2009 @  Arheops & Areski Belaid
 *  ADD THIS SCRIPT IN A CRONTAB JOB
 *
 *  Description :
 *  This script will take care of the recurring service.
 *
 *
    crontab -e
    0 12 * * * php /usr/local/a2billing/Cronjobs/a2billing_batch_process.php

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
$pH= new ProcessHandler("/var/run/a2billing/a2billing_batch_process_pid.php");
if ($pH->isActive()) {
    die(); // Already running!
}

$verbose_level = 0;
$groupcard = 1000;
$groupwait = 10; // number of second to wait if more then  groupcard updates done
$time_checks = 20; // number of minute to check with. i.e if time1-time2< $time_checks minutes, consider it is equal.
// this value must be greater then script run time. used only when checked if service msut be run.
$run = 1; // set to 0 if u want to just report, no updates. must be set to 1 on productional

$A2B = new A2Billing();
$logfile_cront_batch = $A2B->config['log-files']['cront_batch_process'] ?? "/tmp/a2billing_cront_batch_log";

write_log($logfile_cront_batch, basename(__FILE__) . ' line:' . __LINE__ . "[#### BATCH BEGIN ####]");

try {
    $db = Connection::getConnection();
} catch (Throwable) {
    $db = null;
}

if (!$db) {
    echo "[Cannot connect to the database]\n";
    write_log($logfile_cront_batch, basename(__FILE__) . ' line:' . __LINE__ . "[Cannot connect to the database]");
    exit(1);
}

$now = new DateTimeImmutable();
$interval = new DateInterval("P" . (24 + $time_checks) . "H");
$service_lastrun = "";

$result = $db->table("cc_service")
    ->distinct()
    ->select(
        "id", "name", "amount", "period", "rule", "daynumber", "stopmode", "maxnumbercycle", "status", "numberofrun",
        "datecreate", "datelastrun", "emailreport", "totalcredit", "totalcardperform", "dialplan", "operate_mode", "use_group",
    )
    ->where(["status" => 1, ["datelastrun", "<", $now->sub($interval)]])
    ->orderBy("id", "DESC")
    ->get();

if ($verbose_level >= 1) {
    print_r($result);
}

if (count($result) === 0) {
    echo "[No Recurring service to run]\n";
    write_log($logfile_cront_batch, basename(__FILE__) . ' line:' . __LINE__ . "[ No Recurring service to run]");
    exit ();
}

// mail variable for user notification

// BROWSE THROUGH THE SERVICES
foreach ($result as $myservice) {

    $totalcardperform = 0;
    $totalcredit = 0;

    write_log($logfile_cront_batch, basename(__FILE__) . ' line:' . __LINE__ . "[Service : " . $myservice["name"] . " ]");
    $service_name 	= $myservice["name"];
    $period 		= $myservice["period"];
    $rule 			= (int)$myservice["rule"];
    $rule_day 		= (int)$myservice["daynumber"];
    $stopmode 		= (int)$myservice["stopmode"];
    $maxnumbercycle = (int)$myservice["maxnumbercycle"];
    $dialplan		= (int)$myservice["dialplan"];
    $operate_mode	= (int)$myservice["operate_mode"];
    $use_group		= (int)$myservice["use_group"];
    $amount			= $myservice["amount"];
    $filter			= [];
    if ($verbose_level >= 1)
        echo "[ rule $rule  $rule_day ]";

    $card_table = $db->table("cc_card")
        ->select("id", "credit", "nbservice", "lastuse", "username", "servicelastrun", "email");

    if ($use_group === 0) {
        $card_table->leftJoin("cc_cardgroup_service", "id_group", "id_card_group")
            ->where("id_service", $myservice["id"]);
    }

    // RULES
    if ($rule === 3) {
        $interval = new DateInterval("P{$period}D");
        // card last run date <= period
        $card_table->where("servicelastrun", "<=", $now->sub($interval));
    } elseif ($rule === 1 && $rule_day > 0) {
        $interval = new DateInterval("P1D");
        // Apply service if card NO used in last y days
        $card_table->where("lastuse", "<", $now->sub($interval));
    } elseif ($rule === 2 && $rule_day > 0) {
        $interval = new DateInterval("P1D");
        // Apply service if card used in last y days
        $card_table->where("lastuse", ">=", $now->sub($interval));
    }
    //stopmode variants
    if ($stopmode === 2) {
        // NBSERVICE <= MAXNUMBERCYCLE  STOPMODE Max number of cycle reach
        $card_table->where("nbservice", "<=", $myservice["maxnumbercycle"]);
    } elseif ($stopmode === 1) {
        // CREDIT <= 0 STOPMODE Account balance below zero
        $card_table->where("credit", ">", 0);
    }
    // dialplan
    if ($dialplan > 0) {
        // dialplan check
        $card_table->where("tariff", $dialplan);
    }
    $result_card = $card_table
        ->where("firstusedate", ">", "1984-01-01")
        ->whereNotNull("firstusedate")
        ->where("runservice", 1)
        ->get();

    $db->beginTransaction();
    $query_count = 0;

    foreach ($result_card as $mycard) {

        $query_count++;

        if ($query_count >= $groupcard) {
            $db->commit();
            if ($verbose_level >= 1) {
                echo "------>|< commit & wait \n";
            }
            sleep($groupwait);
            $db->beginTransaction();
            $query_count = 0;
        }

        if ($verbose_level >= 1)
            print_r($mycard);

        $card_id = $mycard["id"];
        if ($verbose_level >= 1) {
            echo "------>>>  ID = $card_id - CARD =" . $mycard["username"] . " - BALANCE =" . $mycard["credit"] . " \n";
        }

        // UPDATE THE CARD CREDIT AND SERVICE LAST RUN
        $refill_amount = 0;
        $am = $db->escape($amount);
        if ($operate_mode === 1) {
            $credit_sql = $db->raw("CASE WHEN credit < $am AND credit > 0 THEN 0 WHEN credit <= 0 THEN credit ELSE credit - $am END");
            $current_amount = $mycard["credit"];
            if ($current_amount > $amount) {
                $refill_amount = $amount;
            } elseif ($current_amount > 0) {
                $refill_amount = $current_amount;
            }
        } else {
            $credit_sql = $db->raw("credit - $am");
        }

        if ($refill_amount > 0) {
            if ($run) {
                $db->table("cc_logrefill")
                    ->insert([
                        "credit" => -$refill_amount,
                        "card_id" => $card_id,
                        "description" => "Recurrent $service_name ",
                        "refill_type" => 2
                    ]);
            }
            $totalcredit += $refill_amount;
        }

        if ($run) {
            $db->table("cc_card")
                ->where("id", $mycard["id"])
                ->increment("nbservice", 1, [
                    "credit" => $credit_sql,
                    "servicelastrun" => new DateTime(),
                ]);
        }
        $totalcardperform++;
    }

    $db->commit();

    write_log($logfile_cront_batch, basename(__FILE__) . ' line:' . __LINE__ . "[Service finish]");

    // INSERT REPORT SERVICE INTO THE DATABASE
    if ($run) {
        $result_insert = $db->table("cc_service_report")
            ->insert([
                "cc_service_id" => $myservice["id"],
                "totalcardperform" => $totalcardperform,
                "totalcredit" => $totalcredit,
                "daterun" => $now,
            ]);
    }

    write_log($logfile_cront_batch, basename(__FILE__) . ' line:' . __LINE__ . "[Service report : 'totalcardperform=$totalcardperform', 'totalcredit=$totalcredit']");

    if ($run)
        $result = $db->table("cc_service")
            ->where("id", $myservice["id"])
            ->incrementEach(
                [
                    "numberofrun" => 1,
                    "totalcardperform" => $totalcardperform,
                    "totalcredit" => $totalcredit
                ],
                ["datelastrun" => $now]
            );

    // SEND REPORT
    if (strlen($myservice["emailreport"]) > 0) {
        $mail_subject = "RECURRING SERVICES : REPORT";

        $mail_content = "SERVICE NAME = " . $myservice["name"];
        $mail_content .= "\n\nTotal card updated = " . $totalcardperform;
        $mail_content .= "\nTotal credit removed = " . $totalcredit;

        try {
            $mail = new Mail(null, null, null, $mail_content, $mail_subject);
            $mail -> send($myservice["emailreport"]);
        } catch (A2bMailException $e) {
            if ($verbose_level >= 1)
                echo "[Sent mail failed : $e]";
            write_log($logfile_cront_batch, basename(__FILE__) . ' line:' . __LINE__ . "[Sent mail failed : $e]");
        }
    }

} // END FOREACH SERVICES

if ($verbose_level >= 1)
    echo "#### END RECURRING SERVICES \n";

write_log($logfile_cront_batch, basename(__FILE__) . ' line:' . __LINE__ . "[#### BATCH PROCESS END ####]");
