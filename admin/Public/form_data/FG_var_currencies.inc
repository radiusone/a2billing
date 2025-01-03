<?php

use A2billing\Admin;
use A2billing\Forms\FormBO;
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

$HD_Form = new FormHandler("cc_currencies", _("Currencies"));

$HD_Form->FG_TABLE_DEFAULT_ORDER = "currency";
$HD_Form->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form->FG_LIST_ADDING_BUTTON_LINK1 = "A2B_currencies.php?form_action=ask-add";
$HD_Form->FG_LIST_ADDING_BUTTON_ALT1 = $HD_Form ->FG_LIST_ADDING_BUTTON_MSG1 = _("Add Rate");
$HD_Form->FG_LIST_ADDING_BUTTON_IMG1 = get_image_path("world_add.png") ;


$HD_Form->AddListValue(_("Currency"), "currency");
$HD_Form->AddListValue(_("Detail"), "name");
$HD_Form->AddListValue(_("Value"), "value");

$HD_Form->FieldViewElement(["currency", "name", "value"]);

$HD_Form->FG_ENABLE_EDIT_BUTTON = true;
$HD_Form->FG_ENABLE_DELETE_BUTTON = true;
$HD_Form->FG_ENABLE_ADD_BUTTON = true;

$HD_Form->AddEditElement(
    gettext("Name"),
    "name",
    "",
    ["maxlength" => 30],
    [Validator::class, "min4Chars"],
    gettext("Insert the currency name")
);

$HD_Form->AddEditElement(
    gettext("LABEL"),
    "currency",
    "",
    ["maxlength" => 3],
    [Validator::class, "min3Chars"],
    gettext("Insert the currency label")
);


$HD_Form->AddEditElement(
    gettext("VALUE"),
    "value",
    gettext("Set the value of this currency in your base currency that the user have to pay per month.") . " " . gettext("Base currency :") . " " . strtoupper(BASE_CURRENCY),
    ["maxlength" => 30],
    [Validator::class, "number"],
    gettext("Insert the fee amount.")
);


// Set the filter variables
$HD_Form -> FG_FILTER_ENABLE = true;
$HD_Form -> FG_FILTER_COLUMN = 'currency';
$HD_Form -> FG_FILTER_LABEL = 'currency';

$HD_Form->FG_ADD_QUERY_HIDDEN_INPUTS["basecurrency"] = strtoupper(BASE_CURRENCY);

$HD_Form -> FG_INTRO_TEXT_ADITION = '';

$HD_Form -> FG_ADDITIONAL_FUNCTION_AFTER_ADD = [FormBO::class, "processing_refill_add"];

$HD_Form -> FG_LOCATION_AFTER_ADD = "?id=";
$HD_Form -> FG_LOCATION_AFTER_EDIT = "?id=";
$HD_Form -> FG_LOCATION_AFTER_DELETE = "?id=";



