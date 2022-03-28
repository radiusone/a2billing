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
use A2billing\NotificationsDAO;

$FG_DEBUG = 0;
error_reporting(E_ALL & ~E_NOTICE);

const ACX_CUSTOMER = 1;
const ACX_BILLING = 2;            // 1 << 1
const ACX_RATECARD = 4;            // 1 << 2
const ACX_TRUNK = 8;            // 1 << 3
const ACX_CALL_REPORT = 16;        // 1 << 4
const ACX_CRONT_SERVICE = 32;        // 1 << 5
const ACX_ADMINISTRATOR = 64;        // 1 << 6
const ACX_MAINTENANCE = 128;        // 1 << 7
const ACX_MAIL = 256;        // 1 << 8
const ACX_DID = 512;        // 1 << 9
const ACX_CALLBACK = 1024;        // 1 << 10
const ACX_OUTBOUNDCID = 2048;        // 1 << 11
const ACX_PACKAGEOFFER = 4096;        // 1 << 12
const ACX_PREDICTIVE_DIALER = 8192;        // 1 << 13
const ACX_INVOICING = 16384;        // 1 << 14
const ACX_SUPPORT = 32768;        // 1 << 15
const ACX_DASHBOARD = 65536;        // 1 << 16
const ACX_ACXSETTING = 131072;    // 1 << 17
const ACX_MODIFY_REFILLS = 262144;    // 1 << 18
const ACX_MODIFY_PAYMENTS = 524288;    // 1 << 19
const ACX_MODIFY_CUSTOMERS = 1048576;    // 1 << 20
const ACX_DELETE_NOTIFICATIONS = 2097152;    // 1 << 21
const ACX_DELETE_CDR = 4194304;    // 1 << 22
const ACX_MODIFY_ADMINS = 8388608;    // 1 << 23
const ACX_MODIFY_AGENTS = 16777216;    // 1 << 24

header("Expires: Sat, Jan 01 2000 01:01:01 GMT");

if (($_GET["logout"] ?? "") === "true") {
    (new Logger())->insertLog($_SESSION["admin_id"], 1, "USER LOGGED OUT", "User Logged out from website", '', $_SERVER['REMOTE_ADDR'], $_SERVER['REQUEST_URI']);
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

if (!isset($_SESSION['pr_login']) || !isset($_SESSION['pr_password']) || !isset($_SESSION['rights']) || ($_POST["done"] ?? "")  === "submit_log") {
    if (($_POST["done"] ?? "") === "submit_log") {

        $return = login ($pr_login, $pr_password);

        if (!is_array($return) || $return[1] == 0 ) {
            header ("HTTP/1.0 401 Unauthorized");
            header ("Location: index.php?error=1");
            die();
        }

        $pr_login = $return["login"];
        $admin_id = (int)$return["userid"];
        $pr_groupID = (int)$return["groupid"];

        if ($pr_groupID === 0) {
            $pr_reseller_ID = null;
            $rights = 33554431;
            $is_admin = 1;
        } else {
            // there wasn't a $return[4] here originally maybe they meant to select confaddcust?
            // $pr_reseller_ID = ($pr_groupID === 3) ? $return[4] : $return[0];
            $pr_reseller_ID = $admin_id;
            $rights = $return[1];
            $is_admin = ($pr_groupID === 1) ? 1 : 0;
        }

        $_SESSION["pr_login"] = $pr_login;
        $_SESSION["pr_password"] = $pr_password;
        $_SESSION["rights"] = $rights;
        $_SESSION["is_admin"] = $is_admin;
        $_SESSION["user_type"] = "ADMIN";
        $_SESSION["pr_reseller_ID"] = $pr_reseller_ID;
        $_SESSION["pr_groupID"] = $pr_groupID;
        $_SESSION["admin_id"] = $admin_id;
        (new Logger())->insertLog($admin_id, 1, "User Logged In", "User Logged in to website", '', $_SERVER['REMOTE_ADDR'], 'PP_Intro.php');
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

    $QUERY = "SELECT userid, perms, confaddcust, groupid, login, pwd_encoded FROM cc_ui_authen WHERE login = ?";

    $DBHandle = DbConnect();
    $res = $DBHandle -> Execute($QUERY, [$user]);

    if ($res && $row = $res->FetchRow()) {
        if (password_verify($pass, $row["pwd_encoded"])) {
            return $row;
        }
        // fallback to legacy authentication
        $pass = filter_var($pass, FILTER_SANITIZE_STRING);
        if (hash('whirlpool', $pass) === $row["pwd_encoded"]) {
            return $row;
        }
    }
    return false;
}

function has_rights($condition): bool
{
    return (bool)($_SESSION["rights"] & $condition);
}

$ACXACCESS 				= $_SESSION["rights"] > 0;
$ACXDASHBOARD			= has_rights(ACX_DASHBOARD);
$ACXCUSTOMER 			= has_rights(ACX_CUSTOMER);
$ACXBILLING 			= has_rights(ACX_BILLING);
$ACXRATECARD 			= has_rights(ACX_RATECARD);
$ACXTRUNK				= has_rights(ACX_TRUNK);
$ACXDID					= has_rights(ACX_DID);
$ACXCALLREPORT			= has_rights(ACX_CALL_REPORT);
$ACXCRONTSERVICE		= has_rights(ACX_CRONT_SERVICE);
$ACXMAIL 				= has_rights(ACX_MAIL);
$ACXADMINISTRATOR 		= has_rights(ACX_ADMINISTRATOR);
$ACXMAINTENANCE 		= has_rights(ACX_MAINTENANCE);
$ACXCALLBACK			= has_rights(ACX_CALLBACK);
$ACXOUTBOUNDCID 		= has_rights(ACX_OUTBOUNDCID);
$ACXPACKAGEOFFER 		= has_rights(ACX_PACKAGEOFFER);
$ACXPREDICTIVEDIALER 	= has_rights(ACX_PREDICTIVE_DIALER);
$ACXINVOICING 			= has_rights(ACX_INVOICING);
$ACXSUPPORT 			= has_rights(ACX_SUPPORT);
$ACXSETTING 			= has_rights(ACX_ACXSETTING);
$ACXMODIFY_REFILLS 		= has_rights(ACX_MODIFY_REFILLS);
$ACXMODIFY_PAYMENTS 	= has_rights(ACX_MODIFY_PAYMENTS);
$ACXMODIFY_CUSTOMERS 	= has_rights(ACX_MODIFY_CUSTOMERS);
$ACXDELETE_NOTIFICATIONS= has_rights(ACX_DELETE_NOTIFICATIONS);
$ACXDELETE_CDR			= has_rights(ACX_DELETE_CDR);

if(isset($_SESSION["admin_id"])) {
    $NEW_NOTIFICATION = NotificationsDAO::IfNewNotification($_SESSION["admin_id"]);
}
