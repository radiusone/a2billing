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

getpost_ifset(array('id', 'id_card', 'date'));

if (!isset($form_action) || $form_action=="list") {
    $HD_Form = new FormHandler("cc_billing_customer LEFT JOIN cc_card ON cc_card.id=id_card  ", "Billing");
} else {
    $HD_Form = new FormHandler("cc_billing_customer", "Billing");
}

$HD_Form -> FG_QUERY_PRIMARY_KEY = 'cc_billing_customer.id';

$HD_Form->no_debug();
$HD_Form -> FG_TABLE_DEFAULT_SENS = "DESC";

$HD_Form ->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form ->FG_LIST_ADDING_BUTTON_LINK1 = "A2B_entity_billing_customer.php?form_action=ask-add&section=".$_SESSION["menu_section"];
$HD_Form ->FG_LIST_ADDING_BUTTON_ALT1 = $HD_Form ->FG_LIST_ADDING_BUTTON_MSG1 = gettext("Add Billing");
$HD_Form ->FG_LIST_ADDING_BUTTON_IMG1 = Images_Path ."/time_add.png" ;

$HD_Form -> AddViewElement(gettext("ID"), "cc_billing_customer.id");
$HD_Form -> AddViewElement(gettext("ACCOUNT"), "username", true, 30, "display_customer_link");
$HD_Form -> AddViewElement(gettext("BILLING DATE"), "cc_billing_customer.date", true, 30, "display_dateformat");
$HD_Form -> FieldViewElement ('cc_billing_customer.id, username, cc_billing_customer.date');

$HD_Form -> FG_ENABLE_ADD_BUTTON = true;
$HD_Form -> FG_ENABLE_DELETE_BUTTON = true;

$HD_Form->AddEditPopup(
    "id_card",
    gettext("CUSTOMER ID"),
    "A2B_entity_card.php?popup_select=1&",
    gettext("Select the Customer ID"),
    gettext("Insert the Customer ID")
);

if ($form_action=="ask-add"){
    $comp_date = date("Y-m-d");
    $comp_date="value='$comp_date'";
}

$HD_Form->AddEditElement(
    gettext("BILLING DATE"),
    "date",
    "",
    "size=40 maxlength=40 $comp_date",
    8,
    gettext("Insert the current date (YYYY-MM-DD)")
);

$HD_Form -> FieldEditElement ('id_card, date');

$HD_Form -> FG_FILTER_ENABLE = true;
$HD_Form -> FG_FILTER_COLUMN = 'cardnumber';
$HD_Form -> FG_FILTER_LABEL = 'Account number';

if (isset($filterprefix)  &&  (strlen($filterprefix)>0)){
    if (strlen($HD_Form -> FG_QUERY_WHERE_CLAUSE)>0) $HD_Form -> FG_QUERY_WHERE_CLAUSE.=" AND ";
    $HD_Form -> FG_QUERY_WHERE_CLAUSE.="username like '$filterprefix%'";
}


$HD_Form -> FG_INTRO_TEXT_ADITION = '';

$HD_Form -> FG_ADDITIONAL_FUNCTION_AFTER_ADD = 'proccessing_billing_customer';

$HD_Form -> FG_LOCATION_AFTER_ADD = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?atmenu=document&stitle=Document&wh=AC&id=";
$HD_Form -> FG_LOCATION_AFTER_DELETE = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?atmenu=document&stitle=Document&wh=AC&id=";
