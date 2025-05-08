<?php

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

use A2billing\Table;

$FG_DEBUG = 0;
error_reporting(E_ALL & ~E_NOTICE);

header("Expires: Sat, Jan 01 2000 01:01:01 GMT");

$C_RETURN_URL_DISTANT_LOGIN = 'index.php?';
if (defined("RETURN_URL_DISTANT_LOGIN") && !empty(RETURN_URL_DISTANT_LOGIN)) {
    $C_RETURN_URL_DISTANT_LOGIN = RETURN_URL_DISTANT_LOGIN . (str_contains(RETURN_URL_DISTANT_LOGIN, '?') ? "&" : "?");
}

getpost_ifset (['pr_login', 'pr_password']);
/**
 * @var string $pr_login
 * @var string $pr_password
 */

if (!isset($_SESSION['pr_login']) || !isset($_SESSION['pr_password']) || !isset($_SESSION['rights']) || ($_POST["done"] ?? "") === "submit_log") {

    if (($_POST["done"] ?? "") === "submit_log") {

        $return = login($pr_login, $pr_password);

        if (!is_array($return)) {
            sleep(2);
            header("HTTP/1.0 401 Unauthorized");
            header("Location: {$C_RETURN_URL_DISTANT_LOGIN}error=$return");
            die();
        }

        $pr_login = $return["username"];
        $_SESSION["pr_login"] = $pr_login;
        $_SESSION["pr_password"] = $pr_password;
        $_SESSION["rights"] = (int)$return["users_perms"] + 1;
        $_SESSION["user_type"] = "CUST";
        $_SESSION["card_id"] = $return["id"];
        $_SESSION["id_didgroup"] = $return["id_didgroup"];
        $_SESSION["tariff"] = $return["tariff"];
        $_SESSION["vat"] = $return["vat"];
        $_SESSION["gmtoffset"] = $return["gmtoffset"];
        $_SESSION["currency"] = $return["currency"];
        $_SESSION["voicemail"] = $return["voicemail_permitted"];
    } else {
        $_SESSION["rights"] = 0;
    }
}

/**
 * @param string|null $user
 * @param string|null $pass
 * @return bool|string[]
 */
function login(?string $user, ?string $pass)
{
    $user = trim($user);
    $pass = trim($pass);

    if (empty($user) || empty($pass)) {
        return false;
    }

    $DBHandle = DbConnect();
    $table = new Table(
        "cc_card",
        ["username", "credit", "status", "cc_card.id", "id_didgroup", "tariff", "vat", "gmtoffset", "voicemail_permitted", "voicemail_activated", "users_perms", "currency", "uipass"],
        [
            "cc_timezone" => ["id_timezone", "cc_timezone.id"],
            "cc_card_group" => ["id_group", "cc_card_group.id"]
        ]
    );
    $row = $table->getRow($DBHandle, [["SUB", ["email" => $user, "useralias" => $user], "OR"]]);

    if ($row) {
        if ($row["status"] !== "t" && $row["status"] != 1  && $row["status"] != 8) {
            return false;
        }
        $filterpass = filter_var($pass, FILTER_SANITIZE_STRING);
        // lol wtf is security
        if ($row["uipass"] === $filterpass) {
            return $row;
        }
    }

    return false;
}
