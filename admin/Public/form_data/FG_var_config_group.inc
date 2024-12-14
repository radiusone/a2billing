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

getpost_ifset(["agi_conf", "from_conf"]);
/**
 * @var string $agi_conf
 * @var string $from_conf
 */

$setbool = false;
if (!empty($agi_conf)) {
    add_agi_confx($agi_conf, $from_conf);
}

$HD_Form = new FormHandler("cc_config_group", _("Configuration group"));

$HD_Form->FG_FK_TABLENAMES = ["cc_config"];
$HD_Form->FG_FK_EDITION_CLAUSE = ["config_group_id"];
$HD_Form->FG_FK_DELETE_ALLOWED = true;

$HD_Form->help_text = create_help(_("This action will generate a new agi-conf configuration group, copying configurations from the first agi-conf group found."));
$HD_Form->list_help_text = create_help(_("Here is a list of all configuration groups. You can pick one and see its members"));

$HD_Form->AddListValue(gettext("TITLE"), "group_title");
$HD_Form->AddListValue(gettext("DESCRIPTION"), "group_description");
$HD_Form->FieldViewElement(["group_title", "group_description"]);

$HD_Form->FG_ENABLE_EDIT_BUTTON = false;
$HD_Form->FG_ENABLE_ADD_BUTTON = false;
$HD_Form->FG_ENABLE_DELETE_BUTTON = true;
$HD_Form->FG_DELETE_BUTTON_CONDITION = 'str_starts_with("|col0|", "agi-conf") && "|col0|" !== "agi-conf1"';

$HD_Form->AddListActionButton(
    "A2B_entity_config.php?posted_search=1&config_group_id=|param|",
    gettext('Show group entries'),
    "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAJGSURBVDjLjdJLSNRBHMDx78yqLZaKS75DPdgDDaFDbdJmde5QlhCJGxgpRJfqEEKnIsJLB7skQYQKZaSmdLaopPCgEvSCShCMzR5a7oq7/3l12RVtjfzBMA/4fWZ+MyOccwBM3g8HEbIdfCEhfAFnLVapOa28Uevpjrqz/WOsERJgsu9Uq5CZQzgqrJfo9BajNd5irEYn4p3OUiFExtCLmw2tawFi4l5zUMjMIau9u7K+qxeoAcoAA0wDb2OPwmfA16LiiaOHLj1edRLpkO3WmIis7+oBDgJbgQ2AH6gC6jY19N62RkcctKeVIJAhp9QgUA3kJXdONZVcq9JxPSgQoXRAyIDRth8oAXQyKdWnoCKrTD9CBv4GMqx1WGNZkeRWJKbG2hiD1Cb9FbTnzWFdY/LCdLKlgNQ84gyNKqHm0gDjqVHnxDHgA/B9RQkpaB6YklkZl62np9KBhOqwjpKFgeY2YAz4BESBWHI8Hhs6PVVSvc3v98ye4fP7T676B845nt040ip98qpWJmI9PWiU6bfWgXGN2YHcKwU7tsuc4kpUPMbU0+f8+vKt+Pitl7PLAMDI9cNBoB0hQwICzjqUp6MZvsy8yvp95BRuQUjJ75mPvH4wYo1NlJ64Mza7DPwrhi8cCOeXl/aUB4P4c/NJxKLMvpngycCrzxVFG2v/CwAMnguF80oLe8p27cQh+fnpPV/fTc95S6piXQDAw7a9YbWkezZXFbAwMx/xPFXb1D3+Y90AQF/L7kAsri9mZ4lrTd0TcYA/Kakr+x2JSPUAAAAASUVORK5CYII="
);

$HD_Form->FG_INTRO_TEXT_EDITION = "";
$HD_Form->FG_INTRO_TEXT_ADITION = "";

$HD_Form->AddEditElement(_("Title"), "group_title");
$HD_Form->AddEditElement(_("Description"), "group_description");

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
    $group_id = $handle->Insert_ID("cc_config_group", "id");
    if (!empty($from_conf)) {
        $query = <<< SQL
            INSERT INTO cc_config (
                config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues
            )
            SELECT config_title, config_key, config_value, config_description, config_valuetype, ?, config_listvalues
            FROM cc_config c
                LEFT JOIN cc_config_group g ON c.config_group_id = g.id
            WHERE g.group_title = ?
            SQL;
        $params = [$group_id, $from_conf];
        $handle->Execute($query, $params);
    }
}
