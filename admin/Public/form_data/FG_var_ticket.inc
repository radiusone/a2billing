<?php

use A2billing\Admin;
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

getpost_ifset(['id', 'title', 'id_component','description','priority','status','creator','username','viewed_admin']);
/**
 * @var string $id
 * @var string $title
 * @var string $id_component
 * @var string $description
 * @var string $priority
 * @var string $status
 * @var string $creator
 * @var string $username
 * @var string $viewed_admin
 */
$form_action ??= "list";

$HD_Form = new FormHandler("cc_ticket", _("Ticket"), "cc_ticket.id");
if ($form_action === "list") {
    $virtual = <<< SQL
        SELECT aa.id, CASE WHEN MAX(aa.viewed_admin) = 1 OR SUM(bb.viewed_admin) > 0 THEN 1 ELSE 0 END AS viewed
        FROM cc_ticket aa
        LEFT JOIN cc_ticket_comment bb ON aa.id = bb.id_ticket
        WHERE aa.creator_type = 0
        GROUP BY aa.id
        SQL;
    $HD_Form->query_table_joins = [
        "($virtual) AS v" => ["cc_ticket.id", "v.id"],
        "cc_card" => ["cc_card.id", "cc_ticket.creator"]
    ];
}

$HD_Form->FG_TABLE_DEFAULT_ORDER = "cc_ticket.id";
$HD_Form->FG_TABLE_DEFAULT_SENS = "DESC";
$HD_Form->list_query_conditions["cc_ticket.creator_type"] = 0;

$HD_Form->FG_FK_DELETE_ALLOWED = true;
$HD_Form->FG_FK_DELETE_CONFIRM = true;
$HD_Form->FG_FK_WARNONLY = true;
$HD_Form->FG_FK_TABLENAMES = ["cc_ticket_comment"];
$HD_Form->FG_FK_EDITION_CLAUSE = ["id_ticket"];

$HD_Form ->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form ->FG_LIST_ADDING_BUTTON_LINK1 = "CC_ticket.php?form_action=ask-add";
$HD_Form ->FG_LIST_ADDING_BUTTON_ALT1 = $HD_Form ->FG_LIST_ADDING_BUTTON_MSG1 = gettext("Add TICKET");
$HD_Form ->FG_LIST_ADDING_BUTTON_IMG1 = get_image_path("note_add.png") ;

$priority_list = Ticket::getAllPriorityListView();
$viewed_list = getTicketViewedList();
$status_list = Ticket::getAllStatusListView();

$HD_Form->AddListValue(_("ID"), "id");
$HD_Form->AddListSqlMapping(_("Customer"), "creator", new Table("cc_card", ["id", "CONCAT(firstname, ' ', lastname)"]));
$HD_Form->AddListValue(_("Account number"), "username", "display_customer_link");
$HD_Form->AddListValue(_("Title"), "title");
$HD_Form->AddListValue(_("Creation date"), "creationdate");
$HD_Form->AddListSqlMapping(_("Component"), "id_component", new Table("cc_support_component", ["id", "name"]));
$HD_Form->AddListMapping(_("Priority"), "priority", $priority_list);
$HD_Form->AddListMapping(_("Viewed"), "viewed", $viewed_list, false);
$HD_Form->AddListMapping(_("Status"), "status", $status_list);
$HD_Form->FieldViewElement([
    "cc_ticket.id",
    "cc_ticket.creator",
    "cc_card.username",
    "cc_ticket.title",
    "cc_ticket.creationdate",
    "cc_ticket.id_component",
    "cc_ticket.priority",
    "v.viewed",
    "cc_ticket.status",
]);

$HD_Form->search_form_enabled = !$popup_select;
$HD_Form->search_delete_enabled =false;
$HD_Form->search_session_key = 'ticket_cust_selection';
$HD_Form->search_form_title = gettext('Define specific criteria to search for ticket created.');

$HD_Form->AddSearchDateInput(_("DATE"), "cc_ticket.creationdate");
$HD_Form->AddSearchTextInput(gettext("ACCOUNT NUMBER"), 'username','usernametype');

$HD_Form->FG_ENABLE_EDIT_BUTTON = true;
$HD_Form->FG_ENABLE_DELETE_BUTTON = true;
$HD_Form->FG_ENABLE_ADD_BUTTON = true;
$HD_Form->FG_ENABLE_INFO_BUTTON = true;
$HD_Form->FG_INFO_BUTTON_LINK = "CC_ticket_view.php?id=";

$val_default = "";
if ($form_action === 'ask-add' && $username) {
	$DBHandle_max  = DbConnect();
    $resmax = $DBHandle_max -> Execute("SELECT id FROM cc_card where username='$username'");
    if ($resmax && $row = $resmax->FetchRow()) {
        $val_default = "value='$row[0]'";
    }
}

$HD_Form->AddEditPopup(
    gettext("CREATOR"),
    "creator",
    "A2B_entity_card.php?popup_select=1&",
    gettext("Select the Customer ID"),
    $val_default,
    [Validator::class, "numeric"], gettext("Insert the Customer ID")
);

$HD_Form->AddEditElement(
    gettext("TITLE"),
    "title",
    "",
    "maxlength=50",
    9,
    gettext("Insert the Title of ticket ")
);

$HD_Form->AddEditSqlSelect(gettext("COMPONENT"), "id_component", "cc_support_component", "name,id");

$HD_Form->AddEditSelect(gettext("PRIORITY"), "priority", $priority_list);

$HD_Form->AddEditTextarea(
    gettext("DESCRIPTION"),
    "description",
    "",
    "rows='4'", null, gettext("Insert the description")
);

// Set the filter variables
$HD_Form->FG_FILTER_ENABLE = true;
$HD_Form->FG_FILTER_COLUMN = 'username';
$HD_Form->FG_FILTER_LABEL = gettext('ACCOUNT NUMBER');

$HD_Form->FG_ADD_QUERY_HIDDEN_INPUTS["viewed_admin"] = 0;
$HD_Form->FG_ADDITIONAL_FUNCTION_AFTER_ADD = 'ticket_add';
$HD_Form->FG_INTRO_TEXT_EDITION= '';
$HD_Form->FG_INTRO_TEXT_ADITION = '';
$HD_Form->FG_LOCATION_AFTER_ADD = "?id=";
$HD_Form->FG_LOCATION_AFTER_EDIT = "?id=";
$HD_Form->FG_LOCATION_AFTER_DELETE = "?id=";
