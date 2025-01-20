<?php

use A2billing\Admin;
use A2billing\Agent;
use A2billing\Customer;
use A2billing\Forms\FormBO;
use A2billing\Forms\FormHandler;
use A2billing\Forms\Validator;
use A2billing\Table;
use A2billing\Ticket;

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

/**
 * @var string $popup_select
 * @var string $form_action
 */
Admin::checkPageAccess(Admin::ACX_ADMINISTRATOR);

getpost_ifset(["username", "ticket_type"]);
/**
 * @var string|null $username
 * @var string|null $ticket_type
 */

$ticket_type ??= "user";
$form_action ??= "list";

$HD_Form = new FormHandler("cc_ticket", _("Ticket"), "cc_ticket.id");
if ($form_action === "list") {
    $virtual = <<< SQL
        SELECT aa.id, CASE WHEN MAX(aa.viewed_admin) = 1 OR SUM(bb.viewed_admin) > 0 THEN 1 ELSE 0 END AS viewed
        FROM cc_ticket aa
        LEFT JOIN cc_ticket_comment bb ON aa.id = bb.id_ticket
        GROUP BY aa.id
        SQL;
    $HD_Form->query_table_joins = [
        "($virtual) AS v" => ["cc_ticket.id", "v.id"],
    ];
    if ($ticket_type === "agent") {
        $HD_Form->query_table_joins["cc_agent"] = ["cc_agent.id", "creator"];
    } else {
        $HD_Form->query_table_joins["cc_card"] = ["cc_card.id", "creator"];
    }
}

$HD_Form->list_query_order_columns = ["cc_ticket.creationdate"];
$HD_Form->list_query_order_direction = "DESC";
$HD_Form->list_query_conditions["cc_ticket.creator_type"] = $ticket_type === "agent" ? 1 : 0;

$HD_Form->FG_FK_DELETE_ALLOWED = true;
$HD_Form->FG_FK_DELETE_CONFIRM = true;
$HD_Form->FG_FK_WARNONLY = true;
$HD_Form->FG_FK_TABLENAMES = ["cc_ticket_comment"];
$HD_Form->FG_FK_EDITION_CLAUSE = ["id_ticket"];

$HD_Form->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form->FG_LIST_ADDING_BUTTON_LINK1 = "?form_action=ask-add&ticket_type=$ticket_type";
$HD_Form->FG_LIST_ADDING_BUTTON_IMG1 = get_image_path("note_add.png");
$HD_Form->FG_LIST_ADDING_BUTTON2 = true;
$HD_Form->FG_LIST_ADDING_BUTTON_LINK2 = "?ticket_type=" . ($ticket_type === "agent" ? "user" : "agent");
$HD_Form->FG_LIST_ADDING_BUTTON_MSG2 = $ticket_type === "agent" ? _("Show user tickets") : _("Show agent tickets");

if ($ticket_type === "agent") {
    $HD_Form->list_help_text = create_help(_("You can see here, all tickets created by Agents. You can also add a new ticket for one Agent."));
} else {
    $HD_Form->list_help_text = create_help(_("You can see here, all tickets created. You can also add a new ticket for one customer."));
}

$priority_list = Ticket::getAllPriorityListView();
$viewed_list = getTicketViewedList();
$status_list = Ticket::getAllStatusListView();

$HD_Form->AddListValue(_("ID"), "cc_ticket.id");
$HD_Form->AddListSqlMapping(
    $ticket_type === "agent" ? _("Agent") : _("Customer"),
    "creator",
    new Table($ticket_type === "agent" ? "cc_agent" : "cc_card", ["id", "CONCAT(firstname, ' ', lastname)"])
);
if ($ticket_type === "agent") {
    $HD_Form->AddListValue(_("Login"), "creator", [Agent::class, "getName"]);
} else {
    $HD_Form->AddListValue(_("Account number"), "creator", [Customer::class, "getUsername"]);
}
$HD_Form->AddListValue(_("Title"), "title");
$HD_Form->AddListValue(_("Creation date"), "cc_ticket.creationdate");
$HD_Form->AddListSqlMapping(_("Component"), "id_component", new Table("cc_support_component", ["id", "name"]));
$HD_Form->AddListMapping(_("Priority"), "priority", $priority_list);
$HD_Form->AddListMapping(_("Viewed"), "v.viewed", $viewed_list, false);
$HD_Form->AddListMapping(_("Status"), "cc_ticket.status", $status_list);

