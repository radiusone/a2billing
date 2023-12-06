<?php

use A2billing\Forms\FormHandler;

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
require_once "../../common/lib/admin.defines.php";
include './form_data/FG_var_config_group.inc';
/**
 * @var FormHandler $HD_Form
 * @var Smarty $smarty
 * @var string $id
 * @var string $form_action
 * @var string $CC_help_add_agi_confx
 */

$HD_Form -> init();

// #### HEADER SECTION
$smarty->display('main.tpl');

// #### HELP SECTION
echo $CC_help_add_agi_confx;

// #### TOP SECTION PAGE
$HD_Form->create_toppage($form_action);

[$new_group_title, $first_group_title] = agi_confx_title(); // calling function  to generate agi-conf(title_number)

$config = $HD_Form->DBHandle->GetAll(
    "SELECT config_title, config_key, config_value, config_description FROM cc_config LEFT JOIN cc_config_group ON config_group_id = cc_config_group.id WHERE group_title = ? ORDER BY config_key LIMIT 20",
    [$first_group_title]
);

?>
<div class="row-pb-3">
    <div class="col">
        <strong><?= sprintf(_("Creating a new group configuration named %s"), $new_group_title) ?></strong>
    </div>
</div>

<?php if (count($config)): ?>
<table class="table caption-top">
    <caption><?= sprintf(_("Partial list of configuration values (copied from %s)"), $first_group_title) ?></caption>
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

<div class="row my-4 justify-content-end">
    <div class="col-auto">
        <a class="btn btn-primary" href="A2B_entity_config_group.php?form_action=list&amp;agi_conf=<?= $new_group_title ?>&amp;from_conf=<?= $first_group_title ?>"><?= sprintf(_("Create %s"), $new_group_title) ?></a>
    </div>
</div>
<?php
// #### FOOTER SECTION
$smarty->display('footer.tpl');
