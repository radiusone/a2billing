<?php

use A2billing\Admin;
use A2billing\Forms\FormHandler;
use A2billing\Forms\Validator;
use A2billing\Table;

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

$HD_Form = new FormHandler("cc_support_component", _("Support Component"));

$HD_Form->list_query_order_columns = ["name"];

$HD_Form->AddListTopButton(null, null, "boxes");

$HD_Form->FG_FK_DELETE_ALLOWED = true;
$HD_Form->FG_FK_DELETE_CONFIRM = true;
$HD_Form->FG_FK_WARNONLY = true;
$HD_Form->foreign_keys = ["cc_ticket" => "id_component"];
$HD_Form->delete_message_confirm_fk = _("You have some tickets using this Support Component! Please comfirm that you really want to remove this Support Component? ");

$HD_Form->help_text = create_help(_("Here you can see all components which you use to handle the support task. You can use components to categorise the subject of the ticket. i.e : Tarification, Payment"));

$user_type = [_("CUSTOMERS"), _("AGENTS"), _("CUSTOMERS AND AGENTS")];
$activated_list = getActivationList();

$HD_Form->AddListValue(_("ID"), "id");
$HD_Form->AddListValue(_("Name"), "name");
$HD_Form->AddListMapping(_("Activated"), "activated", $activated_list);
$HD_Form->AddListMapping(_("User type"), "type_user", $user_type);
$HD_Form->AddListSqlMapping(_("Support box"), "id_support", new Table("cc_support", ["id", "name"]));

$HD_Form->FG_ENABLE_EDIT_BUTTON = true;
$HD_Form->FG_ENABLE_DELETE_BUTTON = true;
$HD_Form->FG_ENABLE_ADD_BUTTON = true;

$HD_Form->AddEditElement(
    _("NAME"),
    "name",
    "",
    ["maxlength" => 20],
    [Validator::class, "min1Char"],
    _("Insert the Support Box name ")
);

$HD_Form->AddEditRadio(
    _("ACTIVATED"),
    "activated",
    getYesNoList(),
    "1",
    "",
    [],
    _("Choose if you want to activate this component")
);

$HD_Form->AddEditSqlSelect(
    _("Support Box"),
    "id_support",
    new Table("cc_support", ["name", "id"])
);

$HD_Form->AddEditSelect(
    _("USER TYPE"),
    "type_user",
    $user_type,
    "",
    _("Define who can use this component.")
);

$HD_Form->edit_message_intro = "";
$HD_Form->add_message_intro = "";
