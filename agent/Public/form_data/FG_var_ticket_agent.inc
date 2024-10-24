<?php

use A2billing\Agent;
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

if (! has_rights (Agent::ACX_ACCESS)) {
    Header ("HTTP/1.0 401 Unauthorized");
    Header ("Location: PP_error.php?c=accessdenied");
    die();
}

include (LIBDIR."/support/classes/ticket.php");

getpost_ifset(array('id', 'title', 'id_component','description','priority','status','viewed_agent'));

//$HD_Form = new FormHandler("cc_ticket LEFT JOIN cc_ticket_comment ON cc_ticket.id = cc_ticket_comment.id_ticket", "Ticket", "cc_ticket.id");
$HD_Form = new FormHandler(" cc_ticket LEFT JOIN
( SELECT aa.id, CASE WHEN MAX(aa.viewed_agent)=1 OR SUM(bb.viewed_agent)>0 THEN 1 ELSE 0 END as viewed
  FROM cc_ticket aa
  LEFT JOIN cc_ticket_comment bb ON aa.id = bb.id_ticket
  WHERE aa.creator_type =1 AND aa.creator= " . $_SESSION['agent_id'] . " GROUP BY aa.id
) AS v ON cc_ticket.id = v.id", _("Ticket"), "cc_ticket.id");


$plang='en';

$HD_Form->no_debug();
$HD_Form -> FG_TABLE_DEFAULT_ORDER = " cc_ticket.id";
$HD_Form -> FG_TABLE_DEFAULT_SENS = "DESC";
$HD_Form -> FG_QUERY_WHERE_CLAUSE = "cc_ticket.creator_type=1 AND cc_ticket.creator= ".$_SESSION['agent_id'];
$HD_Form->list_query_conditions["cc_ticket.creator_type"] = 1;
$HD_Form->list_query_conditions["cc_card_group.id_agent"] = $_SESSION["agent_id"];

$status_list = Ticket::getAllStatusListView();
$viewed_list = getTicketViewedList();
$priority_list =getTicketPriorityList();

$filter_view ='v.viewed ';


$HD_Form -> AddViewElement(gettext("ID"), "id");
$HD_Form -> AddViewElement(gettext("TITLE"), "title");
$HD_Form -> AddViewElement(gettext("CREATIONDATE"), "creationdate", true, 19);
$HD_Form -> AddViewElement(gettext("COMPONENT"), "id_component", true, 15, "", "lie", "cc_support_component", "name", "id='%id'", "%1");
$HD_Form -> AddViewElement(gettext("PRIORITY"), "priority", true, 0, "", "list", $priority_list);
$HD_Form -> AddViewElement(gettext("TO SEE?"), "viewed_agent", true, 0, "", "list", $viewed_list);
$HD_Form -> AddViewElement(gettext("STATUS"), "status", true, 0, "", "list", $status_list);

$HD_Form -> FieldViewElement ("cc_ticket.id, title, cc_ticket.creationdate, id_component , priority, $filter_view, cc_ticket.status");


$HD_Form -> FG_OTHER_BUTTON1_LINK = "A2B_ticket_view.php?id=";
$HD_Form -> FG_OTHER_BUTTON1_IMG = Images_Path."/icon-viewdetails.gif";


$HD_Form -> FG_OTHER_BUTTON1 = true;
$HD_Form -> FG_ENABLE_EDIT_BUTTON = false;
$HD_Form -> FG_ENABLE_DELETE_BUTTON = false;
$HD_Form -> FG_ENABLE_ADD_BUTTON = false;

$HD_Form -> FG_INTRO_TEXT_EDITION= '';


$HD_Form -> FG_INTRO_TEXT_ADITION = '';


$HD_Form -> FG_LOCATION_AFTER_ADD = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";
$HD_Form -> FG_LOCATION_AFTER_EDIT = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";
$HD_Form -> FG_LOCATION_AFTER_DELETE = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";
?>
