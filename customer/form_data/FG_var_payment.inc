<?php

use A2billing\Customer;
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

if (! has_rights (Customer::ACX_ACCESS)) {
    Header ("HTTP/1.0 401 Unauthorized");
    Header ("Location: PP_error.php?c=accessdenied");
    die();
}


getpost_ifset(array('id', 'card_id', 'date', 'payment','description','added_refill'));

$HD_Form = new FormHandler("cc_logpayment", gettext("payment"));

$HD_Form -> FG_QUERY_WHERE_CLAUSE="card_id=".$_SESSION["card_id"];
$HD_Form->list_query_conditions["card_id"] = $_SESSION["card_id"];
$HD_Form->no_debug();
$HD_Form -> FG_TABLE_DEFAULT_SENS = "DESC";

$HD_Form -> AddViewElement(gettext("ID"), "cc_logpayment.id");
$HD_Form -> AddViewElement(gettext("PAYMENT DATE"), "date", true, 30);
$HD_Form -> AddViewElement(gettext("PAYMENT AMOUNT"), "payment");
$HD_Form -> AddViewElement(gettext("DESCRIPTION"), "description");

$HD_Form -> FieldViewElement ('cc_logpayment.id, date, payment,description ');

$HD_Form -> CV_TITLE_TEXT = gettext("Payment list");
$HD_Form -> CV_NO_FIELDS  = gettext("No payments have been made!");

$HD_Form -> FG_ENABLE_EDIT_BUTTON = false;
$HD_Form -> FG_ENABLE_DELETE_BUTTON = false;
$HD_Form -> FG_ENABLE_ADD_BUTTON = false;
