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

use A2billing\Table;

require_once __DIR__ . "/../../lib/admin.defines.php";

Admin::checkPageAccess(Admin::ACX_DASHBOARD);

$QUERY_COUNT_CARD_ALL = "SELECT status, COUNT(*) FROM cc_card GROUP BY status";

$result = DbConnect()->GetAll($QUERY_COUNT_CARD_ALL);
$count_total = 0;
$states = [];
if ($result === false) {
    die();
}
foreach ($result as $row) {
    $count_total += $row[1];
    $states[$row[0]] = $row[1];
    // 0 = cancelled, 1 = active, 2 = new, 3 = waiting, 4 = reserved, 5 = expired, 6|7 = suspended
}
?>
<div class="card-text small">
    <strong><?= _("Total Number of Accounts") ?>:</strong>&nbsp;<?= $count_total ?><br/>
    <?php if ($states[1]): ?><strong><?= _("Total Number of Active Accounts") ?>:</strong>&nbsp;<?= $states[1] ?><br/><?php endif ?>
    <?php if ($states[0]): ?><strong><?= _("Cancelled Accounts") ?>:</strong>&nbsp;<?= $states[0] ?><br/><?php endif ?>
    <?php if ($states[2]): ?><strong><?= _("New Accounts") ?>:</strong>&nbsp;<?= $states[2] ?><br/><?php endif ?>
    <?php if ($states[3]): ?><strong><?= _("Account not yet Activated") ?>:</strong>&nbsp;<?= $states[3] ?><br/><?php endif ?>
    <?php if ($states[4]): ?><strong><?= _("Accounts Reserved") ?>:</strong>&nbsp;<?= $states[4] ?><br/><?php endif ?>
    <?php if ($states[5]): ?><strong><?= _("Accounts Expired") ?>:</strong>&nbsp;<?= $states[5] ?><br/><?php endif ?>
    <?php if ($states[6] + $states[7]): ?><strong><?= _("Accounts Suspended") ?>:</strong>&nbsp;<?= $states[6] + $states[7] ?><br/><?php endif ?>
</div>
