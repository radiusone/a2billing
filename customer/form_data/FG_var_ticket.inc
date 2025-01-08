<?php

use A2billing\Customer;
use A2billing\Forms\FormHandler;
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

if (! has_rights (Customer::ACX_ACCESS)) {
    Header ("HTTP/1.0 401 Unauthorized");
    Header ("Location: PP_error.php?c=accessdenied");
    die();
}


getpost_ifset(array('id', 'title', 'id_component','description','priority','status','viewed_cust'));

$HD_Form = new FormHandler("cc_ticket LEFT JOIN ( SELECT aa.id, CASE WHEN MAX(aa.viewed_cust)=1 OR SUM(bb.viewed_cust)>0 THEN 1 ELSE 0 END as viewed FROM cc_ticket aa LEFT JOIN cc_ticket_comment bb ON aa.id = bb.id_ticket WHERE aa.creator_type = 0 AND aa.creator = " . $_SESSION['card_id'] . " GROUP BY aa.id ) AS v ON cc_ticket.id = v.id", _("Ticket"), "cc_ticket.id");

$HD_Form ->list_query_order_columns = ["cc_ticket.id"];
$HD_Form -> list_query_order_direction = "DESC";
$HD_Form->list_query_conditions["cc_ticket.creator_type"] = 0;
$HD_Form->list_query_conditions["cc_ticket.creator"] = $_SESSION["card_id"];

$status_list = Ticket::getAllStatusListView();
$viewed_list = getTicketViewedList();
$priority_list = Ticket::getAllPriorityListView();
$filter_view ='v.viewed ';


$HD_Form -> AddListValue(gettext("ID"), "id");
$HD_Form -> AddListValue(gettext("TITLE"), "title");
$HD_Form -> AddListValue(gettext("CREATIONDATE"), "creationdate");
$HD_Form -> AddListSqlMapping(gettext("COMPONENT"), "id_component", new Table("cc_support_component", ["id", "name"]));
$HD_Form -> AddListMapping(gettext("PRIORITY"), "priority", $priority_list);
$HD_Form -> AddListMapping(gettext("VIEWED"), "viewed_cust", $viewed_list);
$HD_Form -> AddListMapping(gettext("STATUS"), "status", $status_list);

$HD_Form -> FieldViewElement ("cc_ticket.id, title, cc_ticket.creationdate, id_component , priority,$filter_view,status");

$HD_Form->FG_ENABLE_INFO_BUTTON = true;
$HD_Form->FG_INFO_BUTTON_LINK = "A2B_ticket_view.php?id=";
$HD_Form -> FG_ENABLE_EDIT_BUTTON = false;
$HD_Form -> FG_ENABLE_DELETE_BUTTON = false;
$HD_Form -> FG_ENABLE_ADD_BUTTON = false;

$HD_Form -> FG_INTRO_TEXT_EDITION= '';
$HD_Form -> FG_INTRO_TEXT_ADITION = '';
