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


$HD_Form = new FormHandler("cc_agent", "Agent");

$HD_Form->no_debug();
$HD_Form -> FG_LIST_VIEW_PAGE_SIZE = 10;

$HD_Form -> AddViewElement(gettext("ID"), "id");
$HD_Form -> AddViewElement(gettext("Agent"), "username", true, 30, "display_linktoagent");
$HD_Form -> AddViewElement(gettext("CREDIT"), "credit", true, "", "display_2bill");
$HD_Form -> AddViewElement(gettext("COMMISSION BAL"), "com_balance", true, "", "display_2bill");
$HD_Form -> AddViewElement(gettext("TOTAL COM. ACCRUED"), "com_accrued", true, 15, "", "lie", "cc_agent_commission as t2", "TRUNCATE(SUM(t2.amount),2)", "t2.id_agent='%id'  ", "%1");
$HD_Form -> AddViewElement(gettext("CUSTOMER REFILL BY AGENT"), "refill", true, 15, "", "lie", "cc_logrefill as t2", "TRUNCATE(SUM(t2.credit),2)", "t2.agent_id='%id' AND t2.refill_type=3 ", "%1");
$HD_Form -> AddViewElement(gettext("AGENT REFILL"), "refill", true, 15, "", "lie", "cc_logrefill_agent as t2", "TRUNCATE(SUM(t2.credit),2)", "t2.agent_id='%id' ", "%1");
$HD_Form -> AddViewElement(gettext("AGENT PAYMENT"), "payment", true, 15, "", "lie", "cc_logpayment_agent as t2", "TRUNCATE(SUM(t2.payment),2)", "t2.agent_id='%id' ", "%1");

$HD_Form -> FieldViewElement ('id, id, credit,com_balance,id,id, id, id');

$HD_Form -> search_form_enabled = true;
$HD_Form -> search_session_key = 'agent_situation';
$HD_Form -> search_form_title = gettext('Define specific criteria to search for agent infos.');
$HD_Form -> search_delete_enabled =false;
$HD_Form -> AddSearchTextInput(gettext("LOGIN"), 'login','logintype');
$HD_Form -> AddSearchTextInput(gettext("LASTNAME"),'lastname','lastnametype');
$HD_Form -> AddSearchTextInput(gettext("FIRSTNAME"),'firstname','firstnametype');

$HD_Form -> FG_ENABLE_EDIT_BUTTON = false;
$HD_Form -> FG_ENABLE_DELETE_BUTTON = false;
$HD_Form -> FG_ENABLE_ADD_BUTTON = false;

$HD_Form -> FG_LOCATION_AFTER_ADD = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";
$HD_Form -> FG_LOCATION_AFTER_EDIT = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";
$HD_Form -> FG_LOCATION_AFTER_DELETE = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";
