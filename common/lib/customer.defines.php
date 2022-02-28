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

require_once __DIR__ . '/../../vendor/autoload.php';

const WRITELOG_QUERY = false;
define ("FSROOT", substr(dirname(__FILE__),0,-3));
const LIBDIR = FSROOT . "lib/";

sanitize_post_get();

$profiler = new Profiler();

session_name("UICSESSION");
session_start();

$G_instance_Query_trace = Query_trace::getInstance();

$A2B = new A2Billing();

// LOAD THE CONFIGURATION
//if (!isset($disable_load_conf) || !($disable_load_conf)) {
$res_load_conf = $A2B -> load_conf($agi, A2B_CONFIG_DIR."a2billing.conf", 1);
if (!$res_load_conf) exit;
//}

include (dirname(__FILE__) . "/common.defines.php");

// Define a demo mode
const DEMO_MODE = false;

define ("LEN_ALIASNUMBER", $A2B->config['global']['len_aliasnumber'] ?? null);
define ("LEN_VOUCHER", $A2B->config['global']['len_voucher'] ?? null);
define ("BASE_CURRENCY", $A2B->config['global']['base_currency'] ?? null);
define ("MANAGER_HOST", $A2B->config['global']['manager_host'] ?? null);
define ("MANAGER_USERNAME", $A2B->config['global']['manager_username'] ?? null);
define ("MANAGER_SECRET", $A2B->config['global']['manager_secret'] ?? null);
define ("SERVER_GMT", $A2B->config['global']['server_GMT'] ?? null);
define ("CUSTOMER_UI_URL", $A2B->config['global']['customer_ui_url'] ?? null);

//Enable Disable Captcha
define ("CAPTCHA_ENABLE", $A2B->config["signup"]['enable_captcha'] ?? 0);

//Enable Disable
define ("LANGUAGE_ENABLE", $A2B->config["signup"]['field_language'] ?? 0);
define ("CURRENCY_ENABLE", $A2B->config["signup"]['field_currency'] ?? 0);
define ("LASTNAME_ENABLE", $A2B->config["signup"]['field_lastname'] ?? 0);
define ("FIRSTNAME_ENABLE", $A2B->config["signup"]['field_firstname'] ?? 0);
define ("ADDRESS_ENABLE", $A2B->config["signup"]['field_address'] ?? 0);
define ("CITY_ENABLE", $A2B->config["signup"]['field_city'] ?? 0);
define ("STATE_ENABLE", $A2B->config["signup"]['field_state'] ?? 0);
define ("COUNTRY_ENABLE", $A2B->config["signup"]['field_country'] ?? 0);
define ("ZIPCODE_ENABLE", $A2B->config["signup"]['field_zipcode'] ?? 0);
define ("TIMEZONE_ENABLE", $A2B->config["signup"]['field_id_timezone'] ?? 0);
define ("PHONE_ENABLE", $A2B->config["signup"]['field_phone'] ?? 0);
define ("FAX_ENABLE", $A2B->config["signup"]['field_fax'] ?? 0);
define ("COMP_ENABLE", $A2B->config["signup"]['field_company'] ?? 0);
define ("COMP_WEB_ENABLE", $A2B->config["signup"]['field_company_website'] ?? 0);
define ("VAT_RN_ENABLE", $A2B->config["signup"]['field_VAT_RN'] ?? 0);
define ("TRAFFIC_ENABLE", $A2B->config["signup"]['field_traffic'] ?? 0);
define ("TRAFFIC_TARGET_ENABLE", $A2B->config["signup"]['field_traffic_target'] ?? 0);

// For ePayment Modules
const PULL_DOWN_DEFAULT = 'Please Select';
define('TEXT_CCVAL_ERROR_INVALID_DATE', gettext('The expiry date entered for the credit card is invalid.')."<br>".gettext('Please check the date and try again.'));
define('TEXT_CCVAL_ERROR_INVALID_NUMBER', gettext('The credit card number entered is invalid.')."<br>".gettext('Please check the number and try again.'));
define('TEXT_CCVAL_ERROR_UNKNOWN_CARD', gettext('The first four digits of the number entered are').": %s<br>".gettext('If that number is correct, we do not accept that type of credit card.')."<br>".gettext('If it is wrong, please try again.'));

const CC_OWNER_MIN_LENGTH = '2';
const CC_NUMBER_MIN_LENGTH = '15';

// javascript messages
define('JS_ERROR', gettext('Errors have occured during the process of your form.')."\n\n".gettext('Please make the following corrections:\n\n'));
define('JS_ERROR_NO_PAYMENT_MODULE_SELECTED', '* '.gettext('Please select a payment method for your order.').'\n');

define ("EPAYMENT_PURCHASE_AMOUNT", $A2B->config['epayment_method']['purchase_amount'] ?? null);

