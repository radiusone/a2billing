<?php

use A2billing\A2Billing;
use A2billing\Profiler;
use A2billing\Query_trace;

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

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

require_once __DIR__ . '/../../vendor/autoload.php';

SetLocalLanguage();

const LIBDIR = __DIR__;

const WRITELOG_QUERY = false;

sanitize_post_get();

const DEBUG = false;
$profiler = DEBUG ? new Profiler() : null;
$G_instance_Query_trace = DEBUG ? Query_trace::getInstance() : null;

// LOAD THE CONFIGURATION
$A2B = new A2Billing();

// GLOBAL POST/GET VARIABLE
getpost_ifset (['form_action', 'action', 'form_el_index', 'IDmanager', 'current_page', 'order', 'sens', 'mydisplaylimit', 'cssname', 'popup_select', 'popup_formname', 'popup_fieldname', 'ui_language', 'msg', 'exporttype']);
/**
 * @var string $form_action
 * @var string $action
 * @var string $form_el_index
 * @var string $IDmanager
 * @var string $current_page
 * @var string $order
 * @var string $sens
 * @var string $mydisplaylimit
 * @var string $cssname
 * @var string $popup_select
 * @var string $popup_formname
 * @var string $popup_fieldname
 * @var string $ui_language
 * @var string $msg
 * @var string $exporttype
 */

// Enable UI Logger
const ENABLE_LOG = true;

// SETTINGS FOR DATABASE CONNECTION
define ("HOST", $A2B->config['database']['hostname'] ?? null);
define ("PORT", $A2B->config['database']['port'] ?? null);
define ("USER", $A2B->config['database']['user'] ?? null);
define ("PASS", $A2B->config['database']['password'] ?? null);
define ("DBNAME", $A2B->config['database']['dbname'] ?? null);
define ("DB_TYPE", $A2B->config['database']['dbtype'] ?? null);
define ("CSRF_SALT", $A2B->config['csrf']['csrf_token_salt'] ?? 'YOURSALT');

// SETTINGS FOR SMTP
define ("SMTP_SERVER", $A2B->config['global']['smtp_server'] ?? null);
define ("SMTP_HOST", $A2B->config['global']['smtp_host'] ?? null);
define ("SMTP_USERNAME", $A2B->config['global']['smtp_username'] ?? null);
define ("SMTP_PASSWORD", $A2B->config['global']['smtp_password'] ?? null);
define ("SMTP_PORT", $A2B->config['global']['smtp_port'] ?? '25');
define ("SMTP_SECURE", $A2B->config['global']['smtp_secure'] ?? null);

// SETTING FOR REALTIME
define ("USE_REALTIME", $A2B->config['global']['use_realtime'] ?? 0);

// SIP IAX FRIEND CREATION
define ("FRIEND_TYPE", $A2B->config['peer_friend']['type'] ?? null);
define ("FRIEND_ALLOW", $A2B->config['peer_friend']['allow'] ?? null);
define ("FRIEND_CONTEXT", $A2B->config['peer_friend']['context'] ?? null);
define ("FRIEND_NAT", $A2B->config['peer_friend']['nat'] ?? null);
define ("FRIEND_AMAFLAGS", $A2B->config['peer_friend']['amaflags'] ?? null);
define ("FRIEND_QUALIFY", $A2B->config['peer_friend']['qualify'] ?? null);
define ("FRIEND_HOST", $A2B->config['peer_friend']['host'] ?? null);
define ("FRIEND_DTMFMODE", $A2B->config['peer_friend']['dtmfmode'] ?? null);

define ("API_LOGFILE", $A2B->config['webui']['api_logfile'] ?? "/var/log/a2billing/");

// BUDDY ASTERISK FILES
define ("BUDDY_SIP_FILE", $A2B->config['webui']['buddy_sip_file'] ?? null);
define ("BUDDY_IAX_FILE", $A2B->config['webui']['buddy_iax_file'] ?? null);

