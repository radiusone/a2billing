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
Admin::checkPageAccess(Admin::ACX_ACXSETTING);

getpost_ifset(['id', 'group_title', 'group_description','agi_conf', 'groupdelete', 'agi_conf', 'from_conf']);
/**
 * @var string $id
 * @var string $group_title
 * @var string $group_description
 * @var string $agi_conf
 * @var string $groupdelete
 * @var string $agi_conf
 * @var string $from_conf
 */

$setbool = false;
if (!empty($agi_conf)) {
    add_agi_confx($agi_conf, $from_conf);
}

$HD_Form = new FormHandler("cc_config_group", "configuration group");

$HD_Form->no_debug();
$HD_Form->FG_TABLE_DEFAULT_ORDER = "id";
$HD_Form->FG_TABLE_DEFAULT_SENS = "ASC";

$HD_Form->AddViewElement(gettext("TITLE"), "group_title");
$HD_Form->AddViewElement(gettext("DESCRIPTION"), "group_description");
$HD_Form->FieldViewElement ('group_title, group_description');

$HD_Form->FG_LIST_VIEW_PAGE_SIZE = 15;

$HD_Form -> FG_ENABLE_EDIT_BUTTON = false;
$HD_Form -> FG_ENABLE_ADD_BUTTON = false;
$HD_Form -> FG_ENABLE_DELETE_BUTTON = true;
$HD_Form -> FG_DELETE_BUTTON_CONDITION = 'str_starts_with("|col0|", "agi-conf") && "|col0|" !== "agi-conf1"';

$HD_Form -> FG_OTHER_BUTTON1 = true;
$HD_Form -> FG_OTHER_BUTTON1_LINK = "A2B_entity_config.php?posted_search=1&config_group_title=|col0|";
$HD_Form -> FG_OTHER_BUTTON1_ALT = gettext('Show group entries');
$HD_Form -> FG_OTHER_BUTTON1_IMG = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAJGSURBVDjLjdJLSNRBHMDx78yqLZaKS75DPdgDDaFDbdJmde5QlhCJGxgpRJfqEEKnIsJLB7skQYQKZaSmdLaopPCgEvSCShCMzR5a7oq7/3l12RVtjfzBMA/4fWZ+MyOccwBM3g8HEbIdfCEhfAFnLVapOa28Uevpjrqz/WOsERJgsu9Uq5CZQzgqrJfo9BajNd5irEYn4p3OUiFExtCLmw2tawFi4l5zUMjMIau9u7K+qxeoAcoAA0wDb2OPwmfA16LiiaOHLj1edRLpkO3WmIis7+oBDgJbgQ2AH6gC6jY19N62RkcctKeVIJAhp9QgUA3kJXdONZVcq9JxPSgQoXRAyIDRth8oAXQyKdWnoCKrTD9CBv4GMqx1WGNZkeRWJKbG2hiD1Cb9FbTnzWFdY/LCdLKlgNQ84gyNKqHm0gDjqVHnxDHgA/B9RQkpaB6YklkZl62np9KBhOqwjpKFgeY2YAz4BESBWHI8Hhs6PVVSvc3v98ye4fP7T676B845nt040ip98qpWJmI9PWiU6bfWgXGN2YHcKwU7tsuc4kpUPMbU0+f8+vKt+Pitl7PLAMDI9cNBoB0hQwICzjqUp6MZvsy8yvp95BRuQUjJ75mPvH4wYo1NlJ64Mza7DPwrhi8cCOeXl/aUB4P4c/NJxKLMvpngycCrzxVFG2v/CwAMnguF80oLe8p27cQh+fnpPV/fTc95S6piXQDAw7a9YbWkezZXFbAwMx/xPFXb1D3+Y90AQF/L7kAsri9mZ4lrTd0TcYA/Kakr+x2JSPUAAAAASUVORK5CYII=";


$HD_Form->FG_INTRO_TEXT_EDITION = '';
$HD_Form->FG_INTRO_TEXT_ADITION = '';

$HD_Form->AddEditElement(_("Title"), "group_title");
$HD_Form->AddEditElement(_("Description"), "group_description");
$HD_Form->FieldEditElement("group_title, group_description");

$HD_Form->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form->FG_LIST_ADDING_BUTTON_LINK1 = "A2B_entity_config_generate_confirm.php";
$HD_Form->FG_LIST_ADDING_BUTTON_MSG1 = _("Add new agi-conf");

$HD_Form->FG_LOCATION_AFTER_ADD = "?id=";
$HD_Form->FG_LOCATION_AFTER_EDIT = "?id=";
$HD_Form->FG_LOCATION_AFTER_DELETE = "?";

function add_agi_confx(string $group_title, ?string $from_conf): void
{
    $group_desc = 'This configuration group handles the AGI Configuration.';
    $handle = DbConnect();
    $handle->Execute("INSERT INTO cc_config_group (group_title, group_description) VALUES (?, ?)", [$group_title, $group_desc]);
    if (!empty($from_conf)) {
        $handle->Execute(
            "INSERT INTO cc_config_group (config_title, config_key, config_value, config_description, config_valuetype, config_group_title, config_listvalues)
            SELECT config_title, config_key, config_value, config_description, config_valuetype, ?, config_listvalues FROM cc_config WHERE config_group_title = ?",
            [$group_title, $from_conf]
        );
    }
}

function agi_confx_title(): array
{
    $handle = DbConnect();

    $QUERY = "SELECT id, group_title, group_description FROM cc_config_group WHERE group_title like 'agi-conf%' ORDER BY group_title";
    $result = $handle->GetAll($QUERY);

    $agi_number = [];
    foreach ($result as $row) {
        $agi_number[] = (int)str_replace("agi-conf", "", $row["group_title"]);
    }

    $first_group_title = $result[0]["group_title"];
    $agi_conf_number = max($agi_number) + 1;

    return ["agi-conf$agi_conf_number", $first_group_title];
}
