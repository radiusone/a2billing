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


getpost_ifset(array('id', 'agent_id', 'date', 'credit','description'));


if (!isset($form_action) || $form_action=="list") {
	$HD_Form = new FormHandler("cc_logrefill_agent LEFT JOIN cc_agent ON cc_agent.id=agent_id", "Refill Agent");
	$HD_Form -> FG_QUERY_WHERE_CLAUSE = " cc_agent.id =".$_SESSION['agent_id'] ;
} else {
	$HD_Form = new FormHandler("cc_logrefill_agent", "Refill Agent");
}

$HD_Form -> FG_QUERY_PRIMARY_KEY = 'cc_logrefill_agent.id';

$HD_Form->no_debug();
$HD_Form -> FG_TABLE_DEFAULT_SENS = "DESC";


$list_refill_type=getRefillType_List();
$HD_Form -> AddViewElement(gettext("ID"), "cc_logrefill_agent.id");
$HD_Form -> AddViewElement(gettext("AGENT"), "creator", true, "25", null, "lie", "cc_agent", "lastname, firstname", "id='%id'", "%1 %2");
$HD_Form -> AddViewElement(gettext("LOGIN"), "login", true, "25", null, "lie", "cc_agent", "login", "id='%id'", "%1");
$HD_Form -> AddViewElement(gettext("REFILL DATE"), "date", true, "30", "display_dateformat");
$HD_Form -> AddViewElement(gettext("DESCRIPTION"), "description");
$HD_Form -> AddViewElement(gettext("REFILL TYPE"), "refill_type", true, "", null, "list", $list_refill_type);
$HD_Form -> AddViewElement(gettext("REFILL AMOUNT"), "cc_logrefill.credit");

$HD_Form -> FieldViewElement ('cc_logrefill_agent.id, agent_id,agent_id, date, description, cc_logrefill_agent.refill_type as refill_type, cc_logrefill_agent.credit');


$HD_Form -> FG_ENABLE_INFO_BUTTON = true;
$HD_Form -> FG_INFO_BUTTON_LINK = "A2B_refill_info_agent.php?id=";

if (!($popup_select>=1))	 $HD_Form -> FG_FILTER_SEARCH_FORM = true;
$HD_Form -> FG_FILTER_SEARCH_SESSION_NAME = 'refill_agent_selection';
$HD_Form -> FG_FILTER_SEARCH_TOP_TEXT = gettext('Define specific criteria to search for payments created.');
$HD_Form -> FG_FILTER_SEARCH_1_TIME = true;
$HD_Form -> FG_FILTER_SEARCH_1_TIME_TEXT = gettext('DATE');
$HD_Form -> FG_FILTER_SEARCH_1_TIME_FIELD = 'date';
