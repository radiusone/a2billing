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

getpost_ifset(array('id'));
$HD_Form = new FormHandler("cc_currencies", "Currencies");

$HD_Form->no_debug();
$HD_Form -> FG_TABLE_DEFAULT_ORDER = " currency";
$HD_Form -> FG_TABLE_DEFAULT_SENS = "ASC";
$HD_Form ->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form ->FG_LIST_ADDING_BUTTON_LINK1 = "A2B_currencies.php?form_action=ask-add&section=".$_SESSION["menu_section"];
$HD_Form ->FG_LIST_ADDING_BUTTON_ALT1 = $HD_Form ->FG_LIST_ADDING_BUTTON_MSG1 = gettext("Add Rate");
$HD_Form ->FG_LIST_ADDING_BUTTON_IMG1 = Images_Path ."/world_add.png" ;


$HD_Form -> AddViewElement(gettext("CURRENCY"), "currency");
$HD_Form -> AddViewElement(gettext("DETAIL"), "name");
$HD_Form -> AddViewElement(gettext("VALUE"), "value");

$HD_Form -> FieldViewElement ('currency,name,value');

$HD_Form -> FG_VIEW_TABLE_WITDH = '70%';

$HD_Form -> FG_ENABLE_EDIT_BUTTON = true;
$HD_Form -> FG_ENABLE_DELETE_BUTTON = true;
$HD_Form -> FG_ENABLE_ADD_BUTTON = true;

$HD_Form -> AddEditElement (gettext("Name"),
	"name",
	"$value",
	"INPUT",
	"size=35 maxlength=30",
	"3",
	gettext("Insert the currency name"),
	"" , "", "", "", "" , "" , "" , "");

$HD_Form -> AddEditElement (gettext("LABEL"),
	"currency",
	"$value",
	"INPUT",
	"size=3 maxlength=3",
	"0",
	gettext("Insert the currency label"),
	"" , "", "", "", "" , "" , "" , "");


$HD_Form -> AddEditElement (gettext("VALUE"),
	"value",
	'$value',
	"INPUT",
	"size=30 maxlength=30",
	"12",
	gettext("Insert the fee amount."),
	"" , "", "", "", "" , "", "", gettext("Set the value of this currency in your base currency that the user have to pay per month.")." ".gettext("Base currency :")." ".strtoupper(BASE_CURRENCY));


// Set the filter variables
$HD_Form -> FG_FILTER_ENABLE = true;
$HD_Form -> FG_FILTER_COLUMN = 'currency';
$HD_Form -> FG_FILTER_LABEL = 'currency';


if (isset($filterprefix)  &&  (strlen($filterprefix)>0)) {
	$filterprefix = strtoupper($filterprefix);
	if (strlen($HD_Form -> FG_QUERY_WHERE_CLAUSE)>0) $HD_Form -> FG_QUERY_WHERE_CLAUSE.=" AND ";
	$HD_Form -> FG_QUERY_WHERE_CLAUSE.="currency like '$filterprefix%'";
}



$HD_Form -> FieldEditElement ('name, currency,value');

$HD_Form->FG_ADD_QUERY_HIDDEN_INPUTS["basecurrency"] = strtoupper(BASE_CURRENCY);





$HD_Form -> FG_INTRO_TEXT_ADITION = '';


$HD_Form -> FG_BUTTON_EDITION_BOTTOM_TEXT = $HD_Form -> FG_BUTTON_ADITION_BOTTOM_TEXT = gettext("Click 'Confirm Data' to continue.");

$HD_Form -> FG_ADDITIONAL_FUNCTION_AFTER_ADD = 'processing_refill_add';

$HD_Form -> FG_LOCATION_AFTER_ADD = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?atmenu=document&stitle=Document&wh=AC&id=";
$HD_Form -> FG_LOCATION_AFTER_EDIT = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?atmenu=document&stitle=Document&wh=AC&id=";
$HD_Form -> FG_LOCATION_AFTER_DELETE = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?atmenu=document&stitle=Document&wh=AC&id=";



