<?php

use A2billing\Agent;
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

require_once __DIR__ . "/../../common/lib/agent.defines.php";
require_once __DIR__ . "/../../common/form_data/FG_var_remittance_request.inc";
/**
 * @var FormHandler $HD_Form
 */

Agent::checkPageAccess(Agent::ACX_ACCESS);

getpost_ifset(["action", "id"]);
/**
 * @var string|null $action
 * @var numeric-string|null $id
 */
$action ??= "";
$id ??= null;

if ($action === "cancel" && $id) {
    $DBHandle = DbConnect();
    (new Table("cc_remittance_request"))
        ->updateRow(["status" => 3], ["id" => $id, "id_agent" => $_SESSION["agent_id"]]);
    die();
}
$HD_Form->init();

$form_action ??= "list";
$list = $HD_Form->perform_action($form_action);

require_once __DIR__ . "/templates/main.php";

$HD_Form->create_toppage($form_action);
$HD_Form->create_form($form_action, $list);
?>

<script>
document.querySelectorAll(".cancel_click").forEach(function (el) {
    el.addEventListener("click", function() {
        fetch(`A2B_entity_remittance_request.php?id=${this.dataset.primaryKey}&action=cancel`).then(() => location.reload());
    });
});
</script>

<?php
require_once __DIR__ . "/templates/footer.php";
