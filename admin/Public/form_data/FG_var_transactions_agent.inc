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


getpost_ifset(array('id', 'agent_id', 'agent_name', 'agent_email_address', 'item_name', 'item_id', 'item_quantity', 'payment_method','cc_type', 'cc_owner','cc_number', 'cc_expires', 'orders_status', 'last_modified', 'date_purchased','orders_date_finished', 'orders_amount', 'currency', 'currency_value'));


$HD_Form = new FormHandler("cc_payments_agent", "Transactions");

$HD_Form->no_debug();
$HD_Form -> FG_TABLE_DEFAULT_ORDER = "id";
$HD_Form -> FG_TABLE_DEFAULT_SENS = "DESC";

$HD_Form -> AddViewElement(gettext("ID"), "id");
$HD_Form -> AddViewElement(gettext("ORDER DATE"), "date_purchased", true, "19", "display_dateformat");
$HD_Form -> AddViewElement(gettext("CARDID"), "agent_id");
$HD_Form -> AddViewElement(gettext("NAME"), "agent_name");
$HD_Form -> AddViewElement(gettext("ITEM NAME"), "item_name");
$HD_Form -> AddViewElement(gettext("ITEM ID"), "item_id", true, "30", "display_linktoagent");
$HD_Form -> AddViewElement(gettext("METHOD"), "payment_method");
$HD_Form -> AddViewElement(gettext("CC NUMBER"), "cc_number");
$HD_Form -> AddViewElement(gettext("STATUS"), "orders_status", true, "15", null, "lie", "cc_payments_status", "status_name", "status_id='%id'", "%1");
$HD_Form -> AddViewElement(gettext("AMOUNT"), "orders_amount");
$HD_Form -> AddViewElement("<acronym title=\"" . gettext("CURRENCY") . "\">" . gettext("CUR") . "</acronym>", "currency", true, "");
$HD_Form -> AddViewElement(gettext("VALUE"), "currency_value");

$HD_Form -> FieldViewElement ('id, date_purchased, agent_id, agent_name, item_name, item_id, payment_method, cc_number, orders_status, orders_amount, currency, currency_value');


$HD_Form -> FG_ENABLE_ADD_BUTTON = false;
$HD_Form -> FG_ENABLE_EDIT_BUTTON = false;
$HD_Form -> FG_ENABLE_DELETE_BUTTON = true;

// Set the filter variables
$HD_Form -> FG_FILTER_ENABLE = true;
$HD_Form -> FG_FILTER_COLUMN = 'transaction_id';
$HD_Form -> FG_FILTER_LABEL = 'transaction ID';

if (isset($filterprefix)  &&  (strlen($filterprefix)>0)){
	if (strlen($HD_Form -> FG_QUERY_WHERE_CLAUSE)>0) $HD_Form -> FG_QUERY_WHERE_CLAUSE.=" AND ";
	$HD_Form -> FG_QUERY_WHERE_CLAUSE.="id like '$filterprefix%'";
}

$HD_Form -> FG_INTRO_TEXT_ASK_DELETION = gettext("If you really want remove this")." ".$HD_Form->FG_INSTANCE_NAME.", ".gettext("click on the delete button.");


$HD_Form -> FG_GO_LINK_AFTER_ACTION_DELETE = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?atmenu=document&stitle=Document&wh=AC&id=";




