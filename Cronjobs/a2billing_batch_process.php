#!/usr/bin/php -q
<?php

use A2billing\A2Billing;
use A2billing\A2bMailException;
use A2billing\Mail;
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
} else {
    $pH->activate();
}

$verbose_level = 0;
$groupcard = 1000;
$groupwait = 10; // number of second to wait if more then  groupcard updates done
$time_checks = 20; // number of minute to check with. i.e if time1-time2< $time_checks minutes, consider it is equal.
// this value must be greater then script run time. used only when checked if service msut be run.
$run = 1; // set to 0 if u want to just report, no updates. must be set to 1 on productional

$A2B = new A2Billing($idconfig);
$logfile_cront_batch = $A2B->config['log-files']['cront_batch_process'] ?? "/tmp/a2billing_cront_batch_log";

write_log($logfile_cront_batch, basename(__FILE__) . ' line:' . __LINE__ . "[#### BATCH BEGIN ####]");

if (!$A2B->DbConnect()) {
    echo "[Cannot connect to the database]\n";
    write_log($logfile_cront_batch, basename(__FILE__) . ' line:' . __LINE__ . "[Cannot connect to the database]");
    exit;
}

$interval = 24 + $time_checks . " HOUR";
if ($A2B->config["database"]["dbtype"] === "postgres") {
    $interval = "'$interval'";
}
$service_lastrun = "";

$instance_table = new Table(
    "cc_service",
    [
        "DISTINCT id", "name", "amount", "period", "rule", "daynumber", "stopmode", "maxnumbercycle", "status", "numberofrun",
        "datecreate", "datelastrun", "emailreport", "totalcredit", "totalcardperform", "dialplan", "operate_mode", "use_group",
    ]
);
$result = $instance_table->getRows(
    ["status" => 1, "datelastrun" => ["<", "CURRENT_TIMESTAMP - INTERVAL $interval"]],
    ["id"],
    "DESC"
);
if ($verbose_level >= 1)
    print_r($result);

