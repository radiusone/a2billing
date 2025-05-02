<?php

use A2billing\A2Billing;
use A2billing\Admin;
use A2billing\Forms\FormHandler;
use A2billing\Forms\Validator;

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

$HD_Form = new FormHandler("cc_monitor", _("Monitoring"));

$HD_Form->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form->FG_LIST_ADDING_BUTTON_IMG1 = get_image_path("map.png") ;

$HD_Form->help_text = create_help(
    _("IVR Monitoring, an extension should be configured for the admin to call and monitor through an IVR some important data from your system.")
);

$mquery_type_list = [1 => _("SQL"), 2 => _("SHELL SCRIPT")];
$mresult_type_list = [1 => _("TEXT2SPEECH"), 2 => _("UNIXTIME"), 3 => _("NUMBER"), 4 => _("DIGIT")];
$actived_list = getActivationList() ;

$HD_Form->AddListValue(_("Label"), "label");
$HD_Form->AddListValue(_("Dial code"), "dial_code");
$HD_Form->AddListValue(_("Description"), "description", "substr", ["%X", 0, 70]);
$HD_Form->AddListValue(_("Text"), "text_intro", "substr", ["%X", 0, 70]);
$HD_Form->AddListMapping(_("ENABLE"), "enable", $actived_list);

$HD_Form->FG_ENABLE_EDIT_BUTTON = true;
$HD_Form->FG_ENABLE_DELETE_BUTTON = true;
$HD_Form->FG_ENABLE_ADD_BUTTON = true;

$HD_Form->AddEditElement(
    _("LABEL"),
    "label",
    "",
    ["maxlength" => 50],
    [Validator::class, "min4Chars"],
    _("Insert the label")
);

$HD_Form->AddEditElement(
    _("DIAL CODE"),
    "dial_code",
    "",
    ["maxlength" => 3],
    [Validator::class, "numeric"],
    _("Insert the dial code")
);

$HD_Form->AddEditTextarea(
    _("DESCRIPTION"),
    "description",
    "",
    ["rows" => 2],
    _("Insert the description")
);

$HD_Form->AddEditElement(
    _("TEXT INTRO"),
    "text_intro",
    "",
    ["maxlength" => 50],
    [Validator::class, "min4Chars"],
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
    ["rows" => 4],
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
