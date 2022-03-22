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


$HD_Form = new FormHandler("cc_agent_commission", "Commission");

$HD_Form -> FG_QUERY_WHERE_CLAUSE = " cc_agent_commission.id_agent=".$_SESSION['agent_id'] ;
$HD_Form -> FG_QUERY_PRIMARY_KEY = 'cc_agent_commission.id';

$HD_Form->no_debug();
$HD_Form -> FG_TABLE_DEFAULT_SENS = "DESC";

$com_list = getRefillType_List();
$HD_Form -> AddViewElement(gettext("ID"), "cc_agent_commission.id");
$HD_Form -> AddViewElement(gettext("ACCOUNT"), "id_card", true, 0, "display_customer_id_link");
$HD_Form -> AddViewElement(gettext("AGENT"), "id_agent", true, 0, "display_linktoagent");
$HD_Form -> AddViewElement(gettext("PAYMENT DATE"), "date", true, 30, "display_dateformat");
$HD_Form -> AddViewElement(gettext("AMOUNT"), "amount");
$HD_Form -> AddViewElement(gettext("PAID STATUS"), "paid_status", true, 0, "", "list", $com_list);
$HD_Form -> AddViewElement(gettext("DESCRIPTION"), "description", true, 50);
$HD_Form -> AddViewElement(gettext("LINK TO PAYMENT"), "id_payment", true, 0, "", "lie_link", "cc_logpayment", "payment, id", "id='%id'", "%1 " . BASE_CURRENCY . " (id:%2)", "A2B_entity_payment.php");

$HD_Form -> FieldViewElement ('cc_agent_commission.id, id_card, id_agent, date, amount, commission_type , description, id_payment');


$HD_Form -> FG_ENABLE_EDIT_BUTTON = false;
$HD_Form -> FG_ENABLE_DELETE_BUTTON = false;
$HD_Form -> FG_ENABLE_ADD_BUTTON = false;