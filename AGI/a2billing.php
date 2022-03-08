#!/usr/bin/php -q
<?php

use A2billing\A2Billing;
use A2billing\A2bMailException;
use A2billing\Mail;
use A2billing\PhpAgi\Agi;
use A2billing\RateEngine;
use A2billing\Table;

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of A2Billing (http://www.a2billing.net/)
 *
 * A2Billing, Commercial Open Source Telecom Billing platform,
 * powered by Star2billing S.L. <http://www.star2billing.com/>
 *
 * @copyright   Copyright (C) 2004-2015 - Star2billing S.L.
 * @author      Belaid Arezqui <areski@gmail.com>
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

declare(ticks=1);
if (function_exists('pcntl_signal')) {
    pcntl_signal(SIGHUP, SIG_IGN);
}

error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));

require_once __DIR__ . '/../vendor/autoload.php';

$charge_callback = 0;
$G_startime = time();
$agi_version = "A2Billing - v2.2.0";

if ($argc > 1 && ($argv[1] == '--version' || $argv[1] == '-v')) {
    echo "$agi_version\n";
    exit;
}

$agi = new Agi();

$optconfig = [];
if ($argc > 1 && strstr($argv[1], "+")) {
    /*
    This change allows some configuration overrides on the AGI command-line by allowing the user to add them after the configuration number, like so:
    exten => 0312345678, 3, AGI(a2billing.php, "1+use_dnid=0&extracharge_did=12345")
    */
    //check for configuration overrides in the first argument
    $idconfig = substr($argv[1], 0, strpos($argv[1], "+"));
    $configstring = substr($argv[1], strpos($argv[1], "+") + 1);

    foreach (explode("&", $configstring) as $conf) {
        $var = substr($conf, 0, strpos($conf, "="));
        $val = substr($conf, strpos($conf, "=") + 1);
        $optconfig[$var] = $val;
    }
} elseif ($argc > 1 && is_numeric($argv[1]) && $argv[1] >= 0) {
    $idconfig = $argv[1];
} else {
    $idconfig = 1;
}

if ($dynamic_idconfig = intval($agi->get_variable("IDCONF", true))) {
    $idconfig = $dynamic_idconfig;
}

$mode = $argv[2] ?? "standard";

// get the area code for the cid-callback, all-callback and cid-prompt-callback
$caller_areacode = $argv[3] ?? null;

$A2B = new A2Billing();
$A2B->load_conf($agi, null, 0, $idconfig, $optconfig);
$A2B->mode = $mode;
$A2B->G_startime = $G_startime;

$groupid = $argv[4] ?? null;
if ($groupid) {
    $A2B->group_mode = true;
    $A2B->group_id = $groupid;
}

$cid_1st_leg_tariff_id = $argv[5] ?? null;

$A2B->debug(A2Billing::INFO, $agi, __FILE__, __LINE__, "IDCONFIG : $idconfig");
$A2B->debug(A2Billing::INFO, $agi, __FILE__, __LINE__, "MODE : $mode");

$agi->set_play_audio($A2B->config["agi-conf$idconfig"]['play_audio']);

define("DB_TYPE", $A2B->config["database"]['dbtype'] ?? null);
define("SMTP_SERVER", $A2B->config['global']['smtp_server'] ?? null);
define("SMTP_HOST", $A2B->config['global']['smtp_host'] ?? null);
define("SMTP_USERNAME", $A2B->config['global']['smtp_username'] ?? null);
define("SMTP_PASSWORD", $A2B->config['global']['smtp_password'] ?? null);

// Print header
$A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "AGI Request:\n" . json_encode($agi->request));
$A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[INFO : $agi_version]");

/* GET THE AGI PARAMETER */
$A2B->get_agi_request_parameter($agi);

if (!$A2B->DbConnect()) {
    $agi->stream_file('prepaid-final', '#');
    exit;
}

$send_reminder = false;
$callback_mode = null;
$callback_leg = null;
$callback_tariff = null;
$callback_uniqueid = null;
$callback_been_connected = false;
$called_party = null;
$calling_party = null;
$status_channel = 0;

$A2B->set_instance_table(new Table());

$RateEngine = new RateEngine();

// get some common stuff out of the way
if ($mode === "standard" || $mode === "voucher") {
    if ($A2B->agiconfig['answer_call'] == 1) {
        $A2B->debug(A2Billing::INFO, $agi, __FILE__, __LINE__, '[ANSWER CALL]');
        $agi->answer();
        $status_channel = 6;
    } else {
        $A2B->debug(A2Billing::INFO, $agi, __FILE__, __LINE__, '[NO ANSWER CALL]');
        $status_channel = 4;
    }

    $A2B->play_menulanguage($agi);
    // Play intro message
    if (strlen($A2B->agiconfig['intro_prompt']) > 0) {
        $agi->stream_file($A2B->agiconfig['intro_prompt'], '#');
    }
} elseif ($mode === "callback" || $mode === "conference-moderator" || $mode === "conference-member") {
    $called_party = $agi->get_variable("CALLED", true);
    $calling_party = $agi->get_variable("CALLING", true);
    $callback_mode = $agi->get_variable("MODE", true);
    $callback_tariff = $agi->get_variable("TARIFF", true);
    $callback_uniqueid = $agi->get_variable("CBID", true);
    $callback_leg = $agi->get_variable("LEG", true);
}

