<?php

use A2billing\A2Billing;
use A2billing\Agent;
use Profiler_Profiler as Profiler;
use A2billing\Query_trace;

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

const TEMPLATE_DIR = __DIR__ . '/../../agent/Public/templates/';
const TEMPLATE_C_DIR = __DIR__ . '/../../agent/templates_c/';
require_once __DIR__ . "/../../vendor/autoload.php";

$smarty = new Smarty();

$skin_name = $_SESSION["stylefile"];

$smarty->setTemplateDir(TEMPLATE_DIR . $skin_name.'/');
$smarty->setCompileDir(TEMPLATE_C_DIR);
$smarty->setPluginsDir("./plugins/");

$smarty->assign("COPYRIGHT", COPYRIGHT);
$smarty->assign("CCMAINTITLE", CCMAINTITLE);

$smarty->assign("SKIN_NAME", $skin_name);
// if it is a pop window
/** @var int $popup_select from common.defines.php */
if (!is_numeric($popup_select)) {
    $popup_select=0;
}
$smarty->assign("popupwindow", $popup_select);

// for menu
$smarty->assign("ACXCUSTOMER", has_rights(Agent::ACX_CUSTOMER));
$smarty->assign("ACXBILLING", has_rights(Agent::ACX_BILLING));
$smarty->assign("ACXRATECARD", has_rights(Agent::ACX_RATECARD));
$smarty->assign("ACXCALLREPORT", has_rights(Agent::ACX_CALL_REPORT));
$smarty->assign("ACXMYACCOUNT", has_rights(Agent::ACX_MYACCOUNT));
$smarty->assign("ACXSUPPORT", has_rights(Agent::ACX_SUPPORT));
$smarty->assign("ACXSIGNUP", has_rights(Agent::ACX_SIGNUP));
$smarty->assign("ACXVOIPCONF", has_rights(Agent::ACX_VOIPCONF));

getpost_ifset(['section']);

if (!empty($section)) {
    $_SESSION["menu_section"] = $section;
} else {
    $section = $_SESSION["menu_section"];
}
$smarty->assign("section", $section);

$smarty->assign("adminname", $_SESSION["pr_login"]);

/** @var A2Billing $A2B the A2Billing instance from common.defines.php */
$smarty->assign("A2Bconfig", $A2B->config);

/** @var ?Profiler $profiler from common.defines.php */
/** @var Query_trace $G_instance_Query_trace from common.defines.php */
try {
    $smarty->registerPlugin(
        'function',
        'show_profiler',
        function () use ($profiler, $G_instance_Query_trace) {
            if (!is_null($profiler)) {
                $profiler->display($G_instance_Query_trace);
            }
        }
    );
} catch (SmartyException $e) {
}
