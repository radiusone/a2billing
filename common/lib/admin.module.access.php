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

use A2billing\Admin;
use A2billing\Logger;

$FG_DEBUG = 0;
error_reporting(E_ALL & ~E_NOTICE);

getpost_ifset (["pr_login", "pr_password"]);
/**
 * @var string $pr_login
 * @var string $pr_password
 */

if (!isset($_SESSION['pr_login']) || !isset($_SESSION['rights']) || ($_POST["done"] ?? "")  === "submit_log") {
    if (($_POST["done"] ?? "") === "submit_log") {

        $return = Admin::checkLogin($pr_login, $pr_password);

        if (!$return || (int)$return["perms"] === Admin::ACX_NOACCESS || $return["groupid"] > 1 ) {
            header ("HTTP/1.0 401 Unauthorized");
            header ("Location: index.php?error=1");
            die();
        }

        $admin_id = (int)$return["userid"];
        $groupid = (int)$return["groupid"];

        $_SESSION["pr_login"] = $return["login"];
        $_SESSION["rights"] = $groupid ? (int)$return["perms"] : Admin::ACX_ALL_RIGHTS;
        $_SESSION["user_type"] = "ADMIN";
        $_SESSION["admin_id"] = $admin_id;
        Logger::insertLog(
            $admin_id,
            1,
            "User Logged In",
            "User Logged in to website",
            '',
            $_SERVER['REMOTE_ADDR'],
            'PP_Intro.php'
        );
    } else {
        $_SESSION["rights"] = 0;
    }
}
