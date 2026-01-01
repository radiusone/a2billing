#!/usr/bin/php -q
<?php

use A2billing\A2Billing;
use A2billing\Connection;
use A2billing\Customer;
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
 *            a2billing_batch_autodialer.php
 *
 *	Purpose : to proceed the autodialer
 *  Fri Oct 21 11:51 2008
 *  Copyright  2008  A2Billing
 *  ADD THIS SCRIPT IN A CRONTAB JOB
 *
    crontab -e
    * / 5 * * * * php /usr/local/a2billing/Cronjobs/a2billing_batch_autodialer.php

    field	 allowed values
    -----	 --------------
    minute	 		0-59
    hour		 	0-23
    day of month	1-31
    month	 		1-12 (or names, see below)
    day of week	 	0-7 (0 or 7 is Sun, or use names)

    #Run command every 5 minutes during 6-13 hours
    * / 5 6-13 * * mon-fri test.script    !!! no space between * / 5

****************************************************************************/

set_time_limit(0);
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
//dl("pgsql.so"); // remove "extension= pgsql.so !

include (dirname(__FILE__) . "/../common/lib/admin.defines.php");

// CHECK IF THE CRONT PROCESS IS ALREADY RUNNING
$pH= new ProcessHandler("/var/run/a2billing/a2billing_batch_autodialer_pid.php");
if ($pH->isActive()) {
    die(); // Already running!
} else {
    $pH->activate();
}

$verbose_level = 1;

// time to wait between every send in callback queue
$timing = 6;
$group = 20;

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
$num_day = $now->format("N");
$name_day = ["", "monday", "tuesday", "wednesday", "thursday", "friday", "saturday", "sunday"][$num_day];

$result_phonenumbers_all = $db->table("cc_phonenumber", "pn")
    ->select(["pn.id AS cc_phonenumber_id", "pn.number", "c.id AS cc_campaign_id", "c.frequency", "c.forward_number", "c.id_cid_group", "cc_card.id AS cc_card_id", "cc_card.tariff", "cc_card.username"])
    ->leftJoin("cc_phonebook AS pb", "pn.id_phonebook", "pb.id")
    ->leftJoin("cc_campaign_phonebook AS cpb", "cpb.id_phonebook", "pn.id")
    ->leftJoin("cc_campaign AS c", "cpb.id_campaign", "c.id")
    ->leftJoin("cc_card", "c.id_card", "cc_card.id")
    ->where([
        "c.status" => 1,
        ["c.startingdate", "<=", $now],
        ["c.expirationdate", ">", $now],
        "c.$name_day" => 1,
        ["c.daily_start_time", "<=", $now->format("H:i:s")],
        ["c.daily_stop_time", ">", $now->format("H:i:s")],
        "pn.status" => 1,
    ])
    ->get();

if (count($result_phonenumbers_all) === 0) {
    if ($verbose_level >= 1) {
        echo "[No phonenumbers to call now]\n";
    }
    write_log($logfile_cront_batch, basename(__FILE__) . ' line:' . __LINE__ . "[No phonenumbers to call now]");
    exit();
}

$nb_record = count($result_phonenumbers_all);
$nbpage = (ceil($nb_record / $group));
$balance_table = $db->table("cc_card", "c")
    ->select(["flatrate", "credit"])
    ->leftJoin("cc_card_group AS cg", "c.id_group", "cg.id")
    ->leftJoin("cc_campaignconf_cardgroup AS cc_cg", "cg.id", "cc_cg.id_card_group")
    ->leftJoin("cc_campaign_config AS cc", "cc_cg.id_campaign_config", "cc.id");