$HD_Form->search_form_enabled = !$popup_select;
$HD_Form->search_delete_enabled = false;
$HD_Form->search_session_key = 'ticket_cust_selection';
$HD_Form->search_form_title = _('Define specific criteria to search for ticket created.');

$HD_Form->AddSearchDateInput(_("Date"), "cc_ticket.creationdate");
if ($ticket_type === "agent") {
    $HD_Form->AddSearchTextInput(_("Login"), "login");
} else {
    $HD_Form->AddSearchTextInput(_("Account number"), "username");
}
$HD_Form -> AddSearchTextInput(_("Last name"), "lastname");

$HD_Form->FG_ENABLE_EDIT_BUTTON = true;
$HD_Form->FG_ENABLE_DELETE_BUTTON = true;
$HD_Form->FG_ENABLE_ADD_BUTTON = true;
$HD_Form->FG_ENABLE_INFO_BUTTON = true;
$HD_Form->FG_INFO_BUTTON_LINK = "CC_ticket_view.php?id=";
$HD_Form -> FG_ADDITIONAL_FUNCTION_AFTER_ADD = [FormBO::class, "ticket_add"];

if ($ticket_type === "agent") {
    $HD_Form->AddEditPopup(
        _("CREATOR"),
        "creator",
        "A2B_entity_agent.php?popup_select=1&"
    );
    $HD_Form->FG_ADD_QUERY_HIDDEN_INPUTS["creator_type"] = 1;
} else {
    $creator_attr = [];
    if ($form_action === 'ask-add' && !empty($username)) {
        $row = (new Table("cc_card", "id"))
            ->getRow($HD_Form->DBHandle, ["username" => $username]);
        $creator_attr["value"] = $row["id"] ?? "";
    }
    $HD_Form->AddEditPopup(
        _("CREATOR"),
        "creator",
        "A2B_entity_card.php?popup_select=1&",
        _("Select the Customer ID"),
        $creator_attr,
        [Validator::class, "numeric"],
        _("Insert the Customer ID")
    );
    $HD_Form->FG_ADD_QUERY_HIDDEN_INPUTS["creator_type"] = 0;
}

$HD_Form->AddEditElement(
    _("TITLE"),
    "title",
    "",
    ["maxlength" => 50],
    [Validator::class, "min1Char"],
    _("Insert the Title of ticket ")
);

$HD_Form->AddEditSqlSelect(
    _("COMPONENT"),
    "id_component",
    new Table("cc_support_component", ["id", "name"])
);

$HD_Form->AddEditSelect(_("PRIORITY"), "priority", $priority_list);

$HD_Form->AddEditTextarea(
    _("DESCRIPTION"),
    "description",
    "",
    ["rows" => 4],
    _("Insert the description")
);

// Set the filter variables
$HD_Form->FG_FILTER_ENABLE = true;
if ($ticket_type === "agent") {
    $HD_Form->FG_FILTER_COLUMN = "login";
    $HD_Form->FG_FILTER_LABEL = _("Login");
} else {
    $HD_Form->FG_FILTER_COLUMN = "username";
    $HD_Form->FG_FILTER_LABEL = _("Account number");
}

$HD_Form->FG_ADD_QUERY_HIDDEN_INPUTS["viewed_admin"] = 0;
$HD_Form->FG_INTRO_TEXT_EDITION= "";
$HD_Form->FG_INTRO_TEXT_ADITION = "";
