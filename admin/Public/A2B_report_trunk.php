<?php

use A2billing\Admin;
use A2billing\Forms\FormHandler;

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

$HD_Form = new FormHandler(
    "cc_call",
    _("Trunk Report"),
    "cc_call.id",
    [
        "cc_call AS answered" => [["cc_call.id", "answered.id"], ["answered.terminatecauseid", 1]],
        "cc_call AS cic" =>  [["cc_call.id", "cic.id"], ["cic.real_sessiontime", "<", 10]]
    ]
);

$HD_Form->AddListValue(abbr(_("ASR"), _("Answer ratio")), "COUNT(answered.id) / COUNT(cc_call.id) AS asr");
$HD_Form->AddListValue(abbr(_("ALOC"), _("Average length of call")), "SUM(cc_call.real_sessiontime) / COUNT(cc_call.id) AS aloc");
$HD_Form->AddListValue(abbr(_("CIC"), _("???")), "COUNT(cic.id) AS cic");
$HD_Form->AddListValue(_("Total calls"), "COUNT(cc_call.id) AS total_calls");

$HD_Form->search_form_enabled = true;
$HD_Form->search_session_key = 'call_log_selection';
$HD_Form->search_form_title = gettext('Define specific criteria to search for call records');

$HD_Form->AddSearchDateInput(_("Date"), "cc_call.starttime");
$HD_Form->AddSearchDateInput(_("Date"), "cc_call.starttime", false, true);
$HD_Form->AddSearchPopupInput(_("Trunk"), "cc_call.id_trunk", "A2B_entity_trunk.php", 2);

$form_action = "list";
$HD_Form->prepare_list_subselection($form_action);
if (empty($HD_Form->list_query_conditions)) {
    $date = (new DateTime("-1 day"))->format("Y-m-d");
    $HD_Form->list_query_conditions["cc_call.starttime"] = [">=", $date];
}

$list = $HD_Form->perform_action($form_action);

require_once __DIR__ . "/../templates/main.php";

$HD_Form->create_search_form();

$HD_Form->create_toppage($form_action);
$HD_Form->create_form("list", $list);

require_once __DIR__ . "/../templates/footer.php";
