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
 *            a2billing_alarm.php
 *
 *	Purpose : manage different Alarms
 *  ADD THIS SCRIPT IN A CRONTAB JOB
 *
    crontab -e
    0 * * * * php //usr/local/a2billing/Cronjobs/a2billing_alarm.php

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
$pH= new ProcessHandler("/var/run/a2billing/a2billing_alarm_pid.php");
if ($pH->isActive()) {
    die(); // Already running!
} else {
    $pH->activate();
}

$verbose_level = 0;
$groupcard = 5000;

$A2B = new A2Billing();
$cron_logfile = $A2B->config['log-files']['cront_alarm'] ?? "/tmp/a2billing_cront_alarm_log";
write_log($cron_logfile, basename(__FILE__) . ' line:' . __LINE__ . "[#### BATCH BEGIN ####]");

try {
    $db = Connection::getConnection();
} catch (Throwable) {
    $db = null;
}

if (!$db) {
    echo "[Cannot connect to the database]\n";
    write_log($cron_logfile, basename(__FILE__) . ' line:' . __LINE__ . "[Cannot connect to the database]");
    exit(1);
}

// CHECK THE ALARMS
$result = $db->table("cc_alarm")
    ->select(["id", "name", "periode", "type", "maxvalue", "minvalue", "id_trunk", "status", "numberofrun", "datecreate", "datelastrun", "emailreport"])
    ->where("status", 1)
    ->get();
if ($verbose_level >= 1) {
    print_r($result);
}

if (!$result) {
    echo "[No Alarm to run]\n";
    write_log($cron_logfile, basename(__FILE__) . ' line:' . __LINE__ . "[ No Alarm to run]");
    exit();
}
// 0 id, 1 name, 2 period, 3 type, 4 maxvalue, 5 minvalue, 6 id_trunk, 7 status, 8 numberofrun, 9 datecreate, 10 datelastrun, 11 emailreport

$oneday = 60 * 60 * 24;
$groupcalls = 5000;
$now = new DateTimeImmutable();

