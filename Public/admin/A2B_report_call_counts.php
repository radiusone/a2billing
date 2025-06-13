<?php

use A2billing\Admin;
use A2billing\Customer;
use A2billing\Forms\FormHandler;
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

$menu_section = 5;
require_once __DIR__ . "/../../common/lib/admin.defines.php";

Admin::checkPageAccess(Admin::ACX_CALL_REPORT);

getpost_ifset(["displaytop", "groupbyday", "posted_search"]);
/**
 * @var string|null $displaytop
 * @var numeric-string|null $groupbyday
 * @var numeric-string|null $posted_search
 */
$displaytop ??= "card_id";
$groupbyday ??= null;
$posted_search ??= 0;

$HD_Form = new FormHandler("cc_call", _("Call Count Report"));

if ($displaytop === "card_id") {
    $HD_Form->list_query_group_columns = ["card_id"];
    $HD_Form->CV_TITLE_TEXT = $groupbyday ? _("Top users by day") : _("Top users");
} elseif ($displaytop === "destination") {
    $HD_Form->list_query_group_columns = ["destination"];
    $HD_Form->CV_TITLE_TEXT = $groupbyday ? _("Top destinations by day") : _("Top destinations");
} else {
    $displaytop = null;
}
if ($groupbyday) {
    $HD_Form->list_query_group_columns[] = "DATE(starttime)";
    $HD_Form->list_query_order_columns = ["DATE(starttime)"];
} else {
    $HD_Form->list_query_order_columns = ["COUNT(*)"];
}
$HD_Form->list_query_order_direction = "DESC";

if ($groupbyday) {
    $HD_Form->AddListValue(_("Date"), "DATE(starttime)");
}
if ($displaytop === "card_id") {
    $HD_Form->AddListValue(_("Account number"), "card_id", [Customer::class, "getUsername"]);
} else {
    $HD_Form->AddListSqlMapping(_("Destination"), "destination", new Table("cc_prefix", ["prefix", "destination"]));
}
$HD_Form->AddListValue(_("Duration"), "SUM(real_sessiontime)", "get_minute");
$HD_Form->AddListValue(_("Sell"), "SUM(sessionbill)", "get_money_precise");
$HD_Form->AddListValue(_("Buy"), "SUM(buycost)", "get_money_precise");
$HD_Form->AddListValue(_("Calls"), "COUNT(*)");
$HD_Form->AddListHiddenValue("DATE(starttime) AS day");

$HD_Form->search_form_enabled = true;
$HD_Form->AddSearchDateInput(_("Date"), "starttime");
$HD_Form->AddSearchRadioInput(
    _("Display"),
    "displaytop",
    ["card_id" => _("Top users"), "destination" => _("Top destinations")],
    false
);
$HD_Form->AddSearchRadioInput(
    _("Call Type"),
    "terminatecauseid",
    ["1" => _("Answered calls"), "" => _("All calls")]
);
$HD_Form->AddSearchRadioInput(
    _("Group by Date"),
    "groupbyday",
    getYesNoList(),
    false
);

require_once __DIR__ . "/templates/main.php";

$form_action = "list";
if ($posted_search) {
    $list = $HD_Form->perform_action($form_action);
}
$HD_Form->create_search_form();
$HD_Form->create_toppage($form_action);
if ($posted_search) {
    $HD_Form->create_form($form_action, $list);
}

require_once __DIR__ . "/templates/footer.php";