if ($mode === 'standard') {
    $cia_res = $A2B->callingcard_ivr_authenticate($agi);
    $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[TRY : callingcard_ivr_authenticate]");

    // CALL AUTHENTICATE AND WE HAVE ENOUGH CREDIT TO GO AHEAD
    if ($cia_res == 0) {
        // RE-SET THE CALLERID
        $A2B->callingcard_auto_setcallerid($agi);

        for ($i = 0; $i < $A2B->agiconfig['number_try']; $i++) {
            $RateEngine->Reinit();
            $A2B->Reinit();

            // RETRIEVE THE CHANNEL STATUS AND LOG : STATUS - CREIT - MIN_CREDIT_2CALL
            $stat_channel = $agi->channel_status($A2B->channel);
            $A2B->debug(A2Billing::INFO, $agi, __FILE__, __LINE__, '[CHANNEL STATUS : ' . $stat_channel["result"] . ' = ' . $stat_channel["data"] . ']' .
                "\n[CREDIT : " . $A2B->credit . "][CREDIT MIN_CREDIT_2CALL : " . $A2B->agiconfig['min_credit_2call'] . "]");

            // CHECK IF THE CHANNEL IS UP
            if ($A2B->agiconfig['answer_call'] == 1 && $stat_channel["result"] != $status_channel) {
                if ($A2B->set_inuse) {
                    $A2B->callingcard_acct_start_inuse($agi, 0);
                }
                $A2B->write_log("[STOP - EXIT]", 0);
                exit();
            }

            // CREATE A DIFFERENT UNIQUEID FOR EACH TRY
            if ($i > 0) {
                $A2B->uniqueid = $A2B->uniqueid + 1000000000;
            }

            if ($A2B->agiconfig['ivr_enable_locking_option'] == 1) {
                $QUERY = "SELECT block, lock_pin FROM cc_card WHERE username = '$A2B->username'";
                $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[QUERY] : " . $QUERY);
                $result = $A2B->instance_table->SQLExec($A2B->DBHandle, $QUERY);

                // Check if the locking option is enabled for this account
                if ($result[0][0] == 1 && strlen($result[0][1]) > 0) {
                    $try = 0;
                    do {
                        $return = false;
                        $res_dtmf = $agi->get_data('prepaid-enter-pin-lock', 3000, 10); //Please enter your locking code
                        if ($res_dtmf['result'] != $result[0][1]) {
                            $agi->say_digits($res_dtmf['result']);
                            if (strlen($res_dtmf['result']) > 0) {
                                $agi->stream_file('prepaid-no-pin-lock', '#');
                            }
                            $try++;
                            $return = true;
                        }
                        if ($try > 3) {
                            if ($A2B->set_inuse) {
                                $A2B->callingcard_acct_start_inuse($agi, 0);
                            }
                            $agi->hangup();
                            exit();
                        }
                    } while ($return);
                }
            }

            // Feature to switch the Callplan from a customer : callplan_deck_minute_threshold
            $A2B->deck_switch($agi);

            if (!$A2B->enough_credit_to_call() && $A2B->agiconfig['jump_voucher_if_min_credit'] == 1) {

                $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[NOTENOUGHCREDIT - Refill with vouchert]");
                $vou_res = $A2B->refill_card_with_voucher($agi, 2);
                if ($vou_res == 1) {
                    $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[ADDED CREDIT - refill_card_withvoucher Success] ");
                } else {
                    $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[NOTENOUGHCREDIT - refill_card_withvoucher fail] ");
                }
            }

            if ($A2B->agiconfig['ivr_enable_account_information'] == 1) {
                $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, " [GET ACCOUNT INFORMATION]");
                $res_dtmf = $agi->get_data('prepaid-press4-info', 5000, 1); //Press 4 to get information about your account
                if ($res_dtmf['result'] == "4") {

                    $QUERY = "SELECT UNIX_TIMESTAMP(c.lastuse) as lastuse, UNIX_TIMESTAMP(c.lock_date) as lock_date, UNIX_TIMESTAMP(c.firstusedate) as firstuse
                                FROM cc_card c
                                WHERE username = '$A2B->username'
                                LIMIT 1";
                    $result = $A2B->instance_table->SQLExec($A2B->DBHandle, $QUERY);
                    $card_info = $result[0] ?? null;
                    $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[QUERY] : " . $QUERY);

                    if (is_array($card_info)) {
                        $try = 0;
                        do {
                            $try++;
                            $return = false;

                            # INFORMATION MENU
                            $info_menu['1'] = 'prepaid-press1-listen-lastcall'; //Press 1 to listen the time and duration of the last call
                            $info_menu['2'] = 'prepaid-press2-listen-accountlocked'; //Press 2 to time and date when the account last has been locked
                            $info_menu['3'] = 'prepaid-press3-listen-firstuse'; //Press 3 to date of when the account was first in use
                            $info_menu['9'] = 'prepaid-press9-listen-exit-infomenu'; //Press 9 to exit information menu
                            $info_menu['*'] = 'prepaid-pressdisconnect'; //Press * to disconnect
                            //================================================================================================================
                            $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[INFORMATION MENU]");
                            $res_dtmf = $agi->menu($info_menu, 5000);

                            switch ($res_dtmf) {
                                case 1 :
                                    $QUERY = "SELECT starttime FROM cc_call
                                    WHERE card_id = $A2B->id_card ORDER BY starttime DESC LIMIT 1";
                                    $result = $A2B->instance_table->SQLExec($A2B->DBHandle, $QUERY);
                                    $lastcall_info = $result[0] ?? null;
                                    if (is_array($lastcall_info)) {
                                        $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[INFORMATION MENU]:[OPTION 1]");
                                        $agi->stream_file('prepaid-lastcall', '#'); //Your last call was made
                                        $agi->exec("SayUnixTime {$card_info['lastuse']}");
                                        $agi->stream_file('prepaid-call-duration', '#'); //the duration of the call was
                                        $agi->say_number($card_info['sessiontime']);
                                        $agi->stream_file('seconds', '#');
                                    } else {
                                        $agi->stream_file('prepaid-no-call', '#'); //No call has been made
                                    }
                                    $return = true;
                                    break;
                                case 2 :
                                    if ($card_info['lock_date']) {
                                        $agi->stream_file('prepaid-account-has-locked', '#'); //Your Account has been locked the
                                        $agi->exec("SayUnixTime {$card_info['lock_date']}");
                                    } else {
                                        $agi->stream_file('prepaid-account-nolocked', '#'); //Your account is not locked
                                    }
                                    $return = true;
                                    break;
                                case 3 :
                                    $agi->stream_file('prepaid-account-firstused', '#'); //Your Account has been used for the first time the
                                    $agi->exec("SayUnixTime {$card_info['firstuse']}");
                                    $return = true;
                                    break;
                                case 9 :
                                    break;
                                case '*' :
                                    $agi->stream_file('prepaid-final', '#');
                                    if ($A2B->set_inuse) {
                                        $A2B->callingcard_acct_start_inuse($agi, 0);
                                    }
                                    $agi->hangup();
                                    exit();
                            }
                            $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[TRY : $try]");
                        } while ($return && $try < 0);
                    }
                }
            }

            if ($A2B->agiconfig['ivr_enable_locking_option'] == 1) {
                $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[LOCKING OPTION]");

                $return = false;
                $res_dtmf = $agi->get_data('prepaid-press5-lock', 5000, 1); //Press 5 to lock your account

                if ($res_dtmf['result'] == 5) {
                    for ($ind_lock = 0; $ind_lock <= 3; $ind_lock++) {
                        $res_dtmf = $agi->get_data('prepaid-enter-code-lock-account', 3000, 10); //Please, Enter the code you want to use to lock your
                        $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[res_dtmf = " . $res_dtmf['result'] . "]");

                        if (($res_dtmf['result'] ?? 0) > 99) {
                            break;
                        }
                    }

                    if (($res_dtmf['result'] ?? 0) > 99) {

                        $agi->stream_file('prepaid-your-locking-is', '#'); //Your locking code is
                        $agi->say_digits($res_dtmf['result']);
                        $lock_pin = $res_dtmf['result'];

                        # MENU OF LOCK
                        $lock_menu['1'] = 'prepaid-listen-press1-confirmation-lock'; //Do you want to proceed and lock your account, then press 1 ?
                        $lock_menu['9'] = 'prepaid-press9-listen-exit-lockmenu'; //Press 9 to exit lock menu
                        $lock_menu['*'] = 'prepaid-pressdisconnect'; //Press * to disconnect
                        //================================================================================================================
                        $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[MENU OF LOCK]");
                        $res_dtmf = $agi->menu($lock_menu, 5000);

                        switch ($res_dtmf) {
                            case 1 :
                                $QUERY = "UPDATE cc_card SET block = 1, lock_pin = '$lock_pin', lock_date = NOW() WHERE username = '$A2B->username'";
                                $A2B->instance_table->SQLExec($A2B->DBHandle, $QUERY);
                                $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[QUERY]:[$QUERY]");
                                $agi->stream_file('prepaid-locking-accepted', '#'); // Your locking code has been accepted
                                $return = true;
                                break;
                            case 9 :
                                break;
                            case '*' :
                                $agi->stream_file('prepaid-final', '#');
                                if ($A2B->set_inuse) {
                                    $A2B->callingcard_acct_start_inuse($agi, 0);
                                }
                                $agi->hangup();
                                exit();
                        }
                    }
                }
            }

            $A2B->debug(A2Billing::INFO, $agi, __FILE__, __LINE__, "TARIFF ID->" . $A2B->tariff);

            if (!$A2B->enough_credit_to_call()) {
                // SAY TO THE CALLER THAT IT DEOSNT HAVE ENOUGH CREDIT TO MAKE A CALL
                $prompt = "prepaid-no-enough-credit-stop";
                $agi->stream_file($prompt, '#');
                $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[STOP STREAM FILE $prompt]");

                if (($A2B->agiconfig['notenoughcredit_cardnumber'] == 1) && (($i + 1) < $A2B->agiconfig['number_try'])) {

                    if ($A2B->set_inuse) {
                        $A2B->callingcard_acct_start_inuse($agi, 0);
                    }

                    $A2B->agiconfig['cid_enable'] = 0;
                    $A2B->agiconfig['use_dnid'] = 0;
                    $A2B->agiconfig['cid_auto_assign_card_to_cid'] = 0;
                    $A2B->accountcode = '';
                    $A2B->username = '';
                    $A2B->ask_other_cardnumber = 1;

                    $cia_res = $A2B->callingcard_ivr_authenticate($agi);
                    $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[NOTENOUGHCREDIT_CARDNUMBER - TRY : callingcard_ivr_authenticate]");
                    if ($cia_res != 0) {
                        break;
                    }

                    $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[NOTENOUGHCREDIT_CARDNUMBER - callingcard_acct_start_inuse]");
                    $A2B->callingcard_acct_start_inuse($agi, 1);
                    continue;

                } else {
                    $send_reminder = true;
                    $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[SET MAIL REMINDER - NOT ENOUGH CREDIT]");
                    break;
                }
            }

            $A2B->dnid = $A2B->orig_dnid;
            $A2B->extension = $A2B->orig_ext;

            if ($A2B->agiconfig['ivr_voucher'] == 1) {
                $res_dtmf = $agi->get_data('prepaid-refill_card_with_voucher', 5000, 1);
                $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "RES REFILL CARD VOUCHER DTMF : " . $res_dtmf["result"]);
                $A2B->ivr_voucher = $res_dtmf["result"] ?? null;
                if ($A2B->ivr_voucher == $A2B->agiconfig['ivr_voucher_prefixe']) {
                    $vou_res = $A2B->refill_card_with_voucher($agi, $i);
                }
            }

            if ($A2B->agiconfig['ivr_enable_ivr_speeddial'] == 1) {
                $A2B->debug(A2Billing::INFO, $agi, __FILE__, __LINE__, "[IVR SPEED DIAL]");
                do {
                    $return_mainmenu = false;

                    $res_dtmf = $agi->get_data("prepaid-press9-new-speeddial", 5000, 1); //Press 9 to add a new Speed Dial

                    if (($res_dtmf["result"] ?? null) == 9) {
                        $try_enter_speeddial = 0;
                        do {
                            $try_enter_speeddial++;
                            $return_enter_speeddial = false;
                            $res_dtmf = $agi->get_data("prepaid-enter-speeddial", 3000, 1); //Please enter the speeddial number
                            $speeddial_number = $res_dtmf['result'] ?? 0;
                            $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "SPEEDDIAL DTMF : " . $speeddial_number);

                            if ((int)$speeddial_number >= 0) {
                                $action = 'insert';
                                $QUERY = "SELECT cc_speeddial.phone, cc_speeddial.id
                                            FROM cc_speeddial, cc_card WHERE cc_speeddial.id_cc_card = cc_card.id
                                            AND cc_card.id = " . $A2B->id_card . " AND cc_speeddial.speeddial = " . $speeddial_number;
                                $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, $QUERY);
                                $result = $A2B->instance_table->SQLExec($A2B->DBHandle, $QUERY);
                                $id_speeddial = $result[0][1];
                                if (is_array($result)) {
                                    $agi->say_number($speeddial_number);
                                    $agi->stream_file("prepaid-is-used-for", "#");
                                    $agi->say_digits($result[0][0]);
                                    $res_dtmf = $agi->get_data("prepaid-press1-change-speeddial", 3000, 1); //if you want to change it press 1 or an other key to enter an other speed dial number.
                                    if ($res_dtmf['result'] != 1) {
                                        $return_mainmenu = true;
                                        break;
                                    } else {
                                        $action = 'update';
                                    }
                                }
                                $try_phonenumber = 0;
                                do {
                                    $try_phonenumber++;
                                    $return_phonenumber = false;
                                    $res_dtmf = $agi->get_data("prepaid-phonenumber-to-speeddial", 5000, 30); //Please enter the phone number followed by the pound key
                                    $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "PHONENUMBER TO SPEEDDIAL DTMF : " . $res_dtmf['result']);

                                    if ((int)($res_dtmf["result"] ?? 0) > 0) {
                                        break;
                                    }

                                    if ($try_phonenumber < 3) {
                                        $return_phonenumber = true;
                                    } else {
                                        $return_mainmenu = true;
                                    }

                                } while ($return_phonenumber);

                                $assigned_number = $res_dtmf["result"] ?? 0;
                                if ((int)$assigned_number > 0) {
                                    $agi->stream_file("prepaid-the-phonenumber", "#"); //The phone number
                                    $agi->say_digits($assigned_number, "#");
                                    $agi->stream_file("prepaid-assigned-speeddial", "#"); //will be assigned to the speed dial number
                                    $agi->say_number($speeddial_number, "#");

                                    $res_dtmf = $agi->get_data("prepaid-press1-add-speeddial", 3000, 1); //If you want to proceed please press 1 or press an other key to cancel ?
                                    if (($res_dtmf['result'] ?? 0) == 1) {
                                        $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "ACTION : " . $action);
                                        if ($action == 'insert') {
                                            $QUERY = "INSERT INTO cc_speeddial (id_cc_card, phone, speeddial) VALUES (" . $A2B->id_card . ", " . $assigned_number . ", '" . $speeddial_number . "')";
                                        } else {
                                            $QUERY = "UPDATE cc_speeddial SET phone = '" . $assigned_number . "' WHERE id = " . $id_speeddial;
                                        }

                                        $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, $QUERY);
                                        $result = $A2B->instance_table->SQLExec($A2B->DBHandle, $QUERY);
                                        $agi->stream_file("prepaid-speeddial-saved"); //The speed dial number has been successfully saved.
                                        $return_mainmenu = true;
                                        break;
                                    }
                                }
                            }

                            if ($try_enter_speeddial < 3) {
                                $return_enter_speeddial = true;
                            } else {
                                $return_mainmenu = true;
                            }
                        } while ($return_enter_speeddial);
                    }
                } while ($return_mainmenu);
            }

            if ($A2B->agiconfig['sip_iax_friends'] == 1) {

                if ($A2B->agiconfig['sip_iax_pstn_direct_call'] == 1) {

                    if ($A2B->agiconfig['use_dnid'] == 1 && !in_array($A2B->dnid, $A2B->agiconfig['no_auth_dnid']) && strlen($A2B->dnid) > 2 && $i == 0) {

                        $A2B->destination = $A2B->dnid;

                    } elseif ($i == 0) {
                        $prompt_enter_dest = $A2B->agiconfig['file_conf_enter_destination'];
                        $res_dtmf = $agi->get_data($prompt_enter_dest, 4000, 20);
                        $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "RES sip_iax_pstndirect_call DTMF : " . $res_dtmf["result"]);
                        $A2B->destination = $res_dtmf["result"];
                    }

                    if (
                        strlen($A2B->destination)
                        && strlen($A2B->agiconfig['sip_iax_pstn_direct_call_prefix'])
                        && str_starts_with($A2B->destination, $A2B->agiconfig['sip_iax_pstn_direct_call_prefix'])
                    ) {
                        $A2B->dnid = $A2B->destination;
                        $A2B->sip_iax_buddy = $A2B->agiconfig['sip_iax_pstn_direct_call_prefix'];
                        $A2B->agiconfig['use_dnid'] = 1;
                        $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "SIP 1. IAX - dnid : " . $A2B->dnid . " - " . strlen($A2B->agiconfig['sip_iax_pstn_direct_call_prefix']));
                        $A2B->dnid = substr($A2B->dnid, strlen($A2B->agiconfig['sip_iax_pstn_direct_call_prefix']));
                        $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "SIP 2. IAX - dnid : " . $A2B->dnid);

                    } elseif (strlen($A2B->destination)) {
                        $A2B->dnid = $A2B->destination;
                        $A2B->agiconfig['use_dnid'] = 1;
                        $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "TRUNK - dnid : " . $A2B->dnid . " (" . $A2B->agiconfig['use_dnid'] . ")");
                    }
                } else {
                    $res_dtmf = $agi->get_data('prepaid-sipiax-press9', 4000, 1);
                    $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "RES SIP_IAX_FRIEND DTMF : " . $res_dtmf["result"]);
                    $A2B->sip_iax_buddy = $res_dtmf["result"];
                }
            }

            if (strlen($A2B->sip_iax_buddy) > 0 || ($A2B->sip_iax_buddy == $A2B->agiconfig['sip_iax_pstn_direct_call_prefix'])) {

                $A2B->debug(A2Billing::INFO, $agi, __FILE__, __LINE__, 'CALL SIP_IAX_BUDDY');
                $cia_res = $A2B->call_sip_iax_buddy($agi, $RateEngine, $i);

            } else {

                $ans = $A2B->callingcard_ivr_authorize($agi, $RateEngine, $i, true);
                $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, 'ANSWER fct callingcard_ivr authorize:> ' . $ans);

                if ($ans == 1) {
                    attempt_call($A2B, $RateEngine, $agi);
                } elseif ($ans == "2DID") {

                    $A2B->debug(A2Billing::INFO, $agi, __FILE__, __LINE__, "[ CALL OF THE SYSTEM - [DID=" . $A2B->destination . "]");

                    $QUERY = "SELECT cc_did.id, cc_did_destination.id, billingtype, tariff, destination, voip_call, username, useralias, connection_charge, " .
                        " selling_rate, did, aleg_carrier_connect_charge, aleg_carrier_cost_min, aleg_retail_connect_charge, aleg_retail_cost_min, " .
                        " aleg_carrier_initblock, aleg_carrier_increment, aleg_retail_initblock, aleg_retail_increment, " .
                        " aleg_timeinterval, " .
                        " aleg_carrier_connect_charge_offp, aleg_carrier_cost_min_offp, aleg_retail_connect_charge_offp, aleg_retail_cost_min_offp, " .
                        " aleg_carrier_initblock_offp, aleg_carrier_increment_offp, aleg_retail_initblock_offp, aleg_retail_increment_offp, " .
                        " cc_card.id " .
                        " FROM cc_did, cc_did_destination, cc_card " .
                        " WHERE id_cc_did=cc_did.id AND cc_card.status=1 AND cc_card.id=id_cc_card and cc_did_destination.activated=1 " .
                        " AND cc_did.activated=1 AND did='" . $A2B->destination . "' " .
                        " AND cc_did.startingdate <= CURRENT_TIMESTAMP " .
                        " AND (cc_did.expirationdate > CURRENT_TIMESTAMP OR cc_did.expirationdate IS NULL " .
                        " AND cc_did_destination.validated = 1 ";
                    if ($A2B->config["database"]['dbtype'] == "mysql") {
                        $QUERY .= " OR cc_did.expirationdate = '0000-00-00 00:00:00'";
                    }
                    $QUERY .= ") ORDER BY priority ASC";

                    $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, $QUERY);
                    $result = $A2B->instance_table->SQLExec($A2B->DBHandle, $QUERY);

                    if (is_array($result)) {
                        //On Net
                        $A2B->call_2did($agi, $RateEngine, $result);
                        if ($A2B->set_inuse) {
                            $A2B->callingcard_acct_start_inuse($agi, 0);
                        }
                    }
                }
            }
            $A2B->agiconfig['use_dnid'] = 0;
        }//END FOR

    } else {
        $A2B->debug(A2Billing::WARN, $agi, __FILE__, __LINE__, "[NO AUTH (CN:" . $A2B->accountcode . ", cia_res:" . $cia_res . ", CREDIT:" . $A2B->credit . ")]");
    }
    # SAY GOODBYE
    if ($A2B->agiconfig['say_goodbye'] == 1) {
        $agi->stream_file('prepaid-final', '#');
    }

