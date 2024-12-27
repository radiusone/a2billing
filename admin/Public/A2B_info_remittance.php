<?php

use A2billing\Admin;
use A2billing\Agent;

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

$menu_section = 10;
require_once __DIR__ . "/../../common/lib/admin.defines.php";

if (is_admin()) {
    Admin::checkPageAccess(Admin::ACX_BILLING);
} else {
    Agent::checkPageAccess(Agent::ACX_BILLING);
}

getpost_ifset(["id"]);
/**
 * @var numeric-string|null $id
 */

if (empty($id)) {
    header("Location: A2B_entity_logrefill.php");
}

$DBHandle  = DbConnect();

$remittance = $DBHandle->GetRow("SELECT * FROM cc_remittance_request WHERE id = ?", [$id]);

if (empty($remittance)) {
    header("Location: A2B_entity_remittance_request.php");
}

require_once __DIR__ . "/../templates/main.php";
?>
<div class="row pb-3 gx-5">
    <div class="col-6">
        <table class="table table-sm caption-top">
            <caption class="fw-bold fs-5"><?= _("Remittance Info") ?></caption>
            <tbody>
                <tr>
                    <th scope="row"><?= _("Agent") ?></th>
                    <td>
                        <?= Agent::getName($remittance["id_agent"], Admin::allowed(Admin::ACX_MODIFY_AGENTS)) ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Amount") ?></th>
                    <td><?= get_money($remittance["amount"]) ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Date") ?></th>
                    <td><?= get_readable_date($remittance["date"]) ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Type") ?></th>
                    <td><?= getRemittanceType_List()[$remittance["type"]] ?? $remittance["type"] ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Status") ?></th>
                    <td><?= getRemittanceStatus_List()[$remittance["status"]] ?? $remittance["status"] ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="row pb-3 gx-5">
    <div class="col text-end">
        <a href="A2B_entity_remittance_request.php?form_aciton=list">
            <?= _("Return to payment list") ?>
        </a>
    </div>
</div>

<?php
require_once __DIR__ . "/../templates/footer.php";
