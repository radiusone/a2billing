<?php

use A2billing\Admin;
use A2billing\Customer;
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

$menu_section = 1;
require_once __DIR__ . "/../../common/lib/admin.defines.php";

Admin::checkPageAccess(Admin::ACX_CUSTOMER);

$HD_Form = new FormHandler(
    "cc_card_history",
    _("Card History"),
    "cc_card_history.id",
    ["cc_card" => ["cc_card_history.id_cc_card", "cc_card.id"]]
);
$HD_Form->init();

$DBHandle = DbConnect();
$HD_Form->AddListValue(_("Account number"), "id_cc_card", [Customer::class, "getUsername"]);
$HD_Form->AddListValue(_("Date"), "cc_card_history.datecreated");
$HD_Form->AddListValue(_("Description"), "cc_card_history.description");

$HD_Form->list_query_order_columns = ["cc_card_history.datecreated"];
$HD_Form->list_query_order_direction = "DESC";

$HD_Form->list_query_columns = ["cc_card_history.id_cc_card", "cc_card_history.datecreated", "cc_card_history.description"];
$HD_Form->FG_LIST_VIEW_PAGE_SIZE = 25;

$HD_Form->search_form_enabled = true;
$HD_Form->search_session_key = 'card_history_selection';
$HD_Form->search_form_title = gettext('Define specific criteria to search for card history');

$HD_Form->AddSearchPopupInput(_("Enter the customer ID"), "cc_card_history.id_cc_card", "A2B_entity_card.php");
$HD_Form->AddSearchDateInput(_("Date"), "cc_card_history.datecreated");
$form_action ??= "list";
$HD_Form->prepare_list_subselection('list');
if (empty($HD_Form->list_query_conditions)) {
    $date = (new DateTime("-1 month"))->format("Y-m-d H:i:s");
    $HD_Form->list_query_conditions["cc_card_history.datecreated"] = [">=", $date];
}

$list = $HD_Form->perform_action($form_action);

require_once __DIR__ . "/../templates/main.php";

$HD_Form->create_search_form();

$HD_Form->create_toppage($form_action);
$HD_Form->create_form("list", $list);

require_once __DIR__ . "/../templates/footer.php";

