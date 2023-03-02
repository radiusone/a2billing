<?php

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

use A2billing\Logger;

$FG_DEBUG = 0;
error_reporting(E_ALL & ~E_NOTICE);

const ACX_CUSTOMER = 1;
const ACX_BILLING = 2;            // 1 << 1
const ACX_RATECARD = 4;            // 1 << 2
const ACX_CALL_REPORT = 8;            // 1 << 3
const ACX_MYACCOUNT = 16;
const ACX_SUPPORT = 32;
const ACX_CREATE_CUSTOMER = 64;
const ACX_EDIT_CUSTOMER = 128;
const ACX_DELETE_CUSTOMER = 256;
const ACX_GENERATE_CUSTOMER = 512;
const ACX_SIGNUP = 1024;
const ACX_VOIPCONF = 2048;
const ACX_SEE_CUSTOMERS_CALLERID = 4096;

header("Expires: Sat, Jan 01 2000 01:01:01 GMT");

if (($_GET["logout"] ?? "") === "true") {
    (new Logger())->insertLogAgent($_SESSION["agent_id"], 1, "AGENT LOGGED OUT", "User Logged out from website", '', $_SERVER['REMOTE_ADDR'], $_SERVER['REQUEST_URI']);
    session_destroy();
    header ("HTTP/1.0 401 Unauthorized");
    header ("Location: index.php");
    die();
}

getpost_ifset (['pr_login', 'pr_password']);
/**
 * @var string $pr_login
 * @var string $pr_password
 */

if (!isset($_SESSION['pr_login']) || !isset($_SESSION['pr_password']) || !isset($_SESSION['rights']) || ($_POST["done"] ?? "") === "submit_log") {
    if (($_POST["done"] ?? "") === "submit_log") {

        $return = login ($pr_login, $pr_password);

        if (!is_array($return)) {
            header ("HTTP/1.0 401 Unauthorized");
            header ("Location: index.php?error=1");
            die();
        }

        $agent_id = $return[0];
        $rights = $return[1];

        $_SESSION["pr_login"] = $pr_login;
        $_SESSION["pr_password"] = $pr_password;
        $_SESSION["rights"] = $rights;
        $_SESSION["agent_id"] = $agent_id;
        $_SESSION["user_type"] = "AGENT";
        $_SESSION["currency"] = $return["currency"];
        $_SESSION["vat"] = $return["vat"];
        (new Logger())->insertLogAgent($agent_id, 1, "Agent Logged In", "Agent Logged in to website", '', $_SERVER['REMOTE_ADDR'], 'PP_Intro.php');
    } else {
        $_SESSION["rights"] = 0;
    }
}

/**
 * @param string|null $user
 * @param string|null $pass
 * @return bool|string[]
 */
function login (?string $user, ?string $pass)
{
    $user = trim($user);
    $pass = trim($pass);

    if (empty($user) || empty($pass)) {
        return false;
    }
    $QUERY = "SELECT id, perms, active, currency, vat, passwd FROM cc_agent WHERE login = ?";

    $DBHandle = DbConnect();
    $row = $DBHandle->GetRow($QUERY, [$user]);

    if ($row) {
        if ($row["active"] !== "t" && $row["active"] !== "1") {
            return false;
        }
        if (password_verify($pass, $row["passwd"])) {
            return $row;
        }
        // fallback to legacy authentication
        $pass = filter_var($pass, FILTER_SANITIZE_STRING);
        if (hash('whirlpool', $pass) === $row["passwd"]) {
            return $row;
        }
    }

    return false;
}

$ACXACCESS 					= $_SESSION["rights"] > 0;
$ACXSIGNUP 					= has_rights(ACX_SIGNUP);
$ACXCUSTOMER 				= has_rights(ACX_CUSTOMER);
$ACXBILLING 				= has_rights(ACX_BILLING);
$ACXRATECARD 				= has_rights(ACX_RATECARD);
$ACXCALLREPORT				= has_rights(ACX_CALL_REPORT);
$ACXMYACCOUNT  				= has_rights(ACX_MYACCOUNT);
$ACXSUPPORT  				= has_rights(ACX_SUPPORT);
$ACXCREATECUSTOMER  		= has_rights(ACX_CREATE_CUSTOMER);
$ACXEDITCUSTOMER  			= has_rights(ACX_EDIT_CUSTOMER);
$ACXDELETECUSTOMER  		= has_rights(ACX_DELETE_CUSTOMER);
$ACXGENERATECUSTOMER  		= has_rights(ACX_GENERATE_CUSTOMER);
$ACXVOIPCONF  				= has_rights(ACX_VOIPCONF);
$ACXSEE_CUSTOMERS_CALLERID	= has_rights(ACX_SEE_CUSTOMERS_CALLERID);