$status_table = $db->table("cc_campaign_phonestatus")->select(["status", "lastuse"]);
// BROWSE THROUGH THE CARD TO APPLY THE CHECK ACCOUNT SERVICE
for ($page = 0; $page < $nbpage; $page++) {

    $result_phonenumbers = $result_phonenumbers_all->slice($page * $group, $group);

    foreach ($result_phonenumbers as $phone) {

        if ($verbose_level >= 1) {
            print_r($phone);
        }

        // check the balance
        $result_balance = $balance_table->clone()->where("c.id", $phone["cc_card_id"])->first();

        if ($result_balance) {
            if ($result_balance["credit"] < $result_balance["flatrate"]) {
                write_log($logfile_cront_batch, basename(__FILE__) . ' line:' . __LINE__ . "[ user $phone[username] don't have engouh credit ]");
                if ($verbose_level >= 1) {
                    echo "\n[ Error : Can't send callback -> user $phone[username] don't have enough credit ]";
                }
                continue;
            }

        } else {
            write_log($logfile_cront_batch, basename(__FILE__) . ' line:' . __LINE__ . "[ user $phone[username] don't have a group correctly defined ]");
            if ($verbose_level >= 1) {
                echo "\n[ Error : Can't send callback -> user $phone[username] don't have a group correctly defined ]";
            }
            continue;
        }

        //test if you have to inject it again
        $result_search_phonestatus = $status_table->clone()
            ->where(["id_campaign" => $phone["cc_campaign_id"], "id_phonenumber" => $phone["cc_phonenumber_id"]])
            ->first();

        if ($verbose_level >= 1) {
            echo "\nSEARCH PHONESTATUS RESULT : " . print_r($result_search_phonestatus, 1);
        }

        //check callback spool
        $action = '';
        if ($result_search_phonestatus) {
            $lastuse = DateTimeImmutable::createFromFormat("Y-m-d H:i:s", $result_search_phonestatus["lastuse"]);
            $action = "update";
            //Filter phone number holded and stoped
            if ($result_search_phonestatus["status"] == 1 || $result_search_phonestatus["status"] == 2) {
                continue;
            }
            $interval = new DateInterval("P{$phone["frequency"]}M");
            if ($lastuse >= $now->sub($interval)) {
                if ($verbose_level >= 1) {
                    echo "\n[  Can't send callback -> number $phone[number] is not in the frequency ]";
                }
                continue;
            }

        } else {
            $action = "insert";
        }

        // Search Road...
        $A2B->cardnumber = $phone["username"];
        $error_msg = '';

        if ($A2B->callingcard_ivr_authenticate_light($error_msg)) {

            $RateEngine = $A2B->rateEngine();
            // LOOKUP RATE : FIND A RATE FOR THIS DESTINATION

            $A2B->agiconfig['accountcode'] = $phone["username"];
            $A2B->agiconfig['use_dnid'] = 1;
            $A2B->agiconfig['say_timetocall'] = 0;

            $A2B->dnid = $A2B->destination = $phone["number"];

            $resfindrate = $RateEngine->rate_engine_findrates($phone["number"], (int)$phone["tariff"]);

            // IF FIND RATE
            if ($resfindrate != 0) {
                $res_all_calcultimeout = $RateEngine->rate_engine_all_calcultimeout($A2B->credit);
                if ($res_all_calcultimeout) {

                    // MAKE THE CALL
                    if ($RateEngine->ratecard_obj[0]["rt_id_trunk"] != '-1') {
                        $usetrunk_prefix = "rt";
                        $RateEngine->usedtrunk = $RateEngine->ratecard_obj[0]["rt_id_trunk"];
                    } else {
                        $usetrunk_prefix = "tp";
                        $RateEngine->usedtrunk = $RateEngine->ratecard_obj[0]["tp_id_trunk"];
                    }

                    $prefix = $RateEngine->ratecard_obj[0][$usetrunk_prefix . "_trunkprefix"];
                    $tech = $RateEngine->ratecard_obj[0][$usetrunk_prefix . "_providertech"];
                    $ipaddress = $RateEngine->ratecard_obj[0][$usetrunk_prefix . "_providerip"];
                    $removeprefix = $RateEngine->ratecard_obj[0][$usetrunk_prefix . "_removeprefix"];
                    $timeout = $RateEngine->ratecard_obj[0]['timeout'];
                    $failover_trunk = $RateEngine->ratecard_obj[0][$usetrunk_prefix . "_failover_trunk"];
                    $addparameter = $RateEngine->ratecard_obj[0][$usetrunk_prefix . "_addparameter_trunk"];

                    $destination = $phone["number"];
                    if (strncmp($destination, $removeprefix, strlen($removeprefix)) == 0)
                        $destination = substr($destination, strlen($removeprefix));

                    $pos_dialingnumber = strpos($ipaddress, '%dialingnumber%');
                    $ipaddress = str_replace("%cardnumber%", $A2B->cardnumber, $ipaddress);
                    $ipaddress = str_replace("%dialingnumber%", $prefix . $destination, $ipaddress);

                    $dialparams = "";
                    if ($pos_dialingnumber !== false) {
                        $dialstr = "$tech/$ipaddress" . $dialparams;
                    } elseif ($A2B->agiconfig['switchdialcommand'] == 1) {
                        $dialstr = "$tech/$prefix$destination@$ipaddress" . $dialparams;
                    } else {
                        $dialstr = "$tech/$ipaddress/$prefix$destination" . $dialparams;
                    }

                    //ADDITIONAL PARAMETER 			%dialingnumber%,	%cardnumber%
                    if (strlen($addparameter) > 0) {
                        $addparameter = str_replace("%cardnumber%", $A2B->cardnumber, $addparameter);
                        $addparameter = str_replace("%dialingnumber%", $prefix . $destination, $addparameter);
                        $dialstr .= $addparameter;
                    }

                    $channel = $dialstr;
                    $exten = 11;
                    $context = $A2B->config["callback"]['context_campaign_callback'];
                    $id_server_group = $A2B->config["callback"]['id_server_group'];
                    $priority = 1;
                    $timeout = $A2B->config["callback"]['timeout'] * 1000;
                    $application = '';
                    //default callerid
                    $callerid = '111111111';
                    $cidgroupid = $phone["id_cid_group"];
                    $callerid = $db->table("cc_outbound_cid_list")
                        ->where(["activated" => 1, "outbound_cid_group" => $cidgroupid])
                        ->inRandomOrder()
                        ->value("cid");

                    $account = Customer::card();

                    $uniqueid = generate_random_value("#####-XXXXXXX");
                    $status = 'PENDING';
                    $server_ip = 'localhost';
                    $num_attempt = 0;
                    $variable = "CALLED=$destination|USERNAME=$phone[username]|USERID=$phone[cc_card_id]|CBID=$uniqueid|PHONENUMBER_ID=" . $phone['cc_phonenumber_id'] . "|CAMPAIGN_ID=" . $phone['cc_campaign_id'];

                    $instance_table = $db->table("cc_callback_spool");
                    $values = compact("uniqueid", "status", "server_ip", "num_attempt", "channel", "exten", "context", "priority", "variable", "id_server_group", "account", "callerid");
                    $values["callback_time"] = date("Y-m-d H:i:s");
                    $values["timeout"] = 30000;
                    $res = $instance_table->insertGetId($values);

                    if (!$res) {
                        if ($verbose_level >= 1) {
                            echo "[Cannot insert the callback request in the spool!]";
                        }
                    } else {
                        if ($verbose_level >= 1) {
                            echo "[Your callback request has been queued correctly!]";
                        }

                        if ($action == "update") {
                            $res = $db->table("cc_campaign_phonestatus")
                                ->where([
                                    "id_phonenumber" => $phone["cc_phonenumber_id"],
                                    "id_campaign" => $phone["cc_campaign_id"],
                                ])
                                ->update(["id_callback" => $uniqueid, "lastuse" => $now]);
                        } else {
                            $res = $db->table("cc_campaign_phonestatus")
                                ->insert([
                                    "id_phonenumber" => $phone["cc_phonenumber_id"],
                                    "id_campaign" => $phone["cc_campaign_id"],
                                    "id_callback" => $res,
                                    "status" => 0,
                                ]);
                        }
                    }

                } elseif ($verbose_level >= 1) {
                    echo "Error : You don t have enough credit to call you back!";
                }
            } elseif ($verbose_level >= 1) {
                echo "Error : There is no route to call back your phonenumber!";
            }

        } elseif ($verbose_level >= 1) {
            echo "Error : " . $error_msg;
        }

        // End Search Road....

    }

    if ($page != $nbpage -1)
        sleep($timing);

} // End For
