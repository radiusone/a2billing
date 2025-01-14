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
 * @copyright   Copyright © 2004-2015 - Star2billing S.L.
 * @copyright   Copyright © 2022-2025 RadiusOne Inc.
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

$menu_section = 8;
require_once __DIR__ . "/../../common/lib/admin.defines.php";
require_once __DIR__ . "/../../common/form_data/FG_var_diduse.inc";
/**
 * @var FormHandler $HD_Form
 * @var string $action set when the release button is used
 * @var string $did the DID ID to be released
 */
Admin::checkPageAccess(Admin::ACX_DID);

$HD_Form->init();

$form_action ??= "list";
$action ??= "";

require_once __DIR__ . "/../templates/main.php";

// #### TOP SECTION PAGE
$HD_Form->create_toppage($form_action);

if ($action === "ask_release") {
    echo create_help(
        _("Releasing a DID puts it in free state, and the user will not be charged monthly any more.")
    );
    $csrf = $HD_Form->csrf_inputs();
    $text = _("If you really want release this DID, click on the release button.");
    $release = _("Release");
    echo <<< HTML
        <form action="A2B_entity_did_use.php" method="post">
            <input type="hidden" name="did" value="$did">
            <input type="hidden" name="action" value="confirmed_release">
            $csrf
            <p>$text</p>
            <p><button type="submit" class="btn btn-danger">$release</button></p>
        </form>
        HTML;
} elseif ($action === "confirmed_release") {
    (new Table("cc_did"))
        ->updateRow($HD_Form->DBHandle, ["iduser" => 0, "reserved" => 0], ["id" => $did]);
    (new Table("cc_did_use"))
        ->updateRow($HD_Form->DBHandle, ["releasedate" => "CURRENT_TIMESTAMP"], ["id_did" => $did, "activated" => 1]);
    (new Table("cc_did_use"))
        ->addRow($HD_Form->DBHandle, ["activated" => 0, "id_did" => $did]);
    (new Table("cc_did_destination"))
        ->deleteRow($HD_Form->DBHandle, ["id_cc_did" => $did]);
}

if ($action !== "ask_release") {
    $list = $HD_Form->perform_action($form_action);
    $HD_Form->create_search_form();
    $HD_Form->create_form($form_action, $list) ;
}

require_once __DIR__ . "/../templates/footer.php";
