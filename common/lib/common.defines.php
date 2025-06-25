<?php

use A2billing\A2Billing;
use Profiler_Profiler as Profiler;

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

require_once __DIR__ . '/../../vendor/autoload.php';

// $profiler = new Profiler();

// LOAD THE CONFIGURATION
$A2B = new A2Billing();

// GLOBAL POST/GET VARIABLE
getpost_ifset (['form_action', 'action', 'form_el_index', 'current_page', 'order', 'sens', 'mydisplaylimit', 'popup_select', 'popup_formname', 'popup_fieldname', 'ui_language', 'msg', 'exporttype']);
/**
 * @var string|null $form_action
 * @var string|null $action
 * @var string|null $form_el_index
 * @var string|null $current_page
 * @var string|null $order
 * @var string|null $sens
 * @var string|null $mydisplaylimit
 * @var string|null $popup_select
 * @var string|null $popup_formname
 * @var string|null $popup_fieldname
 * @var string|null $ui_language
 * @var string|null $msg
 * @var string|null $exporttype
 */
$popup_select ??= "0";
$popup_formname ??= "";
$popup_fieldname ??= "";
$form_action ??= null;

// SETTINGS FOR DATABASE CONNECTION
define ("HOST", $A2B->config['database']['hostname'] ?? null);
define ("PORT", $A2B->config['database']['port'] ?? null);
define ("USER", $A2B->config['database']['user'] ?? null);
define ("PASS", $A2B->config['database']['password'] ?? null);
define ("DBNAME", $A2B->config['database']['dbname'] ?? null);
define ("DB_TYPE", $A2B->config['database']['dbtype'] ?? null);
define ("CSRF_SALT", $A2B->config['csrf']['csrf_token_salt'] ?? 'YOURSALT');

// SETTING FOR REALTIME
define ("USE_REALTIME", $A2B->config['global']['use_realtime'] ?? 0);

define ("BASE_CURRENCY", $A2B->config['global']['base_currency'] ?? null);
define ("MANAGER_HOST", $A2B->config['global']['manager_host'] ?? null);
define ("MANAGER_USERNAME", $A2B->config['global']['manager_username'] ?? null);
define ("MANAGER_SECRET", $A2B->config['global']['manager_secret'] ?? null);

// VOICEMAIL
const ACT_VOICEMAIL = false;

// WEB DEFINE FROM THE A2BILLING.CONF FILE
define ("MY_MAX_FILE_SIZE_IMPORT", $A2B->config['webui']['my_max_file_size_import'] ?? null);
define ("ADVANCED_MODE", $A2B->config['webui']['advanced_mode'] ?? null);

// Language Selection
if (isset($ui_language)) {
    $_SESSION["ui_language"] = $ui_language;
    setcookie("ui_language", $ui_language);
} elseif (!isset($_SESSION["ui_language"])) {
    $_SESSION["ui_language"] = $_COOKIE["ui_language"] ?? "english";
}

switch ($_SESSION["ui_language"] ?? "") {
    case "brazilian":
        $languageEncoding = "pt_BR.UTF-8";
        $slectedLanguage = "pt_BR";
        $charEncoding = "UTF-8";
        break;
    case "chinese":
        $languageEncoding = "zh_CN.UTF-8";
        $slectedLanguage = "zh_CN";
        $charEncoding = "UTF-8";
        break;
    case "spanish":
        $languageEncoding = "es_ES.iso88591";
        $slectedLanguage = "es_ES";
        $charEncoding = "UTF-8";
        break;
    case "french":
        $languageEncoding = "fr_FR.iso88591";
        $slectedLanguage = "fr_FR";
        $charEncoding = "iso-8859-1";
        break;
    case "german":
        $languageEncoding = "de_DE.iso88591";
        $slectedLanguage = "de_DE";
        $charEncoding = "iso-8859-1";
        break;
    case "italian":
        $languageEncoding = "it_IT.iso8859-1";
        $slectedLanguage = "it_IT";
        $charEncoding = "iso88591";
        break;
    case "polish":
        $languageEncoding = "pt_PT.iso88591";
        $slectedLanguage = "pl_PL";
        $charEncoding = "iso88591";
        break;
    case "romanian":
        $languageEncoding = "ro_RO.iso88591";
        $slectedLanguage = "ro_RO";
        $charEncoding = "iso88591";
        break;
    case "russian":
        $languageEncoding = "ru_RU.UTF-8";
        $slectedLanguage = "ru_RU";
        $charEncoding = "UTF-8";
        break;
    case "turkish":
        // issues with Turkish
        // http://forum.elxis.org/index.php?action=printpage%3Btopic=3090.0
        // http://bugs.php.net/bug.php?id=39993
        $languageEncoding = "tr_TR.UTF-8";
        $slectedLanguage = "tr_TR.UTF-8";
        $charEncoding = "UTF-8";
        break;
    case "urdu":
        $languageEncoding = "ur.UTF-8";
        $slectedLanguage = "ur_PK";
        $charEncoding = "UTF-8";
        break;
    case "ukrainian": // provided by Oleh Miniv  email: oleg-min@ukr.net
        $languageEncoding = "uk_UA.UTF8";
        $slectedLanguage = "uk_UA";
        $charEncoding = "UTF8";
        break;
    case "farsi":
        $languageEncoding = "fa_IR.UTF-8";
        $slectedLanguage = "fa_IR";
        $charEncoding = "UTF-8";
        break;
    case "greek":
        $languageEncoding = "el_GR.UTF-8";
        $slectedLanguage = "el_GR";
        $charEncoding = "UTF-8";
        break;
    case "indonesian":
        $languageEncoding = "id_ID.iso88591";
        $slectedLanguage = "id_ID";
        $charEncoding = "iso88591";
        break;
    default:
        $languageEncoding = "en_US.iso88591";
        $slectedLanguage = "en_US";
        $charEncoding = "iso88591";
        break;
}

setlocale(LC_TIME, $languageEncoding);
putenv("LANG=$slectedLanguage");
putenv("LANGUAGE=$slectedLanguage");
setlocale(LC_ALL, $slectedLanguage);
setlocale(LC_MESSAGES, $languageEncoding);

textdomain("messages");
bindtextdomain("messages", BINDTEXTDOMAIN);
bind_textdomain_codeset("messages", $charEncoding);

/*
 *		GLOBAL USED VARIABLE
 */
// A2BILLING INFO
const COPYRIGHT = <<< HTML
A2Billing v3.0 is licensed under the <a href="https://www.gnu.org/licenses/agpl-3.0.en.html" target="_blank">AGPL 3</a><br/>
Copyright © 2004-2015 Star2billing SL, © 2022-2025 RadiusOne Inc.
HTML;
define ("CCMAINTITLE", gettext("A2Billing Portal"));

$DBHandle = DbConnect();