// MODE DID
} elseif ($mode == 'did') {

    if ($A2B->agiconfig['answer_call'] == 1) {
        $A2B->debug(A2Billing::INFO, $agi, __FILE__, __LINE__, '[ANSWER CALL]');
        $agi->answer();
    } else {
        $A2B->debug(A2Billing::INFO, $agi, __FILE__, __LINE__, '[NO ANSWER CALL]');
    }

    $RateEngine->Reinit();
    $A2B->Reinit();

    $mydnid = $A2B->orig_ext;

    if (strlen($mydnid) > 0) {
        $A2B->debug(A2Billing::INFO, $agi, __FILE__, __LINE__, "[DID CALL - [CallerID=" . $A2B->CallerID . "]:[DID=" . $mydnid . "]");

        $QUERY = "SELECT cc_did.id, cc_did_destination.id, billingtype, tariff, destination, voip_call, username, useralias, connection_charge, " .
            " selling_rate, did, aleg_carrier_connect_charge, aleg_carrier_cost_min, aleg_retail_connect_charge, aleg_retail_cost_min, " .
            " aleg_carrier_initblock, aleg_carrier_increment, aleg_retail_initblock, aleg_retail_increment, " .
            " aleg_timeinterval, " .
            " aleg_carrier_connect_charge_offp, aleg_carrier_cost_min_offp, aleg_retail_connect_charge_offp, aleg_retail_cost_min_offp, " .
            " aleg_carrier_initblock_offp, aleg_carrier_increment_offp, aleg_retail_initblock_offp, aleg_retail_increment_offp " .
            " FROM cc_did, cc_did_destination, cc_card " .
            " WHERE id_cc_did=cc_did.id AND cc_card.status=1 AND cc_card.id=id_cc_card AND cc_did_destination.activated=1 " .
            " AND cc_did.activated=1 AND did='$mydnid' " .
            " AND cc_did.startingdate<= CURRENT_TIMESTAMP AND (cc_did.expirationdate > CURRENT_TIMESTAMP OR cc_did.expirationdate IS NULL " .
            " AND cc_did_destination.validated=1";
        if ($A2B->config["database"]['dbtype'] != "postgres") {
            // MYSQL
            $QUERY .= " OR cc_did.expirationdate = '0000-00-00 00:00:00'";
        }
        $QUERY .= ") ORDER BY priority ASC";

        $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, $QUERY);
        $result = $A2B->instance_table->SQLExec($A2B->DBHandle, $QUERY);
        $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, $result);

        if (is_array($result)) {
            //Off Net
            $A2B->call_did($agi, $RateEngine, $result);
            if ($A2B->set_inuse) {
                $A2B->callingcard_acct_start_inuse($agi, 0);
            }
        }
    }

