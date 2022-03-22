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

getpost_ifset(array('id', 'card_id', 'date', 'credit', 'description'));


if (!isset($form_action) || $form_action=="list") {
	$HD_Form = new FormHandler("cc_logrefill LEFT JOIN cc_card ON cc_card.id=cc_logrefill.card_id LEFT JOIN cc_card_group ON cc_card.id_group=cc_card_group.id", "Refill");
	$HD_Form -> FG_QUERY_WHERE_CLAUSE = " cc_card_group.id_agent=".$_SESSION['agent_id'] ;
} else {
	$HD_Form = new FormHandler("cc_logrefill", "Refill");
}

$HD_Form -> FG_QUERY_PRIMARY_KEY = 'cc_logrefill.id';

$HD_Form->no_debug();
$HD_Form -> FG_TABLE_DEFAULT_SENS = "DESC";


$list_refill_type=getRefillType_List();
$HD_Form -> AddViewElement(gettext("ID"), "cc_logrefill.id");
$HD_Form -> AddViewElement(gettext("ACCOUNT"), "username", true, 30, "display_customer_link");
$HD_Form -> AddViewElement(gettext("REFILL DATE"), "date", true, 30, "display_dateformat");
$HD_Form -> AddViewElement(gettext("DESCRIPTION"), "description");
$HD_Form -> AddViewElement(gettext("REFILL TYPE"), "refill_type", true, 0, "", "list", $list_refill_type);
$HD_Form -> AddViewElement(gettext("REFILL AMOUNT"), "cc_logrefill.credit");

$HD_Form -> FieldViewElement ('cc_logrefill.id, username, date, cc_logrefill.description, cc_logrefill.refill_type as refill_type, cc_logrefill.credit');

$HD_Form -> FG_FILTER_ENABLE = true;
$HD_Form -> FG_FILTER_COLUMN = 'cardnumber';
$HD_Form -> FG_FILTER_LABEL = 'Account number';

if (isset($filterprefix)  &&  (strlen($filterprefix)>0)) {
	if (strlen($HD_Form -> FG_QUERY_WHERE_CLAUSE)>0) $HD_Form -> FG_QUERY_WHERE_CLAUSE.=" AND ";
	$HD_Form -> FG_QUERY_WHERE_CLAUSE.="username like '$filterprefix%'";
}

if (!($popup_select>=1))	 $HD_Form -> FG_FILTER_SEARCH_FORM = true;
$HD_Form -> FG_FILTER_SEARCH_SESSION_NAME = 'refill_cust_selection';
$HD_Form -> FG_FILTER_SEARCH_TOP_TEXT = gettext('Define specific criteria to search for refills created.');
$HD_Form -> FG_FILTER_SEARCH_1_TIME = true;
$HD_Form -> FG_FILTER_SEARCH_1_TIME_TEXT = gettext('DATE');
$HD_Form -> FG_FILTER_SEARCH_1_TIME_FIELD = 'date';

$HD_Form -> AddSearchTextInput(gettext("ACCOUNT NUMBER"), 'username','usernametype');
$HD_Form -> AddSearchTextInput(gettext("LASTNAME"),'lastname','lastnametype');
$HD_Form -> AddSearchTextInput(gettext("FIRSTNAME"),'firstname','firstnametype');
