<?php

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

Admin::checkPageAccess(Admin::ACX_ADMINISTRATOR);

$HD_Form = new FormHandler("cc_support", _("Support Box"));

$HD_Form->FG_TABLE_DEFAULT_ORDER = "name";

$HD_Form->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form->FG_LIST_ADDING_BUTTON_IMG1 = get_image_path("package_add.png") ;

$HD_Form->FG_FK_DELETE_ALLOWED = true;
$HD_Form->FG_FK_DELETE_CONFIRM = true;
$HD_Form->FG_FK_WARNONLY = true;
$HD_Form->FG_FK_TABLENAMES = ["cc_support_component"];
$HD_Form->FG_FK_EDITION_CLAUSE = ["id_support"];
$HD_Form->FG_FK_DELETE_MESSAGE = _("You have some components using this Support Box! Please comfirm that you really want to remove this Support Box? ");

$HD_Form->help_text = create_help(_("You can see here the Support Box, you need at least one support box to start support activity."));

$HD_Form->AddListValue(_("ID"), "id");
$HD_Form->AddListValue(_("Name"), "name");
$HD_Form->AddListValue(_("Email"), "email");
$HD_Form->AddListValue(_("Language"), "language");
$HD_Form->FieldViewElement(["id", "name", "email", "language"]);

$HD_Form->FG_ENABLE_EDIT_BUTTON = true;
$HD_Form->FG_ENABLE_DELETE_BUTTON = true;
$HD_Form->FG_ENABLE_ADD_BUTTON = true;

$language_list = getLanguages();

$HD_Form->AddEditElement(
    _("NAME"),
    "name",
    "",
    ["maxlength" => 20],
    [Validator::class, "min1Char"],
    _("Insert the Support Box name ")
);

$HD_Form->AddEditSelect(_("LANGUAGE"), "language", $language_list);

$HD_Form->AddEditElement(
    _("EMAIL"),
    "email",
    "",
    ["maxlength" => 70],
    [Validator::class, "email"],
    _("Insert the email"),
    "",
    "NO"
);

$HD_Form->FG_INTRO_TEXT_EDITION = "";
$HD_Form->FG_INTRO_TEXT_ADITION = "";

$HD_Form->FG_LOCATION_AFTER_ADD = "?id=";
$HD_Form->FG_LOCATION_AFTER_EDIT = "?id=";
$HD_Form->FG_LOCATION_AFTER_DELETE = "?id=";
