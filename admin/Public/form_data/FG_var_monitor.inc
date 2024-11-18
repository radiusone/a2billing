<?php

use A2billing\A2Billing;
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

$HD_Form = new FormHandler("cc_monitor", "Monitoring");

$HD_Form->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form->FG_LIST_ADDING_BUTTON_LINK1 = "A2B_entity_monitor.php?form_action=ask-add";
$HD_Form->FG_LIST_ADDING_BUTTON_ALT1 = $HD_Form->FG_LIST_ADDING_BUTTON_MSG1 = _("Add Monitoring");
$HD_Form->FG_LIST_ADDING_BUTTON_IMG1 = get_image_path("map.png") ;

$mquery_type_list = [[], [_("SQL"), "1"], [_("SHELL SCRIPT"), "2"]];
$mresult_type_list = [[], [_("TEXT2SPEECH"), "1"], [_("UNIXTIME"), "2"], [_("NUMBER"), "3"], [_("DIGIT"), "4"]];
$actived_list = getActivationList() ;

$HD_Form->AddViewElement(_("LABEL"), "label");
$HD_Form->AddViewElement(_("DIAL CODE"), "dial_code");
$HD_Form->AddViewElement(_("DESCRIPTION"), "description", true, 70);
$HD_Form->AddViewElement(_("TEXT"), "text_intro", true, 70);
$HD_Form->AddViewElement(_("ENABLE"), "enable", true, 30, "", "list", $actived_list);

$HD_Form->FieldViewElement ('label, dial_code, description, text_intro, enable');

$HD_Form->FG_ENABLE_EDIT_BUTTON = true;
$HD_Form->FG_ENABLE_DELETE_BUTTON = true;
$HD_Form->FG_ENABLE_ADD_BUTTON = true;

$HD_Form->AddEditElement(
    _("LABEL"),
    "label",
    "",
    "size=30 maxlength=50",
    3,
    _("Insert the label")
);

$HD_Form->AddEditElement(
    _("DIAL CODE"),
    "dial_code",
    "",
    "size=5 maxlength=3",
    4,
    _("Insert the dial code")
);

$HD_Form->AddEditTextarea(
    _("DESCRIPTION"),
    "description",
    "",
    "cols='60' rows='2'", null, _("Insert the description")
);

$HD_Form->AddEditElement(
    _("TEXT INTRO"),
    "text_intro",
    "",
    "size=30 maxlength=50",
    3,
    _("Insert the text intro")
);

$HD_Form->AddEditSelect(
    _("QUERY TYPE"),
    "query_type",
    $mquery_type_list,
    "",
    _("Define type of the query that will be performed.")
);

$HD_Form->AddEditTextarea(
    _("QUERY / SHELL SCRIPT"),
    "query",
    sprintf(_("Shell scripts need to be copied into the directory %s."), A2Billing::SCRIPT_CONFIG_DIR) . "<br>" . _("Do not specify the path here."),
    "cols='80' rows='4'",
    null,
    _("Insert the description")
);

$HD_Form->AddEditSelect(
    _("SAY RESULT AS"),
    "result_type",
    $mresult_type_list,
    "",
    _("Define type of result.")
);

$HD_Form->AddEditRadio(
    _("ENABLE"),
    "enable",
    getYesNoList(),
    "0"
);

$HD_Form->FG_INTRO_TEXT_ADITION = _("Add the Monitoring below");

$HD_Form->FG_LOCATION_AFTER_ADD = "?id=";
$HD_Form->FG_LOCATION_AFTER_EDIT = "?id=";
$HD_Form->FG_LOCATION_AFTER_DELETE = "?id=";
