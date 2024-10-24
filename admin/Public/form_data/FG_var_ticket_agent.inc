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

include (LIBDIR."/support/classes/ticket.php");

getpost_ifset(array('id', 'title', 'id_component','description','priority','status','creator','username','viewed_admin' ));


if (!isset($form_action) || $form_action=="list"){
	$HD_Form = new FormHandler("cc_ticket LEFT JOIN
	( SELECT aa.id, CASE WHEN MAX(aa.viewed_admin)=1 OR SUM(bb.viewed_admin)>0 THEN 1 ELSE 0 END as viewed FROM cc_ticket aa LEFT JOIN cc_ticket_comment bb ON aa.id = bb.id_ticket	WHERE  aa.creator_type = 0 GROUP BY aa.id ) AS v ON cc_ticket.id = v.id
	LEFT JOIN cc_agent ON cc_agent.id = cc_ticket.creator", _("Ticket"), "cc_ticket.id");
}else {
	$HD_Form = new FormHandler("cc_ticket", _("Ticket"));
}


$HD_Form->no_debug();
$HD_Form -> FG_TABLE_DEFAULT_ORDER = " cc_ticket.id";
$HD_Form -> FG_TABLE_DEFAULT_SENS = "DESC";
$HD_Form -> FG_QUERY_WHERE_CLAUSE = " cc_ticket.creator_type = 1 ";
$HD_Form->list_query_conditions["cc_ticket.creator_type"] = 0;

$HD_Form ->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form ->FG_LIST_ADDING_BUTTON_LINK1 = "A2B_ticket_agent.php?form_action=ask-add";
$HD_Form ->FG_LIST_ADDING_BUTTON_ALT1 = $HD_Form ->FG_LIST_ADDING_BUTTON_MSG1 = gettext("Add TICKET");
$HD_Form ->FG_LIST_ADDING_BUTTON_IMG1 = Images_Path ."/note_add.png" ;


$priority_list = getTicketPriorityList();
$viewed_list = getTicketViewedList();
$status_list = Ticket::getAllStatusListView();

$filter_view ='v.viewed ';

$HD_Form -> AddViewElement(gettext("ID"), "id");
$HD_Form -> AddViewElement(gettext("AGENT"), "creator", true, 25, "", "lie", "cc_agent", "lastname, firstname", "id='%id'", "%1 %2");
$HD_Form -> AddViewElement(gettext("LOGIN"), " cc_agent.login");
$HD_Form -> AddViewElement(gettext("TITLE"), "title");
$HD_Form -> AddViewElement(gettext("CREATIONDATE"), "creationdate", true, 19);
$HD_Form -> AddViewElement(gettext("COMPONENT"), "id_component", true, 15, "", "lie", "cc_support_component", "name", "id='%id'", "%1");
$HD_Form -> AddViewElement(gettext("PRIORITY"), "priority", true, 0, "", "list", $priority_list);
$HD_Form -> AddViewElement(gettext("VIEWED"), "viewed_admin", false, 0, "", "list", $viewed_list);
$HD_Form -> AddViewElement(gettext("STATUS"), "status", true, 0, "", "list", $status_list);

$HD_Form -> FieldViewElement ("cc_ticket.id, cc_ticket.creator, cc_agent.login, title, cc_ticket.creationdate, id_component , priority , $filter_view , cc_ticket.status");

$HD_Form -> FG_OTHER_BUTTON1_LINK = "CC_ticket_view.php?id=";
$HD_Form -> FG_OTHER_BUTTON1_IMG = Images_Path."/icon-viewdetails.gif";
$HD_Form -> FG_OTHER_BUTTON1_ALT = gettext("VIEW DETAILS");

if (!($popup_select>=1))	 $HD_Form -> search_form_enabled = true;
$HD_Form -> search_delete_enabled =false;
$HD_Form -> search_session_key = 'ticket_agent_selection';
$HD_Form -> search_form_title = gettext('Define specific criteria to search for ticket created.');
$HD_Form -> search_date_enabled = true;
$HD_Form -> search_date_text = gettext("DATE");
$HD_Form -> search_date_column = 'cc_ticket.creationdate';

$HD_Form -> AddSearchTextInput(gettext("LOGIN"), 'login','logintype');
$HD_Form -> AddSearchTextInput(gettext("LASTNAME"),'lastname','lastnametype');
$HD_Form -> AddSearchTextInput(gettext("FIRSTNAME"),'firstname','firstnametype');


$HD_Form -> FG_OTHER_BUTTON1 = true;
$HD_Form -> FG_ENABLE_EDIT_BUTTON = true;
$HD_Form -> FG_ENABLE_DELETE_BUTTON = true;
$HD_Form -> FG_ENABLE_ADD_BUTTON = true;

$HD_Form->AddEditSqlSelect(gettext("AGENT"), "creator", "cc_agent", "login,id,firstname,lastname", "", "", "", "%1 (%3, %4)", "");

$HD_Form->AddEditElement(
    gettext("TITLE"),
    "title",
    "",
    "size=60 maxlength=50",
    9,
    gettext("Insert the Title of ticket ")
);

$HD_Form->AddEditSqlSelect(gettext("COMPONENT"), "id_component", "cc_support_component", "name,id");

$HD_Form->AddEditSelect(gettext("PRIORITY"), "priority", $priority_list);

$HD_Form->AddEditTextarea(
    gettext("DESCRIPTION"),
    "description",
    "",
    "cols='50' rows='4'", null, gettext("Insert the description")
);

// Set the filter variables
$HD_Form -> FG_FILTER_ENABLE = true;
$HD_Form -> FG_FILTER_COLUMN = 'cc_agent.login';
$HD_Form -> FG_FILTER_LABEL = gettext('LOGIN');

$HD_Form->FG_ADD_QUERY_HIDDEN_INPUTS["viewed_admin"] = 0;
$HD_Form->FG_ADD_QUERY_HIDDEN_INPUTS["creator_type"] = 1;

$HD_Form -> FG_INTRO_TEXT_EDITION= '';
$HD_Form -> FG_ADDITIONAL_FUNCTION_AFTER_ADD = 'ticket_agent_add';
$HD_Form -> FG_INTRO_TEXT_ADITION = '';

$HD_Form -> FG_LOCATION_AFTER_ADD = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";
$HD_Form -> FG_LOCATION_AFTER_EDIT = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";
$HD_Form -> FG_LOCATION_AFTER_DELETE = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";
