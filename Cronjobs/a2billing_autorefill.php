#!/usr/bin/php
<?php

use A2billing\A2Billing;
use A2billing\A2bMailException;
use A2billing\Connection;
use A2billing\Mail;
use A2billing\ProcessHandler;
use A2billing\Table;
use Illuminate\Database\Query\Builder;

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

set_time_limit(0);
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));

require_once __DIR__ . "/../common/lib/admin.defines.php";

// CHECK IF THE CRONT PROCESS IS ALREADY RUNNING
$pH = new ProcessHandler("/var/run/a2billing/a2billing_autorefill_pid.php");
if ($pH->isActive()) {
    die(); // Already running!
}

$groupcard = 1000;

$A2B = new A2Billing();
$log = $A2B->config['log-files']['cront_autorefill'] ?? "/tmp/a2billing_cront_autorefill_log";

try {
    $db = Connection::getConnection();
} catch (Throwable) {
    $db = null;
}

if (!$db) {
    echo "[Cannot connect to the database]\n";
    write_log($log, basename(__FILE__) . ' line:' . __LINE__ . "[Cannot connect to the database]");
    exit(1);
}

write_log($log, basename(__FILE__) . ' line:' . __LINE__ . "[#### BATCH BEGIN ####]");

$instance_table = $db->table("cc_card")->select("id, credit, initialbalance")
    ->where("autorefill", 1)
    ->where(function (Builder $q) {
        $q->where(fn (Builder $q) => $q->where(["typepaid" => 0, ["initialbalance", ">", 0], ["credit", "<", "initialbalance"]]))
            ->orWhere("typepaid", 1);
    });
// CHECK NUMBER OF CARD
$nb_card = $instance_table->count();
$nbpagemax = ceil($nb_card / $groupcard);

if ($nb_card === 0) {
    write_log($log, basename(__FILE__) . ' line:' . __LINE__ . "[No card to run the Auto Refill]");
    exit();
}

write_log($log, basename(__FILE__) . ' line:' . __LINE__ . "[Number of card found : $nb_card]");

$totalcardperform = 0;
$totalcredit = 0;

// BROWSE THROUGH THE CARD TO APPLY THE AUTO REFILL
for ($page = 0; $page < $nbpagemax; $page++) {
    $result_card = $instance_table->orderBy("id")->limit($groupcard)->offset($page * $groupcard)->get();

    foreach ($result_card as $mycard) {
        $refill_amount = $mycard["initialbalance"] - $mycard["credit"];
        $db->table("cc_card")->where("id", $mycard["id"])->update(["credit" => $db->raw("initialbalance")]);
        $totalcredit += $refill_amount;
        $totalcardperform++;

        // INSERT LOG REFILL INTO THE DATABASE
        $db->table("cc_logrefill")->insert(["credit" => $refill_amount, "card_id" => $mycard["id"]]);
    }
    // Little bit of rest
    sleep(5);
}

write_log($log, basename(__FILE__) . ' line:' . __LINE__ . "[Auto Refill finish]");

if ($totalcredit !== 0) {
// INSERT REPORT SERVICE INTO THE DATABASE
    (new Table("cc_autorefill_report"))
        ->addRow(["totalcardperform" => $totalcardperform, "totalcredit" => $totalcredit]);
    write_log($log, basename(__FILE__) . ' line:' . __LINE__ . "[Service report : 'totalcardperform=$totalcardperform', 'totalcredit=$totalcredit']");
}

// SEND REPORT
if (filter_var($A2B->config["webui"]["email_admin"], FILTER_VALIDATE_EMAIL)) {
    $mail_subject = "A2BILLING AUTO REFILL : REPORT";
    $mail_content = "AUTO REFILL\n\nTotal card updated = $totalcardperform\nTotal credit added = $totalcredit";
    try {
        $mail = new Mail(null, null, null, $mail_content, $mail_subject);
        $mail->send($A2B->config["webui"]["email_admin"]);
    } catch (A2bMailException $e) {
        write_log($log, basename(__FILE__) . ' line:' . __LINE__ . "[Sent mail failed : $e]");
    }
}

write_log($log, basename(__FILE__) . ' line:' . __LINE__ . "[#### AUTO REFILL PROCESS END ####]");
