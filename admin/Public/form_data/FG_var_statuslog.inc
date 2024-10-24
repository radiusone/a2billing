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

getpost_ifset(array('id', 'status', 'id_cc_card', 'updated_date', 'Period', 'frommonth', 'fromstatsmonth', 'tomonth', 'tostatsmonth', 'fromday', 'fromstatsday_sday', 'fromstatsmonth_sday', 'today', 'tostatsday_sday', 'tostatsmonth_sday', 'status', 'current_page', 'order', 'sens','popup_select'));


$HD_Form = new FormHandler("cc_status_log sl LEFT JOIN cc_card ON cc_card.id = sl.id_cc_card", gettext("Status Log"), "sl.id");

$HD_Form->no_debug();
$HD_Form -> FG_TABLE_DEFAULT_ORDER = "updated_date";
$HD_Form -> FG_TABLE_DEFAULT_SENS = "DESC";


$date_clause = "";
if (DB_TYPE == "postgres") {
	$UNIX_TIMESTAMP = "";
} else {
	$UNIX_TIMESTAMP = "UNIX_TIMESTAMP";
}

$lastdayofmonth = date("t", strtotime(($tostatsmonth ?? "2099-12").'-01'));
normalize_day_of_month($fromstatsday_sday, $fromstatsmonth_sday);
normalize_day_of_month($tostatsday_sday, $tostatsmonth_sday);
if ($Period=="Month"){
	if ($frommonth && isset($fromstatsmonth)) $date_clause.=" $UNIX_TIMESTAMP(sl.updated_date) >= $UNIX_TIMESTAMP('$fromstatsmonth-01')";
	if ($tomonth && isset($tostatsmonth)) {
		if (strlen($date_clause)>0) $date_clause.=" AND ";
		$date_clause.=" $UNIX_TIMESTAMP(sl.updated_date) <= $UNIX_TIMESTAMP('".$tostatsmonth."-$lastdayofmonth 23:59:59')";
	}
    $HD_Form->list_query_conditions[] = [
        "SUB",
        [
            ["sl.updated_date" => [">=", sprintf("%s-01", $fromstatsmonth ?? "1900-01")]],
            ["sl.updated_date" => ["<=", sprintf("%s-%s 23:59:59", $tostatsmonth ?? "2099-12", $lastdayofmonth)]],
        ],
    ];
} else {
	if ($fromday && isset($fromstatsday_sday) && isset($fromstatsmonth_sday)) {
		if (strlen($date_clause)>0) $date_clause.=" AND ";
		$date_clause.=" $UNIX_TIMESTAMP(sl.updated_date) >= $UNIX_TIMESTAMP('$fromstatsmonth_sday-$fromstatsday_sday')";
	}
	if ($today && isset($tostatsday_sday) && isset($tostatsmonth_sday)) {
		if (strlen($date_clause)>0) $date_clause.=" AND ";
		$date_clause.=" $UNIX_TIMESTAMP(sl.updated_date) <= $UNIX_TIMESTAMP('$tostatsmonth_sday-".sprintf("%02d",intval($tostatsday_sday)/*+1*/)." 23:59:59')";
	}
    $HD_Form->list_query_conditions[] = [
        "SUB",
        [
            ["sl.updated_date" => [">=", sprintf("%s-%02d", $fromstatsmonth_sday ?? "1900-01", $fromstatsday_sday ?? 1)]],
            ["sl.updated_date" => ["<=", sprintf("%s-%02d 23:59:59", $tostatsmonth_sday ?? "2099-12", $tostatsday_sday ?? 31)]],
        ],
    ];
}

if($date_clause != "") {
	$HD_Form -> FG_QUERY_WHERE_CLAUSE.=" ".$date_clause;
}

if ($id_cc_card != "") {
	if (strlen($HD_Form -> FG_QUERY_WHERE_CLAUSE)>0)
		$HD_Form -> FG_QUERY_WHERE_CLAUSE.=" AND ";
	$HD_Form -> FG_QUERY_WHERE_CLAUSE.=" sl.id_cc_card = '$id_cc_card'";
    $HD_Form->list_query_conditions["sl.id_cc_card"] = $id_cc_card;
}

if ($status >= 0) {
	if (strlen($HD_Form -> FG_QUERY_WHERE_CLAUSE)>0)
		$HD_Form -> FG_QUERY_WHERE_CLAUSE.=" AND ";

	$HD_Form -> FG_QUERY_WHERE_CLAUSE.=" sl.status = '$status'";
    $HD_Form->list_query_conditions["sl.status"] = $status;
}

$cardstatus_list = getCardStatus_List();

$HD_Form -> AddViewElement(gettext("ID CARD"), "id_cc_card");
$HD_Form -> AddViewElement(gettext("ACCOUNT NUMBER"), "id_cc_card", true, 0, "", "lie_link", "cc_card", "username,id", "id='%id'", "%1", "A2B_entity_card.php");
$HD_Form -> AddViewElement(gettext("LAST NAME"), "id_cc_card", true, 15, "", "lie", "cc_card", "lastname", "id='%id'", "%1");
$HD_Form -> AddViewElement(gettext("STATUS"), "status", true, 0, "", "list", $cardstatus_list);
$HD_Form -> AddViewElement(gettext("DATE"), "updated_date", true, 16);

$HD_Form -> FieldViewElement ('sl.id_cc_card, id_cc_card, id_cc_card, sl.status, sl.updated_date');

$HD_Form -> CV_NO_FIELDS  = gettext("THERE IS NO STATUS LOG AVAILABLE!");
$HD_Form -> FG_INTRO_TEXT_EDITION = "";
$HD_Form -> FG_ENABLE_EDIT_BUTTON = false;
$HD_Form -> FG_ENABLE_DELETE_BUTTON = false;
$HD_Form -> FG_ENABLE_ADD_BUTTON = false;

$HD_Form -> FG_FILTER_ENABLE = true;
$HD_Form -> FG_FILTER_COLUMN = 'username';
$HD_Form -> FG_FILTER_LABEL = 'Account number';
