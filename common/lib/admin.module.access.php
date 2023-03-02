<?php

use A2billing\Admin;

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

        if (!is_array($return) || $return["perms"] === "0" || $return["groupid"] > 1 ) {
            header ("HTTP/1.0 401 Unauthorized");
            header ("Location: index.php?error=1");
            die();
        }

        $admin_id = (int)$return["userid"];

        if ($return["groupid"] === "0") {
            $rights = 33554431;
        } else {
            $rights = $return["perms"];
        }

        $_SESSION["pr_login"] = $return["login"];
        $_SESSION["pr_password"] = $pr_password;
        $_SESSION["rights"] = $rights;
        $_SESSION["is_admin"] = 1;
        $_SESSION["user_type"] = "ADMIN";
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
    $row = $DBHandle -> GetRow($QUERY, [$user]);

    if ($row) {
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

$ACXACCESS 				= $_SESSION["rights"] > 0;
$ACXDASHBOARD			= has_rights(Admin::ACX_DASHBOARD);
$ACXCUSTOMER 			= has_rights(Admin::ACX_CUSTOMER);
$ACXBILLING 			= has_rights(Admin::ACX_BILLING);
$ACXRATECARD 			= has_rights(Admin::ACX_RATECARD);
$ACXTRUNK				= has_rights(Admin::ACX_TRUNK);
$ACXDID					= has_rights(Admin::ACX_DID);
$ACXCALLREPORT			= has_rights(Admin::ACX_CALL_REPORT);
$ACXCRONTSERVICE		= has_rights(Admin::ACX_CRONT_SERVICE);
$ACXMAIL 				= has_rights(Admin::ACX_MAIL);
$ACXADMINISTRATOR 		= has_rights(Admin::ACX_ADMINISTRATOR);
$ACXMAINTENANCE 		= has_rights(Admin::ACX_MAINTENANCE);
$ACXCALLBACK			= has_rights(Admin::ACX_CALLBACK);
$ACXOUTBOUNDCID 		= has_rights(Admin::ACX_OUTBOUNDCID);
$ACXPACKAGEOFFER 		= has_rights(Admin::ACX_PACKAGEOFFER);
$ACXPREDICTIVEDIALER 	= has_rights(Admin::ACX_PREDICTIVE_DIALER);
$ACXINVOICING 			= has_rights(Admin::ACX_INVOICING);
$ACXSUPPORT 			= has_rights(Admin::ACX_SUPPORT);
$ACXSETTING 			= has_rights(Admin::ACX_ACXSETTING);
$ACXMODIFY_REFILLS 		= has_rights(Admin::ACX_MODIFY_REFILLS);
$ACXMODIFY_PAYMENTS 	= has_rights(Admin::ACX_MODIFY_PAYMENTS);
$ACXMODIFY_CUSTOMERS 	= has_rights(Admin::ACX_MODIFY_CUSTOMERS);
$ACXDELETE_NOTIFICATIONS= has_rights(Admin::ACX_DELETE_NOTIFICATIONS);
$ACXDELETE_CDR			= has_rights(Admin::ACX_DELETE_CDR);
