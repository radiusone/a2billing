<?php

use A2billing\NotificationsDAO;

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
 * @var bool $ACXSETTING Following from admin.module.access.php
 * @var bool $ACXINVOICING
 * @var bool $ACXPACKAGEOFFER
 * @var bool $ACXOUTBOUNDCID
 * @var bool $ACXCALLBACK
 * @var bool $ACXSUPPORT
 * @var bool $ACXMAINTENANCE
 * @var bool $ACXADMINISTRATOR
 * @var bool $ACXCRONTSERVICE
 * @var bool $ACXCALLREPORT
 * @var bool $ACXMAIL
 * @var bool $ACXDID
 * @var bool $ACXTRUNK
 * @var bool $ACXRATECARD
 * @var bool $ACXBILLING
 * @var bool $ACXCUSTOMER
 */
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));

const SMARTY_DIR = __DIR__ . '/../../vendor/smarty/smarty/libs/';
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
// if it is a pop window
if (!is_numeric($popup_select)) {
    $popup_select=0;
}
$smarty->assign("menu_section", $menu_section);
$smarty->assign("popupwindow", $popup_select > 0);

$smarty->assign("ACXCUSTOMER", $ACXCUSTOMER);
$smarty->assign("ACXBILLING", $ACXBILLING);
$smarty->assign("ACXRATECARD", $ACXRATECARD);
$smarty->assign("ACXTRUNK", $ACXTRUNK);
$smarty->assign("ACXDID", $ACXDID);
$smarty->assign("ACXMAIL", $ACXMAIL);
$smarty->assign("ACXCALLREPORT", $ACXCALLREPORT);
$smarty->assign("ACXCRONTSERVICE", $ACXCRONTSERVICE);
$smarty->assign("ACXADMINISTRATOR", $ACXADMINISTRATOR);
$smarty->assign("ACXMAINTENANCE", $ACXMAINTENANCE);
$smarty->assign("ACXSUPPORT", $ACXSUPPORT);
$smarty->assign("ACXCALLBACK", $ACXCALLBACK);
$smarty->assign("ACXOUTBOUNDCID", $ACXOUTBOUNDCID);
$smarty->assign("ACXPACKAGEOFFER", $ACXPACKAGEOFFER);
$smarty->assign("ACXINVOICING", $ACXINVOICING);
$smarty->assign("ACXSETTING", $ACXSETTING);
if(isset($_SESSION["admin_id"])) {
    $smarty->assign("NEW_NOTIFICATION", NotificationsDAO::IfNewNotification($_SESSION["admin_id"]));
} else {
    $smarty->assign("NEW_NOTIFICATION", null);
}

$smarty->assign("HTTP_HOST", $_SERVER['HTTP_HOST']);
$smarty->assign("ASTERISK_GUI_LINK", ASTERISK_GUI_LINK);

$smarty->disableSecurity();
