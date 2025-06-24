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


getpost_ifset(array('id', 'id_card', 'date','title','description'));


if (!isset($form_action) || $form_action=="list") {
	$list_sql = "cc_receipt LEFT JOIN cc_card ON cc_card.id=id_card " .
	            "LEFT JOIN ( SELECT st1.id_receipt, SUM(st1.price) as total_price FROM cc_receipt_item AS st1 GROUP BY st1.id_receipt ) ".
	            "as items ON items.id_receipt = cc_receipt.id ";
	$HD_Form = new FormHandler($list_sql, _("Receipt"), 'cc_receipt.id');
} else {
	$HD_Form = new FormHandler("cc_receipt", _("Receipt"));
}

$HD_Form->list_query_conditions["id_card"] = $_SESSION["card_id"];
$HD_Form -> list_query_order_direction = "DESC";

$HD_Form -> FG_FK_DELETE_ALLOWED = true;
$HD_Form -> FG_FK_DELETE_CONFIRM = true;
$HD_Form -> FG_FK_WARNONLY = true;
$HD_Form -> FG_FK_TABLENAMES = array("cc_receipt_item");
$HD_Form -> FG_FK_EDITION_CLAUSE = array(" id_receipt ");

$HD_Form -> FG_ENABLE_INFO_BUTTON = true;
$HD_Form -> FG_INFO_BUTTON_LINK = "A2B_receipt_view.php?id=";

$list_refill_type=getRefillType_List();
$HD_Form -> AddListValue(gettext("ID"), "cc_receipt.id");
$HD_Form -> AddListValue(gettext("ACCOUNT"), "username");
$HD_Form -> AddListValue(gettext("DATE"), "date");
$HD_Form -> AddListValue(gettext("TITLE"), "title");
$HD_Form -> AddListValue(gettext("AMOUNT INCL VAT"), "price","get_money");
$HD_Form -> FieldViewElement ("cc_receipt.id, username, cc_receipt.date,title,total_price");
