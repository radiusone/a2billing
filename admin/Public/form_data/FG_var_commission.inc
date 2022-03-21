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

getpost_ifset(array('id', 'card_id', 'date', 'payment','description','added_refill','refill_type'));

$HD_Form = new FormHandler("cc_agent_commission", "Commission Agent");

$HD_Form -> FG_QUERY_PRIMARY_KEY = 'cc_agent_commission.id';

$HD_Form->no_debug();
$HD_Form -> FG_TABLE_DEFAULT_SENS = "DESC";

$HD_Form ->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form ->FG_LIST_ADDING_BUTTON_LINK1 = "A2B_entity_commission_agent.php?form_action=ask-add&section=".$_SESSION["menu_section"];
$HD_Form ->FG_LIST_ADDING_BUTTON_ALT1 = $HD_Form ->FG_LIST_ADDING_BUTTON_MSG1 = gettext("Add Commission");
$HD_Form ->FG_LIST_ADDING_BUTTON_IMG1 = Images_Path ."/money_add.png" ;
$com_list = getRefillType_List();
$HD_Form -> AddViewElement(gettext("ID"), "cc_agent_commission.id");
$HD_Form -> AddViewElement(gettext("ACCOUNT"), "id_card", true, "30", "display_customer_id_link");
$HD_Form -> AddViewElement(gettext("AGENT"), "id_agent", true, "30", "display_linktoagent");
$HD_Form -> AddViewElement(gettext("PAYMENT DATE"), "date", true, "30", "display_dateformat");
$HD_Form -> AddViewElement(gettext("AMOUNT"), "amount");
$HD_Form -> AddViewElement(gettext("PAID STATUS"), "paid_status", true, "", null, "list", $com_list);
$HD_Form -> AddViewElement(gettext("DESCRIPTION"), "description", true, 50);
$HD_Form -> AddViewElement(gettext("LINK TO PAYMENT"), "id_payment", true, "", "", "lie_link", "cc_logpayment", "payment, id", "id='%id'", "%1 " . BASE_CURRENCY . " (id:%2)", "A2B_entity_payment.php");

$HD_Form -> FieldViewElement ('cc_agent_commission.id, id_card, id_agent, date, amount, commission_type , description, id_payment');

$HD_Form -> FG_ENABLE_ADD_BUTTON = true;
//$HD_Form -> FG_EDITION = true;
//$HD_Form -> FG_DELETION = true;

if ($form_action=="ask-add" && !empty($id_card)) {
	$id_val = "value='$id_card'";
}

$HD_Form -> AddEditElement (gettext("CUSTOMER ID"),
			   "id_card",
			   "$value",
			   "POPUPVALUE",
			   "size=30 maxlength=50 ".$id_val,
			   "4",
			   gettext("Insert the Customer ID "),
			    "" , "", "", "", "",
				"A2B_entity_card.php?popup_select=1&"
				, ", 'CardNumberSelection','width=550,height=330,top=20,left=100,scrollbars=1'" ,
				gettext("Select the Customer ID"));

$HD_Form -> AddEditElement (gettext("AGENT ID"),
			   "id_agent",
			   "$value",
			   "POPUPVALUE",
			   "size=30 maxlength=50 ".$id_val,
			   "4",
			   gettext("Insert the AGENT ID "),
			    "" , "", "", "", "",
				"A2B_entity_agent.php?popup_select=1&"
				, ", 'AgentSelection','width=550,height=330,top=20,left=100,scrollbars=1'" ,
				gettext("Select the Agent ID"));

if ($form_action=="ask-add") {
	$begin_date = date("Y");
	$end_date = date("-m-d H:i:s");
	$comp_date=$begin_date.$end_date;
	$comp_date="value='$comp_date'";
}

$HD_Form -> AddEditElement (gettext("PAYMENT DATE"),
			   "date",
			   '$value',
			   "INPUT",
			   "size=40 READONLY maxlength=40  $comp_date",
			   "10",
			   gettext("Insert the current date"),
			   "" , "", "",	"", "", "", "", "" );

$HD_Form -> AddEditElement (gettext("COMMISSION AMOUNT"),
			   "amount",
			   '$value',
			   "INPUT",
			   "size=60 maxlength=10",
			   "12",
			   gettext("Insert the commission amount"),
			   "" , "", "",	"", "" , "", "", gettext("Enter the amount in the currency base : ").BASE_CURRENCY );


$HD_Form -> AddEditElement (gettext("DESCRIPTION"),
				"description",
				'',
				"TEXTAREA",
				"cols=50 rows=4",
				"",
				gettext("Insert the description"),
				"" , "", "", "", "" , "", "", "");

$HD_Form -> AddEditElement (gettext("PAYMENT ID"),
			   "id_payment",
			   "$value",
			   "POPUPVALUE",
			   "size=30 maxlength=50 ".$id_val,
			   "4",
			   gettext("Insert the Payment ID "),
			    "" , "", "", "", "",
				"A2B_entity_payment.php?popup_select=1&"
				, ", 'PaymentSelection','width=550,height=330,top=20,left=100,scrollbars=1'" ,
				gettext("Select the Payment ID"));

$HD_Form -> AddEditElement (gettext("COMMISSION TYPE"),
               "commission_type",
               '$value',
               "SELECT",
               "",
               "",
               "",
               "list" , "", "",     "", $com_list, "%1", "", gettext("Define the type of this commission.") );

$HD_Form -> FieldEditElement ('id_card, id_agent, date, amount, description, id_payment, commission_type');



$HD_Form -> FG_ADDITIONAL_FUNCTION_AFTER_ADD = "processing_commission_add";
$HD_Form -> FG_INTRO_TEXT_ADITION = '';

$HD_Form -> FG_LOCATION_AFTER_ADD = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?atmenu=document&stitle=Document&wh=AC&id=";
$HD_Form -> FG_LOCATION_AFTER_EDIT = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?atmenu=document&stitle=Document&wh=AC&id=";
$HD_Form -> FG_LOCATION_AFTER_DELETE = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?atmenu=document&stitle=Document&wh=AC&id=";


