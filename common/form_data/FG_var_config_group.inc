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

$HD_Form->FG_ENABLE_EDIT_BUTTON = false;
$HD_Form->FG_ENABLE_ADD_BUTTON = false;
$HD_Form->FG_ENABLE_DELETE_BUTTON = true;
$HD_Form->FG_DELETE_BUTTON_CONDITION = 'str_starts_with("|col0|", "agi-conf") && "|col0|" !== "agi-conf1"';

$HD_Form->AddListActionButton(
    "A2B_entity_config.php?posted_search=1&config_group_id=|param|",
    gettext('Show group entries'),
    "",
    "search"
);

$HD_Form->edit_message_intro = "";
$HD_Form->add_message_intro = "";

$HD_Form->AddEditElement(_("Title"), "group_title");
$HD_Form->AddEditElement(_("Description"), "group_description");

$HD_Form->AddListTopButton("A2B_entity_config_generate_confirm.php", _("Add new agi-conf"), "boxes");

function add_agi_confx(string $group_title, ?string $from_conf): void
{
    $group_description = 'This configuration group handles the AGI Configuration.';
    (new Table("cc_config_group"))
        ->addRow(compact("group_title", "group_description"), "id", $group_id);
    if (!empty($from_conf)) {
        (new Table("cc_config"))
            ->addRowsFromSelect(
                new Table(
                    "cc_config AS c",
                    ["config_title", "config_key", "config_value", "config_description", "config_valuetype", "$group_id AS config_group_id", "config_listvalues"],
                    ["cc_config_group AS g" => ["c.config_group_id", "g.id"]]
                ),
                ["group_title" => $from_conf]
            );
    }
}
