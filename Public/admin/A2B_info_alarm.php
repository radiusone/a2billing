<?php

use A2billing\Admin;
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

$menu_section = 16;
require_once __DIR__ . "/../../common/lib/admin.defines.php";

Admin::checkPageAccess(Admin::ACX_ADMINISTRATOR);

getpost_ifset(["id"]);
/**
 * @var numeric-string|null $id
 */

if (empty($id)) {
    header("Location: A2B_entity_alarm.php");
}

$alarm = (new Table("cc_alarm"))->getRow(["id" => $id]);
$items = (new Table("cc_alarm_report"))->getRows(["cc_alarm_id" => $id]);

if (empty($alarm)) {
    header("Location: A2B_entity_alarm.php");
}

require_once __DIR__ . "/templates/main.php";
?>
<div class="row pb-3 gx-5">
    <div class="col-6">
        <table class="table table-sm caption-top">
            <caption class="fw-bold fs-5"><?= _("Alarm Info") ?></caption>
            <tbody>
                <tr>
                    <th scope="row"><?= _("Name") ?></th>
                    <td><?= $alarm["name"] ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Type") ?></th>
                    <td><?= $alarm["type"] ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Number of runs") ?></th>
                    <td><?= $alarm["numberofrun"] ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Number of sent alarms") ?></th>
                    <td><?= $alarm["numberofalarm"] ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Time of last run") ?></th>
                    <td><?= $alarm["datelastrun"] ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="row pb-3 gx-5">
    <div class="col">
    <?php if ($items): ?>
    <table class="table table-sm table-striped caption-top">
        <caption class="fw-bold fs-5"><?= _("Reports") ?></caption>
        <thead>
        <tr>
            <th scope="col"><?= _("Date run") ?></th>
            <th scope="col"><?= _("Calculated value") ?></th>
        </tr>
        </thead>
        <tbody class="table-group-divider">
        <?php foreach ($items as $report): ?>
            <tr>
                <td><?= $report["daterun"] ?></td>
                <td><?= $report["calculatedvalue"] ?></td>
            </tr>
        <?php endforeach ?>
        </tbody>
    </table>
    <?php else: ?>
        <!-- <p><?= _("No reports for this alarm") ?></p> -->
    <?php endif ?>
    </div>
</div>

<div class="row pb-3 gx-5">
    <div class="col text-end">
        <a href="A2B_entity_alarm.php?form_action=list">
            <?= _("Return to alarm list") ?>
        </a>
    </div>
</div>

<?php
require_once __DIR__ . "/templates/footer.php";
