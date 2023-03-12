<?php

use A2billing\Admin;
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

Admin::checkPageAccess(Admin::ACX_ADMINISTRATOR);

getpost_ifset(array('id', 'displayheader', 'type','popup_select','popup_formname','popup_fieldname'));


// type : refill or payment
$HD_Form->no_debug();

if ($type=='payment'){
	$HD_Form = new FormHandler("cc_logpayment as t2", "Payment");
	$HD_Form -> FG_QUERY_WHERE_CLAUSE = "t2.card_id = $id";
	$HD_Form -> FG_TABLE_DEFAULT_SENS = "DESC";

	//SELECT t3.date, t3.credit, t3.reseller_id from cc_logrefill as t3 WHERE t3.card_id='1'
	//SELECT t2.date, t2.payment, t2.reseller_id from cc_logpayment as t2 WHERE t2.card_id='1'
	$HD_Form -> AddViewElement(gettext("DATE"), "date", true, 30, "display_dateformat");
	$HD_Form -> AddViewElement(gettext("AMOUNT"), "payment");

	$HD_Form -> FieldViewElement ('t2.date, t2.payment');
}else{
	$list_sql = "cc_invoice ".
	            "LEFT JOIN ( SELECT st1.id_invoice, SUM(st1.price*(1+(st1.vat/100))) AS total_price FROM cc_invoice_item AS st1 GROUP BY st1.id_invoice) " .
	            "AS items ON items.id_invoice=cc_invoice.id ";
	$HD_Form = new FormHandler($list_sql, "Invoice", 'cc_invoice.id');
	$HD_Form -> FG_TABLE_DEFAULT_SENS = "DESC";
	$HD_Form -> FG_QUERY_WHERE_CLAUSE = "cc_invoice.id_card = $id";
	//SELECT t3.date, t3.credit, t3.reseller_id from cc_logrefill as t3 WHERE t3.card_id='1'
	//SELECT t2.date, t2.payment, t2.reseller_id from cc_logpayment as t2 WHERE t2.card_id='1'
	$HD_Form -> AddViewElement(gettext("DATE"), "date", true, 30, "display_dateformat");
	$HD_Form -> AddViewElement(gettext("AMOUNT INCL VAT"), "total_price", true, "", "display_2bill");
	$HD_Form -> FieldViewElement ("cc_invoice.date, total_price");
}

$HD_Form -> CV_FOLLOWPARAMETERS = "&popup_select=".$popup_select."&id=$id&type=$type";
$HD_Form -> FG_ENABLE_EDIT_BUTTON = false;
$HD_Form -> FG_ENABLE_DELETE_BUTTON = false;
$HD_Form -> FG_ENABLE_ADD_BUTTON = false;