// MOVE VOUCHER TO LET CUSTOMER ONLY REFILL
} elseif ($mode == 'voucher') {
    if (strlen($A2B->CallerID) > 1 && is_numeric($A2B->CallerID)) {
        $A2B->CallerID = $caller_areacode . $A2B->CallerID;
    }
    $cia_res = $A2B->callingcard_ivr_authenticate($agi);
    $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[TRY : callingcard_ivr_authenticate]");

    // CALL AUTHENTICATE AND WE HAVE ENOUGH CREDIT TO GO AHEAD
    if ($A2B->id_card > 0) {
        for ($k = 0; $k < 3; $k++) {
            $vou_res = $A2B->refill_card_with_voucher($agi, null);
            $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "VOUCHER RESULT = $vou_res");
            if ($vou_res == 1) {
                break;
            } else {
                $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[NOTENOUGHCREDIT - refill_card_withvoucher fail] ");
            }
        }
    }

    // SAY GOODBYE
    if ($A2B->agiconfig['say_goodbye'] == 1) {
        $agi->stream_file('prepaid-final', '#');
    }

    $agi->hangup();
    if ($A2B->set_inuse) {
        $A2B->callingcard_acct_start_inuse($agi, 0);
    }
    $A2B->write_log("[STOP - EXIT]", 0);
    exit();