// WEB DEFINE FROM THE A2BILLING.CONF FILE
define ("EMAIL_ADMIN", $A2B->config['webui']['email_admin'] ?? null);
define ("NUM_MUSICONHOLD_CLASS", $A2B->config['webui']['num_musiconhold_class'] ?? null);
define ("SHOW_HELP", $A2B->config['webui']['show_help'] ?? null);
define ("MY_MAX_FILE_SIZE_IMPORT", $A2B->config['webui']['my_max_file_size_import'] ?? null);
define ("DIR_STORE_MOHMP3", $A2B->config['webui']['dir_store_mohmp3'] ?? null);
define ("DIR_STORE_AUDIO", $A2B->config['webui']['dir_store_audio'] ?? null);
define ("MY_MAX_FILE_SIZE_AUDIO", $A2B->config['webui']['my_max_file_size_audio'] ?? null);
$file_ext_allow = $A2B->config['webui']['file_ext_allow'] ?? null;
$file_ext_allow_musiconhold = $A2B->config['webui']['file_ext_allow_musiconhold'] ?? null;
define ("LINK_AUDIO_FILE", $A2B->config['webui']['link_audio_file'] ?? null);
define ("MONITOR_PATH", $A2B->config['webui']['monitor_path'] ?? null);
define ("ADVANCED_MODE", $A2B->config['webui']['advanced_mode'] ?? null);

define ("RETURN_URL_DISTANT_LOGIN", $A2B->config["webcustomerui"]['return_url_distant_login'] ?? null);
define ("RETURN_URL_DISTANT_FORGETPASSWORD", $A2B->config["webcustomerui"]['return_url_distant_forgetpassword'] ?? null);

if (!isset($_SESSION)) {
    session_start();
}

// GLOBAL POST/GET VARIABLE
getpost_ifset (['form_action', 'atmenu', 'action', 'stitle', 'sub_action', 'IDmanager', 'current_page', 'order', 'sens', 'mydisplaylimit', 'filterprefix', 'ui_language', 'cssname', 'popup_select', 'popup_formname', 'popup_fieldname', 'exporttype', 'msg']);

// Language Selection
if (isset($ui_language)) {
    $_SESSION["ui_language"] = $ui_language;
    setcookie  ("ui_language", $ui_language);
} elseif (!isset($_SESSION["ui_language"])) {
    if(!isset($_COOKIE["ui_language"])) {
        $_SESSION["ui_language"] = 'english';
    } else {
        $_SESSION["ui_language"] = $_COOKIE["ui_language"];
    }
}

define ("LANGUAGE", $_SESSION["ui_language"]);
const BINDTEXTDOMAIN = '../common/cust_ui_locale';
require 'languageSettings.php';
SetLocalLanguage();

if (isset($cssname) && $cssname != "") {
    if ($_SESSION["stylefile"]!=$cssname) {
        foreach (glob("./templates_c/*.*") as $filename) {
            unlink($filename);
        }
    }
    $_SESSION["stylefile"] = $cssname;
}

if (!isset($_SESSION["stylefile"]) || $_SESSION["stylefile"]=='') {
    $_SESSION["stylefile"]='default';
}

// EPayment Module Settings
define ("HTTP_SERVER", $A2B->config["epayment_method"]['http_server'] ?? null);
define ("HTTPS_SERVER", $A2B->config["epayment_method"]['https_server'] ?? null);
define ("HTTP_COOKIE_DOMAIN", $A2B->config["epayment_method"]['http_cookie_domain'] ?? null);
define ("HTTPS_COOKIE_DOMAIN", $A2B->config["epayment_method"]['https_cookie_domain'] ?? null);
define ("DIR_WS_HTTP_CATALOG", $A2B->config["epayment_method"]['dir_ws_http_catalog'] ?? null);
define ("DIR_WS_HTTPS_CATALOG", $A2B->config["epayment_method"]['dir_ws_https_catalog'] ?? null);
define ("ENABLE_SSL", $A2B->config["epayment_method"]['enable_ssl'] ?? null);
define ("EPAYMENT_TRANSACTION_KEY", $A2B->config["epayment_method"]['transaction_key'] ?? null);
define ("PAYPAL_VERIFY_URL", $A2B->config["epayment_method"]['paypal_verify_url'] ?? null);
define ("MONEYBOOKERS_SECRETWORD", $A2B->config["epayment_method"]['moneybookers_secretword'] ?? null);

//SIP/IAX Info
define ("SIP_IAX_INFO_TRUNKNAME", $A2B->config['sip-iax-info']['sip_iax_info_trunkname'] ?? null);
define ("SIP_IAX_INFO_ALLOWCODEC", $A2B->config['sip-iax-info']['sip_iax_info_allowcodec'] ?? null);
define ("SIP_IAX_INFO_HOST", $A2B->config['sip-iax-info']['sip_iax_info_host'] ?? null);
define ("IAX_ADDITIONAL_PARAMETERS", $A2B->config['sip-iax-info']['iax_additional_parameters'] ?? null);
define ("SIP_ADDITIONAL_PARAMETERS", $A2B->config['sip-iax-info']['sip_additional_parameters'] ?? null);

// Sign-up
define ("RELOAD_ASTERISK_IF_SIPIAX_CREATED", $A2B->config["signup"]['reload_asterisk_if_sipiax_created'] ?? 0);

//Images Path
define ("Images_Path","./templates/".$_SESSION["stylefile"]."/images");
define ("Images_Path_Main","./templates/".$_SESSION["stylefile"]."/images");
define ("KICON_PATH","./templates/".$_SESSION["stylefile"]."/images/kicons");
const DIR_WS_IMAGES = Images_Path . '/';
define ("ADMIN_EMAIL", $A2B->config["global"]['admin_email'] ?? null);

// INCLUDE HELP
include (dirname(__FILE__) . "/customer.help.php");

const ENABLE_LOG = 0;

//SQLi
$DBHandle  = DbConnect();
