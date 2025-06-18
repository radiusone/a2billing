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
 *            a2billing_notify_account.php
 *
 *  20 May 2008
 *  Purpose: To check account of each Users and send an email if the balance is less than the user have choosed.
 *  you can run this cront every hour
 *  ADD THIS SCRIPT IN A CRONTAB JOB
 *
 *  The sample above will run the script once every hour
    crontab -e
    0 * * * * php /usr/local/a2billing/Cronjobs/a2billing_notify_account.php

    field	 allowed values
    -----	 --------------
    minute	 0-59
    hour		 0-23
    day of month	 1-31
    month	 1-12 (or names, see below)
    day of week	 0-7 (0 or 7 is Sun, or use names)

****************************************************************************/

// CHECK ALL AND ENSURE IT WORKS / NOT URGENT

set_time_limit(0);
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));

include (dirname(__FILE__) . "/../common/lib/admin.defines.php");

// CHECK IF THE CRONT PROCESS IS ALREADY RUNNING
$pH= new ProcessHandler("/var/run/a2billing/a2billing_notify_account_pid.php");
if ($pH->isActive()) {
    die(); // Already running!
} else {
    $pH->activate();
}

$verbose_level = 0;
$groupcard = 5000;

$A2B = new A2Billing($idconfig);
$cron_logfile = $A2B->config['log-files']['cront_check_account'] ?? "/tmp/a2billing_cront_checkaccount_log";

write_log($cron_logfile, basename(__FILE__) . ' line:' . __LINE__ . "[#### BATCH BEGIN ####]");

if (!$A2B->DbConnect()) {
    echo "[Cannot connect to the database]\n";
    write_log($cron_logfile, basename(__FILE__) . ' line:' . __LINE__ . "[Cannot connect to the database]");
    exit;
}

//Check if the notifications is Enable or Disable
if (empty ($A2B->config['notifications']['cron_notifications'])) {
    echo "[The cron of notification is de-activated]\n";
    exit;
}

//$A2B -> DBHandle
$instance_table = new Table("cc_card");

// Prepare the date interval to filter the card that don't have to receive a notification;
$Delay_Clause = "( ";
if ($A2B->config["database"]['dbtype'] == "postgres") {
    $CURRENT_DATE = "CURRENT_DATE";
} else {
    $CURRENT_DATE = "CURDATE()";
}

$condition = ["notify_email" => 1, "status" => 1, "CASE WHEN typepaid = 1 AND creditlimit IS NOT NULL THEN credit + creditlimit ELSE credit END" => ["<", ["credit_notification"]]];
if ($A2B->config['notifications']['delay_notifications'] <= 0) {
    $condition[] = ["SUB", ["last_notification" => [null], ["<", "$CURRENT_DATE + 1"]], "OR"];
} else {
    $condition[] = ["SUB", ["last_notification" => [null], ["<", "$CURRENT_DATE - " . $A2B->config['notifications']['delay_notifications']]], "OR"];
}

// CHECK AMOUNT OF CARD ON WHICH APPLY THE CHECK ACCOUNT SERVICE
$nb_card = $instance_table->countRows($condition);
$nbpagemax = (ceil($nb_card / $groupcard));
if ($verbose_level >= 1)
    echo "===> NB_CARD : $nb_card - NBPAGEMAX:$nbpagemax\n";

if (!($nb_card > 0)) {
    if ($verbose_level >= 1)
        echo "[No card to run the Recurring service]\n";
    write_log($cron_logfile, basename(__FILE__) . ' line:' . __LINE__ . "[No card to run the check account service]");
    exit ();
}

write_log($cron_logfile, basename(__FILE__) . ' line:' . __LINE__ . "[Number of card found : $nb_card]");

// BROWSE THROUGH THE CARD TO APPLY THE CHECK ACCOUNT SERVICE
for ($page = 0; $page < $nbpagemax; $page++) {

    $result_card = $instance_table->getRows($condition);
    foreach ($result_card as $mycard) {

        if ($verbose_level >= 1)
            print_r($mycard);
        if ($verbose_level >= 1)
            echo "------>>>  ID = " . $mycard['id'] . " - CARD =" . $mycard['username'] . " - BALANCE =" . $mycard['credit'] . " \n";

        // SEND NOTIFICATION
        if (strlen($mycard['email_notification']) > 0 || strlen($mycard['email']) > 0) { // ADD CHECK EMAIL

            // Sent Mail
            try {
                $mail = new Mail(Mail::$TYPE_REMINDER, $mycard['id']);
            } catch (Exception $e) {
                if ($verbose_level >= 1)
                    echo "[Cannot find a template mail for reminder]\n";
                write_log($cron_logfile, basename(__FILE__) . ' line:' . __LINE__ . "[Cannot find a template mail for reminder]");
                exit;
            }

            try {
                if (strlen($mycard['email_notification']) > 0)
                    $mail->send($mycard['email_notification']);
                else
                    $mail->send($mycard['email']);

                //update the card with the date of last notification
                $instance_table->updateRow(["last_notification" => "CURRENT_TIMESTAMP"], ["id" => $mycard["id"]]);

                if ($verbose_level >= 1) {
                    echo "[UPDATE CARD ID < " . $mycard['id'] . " > : last_notification]\n";
                }
            } catch (A2bMailException $e) {
                $error_msg = $e->getMessage();
                if ($verbose_level >= 1)
                    echo "$error_msg\n";
                write_log($cron_logfile, basename(__FILE__) . ' line:' . __LINE__ . $mycard['email_notification']." - $error_msg");
            }

        } //endif check the email not null
    }
    // Little bit of rest
    sleep(10);
}

if ($verbose_level >= 1)
    echo "#### END RECURRING CHECK ACCOUNT \n";

write_log($cron_logfile, basename(__FILE__) . ' line:' . __LINE__ . "[#### BATCH PROCESS END ####]");
