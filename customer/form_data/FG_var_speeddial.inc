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


getpost_ifset(array('id', 'speeddial', 'id_cc_card', 'name', 'phone','popup_select','popup_formname','popup_fieldname'));

$HD_Form = new FormHandler("cc_speeddial", "speeddial");

$HD_Form->no_debug();
// FAQ
$HD_Form -> FG_TABLE_DEFAULT_ORDER = " creationdate";
$HD_Form -> FG_TABLE_DEFAULT_SENS = "DESC";


$speeddial_list = array();
for ($k=0;$k<=9;$k++)
	$speeddial_list["$k"]  = array( "$k", "$k");


$HD_Form -> AddViewElement(gettext("SPEEDDIAL"), "speeddial");
$HD_Form -> AddViewElement(gettext("PHONE"), "phone");
$HD_Form -> AddViewElement(gettext("NAME"), "name");
$HD_Form -> FieldViewElement ('speeddial, phone, name');

$HD_Form -> FG_VIEW_TABLE_WITDH = '70%';

// My Code for Where Cluase

if (strlen($HD_Form -> FG_QUERY_WHERE_CLAUSE)>0)
    $HD_Form -> FG_QUERY_WHERE_CLAUSE.=" AND ";
	$HD_Form -> FG_QUERY_WHERE_CLAUSE.="id_cc_card =".$_SESSION["card_id"];

if (strlen($HD_Form -> FG_EDIT_QUERY_CONDITION)>0)
    $HD_Form -> FG_EDIT_QUERY_CONDITION.=" AND ";
	$HD_Form -> FG_EDIT_QUERY_CONDITION.="id_cc_card =".$_SESSION["card_id"];


//end My Code for Where Clause

$HD_Form -> FG_ENABLE_EDIT_BUTTON = true;
$HD_Form -> FG_ENABLE_DELETE_BUTTON = true;

$HD_Form->AddEditSelect(gettext("SPEEDDIAL"), "speeddial", $speeddial_list);

$HD_Form->AddEditElement(
    gettext("NAME"),
    "name",
    gettext("Enter here the name/label that will identify this speed dial"),
    "size=40 maxlength=50",
    0,
    gettext("Insert the name")
);


$HD_Form->AddEditElement(
    gettext("PHONE"),
    "phone",
    gettext("Enter here the phonenumber you want to call for this speed dial"),
    "size=30 maxlength=50",
    4,
    gettext("Insert the phone")
);

$HD_Form -> FieldEditElement ('speeddial, name, phone');

$HD_Form -> FG_INTRO_TEXT_ADITION = '';

$HD_Form -> FG_LOCATION_AFTER_ADD = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";
$HD_Form -> FG_LOCATION_AFTER_EDIT = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";
$HD_Form -> FG_LOCATION_AFTER_DELETE = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";

