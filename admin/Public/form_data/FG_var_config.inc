<?php

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

Admin::checkPageAccess(Admin::ACX_ACXSETTING);

getpost_ifset(['id', 'groupselect']);
/**
 * @var string $id
 * @var string $groupselect
 * @var string $form_action
 */
$form_action = $form_action ?? "list";

$handle = DbConnect();

$HD_Form = new FormHandler("cc_config LEFT JOIN cc_config_group ON cc_config.config_group_id = cc_config_group.id", _("Configuration"), "cc_config.id");
$HD_Form->no_debug();
$HD_Form->FG_TABLE_DEFAULT_ORDER = "group_title";
$HD_Form->FG_QUERY_PRIMARY_KEY = "cc_config.id";
$HD_Form->update_query_conditions = ["cc_config.id" => "%id"];

$value_select = "";
if ($form_action === "list") {
    $yesno = [];
    $config_list = $handle->GetAll("SELECT config_value AS val, config_valuetype AS type, id FROM cc_config ORDER BY id");
    foreach ($config_list as $key => $config) {
        // where type = 1, map values 1 and 0 to yes and no
        $id = $config["id"];
        if ($config["type"] !== "1") {
            $yesno[$id] = [$config["val"], $id];
        } else {
            $yesno[$id] = [$config["val"] === "1" ? "Yes" : "No", $id];
        }
    }

    $HD_Form->AddViewElement(_("TITLE"), "config_title");
    $HD_Form->AddViewElement(_("KEY"), "config_key");
    $HD_Form->AddViewElement(_("VALUE"), "config_value", false, 0, "", "list-conf", $yesno);
    $HD_Form->AddViewElement(_("DESCRIPTION"), "config_description");
    $HD_Form->AddViewElement(_("GROUP"), "group_title");

    $HD_Form->FieldViewElement('config_title, config_key, config_value, config_description, group_title');

    $HD_Form->FG_LIST_VIEW_PAGE_SIZE = 25;
} elseif ($form_action === "ask-edit") {
    $result = $handle->GetOne(
        "SELECT CASE config_valuetype WHEN 0 THEN '' WHEN 1 THEN 'bool' WHEN 2 THEN config_listvalues END FROM cc_config WHERE id = ?",
        [$id]
    );
    if ($result !== "" && $result !== "bool") {
        $result = explode(",", $result);
        $value_select = array_map(fn($v) => [$v, $v], $result);
    } elseif ($result === "bool") {
        $value_select = [["Yes", 1], ["No", 0]];
    } else {
        $value_select = null;
    }
}

$HD_Form->FG_ENABLE_EDIT_BUTTON = true;
$HD_Form->search_form_enabled = true;
$HD_Form->AddSearchTextInput(_("Value"), "config_value");
$HD_Form->AddSearchTextInput(_("Key"), "config_key");
$HD_Form->AddSearchTextInput(_("Description"), "config_description");
$HD_Form->AddSearchSqlSelectInput(_("Select config group"), "cc_config_group", "id,group_title", "", "group_title", "ASC", "config_group_id");

$HD_Form->AddEditElement(
    _("GROUP"),
    "group_title",
    _("Group Name of the configuration variable"),
    "readonly='readonly'",
    null,
    _("This is the group of the configuration. Not editable!")
);

$HD_Form->AddEditElement(
    _("TITLE"),
    "config_title",
    _("Title of the configuration variable"),
    "maxlength=100 readonly='readonly'",
    null,
    _("Insert the TITLE")
);

$HD_Form->AddEditElement(
    _("KEY"),
    "config_key",
    _("Key name of the configuration variable"),
    "maxlength=100 readonly='readonly'",
    null,
    _("Insert the TITLE")
);

if (is_array($value_select)) {
    $HD_Form->AddEditSelect(
        _("VALUE"),
        "config_value",
        $value_select,
    );
} else {
    $HD_Form->AddEditElement(
        _("VALUE"),
        "config_value",
        _("Insert the Value"),
        "maxlength=200"
    );
}

$HD_Form->AddEditSelect(
    _("TYPE"),
    "config_valuetype",
    [["Plain value", 0], ["Yes/No value", 1], ["Selectable list", 2]],
);

$HD_Form->AddEditElement(
    _("LIST OF VALUES"),
    "config_listvalues",
    _("For selectable lists only, insert the comma separated list of values"),
    "",
    null,
    "",
    "",
    "",
    fn ($v) => $v === "yes,no" || $v === "no,yes" ? "" : $v
);

$HD_Form->AddEditTextarea(
    _("DESCRIPTION"),
    "config_description",
    _("Insert the Description"),
    "rows=5 readonly=\"readonly\""
);

$HD_Form->FG_INTRO_TEXT_EDITION= '';
$HD_Form->FG_LOCATION_AFTER_EDIT = "?id=";
