<?php

use A2billing\Logger;

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of A2Billing (http://www.a2billing.net/)
 *
 * A2Billing, Commercial Open Source Telecom Billing platform,
 * powered by Star2billing S.L. <http://www.star2billing.com/>
 *
 * @copyright   Copyright (C) 2004-2015 - Star2billing S.L.
 * @author      Belaid Arezqui <areski@gmail.com>
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
require_once __DIR__ . "/common.defines.php";
require_once __DIR__ . "/agent.module.access.php";
require_once __DIR__ . "/agent.help.php";
require_once __DIR__ . "/agent.smarty.php";

session_name("UIAGENTSESSION");
session_start();

const BINDTEXTDOMAIN = __DIR__ . '/../common/agent_ui_locale';
SetLocalLanguage();

//Enable Disable, list of values on page A2B_entity_config.php?form_action=ask-edit&id=1
const LIST_OF_VALUES = true;

//Enable Disable Captcha
define ("CAPTCHA_ENABLE", $A2B->config["signup"]['enable_captcha'] ?? 0);

// COPYRIGHT
define ("LCMODAL", check_cp());

//Images Path
define ("Images_Path", "../Public/templates/$_SESSION[stylefile]/images");
define ("Images_Path_Main", "../Public/templates/$_SESSION[stylefile]/images");
define ("KICON_PATH", "../Public/templates/$_SESSION[stylefile]/images/kicons");

if (!str_contains($_SERVER['REQUEST_URI'], "Public/index.php") && !empty($_SESSION["agent_id"])) {
    (new Logger())->insertLogAgent($_SESSION["agent_id"], 1, "Page Visit", "Agent Visited the Page", '', $_SERVER['REMOTE_ADDR'], $_SERVER['REQUEST_URI']);
}
