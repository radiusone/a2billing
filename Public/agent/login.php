<?php

use A2billing\Agent;
use A2billing\Logger;
use A2billing\Table;

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

require_once __DIR__ . "/../../common/lib/agent.defines.php";

getpost_ifset (["pr_login", "pr_password"]);
/**
 * @var string|null $pr_login
 * @var string|null $pr_password
 */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
}

$return = Agent::checkLogin($pr_login, $pr_password);

if (!$return) {
    header("HTTP/1.0 401 Unauthorized");
    header("Location: index.php?error=1");
    die();
}

$_SESSION["agent_id"] = (int)$return["id"];
$_SESSION["rights"] = (int)$return["perms"];
$_SESSION["user_type"] = "AGENT";
$_SESSION["currency"] = $return["currency"];
$_SESSION["vat"] = $return["vat"];
Logger::insertLog(
    (int)$return["id"],
    1,
    "Agent Logged In",
    "Agent Logged in to website",
    "",
    $_SERVER["REMOTE_ADDR"],
    "PP_Intro.php",
    "",
    [],
    true
);

header("Location: PP_intro.php");
