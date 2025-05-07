<?php

use A2billing\Agent;
use A2billing\Customer;
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

$menu_section = 2;
require_once __DIR__ . "/../../common/lib/agent.defines.php";
Agent::checkPageAccess(Agent::ACX_BILLING);

getpost_ifset(["id", "type"]);
/**
 * @var numeric-string|null $id
 * @var string|null $type
 */

$type ??= "card";
$page = "A2B_entity_payment" . ($type === "agent" ? "_agent" : "") . ".php";

if (empty($id)) {
    header("Location: $page");
}

if ($type === "agent") {
    $table = new Table("cc_logpayment_agent");
    $cond = ["agent_id" => $_SESSION["agent_id"], "id" => $id];
} else {
    $table = new Table(
        "cc_logpayment",
        ["*"],
        ["cc_card" => ["cc_card.id", "cc_logpayment.card_id"], "cc_card_group" => ["cc_card.id_group", "cc_card_group.id"]]
    );
    $cond = ["cc_card_group.id_agent" => $_SESSION["agent_id"], "cc_logpayment.id" => $id];
}
$DBHandle  = DbConnect();
$payment = $table->getRow($DBHandle, $cond);

if (empty($payment)) {
    header("Location: $page");
}

require_once __DIR__ . "/../templates/main.php";
?>
<div class="row pb-3 gx-5">
    <div class="col-6">
        <table class="table table-sm caption-top">
            <caption class="fw-bold fs-5"><?= _("Payment Info") ?></caption>
            <tbody>
                <tr>
                    <th scope="row"><?= $type === "agent" ? _("Agent") : _("Account number") ?></th>
                    <td>
                        <?php if ($type === "agent"): ?>
                        <?= Agent::getName($payment["agent_id"], false) ?>
                        <?php elseif (Agent::allowed(Agent::ACX_CUSTOMER)): ?>
                        <?= Customer::getInfoLink($payment["card_id"]) ?>
                        <?php else: ?>
                        <?= Customer::getName($payment["card_id"], false) ?>
                        <?php endif ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Amount") ?></th>
                    <td><?= get_money($payment["payment"]) ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Date") ?></th>
                    <td><?= get_readable_date($payment["date"]) ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Type") ?></th>
                    <td><?= getRefillType_List()[$payment["payment_type"]] ?? $payment["payment_type"] ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Description") ?></th>
                    <td><?= $payment["description"] ?></td>
                </tr>
                <?php if (!empty($payment["id_logrefill"])): ?>
                <tr>
                    <th scope="row"><?= _("Refill link") ?></th>
                    <td>
                        <?= $type === "agent" ? get_agent_refill_link($payment["id_logrefill"]) : get_refill_link($payment["id_logrefill"]) ?>
                    </td>
                </tr>
                <?php endif ?>
            </tbody>
        </table>
    </div>
</div>

<div class="row pb-3 gx-5">
    <div class="col text-end">
        <a href="<?= $page ?>?form_action=list">
            <?= _("Return to payment list") ?>
        </a>
    </div>
</div>

<?php
require_once __DIR__ . "/../templates/footer.php";
