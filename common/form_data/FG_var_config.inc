<?php

use A2billing\Admin;
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

getpost_ifset(["id", "group_title"]);
/**
 * @var numeric-string $id
 * @var string $group_title
 * @var string $form_action
 */
$form_action ??= "list";

if (isset($group_title)) {
    $group_id = (new Table("cc_config_group", ["id"]))->getValue(["group_title" => $group_title]);
    if ($group_id) {
        header("Location: A2B_entity_config.php?form-action=list&posted_search=1&config_group_id=$group_id");
        die();
    }
}

$value_select = "";
if ($form_action === "list") {
    $HD_Form = new FormHandler(
        "cc_config",
        _("Configuration"),
        "cc_config.id",
        ["cc_config_group" => ["cc_config.config_group_id", "cc_config_group.id"]]
    );
    $HD_Form->list_query_order_columns = ["group_title", "config_key"];
    $HD_Form->FG_QUERY_PRIMARY_KEY = "cc_config.id";
    $HD_Form->FG_LIST_VIEW_PAGE_SIZE = 25;
    $HD_Form->FG_ENABLE_ADD_BUTTON = true;
    $HD_Form->AddListTopButton();

    $HD_Form->help_text = create_help(_("Here you can see and edit the different A2Billing settings."));

    $HD_Form->AddListValue(_("Title"), "config_title");
    $HD_Form->AddListValue(_("Key"), "config_key");
    // if config_valuetype is 1, display as yes/no
    $HD_Form->AddListValue(_("Value"), "config_value", "str_replace_conditional", [[0, 1], [_("No"), _("Yes")], "%X", "%5", 1]);
    $HD_Form->AddListValue(_("Description"), "config_description");
    $HD_Form->AddListValue(_("Group"), "group_title");
    $HD_Form->AddListHiddenValue("config_valuetype");
} else {
    $HD_Form = new FormHandler("cc_config", _("Configuration"));

    if ($form_action === "ask-edit") {
        $result = (new Table("cc_config", ["config_valuetype", "config_listvalues"]))
            ->getRow(["id" => $id]);
        switch ($result["config_valuetype"]) {
            case 2:
                $value_select = array_combine(
                    explode(",", $result["config_listvalues"]),
                    explode(",", $result["config_listvalues"])
                );
                break;
            case 1:
                $value_select = getYesNoList();
                break;
            default:
                $value_select = null;
        }
    }
}

$HD_Form->FG_ENABLE_EDIT_BUTTON = true;
$HD_Form->search_form_enabled = true;
$HD_Form->search_delete_enabled = false;
$HD_Form->AddSearchTextInput(_("Value"), "config_value");
$HD_Form->AddSearchTextInput(_("Key"), "config_key");
$HD_Form->AddSearchTextInput(_("Description"), "config_description");
$HD_Form->AddSearchSqlSelectInput(_("Select config group"), "config_group_id", new Table("cc_config_group", ["id", "group_title"]), "group_title");

$readonly = ($form_action === "ask-add" || $form_action === "add")
    ? []
    : ["readonly" => "readonly", "disabled" => "disabled"];

$HD_Form->AddEditSqlSelect(
    _("GROUP"),
    "config_group_id",
    new Table("cc_config_group", ["id", "group_title"]),
    [],
    "",
    [],
    _("Group Name of the configuration variable"),
    $readonly,
    _("This is the group of the configuration. Not editable!")
);

$HD_Form->AddEditElement(
    _("TITLE"),
    "config_title",
    _("Title of the configuration variable"),
    $readonly,
    null,
    _("Insert the TITLE")
);

$HD_Form->AddEditElement(
    _("KEY"),
    "config_key",
    _("Key name of the configuration variable"),
    $readonly,
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
        ["maxlength" => 200]
    );
}

$HD_Form->AddEditSelect(
    _("TYPE"),
    "config_valuetype",
    [_("Plain value"), _("Yes/No value"), _("Selectable list")],
    "",
    "",
    $readonly
);

$HD_Form->AddEditElement(
    _("LIST OF VALUES"),
    "config_listvalues",
    _("For selectable lists only, insert the comma separated list of values"),
    [],
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
    array_merge(["rows" => 5], $readonly)
);

$HD_Form->FG_INTRO_TEXT_EDITION = "";
$HD_Form->FG_LOCATION_AFTER_EDIT = "?id=";
$HD_Form->FG_LOCATION_AFTER_ADD = "?id=";
