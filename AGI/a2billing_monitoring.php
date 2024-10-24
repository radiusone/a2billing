#!/usr/bin/php -q
<?php

use A2billing\A2Billing;
use A2billing\Table;
use A2billing\PhpAgi\Agi;

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of A2Billing (http://www.a2billing.net/)
 *
 * A2Billing, Commercial Open Source Telecom Billing platform,
 * powered by Star2billing S.L. <http://www.star2billing.com/>
 *
 * @copyright Copyright (C) 2004-2010 - Star2billing S.L.
 * @author    Belaid Arezqui <areski@gmail.com>
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @package   A2Billing
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

declare(ticks = 1);
if (function_exists('pcntl_signal')) {
    pcntl_signal(SIGHUP, SIG_IGN);
}

error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));

require_once __DIR__ . '/../vendor/autoload.php';

$G_startime = time();
$agi = new Agi();

if ($argc > 1 && is_numeric($argv[1]) && $argv[1] >= 0) {
    $idconfig = $argv[1];
} else {
    $idconfig = 1;
}

if ($dynamic_idconfig = intval($agi->get_variable("IDCONF", true))) {
    $idconfig = $dynamic_idconfig;
}

if ($argc > 2 && strlen($argv[2]) > 0 && $argv[2] == 'saydid') {
    $mode = 'saydid';
} else {
    $mode = 'standard';
}

$A2B = new A2Billing($idconfig);
$A2B->agiconfig['verbosity_level'] = 4;
$A2B->agiconfig['logging_level'] = 0;
$A2B->debug(A2Billing::INFO, "START MORNITORING");

define("DB_TYPE", isset($A2B->config["database"]['dbtype']) ? $A2B->config["database"]['dbtype'] : null);
define("SMTP_SERVER", isset($A2B->config['global']['smtp_server']) ? $A2B->config['global']['smtp_server'] : null);
define("SMTP_HOST", isset($A2B->config['global']['smtp_host']) ? $A2B->config['global']['smtp_host'] : null);
define("SMTP_USERNAME", isset($A2B->config['global']['smtp_username']) ? $A2B->config['global']['smtp_username'] : null);
define("SMTP_PASSWORD", isset($A2B->config['global']['smtp_password']) ? $A2B->config['global']['smtp_password'] : null);

// Print header
$A2B->debug(A2Billing::DEBUG, "AGI Request:\n" . json_encode($agi->request));

/* GET THE AGI PARAMETER */
$A2B->get_agi_request_parameter();

if (!$A2B->DbConnect()) {
    $agi->stream_file('prepaid-final', '#');
    exit;
}

define("WRITELOG_QUERY", false);
$instance_table = new Table();
$A2B->set_table($instance_table);

$agi->answer();

if ($mode == 'standard') {

    //GET MONITORING SETTINGS
    $QUERY = "SELECT dial_code, label, text_intro, query_type, query, result_type FROM cc_monitor WHERE enable=1";
    $A2B->debug(A2Billing::DEBUG, "QUERY : $QUERY");
    $result = $A2B->table->SQLExec($A2B->DBHandle, $QUERY, 1, 0); // 300 ?

    foreach ($result as $res_monitor) {
        $arr_monitor[$res_monitor[0]] = array("label" => $res_monitor[1],
                                               "text_intro" => $res_monitor[2],
                                               "query_type" => $res_monitor[3],
                                               "query" => $res_monitor[4],
                                               "result_type" => $res_monitor[5]);
    }

    if (!is_array($arr_monitor)) {
        $A2B->debug(A2Billing::DEBUG, "No monitoring configuration found!");
        $agi->stream_file('prepaid-final', '#');
        exit;
    }

    for ($i = 0; $i < 10; $i++) {
        $res_dtmf = $agi->get_data('prepaid-enter-dialcode', 6000, 3);
        $A2B->debug(A2Billing::DEBUG, "RES DTMF : " . $res_dtmf["result"]);
        $dial_code = $res_dtmf["result"];

        $A2B->debug(A2Billing::DEBUG, "Dial code : $dial_code");
        if (!intval($dial_code)) {
            continue;
        }

        if (!is_array($arr_monitor[$dial_code])) {
            $agi->stream_file('prepaid-no-dialcode', '#');
            $A2B->debug(A2Billing::DEBUG, "Dial code : $dial_code not configured in monitoring");
            continue;
        }

        $agi->espeak($arr_monitor[$dial_code]["text_intro"], '#', 8000);

        # query_type : 1 SQL ; 2 for shell script
        if ($arr_monitor[$dial_code]["query_type"] == "1") {
            // SQL QUERY

            $QUERY = $arr_monitor[$dial_code]["query"];
            $A2B->debug(A2Billing::DEBUG, "QUERY : $QUERY");
            $result = $A2B->table->SQLExec($A2B->DBHandle, $QUERY, 1, 10);
            $get_result = $result[0][0];

            $A2B->debug(A2Billing::DEBUG, "SAYING RESULT");

        } elseif ($arr_monitor[$dial_code]["query_type"] == "2") {
            // SHELL SCRIPT
            $shellscript = $arr_monitor[$dial_code]["query"];

            // check for bad hack
            if (preg_match("/[:'`\/]|\.\./", $shellscript)) {
                $A2B->debug(A2Billing::DEBUG, "WRONG SHELL SCRIPT : $shellscript");
            }
            $A2B->debug(A2Billing::DEBUG, "RUNNING SHELL SCRIPT : $shellscript");
            exec(A2Billing::SCRIPT_CONFIG_DIR . $shellscript . " 2> /dev/null", $output);

            $get_result = $output[0];
        }

        $A2B->debug(A2Billing::DEBUG, "SAY RESULT (" . $arr_monitor[$dial_code]["result_type"] . "): $get_result");

        # result_type : 1 Text2Speech, 2 Date, 3 Number, 4 Digits
        if ($arr_monitor[$dial_code]["result_type"] == "1") {
            // Text2Speech
            $res_say = $agi->espeak($get_result, '#', 8000);

        } elseif ($arr_monitor[$dial_code]["result_type"] == "2") {
            // Date
            $res_say = $agi->exec("SayUnixTime " . $get_result);

        } elseif ($arr_monitor[$dial_code]["result_type"] == "3") {
            // Number
            $res_say = $agi->exec("SayNumber " . $get_result);

        } elseif ($arr_monitor[$dial_code]["result_type"] == "4") {
            // Digits
            $res_say = $agi->exec("SayDigits " . $get_result);
        }

        if (!$res_say) {
            break;
        }
    }

} elseif ($mode == 'saydid') {
    $accountcode = $agi->request['agi_accountcode'];

    $QUERY = "SELECT did FROM cc_did LEFT JOIN cc_card ON cc_card.id=cc_did.iduser WHERE cc_card.username='$accountcode'";
    $A2B->debug(A2Billing::DEBUG, "QUERY : $QUERY");
    $result = $A2B->table->SQLExec($A2B->DBHandle, $QUERY, 1, 0); // 300 ?

    if (!is_array($result) or strlen($result[0][0]) == 0) {
        $agi->espeak('There is No Phone number provisioned.', '#');
    } else {
        $did = $result[0][0];
        $agi->espeak("Your Phone number is ", '#');
        $res_say = $agi->exec("SayDigits " . $did);
    }
}

$agi->hangup();