// MODE CAMPAIGN-CALLBACK
} elseif ($mode == 'campaign-callback') {
    $A2B->update_callback_campaign($agi);

// MODE cid-callback & cid-prompt-callback
} elseif ($mode === 'cid-callback' || $mode === 'cid-prompt-callback') {

    $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, '[MODE : ' . strtoupper($mode) . ' - ' . $A2B->CallerID . ']');

    if ($A2B->agiconfig['answer_call'] == 1 && $mode == 'cid-callback') {
        $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, '[HANGUP CLI CALLBACK TRIGGER]');
        $agi->hangup();
    } elseif ($mode === 'cid-prompt-callback') {
        $agi->answer();
    } else {
        $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, '[CLI CALLBACK TRIGGER RINGING]');
    }

    // MAKE THE AUTHENTICATION ACCORDING TO THE CALLERID
    $A2B->agiconfig['cid_enable'] = 1;
    $A2B->agiconfig['cid_askpincode_ifnot_callerid'] = 0;
    $A2B->agiconfig['say_balance_after_auth'] = 0;

    if (strlen($A2B->CallerID) > 1 && is_numeric($A2B->CallerID)) {

        /* WE START ;) */
        $cia_res = $A2B->callingcard_ivr_authenticate($agi);
        $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[TRY : callingcard_ivr_authenticate]");
        if ($cia_res == 0) {

            $RateEngine = new RateEngine();

            // Apply 1st leg tariff override if param was passed in
            if (strlen($cid_1st_leg_tariff_id) > 0) {
                $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, 'Callback Tariff override for 1st Leg only. New tariff is ' . $cid_1st_leg_tariff_id);
                $A2B->tariff = $cid_1st_leg_tariff_id;
            }

            $A2B->agiconfig['use_dnid'] = 1;
            $A2B->agiconfig['say_timetocall'] = 0;

            // We arent removing leading zero in front of the callerID if needed this might be done over the dialplan
            $A2B->extension = $A2B->dnid = $A2B->destination = $caller_areacode . $A2B->CallerID;

            $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, '[destination: - ' . $A2B->destination . ']');

            // LOOKUP RATE : FIND A RATE FOR THIS DESTINATION
            $resfindrate = $RateEngine->rate_engine_findrates($A2B, $A2B->destination, $A2B->tariff);
            $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, '[resfindrate: - ' . $resfindrate . ']');

            // IF FIND RATE
            if ($resfindrate) {
                $res_all_calcultimeout = $RateEngine->rate_engine_all_calcultimeout($A2B, $A2B->credit);
                if ($res_all_calcultimeout) {
                    $CALLING_VAR = '';
                    $MODE_VAR = "MODE=CID";
                    if ($mode == 'cid-prompt-callback') {
                        $MODE_VAR = "MODE=CID-PROMPT";

                        $try = 0;
                        do {
                            $try++;
                            $return = true;

                            // GET THE DESTINATION NUMBER
                            $prompt_enter_dest = $A2B->agiconfig['file_conf_enter_destination'];
                            $res_dtmf = $agi->get_data($prompt_enter_dest, 6000, 20);
                            $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "RES DTMF : " . $res_dtmf["result"]);
                            $outbound_destination = $res_dtmf["result"];

                            if ($A2B->agiconfig['cid_prompt_callback_confirm_phonenumber'] == 1) {
                                $agi->stream_file('prepaid-the-number-u-dialed-is', '#');
                                $agi->say_digits($outbound_destination);

                                $subtry = 0;
                                do {
                                    $subtry++;
                                    //= CONFIRM THE DESTINATION NUMBER
                                    $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[MENU OF CONFIRM (" . $res_dtmf["result"] . ")]");
                                    $res_dtmf = $agi->get_data('prepaid-re-enter-press1-confirm', 4000, 1);
                                    if ($subtry >= 3) {
                                        if ($A2B->set_inuse) {
                                            $A2B->callingcard_acct_start_inuse($agi, 0);
                                        }
                                        $agi->hangup();
                                        exit();
                                    }
                                } while ($res_dtmf["result"] != '1' && $res_dtmf["result"] != '2');

                                // Check the result, it has to be 1 or 2 now
                                $return = ($res_dtmf["result"] == '1');

                                $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[TRY : $try]");
                            } else {
                                $return = false;
                            }
                        } while ($return && $try < 3);

                        if (!strlen($outbound_destination)) {
                            if ($A2B->set_inuse) {
                                $A2B->callingcard_acct_start_inuse($agi, 0);
                            }
                            $agi->hangup();
                            exit();
                        }

                        $CALLING_VAR = "CALLING=" . $outbound_destination;
                    } // if ($mode == 'cid-prompt-callback')

                    $channel = get_dialstring($RateEngine->ratecard_obj[0], $A2B, $RateEngine);
                    $exten = $groupid;
                    $uniqueid = MDP_NUMERIC(5) . '-' . MDP_STRING(7);
                    $variable = sprintf(
                        "IDCONF=%s,CALLED=%s,%s,%s,CBID=%s,LEG=%s",
                        $idconfig, $A2B->destination, $CALLING_VAR, $MODE_VAR, $uniqueid, $A2B->username
                    );

                    $callbackrate = $RateEngine->ratecard_obj[0]['callbackrate'] ?? [];
                    foreach ($callbackrate as $key => $value) {
                        $variable .= "," . strtoupper($key) . '=' . $value;
                    }
                    //pass the tariff if it was passed in
                    if (strlen($cid_1st_leg_tariff_id) > 0) {
                        $variable .= ',TARIFF=' . $cid_1st_leg_tariff_id;
                    }
                    insert_callback($A2B, $agi, $uniqueid, $channel, $variable, $exten);

                } else {
                    $error_msg = 'Error : You don t have enough credit to call you back !!!';
                    $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[CALLBACK-CALLERID : CALLED=" . $A2B->destination . " | $error_msg]");
                }

            } else {
                $error_msg = 'Error : There is no route to call back your phonenumber !!!';
                $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[CALLBACK-CALLERID : CALLED=" . $A2B->destination . " | $error_msg]");
            }

        } else {
            $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[CALLBACK-CALLERID : CALLED=" . $A2B->destination . " | Authentication failed]");
        }

    } else {
        $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[CALLBACK-CALLERID : CALLED=" . $A2B->destination . " | error callerid]");
    }

} elseif ($mode == 'all-callback') {

    $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, '[MODE : ALL-CALLBACK - ' . $A2B->CallerID . ']');

    // END
    if ($A2B->agiconfig['answer_call'] == 1) {
        $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, '[HANGUP ALL CALLBACK TRIGGER]');
        $agi->hangup();
    } else {
        $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, '[ALL CALLBACK TRIGGER RINGING]');
    }

    $A2B->credit = 1000;
    $A2B->tariff = $A2B->config["callback"]['all_callback_tariff'];

    if (strlen($A2B->CallerID) > 1 && is_numeric($A2B->CallerID)) {

        /* WE START ;) */
        // removed if ($cia_res == 0) because $cia_res was never defined as far back as 2007
        // https://github.com/Star2Billing/a2billing/blob/9d042c34e7d85ddf8abd6f1da2cbe2d8c02ab854/A2Billing_AGI/a2billing.php

        $RateEngine = new RateEngine();
        // $RateEngine->webui = 0;
        // LOOKUP RATE : FIND A RATE FOR THIS DESTINATION

        $A2B->agiconfig['use_dnid'] = 1;
        $A2B->agiconfig['say_timetocall'] = 0;
        $A2B->agiconfig['say_balance_after_auth'] = 0;
        $A2B->extension = $A2B->dnid = $A2B->destination = $caller_areacode . $A2B->CallerID;

        $resfindrate = $RateEngine->rate_engine_findrates($A2B, $A2B->destination, $A2B->tariff);

        // IF FIND RATE
        if ($resfindrate != 0) {
            //$RateEngine->debug_st = 1;
            $res_all_calcultimeout = $RateEngine->rate_engine_all_calcultimeout($A2B, $A2B->credit);

            if ($res_all_calcultimeout) {
                // MAKE THE CALL
                $channel = get_dialstring($RateEngine->ratecard_obj[0], $A2B, $RateEngine);
                $exten = $groupid;
                $uniqueid = MDP_NUMERIC(5) . '-' . MDP_STRING(7);
                $variable = sprintf(
                    "IDCONF=%s,CALLED=%s,MODE=ALL,CBID=%s,TARIFF=%s,LEG=%s",
                    $idconfig, $A2B->destination, $uniqueid, $A2B->tariff, $A2B->username
                );

                insert_callback($A2B, $agi, $uniqueid, $channel, $variable, $exten);

            } else {
                $error_msg = 'Error : You don t have enough credit to call you back !!!';
                $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[CALLBACK-CALLERID : CALLED=" . $A2B->destination . " | $error_msg]");
            }
        } else {
            $error_msg = 'Error : There is no route to call back your phonenumber !!!';
            $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[CALLBACK-CALLERID : CALLED=" . $A2B->destination . " | $error_msg]");
        }
    } else {
        $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[CALLBACK-CALLERID : CALLED=" . $A2B->destination . " | error callerid]");
    }