// BROWSE THROUGH THE ALARMS
foreach ($result as $myalarm) {
    $query = $db->table("cc_call")->select(["terminatecauseid", "sessiontime"]);
    $lastrun = empty($myalarm["datelastrun"])
        ? null
        : new DateTimeImmutable($myalarm["datelastrun"]);

    //  1 "Daily", 2 "Weekly", 3 "Monthly"
    $run_alarm = false;

    switch ($myalarm["periode"]) {
        // Hourly
        case 1 :
            if (is_null($lastrun)) {
                $run_alarm = true;
            } elseif ($now->format("G") !== $lastrun->format("G")) {
                $run_alarm = true;
                $query->whereBetween("starttime", [
                    $lastrun,
                    $now,
                ]);
            }
            if ($verbose_level >= 1) {
                echo "\n\n TODAY :" . $now->format("G") . " LAST RUN DAY :" . $lastrun?->format("G") ?? "never";
            }
            break;
            // Daily
        case 2 :
            if (is_null($lastrun)) {
                $run_alarm = true;
            }
            if ($now->format("j") !== $lastrun->format("j")) {
                $run_alarm = true;
                $query->whereBetween("starttime", [
                    $now->setTime(0, 0),
                    $now->setTime(23, 59, 59),
                ]);
            }
            if ($verbose_level >= 1) {
                echo "\n\n TODAY :" . $now->format("j") . " LAST RUN DAY :" . $lastrun?->format("j") ?? "never";
            }
            break;
            //Weekly -> will run only monday and check if the week is not the same
        case 3 :
            if (is_null($lastrun)) {
                $run_alarm = true;
            }
            if ($now->format("w") === "1" && $now->format("W") !== $lastrun->format("W")) {
                $run_alarm = true;
                $query->whereBetween("starttime", [
                    $now->modify("last week")->setTime(0, 0),
                    $now->modify("last week")->setTime(23, 59, 59),
                ]);
            }
            if ($verbose_level >= 1) {
                echo "\n\n TODAY :" . $now->format("w") . " WEEK:" . $now->format("W") .
                    " LAST RUN DAY :" . $lastrun?->format("w") ?? "never" . " LAST RUN WEEK :" . $lastrun?->format("W") ?? "never";
            }
            break;
            //Monthly
        case 4 :
            if (is_null($lastrun)) {
                $run_alarm = true;
            }
            if ($now->format("j") == "1" && $now->format("m") !== $lastrun->format("m")) {
                $run_alarm = true;
                $query->whereBetween("starttime", [
                    $now->modify("-1 month")->setTime(0, 0),
                    $now->setTime(0, 0)->modify("-1 second"),
                ]);
            }
            if ($verbose_level >= 1) {
                echo "\n\n THIS MONTH :" . $now->format("m") . " LAST RUN MONTH :" . $lastrun?->format("m") ?? "never";
            }
            break;
    }

    if ($run_alarm) {

        if (!empty($myalarm["id_trunk"])) {
            $query->where("id_trunk", $myalarm["id_trunk"]);
        }
        write_log($cron_logfile, basename(__FILE__) . ' line:' . __LINE__ . "[Alarm : " . $myalarm[1] . " ]");

        $nb_card = $query->count();
        $nbpagemax = (ceil($nb_card / $groupcalls));
        if ($verbose_level >= 1) {
            echo "===> NB_CARD : $nb_card - NBPAGEMAX:$nbpagemax\n";
        }

        if (!($nb_card > 0)) {
            if ($verbose_level >= 1) {
                echo "[No call to run the Alarm Service]\n";
            }
            write_log($cron_logfile, basename(__FILE__) . ' line:' . __LINE__ . "[No call to run the Alarm service]");
            exit();
        }

        $totalsuccess = 0;
        $totalfail = 0;
        $totaltime = 0;
        $max_fail = 0;
        $max = 0;
        $update = [];
        for ($page = 0; $page < $nbpagemax; $page++) {
            // REST AFTER $groupcalls CARD HANDLED
            if ($page > 0) {
                sleep(15);
            }

            $calls = $query
                ->limit($groupcard)
                ->offset($page * $groupcard)
                ->get();
            foreach ($calls as $call) {
                $totalsuccess += $call["terminatecauseid"] == 1 ? 1 : 0;
                $totalfail += $call["terminatecauseid"] == 1 ? 0: 1;
                $totaltime += $call["sessiontime"];
                // FIND THE CIC (Consecutive Incomplete Calls)
                if ($call["terminatecauseid"] != 1) {
                    $max++;
                } else {
                    $max_fail = max($max, $max_fail);
                    $max = 0;
                }
            }
            $max_fail = max($max, $max_fail);
        } // LOOP FOR THE CALLS

        if ($max_fail === 1) {
            $max_fail = 0;
        }

        $ASR = $totalsuccess / ($totalsuccess + $totalfail);
        $ALOC = $totaltime / $totalsuccess;

        $send_alarm = false;

        //$type_list   ["0" ALOC, "1" ASR, "2" CIC]
        switch ($myalarm["type"]) {
            case 1 :
                if ($ALOC > $myalarm["maxvalue"] || $ALOC < $myalarm["minvalue"]) {
                    $value = $ALOC;
                    $send_alarm = true;
                }
                $content = "\n\n The ALOC $ALOC seconds is outside the range max: " . $myalarm["maxvalue"] . " min:" . $myalarm["minvalue"] . " defined in the alarm";
                break;
            case 2 :
                if ($ASR > $myalarm["maxvalue"] || $ASR < $myalarm["minvalue"]) {
                    $value = $ASR;
                    $send_alarm = true;
                }
                $content = "\n\n The ASR " . $ASR . "% is outside the range max: " . $myalarm["maxvalue"] . " min:" . $myalarm["minvalue"] . " defined in the alarm";
                break;

            case 3 :
                if ($max_fail >= $myalarm["maxvalue"]) {
                    $value = $max_fail;
                    $send_alarm = true;
                }
                $content = "\n\n The Max Consecutive Incomplete Calls : " . $max_fail . " calls is greater than the max: " . $myalarm["maxvalue"] . " defined in the alarm";
                break;
        }
        if ($verbose_level >= 1) {
            echo "content = $content\n";
        }
        write_log($cron_logfile, basename(__FILE__) . ' line:' . __LINE__ . "[Alarm finish]");
        // INSERT REPORT ALARM INTO THE DATABASE
        $db->table("cc_alarm_report")->insert(["cc_alarm_id" => $myalarm["id"], "calculatedvalue" => $value]);

        write_log($cron_logfile, basename(__FILE__) . ' line:' . __LINE__ . "[Alarm report : 'Alarm name=$myalarm[name]', 'Alarm type=$myalarm[type]', 'Calculated value=$value']");

        $db->table("cc_alarm")->where("id", $myalarm["id"])
            ->incrementEach([
                "numberofrun" => 1,
                // UPDATE THE ALARM
                "numberofalarm" => $send_alarm ? 1 : 0
            ], ["datelastrun" => $now]);

        // SEND REPORT
        if (($send_alarm) && (strlen($myalarm[7]) > 0)) {
            $mail_subject = "A2BILLING ALARM : REPORT";

            $mail_content = "ALARM NAME = " . $myalarm[1];
            $mail_content .= $content;

            try {
                $mail = new Mail(null, null, null, $mail_content, $mail_subject);
                $mail -> send($myalarm[11]);
            } catch (A2bMailException $e) {
                if ($verbose_level >= 1)
                    echo "[Sent mail failed : $e]";
                write_log($cron_logfile, basename(__FILE__) . ' line:' . __LINE__ . "[Sent mail failed : $e]");
            }
        }

    } // IF ALARM

} // MAIN LOOP FOR THE ALARM

if ($verbose_level >= 1) {
    echo "#### END ALARMS \n";
}
write_log($cron_logfile, basename(__FILE__) . ' line:' . __LINE__ . "[#### ALARM END ####]");
