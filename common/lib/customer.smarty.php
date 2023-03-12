<?php

use A2billing\Customer;

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

error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));

const SMARTY_DIR = __DIR__ . '/../../vendor/smarty/smarty/libs/';
const TEMPLATE_DIR = __DIR__ . '/../../customer/templates/';
const TEMPLATE_C_DIR = __DIR__ . '/../../customer/templates_c/';
require_once __DIR__ . "/../../vendor/autoload.php";

$smarty = new Smarty();

$skin_name = $_SESSION["stylefile"];

$smarty->setTemplateDir(TEMPLATE_DIR . $skin_name.'/');
$smarty->setCompileDir(TEMPLATE_C_DIR);
$smarty->setPluginsDir("./plugins/");

$smarty->assign("COPYRIGHT", COPYRIGHT);
$smarty->assign("CCMAINTITLE", CCMAINTITLE);

$smarty->assign("ACXPASSWORD", has_rights(Customer::ACX_PASSWORD));
$smarty->assign("ACXSIP_IAX", has_rights(Customer::ACX_SIP_IAX));
$smarty->assign("ACXCALL_HISTORY", has_rights(Customer::ACX_CALL_HISTORY));
$smarty->assign("ACXPAYMENT_HISTORY", has_rights(Customer::ACX_PAYMENT_HISTORY));
$smarty->assign("ACXVOUCHER", has_rights(Customer::ACX_VOUCHER));
$smarty->assign("ACXINVOICES", has_rights(Customer::ACX_INVOICES));
$smarty->assign("ACXDID", has_rights(Customer::ACX_DID));
$smarty->assign("ACXSPEED_DIAL", has_rights(Customer::ACX_SPEED_DIAL));
$smarty->assign("ACXRATECARD", has_rights(Customer::ACX_RATECARD));
$smarty->assign("ACXSIMULATOR", has_rights(Customer::ACX_SIMULATOR));
$smarty->assign("ACXWEB_PHONE", has_rights(Customer::ACX_WEB_PHONE));
$smarty->assign("ACXCALL_BACK", has_rights(Customer::ACX_CALL_BACK));
$smarty->assign("ACXCALLER_ID", has_rights(Customer::ACX_CALLER_ID));
$smarty->assign("ACXSUPPORT", has_rights(Customer::ACX_SUPPORT));
$smarty->assign("ACXNOTIFICATION", has_rights(Customer::ACX_NOTIFICATION));
$smarty->assign("ACXAUTODIALER", has_rights(Customer::ACX_AUTODIALER));
$smarty->assign("ACXVOICEMAIL", ACT_VOICEMAIL ? $_SESSION["voicemail"] : false);

if ($exporttype != "" && $exporttype != "html") {
    $smarty->assign("EXPORT", 1);
} else {
    $smarty->assign("EXPORT", 0);
}

getpost_ifset(['section']);

if (!empty($section)) {
    $_SESSION["menu_section"] = intval($section);
} else {
    $section = $_SESSION["menu_section"];
}
$smarty->assign("section", $section);

$smarty->assign("SKIN_NAME", $skin_name);
// if it is a pop window
if (!is_numeric($popup_select)) {
    $popup_select=0;
}
// for menu
$smarty->assign("popupwindow", $popup_select);

// OPTION FOR THE MENU
$smarty->assign("A2Bconfig", $A2B->config);
