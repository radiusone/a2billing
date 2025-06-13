<?php

use A2billing\A2Billing;
use A2billing\Agent;
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
/**
 * @var A2Billing $A2B
 */

Agent::checkPageAccess(Agent::ACX_ACCESS);

$DBHandle = DbConnect();
$table = new Table(
    "cc_agent",
    [
        "credit", "currency", "lastname", "firstname", "address", "city", "state", "country",
        "zipcode", "phone", "email", "fax", "cc_agent.id", "com_balance", "threshold_remittance",
        "(SELECT COALESCE(SUM(amount), 0) FROM cc_remittance_request WHERE status = 0 AND id_agent = cc_agent.id) AS remit"
    ],
);
$agent_info = $table->getRow($DBHandle, ["id" => $_SESSION["agent_id"]]);
if (!$agent_info) {
    exit();
}

$credit_cur = get_money($agent_info["credit"], 2, $agent_info["currency"]);
$remittance_value_cur = get_money($agent_info["remit"], 2, $agent_info["currency"]);
$commision_bal_cur  =  get_money($agent_info["com_balance"], 2, $agent_info["currency"]);

require_once __DIR__ . "/templates/main.php";
?>
<div class="row pb-3 gx-5">
    <div class="col-6">
        <table class="table table-sm caption-top">
            <caption class="fw-bold fs-5"><?= _("Agent Info") ?></caption>
            <tbody>
            <tr>
                <th scope="row"><?= _("Last name") ?></th>
                <td><?= $agent_info["lastname"] ?></td>
            </tr>
            <tr>
                <th scope="row"><?= _("First name") ?></th>
                <td><?= $agent_info["firstname"] ?></td>
            </tr>
            <tr>
                <th scope="row"><?= _("Address") ?></th>
                <td><?= $agent_info["address"] ?></td>
            </tr>
            <tr>
                <th scope="row"><?= _("City") ?></th>
                <td><?= $agent_info["city"] ?></td>
            </tr>
            <tr>
                <th scope="row"><?= _("Region") ?></th>
                <td><?= $agent_info["state"] ?></td>
            </tr>
            <tr>
                <th scope="row"><?= _("Postcode") ?></th>
                <td><?= $agent_info["zipcode"] ?></td>
            </tr>
            <tr>
                <th scope="row"><?= _("Country") ?></th>
                <td><?= $agent_info["country"] ?></td>
            </tr>
            <tr>
                <th scope="row"><?= _("Email") ?></th>
                <td><?= $agent_info["email"] ?></td>
            </tr>
            <tr>
                <th scope="row"><?= _("Phone") ?></th>
                <td><?= $agent_info["phone"] ?></td>
            </tr>
            <tr>
                <th scope="row"><?= _("Mobile/fax") ?></th>
                <td><?= $agent_info["fax"] ?></td>
            </tr>
            <tr>
                <th scope="row"><?= _("Balance") ?></th>
                <td><?= $credit_cur ?></td>
            </tr>
            <tr>
                <th scope="row"><?= _("Outstanding Remittances") ?></th>
                <td><?= $remittance_value_cur ?></td>
            </tr>
            <tr>
                <th scope="row"><?= _("Commission Accrued") ?></th>
                <td><?= $commision_bal_cur ?></td>
            </tr>
            </tbody>
        </table>
    </div>
</div>
<?php if ($A2B->config["webagentui"]['personalinfo']): ?>
<div class="row pb-3 gx-5">
    <div class="col text-end">
        <a href="A2B_entity_agent.php?form_action=ask-edit&id=<?= $_SESSION["agent_id"]?>"><?= _("EDIT PERSONAL INFORMATION");?></a>
    </div>
</div>
<?php endif ?>
<?php if ($A2B->config["webagentui"]['remittance_request'] && $agent_info["remit"] <= 0 && $agent_info["com_balance"] > $agent_info["threshold_remittance"]): ?>
<div class="row pb-3 gx-5">
    <div class="col text-end">
        <a href="A2B_remittance_request.php"><?= _("REMITTANCE REQUEST");?></a>
    </div>
</div>
<?php endif ?>

<?php
require_once __DIR__ . "/templates/footer.php";
