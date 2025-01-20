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

$HD_Form = new FormHandler("cc_card_seria", _("Card series"));

$HD_Form->list_query_order_columns = ["name"];

$HD_Form->FG_FK_DELETE_CONFIRM = true;
$HD_Form->FG_FK_TABLENAMES = ["cc_card"];
$HD_Form->FG_FK_EDITION_CLAUSE = [" id_seria "];
$HD_Form->FG_FK_DELETE_MESSAGE = _("You have some cards using this series! Please comfirm that you really want to remove this series.");

$HD_Form ->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form ->FG_LIST_ADDING_BUTTON_IMG1 = get_image_path("page_white_add.png") ;

$HD_Form->help_text = create_help(
    _("This page shows the series list.")
    . '<br>'
    . _("Series & serials is used for the creation of a card range specifically for accounting purposes instead of card id.")
);

$HD_Form->AddListValue(_("Name"), "name");
$HD_Form->AddListValue(_("Description"), "description", "substr", ["%X", 0, 40]);
$HD_Form->AddListValue(_("Sequence number"), "value");

$HD_Form->FG_ENABLE_ADD_BUTTON = true;
$HD_Form->FG_ENABLE_EDIT_BUTTON = true;
$HD_Form->FG_ENABLE_DELETE_BUTTON = false;

$HD_Form->AddEditElement(
    _("NAME"),
    "name",
    "",
    ["maxlength" => 40],
    [Validator::class, "min3Chars"],
    _("Insert the Seria name")
);

$HD_Form->AddEditTextarea(
    _("DESCRIPTION"),
    "description",
    "",
    ["rows" => 4],
    _("Insert the description")
);
