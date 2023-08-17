<?php

use A2billing\Admin;
use A2billing\NotificationsDAO;
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

/**
 * @var string $popup_select From common.defines.php
 * @var string $menu_section From the individual page
 */
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));

const TEMPLATE_DIR = __DIR__ . '/../../admin/Public/templates/';
const TEMPLATE_C_DIR = __DIR__ . '/../../admin/templates_c/';
require_once __DIR__ . "/../../vendor/autoload.php";

$smarty = new Smarty();

$skin_name = $_SESSION["stylefile"];

$smarty->setTemplateDir(TEMPLATE_DIR . $skin_name.'/');
$smarty->setCompileDir(TEMPLATE_C_DIR);
$smarty->setPluginsDir("./plugins/");

$smarty->assign("COPYRIGHT", COPYRIGHT);
$smarty->assign("CCMAINTITLE", CCMAINTITLE);

$smarty->assign("SKIN_NAME", $skin_name);

$smarty->assign("menu_section", $menu_section);
$smarty->assign("popupwindow", ($popup_select ?? 0) > 0);

$smarty->assign("ACXCUSTOMER", has_rights(Admin::ACX_CUSTOMER));
$smarty->assign("ACXBILLING", has_rights(Admin::ACX_BILLING));
$smarty->assign("ACXRATECARD", has_rights(Admin::ACX_RATECARD));
$smarty->assign("ACXTRUNK", has_rights(Admin::ACX_TRUNK));
$smarty->assign("ACXDID", has_rights(Admin::ACX_DID));
$smarty->assign("ACXMAIL", has_rights(Admin::ACX_MAIL));
$smarty->assign("ACXCALLREPORT", has_rights(Admin::ACX_CALL_REPORT));
$smarty->assign("ACXCRONTSERVICE", has_rights(Admin::ACX_CRONT_SERVICE));
$smarty->assign("ACXADMINISTRATOR", has_rights(Admin::ACX_ADMINISTRATOR));
$smarty->assign("ACXMAINTENANCE", has_rights(Admin::ACX_MAINTENANCE));
$smarty->assign("ACXSUPPORT", has_rights(Admin::ACX_SUPPORT));
$smarty->assign("ACXCALLBACK", has_rights(Admin::ACX_CALLBACK));
$smarty->assign("ACXOUTBOUNDCID", has_rights(Admin::ACX_OUTBOUNDCID));
$smarty->assign("ACXPACKAGEOFFER", has_rights(Admin::ACX_PACKAGEOFFER));
$smarty->assign("ACXINVOICING", has_rights(Admin::ACX_INVOICING));
$smarty->assign("ACXSETTING", has_rights(Admin::ACX_ACXSETTING));
if(isset($_SESSION["admin_id"])) {
    $smarty->assign("NEW_NOTIFICATION", NotificationsDAO::IfNewNotification($_SESSION["admin_id"]));
} else {
    $smarty->assign("NEW_NOTIFICATION");
}

$smarty->assign("HTTP_HOST", $_SERVER['HTTP_HOST']);
$smarty->assign("ASTERISK_GUI_LINK", ASTERISK_GUI_LINK);

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
$smarty->disableSecurity();
