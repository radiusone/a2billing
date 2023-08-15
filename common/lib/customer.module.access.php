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

$FG_DEBUG = 0;
error_reporting(E_ALL & ~E_NOTICE);

header("Expires: Sat, Jan 01 2000 01:01:01 GMT");

$C_RETURN_URL_DISTANT_LOGIN = 'index.php?';
if (defined("RETURN_URL_DISTANT_LOGIN") && !empty(RETURN_URL_DISTANT_LOGIN)) {
    $C_RETURN_URL_DISTANT_LOGIN = RETURN_URL_DISTANT_LOGIN . (str_contains(RETURN_URL_DISTANT_LOGIN, '?') ? "&" : "?");
}

if (($_GET["logout"] ?? "") === "true") {
    $C_RETURN_URL_DISTANT_LOGIN .=  "cssname=" . $_SESSION['stylefile'] ?? "";
    session_destroy();
    header("HTTP/1.0 401 Unauthorized");
    header("Location: $C_RETURN_URL_DISTANT_LOGIN");
    die();
}

getpost_ifset (['pr_login', 'pr_password']);
/**
 * @var string $pr_login
 * @var string $pr_password
 */

if (!isset($_SESSION['pr_login']) || !isset($_SESSION['pr_password']) || !isset($_SESSION['cus_rights']) || ($_POST["done"] ?? "") === "submit_log") {

    if (($_POST["done"] ?? "") === "submit_log") {

        $return = login($pr_login, $pr_password);

        if (!is_array($return)) {
            sleep(2);
            header("HTTP/1.0 401 Unauthorized");
            header("Location: ${C_RETURN_URL_DISTANT_LOGIN}error=$return");
            die();
        }

        $pr_login = $return[0];
        $_SESSION["pr_login"] = $pr_login;
        $_SESSION["pr_password"] = $pr_password;
        $_SESSION["cus_rights"] = empty($return[10]) ? 1 : (int)$return[10] + 1;
        $_SESSION["user_type"] = "CUST";
        $_SESSION["card_id"] = $return[3];
        $_SESSION["id_didgroup"] = $return[4];
        $_SESSION["tariff"] = $return[5];
        $_SESSION["vat"] = $return[6];
        $_SESSION["gmtoffset"] = $return[7];
        $_SESSION["currency"] = $return["currency"];
        $_SESSION["voicemail"] = $return[8];
    } else {
        $_SESSION["cus_rights"] = 0;
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

    $QUERY = "SELECT cc.username, cc.credit, cc.status, cc.id, cc.id_didgroup, cc.tariff, cc.vat, ct.gmtoffset, cc.voicemail_permitted, " .
             "cc.voicemail_activated, cc_card_group.users_perms, cc.currency, cc.uipass " .
             "FROM cc_card cc LEFT JOIN cc_timezone AS ct ON ct.id = cc.id_timezone LEFT JOIN cc_card_group ON cc_card_group.id=cc.id_group " .
             "WHERE cc.email = ? OR cc.useralias = ?";

    $DBHandle = DbConnect();
    $row = $DBHandle->GetRow($QUERY, [$user, $user]);

    if ($row) {
        if ($row["status"] !== "t" && $row["status"] !== "1"  && $row["status"] !== "8") {
            return false;
        }
        if (password_verify($pass, $row["uipass"])) {
            return $row;
        }
        // fallback to legacy authentication
        $pass = filter_var($pass, FILTER_SANITIZE_STRING);
        if (hash('whirlpool', $pass) === $row["uipass"]) {
            return $row;
        }
    }

    return false;
}