// MODE CALLBACK
} elseif ($mode === "callback") {

    $callback_been_connected = 0;

    $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, '[CALLBACK]:[MODE : CALLBACK]');

    if ($A2B->config["callback"]['answer_call'] == 1) {
        $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, '[CALLBACK]:[ANSWER CALL]');
        $agi->answer();
        $status_channel = 6;
        $A2B->play_menulanguage($agi);

        // PLAY INTRO FOR CALLBACK
        if (strlen($A2B->config["callback"]['callback_audio_intro']) > 0) {
            $agi->stream_file($A2B->config["callback"]['callback_audio_intro'], '#');
        }
    } else {
        $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, '[CALLBACK]:[NO ANSWER CALL]');
        $status_channel = 4;
        $A2B->play_menulanguage($agi);
    }

    // |MODEFROM=ALL-CALLBACK|TARIFF=" . $A2B->tariff;
    $A2B->extension = $A2B->dnid = $A2B->destination = $calling_party;

    if ($callback_mode == 'CID') {
        $charge_callback = 1;
        $A2B->agiconfig['use_dnid'] = 0;
        $A2B->CallerID = $called_party;

    } elseif ($callback_mode == 'CID-PROMPT') {
        $charge_callback = 1;
        $A2B->agiconfig['use_dnid'] = 1;
        $A2B->CallerID = $called_party;

    } elseif ($callback_mode == 'ALL') {
        $A2B->agiconfig['use_dnid'] = 0;
        $A2B->agiconfig['cid_enable'] = 0;
        $A2B->CallerID = $called_party;

    } else {
        $charge_callback = 1;
        // FOR THE WEB-CALLBACK
        $A2B->agiconfig['use_dnid'] = 1;
        $A2B->agiconfig['say_balance_after_auth'] = 0;
        $A2B->agiconfig['cid_enable'] = 0;
        $A2B->agiconfig['say_timetocall'] = 0;
    }

    if ($A2B->agiconfig['callback_beep_to_enter_destination'] == 1) {
        $A2B->callback_beep_to_enter_destination = true;
    }

    $A2B->debug(A2Billing::INFO, $agi, __FILE__, __LINE__, "[CALLBACK]:[GET VARIABLE : CALLED=$called_party | CALLING=$calling_party | MODE=$callback_mode | TARIFF=$callback_tariff | CBID=$callback_uniqueid | LEG=$callback_leg | CALLERID=" . $A2B->CallerID . "]");

    $QUERY = "UPDATE cc_callback_spool SET agi_result='AGI PROCESSING' WHERE uniqueid='$callback_uniqueid'";
    $res = $A2B->DBHandle->Execute($QUERY);
    $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[CALLBACK : UPDATE CALLBACK AGI_RESULT : QUERY=$QUERY]");


    /* WE START ;) */
    $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[CALLBACK]:[TRY : callingcard_ivr_authenticate]");
    $cia_res = $A2B->callingcard_ivr_authenticate($agi);
    if ($cia_res == 0) {

        $charge_callback = 1; // EVEN FOR ALL CALLBACK
        $callback_leg = $A2B->username;

        $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[CALLBACK]:[Start]");
        $A2B->callingcard_auto_setcallerid($agi);

        for ($i = 0; $i < $A2B->agiconfig['number_try']; $i++) {

            $RateEngine->Reinit();
            $A2B->Reinit();

            // DIVIDE THE AMOUNT OF CREDIT BY 2 IN ORDER TO AVOID NEGATIVE BALANCE IF THE USER USE ALL HIS CREDIT
            $orig_credit = $A2B->credit;

            if ($A2B->agiconfig['callback_reduce_balance'] > 0 && $A2B->credit > $A2B->agiconfig['callback_reduce_balance']) {
                $A2B->credit = $A2B->credit - $A2B->agiconfig['callback_reduce_balance'];
            } else {
                $A2B->credit = $A2B->credit / 2;
            }

            $stat_channel = $agi->channel_status($A2B->channel);
            $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, '[CALLBACK]:[CHANNEL STATUS : ' . $stat_channel["result"] . ' = ' . $stat_channel["data"] . ']' .
                "[status_channel=$status_channel]:[ORIG_CREDIT : " . $orig_credit . " - CUR_CREDIT - : " . $A2B->credit .
                " - CREDIT MIN_CREDIT_2CALL : " . $A2B->agiconfig['min_credit_2call'] . "]");

            if (!$A2B->enough_credit_to_call()) {
                // SAY TO THE CALLER THAT IT DEOSNT HAVE ENOUGH CREDIT TO MAKE A CALL
                $prompt = "prepaid-no-enough-credit-stop";
                $agi->stream_file($prompt, '#');
                $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[CALLBACK]:[STOP STREAM FILE $prompt]");
            }

            if ($A2B->callingcard_ivr_authorize($agi, $RateEngine, $i) == 1) {
                // PERFORM THE CALL
                attempt_call($A2B, $RateEngine, $agi);

                if ($RateEngine->dialstatus === "ANSWER") {
                    $callback_been_connected = 1;
                }

                $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[CALLBACK]:[a2billing end loop num_try] RateEngine->usedratecard=" . $RateEngine->usedratecard);
            }
        }//END FOR

        if ($A2B->set_inuse) {
            $A2B->callingcard_acct_start_inuse($agi, 0);
        }

    } else {
        $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[CALLBACK]:[AUTHENTICATION FAILED (cia_res:" . $cia_res . ")]");
    }