// BACKUP
define ("BACKUP_PATH", $A2B->config['backup']['backup_path'] ?? null);
define ("GZIP_EXE", $A2B->config['backup']['gzip_exe'] ?? null);
define ("GUNZIP_EXE", $A2B->config['backup']['gunzip_exe'] ?? null);
define ("MYSQLDUMP", $A2B->config['backup']['mysqldump'] ?? null);
define ("PG_DUMP", $A2B->config['backup']['pg_dump'] ?? null);
define ("MYSQL", $A2B->config['backup']['mysql'] ?? null);
define ("PSQL", $A2B->config['backup']['psql'] ?? null);

define ("LEN_ALIASNUMBER", $A2B->config['global']['len_aliasnumber'] ?? null);
define ("LEN_VOUCHER", $A2B->config['global']['len_voucher'] ?? null);
define ("BASE_CURRENCY", $A2B->config['global']['base_currency'] ?? null);
define ("MANAGER_HOST", $A2B->config['global']['manager_host'] ?? null);
define ("MANAGER_USERNAME", $A2B->config['global']['manager_username'] ?? null);
define ("MANAGER_SECRET", $A2B->config['global']['manager_secret'] ?? null);
define ("SERVER_GMT", $A2B->config['global']['server_GMT'] ?? null);
define ("CUSTOMER_UI_URL", $A2B->config['global']['customer_ui_url'] ?? null);

define ("API_SECURITY_KEY", $A2B->config['webui']['api_security_key'] ?? null);

// EPayment Module Settings
define ("HTTP_SERVER", $A2B->config["epayment_method"]['http_server_agent'] ?? null);
define ("HTTPS_SERVER", $A2B->config["epayment_method"]['https_server_agent'] ?? null);
define ("HTTP_COOKIE_DOMAIN", $A2B->config["epayment_method"]['http_cookie_domain_agent'] ?? null);
define ("HTTPS_COOKIE_DOMAIN", $A2B->config["epayment_method"]['https_cookie_domain_agent'] ?? null);
define ("DIR_WS_HTTP_CATALOG", $A2B->config["epayment_method"]['dir_ws_http_catalog_agent'] ?? null);
define ("DIR_WS_HTTPS_CATALOG", $A2B->config["epayment_method"]['dir_ws_https_catalog_agent'] ?? null);
define ("ENABLE_SSL", $A2B->config["epayment_method"]['enable_ssl'] ?? null);
define ("EPAYMENT_TRANSACTION_KEY", $A2B->config["epayment_method"]['transaction_key'] ?? null);
define ("PAYPAL_VERIFY_URL", $A2B->config["epayment_method"]['paypal_verify_url'] ?? null);
define ("MONEYBOOKERS_SECRETWORD", $A2B->config["epayment_method"]['moneybookers_secretword'] ?? null);
define ("EPAYMENT_PURCHASE_AMOUNT", $A2B->config['epayment_method']['purchase_amount_agent'] ?? null);

const CC_OWNER_MIN_LENGTH = '2';
const CC_NUMBER_MIN_LENGTH = '15';

//SIP/IAX Info
define ("SIP_IAX_INFO_TRUNKNAME", $A2B->config['sip-iax-info']['sip_iax_info_trunkname'] ?? null);
define ("SIP_IAX_INFO_ALLOWCODEC", $A2B->config['sip-iax-info']['sip_iax_info_allowcodec'] ?? null);
define ("SIP_IAX_INFO_HOST", $A2B->config['sip-iax-info']['sip_iax_info_host'] ?? null);
define ("IAX_ADDITIONAL_PARAMETERS", $A2B->config['sip-iax-info']['iax_additional_parameters'] ?? null);
define ("SIP_ADDITIONAL_PARAMETERS", $A2B->config['sip-iax-info']['sip_additional_parameters'] ?? null);

