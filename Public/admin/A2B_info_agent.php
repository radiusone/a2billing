<?php

use A2billing\Admin;
use A2billing\Connection;

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
require_once __DIR__ . "/../../common/lib/admin.defines.php";

Admin::checkPageAccess(Admin::ACX_ADMINISTRATOR);

getpost_ifset(["id"]);
/**
 * @var numeric-string|null $id
 */

if (empty($id)) {
    header("Location: A2B_entity_agent.php");
}

$agent = Connection::getConnection("cc_agent")
    ->where("id", $id)
    ->first();

if (empty($agent)) {
    header("Location: A2B_entity_agent.php");
}

require_once __DIR__ . "/templates/main.php";
?>
<div class="row pb-3 gx-5">
    <div class="col-6">
        <table class="table table-sm caption-top">
            <caption class="fw-bold fs-5"><?= _("Administrator Info") ?></caption>
            <tbody>
                <tr>
                    <th scope="row"><?= _("Login") ?></th>
                    <td><?= $agent["login"] ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Last name") ?></th>
                    <td><?= $agent["lastname"] ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("First name") ?></th>
                    <td><?= $agent["firstname"] ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Address") ?></th>
                    <td><?= $agent["address"] ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("City") ?></th>
                    <td><?= $agent["city"] ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Region") ?></th>
                    <td><?= $agent["state"] ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Postcode") ?></th>
                    <td><?= $agent["zipcode"] ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Country") ?></th>
                    <td><?= $agent["country"] ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Email") ?></th>
                    <td><?= $agent["email"] ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Phone") ?></th>
                    <td><?= $agent["phone"] ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Mobile/fax") ?></th>
                    <td><?= $agent["fax"] ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Balance") ?></th>
                    <td><?= get_money($agent["credit"]) ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Currency") ?></th>
                    <td><?= getCurrenciesList()[$agent["currency"]] ?? $agent["currency"] ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Tax rate") ?></th>
                    <td><?= get_percent($agent["vat"]) ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Language") ?></th>
                    <td><?= getLanguages()[$agent["language"]] ?? $agent["language"] ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="row pb-3 gx-5">
    <div class="col text-end">
        <a href="A2B_entity_agent.php?form_action=list">
            <?= _("Return to agent list") ?>
        </a>
    </div>
</div>

<?php
require_once __DIR__ . "/templates/footer.php";
