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

getpost_ifset(array('id', 'tariffgroupname', 'lcrtype', 'removeinterprefix', 'idtariffplan', 'idtariffplan_hidden', 'id_cc_package_offer', 'popup_select'));

$HD_Form = new FormHandler("cc_tariffgroup", "Call Plan");

$HD_Form->no_debug();
$HD_Form -> FG_TABLE_DEFAULT_ORDER = " tariffgroupname";
$HD_Form -> FG_TABLE_DEFAULT_SENS = "DESC";


$HD_Form ->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form ->FG_LIST_ADDING_BUTTON_LINK1 = "A2B_entity_tariffgroup.php?form_action=ask-add";
$HD_Form ->FG_LIST_ADDING_BUTTON_ALT1 = $HD_Form ->FG_LIST_ADDING_BUTTON_MSG1 = gettext("Add Callplan");
$HD_Form ->FG_LIST_ADDING_BUTTON_IMG1 = Images_Path ."/server_add.png" ;


// Code Here for Deleting the Dependent Records
// Dependent Tables
$HD_Form -> FG_FK_DELETE_ALLOWED = true;
$HD_Form -> FG_FK_DELETE_CONFIRM = true;
$HD_Form -> FG_FK_WARNONLY = true;
$HD_Form -> FG_FK_TABLENAMES = array("cc_card");
$HD_Form -> FG_FK_EDITION_CLAUSE = array(" tariff ");

$HD_Form -> FG_FK_DELETE_MESSAGE = gettext("You have some cards using this Call Plan! Please comfirm that you really want to remove this Call Plan ? ");

// LCD :the cheapest Provider for a given destination number
$lcrtype_list = [[_("LCR : According to the buyer price"), 0], [_("LCD : According to the seller price"), 1]];

$lcrtype_list_short = [[_("LCR : buyer price"), 0], [_("LCD : seller price"), 1]];

$inter_prefix_list_short = array();
$inter_prefix_list_short["0"] = array( gettext("Keep prefix"), "0");
$inter_prefix_list_short["1"] = array( gettext("Remove prefix"), "1");

$HD_Form -> AddViewElement(gettext("ID"), "id");
$HD_Form -> AddViewElement(gettext("NAME"), "tariffgroupname");
if (!$popup_select) {
    $HD_Form->AddViewElement(gettext("CREATIONDATE"), "creationdate", true, 19, "display_dateformat");
}
$HD_Form -> AddViewElement(gettext("LC TYPE"), "lcrtype", true, 0, "", "list", $lcrtype_list_short);
$HD_Form -> AddViewElement(gettext("PACKAGE"), "id_cc_package_offer", true, 15, "", "lie", "cc_package_offer", "label", "id='%id'", "%1");
$HD_Form -> AddViewElement(gettext("INTER PREFIX"), "removeinterprefix", true, 0, "", "list", $inter_prefix_list_short);

if (!$popup_select) {
	$HD_Form -> FieldViewElement ('id, tariffgroupname, creationdate, lcrtype,id_cc_package_offer, removeinterprefix');
} else {
	$HD_Form -> FieldViewElement ('id, tariffgroupname, lcrtype,id_cc_package_offer, removeinterprefix');
}

if ($popup_select) {
	$HD_Form -> FG_LIST_VIEW_PAGE_SIZE = 7;
	$HD_Form -> CV_FOLLOWPARAMETERS = "&popup_select=" . $popup_select . "&popup_formname=" . $popup_formname . "&popup_fieldname=" . $popup_fieldname;

	$HD_Form -> FG_OTHER_BUTTON1 = true;
	$HD_Form -> FG_OTHER_BUTTON1_ALT = '<font color="red">&lt;select&gt;</font>';
	$HD_Form -> FG_OTHER_BUTTON1_IMG = '';

	//echo $popup_select;
	if ($popup_select == 1)
		$HD_Form -> FG_OTHER_BUTTON1_LINK = "javascript:sendValue('|param|');";
	elseif ($popup_select == 2)
		$HD_Form -> FG_OTHER_BUTTON1_LINK = "javascript:sendValue('|col0|');";
} else {
	$HD_Form -> FG_ENABLE_EDIT_BUTTON = true;
	$HD_Form -> FG_ENABLE_DELETE_BUTTON = true;
	$HD_Form -> FG_ENABLE_ADD_BUTTON = true;
}


$HD_Form->AddEditElement(
    gettext("NAME"),
    "tariffgroupname",
    "",
    "size=30 maxlength=20",
    9,
    gettext("Insert the Call Plan name ")
);

$HD_Form->AddEditSelect("lcrtype", $lcrtype_list, gettext("LC TYPE"));

$HD_Form->AddEditSqlSelect(
    "id_cc_package_offer",
    gettext("PACKAGE"),
    "cc_package_offer",
    "label,id",
    "",
    "",
    '<option value="-1" selected="selected">' . gettext("NOT DEFINED") . '</option>',
    gettext("Set the Package Group offer if you wish to use one with this Call Plan")
);

$HD_Form->AddEditRadio(
    "removeinterprefix",
    [[gettext("Yes"), "1"], [gettext("No"), "0"]],
    gettext("REMOVE INTER PREFIX"),
    "0",
    gettext("Remove the international access prefix (00 or 011) before matching the dialled digits with the rate card. E.G. If the dialled digits were 0044 for a call to the UK, only 44 would be delivered."),
    gettext("Define if the international prefix will be removed to check the rate.")
);

$DBHandle_max  = DbConnect();
$numrow = 0;
$resmax = $DBHandle_max -> Execute("SELECT * FROM cc_tariffplan");
if ( $resmax )
	$numrow = $resmax -> RecordCount( );

if ($numrow>0){
    $HD_Form->AddEditSqlSelect(
        "tariffplan",
        gettext("RATECARD"),
        "cc_tariffgroup_plan",
        "label,id",
        "",
        "",
        "",
        "",
        "%1 : (%2)",
        "size='10' multiple='muliple'",
        [
            "name" => "idtariffplan",
            "tables" => "cc_tariffplan LEFT JOIN cc_tariffgroup_plan ON cc_tariffplan.id=cc_tariffgroup_plan.idtariffplan",
            "columns" => "DISTINCT cc_tariffplan.tariffname, cc_tariffplan.id",
            "where" => "cc_tariffgroup_plan.idtariffgroup = %id",
            "format" => "%2 - (%1)",
        ]
    );

}


$HD_Form -> FieldEditElement ('tariffgroupname, lcrtype, id_cc_package_offer, removeinterprefix');


$HD_Form -> FG_INTRO_TEXT_EDITION= '';



$HD_Form -> FG_INTRO_TEXT_ADITION = '';


$HD_Form -> FG_LOCATION_AFTER_ADD = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";
$HD_Form -> FG_LOCATION_AFTER_EDIT = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";
$HD_Form -> FG_LOCATION_AFTER_DELETE = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";




