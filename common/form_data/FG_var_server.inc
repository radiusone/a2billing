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

$HD_Form = new FormHandler("cc_server_manager", _("Callback Server"));

$HD_Form->AddListTopButton(null, null, "hdd-network-fill");

$HD_Form->help_text = create_help(
    _("Servers are used by the callback system through the asterisk manager in order to initiate the callback and outbound calls for your customers. You can add/modify the callback server to be used here. The AGI and callback modes need to be installed on those machines.")
);

$HD_Form->AddListValue(_("ID"), "id_group");
$HD_Form->AddListSqlMapping(_("Group"), "id_group", new Table("cc_server_group", ["id", "name"]));
$HD_Form->AddListValue(_("Hostname"), "server_ip");
$HD_Form->AddListValue(_("Manager host"), "manager_host");
$HD_Form->AddListValue(_("Manager username"), "manager_username");
$HD_Form->AddListValue(_("Last used"), "lasttime_used");

$HD_Form->FG_ENABLE_ADD_BUTTON = true;
$HD_Form->FG_ENABLE_EDIT_BUTTON = true;
$HD_Form->FG_ENABLE_DELETE_BUTTON = true;

$HD_Form->AddEditSqlSelect(_("SERVER GROUP"), "id_group", new Table("cc_server_group", ["name", "id"]));

$HD_Form->AddEditElement(
    _("SERVER HOSTNAME"),
    "server_ip",
    "",
    ["maxlength" => 40],
    [Validator::class, "min4Chars"],
    _("Insert the server hostname")
);

$HD_Form->AddEditElement(
    _("MANAGER HOST"),
    "manager_host",
    "",
    ["maxlength" => 40],
    [Validator::class, "min1Char"],
    _("Insert the manager host")
);

$HD_Form->AddEditElement(
    _("MANAGER USERNAME"),
    "manager_username",
    "",
    ["maxlength" => 40],
    [Validator::class, "min1Char"],
    _("Insert the manager username")
);

$HD_Form->AddEditElement(
    _("MANAGER SECRET"),
    "manager_secret",
    "",
    ["maxlength" => 40],
    null,
    _("Insert the manager secret")
);

// Set the filter variables
$HD_Form->AddListFilter("id_group", _("Server ID Group"));

$HD_Form->add_message_intro = "";
