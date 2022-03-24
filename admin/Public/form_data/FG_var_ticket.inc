<?php

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

if (! has_rights (ACX_ADMINISTRATOR)) {
    Header ("HTTP/1.0 401 Unauthorized");
    Header ("Location: PP_error.php?c=accessdenied");
    die();
}

include (LIBDIR."/support/classes/ticket.php");

getpost_ifset(array('id', 'title', 'id_component','description','priority','status','creator','username','viewed_admin' ));


if (!isset($form_action) || $form_action=="list") {
	$HD_Form = new FormHandler("cc_ticket LEFT JOIN
	( SELECT aa.id, CASE WHEN MAX(aa.viewed_admin)=1 OR SUM(bb.viewed_admin)>0 THEN 1 ELSE 0 END as viewed FROM cc_ticket aa LEFT JOIN cc_ticket_comment bb ON aa.id = bb.id_ticket	WHERE  aa.creator_type = 0 GROUP BY aa.id ) AS v ON cc_ticket.id = v.id
	LEFT JOIN cc_card ON cc_card.id=cc_ticket.creator", "Ticket");
} else {
	$HD_Form = new FormHandler("cc_ticket", "Ticket");
}


$HD_Form->no_debug();
$HD_Form -> FG_TABLE_DEFAULT_ORDER = " cc_ticket.id";
$HD_Form -> FG_TABLE_DEFAULT_SENS = "DESC";
$HD_Form -> FG_QUERY_WHERE_CLAUSE = " cc_ticket.creator_type= 0 ";
$HD_Form -> FG_QUERY_PRIMARY_KEY="cc_ticket.id";

$HD_Form -> FG_FK_DELETE_ALLOWED = true;
$HD_Form -> FG_FK_DELETE_CONFIRM = true;
$HD_Form -> FG_FK_WARNONLY = true;
$HD_Form -> FG_FK_TABLENAMES = array("cc_ticket_comment");
$HD_Form -> FG_FK_EDITION_CLAUSE = array(" id_ticket ");

$HD_Form ->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form ->FG_LIST_ADDING_BUTTON_LINK1 = "CC_ticket.php?form_action=ask-add&section=".$_SESSION["menu_section"];
$HD_Form ->FG_LIST_ADDING_BUTTON_ALT1 = $HD_Form ->FG_LIST_ADDING_BUTTON_MSG1 = gettext("Add TICKET");
$HD_Form ->FG_LIST_ADDING_BUTTON_IMG1 = Images_Path ."/note_add.png" ;


$priority_list = getTicketPriorityList();

$viewed_list = getTicketViewedList();
$status_list = Ticket::getAllStatusListView();

$filter_view ='v.viewed ';

$HD_Form -> AddViewElement(gettext("ID"), "id");
$HD_Form -> AddViewElement(gettext("CUSTOMER"), "creator", true, 25, "", "lie", "cc_card", "lastname, firstname", "id='%id'", "%1 %2");
$HD_Form -> AddViewElement(gettext("ACCOUNT NUMBER"), "username", true, 30, "display_customer_link");
$HD_Form -> AddViewElement(gettext("TITLE"), "title");
$HD_Form -> AddViewElement(gettext("CREATIONDATE"), "creationdate", true, 19, "display_dateformat");
$HD_Form -> AddViewElement(gettext("COMPONENT"), "id_component", true, 15, "", "lie", "cc_support_component", "name", "id='%id'", "%1");
$HD_Form -> AddViewElement(gettext("PRIORITY"), "priority", true, 0, "", "list", $priority_list);
$HD_Form -> AddViewElement(gettext("VIEWED"), "viewed_admin", false, 0, "", "list", $viewed_list);
$HD_Form -> AddViewElement(gettext("STATUS"), "status", true, 0, "", "list", $status_list);

$HD_Form -> FieldViewElement ("cc_ticket.id, cc_ticket.creator, cc_card.username, cc_ticket.title, cc_ticket.creationdate, cc_ticket.id_component , cc_ticket.priority , $filter_view , cc_ticket.status");


$HD_Form -> FG_OTHER_BUTTON1_LINK = "CC_ticket_view.php?id=";
$HD_Form -> FG_OTHER_BUTTON1_IMG = Images_Path."/icon-viewdetails.gif";
$HD_Form -> FG_OTHER_BUTTON1_ALT = gettext("VIEW DETAILS");


if (!($popup_select>=1))	 $HD_Form -> FG_FILTER_SEARCH_FORM = true;
$HD_Form -> FG_FILTER_SEARCH_DELETE_ALL =false;
$HD_Form -> FG_FILTER_SEARCH_SESSION_NAME = 'ticket_cust_selection';
$HD_Form -> FG_FILTER_SEARCH_TOP_TEXT = gettext('Define specific criteria to search for ticket created.');
$HD_Form -> FG_FILTER_SEARCH_1_TIME = true;
$HD_Form -> FG_FILTER_SEARCH_1_TIME_TEXT = gettext('DATE');
$HD_Form -> FG_FILTER_SEARCH_1_TIME_FIELD = 'cc_ticket.creationdate';

$HD_Form -> AddSearchTextInput(gettext("ACCOUNT NUMBER"), 'username','usernametype');

$HD_Form -> FG_OTHER_BUTTON1 = true;
$HD_Form -> FG_ENABLE_EDIT_BUTTON = true;
$HD_Form -> FG_ENABLE_DELETE_BUTTON = true;
$HD_Form -> FG_ENABLE_ADD_BUTTON = true;

if ($form_action=='ask-add') {
	$DBHandle_max  = DbConnect();
	if ($username) {
		$resmax = $DBHandle_max -> Execute("SELECT id FROM cc_card where username='$username'");
		$numrow = 0;
		if ($resmax)
			$numrow = $resmas -> RecordCount( );

		if ($numrow!=0) {
			$db_row [] = $resmax -> fetchRow();
			$val_default = "value='".$db_row[0][0]."'";
		}
	}
}

$HD_Form -> AddEditElement (gettext("CREATOR"),
    "creator",
    '',
    gettext("Set the Customer ID"),
    "",
    "POPUPVALUE",
    "size=30 maxlength=50 $val_default",
    4, gettext("Insert the Customer ID"), "", "", "",
    ""
	, "",
    "A2B_entity_card.php?popup_select=1&", ", 'CardNumberSelection','width=550,height=350,top=20,left=100,scrollbars=1'");

$HD_Form -> AddEditElement (gettext("TITLE"),
    "title",
    '$value',
    "",
    "",
    "INPUT",
    "size=60 maxlength=50",
    9, gettext("Insert the Title of ticket "));

$HD_Form -> AddEditElement (gettext("COMPONENT"),
    "id_component",
    "",
    "",
    "", "SELECT", "",
    null,
    "",
    "sql",
    "cc_support_component", "name, id", "", "", "%1");

$HD_Form -> AddEditElement (gettext("PRIORITY"),
    "priority",
    '$value',
    "",
    "",
    "SELECT",
    "",
    null, "", "list", "", "", "", $priority_list, "%1");

$HD_Form -> AddEditElement (gettext("DESCRIPTION"),
    "description",
    '',
    "",
    "",
    "TEXTAREA",
    "cols=50 rows=4",
    null, gettext("Insert the description"));

$HD_Form -> FieldEditElement (' creator, title, id_component, priority, description');

// Set the filter variables
$HD_Form -> FG_FILTER_ENABLE = true;
$HD_Form -> FG_FILTER_COLUMN = 'username';
$HD_Form -> FG_FILTER_LABEL = gettext('ACCOUNT NUMBER');

if (isset($filterprefix)  &&  (strlen($filterprefix)>0)){
	if (strlen($HD_Form -> FG_QUERY_WHERE_CLAUSE)>0) $HD_Form -> FG_QUERY_WHERE_CLAUSE.=" AND ";
	$HD_Form -> FG_QUERY_WHERE_CLAUSE.="username like '$filterprefix%'";
}

$HD_Form->FG_ADD_QUERY_HIDDEN_INPUTS["viewed_admin"] = 0;
$HD_Form -> FG_ADDITIONAL_FUNCTION_AFTER_ADD = 'ticket_add';
$HD_Form -> FG_INTRO_TEXT_EDITION= '';


$HD_Form -> FG_INTRO_TEXT_ADITION = '';


$HD_Form -> FG_LOCATION_AFTER_ADD = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?atmenu=document&stitle=Document&wh=AC&id=";
$HD_Form -> FG_LOCATION_AFTER_EDIT = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?atmenu=document&stitle=Document&wh=AC&id=";
$HD_Form -> FG_LOCATION_AFTER_DELETE = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?atmenu=document&stitle=Document&wh=AC&id=";


