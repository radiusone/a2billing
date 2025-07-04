<?php

use A2billing\Agent;
use A2billing\A2Billing;
use A2billing\Notification;
use A2billing\NotificationsDAO;
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

if (!$A2B->config["webagentui"]['remittance_request']) {
    header("HTTP/1.0 401 Unauthorized");
    header("Location: PP_error.php?c=accessdenied");
    die();
}

getpost_ifset(["amount","remittance_type","action"]);
/**
 * @var numeric-string|null $amount
 * @var string|null $remittance_type
 * @var string|null $action
 */

$DBHandle_max = DbConnect();
$agent_info = (new Table("cc_agent", ["credit", "currency", "com_balance", "threshold_remittance", "firstname", "lastname", "address", "bank_info"]))
    ->getRow(["id" => Agent::id()]);
if (!$agent_info) {
    exit();
}

$currencies_list = get_currencies();
$two_currency = false;
$mycur = $currencies_list[strtoupper($agent_info["currency"])]["value"] ?? 1;
if (strtoupper($agent_info['currency']) !== strtoupper(BASE_CURRENCY)) {
    $two_currency = true;
}

$credit_cur = $agent_info['credit'] / $mycur;
$commision_bal_cur = $agent_info['com_balance'] / $mycur;
$threshold_cur = $agent_info['threshold_remittance'] / $mycur;

$result = (new Table("cc_remittance_request", "amount"))
    ->getValue(["id_agent" => Agent::id(), "status" => 0]);

if ($result) {
    $remittance_in_progress = true;
    $remittance_value = $result;
} else {
    $remittance_in_progress = false;
    $remittance_value = 0;
}
$remittance_value_cur = $remittance_value / $mycur;

$action ??= "";
$amount_rounded = 0;
$amount_gobal_cur = 0;
$err_msg = "";

if (!$remittance_in_progress && ($action === "check" || $action === "add")) {
    if ($two_currency) {
        $amount_gobal_cur = a2b_round($amount * $mycur);
        $amount_rounded = a2b_round($amount_gobal_cur / $mycur);
    } else {
        $amount_rounded = $amount_gobal_cur = a2b_round($amount);
    }

    if ($amount_rounded < $threshold_cur) {
        $err_msg = _("Invalid amount, is higher than the threshold to authorize a remittance");
    }
    if ($amount_rounded > $commision_bal_cur) {
        $err_msg = _("Invalid amount, is higher than your commission Accrued");
    }

    if ($action === "add" && empty($err_msg)) {
        $type = $remittance_type == "BANK" ? 1 : 0;
        $insert = (new Table("cc_remittance_request"))
            ->addRow(
                ["id_agent" => Agent::id(), "amount" => $amount_gobal_cur, "type" => $type],
                "id",
                $id
            );
        NotificationsDAO::addNotification(
            "remittance_added_agent",
            Notification::$MEDIUM,
            Notification::$AGENT,
            Agent::id(),
            Notification::$LINK_REMITTANCE,
            $id
        );
    }
}

require_once __DIR__ . "/templates/main.php";

if (empty($action)) {
    echo create_help(gettext("On this page you will be able to create a remittance Remittance Request according to the commission accrued on your account.If the commission accrued is higher than a predefined threshold then it will be possible to ask a transfer on your balance or by a funds transfer."));
}
?>

<div class="row pb-3 gx-5">
    <div class="col-6">
        <table class="table table-sm caption-top">
            <caption class="fw-bold fs-5"><?= _("Remittance Info") ?></caption>
            <tbody>
            <tr>
                <th scope="row"><?= _("Balance Remaining") ?></th>
                <td>
                    <?= get_money($agent_info["credit"], 2, $agent_info["currency"]) ?>
                </td>
            </tr>
            <tr>
                <th scope="row"><?= _("Request Threshold") ?></th>
                <td><?= get_money($agent_info["threshold_remittance"], 2, $agent_info["currency"]) ?></td>
            </tr>
            <tr>
                <th scope="row"><?= _("Commission Accrued") ?></th>
                <td><?= get_money($agent_info["com_balance"], 2, $agent_info["currency"]) ?></td>
            </tr>
            <?php if ($remittance_in_progress): ?>
                <tr>
                    <th scope="row"><?= _("Remittance in Progress") ?></th>
                    <td><?= get_money($remittance_value, 2, $agent_info["currency"]) ?></td>
                </tr>
            <?php elseif (empty($action)): ?>
                <tr>
                    <th scope="row"><?= _("Remittance Status") ?></th>
                    <td><?= $commision_bal_cur >= $threshold_cur && $commision_bal_cur > 0 ? _("Available") : _("Not Available") ?></td>
                </tr>
            <?php endif ?>
            </tbody>
        </table>
    </div>
