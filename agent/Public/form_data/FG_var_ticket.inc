<?php

use A2billing\Agent;
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

if (! has_rights (Agent::ACX_ACCESS)) {
    Header ("HTTP/1.0 401 Unauthorized");
    Header ("Location: PP_error.php?c=accessdenied");
    die();
}



getpost_ifset(array('id', 'title', 'id_component','description','priority','status','creator','username','viewed_agent' ));

if (!isset($form_action) || $form_action=="list"){
	$HD_Form = new FormHandler("cc_ticket LEFT JOIN
	( SELECT aa.id, CASE WHEN MAX(aa.viewed_agent)=1 OR SUM(bb.viewed_agent )>0 THEN 1 ELSE 0 END as viewed
	  FROM cc_ticket aa
	  LEFT JOIN cc_ticket_comment bb ON aa.id = bb.id_ticket
	  LEFT JOIN cc_card dd ON dd.id = aa.creator
	  LEFT JOIN cc_card_group ee ON dd.id_group = ee.id
	  WHERE  aa.creator_type = 0 AND ee.id_agent=" . $_SESSION['agent_id'] . " GROUP BY aa.id
	) AS v ON cc_ticket.id = v.id
	LEFT JOIN cc_card ON cc_card.id=cc_ticket.creator LEFT JOIN cc_card_group ON cc_card.id_group=cc_card_group.id", _("Ticket"), "cc_ticket.id");
}else {
	 $HD_Form = new FormHandler("cc_ticket", _("Ticket"));
}

$HD_Form -> FG_TABLE_DEFAULT_ORDER = " cc_ticket.id";
$HD_Form -> FG_TABLE_DEFAULT_SENS = "DESC";
$HD_Form->list_query_conditions["cc_ticket.creator_type"] = 0;
$HD_Form->list_query_conditions["cc_card_group.id_agent"] = $_SESSION["agent_id"];

$priority_list = Ticket::getAllPriorityListView();
$viewed_list = getTicketViewedList();
$status_list = Ticket::getAllStatusListView();

$filter_view ='v.viewed ';


$HD_Form -> AddListValue(gettext("ID"), "id");
$HD_Form -> AddListValue(gettext("CUSTOMER"), "CONCAT(lastname, ', ', firstname)");
$HD_Form -> AddListValue(gettext("ACCOUNT NUMBER"), "username", "display_customer_link");
$HD_Form -> AddListValue(gettext("TITLE"), "title");
$HD_Form -> AddListValue(gettext("CREATIONDATE"), "creationdate");
$HD_Form -> AddListSqlMapping(gettext("COMPONENT"), "id_component", new Table("cc_support_component", ["id", "name"]));
$HD_Form -> AddListMapping(gettext("PRIORITY"), "priority", $priority_list);
$HD_Form -> AddListMapping(gettext("VIEWED"), "viewed_agent", $viewed_list);
$HD_Form -> AddListMapping(gettext("STATUS"), "status", $status_list);

$HD_Form -> FieldViewElement ("cc_ticket.id, CONCAT(lastname, ', ', firstname), username, cc_ticket.title, cc_ticket.creationdate, id_component , priority ,$filter_view, cc_ticket.status");

$HD_Form->FG_ENABLE_INFO_BUTTON = true;
$HD_Form->FG_INFO_BUTTON_LINK = "A2B_ticket_view.php?id=";
$HD_Form -> FG_ENABLE_EDIT_BUTTON = true;
$HD_Form -> FG_ENABLE_DELETE_BUTTON = true;
$HD_Form -> FG_ENABLE_ADD_BUTTON = true;

if ($form_action=='ask-add'){
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
    "size=60 maxlength=50",
    [Validator::class, "min1Char"],
    gettext("Insert the Title of ticket ")
);

$HD_Form->AddEditSqlSelect(gettext("Insert the Title of ticket"), "id_component", new Table("cc_support_component", [
    "id", "name"
]));
$HD_Form->AddEditSelect(gettext("PRIORITY"), "priority", $priority_list);

$HD_Form->AddEditTextarea(
    gettext("DESCRIPTION"),
    "description",
    "",
    "cols='50' rows='4'", null, gettext("Insert the description")
);

$HD_Form->FG_ADD_QUERY_HIDDEN_INPUTS["viewed_agent"] = 0;

$HD_Form -> FG_INTRO_TEXT_EDITION= '';
$HD_Form -> FG_INTRO_TEXT_ADITION = '';

$HD_Form -> FG_LOCATION_AFTER_ADD = "?id=";
$HD_Form -> FG_LOCATION_AFTER_EDIT = "?id=";
$HD_Form -> FG_LOCATION_AFTER_DELETE = "?id=";
