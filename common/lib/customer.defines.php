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
session_name("UICSESSION");
session_start();

const BINDTEXTDOMAIN = __DIR__ . '/../cust_ui_locale';

require_once __DIR__ . "/common.defines.php";

// these constants are used in the included file
define ("RETURN_URL_DISTANT_LOGIN", $A2B->config["webcustomerui"]['return_url_distant_login'] ?? null);
define ("RETURN_URL_DISTANT_FORGETPASSWORD", $A2B->config["webcustomerui"]['return_url_distant_forgetpassword'] ?? null);

if (PHP_SAPI !== "cli") {
    require_once __DIR__ . "/customer.module.access.php";
    Customer::checkPageAccess();
}

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

define ("ADMIN_EMAIL", $A2B->config["global"]['admin_email'] ?? null);

const ENABLE_LOG = false;