</div>

<?php if (empty($action)): ?>
    <?php if (!$remittance_in_progress && $commision_bal_cur >= $threshold_cur && $commision_bal_cur > 0): ?>
<form action="" method="post">
    <div class="row pb-3">
        <label class="form-col-label col-3" for="amount"><?= _("Amount") ?></label>
        <div class="col">
            <div class="input-group">
                <input type="number" name="amount" id="amount" class="form-control" min="<?= $threshold_cur ?>" max="<?= $commision_bal_cur ?>" step="0.01" required="required"/>
                <div class="input-group-text"><?= $agent_info["currency"] ?></div>
            </div>
            <?php if ($threshold_cur): ?>
                <div class="form-text">
                    <?= sprintf(_("You have to use an amount higher than %s"), get_money($agent_info["threshold_remittance"], 2, $agent_info["currency"])) ?>
                </div>
            <?php endif ?>
        </div>
    </div>
    <div class="row pb-3">
        <label class="form-col-label col-3" for="remittance_type"><?= _("Type") ?></label>
        <div class="col">
            <select name="remittance_type" id="remittance_type" class="form-select">
                <option value="BALANCE"><?= _("Balance Withdrawl") ?></option>
                <option value="BANK"><?= _("Bank Withdrawl") ?></option>
            </select>
        </div>
    </div>
    <div class="py-4 align-items-end">
        <div class="col-auto">
            <button type="submit" class="btn btn-primary"><?= _("Confirm") ?></button>
        </div>
    </div>
</form>
    <?php elseif (!$remittance_in_progress): ?>
<div class="alert alert-warning" role="alert">
    <?= _("Remittance Request is not available") ?>
</div>
    <?php else: ?>
<div class="alert alert-info" role="alert">
    <?= sprintf(_("One remittance request is already in progress for %s"), get_money($remittance_value, 2, $agent_info["currency"])) ?>
</div>
    <?php endif ?>
<?php elseif (!$remittance_in_progress): ?>
<div class="row pb-3 gx-5">
    <div class="col-6">
        <table class="table table-sm caption-top">
            <caption><?= _("Remittance Request Confirmation") ?></caption>
            <tbody>
            <tr>
                <th scope="row"><?= _("First Name") ?></th>
                <td><?= $agent_info["firstname"] ?></td>
            </tr>
            <tr>
                <th scope="row"><?= _("Last Name") ?></th>
                <td><?= $agent_info["lastname"] ?></td>
            </tr>
            <tr>
                <th scope="row"><?= _("Address") ?></th>
                <td><?= $agent_info["address"] ?></td>
            </tr>
            <tr>
                <th scope="row"><?= $amount !== $amount_rounded ? _("Amount (rounded for currency)") : _("Amount") ?></th>
                <td><?= $amount_rounded ?> <?= $agent_info["currency"] ?></td>
            </tr>
            <?php if ($two_currency): ?>
                <tr>
                    <th scope="row"><?= sprintf(_("Amount in %s"), BASE_CURRENCY) ?></th>
                    <td><?= $amount_gobal_cur ?></td>
                </tr>
            <?php endif ?>
            <tr>
                <th scope="row"><?= _("Type") ?></th>
                <td><?= $remittance_type === "BANK" ? _("Withdraw to your bank account") : _("Withdraw to your balance") ?></td>
            </tr>
            <?php if ($remittance_type === "BANK"): ?>
                <tr>
                    <th scope="row"><?= _("Bank Info") ?></th>
                    <td><?= $agent_info["bank_info"] ?></td>
                </tr>
            <?php endif ?>
            </tbody>
        </table>
    </div>
</div>
    <?php if ($action === "check"): ?>
<div class="row pb-3">
    <div class="col-auto">
        <form method="post" action="" name="frmRemittance">
            <input type="hidden" name="action" value="add"/>
            <input type="hidden" name="amount" value="<?php echo $amount_gobal_cur?>" />
            <input type="hidden" name="remittance_type" value="<?php echo $remittance_type?>" />
            <button type="submit" class="btn btn-primary"><?= _("Confirm") ?></button>
        </form>
    </div>
</div>
    <?php endif ?>
<?php endif ?>

<?php

require_once __DIR__ . "/templates/footer.php";