// MODE CONFERENCE MODERATOR OR MEMBER
} elseif ($mode === "conference-moderator" || $mode === "conference-member") {

    $callback_been_connected = 0;

    $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[CALLBACK]:[MODE : $mode]");

    if ($A2B->config["callback"]['answer_call'] == 1) {
        $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, '[CALLBACK]:[ANSWER CALL]');
        $agi->answer();
        $status_channel = 6;
    } else {
        $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, '[CALLBACK]:[NO ANSWER CALL]');
        $status_channel = 4;
    }

    $A2B->play_menulanguage($agi);

    $accountcode = $agi->get_variable("ACCOUNTCODE", true);
    $room_number = $agi->get_variable("ROOMNUMBER", true);
    $phonenumber_member = ($mode === 'conference-moderator') ? $agi->get_variable("PN_MEMBER", true) : "n/a";

    $A2B->debug(A2Billing::INFO, $agi, __FILE__, __LINE__, "[CALLBACK]:[GET VARIABLE : CALLED=$called_party | CALLING=$calling_party | MODE=$callback_mode | TARIFF=$callback_tariff | CBID=$callback_uniqueid | LEG=$callback_leg | ACCOUNTCODE=$accountcode | PN_MEMBER=$phonenumber_member | ROOMNUMBER=$room_number]");

    $error_settings = false;
    $room_number = intval($room_number);
    if ($room_number <= 0) {
        $error_settings = true;
    }

    if (strlen($accountcode) === 0 || ($mode === 'conference-moderator' && strlen($phonenumber_member) === 0)) {
        $error_settings = true;
        $list_pn_member = [];
    } elseif ($mode === 'conference-moderator') {
        $list_pn_member = preg_split("/[\s;]+/", $phonenumber_member);

        if (count($list_pn_member) === 0) {
            $error_settings = true;
        }
    }

    if ($error_settings) {
        $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[CALLBACK : Error settings accountcode or phonenumber_member]");
        $agi->hangup();
        $A2B->write_log("[STOP - EXIT]", 0);
        exit();
    }

    $A2B->username = $A2B->accountcode = $accountcode;
    $A2B->callingcard_acct_start_inuse($agi, 1);

    if ($callback_mode === 'CONF-MODERATOR') {
        $charge_callback = 1;
        $A2B->CallerID = $called_party;
        $A2B->agiconfig['number_try'] = 1;
        $A2B->agiconfig['use_dnid'] = 1;
        $A2B->agiconfig['say_balance_after_auth'] = 0;
        $A2B->agiconfig['cid_enable'] = 0;
        $A2B->agiconfig['say_timetocall'] = 0;
    }

    $QUERY = "UPDATE cc_callback_spool SET agi_result = 'AGI PROCESSING' WHERE uniqueid = '$callback_uniqueid'";
    $res = $A2B->DBHandle->Execute($QUERY);
    $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[CALLBACK : UPDATE CALLBACK AGI_RESULT : QUERY = $QUERY]");


    /* WE START ;) */
    $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[CALLBACK]:[TRY : callingcard_ivr_authenticate]");
    $cia_res = $A2B->callingcard_ivr_authenticate($agi);
    if ($cia_res == 0) {

        $charge_callback = 1; // EVEN FOR ALL CALLBACK
        $callback_leg = $A2B->username;

        for ($i = 0; $i < $A2B->agiconfig['number_try']; $i++) {

            $RateEngine->Reinit();
            $stat_channel = $agi->channel_status($A2B->channel);
            $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, '[CALLBACK]:[CHANNEL STATUS : ' . $stat_channel["result"] . ' = ' . $stat_channel["data"] . ']' .
                "[status_channel=$status_channel]:[CREDIT - : " . $A2B->credit . " - CREDIT MIN_CREDIT_2CALL : " . $A2B->agiconfig['min_credit_2call'] . "]");

            if (!$A2B->enough_credit_to_call()) {
                // SAY TO THE CALLER THAT IT DEOSNT HAVE ENOUGH CREDIT TO MAKE A CALL
                $prompt = "prepaid-no-enough-credit-stop";
                $agi->stream_file($prompt, '#');
                $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[CALLBACK]:[STOP STREAM FILE $prompt]");
            }

            if ($mode === 'conference-moderator') {
                // find the route and Initiate new callback for all the members
                foreach ($list_pn_member as $inst_pn_member) {
                    $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, '[CALLBACK]:[Spool Callback for the PhoneNumber ' . $inst_pn_member . ']');
                    $A2B->extension = $A2B->dnid = $A2B->destination = $inst_pn_member;

                    $resfindrate = $RateEngine->rate_engine_findrates($A2B, $A2B->destination, $A2B->tariff);

                    // IF FIND RATE
                    if ($resfindrate != 0) {
                        //$RateEngine->debug_st = 1;
                        $res_all_calcultimeout = $RateEngine->rate_engine_all_calcultimeout($A2B, $A2B->credit);

                        if ($res_all_calcultimeout) {
                            // MAKE THE CALL
                            $channel = get_dialstring($RateEngine->ratecard_obj[0], $A2B, $RateEngine);
                            $exten = $inst_pn_member;
                            $context = 'a2billing-conference-member';
                            $id_server_group = $A2B->config["callback"]['id_server_group'];
                            $callerid = $called_party;
                            $uniqueid = $callback_uniqueid . '-' . MDP_NUMERIC(5);

                            $variable = sprintf(
                                "CALLED=%s,CALLING=%s,CBID=%s,TARIFF=%s,LEG=%s,ACCOUNTCODE=%s,ROOMNUMBER=%s",
                                // some copy/paste errrors going on here?
                                $inst_pn_member, $inst_pn_member, $callback_uniqueid, $callback_tariff, $A2B->accountcode, $A2B->accountcode, $room_number
                            );

                            insert_callback($A2B, $agi, $uniqueid, $channel, $variable, $exten, $context, $callerid);

                        } else {
                            $error_msg = 'Error : You don t have enough credit to call you back !!!';
                            $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[CALLBACK-CALLERID : CALLED=" . $A2B->destination . " | $error_msg]");
                        }
                    } else {
                        $error_msg = 'Error : There is no route to call back your phonenumber !!!';
                        $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[CALLBACK-CALLERID : CALLED=" . $A2B->destination . " | $error_msg]");
                    }
                }
            }

            // DIAL INTO THE CONFERENCE AS ADMINISTRATOR
            $dialstr = "local/$room_number@a2billing-conference-room";

            $A2B->debug(A2Billing::INFO, $agi, __FILE__, __LINE__, "DIAL $dialstr");
            $myres = $A2B->run_dial($agi, $dialstr);

        }//END FOR

        if ($A2B->set_inuse) {
            $A2B->callingcard_acct_start_inuse($agi, 0);
        }

    } else {
        $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[CALLBACK]:[AUTHENTICATION FAILED (cia_res:" . $cia_res . ")]");
    }
}

