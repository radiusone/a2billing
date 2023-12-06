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

$setbool = false;
if ($form_action === "ask-edit") {
    $setbool = (bool)$handle->GetOne("SELECT IF(config_listvalues = 'yes,no' OR config_listvalues = 'no,yes', 1, 0) FROM cc_config WHERE id = ?", [$id]);
}

$HD_Form = new FormHandler("cc_config LEFT JOIN cc_config_group ON cc_config.config_group_id = cc_config_group.id", _("Configuration"));
$HD_Form->no_debug();
$HD_Form->FG_TABLE_DEFAULT_ORDER = "group_title";

if ($form_action === "list") {
    $yesno = [];
    $config_list = $handle->GetAll("SELECT config_value, config_valuetype, id FROM cc_config ORDER BY id");
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
    $HD_Form->AddViewElement(_("GROUP"), "group_title");

    $HD_Form->FieldViewElement('config_title, config_key, config_value, config_description, group_title');

    $HD_Form->FG_LIST_VIEW_PAGE_SIZE = 25;
}
if(!$setbool) {
	$HD_Form->FG_DISPLAY_SELECT = true;
	$HD_Form->FG_SELECT_FIELDNAME = "config_listvalues";
	$HD_Form->FG_CONF_VALUE_FIELDNAME = "config_value";
}

$HD_Form->FG_ENABLE_EDIT_BUTTON = true;
$HD_Form->search_form_enabled = true;
$HD_Form->AddSearchTextInput(_("Value"), "config_value");
$HD_Form->AddSearchTextInput(_("Key"), "config_key");
$HD_Form->AddSearchTextInput(_("Description"), "config_description");
$HD_Form->AddSearchSqlSelectInput(_("Select config group"), "cc_config_group", "id,group_title", "", ["group_title"], "ASC", "config_group_id");

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

$HD_Form->FieldEditElement('config_title, config_key, config_value ,config_listvalues, config_description');

$HD_Form->FG_INTRO_TEXT_EDITION= '';
$HD_Form->FG_LOCATION_AFTER_EDIT = "?id=";
