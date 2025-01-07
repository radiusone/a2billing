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


getpost_ifset(array('id', 'agent_id', 'date', 'payment','description','added_refill','refill_type'));


if (!isset($form_action) || $form_action=="list"){
	$HD_Form = new FormHandler("cc_logpayment_agent LEFT JOIN cc_agent ON cc_agent.id=agent_id", _("Payment Agent"), 'cc_logpayment_agent.id');
    $HD_Form->list_query_conditions["cc_agent.id"] = $_SESSION["agent_id"];
}else{
	$HD_Form = new FormHandler("cc_logpayment_agent", _("Payment Agent"));
}

$HD_Form -> list_query_order_direction = "DESC";

$list_refill_type=getRefillType_List();
$HD_Form -> AddListValue(gettext("ID"), "cc_logpayment_agent.id");
$HD_Form -> AddListValue(gettext("AGENT"), "CONCAT(lastname, ', ', firstname)");
$HD_Form -> AddListValue(gettext("LOGIN"), "login");
$HD_Form -> AddListValue(gettext("PAYMENT DATE"), "date");
$HD_Form -> AddListValue(gettext("PAYMENT AMOUNT"), "payment");
$HD_Form -> AddListValue(gettext("DESCRIPTION"), "description");
$HD_Form -> AddListMapping(gettext("PAYMENT TYPE"), "payment_type", $list_refill_type);

$HD_Form -> FieldViewElement ("cc_logpayment_agent.id, CONCAT(lastname, ', ', firstname), login, date, payment,description ,payment_type");


$HD_Form -> FG_ENABLE_INFO_BUTTON = true;
$HD_Form -> FG_INFO_BUTTON_LINK = "A2B_payment_info_agent.php?id=";

if (!($popup_select>=1))	 $HD_Form -> search_form_enabled = true;
$HD_Form -> search_session_key = 'payment_agent_selection';
$HD_Form -> search_form_title = gettext('Define specific criteria to search for payments created.');
$HD_Form->AddSearchDateInput(_("DATE"), "date");
