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

$form_action ??= "list";

$HD_Form = new FormHandler("cc_package_offer", _("Package"));

$HD_Form->list_query_order_columns = ["label"];
$HD_Form->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form ->FG_LIST_ADDING_BUTTON_IMG1 = get_image_path("package_add.png");

$HD_Form->help_text = create_help(_("PACKAGES SYSTEM - FREE MINUTES, etc..."));

$packagetype_list = getPackagesTypeList();
$billingtype_list = [_("Monthly"), _("Weekly")];

$HD_Form->AddListValue(_("Label"), "label");
$HD_Form->AddListValue(_("Creation date"), "creationdate");
$HD_Form->AddListMapping(_("Type"), "packagetype", $packagetype_list);
$HD_Form->AddListMapping(_("Billing"), "billingtype", $billingtype_list);
$HD_Form->AddListValue(_("Starting day"), "startday");
// If packagetype == NUMBER OF FREE CALL -> concat unit with "times"
$HD_Form->AddListValue(_("Free calls"), "freetimetocall", fn($type, $num) => $type ? sprintf($type == 1 ? _("%d times") : _("%d seconds"), $num) : _("ALL"), ["%6", "%X"]);
$HD_Form->AddListHiddenValue("packagetype");

$HD_Form -> FG_ENABLE_ADD_BUTTON = true;
$HD_Form -> FG_ENABLE_EDIT_BUTTON = true;
$HD_Form -> FG_ENABLE_DELETE_BUTTON = true;
$HD_Form->AddListActionButton(
    "A2B_info_package.php?id=|param|",
    _("Add rates"),
    ""
);

$HD_Form->AddEditElement(
    _("LABEL"),
    "label",
    "",
    ["maxlength" => 30],
    [Validator::class, "min3Chars"],
    _("Insert the package label")
);

$HD_Form->AddEditSelect(_("PACKAGE TYPE"), "packagetype", $packagetype_list);
$HD_Form->AddEditSelect(_("BILLING TYPE"), "billingtype", $billingtype_list);


$HD_Form->AddEditElement(
    _("STARTING DAY"),
    "startday",
    _("The value will be related to billing type ; if monthly accepted value are 1-31 ; if Weekly accepted value 1-7 (Monday to Sunday) "),
    ["maxlength" => 5] + ($form_action === "ask-add" ? ["value" => 1] : []),
    [Validator::class, "numeric"],
    _("Insert the start day")
);


$HD_Form->AddEditElement(
    _("FREE UNITS"),
    "freetimetocall",
    _("Insert the amount of free units, in seconds,for the FREE SECONDS mode. For NUMBER OF FREE CALLS insert the number of free calls"),
    ["maxlength" => 10],
    [Validator::class, "numeric"],
    _("Insert the amount of free time to call (seconds)")
);

$HD_Form -> FG_INTRO_TEXT_ADITION = "";
