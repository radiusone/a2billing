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


$HD_Form = new FormHandler("cc_card LEFT JOIN cc_card_group ON cc_card.id_group=cc_card_group.id ", _("Card"), "cc_card.id");
$HD_Form -> FG_QUERY_WHERE_CLAUSE = " cc_card_group.id_agent=".$_SESSION['agent_id'] ;
$HD_Form->no_debug();
$HD_Form -> FG_LIST_VIEW_PAGE_SIZE = 10;

$HD_Form -> AddViewElement(gettext("ID"), "cc_card.id");
$HD_Form -> AddViewElement(gettext("CardNumber"), "username", true, 30, "display_customer_link");
$HD_Form -> AddViewElement(gettext("FIRSTNAME"), "firstname", true, 15);
$HD_Form -> AddViewElement(gettext("LASTNAME"), "lastname", true, 15);

$HD_Form -> AddViewElement(gettext("CREDIT"), "credit", true, "", "display_2bill");
$HD_Form -> AddViewElement(gettext("COMMISSION ACCRUED"), "commission", true, 15, "", "lie", "cc_agent_commission", "COALESCE(TRUNCATE(SUM(amount),2),0)", "id_card='%id'", "%1 " . strtoupper(BASE_CURRENCY));
$HD_Form -> AddViewElement(gettext("INVOICE"), "invoice", true, 15, "", "lie", "cc_invoice LEFT JOIN cc_invoice_item ON cc_invoice_item.id_invoice=cc_invoice.id", "TRUNCATE(SUM(CEIL(cc_invoice_item.price*(1+(cc_invoice_item.vat/100))*100)/100),2)", "cc_invoice.id_card='%id' GROUP BY cc_invoice.id_card", "%1");
$HD_Form -> AddViewElement(gettext("PAYMENT"), "payment", true, 15, "", "lie", "cc_logpayment as t2", "TRUNCATE(SUM(t2.payment),2),t2.card_id", "t2.card_id='%id' GROUP BY t2.card_id", "%1");
$HD_Form -> AddViewElement(gettext("TO PAY"), "to pay", true, 0, "", "eval", '(%7 - %6 )'); //abs

$HD_Form -> FieldViewElement ('cc_card.id, username, firstname, lastname, credit,cc_card.id,cc_card.id, cc_card.id, cc_card.id');

$HD_Form -> FG_ENABLE_EDIT_BUTTON = false;
$HD_Form -> FG_ENABLE_DELETE_BUTTON = false;
$HD_Form -> FG_ENABLE_ADD_BUTTON = false;

$HD_Form -> FG_OTHER_BUTTON1 = true;
$HD_Form -> FG_OTHER_BUTTON2 = true;
$HD_Form -> FG_OTHER_BUTTON2_LINK="javascript:;\" onClick=\"window.open('A2B_entity_moneysituation_details.php?type=payment&form_action=list&displayheader=0&popup_select=1&id=|param|','','scrollbars=yes,resizable=yes,width=500,height=270')\"";
$HD_Form -> FG_OTHER_BUTTON1_LINK="javascript:;\" onClick=\"window.open('A2B_entity_moneysituation_details.php?type=invoice&form_action=list&displayheader=0&popup_select=1&id=|param|','','scrollbars=yes,resizable=yes,width=500,height=270')\"";
$HD_Form -> FG_OTHER_BUTTON2_ALT = '<font color="red">'.gettext('PAYMENT').'</font>';
$HD_Form -> FG_OTHER_BUTTON1_ALT = '<font color="red">'.gettext('INVOICE').'</font>';

$HD_Form -> FG_OTHER_BUTTON1_IMG = '';
$HD_Form -> FG_OTHER_BUTTON2_IMG = '';

$HD_Form -> FG_FILTER_ENABLE = true;
$HD_Form -> FG_FILTER_COLUMN = 'username';
$HD_Form -> FG_FILTER_LABEL = 'cardnumber';

$HD_Form -> FG_LOCATION_AFTER_ADD = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";
$HD_Form -> FG_LOCATION_AFTER_EDIT = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";
$HD_Form -> FG_LOCATION_AFTER_DELETE = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";

