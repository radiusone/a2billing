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

getpost_ifset(array('id', 'name', 'periode', 'type', 'maxvalue', 'minvalue', 'status', 'id_trunk', 'numberofrun', 'datelastrun', 'popup_select'));

$HD_Form = new FormHandler("cc_monitor", "Monitoring");

$HD_Form->no_debug();
$HD_Form -> FG_TABLE_DEFAULT_ORDER = "id";
$HD_Form -> FG_TABLE_DEFAULT_SENS = "DESC";



$HD_Form ->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form ->FG_LIST_ADDING_BUTTON_LINK1 = "A2B_entity_monitor.php?form_action=ask-add&section=".$_SESSION["menu_section"];
$HD_Form ->FG_LIST_ADDING_BUTTON_ALT1 = $HD_Form ->FG_LIST_ADDING_BUTTON_MSG1 = gettext("Add Monitoring");
$HD_Form ->FG_LIST_ADDING_BUTTON_IMG1 = Images_Path ."/map.png" ;


// TODO Integrate a generic LIST to Framework

$mquery_type_list = [[], [_("SQL"), "1"], [_("SHELL SCRIPT"), "2"]];
$mresult_type_list = [[], [_("TEXT2SPEECH"), "1"], [_("UNIXTIME"), "2"], [_("NUMBER"), "3"], [_("DIGIT"), "4"]];
$actived_list = getActivationList() ;



$HD_Form -> AddViewElement(gettext("LABEL"), "label");
$HD_Form -> AddViewElement(gettext("DIAL CODE"), "dial_code");
$HD_Form -> AddViewElement(gettext("DESCRIPTION"), "description", true, 70);
$HD_Form -> AddViewElement(gettext("TEXT"), "text_intro", true, 70);
$HD_Form -> AddViewElement(gettext("ENABLE"), "enable", true, 30, "", "list", $actived_list);


$HD_Form -> FieldViewElement ('label, dial_code, description, text_intro, enable');

$HD_Form -> FG_ENABLE_EDIT_BUTTON = true;
$HD_Form -> FG_ENABLE_DELETE_BUTTON = true;
$HD_Form -> FG_ENABLE_ADD_BUTTON = true;

$HD_Form->AddEditElement(
    gettext("LABEL"),
    "label",
    "",
    "size=30 maxlength=50",
    3,
    gettext("Insert the label")
);


$HD_Form->AddEditElement(
    gettext("DIAL CODE"),
    "dial_code",
    "",
    "size=5 maxlength=3",
    4,
    gettext("Insert the dial code")
);


$HD_Form->AddEditTextarea(
    "description",
    gettext("DESCRIPTION"),
    "cols='60' rows='2'",
    gettext("Insert the description")
);

$HD_Form->AddEditElement(
    gettext("TEXT INTRO"),
    "text_intro",
    "",
    "size=30 maxlength=50",
    3,
    gettext("Insert the text intro")
);

$HD_Form->AddEditSelect("query_type", $mquery_type_list, gettext("QUERY TYPE"), gettext("Define type of the query that will be performed."));

$HD_Form->AddEditTextarea(
    "query",
    gettext("QUERY / SHELL SCRIPT"),
    "cols='80' rows='4'",
    gettext("Insert the description"),
    "",
    null,
    gettext("Shell Script need to be copied into the directory") . SCRIPT_CONFIG_DIR . "<br>" . gettext("Do not specify the path here.")
);

$HD_Form->AddEditSelect(
    "result_type",
    $mresult_type_list,
    gettext("SAY RESULT AS"),
    gettext("Define type of result.")
);

$HD_Form->AddEditRadio(
    "enable",
    [[gettext("Yes"), "1"], [gettext("No"), "0"]],
    gettext("ENABLE"),
    "0"
);

$HD_Form -> FieldEditElement ('label, dial_code, description, text_intro, query_type, query, result_type, enable');




$HD_Form -> FG_INTRO_TEXT_ADITION = gettext("Add the Monitoring below");





$HD_Form -> FG_LOCATION_AFTER_ADD = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";
$HD_Form -> FG_LOCATION_AFTER_EDIT = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";
$HD_Form -> FG_LOCATION_AFTER_DELETE = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";