// VOICEMAIL
const ACT_VOICEMAIL = false;

// WEB DEFINE FROM THE A2BILLING.CONF FILE
define ("EMAIL_ADMIN", $A2B->config['webui']['email_admin'] ?? 'root@localhost');
define ("SHOW_HELP", $A2B->config['webui']['show_help'] ?? null);
define ("MY_MAX_FILE_SIZE_IMPORT", $A2B->config['webui']['my_max_file_size_import'] ?? null);
define ("DIR_STORE_MOHMP3", $A2B->config['webui']['dir_store_mohmp3'] ?? null);
define ("DIR_STORE_AUDIO", $A2B->config['webui']['dir_store_audio'] ?? null);
define ("MY_MAX_FILE_SIZE_AUDIO", $A2B->config['webui']['my_max_file_size_audio'] ?? null);
$file_ext_allow = is_array($A2B->config['webui']['file_ext_allow'])?$A2B->config['webui']['file_ext_allow']:null;
$file_ext_allow_musiconhold = is_array($A2B->config['webui']['file_ext_allow_musiconhold'])?$A2B->config['webui']['file_ext_allow_musiconhold']:null;
define ("LINK_AUDIO_FILE", $A2B->config['webui']['link_audio_file'] ?? null);
define ("MONITOR_PATH", $A2B->config['webui']['monitor_path'] ?? null);
define ("ADVANCED_MODE", $A2B->config['webui']['advanced_mode'] ?? null);
define ("DELETE_FK_CARD", $A2B->config['webui']['delete_fk_card'] ?? null);
define ("CARD_EXPORT_FIELD_LIST", $A2B->config['webui']['card_export_field_list'] ?? null);
define ("RATE_EXPORT_FIELD_LIST", $A2B->config['webui']['rate_export_field_list'] ?? null);
define ("VOUCHER_EXPORT_FIELD_LIST", $A2B->config['webui']['voucher_export_field_list'] ?? null);

define ("RELOAD_ASTERISK_IF_SIPIAX_CREATED", $A2B->config["signup"]['reload_asterisk_if_sipiax_created'] ?? 0);

# define the amount of emails you want to send per period. If 0, batch processing
# is disabled and messages are sent out as fast as possible
const MAILQUEUE_BATCH_SIZE = 0;

# define the length of one batch processing period, in seconds (3600 is an hour)
const MAILQUEUE_BATCH_PERIOD = 3600;

# to avoid overloading the server that sends your email, you can add a little delay
# between messages that will spread the load of sending
# you will need to find a good value for your own server
# value is in seconds (or you can play with the autothrottle below)
const MAILQUEUE_THROTTLE = 0;

// Language Selection
if (isset($ui_language)) {
    $_SESSION["ui_language"] = $ui_language;
    setcookie("ui_language", $ui_language);
} elseif (!isset($_SESSION["ui_language"])) {
    $_SESSION["ui_language"] = $_COOKIE["ui_language"] ?? "english";
}

// Open menu
if (!empty($section)) {
    $_SESSION["menu_section"] = intval($section);
}

if (!empty($cssname)) {
    if ($_SESSION["stylefile"] !== $cssname) {
        foreach (glob("./templates_c/*.*") as $filename) {
            unlink($filename);
        }
    }
    $_SESSION["stylefile"] = $cssname;
}

if (empty($_SESSION["stylefile"])) {
    $_SESSION["stylefile"] = "default";
}

/*
 *		GLOBAL USED VARIABLE
 */
// A2BILLING INFO
const COPYRIGHT = <<< HTML
A2Billing v3.0 is licensed under the <a href="https://www.gnu.org/licenses/agpl-3.0.en.html" target="_blank">AGPL 3</a><br/>
Copyright © 2004-2015 Star2billing SL, © 2022 RadiusOne Inc.
HTML;
define ("CCMAINTITLE", gettext("A2Billing Portal"));

$DBHandle = DbConnect();
