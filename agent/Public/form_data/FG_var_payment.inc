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

if (! has_rights (ACX_ACCESS)) {
    Header ("HTTP/1.0 401 Unauthorized");
    Header ("Location: PP_error.php?c=accessdenied");
    die();
}


getpost_ifset(array('id', 'card_id', 'date', 'payment','description','added_refill','refill_type'));



if (!isset($form_action) || $form_action=="list"){
	$HD_Form = new FormHandler("cc_logpayment LEFT JOIN cc_card ON cc_card.id=cc_logpayment.card_id LEFT JOIN cc_card_group ON cc_card.id_group=cc_card_group.id", "payment");
	$HD_Form -> FG_QUERY_WHERE_CLAUSE = " cc_card_group.id_agent=".$_SESSION['agent_id'] ;
}else{
	$HD_Form = new FormHandler("cc_logpayment", "payment");
}



$HD_Form -> FG_QUERY_PRIMARY_KEY = 'cc_logpayment.id';

$HD_Form->no_debug();
$HD_Form -> FG_TABLE_DEFAULT_SENS = "DESC";

$list_refill_type=getRefillType_List();
$HD_Form -> AddViewElement(gettext("ID"), "cc_logpayment.id");
$HD_Form -> AddViewElement(gettext("ACCOUNT"), "username", true, 30, "display_customer_link");
$HD_Form -> AddViewElement(gettext("PAYMENT DATE"), "date", true, 30, "display_dateformat");
$HD_Form -> AddViewElement(gettext("PAYMENT AMOUNT"), "payment");
$HD_Form -> AddViewElement(gettext("DESCRIPTION"), "description");
$HD_Form -> AddViewElement(gettext("PAYMENT TYPE"), "payment_type", true, 0, "", "list", $list_refill_type);
$HD_Form -> AddViewElement(gettext("REFILL"), "id_logrefill", true, 0, "", "lie_link", "cc_logrefill", "credit, id", "id='%id'", "%1 " . BASE_CURRENCY . " (id:%2)", "A2B_entity_logrefill.php");

$HD_Form -> FieldViewElement ('cc_logpayment.id, username, date, payment, cc_logpayment.description ,payment_type,id_logrefill');


$HD_Form -> FG_ENABLE_INFO_BUTTON = true;
$HD_Form -> FG_INFO_BUTTON_LINK = "A2B_payment_info.php?id=";

$HD_Form -> FG_FILTER_ENABLE = true;
$HD_Form -> FG_FILTER_COLUMN = 'cardnumber';
$HD_Form -> FG_FILTER_LABEL = 'account number';


if (!($popup_select>=1))	 $HD_Form -> search_form_enabled = true;

$HD_Form -> search_session_key = 'payment_cust_selection';

$HD_Form -> search_form_title = gettext('Define specific criteria to search for payments created.');
$HD_Form -> search_date_enabled = true;
$HD_Form -> search_date_text = gettext('DATE');
$HD_Form -> search_date_column = 'date';

$HD_Form -> AddSearchTextInput(gettext("ACCOUNT NUMBER"), 'username','usernametype');
$HD_Form -> AddSearchTextInput(gettext("LASTNAME"),'lastname','lastnametype');
$HD_Form -> AddSearchTextInput(gettext("FIRSTNAME"),'firstname','firstnametype');


if (isset($filterprefix)  &&  (strlen($filterprefix)>0)){
	if (strlen($HD_Form -> FG_QUERY_WHERE_CLAUSE)>0) $HD_Form -> FG_QUERY_WHERE_CLAUSE.=" AND ";
	$HD_Form -> FG_QUERY_WHERE_CLAUSE.="username like '$filterprefix%'";
}




