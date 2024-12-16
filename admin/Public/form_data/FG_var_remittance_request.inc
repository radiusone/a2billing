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
    "cc_remittance_request",
    _("Remittance Request Agent"),
    "cc_remittance_request.id",
    ["cc_agent" => ["cc_remittance_request.id_agent", "cc_agent.id"]]
);

$HD_Form->FG_QUERY_ORDERBY_COLUMNS = ["date"];
$HD_Form->FG_TABLE_DEFAULT_SENS = "DESC";
$HD_Form->FG_ENABLE_INFO_BUTTON = true;
$HD_Form->FG_INFO_BUTTON_LINK = "A2B_info_remittance.php?id=";

$HD_Form->AddListActionButton(
    "",
    _("ACCEPT THIS REQUEST"),
    get_image_path("accept.png"),
    "accept_click",
    "status",
    0 // waiting
);
$HD_Form->AddListActionButton(
    "",
    _("REFUSE THIS REQUEST"),
    get_image_path("cancel.png"),
    "refuse_click",
    "status",
    0 // waiting
);

$HD_Form->help_text = create_help(
    _("Agents Remittance request history - The section below allows you to confirm or refuse remittance request of an agent. The remittance reques are generated automatically by the agent."),
    'ViewRemittance'
);

$status_list = getRemittanceStatus_List();
$type_list = getRemittanceType_List();

$HD_Form->AddListValue(_("ID"), "cc_remittance_request.id");
$HD_Form->AddListValue(_("Agent"), "id_agent", "display_linktoagent");
$HD_Form->AddListValue(_("Date"), "date");
$HD_Form->AddListValue(_("Amount"), "amount", "display_money");
$HD_Form->AddListMapping(_("Type"), "type", $type_list);
$HD_Form->AddListMapping(_("Status"), "status", $status_list);
$HD_Form->FieldViewElement (["cc_remittance_request.id", "id_agent", "date", "amount", "type", "status"]);

$HD_Form->search_form_enabled = true;
$HD_Form->search_session_key = 'remittance_agent_selection';
$HD_Form->search_form_title = _('Define specific criteria to search for remittances requests created.');
$HD_Form->search_delete_enabled = false;

$HD_Form->AddSearchDateInput(_("Date"), "date");
$HD_Form->AddSearchTextInput(_("Login"), 'login');
$HD_Form->AddSearchTextInput(_("Last name"),'lastname');
$HD_Form->AddSearchTextInput(_("First name"),'firstname');
$HD_Form->AddSearchSelectInput(_("Request type"), "type", $type_list);
$HD_Form->AddSearchSelectInput(_("Request status"), "status", $status_list);

$HD_Form->CV_NO_FIELDS  = _("THERE ARE NO REMITTANCE REQUEST CREATED!");
