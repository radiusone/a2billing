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

getpost_ifset(array('id', 'tariffname', 'startingdate', 'expirationdate', 'id_trunk', 'description', 'dnidprefix', 'calleridprefix'));

$HD_Form = new FormHandler("cc_tariffplan", "Ratecard");

$HD_Form->no_debug();
$HD_Form -> FG_TABLE_DEFAULT_ORDER = " tariffname";
$HD_Form -> FG_TABLE_DEFAULT_SENS = "DESC";


$HD_Form ->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form ->FG_LIST_ADDING_BUTTON_LINK1 = "A2B_entity_tariffplan.php?form_action=ask-add";
$HD_Form ->FG_LIST_ADDING_BUTTON_ALT1 = $HD_Form ->FG_LIST_ADDING_BUTTON_MSG1 = gettext("Add RateCard");
$HD_Form ->FG_LIST_ADDING_BUTTON_IMG1 = Images_Path ."/table_multiple.png" ;


// Dependent Tables
$HD_Form -> FG_FK_DELETE_ALLOWED = true;
$HD_Form -> FG_FK_DELETE_CONFIRM = true;
$HD_Form -> FG_FK_TABLENAMES = array("cc_ratecard");
$HD_Form -> FG_FK_EDITION_CLAUSE = array(" idtariffplan ");
$HD_Form -> FG_FK_DELETE_MESSAGE = gettext("You are going as well to remove all the rates attached to this ratecard! Please comfirm that you really want to remove this ratecard and those rates ? ");


$HD_Form -> AddViewElement(gettext("ID"), "id");
$HD_Form -> AddViewElement(gettext("TARIFFNAME"), "tariffname");
$HD_Form -> AddViewElement(gettext("START DATE"), "startingdate", true, 16, "display_dateformat");
$HD_Form -> AddViewElement(gettext("EXPIRY DATE"), "expirationdate", true, 16, "display_dateformat");
$HD_Form -> AddViewElement(gettext("TRUNK"), "id_trunk", true, 15, "", "lie", "cc_trunk", "trunkcode", "id_trunk='%id'", "%1");
$HD_Form -> AddViewElement(gettext("MINUTES USED"), "secondusedreal", true, 30, "display_minute");
$HD_Form -> AddViewElement(gettext("DNID PREFIX"), "dnidprefix");
$HD_Form -> AddViewElement(gettext("CID PREFIX"), "calleridprefix");

$HD_Form -> FieldViewElement ('id, tariffname, startingdate, expirationdate, id_trunk, secondusedreal, dnidprefix, calleridprefix');

$HD_Form -> FG_ENABLE_EDIT_BUTTON = true;
$HD_Form -> FG_ENABLE_DELETE_BUTTON = true;
$HD_Form -> FG_ENABLE_ADD_BUTTON = true;

$HD_Form->AddEditElement(
    gettext("TARIFFNAME"),
    "tariffname",
    "",
    "size=40 maxlength=30",
    3,
    gettext("Insert the tariff name ")
);

if ($form_action=="ask-add"){
	$begin_date = date("Y");
	$begin_date_plus = date("Y") + 10;
	$end_date = date("-m-d H:i:s");
	$comp_date = "value='".$begin_date.$end_date."'";
	$comp_date_plus = "value='".$begin_date_plus.$end_date."'";
}

$HD_Form->AddEditElement(
    gettext("START DATE"),
    "startingdate",
    gettext("Please use the format YYYY-MM-DD HH:MM:SS. For instance, '2004-12-31 00:00:00'"),
    "size=40 maxlength=40 $comp_date",
    10,
    gettext("Insert the starting date"),
    "",
    "",
    "res_display_dateformat"
);

$HD_Form->AddEditElement(
    gettext("EXPIRY DATE"),
    "expirationdate",
    gettext("Format YYYY-MM-DD HH:MM:SS. For instance, '2004-12-31 00:00:00'"),
    "size=40 maxlength=40  $comp_date_plus",
    10,
    gettext("Insert the expiration date"),
    "",
    "",
    "res_display_dateformat"
);

$HD_Form->AddEditSqlSelect("id_trunk", gettext("TRUNK"), "cc_trunk", "trunkcode,id_trunk");

$HD_Form->AddEditTextarea(
    gettext("DESCRIPTION"),
    "description",
    "",
    "cols='50' rows='4'", null, gettext("Insert the description")
);

if ($form_action=="ask-add"){
	$val_dnidprefix = "value='all'";
	$val_calleridprefix = "value='all'";
}

$HD_Form->AddEditElement(
    gettext("DNID PREFIX"),
    "dnidprefix",
    gettext("Set the DNID rules to choose the ratecard 'dnidprefix', by default, matches all DNID. For instance, Set the DNIDPrefix  to 900540540 to choose this ratecard when the DNID is 900540540"),
    "size=20 $val_dnidprefix maxlength=20",
    14,
    gettext("Insert the dnid prefix")
);


$HD_Form->AddEditElement(
    gettext("CALLERID PREFIX"),
    "calleridprefix",
    gettext("Set the CallerID rules to choose the ratecard 'calleridprefix', by default, matches all callerID. For instance, Set the calleridprefix to 900540540 to choose this ratecard when the CallerID is 900540540."),
    "size=20 $val_calleridprefix maxlength=20",
    14,
    gettext("Insert the dnid prefix")
);


$HD_Form -> FieldEditElement ('tariffname, startingdate, expirationdate, id_trunk, description, dnidprefix, calleridprefix');

$HD_Form -> FG_INTRO_TEXT_ADITION = '';

$HD_Form -> FG_LOCATION_AFTER_ADD = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";
$HD_Form -> FG_LOCATION_AFTER_EDIT = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";
$HD_Form -> FG_LOCATION_AFTER_DELETE = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";


