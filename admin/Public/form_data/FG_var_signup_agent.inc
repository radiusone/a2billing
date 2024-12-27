<?php

use A2billing\A2Billing;
use A2billing\Admin;
use A2billing\Agent;
use A2billing\Forms\FormHandler;
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
/**
 * @var A2Billing $A2B
 */
Admin::checkPageAccess(Admin::ACX_ADMINISTRATOR);

getpost_ifset(["id_agent"]);
/**
 * @var numeric-string|null $id_agent
 */

$form_action ??= "list";

$HD_Form = new FormHandler("cc_agent_signup", _("Signup Key"));

$HD_Form->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form->FG_LIST_ADDING_BUTTON_IMG1 = get_image_path("key_add.png");
$HD_Form->FG_ENABLE_DELETE_BUTTON = true;
$HD_Form->FG_ENABLE_EDIT_BUTTON = false;
$HD_Form->FG_ENABLE_ADD_BUTTON = true;

$HD_Form->FG_LOCATION_AFTER_DELETE = "?form_action=list&del=";
$HD_Form->FG_LOCATION_AFTER_ADD = "?form_action=list&id=";

$HD_Form->add_help_text = create_help(_("Generate a specific crypted URL to configure signup with a customer group and call plan."));
$HD_Form->help_text = create_help(_("This shows a list of all signup key create for the Agents, this key is used to identify the default paramater for the subscription on the signup page"));

$HD_Form->AddListValue(_("Key"), "code");
$HD_Form->AddListValue(_("Agent"), "id_agent", [Agent::class, "getName"]);
$HD_Form->AddListSqlMapping(_("Call plan"), "id_tariffgroup", new Table("cc_tariffgroup", ["id", "tariffgroupname"]));
$HD_Form->AddListSqlMapping(_("Group"), "id_group", new Table("cc_card_group", ["id" , "name"]));
$HD_Form->FieldViewElement (["code", "id_agent", "id_tariffgroup", "id_group"]);

$url = htmlspecialchars(json_encode($A2B->config["signup"]["urlcustomerinterface"]));
$HD_Form->AddListActionButton(
    "javascript:alert($url + 'signup.php?key=|col0|')",
    _("URL"),
    get_image_path("link.png")
);

$HD_Form->AddEditSqlSelect(
    _("Agent"),
    "id_agent",
    new Table("cc_agent", ["id", "CONCAT(login, ' - ', firstname, ' ', lastname)"]),
    [],
    $id_agent ?? "",
    ["" => _("Select agent")],
    "",
    ["required" => "required"]
);

if ((isset($id_agent) && is_numeric($id_agent) && ($form_action === "ask-add" || $form_action === "add")) || $form_action === "ask-delete") {
    $HD_Form->AddEditSqlSelect(
        _("Call Plan"),
        "id_tariffgroup",
        new Table("cc_tariffgroup", ["id", "tariffgroupname"], [
            "cc_agent_tariffgroup" => [
                "cc_tariffgroup.id", "cc_agent_tariffgroup.id_tariffgroup"
            ]
        ]),
        $form_action === "ask-delete" ? [] : ["id_agent" => $id_agent],
        "",
        ["" => _("Select call plan")],
        "",
        ["required" => "required"]
    );

    $HD_Form->AddEditSqlSelect(
        _("Customer Group"),
        "id_group",
        new Table("cc_card_group", ["id", "name"]),
        $form_action === "ask-delete" ? [] : ["id_agent" => $id_agent],
        "",
        ["" => _("Select group")],
        "",
        ["required" => "required"]
    );

    if ($form_action === "ask-delete") {
        $HD_Form->AddEditElement(_("Code"), "code");
    }
    $HD_Form->FG_ADD_QUERY_HIDDEN_INPUTS = [
        "code" => generate_unique_value("cc_agent_signup", 10, "code"),
    ];
}
