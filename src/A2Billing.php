<?php

namespace A2billing;

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

use A2billing\PhpAgi\Agi;
use ADOConnection;

/**
 * This file is part of A2Billing (http://www.a2billing.net/)
 *
 * A2Billing, Commercial Open Source Telecom Billing platform,
 * powered by Star2billing S.L. <http://www.star2billing.com/>
 *
 * @copyright   Copyright © 2004-2015 - Star2billing S.L.
 * @copyright   Copyright © 2022 RadiusOne Inc.
 * @author      Belaid Arezqui <areski@gmail.com>
 * @author      Michael Newton <mnewton@goradiusone.com>
 * @license     http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @package     A2Billing
 * @contributor Steve Dommett <steve@st4vs.net>
 *              Belaid Rachid <rachid.belaid@gmail.com>
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

class A2Billing
{
    public const FATAL = 0;
    public const ERROR = 1;
    public const WARN  = 2;
    public const INFO  = 3;
    public const DEBUG = 4;

    public const SCRIPT_CONFIG_DIR = "/var/lib/a2billing/script/";
    public const DEFAULT_A2BILLING_CONFIG = "/etc/a2billing.conf";

    /** @var array */
    public array $config;

    /** @var array */
    public array $agiconfig;

    /** @var int */
    public int $idconfig = 1;

    /** @var bool */
    public bool $hangupdetected = false;

    /** @var string */
    public string $cardnumber;
    /** @var string */
    public string $CallerID = "";

    /** @var bool|ADOConnection */
    public $DBHandle;

    /** @var Table */
    public Table $table;

    /** @var string the file name to store the logs */
    public string $log_file = '';

    /** @var string value of agi_channel */
    public string $channel;
    /** @var string value of agi_uniqueid */
    public string $uniqueid;
    /** @var string value of agi_accountcode unless overridden by configured default account code */
    public string $accountcode;
    /** @var string value of agi_extension ???? */
    public string $dnid = "";
    /** @var string value of agi_dnid */
    public string $orig_dnid;
    /** @var string value of agi_extension */
    public string $orig_ext;
    /** @var string not sure? */
    public string $extension;

    /** @var string the call destination */
    public string $destination;
    /** @var string */
    public string $early_destination = '';
    /** @var string */
    public string $sip_iax_buddy = "";
    /** @var int credit on the account */
    public int $credit;
    public int $tariff;
    public string $active;
    /** @var int card status; 1=active 5=expired */
    public int $status;
    /** @var string seems to always be blank */
    public string $hostname = '';
    public string $currency = 'usd';

    public bool $group_mode = false;
    public int $group_id = 0;
    public string $mode = '';
    public int $timeout;
    public string $tech;
    public string $prefix;
    public string $username;

    /** @var int type of card; 0=prepaid 1=postpaid */
    public int $typepaid = 0;
    /** @var bool whether to remove the idd prefix before making the call */
    public bool $removeinterprefix = true;
    /** @var int restriction type; 0=no restriction 1=deny numbers in list 2=only allow numbers in list */
    public int $restriction = 1;
    /** @var string the last dialled number */
    public string $redial = "";
    /** @var int how many times the card has been used */
    public int $nbused = 0;

    /** @var int card expiry type: 1=$expirationdate 2=$expiredays since $firstusedate 3=$expiredays since $creationdate */
    public int $enableexpire = 0;
    /** @var int expiration date as timestamp */
    public int $expirationdate = 0;
    /** @var int number of days before expiring */
    public int $expiredays = 0;
    /** @var int first use date as timestamp */
    public int $firstusedate = 0;
    /** @var int creation date as timestamp */
    public int $creationdate = 0;

    /** @var int postpaid card credit limit */
    public int $creditlimit = 0;

    /** @var int */
    public int $languageselected = 0;
    public string $current_language = "en";

    public string $cardholder_lastname;
    public string $cardholder_firstname;
    public string $cardholder_email;
    public string $cardholder_uipass;
    /** @var int seems this is never read, only set */
    public int $id_campaign;
    public int $id_card;
    public string $useralias;
    public string $countryprefix;

    /** @var int start time of the script */
    public int $G_startime = 0;

    /** @var bool Enable voicemail for this card. For DID and SIP/IAX call */
    public bool $voicemail = false;

    /** @var bool whether to prompt for another cardnumber when needed (e.g. if not enough credit to call) */
    public bool $ask_other_cardnumber = false;
    /** @var bool if another card is applied, whether to update the cc_callerid table as well? not sure */
    public bool $update_callerid = false;

    /** @var int[]|null valid card number lengths */
    public ?array $cardnumber_range;

    /** @var bool $set_inuse Define if we have changed the status of the card */
    public bool $set_inuse = false;

    /** @var bool */
    public bool $callback_beep_to_enter_destination = false;

    /** @var string either empty string or ", a2b_custom1, a2b_custom2" to be added to cc_call insert query */
    public string $CDR_CUSTOM_SQL = '';

    /** @var string values pulled from A2B_CUSTOM1 and A2B_CUSTOM2 AGI variables */
    public string $CDR_CUSTOM_VAL = '';

    public array $dialstatus_rev_list = ["ANSWER" => 1, "BUSY" => 2, "NOANSWER" => 3, "CANCEL" => 4, "CONGESTION" => 5, "CHANUNAVAIL" => 6, "DONTCALL" => 7, "TORTURE" => 8, "INVALIDARGS" => 9];

    /** @var array list of currency exchange rates indexed by currency code */
    private array $currencies_list = [];

    /** @var Agi|null the current AGI instance */
    private ?Agi $agi;

    /** @var RateEngine the RateEngine instance used for calculations */
    private RateEngine $rateEngine;


    public function __construct(int $idconfig = 1, array $optconfig = [], ?Agi $agi = null)
    {
        if (function_exists('pcntl_signal')) {
            pcntl_signal(SIGHUP, [$this, "Hangupsignal"]);
        }
        $this->agi = $agi;
        $this->rateEngine = new RateEngine($this);
        // populate the configuration object
        $this->idconfig = $idconfig;
        $this->load_conf($optconfig);
        // populate the $DBHandle property
        $this->DbConnect();
    }

    /* Init */
    public function Reinit(): void
    {
        $this->destination = '';
        $this->rateEngine->Reinit();
    }

    /* Hangupsignal */
    public function Hangupsignal(): void
    {
        $this->hangupdetected = true;
        $this->debug(self::INFO, "HANGUP DETECTED!\n");
    }

    /*
    * Debug
    */
    public function debug(int $level, ...$data): void
    {
        $st = debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $st = $st[1] ?? $st[0];
        $file = $st["file"];
        $line = $st["line"];
        $func = basename(str_replace("\\", "/", $st["class"] ?? "")) . ($st["type"] ?? "") . $st["function"] . "()";
        $file = basename($file);
        $u = $this->uniqueid ?? "n/a";
        // VERBOSE
        if ($this->agi && $this->agiconfig['verbosity_level'] >= $level) {
            foreach ($data as $item) {
                if (!is_string($output = $item)) {
                    $output = json_encode($output);
                }
                $chunks = str_split($output, 1024);
                foreach ($chunks as $key => $chunk) {
                    $part = $key > 0 ? sprintf(" %d/%d", $key + 1, count($chunks)) : "";
                    $this->agi->verbose("$func $file:$line [$u]$part $chunk");
                }
            }
        }
        // LOG INTO FILE
        foreach ($data as $item) {
            if ($this->agiconfig['logging_level'] >= $level) {
                if (!is_string($output =$item)) {
                    $output = print_r($output, true);
                }
                $this->write_log($output, "$func $file:$line [$u]");
            }
        }
    }

    /*
    * Write log into file
    */
    public function write_log(string $output, string $line_file_info = ''): void
    {
        if (!empty($this->log_file) && is_writable($this->log_file)) {
            $date = date("Y-m-d H:i:s");
            $string_log = "[$date] $line_file_info $output\n";
            error_log($string_log, 3, $this->log_file);
        }
    }

    /*
    * set_instance_table
    */
    public function set_table($table): void
    {
        $this->table = $table;
    }

    /*
    * load_conf
    */
    public function load_conf(array $optconfig = []): void
    {
        $config = self::DEFAULT_A2BILLING_CONFIG;

        if (!is_readable($config)) {
            echo "Error : A2Billing configuration file $config is missing!";
            exit;
        }

        $idconfig = $this->idconfig;
        if (!empty($this->config["agi-conf$idconfig"])) {
            // config has already been parsed, assume it isn't going to change
            $this->agiconfig = $this->config["agi-conf$idconfig"];
            return;
        }

        $this->config = parse_ini_file($config, true);

        // conf for the database connection
        $default = [
            "hostname" => "localhost",
            "port" => "5432",
            "user" => "postgres",
            "password" => "",
            "dbname" => "a2billing",
            "dbtype" => "postgres",
        ];
        $this->config["database"] = array_merge($default, $this->config["database"]);

        $this->DbConnect();
        $this->currencies_list = $this->get_currencies();
        $query = "SELECT config_key, config_value, config_group_title, config_valuetype FROM cc_config";
        $config_res = $this->DBHandle->GetAll($query);
        if ($config_res === false || $config_res === []) {
            echo 'Error : cannot load conf : load_conf_db';
            exit;
        }

        foreach ($config_res as $conf) {
            $type = $conf["config_valuetype"];
            $group = $conf["config_group_title"];
            $key = $conf["config_key"];
            $val = $conf["config_value"];
            if ($type == 1 && preg_match("/(yes|true|1)/i", $val)) { // if its type is boolean yes
                $val = 1;
            } elseif ($type == 1) { // if equal to 'no'
                $val = 0;
            }
            $this->config[$group][$key] = $val;
        }
        $this->DbDisconnect();

        // If optconfig is specified, stuff vals and vars into 'a2billing' config array.
        foreach ($optconfig as $var=>$val) {
            $this->config["agi-conf$idconfig"][$var] = $val;
        }

        // add default values to config for uninitialized values
        //Card Number Length Code
        $card_length_range = $this->config['global']['interval_len_cardnumber'] ?? null;
        $this->cardnumber_range = split_data($card_length_range);

        if (!count($this->cardnumber_range)) {
            echo gettext("Invalid card number length list defined in configuration.");
            exit;
        }

        $default["global"]["len_aliasnumber"] = 15;
        $default["global"]["len_voucher"] = 15;
        $default["global"]["base_currency"] = 'usd';
        $default["global"]["didbilling_daytopay"] = 5;
        $default["global"]["admin_email"] = 'root@localhost';

                // Conf for the Callback
        $default["callback"]["context_callback"] = 'a2billing-callback';
        $default["callback"]["ani_callback_delay"] = '10';
        $default["callback"]["extension"] = '1000';
        $default["callback"]["sec_avoid_repeate"] = '30';
        $default["callback"]["timeout"] = '20';
        $default["callback"]["answer_call"] = '1';
        $default["callback"]["nb_predictive_call"] = '10';
        $default["callback"]["nb_day_wait_before_retry"] = '1';
        $default["callback"]["context_preditctivedialer"] = 'a2billing-predictivedialer';
        $default["callback"]["predictivedialer_maxtime_tocall"] = '5400';
        $default["callback"]["sec_wait_before_callback"] = '10';

        // Conf for the signup
        $default["signup"]["enable_signup"] = '1';
        $default["signup"]["credit"] = '0';
        $default["signup"]["tariff"] = '8';
        $default["signup"]["activated"] = 't';
        $default["signup"]["simultaccess"] = '0';
        $default["signup"]["typepaid"] = '0';
        $default["signup"]["creditlimit"] = '0';
        $default["signup"]["runservice"] = '0';
        $default["signup"]["enableexpire"] = '0';
        $default["signup"]["expiredays"] = '0';

        // Conf for Paypal
        $default["paypal"]["item_name"] = 'Credit Purchase';
        $default["paypal"]["currency_code"] = 'USD';
        $default["paypal"]["purchase_amount"] = '5;10;15';
        $default["paypal"]["paypal_fees"] = '1';

        // Conf for Backup
        $default["backup"]["backup_path"] = '/tmp';
        $default["backup"]["gzip_exe"] = '/bin/gzip';
        $default["backup"]["gunzip_exe"] = '/bin/gunzip';
        $default["backup"]["mysqldump"] = '/usr/bin/mysqldump';
        $default["backup"]["pg_dump"] = '/usr/bin/pg_dump';
        $default["backup"]["mysql"] = '/usr/bin/mysql';
        $default["backup"]["psql"] = '/usr/bin/psql';
        $default["backup"]["archive_data_x_month"] = '3';

        // Conf for Customer Web UI
        $default["webcustomerui"]["customerinfo"] = '1';
        $default["webcustomerui"]["personalinfo"] = '1';
        $default["webcustomerui"]["limit_callerid"] = '5';
        $default["webcustomerui"]["error_email"] = 'root@localhost';
        // conf for the web ui
        $default["webui"]["buddy_sip_file"] = '/etc/asterisk/additional_a2billing_sip.conf';
        $default["webui"]["buddy_iax_file"] = '/etc/asterisk/additional_a2billing_iax.conf';
        $default["webui"]["api_logfile"] = '/tmp/api_ecommerce_request.log';

        $default["webui"]["dir_store_mohmp3"] = '/var/lib/asterisk/mohmp3';
        $default["webui"]["num_musiconhold_class"] = 10;
        $default["webui"]["show_help"] = 1;
        $default["webui"]["my_max_file_size_import"] = 1024000;
        $default["webui"]["dir_store_audio"] = '/var/lib/asterisk/sounds/a2billing';
        $default["webui"]["my_max_file_size_audio"] = 3072000;

        $default['webui']['file_ext_allow'] = explode(",", "gsm, mp3, wav");

        $default['webui']['file_ext_allow_musiconhold'] = explode(",", "mp3");

        $default["webui"]["show_top_frame"] = 1;
        $default["webui"]["currency_choose"] = 'all';
        $default["webui"]["card_export_field_list"] = 'creationdate, username, credit, lastname, firstname';
        $default["webui"]["rate_export_field_list"] = 'dest_name, dialprefix, rateinitial';
        $default["webui"]["voucher_export_field_list"] = 'id, voucher, credit, tag, activated, usedcardnumber, usedate, currency';
        $default["webui"]["advanced_mode"] = 0;
        $default["webui"]["delete_fk_card"] = 1;

        // conf for the recurring process
        $default["recprocess"]['batch_log_file'] = '/tmp/batch-a2billing.log';

        // conf for the peer_friend
        $default["peer_friend"]["type"] = 'friend';
        $default["peer_friend"]["allow"] = 'ulaw,alaw,gsm,g729';
        $default["peer_friend"]["context"] = 'a2billing';
        $default["peer_friend"]["nat"] = 'yes';
        $default["peer_friend"]["amaflags"] = 'billing';
        $default["peer_friend"]["qualify"] = 'yes';
        $default["peer_friend"]["host"] = 'dynamic';
        $default["peer_friend"]["dtmfmode"] = 'RFC2833';
        $default["peer_friend"]["use_realtime"] = '0';


        //conf for the notifications
        $default["notifications"]["values_notifications"] = '0';
        $default["notifications"]["cron_notifications"] = '1';
        $default["notifications"]["delay_notifications"] = '1';

        $this->config = array_merge($default, $this->config);

        // conf for the log-files
        if (isset($this->config['log-files']['agi']) && strlen($this->config['log-files']['agi']) > 1) {
            $this->log_file = $this->config['log-files']['agi'];
        }
        if (isset($this->config['webui']['file_ext_allow'])) {
            $this->config['webui']['file_ext_allow'] = explode(",", $this->config['webui']['file_ext_allow']);
        }
        if (isset($this->config['webui']['file_ext_allow_musiconhold'])) {
            $this->config['webui']['file_ext_allow_musiconhold'] = explode(",", $this->config['webui']['file_ext_allow_musiconhold']);
        }
        if (isset($this->config['webui']['api_ip_auth'])) {
            $this->config['webui']['api_ip_auth'] = explode(";", $this->config['webui']['api_ip_auth']);
        }

        // conf for the AGI
        $default["play_audio"] = 1;

        $default["verbosity_level"] = 0;
        $default["logging_level"] = 3;

        $default["logger_enable"] = 1;
        $default["log_file"] = '/var/log/a2billing/a2billing.log';

        $default["answer_call"] = 1;
        $default["auto_setcallerid"] = 1;
        $default["say_goodbye"] = 0;
        $default["play_menulanguage"] = 0;
        $default["force_language"] = 'EN';
        $default["min_credit_2call"] = 0;
        $default["min_duration_2bill"] = 0;

        $default["use_dnid"] = 0;

        $default["number_try"] = 3;
        $default["say_balance_after_auth"] = 1;
        $default["say_balance_after_call"] = 0;
        $default["say_rateinitial"] = 0;
        $default["say_timetocall"] = 1;
        $default["cid_enable"] = 0;
        $default["cid_sanitize"] = 0;
        $default["cid_askpincode_ifnot_callerid"] = 1;
        $default["cid_auto_assign_card_to_cid"] = 0;
        $default["notenoughcredit_cardnumber"] = 0;
        $default["notenoughcredit_assign_newcardnumber_cid"] = 0;
        $default["maxtime_tocall_negatif_free_route"] = 1800;
        $default["callerid_authentication_over_cardnumber"] = 0;
        $default["cid_auto_create_card_len"] = 10;
        $default["cid_auto_create_card"] = 0;
        $default["sip_iax_friends"] = 0;
        $default["sip_iax_pstn_direct_call"] = 0;
        $default["dialcommand_param"] = '|30|HL(%timeout%:61000:30000)';
        $default["dialcommand_param_sipiax_friend"] = '|30|HL(3600000:61000:30000)';
        $default["dialcommand_param_call_2did "] = '|30|HL(3600000:61000:30000)';
        $default["switchdialcommand"] = 0;
        $default["failover_recursive_limit"] = 1;
        $default["record_call"] = 0;
        $default["monitor_formatfile"] = 'gsm';

        $default["currency_association"] = 'all:credit';
        $default['international_prefixes'] = "011,09,00,1";

        $default["file_conf_enter_destination"] = 'prepaid-enter-number-u-calling-1-or-011';
        $default["file_conf_enter_menulang"] = 'prepaid-menulang';
        $default["send_reminder"] = 0;

        $default["ivr_voucher"] = 0;
        $default["ivr_voucher_prefixe"] = 8;
        $default["jump_voucher_if_min_credit"] = 0;
        $default["failover_lc_prefix"] = 0;
        $default["cheat_on_announcement_time"] = 0;
        $default["busy_timeout"] = 1;
        $default["lcr_mode"] = 0;
        $default["default_accountcode"] = '';
        $default["default_accountcode_all"] = 0;

        $this->config["agi-conf$idconfig"] = array_merge($default, $this->config["agi-conf$idconfig"]);

        // Explode the no_auth_dnid string
        if (isset($this->config["agi-conf$idconfig"]['no_auth_dnid'])) {
            $this->config["agi-conf$idconfig"]['no_auth_dnid'] = explode(",", $this->config["agi-conf$idconfig"]['no_auth_dnid']);
        }
        // Explode the international_prefixes, extracharge_did and extracharge_fee strings
        if (isset($this->config["agi-conf$idconfig"]['extracharge_did'])) {
            $this->config["agi-conf$idconfig"]['extracharge_did'] = explode(",", $this->config["agi-conf$idconfig"]['extracharge_did']);
        }
        if (isset($this->config["agi-conf$idconfig"]['extracharge_fee'])) {
            $this->config["agi-conf$idconfig"]['extracharge_fee'] = explode(",", $this->config["agi-conf$idconfig"]['extracharge_fee']);
        }
        if (isset($this->config["agi-conf$idconfig"]['extracharge_buyfee'])) {
            $this->config["agi-conf$idconfig"]['extracharge_buyfee'] = explode(",", $this->config["agi-conf$idconfig"]['extracharge_buyfee']);
        }
        if (isset($this->config["agi-conf$idconfig"]['international_prefixes'])) {
            $this->config["agi-conf$idconfig"]['international_prefixes'] = explode(",", $this->config["agi-conf$idconfig"]['international_prefixes']);
        }
        if (isset($this->config["agi-conf$idconfig"]['currency_association'])) {
            $this->config["agi-conf$idconfig"]['currency_association'] = explode(",", $this->config["agi-conf$idconfig"]['currency_association']);
            foreach ($this->config["agi-conf$idconfig"]['currency_association'] as $cur_val) {
                $cur_val = explode(":", $cur_val);
                $this->config["agi-conf$idconfig"]['currency_association_internal'][$cur_val[0]] = $cur_val[1];
            }
        }
        if (isset($this->config["agi-conf$idconfig"]['currency_cents_association']) && strlen($this->config["agi-conf$idconfig"]['currency_cents_association']) > 0) {
            $this->config["agi-conf$idconfig"]['currency_cents_association'] = explode(",", $this->config["agi-conf$idconfig"]['currency_cents_association']);
            foreach ($this->config["agi-conf$idconfig"]['currency_cents_association'] as $cur_val) {
                $cur_val = explode(":", $cur_val);
                $this->config["agi-conf$idconfig"]['currency_cents_association_internal'][$cur_val[0]] = $cur_val[1];
            }
        }

        // Define the agiconfig property
        $this->agiconfig = $this->config["agi-conf$idconfig"];

        define("PLAY_AUDIO", $this->config["agi-conf$idconfig"]['play_audio']);

        // Print out on CLI for debug purpose
        $this->debug(self::DEBUG, 'A2Billing AGI internal configuration:');
        $this->debug(self::DEBUG, $this->agiconfig);
    }

    public function rateEngine(): RateEngine
    {
        return $this->rateEngine;
    }

    public function agi(): ?Agi
    {
        return $this->agi;
    }

    /*
    * Function to create a menu to select the language
    */
    public function play_menulanguage(): void
    {
        // MENU LANGUAGE
        if ($this->agiconfig['play_menulanguage'] == 1) {
            $list_prompt_menulang = explode(':', $this->agiconfig['conf_order_menulang']);
            $i = 1;
            $res_dtmf = null;
            foreach ($list_prompt_menulang as $lg_value) {
                $res_dtmf = $this->agi->get_data("menu_" . $lg_value, 500, 1);
                if (!empty($res_dtmf["result"]) && is_numeric($res_dtmf["result"])) {
                    break;
                }

                if ($i === count($list_prompt_menulang)) {
                    $res_dtmf = $this->agi->get_data("num_" . $lg_value . "_" . $i, 3000, 1);
                } else {
                    $res_dtmf = $this->agi->get_data("num_" . $lg_value . "_" . $i, 1000, 1);
                }

                if (!empty($res_dtmf["result"]) && is_numeric($res_dtmf["result"])) {
                    break;
                }
                $i++;
            }

            $this->debug(self::DEBUG, "RES Menu Language DTMF : " . $res_dtmf["result"]);
            $this->languageselected = (int)$res_dtmf["result"];

            if ($this->languageselected > 0 && $this->languageselected <= count($list_prompt_menulang)) {
                $language = $list_prompt_menulang[$this->languageselected - 1];
            } elseif (strlen($this->agiconfig['force_language']) === 2) {
                $language = strtolower($this->agiconfig['force_language']);
            } else {
                $language = 'en';
            }
            $this->current_language = $language;
            $this->debug(self::DEBUG, " CURRENT LANGUAGE : " . $language);

            $this->agi->set_variable('CHANNEL(language)', $language);
            $this->debug(self::INFO, "[SET CHANNEL(language) $language]");
            $this->languageselected = 1;

        } elseif (strlen($this->agiconfig['force_language']) === 2) {

            $this->debug(self::DEBUG, "FORCE LANGUAGE : " . $this->agiconfig['force_language']);
            $this->languageselected = 1;
            $language = strtolower($this->agiconfig['force_language']);
            $this->current_language = $language;
            $this->agi->set_variable('CHANNEL(language)', $language);
            $this->debug(self::INFO, "[SET CHANNEL(language) $language]");
        }
    }

    /*
     * function sanitize_agi_data
     */
    public function sanitize_agi_data(string $input): string
    {
        // Remove whitespaces (not a must though)
        $input = trim($input);
        $input = str_replace('--', '', $input);
        $input = str_replace(';', '', $input);
        $input = str_replace('/*', '', $input);
        $input = str_replace('(', '', $input);
        $input = str_replace('[', '', $input);
        // Sql Injection
        $input = str_ireplace('HAVING', '', $input);
        $input = str_ireplace('UNION', '', $input);
        $input = str_ireplace('SUBSTRING', '', $input);
        $input = str_ireplace('INSERT', '', $input);
        $input = str_ireplace('INTO', '', $input);
        $input = str_ireplace('ASCII', '', $input);
        $input = str_ireplace('SHA1', '', $input);
        $input = str_ireplace('MD5', '', $input);
        $input = str_ireplace('ROW_COUNT', '', $input);
        $input = str_ireplace('CONCAT', '', $input);
        $input = str_ireplace('WHERE', '', $input);
        $input = str_ireplace('SELECT', '', $input);
        $input = str_ireplace('UPDATE', '', $input);
        $input = str_ireplace('DROP', '', $input);
        $input = str_ireplace('DELETE', '', $input);
        $input = str_ireplace('TRUE', '', $input);
        $input = str_ireplace('FALSE', '', $input);

        if (preg_match("/ or (1|true)/i", $input) || strlen($input) >= 30) {
            return false;
        }

        return addslashes($input);
    }

    /*
    * intialize evironement variables from the agi values
    */
    public function get_agi_request_parameter(): void
    {
        $A2B_CUSTOM1 = substr($this->agi->get_variable("A2B_CUSTOM1", true), 0, 20);
        $A2B_CUSTOM2 = substr($this->agi->get_variable("A2B_CUSTOM2", true), 0, 20);
        $A2B_CUSTOM1 = trim($this->sanitize_agi_data($A2B_CUSTOM1));
        $A2B_CUSTOM2 = trim($this->sanitize_agi_data($A2B_CUSTOM2));

        if ($A2B_CUSTOM1) {
            $this->CDR_CUSTOM_SQL .= ($this->CDR_CUSTOM_SQL ? ", " : "") . "a2b_custom1";
            $this->CDR_CUSTOM_VAL .= ($this->CDR_CUSTOM_VAL ? ", " : "") . "'$A2B_CUSTOM1'";
        }
        if ($A2B_CUSTOM2) {
            $this->CDR_CUSTOM_SQL .= ($this->CDR_CUSTOM_SQL ? ", " : "") . "a2b_custom2";
            $this->CDR_CUSTOM_VAL .= ($this->CDR_CUSTOM_VAL ? ", " : "") . "'$A2B_CUSTOM2'";
        }

        $this->CallerID    = $this->sanitize_agi_data($this->agi->request['agi_callerid']);
        $this->channel     = $this->sanitize_agi_data($this->agi->request['agi_channel']);
        $this->uniqueid    = $this->sanitize_agi_data($this->agi->request['agi_uniqueid']);
        $this->orig_dnid   = preg_replace("/[^0-9]/", "", $this->agi->request['agi_dnid']);
        $this->orig_ext    = preg_replace("/[^0-9]/", "", $this->agi->request['agi_extension']);
        $this->dnid        = preg_replace("/[^0-9]/", "", $this->agi->request['agi_extension']);
        if ($this->agiconfig['default_accountcode_all'] && !empty($this->agiconfig['default_accountcode'])) {
            $this->accountcode = $this->agiconfig['default_accountcode'];
        } elseif (empty($this->agi->request['agi_accountcode']) && !empty($this->agiconfig['default_accountcode'])) {
            $this->accountcode = $this->agiconfig['default_accountcode'];
        } else {
            $this->accountcode = $this->sanitize_agi_data($this->agi->request['agi_accountcode']);
        }
        //Call function to find the cid number
        $this->isolate_cid();

        $this->debug(self::INFO, "get_agi_request_parameter: CID=$this->CallerID CHANNEL=$this->channel ID=$this->uniqueid ACCOUNT=$this->accountcode DNID=$this->dnid");
    }

    /*
    * function to find the cid number
    */
    public function isolate_cid(): void
    {
        if (preg_match("/<(.+?)>/", $this->CallerID, $matches)) {
            $this->CallerID = $matches[1];
        }
    }

    /*
    * function would set when the card is used or when it release
    */
    public function callingcard_acct_start_inuse(bool $inuse = false): void
    {
        $upd_balance = 0;
        if (is_numeric($this->agiconfig['dial_balance_reservation'])) {
            $upd_balance = $this->agiconfig['dial_balance_reservation'];
        }

        $this->debug(self::DEBUG, "[CARD STATUS UPDATE]");
        if ($inuse) {
            $query = "UPDATE cc_card SET inuse = inuse + 1, credit = credit - ? WHERE username = ?";
            $this->set_inuse = true;
        } else {
            $query = "UPDATE cc_card SET inuse = inuse - 1, credit = credit + ? WHERE username = ?";
            $this->set_inuse = false;
        }
        $params = [$upd_balance, $this->username];

        $this->DBHandle->Execute($query, $params);
        $this->debug(self::DEBUG, "Query: $query", $params);
    }

    /*
    * function enough_credit_to_call
    */
    public function enough_credit_to_call(): bool
    {
        if ($this->typepaid == 0) {
            if ($this->credit < $this->agiconfig['min_credit_2call'] || $this->credit < 0) {
                return false;
            } else {
                return true;
            }
        } elseif ($this->credit <= -$this->creditlimit) {
            $query = "SELECT id_cc_package_offer FROM cc_tariffgroup WHERE id = ?";
            $params = [$this->tariff];
            $val = $this->DBHandle->GetOne($query, $params);
            $this->debug(self::DEBUG, "Query: $query", $params, $val);
            return (int)$val > 0;
        } else {
            return true;
        }
    }

    /**
    * Function callingcard_ivr_authorize : check the dialed/dialing number and play the time to call
    **/
    public function callingcard_ivr_authorize(int $try_num, bool $call2did = false): int
    {
        /************** ASK DESTINATION ******************/
        $prompt_enter_dest = $this->agiconfig['file_conf_enter_destination'];

        $this->debug(self::DEBUG, "use_dnid:" . $this->agiconfig['use_dnid'] . " && (!in_array:" . in_array($this->dnid, $this->agiconfig['no_auth_dnid']) . ") && len_dnid:(" . strlen($this->dnid) . " || len_exten:" . strlen($this->extension) . " ) && (try_num:$try_num)");

        // CHECK IF USE_DNID IF NOT GET THE DESTINATION NUMBER
        if (
            $this->agiconfig['use_dnid'] == 1
            && !in_array($this->dnid, $this->agiconfig['no_auth_dnid'])
            && (strlen($this->dnid) >= 1 || strlen($this->extension) >= 1)
            && $try_num == 0
        ) {
            if ($this->extension == 's') {
                $this->destination = $this->dnid;
            } else {
                $this->destination = $this->extension;
            }
            $this->debug(self::DEBUG, "[USE_DNID DESTINATION ::> " . $this->destination . "]");
        // we accept if destination was enetered earlier in balance prompt
        } elseif (strlen($this->early_destination) && $this->early_destination != '#') {
            $this->destination = $this->early_destination; // use it
            $this->early_destination = ''; // 'consume' to prevent looping
        } else {
            if ($this->callback_beep_to_enter_destination) {
                $res_dtmf = $this->agi->get_data('beep', 6000, 20);
            } else {
                $res_dtmf = $this->agi->get_data($prompt_enter_dest, 6000, 20);
            }

            $this->debug(self::DEBUG, "RES DTMF : " . $res_dtmf["result"]);
            $this->destination = $res_dtmf["result"];
        }

        //REDIAL FIND THE LAST DIALED NUMBER (STORED IN THE DATABASE)
        if ($this->destination == '0*') {
            $this->destination = $this->redial;
            $this->debug(self::DEBUG, "[REDIAL : DTMF DESTINATION ::> " . $this->destination . "]");
        }

        if (strlen($this->destination) <= 2 && is_numeric($this->destination) && $this->destination >= 0) {
            $query = "SELECT phone FROM cc_speeddial WHERE id_cc_card = ? AND speeddial = ?";
            $params = [$this->id_card, $this->destination];
            $val = $this->DBHandle->GetOne($query, $params);
            $this->debug(self::DEBUG, "Query: $query", $params, $val);
            if ($val !== false && !is_null($val)) {
                $this->destination = $val;
            }
            $this->debug(self::INFO, "SPEEDIAL REPLACE DESTINATION ::> " . $this->destination);
        }

        //Check if Account have restriction
        // 1=block list, 2=allow list
        if ($this->restriction === 1 || $this->restriction === 2) {

            $this->debug(self::DEBUG, "[ACCOUNT WITH RESTRICTION]");

            $query = "SELECT COUNT(*) FROM cc_restricted_phonenumber WHERE id_card = ? AND ? LIKE number";
            $params = [$this->id_card, $this->destination];
            if ($this->removeinterprefix) {
                $query .= " OR ? LIKE number";
                $params[] = $this->apply_rules($this->destination);
            }
            $count = $this->DBHandle->GetOne($query, $params);
            $this->debug(self::DEBUG, "Query: $query", $params, $count);

            if (($this->restriction === 1 && $count !== false && !is_null($count)) || ($this->restriction === 2 && is_null($count))) {
                $this->debug(self::INFO, "[NUMBER NOT AUHTORIZED - RESTRICTION POLICY $this->restriction]");
                $this->agi->stream_file('prepaid-not-authorized-phonenumber', '#');

                return -1;
            }
        }

        //Test if the destination is a did
        //if call to did is authorized chez if the destination is a did of system
        $iscall2did = false;
        if ($call2did) {
            $this->debug(self::INFO, "[CALL 2 DID]");
            $query = "SELECT cc_did.id, iduser" .
                    " FROM cc_did, cc_card " .
                    " WHERE cc_card.status=1 and cc_card.id = iduser and cc_did.activated = 1 and did = ? " .
                    " AND cc_did.startingdate<= CURRENT_TIMESTAMP AND (cc_did.expirationdate > CURRENT_TIMESTAMP OR cc_did.expirationdate IS NULL";
            if ($this->config["database"]['dbtype'] != "postgres") {
                $query .= " OR cc_did.expirationdate = '0000-00-00 00:00:00'";
            }
            $query .= ")";
            $params = [$this->destination];
            $row = $this->DBHandle->GetRow($query, $params);
            $this->debug(self::DEBUG, "Query: $query", $params, $row);
            if ($row !== false && $row !== []) {
                $iscall2did = true;
            }
        }

        $this->debug(self::INFO, "DESTINATION ::> " . $this->destination);

        if ($iscall2did) {
            //it's call to did
            $this->save_redial_number($this->destination);

            return 2;
        }
        $this->destination = $this->apply_add_countryprefixto($this->destination);

        if ($this->removeinterprefix) {
            $this->destination = $this->apply_rules($this->destination);
        }

        $this->debug(self::DEBUG, "RULES APPLY ON DESTINATION ::> " . $this->destination);

        // TRIM THE "#"s IN THE END, IF ANY
        // usefull for SIP or IAX friends with "use_dnid" when their device sends also the "#"
        // it should be safe for normal use
        $this->destination = rtrim($this->destination, "#");

        // SAY BALANCE AND FT2C PACKAGE IF APPLICABLE
        // this is hardcoded for now but we might have a setting in a2billing.conf for the combination
        if ($this->destination == '*0') {
            $this->debug(self::DEBUG, "[SAY BALANCE ::> " . $this->credit . "]");
            $this->fct_say_balance($this->credit);

            // Retrieve this customer's FT2C package details
            $query = "SELECT freetimetocall, packagetype, billingtype, startday, id_cc_package_offer " .
                    "FROM cc_card RIGHT JOIN cc_tariffgroup ON cc_tariffgroup.id = cc_card.tariff " .
                    "RIGHT JOIN cc_package_offer ON cc_package_offer.id = cc_tariffgroup.id_cc_package_offer " .
                    "WHERE cc_card.id = ?";
            $params = [$this->id_card];
            $row = $this->DBHandle->GetRow($query, $params);
            $this->debug(self::DEBUG, "Query: $query", $params, $row);
            if ($row !== false && $row !== []) {
                [$freetime, $packagetype, $billingtype, $startday, $id_cc_package_offer] = $row;
                $freetimetocall_used = $this->free_calls_used($this->id_card, (int)$id_cc_package_offer, (int)$billingtype, (int)$startday, "time");

                //TO MANAGE BY PACKAGE TYPE IT->only for freetime
                if ($packagetype == 0 || $packagetype == 1) {
                    $minutes = intval(($freetime - $freetimetocall_used) / 60);
                    $seconds = ($freetime - $freetimetocall_used) % 60;
                } else {
                    $minutes = intval($freetimetocall_used / 60);
                    $seconds = $freetimetocall_used % 60;
                }
                // Now say either "You have X minutes and Y seconds of free package calls remaining this week/month"
                // or "You have dialed X minutes and Y seconds of free package calls this week/month"
                if ($packagetype == 0 || $packagetype == 1) {
                    $this->agi->stream_file('prepaid-you-have', '#');
                } else {
                    $this->agi->stream_file('prepaid-you-have-dialed', '#');
                }
                if ($minutes > 0 || $seconds === 0) {
                    if ($minutes === 1) {
                        if ((strtolower($this->current_language) == 'ru')) {
                            $this->agi->stream_file('digits/1f', '#');
                        } else {
                            $this->agi->say_number($minutes);
                        }
                        $this->agi->stream_file('prepaid-minute', '#');
                    } else {
                        $this->agi->say_number($minutes);
                        if ((strtolower($this->current_language) == 'ru') && (($minutes % 10 == 2) || ($minutes % 10 == 3) || ($minutes % 10 == 4))) {
                            // test for the specific grammatical rules in RUssian
                            $this->agi->stream_file('prepaid-minute2', '#');
                        } else {
                            $this->agi->stream_file('prepaid-minutes', '#');
                        }
                    }
                }
                if ($seconds > 0) {
                    if ($minutes > 0) {
                        $this->agi->stream_file('vm-and', '#');
                    }
                    if ($seconds === 1) {
                        if ((strtolower($this->current_language) === 'ru')) {
                            $this->agi->stream_file('digits/1f', '#');
                        } else {
                            $this->agi->say_number($seconds);
                        }
                        $this->agi->stream_file('prepaid-second', '#');
                    } else {
                        $this->agi->say_number($seconds);
                        if (
                            strtolower($this->current_language) === 'ru'
                            && ($seconds % 10 == 2 || $seconds % 10 == 3 || $seconds % 10 == 4)
                        ) {
                            // test for the specific grammatical rules in RUssian
                            $this->agi->stream_file('prepaid-second2', '#');
                        } else {
                            $this->agi->stream_file('prepaid-seconds', '#');
                        }
                    }
                }
                $this->agi->stream_file('prepaid-of-free-package-calls', '#');
                if ($packagetype == 0 || $packagetype == 1) {
                        $this->agi->stream_file('prepaid-remaining', '#');
                        $this->debug(self::DEBUG, "[SAY FT2C REMAINING ::> " . $minutes . ":" . $seconds . "]");
                } else {
                        $this->debug(self::DEBUG, "[SAY FT2C USED ::> " . $minutes . ":" . $seconds . "]");
                }
                $this->agi->stream_file('this', '#');
                if ($billingtype == 0) {
                        $this->agi->stream_file('month', '#');
                } else {
                        $this->agi->stream_file('weeks', '#');
                }
            }

            return -1;
        }

        if ($this->destination <= 0) {
            // do not play the error message if the destination number is not numeric
            // because most probably it wasn't entered by user (he has a phone keypad remember?)
            // it helps with using "use_dnid" and extensions.conf routing
            if (is_numeric($this->destination)) {
                $this->agi->stream_file("prepaid-invalid-digits", '#');
            }

            return -1;
        }

        // STRIP * FROM DESTINATION NUMBER
        $this->destination = str_replace(['*', '.'], '', $this->destination);

        $this->save_redial_number($this->destination);

        // LOOKUP RATE : FIND A RATE FOR THIS DESTINATION
        $resfindrate = $this->rateEngine->rate_engine_findrates($this->destination, $this->tariff);
        if ($resfindrate == 0) {
            $this->debug(self::ERROR, "ERROR ::> The phone number (" . $this->destination . ") cannot be dialed by the Rate engine, check that the Ratecard and Call Plan are well configured!");
        } else {
            $this->debug(self::DEBUG, "OK - RESFINDRATE::> " . $resfindrate);
        }

        // IF DONT FIND RATE
        if ($resfindrate == 0) {
            $this->agi->stream_file("prepaid-dest-unreachable", '#');

            return -1;
        }
        // CHECKING THE TIMEOUT
        $res_all_calcultimeout = $this->rateEngine->rate_engine_all_calcultimeout($this->credit);

        $this->debug(self::DEBUG, "RES_ALL_CALCULTIMEOUT ::> $res_all_calcultimeout");
        if (!$res_all_calcultimeout) {
            $this->agi->stream_file("prepaid-no-enough-credit", '#');

            return -1;
        }

        $this->timeout = $this->rateEngine->ratecard_obj[0]['timeout'];
        $timeout = $this->timeout;
        if ($this->agiconfig['cheat_on_announcement_time'] == 1) {
            $timeout = $this->rateEngine->ratecard_obj[0]['timeout_without_rules'];
        }

        $announce_time_correction = $this->rateEngine->ratecard_obj[0][61];
        $timeout = $timeout * $announce_time_correction;
        $this->fct_say_time_2_call($timeout, $this->rateEngine->ratecard_obj[0][12]);

        return 1;
    }


    /**
    * Function call_sip_iax_buddy : make the Sip/IAX free calls
    *
    *  @return 1 if Ok ; -1 if error
    **/
    public function call_sip_iax_buddy(): int
    {
        if (
            $this->agiconfig['use_dnid'] == 1
            && !in_array($this->dnid, $this->agiconfig['no_auth_dnid'])
            && strlen($this->dnid) > 2
        ) {
            $this->destination = $this->dnid;
        } else {
            $res_dtmf = $this->agi->get_data('prepaid-sipiax-enternumber', 6000, $this->config['global']['len_aliasnumber']);
            $this->debug(self::DEBUG, "RES DTMF : " . $res_dtmf["result"]);
            $this->destination = $res_dtmf["result"];

            if ($this->destination <= 0) {
                return -1;
            }
        }

        $this->save_redial_number($this->destination);

        $this->debug(self::DEBUG, "SIP o IAX DESTINATION : " . $this->destination);
        $sip_buddies = 0;
        $iax_buddies = 0;
        $destsip = '';
        $destiax = '';
        $dialstatus = null;
        $dest_username = "";

        $query = "SELECT name, cc_card.username FROM cc_iax_buddies, cc_card WHERE cc_iax_buddies.id_cc_card = cc_card.id AND useralias = ?";
        $params = [$this->destination];
        $row = $this->DBHandle->GetRow($query, $params);
        $this->debug(self::DEBUG, "Query: $query", $params, $row);

        if ($row !== false && $row !== []) {
            $iax_buddies = 1;
            [$destiax, $dest_username] = $row;
        }

        $card_alias = $this->destination;
        $query = "SELECT name, cc_card.username FROM cc_sip_buddies, cc_card WHERE cc_sip_buddies.id_cc_card = cc_card.id AND useralias = ?";
        $params = [$this->destination];
        $row = $this->DBHandle->GetRow($query, $params);
        $this->debug(self::DEBUG, "Query: $query", $params, $row);

        if ($row !== false && $row !== []) {
            $sip_buddies = 1;
            [$destsip, $dest_username] = $row;
        }

        if (!$sip_buddies && !$iax_buddies) {
            $this->agi->stream_file('prepaid-sipiax-num-nomatch', '#');

            return -1;
        }

        for ($k = 0; $k < $sip_buddies + $iax_buddies; $k++) {
            if ($k === 0 && $sip_buddies) {
                $this->tech = 'SIP';
                $this->destination = $destsip;
            } else {
                $this->tech = 'IAX2';
                $this->destination = $destiax;
            }

            if ($this->agiconfig['record_call'] == 1) {
                $command_mixmonitor = "MixMonitor $this->uniqueid.{$this->agiconfig['monitor_formatfile']},b";
                $this->agi->exec($command_mixmonitor);
                $this->debug(self::INFO, $command_mixmonitor);
            }

            $this->debug(self::DEBUG, "[EXEC SetCallerID : $this->useralias]");
            $this->agi->set_callerid($this->useralias);

            $dialparams = $this->agiconfig['dialcommand_param_sipiax_friend'];
            $dialstr = "$this->tech/$this->destination$dialparams";

            $this->debug(self::DEBUG, "app_callingcard sip/iax friend: Dialing '$dialstr' $this->tech Friend.\n");

            //# Channel: technology/number@ip_of_gw_to PSTN
            // Dial(IAX2/guest@misery.digium.com/s@default)
            $this->debug(self::INFO, "DIAL $dialstr");
            $this->agi->exec("DIAL $dialstr");

            $answeredtime = $this->agi->get_variable("ANSWEREDTIME");
            $answeredtime = $answeredtime['data'];
            $dialstatus = $this->agi->get_variable("DIALSTATUS");
            $dialstatus = $dialstatus['data'];

            if ($this->agiconfig['record_call'] == 1) {
                $this->debug(self::INFO, "EXEC StopMixMonitor ($this->uniqueid)");
                $this->agi->exec("StopMixMonitor");
            }

            $this->debug(self::INFO, "[$this->tech Friend]:[ANSWEREDTIME=$answeredtime-DIALSTATUS=$dialstatus]");

            //# Ooh, something actually happend!
            if ($dialstatus === "BUSY") {
                $answeredtime = 0;
                if ($this->agiconfig['busy_timeout'] > 0) {
                    $this->agi->exec("Busy " . $this->agiconfig['busy_timeout']);
                }
                $this->agi->stream_file('prepaid-isbusy', '#');
            } elseif ($dialstatus === "NOANSWER") {
                $answeredtime = 0;
                $this->agi->stream_file('prepaid-noanswer', '#');
            } elseif ($dialstatus === "CANCEL") {
                $answeredtime = 0;
            } elseif ($dialstatus === "ANSWER") {
                $this->debug(self::DEBUG, "->dialstatus : $dialstatus, answered time is " . $answeredtime . " \n");
            } elseif ($k + 1 === $sip_buddies + $iax_buddies) {
                $prompt = "prepaid-dest-unreachable";
                $this->agi->stream_file($prompt, '#');
            }

            if ($dialstatus === "CHANUNAVAIL" || $dialstatus === "CONGESTION") {
                continue;
            }

            if (strlen($this->dialstatus_rev_list[$dialstatus] ?? 0) > 0) {
                $terminatecauseid = $this->dialstatus_rev_list[$dialstatus];
            } else {
                $terminatecauseid = 0;
            }

            if ($answeredtime > 0) {
                $this->debug(self::DEBUG, "[CC_RATE_ENGINE_UPDATESYSTEM: (answeredtime=$answeredtime :: dialstatus=$dialstatus)]");

                $query = "INSERT INTO cc_call (uniqueid, sessionid, card_id, nasipaddress, starttime, sessiontime, calledstation, terminatecauseid, stoptime, sessionbill, id_tariffplan, id_ratecard, id_trunk, src, sipiax $this->CDR_CUSTOM_SQL) VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP - INTERVAL ? SECOND, ?, ?, ?, now(), '0', '0', '0', '0', ?, '1' $this->CDR_CUSTOM_VAL)";
                $params = [$this->uniqueid, $this->channel, $this->id_card, $this->hostname, $answeredtime, $answeredtime, $card_alias, $terminatecauseid, $this->CallerID];
                $this->DBHandle->Execute($query, $params);
                $this->debug(self::DEBUG, "Query: $query", $params);

                return 1;
            }
        }

        if ($this->voicemail) {
            if ($dialstatus === "CHANUNAVAIL" || $dialstatus === "CONGESTION" || $dialstatus === "NOANSWER") {
                // The following section will send the caller to VoiceMail
                // with the unavailable priority.
                $this->debug(self::INFO, "[STATUS] CHANNEL UNAVAILABLE - GOTO VOICEMAIL ($dest_username)");
                $this->agi->exec("VoiceMail", [$dest_username, "u"]);
            } elseif (($dialstatus === "BUSY")) {
                // The following section will send the caller to VoiceMail with the busy priority.
                $this->debug(self::INFO, "[STATUS] CHANNEL BUSY - GO VOICEMAIL ($dest_username)");
                $this->agi->exec("VoiceMail", [$dest_username, "b"]);
            }
        }

        return -1;
    }


    /**
    * Function call_did
    *
    * @param array|iterable $listdestination
    *         cc_did.id, cc_did_destination.id, billingtype, cc_did.id_trunk, destination, cc_did.id_trunk, voip_call
    **/
    public function call_did(array $listdestination)
    {
        $this->agiconfig['say_balance_after_auth'] = 0;
        $this->agiconfig['say_timetocall'] = 0;

        $callcount = 0;
        $dialstatus = null;
        $doibill = ($listdestination[0]["billingtype"] === "0" || $listdestination[0]["billingtype"] === "2");
        foreach ($listdestination as $dest) {
            $callcount++;

            $this->debug(self::INFO, "[A2Billing] DID call friend: FOLLOWME=$callcount (cardnumber:$dest[6]|destination:$dest[4]|tariff:$dest[3])\n");

            $this->agiconfig['cid_enable'] = 0;
            $this->accountcode = $dest["username"];
            $this->tariff      = (int)$dest["tariff"];
            $this->destination = $dest["destination"];
            $this->username    = $dest["username"];
            $this->useralias   = $dest["useralias"];

            if ($this->set_inuse) {
                $this->callingcard_acct_start_inuse();
            }

            // MAKE THE AUTHENTICATION TO GET ALL VALUE : CREDIT - EXPIRATION - ...
            if (!$this->callingcard_ivr_authenticate()) {
                $this->debug(self::INFO, "[A2Billing] DID call friend: AUTHENTICATION FAILS !!!\n");
                continue;
            }
            // CHECK IF DESTINATION IS SET
            if (strlen($dest["destination"]) === 0) {
                continue;
            }

            // IF VOIP CALL
            if ($dest["voip_call"]) {

                // RUN MIXMONITOR TO RECORD CALL
                if ($this->agiconfig['record_call']) {
                    $command_mixmonitor = "MixMonitor $this->uniqueid.{$this->agiconfig['monitor_formatfile']},b";
                    $this->agi->exec($command_mixmonitor);
                    $this->debug(self::INFO, $command_mixmonitor);
                }

                $max_long = 36000000; //Maximum 10 hours
                $time2call = $this->agiconfig['max_call_call_2_did'];
                $dialparams = $this->agiconfig['dialcommand_param_call_2did'];
                $dialparams = str_replace("%timeout%", min($time2call * 1000, $max_long), $dialparams);
                $dialparams = str_replace("%timeoutsec%", min($time2call, $max_long), $dialparams);
                $dialstr = $dest["destination"] . $dialparams;

                $this->debug(self::DEBUG, "[A2Billing] DID call friend: Dialing '$dialstr' Friend.\n");

                //# Channel: technology/number@ip_of_gw_to PSTN
                // Dial(IAX2/guest@misery.digium.com/s@default)
                $this->agi->exec("DIAL $dialstr");
                $this->debug(self::INFO, "DIAL $dialstr");

                $answeredtime = $this->agi->get_variable("ANSWEREDTIME");
                $answeredtime = (int)$answeredtime['data'];
                $dialstatus = $this->agi->get_variable("DIALSTATUS");
                $dialstatus = $dialstatus['data'];

                if ($this->agiconfig['record_call']) {
                    $this->agi->exec("StopMixMonitor");
                    $this->debug(self::INFO, "EXEC StopMixMonitor ($this->uniqueid)");
                }

                $this->debug(self::INFO, "[$dest[destination] Friend][followme=$callcount]:[ANSWEREDTIME=$answeredtime-DIALSTATUS=$dialstatus]");

                //# Ooh, something actually happend!
                if ($dialstatus === "BUSY") {
                    $answeredtime = 0;
                    if ($this->agiconfig['busy_timeout'] > 0) {
                        $this->agi->exec("Busy " . $this->agiconfig['busy_timeout']);
                    }
                    $this->agi->stream_file('prepaid-isbusy', '#');
                    if (count($listdestination) > $callcount) {
                        continue;
                    }
                } elseif ($dialstatus === "NOANSWER") {
                    $answeredtime = 0;
                    $this->agi->stream_file('prepaid-callfollowme', '#');
                    if (count($listdestination) > $callcount) {
                        continue;
                    }
                } elseif ($dialstatus === "CANCEL") {
                    // Call cancelled, no need to follow-me
                    return;
                } elseif ($dialstatus === "ANSWER") {
                    $this->debug(self::DEBUG,
                        "[A2Billing] DID call friend: dialstatus : $dialstatus, answered time is $answeredtime\n");
                } elseif (($dialstatus === "CHANUNAVAIL") || ($dialstatus === "CONGESTION")) {
                    $answeredtime = 0;
                    if (count($listdestination) > $callcount) {
                        continue;
                    }
                } else {
                    $this->agi->stream_file('prepaid-callfollowme', '#');
                    if (count($listdestination) > $callcount) {
                        continue;
                    }
                }

                if ($answeredtime > 0) {

                    $this->debug(self::INFO, "[DID CALL - LOG CC_CALL: FOLLOWME=$callcount - (answeredtime=$answeredtime :: dialstatus=$dialstatus)]");

                    $terminatecauseid = $this->dialstatus_rev_list[$dialstatus] ?? 0;

                    $query = "INSERT INTO cc_call (uniqueid, sessionid, card_id, nasipaddress, starttime, sessiontime, calledstation, " .
                        " terminatecauseid, stoptime, sessionbill, id_tariffgroup, id_tariffplan, id_ratecard, id_trunk, src, sipiax $this->CDR_CUSTOM_SQL) VALUES " .
                        "(?, ?, ?, ?, CURRENT_TIMESTAMP - INTERVAL ? SECOND, ?, ?, ?, now(), '0', '0', '0', '0', '0', ?, '3' $this->CDR_CUSTOM_VAL)";

                    $params = [$this->uniqueid, $this->channel, $this->id_card, $this->hostname, $answeredtime, $answeredtime, $dest["destination"], $terminatecauseid, $this->CallerID];
                    $this->DBHandle->Execute($query, $params);
                    $this->debug(self::DEBUG, "Query: $query", $params);

                    // CC_DID & CC_DID_DESTINATION - cc_did.id, cc_did_destination.id
                    $query = "UPDATE cc_did SET secondusedreal = secondusedreal + ? WHERE id = ?";
                    $params = [$answeredtime, $dest["id_cc_did"]];
                    $this->DBHandle->Execute($query, $params);
                    $this->debug(self::DEBUG, "Query: $query", $params);
                    $this->debug(self::INFO, "UPDATE DID");

                    $query = "UPDATE cc_did_destination SET secondusedreal = secondusedreal + ? WHERE id = ?";
                    $params = [$answeredtime, $dest["id"]];
                    $this->DBHandle->Execute($query, $params);
                    $this->debug(self::DEBUG, "Query: $query", $params);
                    $this->debug(self::INFO, "UPDATE DID_DESTINATION");

                    $this->bill_did_aleg($dest, $answeredtime);

                    return;
                }

            // ELSEIF NOT VOIP CALL
            } else {

                $this->agiconfig['use_dnid'] = 1;
                $this->agiconfig['say_timetocall'] = 0;

                $this->extension = $this->dnid = $this->destination = $dest["destination"];

                if ($this->callingcard_ivr_authorize(0) === 1) {

                    // PERFORM THE CALL
                    $result_callperf = $this->rateEngine->rate_engine_performcall($this->destination);
                    if (!$result_callperf) {
                        $prompt = "prepaid-callfollowme";
                        $this->agi->stream_file($prompt, '#');
                        continue;
                    }

                    $dialstatus = $this->rateEngine->dialstatus;
                    if ($this->rateEngine->dialstatus === "NOANSWER" || $this->rateEngine->dialstatus === "BUSY" || $this->rateEngine->dialstatus === "CHANUNAVAIL" || $this->rateEngine->dialstatus === "CONGESTION") {
                        continue;
                    }

                    if ($this->rateEngine->dialstatus === "CANCEL") {
                        break;
                    }

                    // INSERT CDR & UPDATE SYSTEM
                    $this->rateEngine->rate_engine_updatesystem($this->destination, $doibill, true);
                    // CC_DID & CC_DID_DESTINATION - cc_did.id, cc_did_destination.id
                    $query = "UPDATE cc_did SET secondusedreal = secondusedreal + ? WHERE id = ?";
                    $params = [$this->rateEngine->answeredtime, $dest["id_cc_did"]];
                    $this->DBHandle->Execute($query, $params);
                    $this->debug(self::DEBUG, "Query: $query", $params);
                    $this->debug(self::DEBUG, "UPDATE DID");

                    $query = "UPDATE cc_did_destination SET secondusedreal = secondusedreal + ? WHERE id = ?";
                    $params = [$this->rateEngine->answeredtime, $dest["id"]];
                    $this->DBHandle->Execute($query, $params);
                    $this->debug(self::DEBUG, "Query: $query", $params);
                    $this->debug(self::DEBUG, "UPDATE DID_DESTINATION");

                    $this->bill_did_aleg($dest, $this->rateEngine->answeredtime);

                    // THEN STATUS IS ANSWER
                    break;
                }
            }
        }// END FOR

        if ($this->voicemail) {
            if (($dialstatus === "CHANUNAVAIL") || ($dialstatus === "CONGESTION") || ($dialstatus === "NOANSWER") || ($dialstatus === "BUSY")) {
                // The following section will send the caller to VoiceMail with the unavailable priority.\
                $dest_username = $this->username;
                $this->debug(self::INFO, "[STATUS] CHANNEL ($dialstatus) - GOTO VOICEMAIL ($dest_username)");
                $this->agi->exec("VoiceMail", [$dest_username, "s"]);
            }
        }
    }

    public function call_2did(array $listdestination)
    {
        $card_number = $this->username; // username of the caller
        $nbused = $this->nbused;
        $dialstatus = null;
        $new_username = '';
        $connection_charge = (float)$listdestination[0]["connection_charge"];
        $selling_rate = (float)$listdestination[0]["selling_rate"];

        if ($connection_charge === 0.0 && $selling_rate === 0.0) {
            $call_did_free = true;
            $this->debug(self::INFO, "[A2Billing] DID call free ");
        } else {
            $call_did_free = false;
            $this->debug(self::INFO, "[A2Billing] DID call not free: (connection charge:" . $connection_charge . "|selling_rate:" . $selling_rate);
        }

        $doibill = ($listdestination[0]["billingtype"] === "0" || $listdestination[0]["billingtype"] === "2");

        $time2call = $this->agiconfig['max_call_call_2_did'];
        if (!$call_did_free) {
            if ($this->typepaid === 0) {
                if ($this->credit < $this->agiconfig['min_credit_2call']) {
                    $time2call = 0;
                } else {
                    $credit_without_charge = $this->credit - abs($connection_charge);
                    if ($credit_without_charge > 0 && $selling_rate !== 0.0) {
                        $time2call = intval($credit_without_charge / abs($selling_rate)) * 60;
                    }
                }
            } elseif ($this->credit <= -$this->creditlimit) {
                $time2call = 0;
            } else {
                $credit_without_charge = $this->credit + abs($this->creditlimit) - abs($connection_charge);
                if ($credit_without_charge > 0 && $selling_rate !== 0.0) {
                    $time2call = intval($credit_without_charge / abs($selling_rate)) * 60;
                }
            }
        }

        $this->timeout = $time2call;
        $callcount = 0;
        $accountcode = $this->accountcode;
        $username = $this->username;
        $useralias = $this->useralias;
        $set_inuse = $this->set_inuse;

        foreach ($listdestination as $dest) {
            $callcount++;
            $this->agiconfig['cid_enable'] = 0;
            $this->accountcode = $dest["username"];
            $this->tariff      = (int)$dest["tariff"];
            $this->destination = $dest["did"];
            $new_username      = $dest["username"];
            $this->useralias   = $dest["useralias"];
            $this->id_card     = (int)$dest["id_cc_card"];
            $this->debug(self::INFO, "[A2Billing] DID call friend: FOLLOWME=$callcount (cardnumber:$this->accountcode|destination:$this->destination|tariff:$this->tariff)\n");

            // CHECK IF DESTINATION IS SET
            if (strlen($dest["destination"]) === 0) {
                continue;
            }

            // IF VOIP CALL
            if ($dest["voip_call"]) {

                // RUN MIXMONITOR TO RECORD CALL
                if ($this->agiconfig['record_call']) {
                    $command_mixmonitor = "MixMonitor $this->uniqueid.{$this->agiconfig['monitor_formatfile']},b";
                    $this->agi->exec($command_mixmonitor);
                    $this->debug(self::INFO, $command_mixmonitor);
                }

                $max_long = 36000000; //Maximum 10 hours
                if ($call_did_free) {
                    $this->fct_say_time_2_call($time2call);
                    $dialparams = $this->agiconfig['dialcommand_param_call_2did'];
                    $dialparams = str_replace("%timeout%", min($time2call * 1000, $max_long), $dialparams);
                    $dialparams = str_replace("%timeoutsec%", min($time2call, $max_long), $dialparams);
                    $dialstr = $dest["destination"] . $dialparams;

                    $this->debug(self::DEBUG, "[A2Billing] DID call friend: Dialing '$dialstr' Friend.\n");
                    //# Channel: technology/number@ip_of_gw_to PSTN
                    // Dial(IAX2/guest@misery.digium.com/s@default)
                    $this->agi->exec("DIAL $dialstr");
                    $this->debug(self::INFO, "DIAL $dialstr");
                } else {

                    $this->debug(self::INFO, "TIME TO CALL : $time2call");
                    $this->fct_say_time_2_call($time2call, $selling_rate);
                    $dialparams = str_replace("%timeout%", min($time2call * 1000, $max_long), $this->agiconfig['dialcommand_param']);
                    $dialparams = str_replace("%timeoutsec%", min($time2call, $max_long), $dialparams);

                    if ($this->agiconfig['record_call']) {
                        $command_mixmonitor = "MixMonitor $this->uniqueid.{$this->agiconfig['monitor_formatfile']},b";
                        $this->agi->exec($command_mixmonitor);
                        $this->debug(self::INFO, $command_mixmonitor);
                    }
                    $dialstr = $dest["destination"] . $dialparams;
                    $this->agi->exec("DIAL $dialstr");
                    $this->debug(self::INFO, "DIAL $dialstr");
                    if ($this->agiconfig['record_call']) {
                        $this->agi->exec("StopMixMonitor");
                        $this->debug(self::INFO, "EXEC StopMixMonitor (" . $this->uniqueid . ")");
                    }
                }

                $answeredtime = $this->agi->get_variable("ANSWEREDTIME");
                $answeredtime = (int)$answeredtime['data'];
                $dialstatus = $this->agi->get_variable("DIALSTATUS");
                $dialstatus = $dialstatus['data'];

                if ($this->agiconfig['record_call']) {
                    $this->agi->exec("StopMixMonitor");
                    $this->debug(self::INFO, "EXEC StopMixMonitor ($this->uniqueid)");
                }

                $this->debug(self::INFO, "[$dest[destination] Friend][followme=$callcount]:[ANSWEREDTIME=$answeredtime-DIALSTATUS=$dialstatus]");

                //# Ooh, something actually happend!
                if ($dialstatus === "BUSY") {
                    $answeredtime = 0;
                    if ($this->agiconfig['busy_timeout']) {
                        $this->agi->exec("Busy " . $this->agiconfig['busy_timeout']);
                    }
                    if (count($listdestination) > $callcount) {
                        continue;
                    } else {
                        $this->agi->stream_file('prepaid-isbusy', '#');
                    }
                } elseif ($dialstatus === "NOANSWER") {
                    $answeredtime = 0;
                    $this->agi->stream_file('prepaid-callfollowme', '#');
                    if (count($listdestination) > $callcount) {
                        continue;
                    }
                } elseif ($dialstatus === "CANCEL") {
                    // Call cancelled, no need to follow-me
                    return;
                } elseif ($dialstatus === "ANSWER") {
                    $this->debug(self::DEBUG, "[A2Billing] DID call friend: dialstatus : $dialstatus, answered time is $answeredtime\n");
                } elseif (($dialstatus === "CHANUNAVAIL") || ($dialstatus === "CONGESTION")) {
                    $answeredtime = 0;
                    if (count($listdestination) > $callcount) {
                        continue;
                    }
                } else {
                    $this->agi->stream_file('prepaid-callfollowme', '#');
                    if (count($listdestination) > $callcount) {
                        continue;
                    }
                }

                if ($answeredtime > 0) {

                    $this->debug(self::INFO, "[DID CALL - LOG CC_CALL: FOLLOWME=$callcount - (answeredtime=$answeredtime :: dialstatus=$dialstatus :: call_did_free=$call_did_free)]");

                    $terminatecauseid = $this->dialstatus_rev_list[$dialstatus] ?? 0;

                    // A-LEG below to the owner of the DID
                    if ($call_did_free) {
                        //CALL2DID CDR is free
                        /* CDR A-LEG OF DID CALL */
                        $cost = 0;
                        $cdr_dest = $dest["did"];
                    } else {
                        //CALL2DID CDR is not free
                        $cost = ($answeredtime / 60) * abs($selling_rate) + abs($connection_charge);
                        /* CDR A-LEG OF DID CALL */
                        $cdr_dest = $listdestination[0]["did"];
                    }
                    $query = "INSERT INTO cc_call (uniqueid, sessionid, card_id, nasipaddress, starttime, sessiontime, calledstation, terminatecauseid, stoptime, sessionbill, id_tariffgroup, id_tariffplan, id_ratecard, id_trunk, src, sipiax $this->CDR_CUSTOM_SQL) " .
                        "VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP - INTERVAL ? SECOND, ?, ?, ?, now(), ?, '0', '0', '0', '0', ?, '3' $this->CDR_CUSTOM_VAL)";
                    $params = [$this->uniqueid, $this->channel, $this->id_card, $this->hostname, $answeredtime, $answeredtime, $cdr_dest, $terminatecauseid, $cost, $this->CallerID];
                    $this->DBHandle->Execute($query, $params);
                    $this->debug(self::DEBUG, "Query: $query", $params);

                    // Update the account
                    $firstuse = $nbused ? "" : "firstusedate = now(),";
                    $query = "UPDATE cc_card SET credit= credit - ?, lastuse = now(), $firstuse nbused = nbused + 1 WHERE username = ?";
                    $params = [a2b_round(abs($cost)), $card_number];
                    $this->DBHandle->Execute($query, $params);
                    $this->debug(self::DEBUG, "Query: $query", $params);

                    // CC_DID & CC_DID_DESTINATION - cc_did.id, cc_did_destination.id
                    $query = "UPDATE cc_did SET secondusedreal = secondusedreal + ? WHERE id = ?";
                    $params = [$answeredtime, $dest["id_cc_did"]];
                    $this->DBHandle->Execute($query, $params);
                    $this->debug(self::DEBUG, "Query: $query", $params);

                    $query = "UPDATE cc_did_destination SET secondusedreal = secondusedreal + ? WHERE id = ?";
                    $params = [$answeredtime, $dest["id"]];
                    $this->DBHandle->Execute($query, $params);
                    $this->debug(self::DEBUG, "Query: $query", $params);

                    #This is a call from user to DID
                    #we will change the B-Leb using the did bill_did_aleg function
                    $this->bill_did_aleg($listdestination[0], $answeredtime);
                }
            // ELSEIF NOT VOIP CALL
            } else {

                $this->agiconfig['use_dnid'] = 1;
                $this->agiconfig['say_timetocall'] = 0;

                $this->extension = $this->dnid = $this->destination = $dest["destination"];

                if ($this->callingcard_ivr_authorize(0) === 1) {
                    // check the min to call
                    if (!$call_did_free) {
                        $this->timeout = min($this->timeout, $time2call);
                    }
                    $this->fct_say_time_2_call($this->timeout, $selling_rate);

                    // PERFORM THE CALL
                    $result_callperf = $this->rateEngine->rate_engine_performcall($this->destination);
                    if (!$result_callperf) {
                        $prompt = "prepaid-callfollowme";
                        $this->agi->stream_file($prompt, '#');
                        continue;
                    }

                    $dialstatus = $this->rateEngine->dialstatus;
                    if ($dialstatus == "NOANSWER" || $dialstatus == "BUSY" || $dialstatus == "CHANUNAVAIL" || $dialstatus == "CONGESTION") {
                        continue;
                    } elseif ($dialstatus == "CANCEL") {
                        break;
                    }

                    // INSERT CDR & UPDATE SYSTEM
                    $this->rateEngine->rate_engine_updatesystem($this->destination, $doibill, true);

                    // CC_DID & CC_DID_DESTINATION - cc_did.id, cc_did_destination.id
                    $query = "UPDATE cc_did SET secondusedreal = secondusedreal + ? WHERE id = ?";
                    $params = [$this->rateEngine->answeredtime, $dest["id_cc_did"]];
                    $this->DBHandle->Execute($query, $params);
                    $this->debug(self::DEBUG, "Query: $query", $params);

                    $query = "UPDATE cc_did_destination SET secondusedreal = secondusedreal + ? WHERE id = ?";
                    $params = [$this->rateEngine->answeredtime, $dest["id"]];
                    $this->DBHandle->Execute($query, $params);
                    $this->debug(self::DEBUG, "Query: $query", $params);

                    $answeredtime = $this->agi->get_variable("ANSWEREDTIME");
                    $answeredtime = (int)$answeredtime['data'];
                    $dialstatus = $this->agi->get_variable("DIALSTATUS");
                    $dialstatus = $dialstatus['data'];

                    $terminatecauseid = $this->dialstatus_rev_list[$dialstatus] ?? 0;

                    // A-LEG below to the owner of the DID
                    if ($call_did_free) {
                        //CALL2DID CDR is free
                        $cost = 0;
                        $cdr_dest = $dest["did"];
                    } else {
                        //CALL2DID CDR is not free
                        $cost = ($answeredtime / 60) * abs($selling_rate) + abs($connection_charge);
                        /* CDR A-LEG OF DID CALL */
                        $cdr_dest = $listdestination[0]["did"];
                    }
                    $query = "INSERT INTO cc_call (uniqueid, sessionid, card_id, nasipaddress, starttime, sessiontime, calledstation, terminatecauseid, stoptime, sessionbill, id_tariffgroup, id_tariffplan, id_ratecard, id_trunk, src, sipiax $this->CDR_CUSTOM_SQL) " .
                        "VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP - INTERVAL ? SECOND, ?, ?, ?, now(), ?, '0', '0', '0', '0', ?, '3' $this->CDR_CUSTOM_VAL)";
                    $params = [$this->uniqueid, $this->channel, $this->id_card, $this->hostname, $answeredtime, $answeredtime, $cdr_dest, $terminatecauseid, $cost, $this->CallerID];
                    $this->DBHandle->Execute($query, $params);
                    $this->debug(self::DEBUG, "Query: $query", $params);

                    $firstuse = $nbused ? "" : "firstusedate = now(),";
                    $query = "UPDATE cc_card SET credit= credit - ? , lastuse = now(), $firstuse nbused = nbused + 1 WHERE username = ?";
                    $params = [a2b_round(abs($cost)), $card_number];
                    $this->DBHandle->Execute($query, $params);
                    $this->debug(self::DEBUG, "Query: $query", $params);

                    #This is a call from user to DID, we dont want to charge the A-leg
                    $this->bill_did_aleg($listdestination[0], $answeredtime);

                    break;
                }
            }
        }// END FOR

        if ($this->voicemail) {
            if ($dialstatus === "CHANUNAVAIL" || $dialstatus === "CONGESTION" || $dialstatus === "NOANSWER" || $dialstatus === "BUSY") {
                // The following section will send the caller to VoiceMail with the unavailable priority.\
                $dest_username = $new_username;
                $this->debug(self::INFO, "[STATUS] CHANNEL ($dialstatus) - GOTO VOICEMAIL ($dest_username)");
                $this->agi->exec("VoiceMail", [$dest_username,"s"]);
            }
        }
        $this->accountcode = $accountcode;
        $this->username = $username;
        $this->useralias = $useralias;
        $this->set_inuse = $set_inuse;
    }

    /*
    * Function to bill the A-Leg on DID Calls
    */
    public function bill_did_aleg(array $dest, int $b_leg_answeredtime = 0)
    {

        $start_time = $this->G_startime;
        $stop_time = time();
        $timeinterval = (int)$dest["aleg_timeinterval"];
        $this->debug(self::DEBUG, "[bill_did_aleg]: START TIME peak=" . $this->calculate_time_condition($start_time, $timeinterval, "peak") . " ,offpeak=" . $this->calculate_time_condition($start_time, $timeinterval, "offpeak") . " ");
        $this->debug(self::DEBUG, "[bill_did_aleg]: STOP TIME peak=" . $this->calculate_time_condition($stop_time, $timeinterval, "peak") . " ,offpeak=" . $this->calculate_time_condition($stop_time, $timeinterval, "offpeak") . " ");

        //TO DO - for now we use peak values only if whole call duration is inside a peak time interval. Should be devided in two parts - peak and off peak duration. May be later.
        if (
            $this->calculate_time_condition($start_time, $timeinterval, "peak")
            && !$this->calculate_time_condition($start_time, $timeinterval, "offpeak")
            && $this->calculate_time_condition($stop_time, $timeinterval, "peak")
            && !$this->calculate_time_condition($stop_time, $timeinterval, "offpeak")
        ) {
            # We have PEAK time
            $this->debug(self::INFO, "[bill_did_aleg]: We have PEAK time.");
            $aleg_carrier_connect_charge = (float)$dest["aleg_carrier_connect_charge"];
            $aleg_carrier_cost_min = (float)$dest["aleg_carrier_cost_min"];
            $aleg_retail_connect_charge = (float)$dest["aleg_retail_connect_charge"];
            $aleg_retail_cost_min = (float)$dest["aleg_retail_cost_min"];

            $aleg_carrier_initblock = (int)$dest["aleg_carrier_initblock"];
            $aleg_carrier_increment = (int)$dest["aleg_carrier_increment"];
            $aleg_retail_initblock = (int)$dest["aleg_retail_initblock"];
            $aleg_retail_increment = (int)$dest["aleg_retail_increment"];
            #TODO use the above variables to define the time2call
        } else {
            #We have OFF-PEAK time
            $this->debug(self::INFO, "[bill_did_aleg]: We have OFF-PEAK time.");
            $aleg_carrier_connect_charge = $dest["aleg_carrier_connect_charge_offp"];
            $aleg_carrier_cost_min = $dest["aleg_carrier_cost_min_offp"];
            $aleg_retail_connect_charge = $dest["aleg_retail_connect_charge_offp"];
            $aleg_retail_cost_min = $dest["aleg_retail_cost_min_offp"];

            $aleg_carrier_initblock = $dest["aleg_carrier_initblock_offp"];
            $aleg_carrier_increment = $dest["aleg_carrier_increment_offp"];
            $aleg_retail_initblock = $dest["aleg_retail_initblock_offp"];
            $aleg_retail_increment = $dest["aleg_retail_increment_offp"];
            #TODO use the above variables to define the time2call
        }

        $this->debug(
            self::INFO,
            "[bill_did_aleg]:[aleg_carrier_connect_charge=$aleg_carrier_connect_charge;\
                aleg_carrier_cost_min=$aleg_carrier_cost_min;\
                aleg_retail_connect_charge=$aleg_retail_connect_charge;\
                aleg_retail_cost_min=$aleg_retail_cost_min;\
                aleg_carrier_initblock=$aleg_carrier_initblock;\
                aleg_carrier_increment=$aleg_carrier_increment;\
                aleg_retail_initblock=$aleg_retail_initblock;\
                aleg_retail_increment=$aleg_retail_increment - b_leg_answeredtime=$b_leg_answeredtime]"
        );

        $this->dnid = $dest["did"];

        // SET CORRECTLY THE CALLTIME FOR THE 1st LEG
        $aleg_answeredtime = $this->agiconfig['answer_call'] == 1 ? time() - $this->G_startime : $b_leg_answeredtime;

        $terminatecauseid = 1; // ANSWERED

        # if we add a new CDR for A-Leg
        if ($aleg_carrier_connect_charge || $aleg_carrier_cost_min || $aleg_retail_connect_charge || $aleg_retail_cost_min) {
            # duration of the call for the A-Leg is since the start date

            $this->debug(self::INFO, "[DID CALL]:[A-Leg->dnid=$this->dnid; answeredtime=$aleg_answeredtime]");

            # Carrier Minimum Duration and Billing Increment
            $aleg_carrier_callduration = max($aleg_answeredtime, $aleg_carrier_initblock);

            if (($aleg_carrier_increment > 0) && ($aleg_carrier_callduration > $aleg_carrier_initblock)) {
                $mod_sec = $aleg_carrier_callduration % $aleg_carrier_increment; // 12 = 30 % 18
                if ($mod_sec > 0) {
                    $aleg_carrier_callduration += ($aleg_carrier_increment - $mod_sec); // 30 += 18 - 12
                }
            }

            # Retail Minimum Duration and Billing Increment
            $aleg_retail_callduration = max($aleg_answeredtime, $aleg_retail_initblock);

            if (($aleg_retail_increment > 0) && ($aleg_retail_callduration > $aleg_retail_initblock)) {
                $mod_sec = $aleg_retail_callduration % $aleg_retail_increment; // 12 = 30 % 18
                if ($mod_sec > 0) {
                    $aleg_retail_callduration += ($aleg_retail_increment - $mod_sec);
                } // 30 += 18 - 12
            }

            $aleg_carrier_cost = 0;
            $aleg_carrier_cost += $aleg_carrier_connect_charge;
            $aleg_carrier_cost += ($aleg_carrier_callduration / 60) * $aleg_carrier_cost_min;

            $aleg_retail_cost = 0;
            $aleg_retail_cost += $aleg_retail_connect_charge;
            $aleg_retail_cost += ($aleg_retail_callduration / 60) * $aleg_retail_cost_min;
            if ($aleg_retail_cost) {
                // update card
                $query = "UPDATE cc_card SET credit= credit - ? WHERE username = ?";
                $params = [a2b_round($aleg_retail_cost), $this->username];
                $this->DBHandle->Execute($query, $params);
                $this->debug(self::DEBUG, "Query: $query", $params);
                $this->debug(self::INFO, "[DID CALL - (id_card=$this->id_card)]");
            }
        } else {
            // Zero on rate
            $aleg_carrier_cost = 0;
            $aleg_retail_cost = 0;
        }
        $QUERY_COLUMN = " uniqueid, sessionid, card_id, nasipaddress, starttime, sessiontime, real_sessiontime, calledstation, terminatecauseid, stoptime, sessionbill, id_tariffgroup, id_tariffplan, id_ratecard, id_trunk, src, sipiax, buycost, dnid";
        $calltype = '7'; // DID-ALEG
        $query = "INSERT INTO cc_call ($QUERY_COLUMN $this->CDR_CUSTOM_SQL) " .
            "VALUES (?, ?, ?, ?, NOW() - INTERVAL ? SECOND, ?, ?, ?, ?, NOW(), ?, 0, 0, 0, 0, ?, ?, ?, ? $this->CDR_CUSTOM_VAL)";
        $params = [
            $this->uniqueid,
            $this->channel,
            $this->id_card,
            $this->hostname,
            $aleg_answeredtime,
            $aleg_answeredtime,
            $aleg_answeredtime,
            $dest["did"],
            $terminatecauseid,
            a2b_round($aleg_retail_cost),
            $this->CallerID,
            $calltype,
            $aleg_carrier_cost,
            $this->dnid
        ];

        $this->DBHandle->Execute($query, $params);
        $this->debug(self::DEBUG, "Query: $query", $params);
        $this->debug(self::INFO, "[DID CALL ZERO]");
    }


    public function fct_say_time_2_call(int $timeout, int $rate = 0)
    {
        // set destination and timeout
        // say 'you have x minutes and x seconds'
        $minutes = intval($timeout / 60);
        $seconds = $timeout % 60;

        $this->debug(self::DEBUG, "TIMEOUT::>$this->timeout : minutes=$minutes - seconds=$seconds");
        if ($timeout <= 10) {
            $prompt = "prepaid-no-enough-credit";
            $this->agi->stream_file($prompt, '#');
            return;
        }

        if ($this->agiconfig['say_rateinitial'] == 1) {
            $this->fct_say_rate($rate);
        }

        if ($this->agiconfig['say_timetocall'] == 1) {
            $this->agi->stream_file('prepaid-you-have', '#');
            if ($minutes > 0) {
                if ($minutes === 1) {
                    if (strtolower($this->current_language) === 'ru') {
                        $this->agi->stream_file('digits/1f', '#');
                    } else {
                        $this->agi->say_number($minutes);
                    }
                    $this->agi->stream_file('prepaid-minute', '#');
                } else {
                    $this->agi->say_number($minutes);
                    if ((strtolower($this->current_language) === 'ru') && (($minutes % 10 == 2) || ($minutes % 10 == 3) || ($minutes % 10 == 4))) {
                        // test for the specific grammatical rules in RUssian
                        $this->agi->stream_file('prepaid-minute2', '#');
                    } else {
                        $this->agi->stream_file('prepaid-minutes', '#');
                    }
                }
            }
            if ($seconds > 0 && ($this->agiconfig['disable_announcement_seconds'] == 0 || $minutes === 0)) {
                if ($minutes > 0) {
                    $this->agi->stream_file('vm-and', '#');
                }
                if ($seconds === 1) {
                    if ((strtolower($this->current_language) === 'ru')) {
                        $this->agi->stream_file('digits/1f', '#');
                    } else {
                        $this->agi->say_number($seconds);
                        $this->agi->stream_file('prepaid-second', '#');
                    }
                } else {
                    $this->agi->say_number($seconds);
                    if (strtolower($this->current_language) === 'ru' && ($seconds % 10 === 2 || $seconds % 10 === 3 || $seconds % 10 === 4)) {
                        // test for the specific grammatical rules in RUssian
                        $this->agi->stream_file('prepaid-second2', '#');
                    } else {
                        $this->agi->stream_file('prepaid-seconds', '#');
                    }
                }
            }
        }
    }

    /**
    * Function to play the balance
    * format : "you have 100 dollars and 28 cents"
    *
    * @param float $credit
    **/
    public function fct_say_balance(float $credit, bool $fromvoucher = false): string
    {
        if (isset($this->agiconfig['agi_force_currency']) && strlen($this->agiconfig['agi_force_currency']) == 3) {
            $this->currency = $this->agiconfig['agi_force_currency'];
        }

        $this->debug(self::DEBUG, "[CURRENCY : $this->currency]");
        $curr = strtoupper($this->currency);
        if (!is_numeric($this->currencies_list[$curr] ?? null)) {
            $mycur = 1;
        } else {
            $mycur = $this->currencies_list[$curr];
        }

        $credit_cur = $credit / $mycur;
        [$units, $cents] = explode('.', sprintf('%01.2f', $credit_cur));
        $curr = strtolower($this->currency);
        $lang = strtolower($this->current_language);

        $this->debug(self::DEBUG, "[BEFORE: $credit_cur SPRINTF : " . sprintf('%01.2f', $credit_cur) . "]");

        if (isset($this->agiconfig['currency_association_internal'][$curr])) {
            $units_audio = $this->agiconfig['currency_association_internal'][$curr];
            // substract the last character ex: dollars->dollar
            $unit_audio = substr($units_audio, 0, -1);
        } else {
            $units_audio = $this->agiconfig['currency_association_internal']['all'];
            $unit_audio = $units_audio;
        }

        $cents_audio = $this->agiconfig['currency_cents_association_internal'][$curr] ?? "prepaid-cents";

        $cent_audio = $cents_audio === "prepaid-pence" ? "prepaid-penny" : substr($cents_audio, 0, -1);

        // say 'you have x dollars and x cents'
        $say = [];
        $say[] = $fromvoucher ? "prepaid-account_refill" : "prepaid-you-have";
        $units = intval($units);
        $cents = intval($cents);
        if ($units === 0 && $cents === 0) {
            $say[] = 0;
            $say[] = ($lang === 'ru' && $curr === 'usd') ? $units_audio : $unit_audio;
        } else {
            $say[] = $units;
            if ($units > 1) {
                if ($lang === 'ru' && $curr === 'usd' && ($units % 10 === 0 || $units % 10 === 2 || $units % 10 === 3 || $units % 10 === 4)) {
                    // test for the specific grammatical rules in Russian
                    $say[] = 'dollar2';
                } elseif ($lang === 'ru' && $curr === 'usd' && $units % 10 === 1) {
                    // test for the specific grammatical rules in Russian
                    $say[] = $unit_audio;
                } else {
                    $say[] = $units_audio;
                }
            } elseif ($lang === 'ru' && $curr === 'usd' && $units === 0) {
                $say[] = $units_audio;
            } else {
                $say[] = $unit_audio;
            }

            if ($units > 0 && $cents > 0) {
                $say[] = 'vm-and';
            }
            if ($cents > 0) {
                $say[] = $cents;
                if ($cents > 1) {
                    if ($lang === 'ru' && $curr === 'usd' && ($cents % 10 === 2 || $cents % 10 === 3 || $cents % 10 === 4)) {
                        // test for the specific grammatical rules in RUssian
                        $say[] = 'prepaid-cent2';
                    } elseif ($lang === 'ru' && $curr === 'usd' && $cents % 10 === 1) {
                        // test for the specific grammatical rules in RUssian
                        $say[] = $cent_audio;
                    } else {
                        $say[] = $cents_audio;
                    }
                } else {
                    $say[] = $cent_audio;
                }
            }
        }

        // now we will play audios of the balance prompt expecting input
        $entered = '';
        // now start saying and get ready to be interrupted
        foreach ($say as $item) {
            if (is_integer($item)) {
                $res = $this->agi->say_number($item, '1234567890*#');
            } else {
                $res = $this->agi->stream_file($item, '1234567890*#');
            }
            if ($res['result'] > 0) {
                $entered .= chr($res['result']);
                break;
            }
        }

        // if say balance was interupted, let customer to enter remaing digits
        // it is essential that silence/1 file exists
        if (strlen($entered)) {
            $res = $this->agi->get_data('silence/1', 6000, 20);
            $entered .= $res['result'];
        }

        return $entered;
    }


    /**
    *  Function to play the initial rate
    *  format : "the cost of the call is 7 dollars and 50 cents per minutes"
    */
    public function fct_say_rate(float $rate)
    {
        if (isset($this->agiconfig['agi_force_currency']) && strlen($this->agiconfig['agi_force_currency']) == 3) {
            $this->currency = $this->agiconfig['agi_force_currency'];
        }

        $this->debug(self::DEBUG, "[CURRENCY : $this->currency]");
        $curr = strtoupper($this->currency);
        if (!is_numeric($this->currencies_list[$curr] ?? null)) {
            $mycur = 1;
        } else {
            $mycur = $this->currencies_list[$curr];
        }
        $credit_cur = $rate / $mycur;

        [$units, $cents] = preg_split('/[.]/', sprintf('%f',$credit_cur));
        $point = '';
        if (substr($cents, 2) > 0) {
            $point = substr($cents, 2, 1);
        }
        if (strlen($cents) > 2) {
            $cents = substr($cents, 0, 2);
        }
        if ($units === '') {
            $units = 0;
        }
        if ($cents === '') {
            $cents = 0;
        }
        if ($point === '') {
            $point = 0;
        } elseif (strlen($cents) === 1) {
            $cents .= '0';
        }

        $curr = strtolower($this->currency);
        $lang = strtolower($this->current_language);

        if (isset($this->agiconfig['currency_association_internal'][$curr])) {
            $units_audio = $this->agiconfig['currency_association_internal'][$curr];
            // leave the last character ex: dollars->dollar
            $unit_audio = substr($units_audio, 0, -1);
        } else {
            $units_audio = $this->agiconfig['currency_association_internal']['all'];
            $unit_audio = $units_audio;
        }
        $cent_audio = 'prepaid-cent';
        $cents_audio = 'prepaid-cents';

        // say 'the cost of the call is '
        $this->agi->stream_file('prepaid-cost-call', '#');
        $units = intval($units);
        $cents = intval($cents);
        $point = intval($point);

        if ($units === 0 && $cents === 0 && !$this->agiconfig['play_rate_cents_if_lower_one'] && !($this->agiconfig['play_rate_cents_if_lower_one'] && $point === 0)) {
            $this->agi->say_number(0);
            $this->agi->stream_file($unit_audio, '#');
        } else {
            if ($units >= 1) {
                $this->agi->say_number($units);

                if ($lang === 'ru' && $curr === 'usd' && ($units % 10 === 2 || $units % 10 === 3 || $units % 10 === 4)) {
                    // test for the specific grammatical rules in RUssian
                    $this->agi->stream_file('dollar2', '#');
                } elseif ($lang === 'ru' && $curr === 'usd' && $units % 10 === 1) {
                    // test for the specific grammatical rules in RUssian
                    $this->agi->stream_file($unit_audio, '#');
                } else {
                    $this->agi->stream_file($units_audio, '#');
                }
            } elseif (!$this->agiconfig['play_rate_cents_if_lower_one']) {
                $this->agi->say_number($units);
                $this->agi->stream_file($unit_audio, '#');
            }

            if ($units > 0 && $cents > 0) {
                $this->agi->stream_file('vm-and', '#');
            }
            if ($cents > 0 || ($point > 0 && $this->agiconfig['play_rate_cents_if_lower_one'])) {
                $this->agi->say_number($cents);
                if ($point > 0 && $this->agiconfig['play_rate_cents_if_lower_one']) {
                    $this->debug(self::INFO, "point");
                    $this->agi->stream_file('prepaid-point', '#');
                    $this->agi->say_number($point);
                }
                if ($cents > 1) {
                    if ($lang === 'ru' && $curr === 'usd' && ($cents % 10 === 2 || $cents % 10 === 3 || $cents % 10 === 4)) {
                        // test for the specific grammatical rules in RUssian
                        $this->agi->stream_file('prepaid-cent2', '#');
                    } elseif ($lang === 'ru' && $curr === 'usd' && $cents % 10 === 1) {
                        // test for the specific grammatical rules in RUssian
                        $this->agi->stream_file($cent_audio, '#');
                    } else {
                        $this->agi->stream_file($cents_audio, '#');
                    }
                } else {
                    $this->agi->stream_file($cent_audio, '#');
                }
            }
        }
        // say 'per minutes'
        $this->agi->stream_file('prepaid-per-minutes', '#');
    }

    /**
    * Function refill_card_with_voucher
    */
    public function refill_card_with_voucher(): bool
    {
        $this->debug(self::DEBUG, "[VOUCHER REFILL CARD LOG BEGIN]");
        if (isset($this->agiconfig['agi_force_currency']) && strlen($this->agiconfig['agi_force_currency']) == 3) {
            $this->currency = $this->agiconfig['agi_force_currency'];
        }
        $curr = strtoupper($this->currency);

        if (!is_numeric($this->currencies_list[$curr] ?? null)) {
            $mycur = 1;
        } else {
            $mycur = $this->currencies_list[$curr];
        }
        $timetowait = ($this->config['global']['len_voucher'] < 6) ? 8000 : 20000;
        $res_dtmf = $this->agi->get_data('prepaid-voucher_enter_number', $timetowait, $this->config['global']['len_voucher']);
        $this->debug(self::DEBUG, "VOUCHERNUMBER RES DTMF : " . $res_dtmf["result"]);
        $vouchernumber = $res_dtmf["result"];
        if ($vouchernumber <= 0) {
            return false;
        }

        $this->debug(self::DEBUG, "VOUCHER NUMBER : " . $vouchernumber);

        $query = "SELECT voucher, credit, currency FROM cc_voucher WHERE expirationdate >= CURRENT_TIMESTAMP AND activated = 't' AND voucher = ?";
        $params = [$vouchernumber];

        $row = $this->DBHandle->GetRow($query, $params);
        $this->debug(self::DEBUG, "Query: $query", $params, $row);
        if ($row !== false && $row !== []) {
            if (!isset($this->currencies_list[strtoupper($row["currency"])])) {
                $this->debug(self::ERROR, "System Error : No currency table complete !!!");

                return false;
            } else {
                // DISABLE THE VOUCHER
                $add_credit = $row["credit"] * $this->currencies_list[strtoupper($row["currency"])];
                $query = "UPDATE cc_voucher SET activated = 'f', usedcardnumber = ?, used = 1, usedate = now() WHERE voucher = ?";
                $params = [$this->accountcode, $vouchernumber];
                $this->DBHandle->Execute($query, $params);
                $this->debug(self::DEBUG, "Query: $query", $params);

                // UPDATE THE CARD AND THE CREDIT PROPERTY OF THE CLASS
                $query = "UPDATE cc_card SET credit = credit + ? WHERE username = ?";
                $params = [$add_credit, $this->accountcode];
                $this->DBHandle->Execute($query, $params);
                $this->debug(self::DEBUG, "Query: $query", $params);
                $this->credit += $add_credit;

                $this->debug(self::INFO, ' The Voucher ' . $vouchernumber . ' has been used, We added ' . $add_credit / $mycur . ' ' . strtoupper($this->currency) . ' of credit on your account!');
                $this->fct_say_balance($add_credit, true);
            }
        } else {
            $this->debug(self::DEBUG, "[VOUCHER REFILL ERROR: " . $vouchernumber . " Voucher not avaible or dosn't exist]");
            $this->agi->stream_file('voucher_does_not_exist');

            return false;
        }
        $this->debug(self::DEBUG, "[VOUCHER REFILL CARD LOG END]");

        return true;
    }


    /*
    * Function to generate a cardnumber
    */
    public function MDP(int $chrs = 10): string
    {
        $pwd = "";
        mt_srand((double) microtime() * 1000000);
        while (strlen($pwd) < $chrs) {
            $chr = chr(mt_rand(0, 255));
            if (preg_match("/^[0-9]$/i", $chr)) {
                $pwd = $pwd . $chr;
            }
        }
        return $pwd;
    }

    /**
     * Function to retrieve the number of used package Free call for a customer according to billingtype (Monthly ; Weekly) & Startday
     * @return int|int[]
    */

    public function free_calls_used(int $id_cc_card, int $id_cc_package_offer, int $billingtype, int $startday, string $ret = "both")
    {
        if ($billingtype === 0) {
            // PROCESSING FOR MONTHLY
            // if > last day of the month
            if ($startday > date("t")) {
                $startday = date("t");
            }
            if ($startday <= 0) {
                $startday = 1;
            }

            // Check if the startday is upper that the current day
            if ($startday > date("j")) {
                $year_month = date('Y-m', strtotime('-1 month'));
            } else {
                $year_month = date('Y-m');
            }

            $yearmonth = sprintf("%s-%02d", $year_month, $startday);
            $CLAUSE_DATE = " date_consumption >= ?";
            $clause_param = $yearmonth;
        } else {
            // PROCESSING FOR WEEKLY
            $startday = $startday % 7;
            $dayofweek = date("w"); // Numeric representation of the day of the week 0 (for Sunday) through 6 (for Saturday)
            if ($dayofweek == 0) {
                $dayofweek = 7;
            }
            if ($dayofweek < $startday) {
                $dayofweek = $dayofweek + 7;
            }
            $diffday = $dayofweek - $startday;
            $CLAUSE_DATE = "date_consumption >= NOW() - INTERVAL ? DAY ";
            $clause_param = $diffday;
        }
        $query = "SELECT COUNT(*), SUM(used_secondes) FROM cc_card_package_offer " .
                "WHERE $CLAUSE_DATE AND id_cc_card = ? AND id_cc_package_offer = ?";
        $params = [$clause_param, $id_cc_card, $id_cc_package_offer];
        $result = $this->DBHandle->GetRow($query, $params);
        $this->debug(self::DEBUG, "Query: $query", $params, $result);
        if ($result !== false && $result !== []) {
            $number_calls_used = intval($result[0]);
            $freetimetocall_used = intval($result[1]);
        } else {
            $number_calls_used = 0;
            $freetimetocall_used = 0;
        }

        if ($ret === "time") {
            return $freetimetocall_used;
        } elseif ($ret === "count") {
            return $number_calls_used;
        }
        return [$number_calls_used, $freetimetocall_used];

    }

    /*
    * Function apply_rules to the phonenumber : Remove internation prefix
    */
    public function apply_rules(string $phonenumber)
    {
        if (is_array($this->agiconfig['international_prefixes']) && (count($this->agiconfig['international_prefixes']) > 0)) {
            foreach ($this->agiconfig['international_prefixes'] as $testprefix) {
                if (str_starts_with($phonenumber, $testprefix)) {
                    return substr($phonenumber, strlen($testprefix));
                }
            }
        }
        return $phonenumber;
    }

    /*
    * Function apply_add_countryprefixto the phonenumber
    */
    public function apply_add_countryprefixto(string $phonenumber): string
    {
        if ($this->agiconfig['local_dialing_addcountryprefix']) {
            return preg_replace("/^0([^0])/", $this->countryprefix . "$1", $phonenumber);
        }

        return $phonenumber;
    }


    /*
    * Function callingcard_cid_sanitize : Ensure the caller is allowed to use their claimed CID.
    * Returns: clean CID value, possibly empty.
    */
    public function callingcard_cid_sanitize(): string
    {
        $this->debug(self::DEBUG, "[CID_SANITIZE - CID:" . $this->CallerID . "]");

        if (strlen($this->CallerID) == 0) {
            $this->debug(self::DEBUG, "[CID_SANITIZE - CID: NO CID]");
            return '';
        }
        $result1 = $result2 = [];

        $san = strtoupper($this->agiconfig['cid_sanitize']);

        if ($san === 'CID' || $san === 'BOTH') {
            $query = "SELECT cc_callerid.cid " .
                " FROM cc_callerid " .
                " JOIN cc_card ON cc_callerid.id_cc_card = cc_card.id " .
                " WHERE (cc_callerid.activated = 1 OR cc_callerid.activated = 't') AND cc_card.username = ?" .
                "ORDER BY 1";
            $params = [$this->username];
            $result1 = $this->DBHandle->GetCol($query, $params);
            $this->debug(self::DEBUG, "Query: $query", $params, $result1);
        }

        if ($san === "DID" || $san === "BOTH") {
            $query = "SELECT cc_did.did " .
                " FROM cc_did " .
                " JOIN cc_did_destination ON cc_did_destination.id_cc_did = cc_did.id " .
                " JOIN cc_card ON cc_did_destination.id_cc_card = cc_card.id " .
                " WHERE (cc_did.activated = 1 OR cc_did.activated = 't') AND " .
                " cc_did_destination.activated = 1 AND cc_did.startingdate <= NOW() " .
                " AND cc_did.expirationdate >= NOW()" .
                " AND cc_card.username = ?" .
                " AND cc_did_destination.validated = 1" .
                " ORDER BY 1";
            $params = [$this->username];
            $result2 = $this->DBHandle->GetCol($query, $params);
            $this->debug(self::DEBUG, "Query: $query", $params, $result2);
        }

        if (count($result1) === 0 && count($result2) === 0) {
            $this->debug(self::DEBUG, "[CID_SANITIZE - CID: NO DATA]");
            return '';
        }
        $result = array_merge($result1, $result2);
        $this->debug(self::DEBUG, "RESULT MERGE->" . json_encode($result));

        foreach ($result as $res) {
            $comp = substr($res, strlen($this->CallerID) * -1);
            $this->debug(self::DEBUG, "[CID_SANITIZE - CID COMPARING: $comp to $this->CallerID]");
            if ($comp === $this->CallerID) {
                $this->debug(self::DEBUG, "[CID_SANITIZE - CID: $res]");
                return $res;
            }
        }
        $this->debug(self::DEBUG, "[CID_SANITIZE - CID UNIQUE RESULT: $result[0]]");

        return $result[0];
    }


    public function callingcard_auto_setcallerid()
    {
        // AUTO SetCallerID
        $this->debug(self::DEBUG, "[AUTO SetCallerID]");
        if ($this->agiconfig['auto_setcallerid']) {

            if (strlen($this->agiconfig['force_callerid'])) {
                $this->agi->set_callerid($this->agiconfig['force_callerid']);
                $this->debug(self::DEBUG, "[EXEC SetCallerID : " . $this->agiconfig['force_callerid'] . "]");

            } elseif (strlen($this->CallerID)) {
                if ($this->CallerID === $this->accountcode) {
                    $this->debug(self::DEBUG, "[Overwrite callerID security : " . $this->CallerID . "]");

                    if ($this->agi->request['agi_calleridname'] === $this->accountcode) {
                        $this->CallerID = '0';
                    } else {
                        $this->CallerID = $this->agi->request['agi_calleridname'];
                    }
                } else {
                    $this->debug(self::DEBUG, "[REQUESTED SetCallerID : " . $this->CallerID . "]");
                }

                // IF REQUIRED, VERIFY THAT THE CALLERID IS LEGAL
                $cid_sanitized = $this->CallerID;
                $san = strtoupper($this->agiconfig['cid_sanitize']);
                if ($san === "DID" || $san === "CID" || $san === "BOTH") {
                    $cid_sanitized = $this->callingcard_cid_sanitize();
                    $this->debug(self::INFO, "[TRY : callingcard_cid_sanitize]");
                    if ($this->agiconfig['debug'] >= 1) {
                        $this->agi->verbose('CALLERID SANITIZED: "' . $cid_sanitized . '"');
                    }
                }

                if (strlen($cid_sanitized) > 0) {
                    $this->agi->set_callerid($cid_sanitized);
                    $this->debug(self::DEBUG, "[EXEC SetCallerID : " . $cid_sanitized . "]");
                } else {
                    $this->debug(self::DEBUG, "[CANNOT SetCallerID : cid_san is empty]");
                }
            }
        }

        // Let the Caller set his CallerID
        if ($this->agiconfig['callerid_update']) {
            $this->debug(self::DEBUG, "[UPDATE CallerID]");

            $res_dtmf = $this->agi->get_data('prepaid-enter-cid', 6000, 20);
            $this->debug(self::DEBUG, "RES DTMF : " . $res_dtmf["result"]);

            if (strlen($res_dtmf["result"]) > 0 && is_numeric($res_dtmf["result"])) {
                $this->agi->set_callerid($res_dtmf["result"]);
            }
        }
    }

    public function update_callback_campaign()
    {
        $now = time();
        $username = $this->agi->get_variable("USERNAME", true);
        $userid= $this->agi->get_variable("USERID", true);
        $called= $this->agi->get_variable("CALLED", true);
        $phonenumber_id= $this->agi->get_variable("PHONENUMBER_ID", true);
        $campaign_id= $this->agi->get_variable("CAMPAIGN_ID", true);
        $this->debug(self::DEBUG, "[MODE CAMPAIGN CALLBACK: USERNAME=$username USERID=$userid ]");

        $query = "SELECT cc_campaign_config.flatrate, cc_campaign_config.context FROM cc_card, cc_card_group, cc_campaignconf_cardgroup, cc_campaign_config, cc_campaign WHERE cc_card.id = ? AND cc_card.id_group = cc_card_group.id AND cc_campaignconf_cardgroup.id_card_group = cc_card_group.id AND cc_campaignconf_cardgroup.id_campaign_config = cc_campaign_config.id AND cc_campaign.id = ? AND cc_campaign.id_campaign_config = cc_campaign_config.id";
        $params = [$userid, $campaign_id];
        $row = $this->DBHandle->GetRow($query, $params);
        $this->debug(self::DEBUG, "Query: $query", $params, $row);

        $cost = 0;
        if ($row !== false && !is_null($row)) {
            $cost = $row[0];
            $context = $row[1];
        }

        if (empty($context)) {
            $context = $this->config["callback"]['context_campaign_callback'];
        }

        //update balance
        $query = "UPDATE cc_card SET credit = credit + ?, lastuse = now() WHERE username = ?";
        $params = [a2b_round($cost), $username];
        $this->DBHandle->Execute($query, $params);
        $this->debug(self::DEBUG, "Query: $query", $params);

        //dial other context
        $this->agi->set_variable('CALLERID(name)', $phonenumber_id . ',' . $campaign_id);
        $this->debug(self::DEBUG, "[CONTEXT TO CALL : " . $context . "]");
        $this->agi->exec_dial("local", "1@" . $context);

        $duration = time() - $now;
        ///create campaign cdr
        $query = "INSERT INTO cc_call (uniqueid, sessionid, card_id, calledstation, sipiax, sessionbill, sessiontime, stoptime, starttime $this->CDR_CUSTOM_SQL)" .
            " VALUES (?, ?, ?, ?, 6, ?, ?, CURRENT_TIMESTAMP , NOW() - INTERVAL ? SECOND $this->CDR_CUSTOM_VAL)";
        $params = [
            $this->uniqueid,
            $this->channel,
            $userid,
            $called,
            $cost,
            $duration,
            $duration,
        ];
        $this->DBHandle->Execute($query, $params);
        $this->debug(self::DEBUG, "Query: $query", $params);

    }

    public function callingcard_ivr_authenticate(): bool
    {
        $authentication = false;
        $prompt = '';
        $res = 0;
        $language = 'en';
        $isused = 0;
        $simultaccess = 0;
        $callerID_enable = $this->agiconfig['cid_enable'];

        // -%-%-%-%-%-%- FIRST TRY WITH THE CALLERID AUTHENTICATION -%-%-%-%-%-%-
        if ($callerID_enable && is_numeric($this->CallerID) && $this->CallerID > 0) {

            $this->debug(self::DEBUG, "[CID_ENABLE - CID_CONTROL - CID:" . $this->CallerID . "]");

            // NOT USE A LEFT JOIN HERE - In case the callerID is alone without card bound
            $query = "SELECT cc_callerid.cid, cc_callerid.id_cc_card, cc_callerid.activated, cc_card.credit, " .
                    " cc_card.tariff, cc_card.activated, cc_card.inuse, cc_card.simultaccess, cc_card.typepaid, cc_card.creditlimit, " .
                    " cc_card.language, cc_card.username, removeinterprefix, cc_card.redial, enableexpire, UNIX_TIMESTAMP(expirationdate), " .
                    " expiredays, nbused, UNIX_TIMESTAMP(firstusedate), UNIX_TIMESTAMP(cc_card.creationdate), cc_card.currency, " .
                    " cc_card.lastname, cc_card.firstname, cc_card.email, cc_card.uipass, cc_card.id_campaign, cc_card.id, useralias, " .
                    " cc_card.status, cc_card.voicemail_permitted, cc_card.voicemail_activated, cc_card.restriction, cc_country.countryprefix" .
                    " FROM cc_callerid " .
                    " LEFT JOIN cc_card ON cc_callerid.id_cc_card = cc_card.id " .
                    " LEFT JOIN cc_tariffgroup ON cc_card.tariff = cc_tariffgroup.id " .
                    " LEFT JOIN cc_country ON cc_card.country = cc_country.countrycode " .
                    " WHERE cc_callerid.cid = ?";
            $params = [$this->CallerID];
            $row = $this->DBHandle->GetRow($query, $params);
            $this->debug(self::DEBUG, "Query: $query", $params, $row);

            if ($row === false || $row === []) {

                $this->debug(self::DEBUG, "[CID_CONTROL - NO CALLERID]");

                if ($this->agiconfig['cid_auto_create_card']) {
                    $this->debug(self::DEBUG, "[CID_CONTROL - NO CALLERID - ASK PIN CODE]");
                    for ($k = 0; $k <= 20; $k++) {
                        if ($k === 20) {
                            $this->debug(self::WARN, "ERROR : Impossible to generate a cardnumber not yet used!");
                            $prompt = "prepaid-auth-fail";
                            $this->debug(self::DEBUG, "[StreamFile : $prompt]");
                            $this->agi->stream_file($prompt, '#');
                            return false;
                        }
                        $card_gen = $this->MDP($this->agiconfig['cid_auto_create_card_len']);
                        $card_alias = $this->MDP($this->agiconfig['cid_auto_create_card_len']);
                        $query = "SELECT username, useralias FROM cc_card WHERE username = ? OR useralias = ?";
                        $params = [$card_gen, $card_alias];
                        $resmax = $this->DBHandle->GetRow($query, $params);
                        $this->debug(self::DEBUG, "Query: $query", $params, $resmax);
                        if ($resmax === false || $resmax === []) {
                            $this->debug(self::INFO, "[CN:$card_gen|CA:$card_alias] No card found, using these values");
                            break;
                        }
                    }
                    $uipass = $this->MDP().$this->MDP(5);
                    $typepaid = ($this->agiconfig['cid_auto_create_card_typepaid'] === "POSTPAID") ? 1 : 0;

                    //CREATE A CARD
                    $QUERY_FIELDS = 'username, useralias, uipass, credit, language, tariff, activated, typepaid, creditlimit, inuse, status, currency';
                    $QUERY_VALUES = "?, ?, ?, ?, 'en', ?, 't', ?, ?, 0, 1, ?";
                    $params = [$card_gen, $card_alias, $uipass, $this->agiconfig['cid_auto_create_card_credit'], $this->agiconfig['cid_auto_create_card_tariffgroup'], $typepaid, $this->agiconfig['cid_auto_create_card_credit_limit'], $this->config['global']['base_currency']];

                    if ($this->group_mode) {
                        $QUERY_FIELDS .= ", id_group";
                        $QUERY_VALUES .= " , ?";
                        $params[] = $this->group_id;
                    }

                    $query = "INSERT INTO cc_card $QUERY_FIELDS VALUES($QUERY_VALUES)";
                    $this->DBHandle->Execute($query, $params);
                    $this->debug(self::DEBUG, "Query: $query", $params);
                    $result = $this->DBHandle->Insert_ID();
                    $this->debug(self::INFO, "[CARDNUMBER:$card_gen]:[CREATED:$result]:[QUERY_VALUES:$QUERY_VALUES]");

                    //CREATE A CARD AND AN INSTANCE IN CC_CALLERID
                    $query = "INSERT INTO cc_callerid cid, id_cc_card VALUES(?, ?)";
                    $params = [$this->CallerID, $result];
                    $this->DBHandle->Execute($query, $params);
                    $this->debug(self::DEBUG, "Query: $query", $params);
                    $result = $this->DBHandle->Insert_ID();
                    if (!$result) {
                        $this->debug(self::ERROR, "[CALLERID CREATION ERROR TABLE cc_callerid]");
                        $this->debug(self::DEBUG, "prepaid-auth-fail");
                        $this->agi->stream_file($prompt, '#');
                        return false;
                    }

                    $this->credit = (int)$this->agiconfig['cid_auto_create_card_credit'];
                    $this->tariff = (int)$this->agiconfig['cid_auto_create_card_tariffgroup'];
                    $this->active = 1;
                    $this->status = 1;
                    $this->typepaid = $typepaid;
                    $this->creditlimit = (int)$this->agiconfig['cid_auto_create_card_credit_limit'];
                    $this->accountcode = $card_gen;

                    if ($this->typepaid == 1) {
                        $this->credit = $this->credit + $this->creditlimit;
                    }

                } elseif ($this->agiconfig['cid_askpincode_ifnot_callerid'] == 1) {
                    $this->debug(self::DEBUG, "[CID_CONTROL - NO CALLERID - ASK PIN CODE]");
                    $this->accountcode = '';
                    $callerID_enable = 0;
                } else {
                    $prompt = "prepaid-auth-fail";
                }

            } else {
                // authenticate OK using the callerID
                $cid_active                 = $row[2];
                $this->credit               = (int)$row[3];
                $this->tariff               = (int)$row[4];
                $this->active               = $row[5];
                $isused                     = $row[6];
                $simultaccess               = (int)$row[7];
                $this->typepaid             = (int)$row[8];
                $this->creditlimit          = (int)$row[9];
                $language                   = $row[10];
                $this->accountcode          = $row[11];
                $this->username             = $row[11];
                $this->removeinterprefix    = (bool)$row[12];
                $this->redial               = $row[13];
                $this->enableexpire         = (int)$row[14];
                $this->expirationdate       = (int)$row[15];
                $this->expiredays           = (int)$row[16];
                $this->nbused               = (int)$row[17];
                $this->firstusedate         = (int)$row[18];
                $this->creationdate         = (int)$row[19];
                $this->currency             = $row[20];
                $this->cardholder_lastname  = $row[21];
                $this->cardholder_firstname = $row[22];
                $this->cardholder_email     = $row[23];
                $this->cardholder_uipass    = $row[24];
                $this->id_campaign          = (int)$row[25];
                $this->id_card              = (int)$row[26];
                $this->useralias            = $row[27];
                $this->status               = (int)$row[28];
                $this->voicemail            = $row[29] && $row[30];
                $this->restriction          = (int)$row[31];
                $this->countryprefix        = $row[32];

                if (strlen($language) === 2 && !($this->languageselected >= 1)) {
                    $this->agi->set_variable('CHANNEL(language)', $language);
                    $this->debug(self::DEBUG, "[SET CHANNEL(language) $language]");
                    $this->current_language = $language;
                }

                if ($this->typepaid == 1) {
                    $this->credit = $this->credit + $this->creditlimit;
                }
                // CHECK IF CALLERID ACTIVATED
                if ($cid_active !== "t" && $cid_active !== "1") {
                    $prompt = "prepaid-auth-fail";
                }

                // CHECK credit < min_credit_2call / you have zero balance
                if (!$this->enough_credit_to_call()) {
                    $prompt = "prepaid-no-enough-credit-stop";
                }

                // CHECK activated=t / CARD NOT ACTIVE, CONTACT CUSTOMER SUPPORT
                if ($this->status != "1") {
                    $prompt = "prepaid-auth-fail"; // not expired but inactive.. probably not yet sold.. find better prompt
                }

                // CHECK IF THE CARD IS USED
                if ($isused > 0 && $simultaccess !== 1) {
                    $prompt = "prepaid-card-in-use";
                }

                // CHECK FOR EXPIRATION  -  enableexpire ( 0 : none, 1 : expire date, 2 : expire days since first use, 3 : expire days since creation)
                if ($this->enableexpire > 0) {
                    if ($this->enableexpire === 1 && $this->expirationdate !== 0) {
                        // expire date
                        $date_will_expire = $this->expirationdate;
                    } elseif ($this->enableexpire === 2 && $this->firstusedate !== 0 && $this->expiredays > 0) {
                        // expire days since first use
                        $date_will_expire = $this->firstusedate + (60 * 60 * 24 * $this->expiredays);
                    } elseif ($this->enableexpire === 3 && $this->creationdate !== 0 && $this->expiredays > 0) {
                        // expire days since creation
                        $date_will_expire = $this->creationdate + (60 * 60 * 24 * $this->expiredays);
                    } else {
                        return false;
                    }
                    if ($date_will_expire < time()) {
                        $prompt = "prepaid-card-expired";
                        $this->status = 5;
                        $query = "UPDATE cc_card SET status = '5' WHERE id = ?";
                        $params = [$this->id_card];
                        $this->DBHandle->Execute($query, $params);
                        $this->debug(self::DEBUG, "Query: $query", $params);
                    }
                }

                if (!empty($prompt)) {
                    $this->agi->stream_file($prompt, '#');
                    $this->debug(self::DEBUG, "[ERROR CHECK CARD : $prompt (cardnumber:" . $this->cardnumber . ")]");

                    if ($this->agiconfig['jump_voucher_if_min_credit'] && !$this->enough_credit_to_call()) {

                        $this->debug(self::DEBUG, "[NOTENOUGHCREDIT - refill_card_withvoucher] ");
                        if ($this->refill_card_with_voucher()) {
                            return true;
                        } else {
                            $this->debug(self::DEBUG, "[NOTENOUGHCREDIT - refill_card_withvoucher fail] ");
                        }
                    }
                    if ($prompt === "prepaid-no-enough-credit-stop" && $this->agiconfig['notenoughcredit_cardnumber']) {
                        $this->accountcode = '';
                        $callerID_enable = 0;
                        $this->agiconfig['cid_auto_assign_card_to_cid'] = 0;

                        if ($this->agiconfig['notenoughcredit_assign_newcardnumber_cid']) {
                            $this->ask_other_cardnumber = true;
                            $this->update_callerid = true;
                        }
                    } elseif ($prompt == "prepaid-card-expired") {
                        $this->accountcode = ''; $callerID_enable = 0;
                        $this->ask_other_cardnumber = true;
                        $this->update_callerid = true;
                    } else {
                        return false;
                    }
                } else {
                    $authentication = true;
                }

            } // elseif We->found a card for this callerID

        } else {
            // NO CALLERID AUTHENTICATION
            $callerID_enable = 0;
        }

        // -%-%-%-%-%-%- CHECK IF WE CAN AUTHENTICATE THROUGH THE "ACCOUNTCODE" -%-%-%-%-%-%-

        $prompt_entercardnum= "prepaid-enter-pin-number";
        $this->debug(self::DEBUG, ' - Account code ::> ' . $this->accountcode);
        if (strlen($this->accountcode) >= 1 && !$authentication) {
            $this->username = $this->cardnumber = $this->accountcode;
            for ($i = 0; $i <= 0; $i++) {
                if ($callerID_enable != 1 || !is_numeric($this->CallerID) || $this->CallerID <= 0) {
                    $query = "SELECT credit, tariff, activated, inuse, simultaccess, typepaid, creditlimit, " .
                        " language, removeinterprefix, redial, enableexpire, " .
                        " UNIX_TIMESTAMP(expirationdate), expiredays, nbused, UNIX_TIMESTAMP(firstusedate), " .
                        " UNIX_TIMESTAMP(cc_card.creationdate), cc_card.currency, cc_card.lastname, cc_card.firstname, cc_card.email, " .
                        " cc_card.uipass, cc_card.id_campaign, cc_card.id, useralias, status, voicemail_permitted, voicemail_activated, " .
                        " cc_card.restriction, cc_country.countryprefix " .
                        " FROM cc_card " .
                        " LEFT JOIN cc_tariffgroup ON tariff = cc_tariffgroup.id " .
                        " LEFT JOIN cc_country ON cc_card.country = cc_country.countrycode " .
                        " WHERE username = ?";
                    $params = [$this->cardnumber];
                    $row = $this->DBHandle->GetRow($query, $params);
                    $this->debug(self::DEBUG, "Query: $query", $params, $row);

                    if ($row === false || $row === []) {
                        $prompt = "prepaid-auth-fail";
                        $this->debug(self::DEBUG, strtoupper($prompt));
                        $res = -2;
                        break;
                    } elseif ($this->agiconfig['callerid_authentication_over_cardnumber'] == 1) {
                        // -%-%-%- WE ARE GOING TO CHECK IF THE CALLERID IS CORRECT FOR THIS CARD -%-%-%-
                        if (!is_numeric($this->CallerID) && $this->CallerID <= 0) {
                            $res = -2;
                            break;
                        }
                        $query = " SELECT cid, id_cc_card, activated FROM cc_callerid WHERE cc_callerid.cid = ? AND cc_callerid.id_cc_card = ?";
                        $params = [$this->CallerID, $row[22]];
                        $result_check_cid = $this->DBHandle->GetRow($query, $params);
                        $this->debug(self::DEBUG, "Query: $query", $params, $result_check_cid);

                        if ($result_check_cid === false || $result_check_cid === []) {
                            $prompt = "prepaid-auth-fail";
                            $this->debug(self::DEBUG, strtoupper($prompt));
                            $res = -2;
                            break;
                        }
                    }

                    $this->credit               = (int)$row[0];
                    $this->tariff               = (int)$row[1];
                    $this->active               = $row[2];
                    $isused                     = $row[3];
                    $simultaccess               = (int)$row[4];
                    $this->typepaid             = (int)$row[5];
                    $this->creditlimit          = (int)$row[6];
                    $language                   = $row[7];
                    $this->removeinterprefix    = (bool)$row[8];
                    $this->redial               = $row[9];
                    $this->enableexpire         = (int)$row[10];
                    $this->expirationdate       = (int)$row[11];
                    $this->expiredays           = (int)$row[12];
                    $this->nbused               = (int)$row[13];
                    $this->firstusedate         = (int)$row[14];
                    $this->creationdate         = (int)$row[15];
                    $this->currency             = $row[16];
                    $this->cardholder_lastname  = $row[17];
                    $this->cardholder_firstname = $row[18];
                    $this->cardholder_email     = $row[19];
                    $this->cardholder_uipass    = $row[20];
                    $this->id_campaign          = (int)$row[21];
                    $this->id_card              = (int)$row[22];
                    $this->useralias            = $row[23];
                    $this->status               = (int)$row[24];
                    $this->voicemail            = $row[25] && $row[26];
                    $this->restriction          = (int)$row[27];
                    $this->countryprefix        = $row[28];

                    if ($this->typepaid == 1) {
                        $this->credit = $this->credit + $this->creditlimit;
                    }
                }

                if (strlen($language) === 2 && !($this->languageselected >= 1)) {
                    $this->agi->set_variable("CHANNEL(language)", $language);
                    $this->debug(self::DEBUG, "[SET CHANNEL(language) $language]");
                    $this->current_language = $language;
                }

                $this->debug(self::DEBUG, "[credit=$this->credit :: tariff=$this->tariff :: status=$this->status :: isused=$isused :: simultaccess=$simultaccess :: typepaid=$this->typepaid :: creditlimit=$this->creditlimit :: language=$language]");

                $prompt = '';
                // CHECK credit > min_credit_2call / you have zero balance
                if (!$this->enough_credit_to_call()) {
                    $prompt = "prepaid-no-enough-credit-stop";
                }
                // CHECK activated=t / CARD NOT ACTIVE, CONTACT CUSTOMER SUPPORT
                if ($this->status != "1") {
                    $prompt = "prepaid-auth-fail";
                    // not expired but inactive.. probably not yet sold.. find better prompt
                }
                // CHECK IF THE CARD IS USED
                if (($isused > 0) && ($simultaccess !== 1)) {
                    $prompt = "prepaid-card-in-use";
                }
                // CHECK FOR EXPIRATION  -  enableexpire ( 0 : none, 1 : expire date, 2 : expire days since first use, 3 : expire days since creation)
                if ($this->enableexpire > 0) {
                    if ($this->enableexpire === 1 && $this->expirationdate !== 0) {
                        // expire date
                        $date_will_expire = $this->expirationdate;
                    } elseif ($this->enableexpire === 2 && $this->firstusedate !== 0 && $this->expiredays > 0) {
                        // expire days since first use
                        $date_will_expire = $this->firstusedate + (60 * 60 * 24 * $this->expiredays);
                    } elseif ($this->enableexpire === 3 && $this->creationdate !== 0 && $this->expiredays > 0) {
                        // expire days since creation
                        $date_will_expire = $this->creationdate + (60 * 60 * 24 * $this->expiredays);
                    } else {
                        return false;
                    }
                    if ($date_will_expire < time()) {
                        $prompt = "prepaid-card-expired";
                        $this->status = 5;
                        $query = "UPDATE cc_card SET status = '5' WHERE id = ?";
                        $params = [$this->id_card];
                        $this->DBHandle->Execute($query, $params);
                        $this->debug(self::DEBUG, "Query: $query", $params);
                    }
                }

                if (!empty($prompt)) {

                    $this->agi->stream_file($prompt, '#'); // Added because was missing the prompt
                    $this->debug(self::DEBUG, "[ERROR CHECK CARD : $prompt (cardnumber:$this->cardnumber)]");

                    if ($this->agiconfig['jump_voucher_if_min_credit'] == 1 && !$this->enough_credit_to_call()) {

                        $this->debug(self::DEBUG, "[NOTENOUGHCREDIT - refill_card_withvoucher] ");
                        if ($this->refill_card_with_voucher()) {
                            return true;
                        } else {
                            $this->debug(self::DEBUG, "[NOTENOUGHCREDIT - refill_card_withvoucher fail] ");
                        }
                    }

                    if ($prompt === "prepaid-no-enough-credit-stop" && $this->agiconfig['notenoughcredit_cardnumber']) {
                        $this->accountcode = '';
                        $callerID_enable = 0;
                        $this->agiconfig['cid_auto_assign_card_to_cid'] = 0;

                        if ($this->agiconfig['notenoughcredit_assign_newcardnumber_cid']) {
                            $this->ask_other_cardnumber = true;
                            $this->update_callerid = true;
                        }
                    } elseif ($prompt === "prepaid-card-expired") {
                        $this->accountcode = '';
                        $callerID_enable = 0;
                        $this->ask_other_cardnumber = true;
                        $this->update_callerid = true;
                    } else {
                        return false;
                    }
                } else {
                    $authentication = true;
                }
            } // For end
        }

        if ($callerID_enable == 0 && !$authentication) {

            // IF NOT PREVIOUS WE WILL ASK THE CARDNUMBER AND AUTHENTICATE ACCORDINGLY
            for ($retries = 0; $retries < 3; $retries++) {
                if ($retries > 0 && !empty($prompt)) {
                    $this->agi->stream_file($prompt, '#');
                    $this->debug(self::DEBUG, "Streamfile : $prompt");
                }
                if ($res < 0) {
                    $res = -1;
                    break;
                }
                $res = 0;
                $this->debug(self::DEBUG, "CARDNUMBER_LENGTH_MAX " . max($this->cardnumber_range));
                $res_dtmf = $this->agi->get_data($prompt_entercardnum, 6000, max($this->cardnumber_range));
                $this->debug(self::DEBUG, "RES DTMF : $res_dtmf[result]");
                $this->cardnumber = $res_dtmf["result"];

                $this->debug(self::DEBUG, "CARDNUMBER ::> $this->cardnumber");

                if (!isset($this->cardnumber) || strlen($this->cardnumber) == 0) {
                    $this->debug(self::DEBUG, "prepaid-no-card-entered");
                    continue;
                }

                if (strlen($this->cardnumber) > max($this->cardnumber_range) || strlen($this->cardnumber) < min($this->cardnumber_range)) {
                    $this->debug(self::DEBUG, "prepaid-invalid-digits");
                    continue;
                }
                $this->accountcode = $this->username = $this->cardnumber;

                $query = "SELECT credit, tariff, activated, inuse, simultaccess, typepaid, creditlimit, language, removeinterprefix, redial, " .
                    " enableexpire, UNIX_TIMESTAMP(expirationdate), expiredays, nbused, UNIX_TIMESTAMP(firstusedate), " .
                    " UNIX_TIMESTAMP(cc_card.creationdate), cc_card.currency, cc_card.lastname, cc_card.firstname, cc_card.email, " .
                    " cc_card.uipass, cc_card.id, cc_card.id_campaign, cc_card.id, useralias, status, voicemail_permitted, " .
                    " voicemail_activated, cc_card.restriction, cc_country.countryprefix " .
                    " FROM cc_card LEFT JOIN cc_tariffgroup ON tariff = cc_tariffgroup.id " .
                    " LEFT JOIN cc_country ON cc_card.country = cc_country.countrycode " .
                    " WHERE username = ?";
                $params = [$this->cardnumber];
                $row = $this->DBHandle->GetRow($query, $params);
                $this->debug(self::DEBUG, "Query: $query", $params, $row);

                if ($row === false || $row === []) {
                    $prompt = "prepaid-auth-fail";
                    $this->debug(self::DEBUG, strtoupper($prompt));
                    continue;
                } elseif ($this->agiconfig['callerid_authentication_over_cardnumber']) {
                    // WE ARE GOING TO CHECK IF THE CALLERID IS CORRECT FOR THIS CARD

                    if (!is_numeric($this->CallerID) && $this->CallerID <= 0) {
                        $this->debug(self::DEBUG, "prepaid-auth-fail");
                        continue;
                    }
                    $query = " SELECT cid, id_cc_card, activated FROM cc_callerid WHERE cc_callerid.cid = ? AND cc_callerid.id_cc_card = ?";
                    $params = [$this->CallerID, $row[23]];
                    $result_check_cid = $this->DBHandle->GetRow($query, $params);
                    $this->debug(self::DEBUG, "Query: $query", $params, $result_check_cid);

                    if ($result_check_cid === false || $result_check_cid === []) {
                        $prompt = "prepaid-auth-fail";
                        $this->debug(self::DEBUG, strtoupper($prompt));
                        continue;
                    }
                }

                $this->credit               = (int)$row[0];
                $this->tariff               = (int)$row[1];
                $this->active               = $row[2];
                $isused                     = $row[3];
                $simultaccess               = (int)$row[4];
                $this->typepaid             = (int)$row[5];
                $this->creditlimit          = (int)$row[6];
                $language                   = $row[7];
                $this->removeinterprefix    = (bool)$row[8];
                $this->redial               = $row[9];
                $this->enableexpire         = (int)$row[10];
                $this->expirationdate       = (int)$row[11];
                $this->expiredays           = (int)$row[12];
                $this->nbused               = (int)$row[13];
                $this->firstusedate         = (int)$row[14];
                $this->creationdate         = (int)$row[15];
                $this->currency             = $row[16];
                $this->cardholder_lastname  = $row[17];
                $this->cardholder_firstname = $row[18];
                $this->cardholder_email     = $row[19];
                $this->cardholder_uipass    = $row[20];
                $the_card_id                = $row[21];
                $this->id_campaign          = (int)$row[22];
                $this->id_card              = (int)$row[23];
                $this->useralias            = $row[24];
                $this->status               = (int)$row[25];
                $this->voicemail            = $row[26] && $row[27];
                $this->restriction          = (int)$row[28];
                $this->countryprefix        = $row[29];

                if ($this->typepaid == 1) {
                    $this->credit = $this->credit + $this->creditlimit;
                }

                if (strlen($language) === 2 && !($this->languageselected >= 1)) {
                    $this->agi->set_variable("CHANNEL(language)", $language);
                    $this->debug(self::DEBUG, "[SET CHANNEL(language) $language]");
                }

                $prompt = '';

                // CHECK credit > min_credit_2call / you have zero balance
                if (!$this->enough_credit_to_call()) {
                    $prompt = "prepaid-no-enough-credit-stop";
                }

                // CHECK activated=t / CARD NOT ACTIVE, CONTACT CUSTOMER SUPPORT
                if ($this->status != "1") {
                    $prompt = "prepaid-auth-fail"; // not expired but inactive.. probably not yet sold.. find better prompt
                }

                // CHECK IF THE CARD IS USED
                if ($isused && $simultaccess !== 1) {
                    $prompt = "prepaid-card-in-use";
                }

                // CHECK FOR EXPIRATION
                // enableexpire ( 0 : none, 1 : expire date, 2 : expire days since first use, 3 : expire days since creation)
                if ($this->enableexpire > 0) {

                    if ($this->enableexpire === 1 && $this->expirationdate !== 0) {
                        // expire date
                        $date_will_expire = $this->expirationdate;
                    } elseif ($this->enableexpire === 2 && $this->firstusedate !== 0 && $this->expiredays > 0) {
                        // expire days since first use
                        $date_will_expire = $this->firstusedate + (60 * 60 * 24 * $this->expiredays);
                    } elseif ($this->enableexpire === 3 && $this->creationdate !== 0 && $this->expiredays > 0) {
                        // expire days since creation
                        $date_will_expire = $this->creationdate + (60 * 60 * 24 * $this->expiredays);
                    } else {
                        return false;
                    }
                    if ($date_will_expire < time()) {
                        $prompt = "prepaid-card-expired";
                        $this->status = 5;
                        $query = "UPDATE cc_card SET status = '5' WHERE id = ?";
                        $params = [$this->id_card];
                        $this->DBHandle->Execute($query, $params);
                        $this->debug(self::DEBUG, "Query: $query", $params);
                    }
                }

                //CREATE AN INSTANCE IN CC_CALLERID
                if ($this->agiconfig['cid_enable'] && $this->agiconfig['cid_auto_assign_card_to_cid'] && is_numeric($this->CallerID) && $this->CallerID > 0 && !$this->ask_other_cardnumber && !$this->update_callerid) {

                    $query = "SELECT count(*) FROM cc_callerid WHERE id_cc_card = ?";
                    $params = [$the_card_id];
                    $count = $this->DBHandle->GetOne($query, $params);
                    $this->debug(self::DEBUG, "Query: $query", $params, $count);

                    // CHECK IF THE AMOUNT OF CALLERID IS LESS THAN THE LIMIT
                    if ($count !== false && $count < $this->config["webcustomerui"]['limit_callerid']) {
                        $query = "INSERT INTO cc_callerid cid, id_cc_card VALUES(?, ?)";
                        $params = [$this->CallerID, $the_card_id];
                        $result = $this->DBHandle->Execute($query, $params);
                        $this->debug(self::DEBUG, "Query: $query", $params);
                        if ($result === false) {
                            $this->debug(self::WARN, "[CALLERID CREATION ERROR TABLE cc_callerid]");
                            $prompt = "prepaid-auth-fail";
                            $this->debug(self::DEBUG, strtoupper($prompt));
                            $this->agi->stream_file($prompt, '#');

                            return false;
                        }
                    } else {
                        $this->debug(self::DEBUG, "[NOT ADDING NEW CID IN CC_CALLERID : CID LIMIT]");
                    }
                }

                //UPDATE THE CARD ASSIGN TO THIS CC_CALLERID
                if ($this->update_callerid && strlen($this->CallerID) > 1 && $this->ask_other_cardnumber) {
                    $this->ask_other_cardnumber = false;
                    $query = "UPDATE cc_callerid SET id_cc_card = ? WHERE cid = ?";
                    $params = [$the_card_id, $this->CallerID];
                    $this->DBHandle->Execute($query, $params);
                    $this->debug(self::DEBUG, "Query: $query", $params);
                }

                if ($prompt) {
                    $this->debug(self::DEBUG, "[ERROR CHECK CARD : $prompt (cardnumber:" . $this->cardnumber . ")]");
                    $res = -2;
                    break;
                }
                break;
            }//end for

        } elseif (!$authentication) {
            $res = -2;
        }

        if (($retries ?? 0) < 3 && $res == 0) {
            $this->callingcard_acct_start_inuse(true);
            if ($this->agiconfig['say_balance_after_auth'] == 1) {
                $this->debug(self::DEBUG, "[A2Billing] SAY BALANCE : $this->credit \n");
                $this->early_destination = $this->fct_say_balance($this->credit);
            }
        } elseif ($res == -2) {
            $this->agi->stream_file($prompt, '#');
        } else {
            $res = -1;
        }

        return $res >= 0;
    }

    public function callingcard_ivr_authenticate_light(?string &$error_msg, int $simbalance = 0): bool
    {
        $query = "SELECT credit, tariff, activated, inuse, simultaccess, typepaid, creditlimit, language, removeinterprefix, redial, enableexpire, " .
                    " UNIX_TIMESTAMP(expirationdate), expiredays, nbused, UNIX_TIMESTAMP(firstusedate), UNIX_TIMESTAMP(cc_card.creationdate), " .
                    " cc_card.currency, cc_card.lastname, cc_card.firstname, cc_card.email, cc_card.uipass, cc_card.id_campaign, status, " .
                    " voicemail_permitted, voicemail_activated, cc_card.restriction, cc_country.countryprefix " .
                    " FROM cc_card LEFT JOIN cc_tariffgroup ON tariff = cc_tariffgroup.id " .
                    " LEFT JOIN cc_country ON cc_card.country = cc_country.countrycode " .
                    " WHERE username = ?";
        $params = [$this->cardnumber];
        $row = $this->DBHandle->GetRow($query, $params);
        $this->debug(self::DEBUG, "Query: $query", $params, $row);

        if ($row === false || $row === []) {
            $error_msg = gettext("Error : Authentication Failed !!!");

            return false;
        }
        //If we receive a positive value from the rate simulator, we simulate with that initial balance. If we receive <=0 we use the value retrieved from the account
        if ($simbalance > 0) {
            $this->credit = $simbalance;
        } else {
            $this->credit = $row["credit"];
        }
        $this->tariff               = (int)$row[1];
        $this->active               = $row[2];
        $isused                     = $row[3];
        $simultaccess               = $row[4];
        $this->typepaid             = (int)$row[5];
        $this->creditlimit          = (int)$row[6];
        $this->removeinterprefix    = (bool)$row[8];
        $this->redial               = $row[9];
        $this->enableexpire         = (int)$row[10];
        $this->expirationdate       = (int)$row[11];
        $this->expiredays           = (int)$row[12];
        $this->nbused               = (int)$row[13];
        $this->firstusedate         = (int)$row[14];
        $this->creationdate         = (int)$row[15];
        $this->currency             = $row[16];
        $this->cardholder_lastname  = $row[17];
        $this->cardholder_firstname = $row[18];
        $this->cardholder_email     = $row[19];
        $this->cardholder_uipass    = $row[20];
        $this->id_campaign          = (int)$row[21];
        $this->status               = (int)$row[22];
        $this->voicemail            = $row[23] && $row[24];
        $this->restriction          = (int)$row[25];
        $this->countryprefix        = $row[26];

        $error_msg = "";
        if ($this->typepaid == 1) {
            $this->credit = $this->credit + $this->creditlimit;
        }

        // CHECK IF ENOUGH CREDIT TO CALL
        if (!$this->enough_credit_to_call()) {
            $error_msg = gettext("Error : Not enough credit to call !!!");
        }

        // CHECK activated=t / CARD NOT ACTIVE, CONTACT CUSTOMER SUPPORT
        if ($this->status != "1") {
            $error_msg = gettext("Error : Card is not active!!!");
        }

        // CHECK IF THE CARD IS USED
        if ($isused && $simultaccess !== 1) {
            $error_msg = gettext("Error : Card is actually in use!!!");
        }

        // CHECK FOR EXPIRATION  -  enableexpire ( 0 : none, 1 : expire date, 2 : expire days since first use, 3 : expire days since creation)
        if ($this->enableexpire > 0) {
            if ($this->enableexpire === 1 && $this->expirationdate > 0) {
                // expire date
                $date_will_expire = $this->expirationdate;
            } elseif ($this->enableexpire === 2 && $this->firstusedate !== 0 && $this->expiredays > 0) {
                // expire days since first use
                $date_will_expire = $this->firstusedate + (60 * 60 * 24 * $this->expiredays);
            } elseif ($this->enableexpire === 3 && $this->creationdate !== 0 && $this->expiredays > 0) {
                // expire days since creation
                $date_will_expire = $this->creationdate + (60 * 60 * 24 * $this->expiredays);
            } else {
                $date_will_expire = 0;
            }
            if ($date_will_expire < time()) {
                $error_msg = gettext("Error : Card have expired!!!");
            }
        }

        return empty($error_msg);
    }


    /*
    * Function deck_switch
    * to switch the Callplan from a customer : callplan_deck_minute_threshold
    *
    */
    public function deck_switch(): bool
    {
        $thresh = $this->agiconfig['callplan_deck_minute_threshold'] ?? "";
        if (!str_contains($thresh, ',')) {
            return false;
        }

        $arr_splitable_deck = explode(",", $thresh);
        $arr_value_deck_callplan = [];
        $arr_value_deck_minute = [];

        foreach ($arr_splitable_deck as $arr_value) {
            $arr_value = trim($arr_value);
            $arr_value_explode = explode(":", $arr_value, 2);
            if (count($arr_value_explode) > 1) {
                if (is_numeric($arr_value_explode[0]) && is_numeric($arr_value_explode[1])) {
                    $arr_value_deck_callplan[] = (int)$arr_value_explode[0];
                    $arr_value_deck_minute[] = (int)$arr_value_explode[1];
                }
            } elseif (is_numeric($arr_value)) {
                $arr_value_deck_callplan[] = (int)$arr_value;
                $arr_value_deck_minute[] = 0;
            }
        }
        // We have $arr_value_deck_callplan with 1, 2, 3 & we have $arr_value_deck_minute with 5, 1, 0
        if (count($arr_value_deck_callplan) === 0) {
            return false;
        }

        $query = "SELECT sum(sessiontime), count(*) FROM cc_call WHERE card_id = ?";
        $params = [$this->id_card];
        $row = $this->DBHandle->GetRow($query, $params);
        $this->debug(self::DEBUG, "Query: $query", $params, $row);
        if ($row === false || $row === []) {
            return false;
        }
        $sessiontime_for_card = $row[0];
        $calls_for_card = $row[1];

        $find_deck = false;
        $accumul_seconds = 0;
        for ($ind_deck = 0; $ind_deck < count($arr_value_deck_callplan); $ind_deck++) {
            $accumul_seconds += $arr_value_deck_minute[$ind_deck] ?? 0;

            if ($arr_value_deck_callplan[$ind_deck] === $this->tariff) {
                $find_deck = is_numeric($arr_value_deck_callplan[$ind_deck + 1] ?? null);
                break;
            }
        }

        $ind_deck = $ind_deck + 1;
        if ($find_deck) {
            // Check if the sum sessiontime call is more the the accumulation of the parameters seconds & that the amount of calls made is upper than the deck level
            if (($sessiontime_for_card > $accumul_seconds) && ($calls_for_card > $ind_deck)) {
                // UPDATE CARD
                $this->debug(self::DEBUG, "[DECK SWITCH] : UPDATE CARD TO CALLPLAN ID = " . $arr_value_deck_callplan[$ind_deck]);
                $query = "UPDATE cc_card SET tariff = ? WHERE id = ?";
                $params = [$arr_value_deck_callplan[$ind_deck], $this->id_card];
                $this->DBHandle->Execute($query, $params);
                $this->debug(self::DEBUG, "Query: $query", $params);
                $this->tariff = $arr_value_deck_callplan[$ind_deck];
            }
        }

        return true;
    }


    /*
    * Function DbConnect
    * Returns: true / false if connection has been established
    */
    public function DbConnect(): bool
    {
        $scheme = $this->config['database']['dbtype'] === "postgres" ? "pgsql" : "mysqli";
        $datasource = sprintf(
            "%s://%s:%s@%s/%s",
            $scheme,
            $this->config["database"]["user"],
            $this->config["database"]["password"],
            $this->config["database"]["hostname"],
            $this->config["database"]["dbname"]
        );
        $this->DBHandle = NewADOConnection($datasource);
        if (!$this->DBHandle) {
            return false;
        }
        if ($this->config['database']['dbtype'] === "mysql") {
            $this->DBHandle->Execute('SET AUTOCOMMIT = 1');
        }
        if (empty($this->table)) {
            $this->table = new Table();
        }
        return true;
    }

    /*
    * Function DbReConnect
    * Returns: true / false if connection has been established
    */
    public function DbReConnect(): bool
    {
        $res = $this->DBHandle->Execute("select 1");
        if (!$res) {
            $this->debug(self::DEBUG, "[DB CONNECTION LOST] - RECONNECT ATTEMPT");
            $this->DBHandle->Close();
            $scheme = $this->config['database']['dbtype'] === "postgres" ? "pgsql" : "mysqli";
            $datasource = sprintf(
                "%s://%s:%s@%s/%s",
                $scheme,
                $this->config["database"]["user"],
                $this->config["database"]["password"],
                $this->config["database"]["hostname"],
                $this->config["database"]["dbname"]
            );
            $count = 1;
            $sleep = 1;
            do {
                $this->DBHandle = NewADOConnection($datasource);
                if ($this->DBHandle !== false) {
                    break;
                }
                $this->debug(self::DEBUG, "[DB CONNECTION LOST]- RECONNECT FAILED ,ATTEMPT $count sleep for $sleep ");
                sleep($sleep);
                $count++;
                $sleep *= 2;
            } while ($count < 5);
            if ($this->DBHandle === false) {
                $this->debug(self::FATAL, "[DB CONNECTION LOST] CDR NOT POSTED");
                return false;
            }
            if ($this->config['database']['dbtype'] === "mysql") {
                $this->DBHandle->Execute('SET AUTOCOMMIT = 1');
            }
            if (empty($this->table)) {
                $this->table = new Table();
            }

            $this->debug(self::DEBUG, "[NO DB CONNECTION] - RECONNECT OK]");

        } else {
            $res->Close();
        }

        return true;
    }

    /*
    * Function DbDisconnect
    */
    public function DbDisconnect()
    {
        $this->DBHandle->Disconnect();
    }

    public function save_redial_number(string $number): void
    {
        if ($this->mode === 'did' || $this->mode === 'callback') {
            return;
        }
        $query = "UPDATE cc_card SET redial = ? WHERE username = ?";
        $params = [$number, $this->accountcode];
        $this->DBHandle->Execute($query, $params);
        $this->debug(self::DEBUG, "Query: $query", $params);
    }

    public function calculate_time_condition(int $now, int $timeinterval, string $type): int
    {
        $week_range = [
            'mon' => 1,
            'tue' => 2,
            'wed' => 3,
            'thu' => 4,
            'fri' => 5,
            'sat' => 6,
            'sun' => 7
        ];

        $month_range = [
            'jan' => 1,
            'feb' => 2,
            'mar' => 3,
            'apr' => 4,
            'may' => 5,
            'jun' => 6,
            'jul' => 7,
            'aug' => 8,
            'sep' => 9,
            'oct' => 10,
            'nov' => 11,
            'dec' => 12
        ];

        if (empty($timeinterval)) {
            return $type === 'peak'? 1 : 0;
        }

        $cond_result = [];
        $row_conditions = $this->extract_cond_values($timeinterval);
        $x = 0;
        $cond_type = "";
        foreach ($row_conditions as $conditions) {

            /* Options */
            if (!empty($conditions[4])) {
                switch ($conditions[4][0]) {
                    case 0:
                    case 1:
                        break;
                    case 2:
                        switch (strtolower($conditions[4][1])) {
                            case "p":
                                // Peak
                                $cond_type = "peak";
                                break;
                            case "o":
                                // Off peak
                                $cond_type = "offpeak";
                                break;
                        }
                        break;
                    default:
                        // Default Peak
                        $cond_type = "peak";
                        break;
                }
            }
            if ($type == $cond_type) {
                $cond_result[$x] = 0;
                /* Time */
                switch ($conditions[0][0]) {
                    case 0:
                        $i = 0;
                        foreach ($conditions[0] as $condition) {
                            if ($i > 0) {
                                $conditions[0][$i] = strtotime($condition);
                            }
                            $i++;
                        }
                        if ($now >= $conditions[0][1] && $now <= $conditions[0][2]) {
                            $cond_result[$x] = $cond_result[$x] + 1;
                        }
                        break;
                    case 1:
                    case 2:
                        array_splice($conditions[0], 0, 1);
                        if (in_array(date("G:i", $now), $conditions[0])) {
                            $cond_result[$x] = $cond_result[$x] + 1;
                        }
                        break;
                    case 3:
                        $cond_result[$x] = $cond_result[$x] + 1;
                        break;
                }

                /* Day of week */
                switch ($conditions[1][0]) {
                    case 0:
                        $day = date("N", $now);
                        if ($day >= $week_range[strtolower($conditions[1][1])] && $day <= $week_range[strtolower($conditions[1][2])]) {
                            $cond_result[$x] += 2;
                        }
                        break;
                    case 1:
                    case 2:
                        $day = strtolower(date("D", $now));
                        array_splice($conditions[1], 0, 1);
                        $conditions[1] = array_map('strtolower', $conditions[1]);
                        if (in_array($day, $conditions[1])) {
                            $cond_result[$x] += 2;
                        }
                        break;
                    case 3:
                        $cond_result[$x] += 2;
                        break;
                }

                /* Day of month */
                switch ($conditions[2][0]) {
                    case 0:
                        $month_day = date("j", $now);
                        if ($month_day >= $conditions[2][1] && $month_day <= $conditions[2][2]) {
                            $cond_result[$x] += 4;
                        }
                        break;
                    case 1:
                    case 2:
                        $month_day = date("j", $now);
                        array_splice($conditions[2], 0, 1);
                        if (in_array($month_day, $conditions[2])) {
                            $cond_result[$x] += 4;
                        }
                        break;
                    case 3:
                        $cond_result[$x] += 4;
                        break;
                }

                /* Month */
                switch ($conditions[3][0]) {
                    case 0:
                        $month = strtolower(date("n", $now));
                        if ($month >= $month_range[strtolower($conditions[3][1])] && $month <= $month_range[strtolower($conditions[3][2])]) {
                            $cond_result[$x] += 8;
                        }
                        break;
                    case 1:
                    case 2:
                        $month = strtolower(date("M", $now));
                        array_splice($conditions[3], 0, 1);
                        $conditions[3] = array_map('strtolower', $conditions[3]);
                        if (in_array($month, $conditions[3])) {
                            $cond_result[$x] += 8;
                        }
                        break;
                    case 3:
                        $cond_result[$x] += 8;
                        break;
                }
                $x++;
            }
        }
        $i = 0;
        $final_result_set = 0;
        foreach ($cond_result as $result) {
            if ($result == 15) {
                $final_result_set += pow(2, $i);
            }
            $i++;
        }

        return $final_result_set;
    }

    public function extract_cond_values(string $value): array
    {
        $output = [];
        $rows = explode("\n", $value);
        foreach ($rows as $i => $row) {
            $items = explode("|", trim($row));
            foreach ($items as $item) {
                if (preg_match('/^([0-9a-z]+|\d+:\d+)-([0-9a-z]+|\d+:\d+)$/i', $item, $intvals)) {
                    $output[$i][] = [0, $intvals[1], $intvals[2]];
                } elseif (preg_match('/^([0-9a-z]+|\d+:\d+)(,[0-9a-z]+|,\d+:\d+)+$/i', $item)) {
                    $output[$i][] = array_merge([1], explode(',', $item));
                } elseif (preg_match('/^([0-9a-z]+|\d+:\d+)$/i', $item)) {
                    $output[$i][] = [2, $item];
                } elseif ($item === "*") {
                    $output[$i][] = [3];
                } else {
                    $output[$i][] = [-1];
                }
            }
        }

        return $output;
    }

    private function get_currencies(): array
    {
        $result = $this->DBHandle->CacheGetAll(900, "SELECT currency, `value` FROM cc_currencies ORDER BY id");

        return array_combine(
            array_column($result, "currency"),
            array_column($result, "value")
        );
    }
}
