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
 * @copyright   Copyright (C) 2004-2012 - Star2billing S.L.
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

Admin::checkPageAccess(Admin::ACX_ADMINISTRATOR);

$HD_Form = new FormHandler(
    "cc_status_log",
    _("Status Log"),
    "cc_status_log.id",
    ["cc_card" => ["cc_card.id", "cc_status_log.id_cc_card"]]
);

$HD_Form->list_query_order_columns = ["updated_date"];
$HD_Form->list_query_order_direction = "DESC";

$HD_Form->help_text = create_help(
    _("Status logs help you to keep track of the status of all customers. The status can be 'New, Active, Cancelled, Reserved, Waiting-MailConfirmation and Expired.")
);

$cardstatus_list = getCardStatus_List();

$HD_Form->AddListValue(_("Account"), "id_cc_card", [Customer::class, "getUsername"]);
$HD_Form->AddListValue(_("Last name"), "lastname");
$HD_Form->AddListMapping(_("Status"), "cc_status_log.status", $cardstatus_list);
$HD_Form->AddListValue(_("Date"), "updated_date");

$HD_Form->CV_NO_FIELDS  = _("THERE IS NO STATUS LOG AVAILABLE!");
$HD_Form->FG_INTRO_TEXT_EDITION = "";
$HD_Form->FG_ENABLE_EDIT_BUTTON = false;
$HD_Form->FG_ENABLE_DELETE_BUTTON = false;
$HD_Form->FG_ENABLE_ADD_BUTTON = false;

$HD_Form->FG_FILTER_ENABLE = true;
$HD_Form->FG_FILTER_COLUMN = "username";
$HD_Form->FG_FILTER_LABEL = _("Account number");

$HD_Form->search_form_enabled = true;
$HD_Form->search_delete_enabled = false;
$HD_Form->AddSearchPopupInput(_("Account"), "id_cc_card", "A2B_entity_card.php");
$HD_Form->AddSearchDateInput(_("Dates"), "updated_date");
$HD_Form->AddSearchSelectInput(
    _("Status"),
    "cc_status_log.status",
    array_map("array_reverse", $cardstatus_list)
);
