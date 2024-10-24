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

getpost_ifset(array('id', 'loglevel', 'enteradmin', 'Period', 'frommonth', 'fromstatsmonth', 'tomonth', 'tostatsmonth', 'fromday', 'fromstatsday_sday', 'fromstatsmonth_sday', 'today', 'tostatsday_sday', 'tostatsmonth_sday', 'current_page', 'order', 'sens','popup_select'));

$HD_Form = new FormHandler("cc_system_log csl LEFT JOIN cc_ui_authen cua ON csl.iduser = cua.userid", "Log");

$HD_Form->no_debug();
$HD_Form -> FG_TABLE_DEFAULT_ORDER = "creationdate";
$HD_Form -> FG_TABLE_DEFAULT_SENS = "DESC";

$condition_ary = array();
$lastdayofmonth = date("t", strtotime(($tostatsmonth ?? "2099-12").'-01'));
normalize_day_of_month($fromstatsday_sday, $fromstatsmonth_sday);
normalize_day_of_month($tostatsday_sday, $tostatsmonth_sday);
if ($Period=="Month"){
	if ($frommonth && isset($fromstatsmonth)) $condition_ary[] = "UNIX_TIMESTAMP(csl.creationdate) >= UNIX_TIMESTAMP('$fromstatsmonth-01')";
	if ($tomonth && isset($tostatsmonth))
	{
		$condition_ary[] = "UNIX_TIMESTAMP(csl.creationdate) <= UNIX_TIMESTAMP('".$tostatsmonth."-$lastdayofmonth 23:59:59')";
	}
    $HD_Form->list_query_conditions[] = [
        "SUB",
        [
            ["csl.creationdate" => [">=", sprintf("%s-01", $fromstatsmonth ?? "1900-01")]],
            ["csl.creationdate" => ["<=", sprintf("%s-%s 23:59:59", $tostatsmonth ?? "2099-12", $lastdayofmonth)]],
        ],
    ];
}else{
	if ($fromday && isset($fromstatsday_sday) && isset($fromstatsmonth_sday))
	{
		$condition_ary[] = "UNIX_TIMESTAMP(csl.creationdate) >= UNIX_TIMESTAMP('$fromstatsmonth_sday-$fromstatsday_sday')";
	}
	if ($today && isset($tostatsday_sday) && isset($tostatsmonth_sday))
	{
		$condition_ary[] = " UNIX_TIMESTAMP(csl.creationdate) <= UNIX_TIMESTAMP('$tostatsmonth_sday-".sprintf("%02d",intval($tostatsday_sday)/*+1*/)." 23:59:59')";
	}
    $HD_Form->list_query_conditions[] = [
        "SUB",
        [
            ["csl.creationdate" => [">=", sprintf("%s-%02d", $fromstatsmonth_sday ?? "1900-01", $fromstatsday_sday ?? 1)]],
            ["csl.creationdate" => ["<=", sprintf("%s-%02d 23:59:59", $tostatsmonth_sday ?? "2099-12", $tostatsday_sday ?? 31)]],
        ],
    ];
}

if($enteradmin != "")
{
	$condition_ary[] = "csl.iduser = '$enteradmin'";
    $HD_Form->list_query_conditions["csl.iduser"] = $enteradmin;
}
if($loglevel != "" && $loglevel > 0)
{
	$condition_ary[] = " csl.loglevel = '$loglevel'";
    $HD_Form->list_query_conditions["csl.loglevel"] = $loglevel;
}

$HD_Form -> FG_QUERY_WHERE_CLAUSE = implode(' AND ', $condition_ary);

//$HD_Form -> FG_EDITION_CLAUSE = " id = $id";

$HD_Form -> AddViewElement(gettext("ID"), "id");
$HD_Form -> AddViewElement(gettext("LOGIN"), "login");
$HD_Form -> AddViewElement(gettext("LOGLEVEL"), "loglevel");
$HD_Form -> AddViewElement(gettext("ACTION"), "action");
$HD_Form -> AddViewElement(gettext("description"), "description");
$HD_Form -> AddViewElement(gettext("TABLENAME"), "tablename", true, 15);
$HD_Form -> AddViewElement(gettext("PAGENAME"), "pagename");
$HD_Form -> AddViewElement(gettext("IPADDRESS"), "ipaddress");
$HD_Form -> AddViewElement(gettext("DATE"), "creationdate", true, 16);

// added a parameter to append  FG_TABLE_ID  ( by default ) or disable 0.
$HD_Form -> FieldViewElement ('id, cua.login, loglevel, action, description, tablename, pagename, ipaddress, creationdate');
$HD_Form -> CV_NO_FIELDS  = gettext("THERE IS NO LOG AVAILABLE1!");
$HD_Form -> FG_INTRO_TEXT_EDITION = "";
$HD_Form -> FG_ENABLE_EDIT_BUTTON = false;
$HD_Form -> FG_ENABLE_DELETE_BUTTON = false;
$HD_Form -> FG_ENABLE_ADD_BUTTON = false;
$HD_Form -> FG_ENABLE_INFO_BUTTON = true;
$HD_Form -> FG_INFO_BUTTON_LINK = "A2B_log_activity_info.php?id=";