// CHECK IF WE HAVE TO CHARGE CALLBACK
if ($charge_callback) {

    $callback_username = $callback_leg;
    $A2B->accountcode = $callback_username;
    $A2B->agiconfig['say_balance_after_auth'] = 0;
    $A2B->agiconfig['cid_enable'] = 0;
    $A2B->agiconfig['say_timetocall'] = 0;

    $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[CALLBACK 1ST LEG]:[INFO FOR THE 1ST LEG - callback_username=$callback_username");
    $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[CALLBACK 1ST LEG]:[TRY : callingcard_ivr_authenticate]");
    $cia_res = $A2B->callingcard_ivr_authenticate($agi);

    //overrides the tariff for the user with the one passed in.
    if (strlen($callback_tariff) > 0) {
        $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "*** Tariff override **** Changing from " . $A2B->tariff . " to " . $callback_tariff . " cia_res=$cia_res");
        $A2B->tariff = $callback_tariff;
    }

    if ($cia_res == 0) {

        $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[CALLBACK 1ST LEG]:[MAKE BILLING FOR THE 1ST LEG - TARIFF:" . $A2B->tariff . ";CALLED=$called_party]");
        $A2B->agiconfig['use_dnid'] = 1;
        $A2B->dnid = $A2B->destination = $called_party;

        $resfindrate = $RateEngine->rate_engine_findrates($A2B, $called_party, $A2B->tariff);
        $RateEngine->usedratecard = 0;

        // IF FIND RATE
        if ($resfindrate != 0) {
            $res_all_calcultimeout = $RateEngine->rate_engine_all_calcultimeout($A2B, $A2B->credit);

            if ($res_all_calcultimeout) {
                // SET CORRECTLY THE CALLTIME FOR THE 1st LEG
                $RateEngine->answeredtime = time() - $G_startime;
                $RateEngine->dialstatus = 'ANSWERED';
                $A2B->debug(A2Billing::INFO, $agi, __FILE__, __LINE__, "[CALLBACK]:[RateEngine->answeredtime=" . $RateEngine->answeredtime . "]");

                //(ST) replace above code with the code below to store CDR for all callbacks and to only charge for the callback if requested
                if ($callback_been_connected == 1 || ($A2B->agiconfig['callback_bill_1stleg_ifcall_notconnected'] == 1)) {
                    //(ST) this is called if we need to bill the user
                    $RateEngine->rate_engine_updatesystem($A2B, $agi, $A2B->destination, 1, 0, 1);
                } else {
                    //(ST) this is called if we don't bill ther user but to keep track of call costs
                    $RateEngine->rate_engine_updatesystem($A2B, $agi, $A2B->destination, 0, 0, 1);
                }

            } else {
                $A2B->debug(A2Billing::ERROR, $agi, __FILE__, __LINE__, "[CALLBACK 1ST LEG]:[ERROR - BILLING FOR THE 1ST LEG - rate_engine_all_calcultimeout: CALLED=$called_party]");
            }
        } else {
            $A2B->debug(A2Billing::ERROR, $agi, __FILE__, __LINE__, "[CALLBACK 1ST LEG]:[ERROR - BILLING FOR THE 1ST LEG - rate_engine_findrates: CALLED=$called_party - RateEngine->usedratecard=" . $RateEngine->usedratecard . "]");
        }
    } else {
        $A2B->debug(A2Billing::ERROR, $agi, __FILE__, __LINE__, "[CALLBACK 1ST LEG]:[ERROR - AUTHENTICATION USERNAME]");
    }

}// END if ($charge_callback)

if ($mode !== 'cid-callback' && $mode !== 'all-callback') {
    $agi->hangup();
} elseif ($A2B->agiconfig['answer_call'] == 1) {
    $agi->hangup();
}

// SEND MAIL REMINDER WHEN CREDIT IS TOO LOW
if ($send_reminder && $A2B->agiconfig['send_reminder'] && $A2B->cardholder_email) {
    try {
        $mail = new Mail(Mail::$TYPE_REMINDERCALL, $A2B->id_card);
        $mail->send();
        $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[SEND-MAIL REMINDER]:[TO:$A2B->cardholder_email]");
    } catch (A2bMailException $e) {
    }
}

if ($A2B->set_inuse) {
    $A2B->callingcard_acct_start_inuse($agi, 0);
}

# End
$A2B->write_log("[exit]", 0);

function get_dialstring(array $ratecard, A2Billing $A2B, RateEngine $RateEngine): string
{
    if ($ratecard[34] != '-1') {
        $usetrunk = 34;
        $usetrunk_failover = 1;
        $RateEngine->usedtrunk = $ratecard[34];
    } else {
        $usetrunk = 29;
        $RateEngine->usedtrunk = $ratecard[29];
        $usetrunk_failover = 0;
    }

    $prefix = $ratecard[$usetrunk + 1];
    $tech = $ratecard[$usetrunk + 2];
    $ipaddress = $ratecard[$usetrunk + 3];
    $removeprefix = $ratecard[$usetrunk + 4];
    $addparameter = $ratecard[42 + $usetrunk_failover] ?? "";

    $destination = preg_replace("/^$removeprefix/", "", $A2B->destination);

    $pos_dialingnumber = str_contains($ipaddress, '%dialingnumber%');
    $ipaddress = str_replace(
        ["%cardnumber%", "%dialingnumber%"],
        [$A2B->cardnumber, "$prefix$destination"],
        $ipaddress
    );

    if ($pos_dialingnumber) {
        $dialstr = "$tech/$ipaddress";
    } elseif ($A2B->agiconfig['switchdialcommand'] == 1) {
        $dialstr = "$tech/$prefix$destination@$ipaddress";
    } else {
        $dialstr = "$tech/$ipaddress/$prefix$destination";
    }

    //ADDITIONAL PARAMETER %dialingnumber%, %cardnumber%
    $dialstr .= str_replace(
        ["%cardnumber%", "%dialingnumber%"],
        [$A2B->cardnumber, "$prefix$destination"],
        $addparameter
    );

    return $dialstr;
}

function insert_callback(A2Billing $A2B, Agi $agi, string $uniqueid, string $channel, string $variable, ?string $exten, ?string $context = null, ?string $callerid = null): bool
{
    $exten = $exten ?? $A2B->config["callback"]['extension'];
    $context = $context ?? $A2B->config["callback"]['context_callback'];
    $id_server_group = $A2B->config["callback"]['id_server_group'];
    $callback_time = max((int)$A2B->config["callback"]['sec_wait_before_callback'], 1);
    $account = $A2B->accountcode;
    $caller_id = $callerid ?? $A2B->config["callback"]['callerid'];
    $timeout = $A2B->config["callback"]['timeout'] * 1000;

    $query = "INSERT INTO cc_callback_spool (status, server_ip, num_attempt, priority, uniqueid, channel, exten, context, variable, id_server_group, callback_time, account, callerid, timeout)";
    $query .= " VALUES ('PENDING', 'localhost', 0, 1, ?, ?, ?, ?, ?, ?, NOW() + INTERVAL ? SECOND, ?, ?, ?)";
    $params = [
        $uniqueid,
        $channel,
        $exten,
        $context,
        $variable,
        $id_server_group,
        $callback_time,
        $account,
        $caller_id,
        $timeout,
    ];
    $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[CALLBACK-ALL : INSERT CALLBACK REQUEST IN SPOOL : QUERY=$query, PARAMS=". json_encode($params) . "]");
    $res = $A2B->DBHandle->Execute($query, $params);

    if (!$res) {
        $error_msg = "Cannot insert the callback request in the spool!";
        $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[CALLBACK-ALL : CALLED=" . $A2B->destination . " | $error_msg]");
        return false;
    }

    return true;
}

function attempt_call(A2Billing $A2B, RateEngine $RateEngine, Agi $agi)
{
    // PERFORM THE CALL
    $result_callperf = $RateEngine->rate_engine_performcall($agi, $A2B->destination, $A2B);

    if (!$result_callperf) {
        $prompt = "prepaid-dest-unreachable";
        $agi->stream_file($prompt, '#');
    }
    // INSERT CDR & UPDATE SYSTEM
    $RateEngine->rate_engine_updatesystem($A2B, $agi, $A2B->destination);

    if ($A2B->agiconfig['say_balance_after_call'] == 1) {
        $A2B->fct_say_balance($agi, $A2B->credit);
    }
    $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, '[a2billing account stop]');
}