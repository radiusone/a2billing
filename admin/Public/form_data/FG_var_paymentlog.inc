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

if (! has_rights (Admin::ACX_ADMINISTRATOR)) {
    Header ("HTTP/1.0 401 Unauthorized");
    Header ("Location: PP_error.php?c=accessdenied");
    die();
}


getpost_ifset(array('id', 'status', 'Period', 'frommonth', 'fromstatsmonth', 'tomonth', 'tostatsmonth', 'fromday', 'fromstatsday_sday', 'fromstatsmonth_sday', 'today', 'tostatsday_sday', 'tostatsmonth_sday', 'current_page', 'order', 'sens','popup_select'));

$HD_Form = new FormHandler("cc_epayment_log");

$HD_Form->no_debug();
$HD_Form -> FG_TABLE_DEFAULT_ORDER = "creationdate";
$HD_Form -> FG_TABLE_DEFAULT_SENS = "DESC";

$date_clause = "";

if (DB_TYPE == "postgres") {
	 	$UNIX_TIMESTAMP = "";
}else{
		$UNIX_TIMESTAMP = "UNIX_TIMESTAMP";
}
$lastdayofmonth = date("t", strtotime($tostatsmonth.'-01'));
normalize_day_of_month($fromstatsday_sday, $fromstatsmonth_sday);
normalize_day_of_month($tostatsday_sday, $tostatsmonth_sday);
if ($Period=="Month"){
	if ($frommonth && isset($fromstatsmonth)) $date_clause.=" $UNIX_TIMESTAMP(creationdate) >= $UNIX_TIMESTAMP('$fromstatsmonth-01')";
	if ($tomonth && isset($tostatsmonth))
	{
		if (strlen($date_clause)>0) $date_clause.=" AND ";
		$date_clause.=" $UNIX_TIMESTAMP(creationdate) <= $UNIX_TIMESTAMP('".$tostatsmonth."-$lastdayofmonth 23:59:59')";
	}
}else{
	if ($fromday && isset($fromstatsday_sday) && isset($fromstatsmonth_sday))
	{
		if (strlen($date_clause)>0) $date_clause.=" AND ";
		$date_clause.=" $UNIX_TIMESTAMP(creationdate) >= $UNIX_TIMESTAMP('$fromstatsmonth_sday-$fromstatsday_sday')";
	}
	if ($today && isset($tostatsday_sday) && isset($tostatsmonth_sday))
	{
		if (strlen($date_clause)>0) $date_clause.=" AND ";
		$date_clause.=" $UNIX_TIMESTAMP(creationdate) <= $UNIX_TIMESTAMP('$tostatsmonth_sday-".sprintf("%02d",intval($tostatsday_sday)/*+1*/)." 23:59:59')";
	}
}

if (strlen($HD_Form -> FG_QUERY_WHERE_CLAUSE)>0 && strlen($enteradmin)>0) $HD_Form -> FG_QUERY_WHERE_CLAUSE.=" AND ";
if($enteradmin != "")
{
	$HD_Form -> FG_QUERY_WHERE_CLAUSE.=" iduser = '$enteradmin'";
}
if (strlen($enteradmin)>0 && strlen($loglevel)>0 && $loglevel > 0) $HD_Form -> FG_QUERY_WHERE_CLAUSE.=" AND ";
if($status >= 0 && $status != '')
{
	$HD_Form -> FG_QUERY_WHERE_CLAUSE.=" status = '$status'";
}
if (strlen($loglevel)>0 && strlen($date_clause)>0) $HD_Form -> FG_QUERY_WHERE_CLAUSE.=" AND ";
if($date_clause != "")
{
	if (strlen($HD_Form -> FG_QUERY_WHERE_CLAUSE)>0) $date_clause =" AND ".$date_clause;
	$HD_Form -> FG_QUERY_WHERE_CLAUSE.=" ".$date_clause;
}


$status_list = getPaymentStateList();


$HD_Form -> AddViewElement(gettext("Id"), "ID");
$HD_Form -> AddViewElement(gettext("Created At"), "creationdate");
$HD_Form -> AddViewElement(gettext("Payment Method"), "paymentmethod");
$HD_Form -> AddViewElement(gettext("Status"), "status", true, 0, "", "list", $status_list);
$HD_Form -> AddViewElement(gettext("Owner"), "cc_owner");
$HD_Form -> AddViewElement(gettext("Amount"), "amount");
$HD_Form -> AddViewElement(gettext("CardID"), "cardid");
$HD_Form -> AddViewElement(gettext("Cardnumber"), "cardid", true, 0, "", "lie_link", "cc_card", "username,id", "id='%id'", "%1", "A2B_entity_card.php");

// added a parameter to append  FG_TABLE_ID  ( by default ) or disable 0.
$HD_Form -> FieldViewElement ('id, creationdate, paymentmethod, status, cc_owner, amount, cardid, cardid ');

$HD_Form -> FG_LIST_VIEW_PAGE_SIZE = 25;

$HD_Form -> FG_ENABLE_EDIT_BUTTON = true;
$HD_Form -> FG_ENABLE_DELETE_BUTTON = true;
$HD_Form -> FG_ENABLE_ADD_BUTTON = true;

$HD_Form->AddEditElement(
    gettext("ID"),
    "id",
    gettext("Select the ID"),
    "size=10 READONLY maxlength=10",
    4,
    gettext("Insert the Id ")
);


$HD_Form->AddEditElement(
    gettext("DATE"),
    "creationdate",
    "",
    "size=40 READONLY maxlength=40 ",
    10,
    gettext("Insert the creation date")
);


$HD_Form->AddEditElement(
    gettext("PAYMENT METHOD"),
    "paymentmethod",
    gettext("Enter the payment method "),
    "size=30 maxlength=30",
    12,
    gettext("Insert the payment method")
);


$HD_Form->AddEditSelect("status", $status_list, gettext("STATUS"));

$HD_Form->AddEditElement(
    gettext("OWNER"),
    "cc_owner",
    gettext("Enter the owner"),
    "size=30 maxlength=30",
    12,
    gettext("Insert the owner")
);


$HD_Form->AddEditElement(
    gettext("AMOUNT"),
    "amount",
    gettext("Enter the amount "),
    "size=30 maxlength=30",
    12,
    gettext("Insert the amount")
);

$HD_Form->AddEditPopup(
    "card_id",
    gettext("CARD ID"),
    "A2B_entity_card.php?popup_select=1&",
    gettext("Define the card number ID to use."),
    gettext("Insert the Id CardNumber"),
);

$HD_Form -> FieldEditElement ('id, creationdate, paymentmethod, status, cc_owner, amount, cardid ');


$HD_Form -> FG_FILTER_ENABLE = true;
$HD_Form -> FG_FILTER_COLUMN = 'cardid';
$HD_Form -> FG_FILTER_LABEL = 'Card ID';



$HD_Form -> FG_INTRO_TEXT_EDITION= '';

$HD_Form -> FG_LOCATION_AFTER_EDIT = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";


