<?php

use A2billing\Admin;

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

$menu_section = 3;
require_once __DIR__ . "/../../common/lib/admin.defines.php";

Admin::checkPageAccess(Admin::ACX_ADMINISTRATOR);

getpost_ifset(["id", "groupID"]);
/**
 * @var numeric-string|null $id
 * @var numeric-string|null $groupID
 */

$groupID ??= 0;

if (empty($id)) {
    header("Location: A2B_entity_user.php?groupID=$groupID");
}

$DBHandle  = DbConnect();
$admin = $DBHandle->GetRow("SELECT * FROM cc_ui_authen WHERE userid = ?", [$id]);
if (!$admin) {
    header("Location: A2B_entity_user.php?groupID=$groupID");
}

require_once __DIR__ . "/../templates/main.php";

?>
<div class="row pb-3 gx-5">
    <div class="col-6">
        <table class="table table-sm caption-top">
            <caption class="fw-bold fs-5"><?= _("Administrator Info") ?></caption>
            <tbody>
                <tr>
                    <th scope="row"><?= _("Login") ?></th>
                    <td><?= $admin["login"] ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Name") ?></th>
                    <td><?= $admin["name"] ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Address") ?></th>
                    <td><?= $admin["direction"] ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("City") ?></th>
                    <td><?= $admin["city"] ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Region") ?></th>
                    <td><?= $admin["state"] ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Postcode") ?></th>
                    <td><?= $admin["zipcode"] ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Country") ?></th>
                    <td><?= $admin["country"] ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Email") ?></th>
                    <td><?= $admin["email"] ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Phone") ?></th>
                    <td><?= $admin["phone"] ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Mobile/fax") ?></th>
                    <td><?= $admin["fax"] ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="row pb-3 gx-5">
    <div class="col text-end">
        <a href="A2B_entity_user.php?form_action=list&groupID=<?=$groupID?>">
            <?= _("Return to administrator list") ?>
        </a>
    </div>
</div>

<?php
require_once __DIR__ . "/../templates/footer.php";