if (!$result) {
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
    $rule 			= $myservice["rule"];
    $rule_day 		= $myservice["daynumber"];
    $stopmode 		= $myservice["stopmode"];
    $maxnumbercycle = $myservice["maxnumbercycle"];
    $dialplan		= $myservice["dialplan"];
    $operate_mode	= $myservice["operate_mode"];
    $use_group		= $myservice["use_group"];
    $amount			= $myservice["amount"];
    $filter			= [];
    if ($verbose_level >= 1)
        echo "[ rule $rule  $rule_day ]";

    // RULES
    if ($rule == 3) {
        $interval = "$period DAY";
        if ($A2B->config["database"]['dbtype'] == "postgres") {
            $interval = "'$interval'";
        }
        // card last run date <= period
        $filter["servicelastrun"] = ["<=", "CURRENT_TIMESTAMP - INTERVAL $interval"];
    }
    if (($rule == 1) && ($rule_day > 0)) {
        $interval = "1 DAY";
        if ($A2B->config["database"]['dbtype'] == "postgres") {
            $interval = "'$interval'";
        }
        // Apply service if card NO used in last y days
        $filter["lastuse"] = ["<", "CURRENT_TIMESTAMP - INTERVAL $interval"];
    }
    if (($rule == 2) && ($rule_day > 0)) {
        $interval = "1 DAY";
        if ($A2B->config["database"]['dbtype'] == "postgres") {
            $interval = "'$interval'";
        }
        // Apply service if card used in last y days
        $filter["lastuse"] = [">=", "CURRENT_TIMESTAMP - INTERVAL $interval"];
    }
    //stopmode variants
    if ($stopmode == 2) {
        // NBSERVICE <= MAXNUMBERCYCLE  STOPMODE Max number of cycle reach
        $filter["nbservice"] = ["<=", $myservice["maxnumbercycle"]];
    }
    if ($stopmode == 1) {
        // CREDIT <= 0 STOPMODE Account balance below zero
        $filter["credit"] = [">", 0];
    }
    // dialplan
    if ($dialplan > 0) {
        // dialplan check
        $filter["tariff"] = $dialplan;
    }
    $sql = "";
    $filter[] = [
        "SUB",
        [
            "firstusedate" => [["!=", null], [">", "1984-01-01"]]
        ],
        "AND"
    ];
    $filter["runservice"] = 1;
    if ($use_group == 0) {
        $filter["id_service"] = $myservice["id"];
        $result_card = (new Table("cc_card", ["id", "credit", "nbservice", "lastuse", "username", "servicelastrun", "email"], ["cc_cardgroup_service" => ["id_group", "id_card_group"]]))
            ->getRows($filter);
    } else {
        $result_card = (new Table("cc_card", ["id", "credit", "nbservice", "lastuse", "username", "servicelastrun", "email"]))
            ->getRows($filter);
    }

    $instance_table = new Table();
    $instance_table->begin();
    $query_count=0;

    foreach ($result_card as $mycard) {

        $query_count=$query_count+1;

        if ($query_count>=$groupcard) {
            $instance_table->end();
            if ($verbose_level >= 1)
                            echo "------>|< commit & wait \n";
            sleep($groupwait);
            $instance_table->begin();
            $query_count=0;
        }

        if ($verbose_level >= 1)
            print_r($mycard);

        $card_id = $mycard["id"];
        if ($verbose_level >= 1)
            echo "------>>>  ID = $card_id - CARD =" . $mycard["username"] . " - BALANCE =" . $mycard["credit"] . " \n";

        // UPDATE THE CARD CREDIT AND SERVICE LAST RUN
        $refill_amount = 0;
        if ($operate_mode == 1) {
            $credit_sql = ["CASE WHEN credit < ? AND credit > 0 THEN 0 WHEN credit <= 0 THEN credit ELSE credit - ? END", $amount, $amount];
            $current_amount = $mycard["credit"];
            if ($current_amount > $amount) {
                $refill_amount = $amount;
            } else {
                if ($current_amount > 0) {
                    $refill_amount = $current_amount;
                }
            }
        } else {
            $credit_sql = ["credit - ?", $amount];
            $refill_amount = $amount;
        }

        if ($refill_amount > 0) {
            if ($run) {
                $result = (new Table("cc_logrefill"))
                    ->addRow(["credit" => -$refill_amount, "card_id" => $card_id, "description" => "Recurrent $service_name ", "refill_type" => 2]);
            }
            $totalcredit += $refill_amount;
        }

        if ($run) {
            $result = (new Table("cc_card"))
                ->updateRow(["nbservice" => ["nbservice + ?", 1], "credit" => $credit_sql, "servicelastrun" => "CURRENT_TIMESTAMP"], ["id" => $mycard["id"]]);
        }
        $totalcardperform++;
    }

    $instance_table->end();

    write_log($logfile_cront_batch, basename(__FILE__) . ' line:' . __LINE__ . "[Service finish]");

    // INSERT REPORT SERVICE INTO THE DATABASE
    if ($run) {
        $result_insert = (new Table("cc_service_report"))
            ->addRow(["cc_service_id" => $myservice["id"], "totalcardperform" => $totalcardperform, "totalcredit" => $totalcredit, "daterun" => "CURRENT_TIMESTAMP"]);
    }

    write_log($logfile_cront_batch, basename(__FILE__) . ' line:' . __LINE__ . "[Service report : 'totalcardperform=$totalcardperform', 'totalcredit=$totalcredit']");

    if ($run)
        $result = (new Table("cc_service"))
            ->updateRow(
                ["datelastrun" => "CURRENT_TIMESTAMP", "numberofrun" => ["numberofrun + ?", 1], "totalcardperform" => ["totalcardperform + ?", $totalcardperform], "totalcredit" => ["totalcredit + ?", $totalcredit]],
                ["id" => $myservice["id"]]
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
