<?php

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

if (!has_rights(ACX_ACXSETTING)) {
    header("HTTP/1.0 401 Unauthorized");
    header("Location: PP_error.php?c=accessdenied");
    die();
}

getpost_ifset([
    'id', 'config_title', 'config_key', 'config_value', 'config_description', 'config_group_title', 
    'groupselect', 'filterKey', 'rbKey', 'filterDescription', 'rbDesc', 'filterValue', 'rbValue', 'posted','agi_conf'
]);
/**
 * @var string $id
 * @var string $config_title
 * @var string $config_key
 * @var string $config_value
 * @var string $config_description
 * @var string $config_group_title
 * @var string $groupselect
 * @var string $filterKey
 * @var string $rbKey
 * @var string $filterDescription
 * @var string $rbDesc
 * @var string $filterValue
 * @var string $rbValue
 * @var string $posted
 * @var string $agi_conf
 * @var string $form_action
 */

// This will generate agi-confx
// agi-confx is duplication of Global configuration and list of configurations of : agi-conf1
if (!empty($agi_conf)) {
	$groupselect = add_agi_confx();
	unset($agi_conf);
}

$handle = DbConnect();

$setbool = false;
if ($form_action === "ask-edit") {
    $result = $handle->Execute("SELECT config_listvalues FROM cc_config WHERE id = ?", [$id]);
    if ($result && $row = $result->FetchRow()) {
        $setbool = ($row[0] === "yes,no" || $row[0] === "no,yes");
    }
}

$HD_Form = new FormHandler("cc_config", _("Configuration"));
$HD_Form->no_debug();
$HD_Form->FG_TABLE_DEFAULT_ORDER = "config_group_title";

if ($form_action === "list") {
    $yesno = [];
    $result = $handle->Execute("SELECT config_value, config_valuetype, id FROM cc_config ORDER BY id");
    $config_list = $result ? $result->GetAll() : [];
    foreach ($config_list as $key => $cur_value) {
        $i = $cur_value[2];
        if ($cur_value[1] !== '1') {
            $yesno[$i] = [$cur_value[0], $cur_value[2]];
        } elseif ($cur_value[0] === '1') {
            $yesno[$i] = ["Yes", $cur_value[2]];
        } elseif ($cur_value[0] === '0') {
            $yesno[$i] = ["No", $cur_value[2]];
        } else {
            $yesno[$i] = [$cur_value[0], $cur_value[2]];
        }
    }

    $HD_Form->FG_EDIT_QUERY_CONDITION = "id = %id";
    $HD_Form->AddViewElement(_("TITLE"), "config_title");
    $HD_Form->AddViewElement(_("KEY"), "config_key");
    $HD_Form->AddViewElement(_("VALUE"), "config_value", false, 0, "", "list-conf", $yesno);
    $HD_Form->AddViewElement(_("DESCRIPTION"), "config_description");
    $HD_Form->AddViewElement(_("GROUP"), "config_group_title");

    $HD_Form->FieldViewElement('config_title, config_key, config_value, config_description, config_group_title');

    $HD_Form->FG_LIST_VIEW_PAGE_SIZE = 25;
    $HD_Form->FG_ENABLE_EDIT_BUTTON = true;
}
if(!$setbool) {
	$HD_Form->FG_DISPLAY_SELECT = true;
	$HD_Form->FG_SELECT_FIELDNAME = "config_listvalues";
	$HD_Form->FG_CONF_VALUE_FIELDNAME = "config_value";
}

$HD_Form->search_form_enabled = true;
$HD_Form->AddSearchTextInput(_("Value"), "config_value");
$HD_Form->AddSearchTextInput(_("Key"), "config_key");
$HD_Form->AddSearchTextInput(_("Description"), "config_description");
$HD_Form->AddSearchSqlSelectInput(_("Group"), "cc_config_group", "group_title,group_title", "", ["group_title"], "ASC", "config_group_title");

$HD_Form->AddEditElement(
    _("GROUP"),
    "config_group_title",
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

if ($setbool) {
    $HD_Form->AddEditSelect(
        "config_value",
        [1 => [_("yes"), 1], 2 => [_("no"), 0]],
        _("VALUE")
    );
} else {
    $HD_Form->AddEditElement(
        _("VALUE"),
        "config_value",
        _("Insert the Value"),
        "maxlength=200"
    );
}

    $HD_Form->AddEditElement(
        _("LIST OF VALUES"),
        "config_listvalues",
        _("Insert the List of values, comma separated")
    );

$HD_Form->AddEditElement(
    _("DESCRIPTION"),
    "config_description",
    "",
    "rows=10",
    null,
    _("Insert the Description")
);

$HD_Form->FieldEditElement ('config_group_title, config_title, config_key, config_value ,config_listvalues, config_description');

$HD_Form->FG_INTRO_TEXT_EDITION= '';
$HD_Form->FG_LOCATION_AFTER_EDIT = "?atmenu=document&stitle=Document&wh=AC&id=";

function agi_confx_title(): array
{
    $handle = DbConnect();

    $QUERY = "SELECT id, group_title, group_description FROM cc_config_group WHERE group_title like 'agi-conf%' ORDER BY group_title";
    $result = $handle->Execute($QUERY);
    $result = $result ? $result->FetchRow() : [];

    $agi_number = [];
    foreach ($result as $row) {
        $agi_number[] = (int)str_replace("agi-conf", "", $row["group_title"]);
    }

    $first_group_title = $result[0]["group_title"];
    $agi_conf_number = max($agi_number) + 1;

    return ["agi-conf$agi_conf_number", $first_group_title];
}

function add_agi_confx($handle = null)
{
    [$group_title, $base_group_title] = agi_confx_title();

    $group_desc = 'This configuration group handles the AGI Configuration.';
    $handle = DbConnect();
    $handle->Execute("INSERT INTO cc_config_group (group_title, group_description) VALUES (?, ?)", [$group_title, $group_desc]);
    $handle->Execute(
        "INSERT INTO cc_config_group (config_title, config_key, config_value, config_description, config_valuetype, config_group_title, config_listvalues)
        SELECT config_title, config_key, config_value, config_description, config_valuetype, ?, config_listvalues FROM cc_config WHERE config_group_title = ?",
        [$group_title, $base_group_title]
    );
    return $group_title;
}

/*
 * This function delete agi-confx, all its global configurations and list of configurations
 * Operations : DELETE
 * Tables : cc_config, cc_config_group
 */
function delete_agi_confx($agi_conf)
{
    if (empty($handle)) {
        $handle = DbConnect();
    }
    $instance_table = new Table();

    $clause = "group_title = '$agi_conf'";
    $fun_table = "cc_config_group";
    $result = $instance_table->Delete_table ($handle, $clause, $fun_table);

    $clause = "config_group_title = '$agi_conf'";
    $fun_table = "cc_config";
    $result = $instance_table->Delete_table ($handle, $clause, $fun_table);

    return $result;
}
