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
 * @copyright   Copyright © 2004-2015 - Star2billing S.L.
 * @copyright   Copyright © 2022 RadiusOne Inc.
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

$menu_section = 18;
require_once __DIR__ . "/../../common/lib/admin.defines.php";
require_once __DIR__ . "/form_data/FG_var_config_group.inc";
/**
 * @var FormHandler $HD_Form
 * @var string $form_action
 */

$form_action ??= "";
$HD_Form -> init();

require_once __DIR__ . "/../templates/main.php";

$HD_Form->create_toppage($form_action);

$table = new Table(
    "cc_config",
    [
        "config_title",
        "config_key",
        "config_value",
        "config_description",
        "(SELECT CONCAT('agi-conf', REPLACE(MAX(group_title), 'agi-conf', '') + 1) FROM cc_config_group WHERE group_title LIKE 'agi-conf%') AS new_title",
    ],
    ["cc_config_group" => ["config_group_id", "cc_config_group.id"]]
);
$config = $table->getRows(
    $HD_Form->DBHandle,
    ["group_title" => "agi-conf1"],
    ["config_key"],
    "ASC",
    [],
    20
);
$new_group_title = $config[0]["new_title"];

?>
<div class="row-pb-3">
    <div class="col">
        <strong><?= sprintf(_("Creating a new group configuration named %s"), $new_group_title) ?></strong>
    </div>
</div>

<?php if (count($config)): ?>
<table class="table caption-top">
    <caption><?= _("Partial list of configuration values (copied from agi-conf1)") ?></caption>
    <thead>
    <tr>
        <th><?= _("Title") ?></th>
        <th><?= _("Key") ?></th>
        <th><?= _("Value") ?></th>
        <th><?= _("Description") ?></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($config as $conf): ?>
        <tr>
            <td><?= $conf["config_title"] ?></td>
            <td><?= $conf["config_key"] ?></td>
            <td><?= $conf["config_value"] ?></td>
            <td><?= $conf["config_description"] ?></td>
        </tr>
    <?php endforeach ?>
    </tbody>
</table>
<?php endif ?>

<form class="row my-4 justify-content-end" method="post" action="A2B_entity_config_group.php">
    <div class="col-auto">
        <input type="hidden" name="agi_conf" value="<?= $new_group_title ?>"/>
        <input type="hidden" name="from_conf" value="agi-conf1"/>
        <button type="submit" class="btn btn-primary"><?= sprintf(_("Create %s"), $new_group_title) ?></button>
    </div>
</form>

<?php
require_once __DIR__ . "/../templates/footer.php";
