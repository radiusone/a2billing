<?php

use A2billing\A2Billing;
use A2billing\Admin;
use A2billing\Realtime;
use PhpAgi\AMI as AGI_AsteriskManager;

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

require_once __DIR__ . "/../../common/lib/admin.defines.php";
/**
 * @var A2Billing $A2B
 */

Admin::checkPageAccess(Admin::ACX_CUSTOMER);

getpost_ifset(["action", "voip_type"]);
/**
 * @var string|null $action
 * @var string|null $voip_type
 */
$action ??= "";
$voip_type ??= "";
$error_msg = "";
$buddyfile = "";

if ($action === "reload") {
    $as = new AGI_AsteriskManager();
    if ($as->connect(MANAGER_HOST, MANAGER_USERNAME, MANAGER_SECRET)) {
        if ($voip_type === "sipfriend") {
            $as->Command('sip reload');
        } elseif ($voip_type === "iaxfriend") {
            $as->Command('iax2 reload');
        } else {
            $as->Command('sip reload');
            $as->Command('iax2 reload');
        }
        $as->disconnect();
    } else {
        $error_msg= _("Cannot connect to the asterisk manager! Please check your manager configuration.");
    }
} elseif ($voip_type == "sipfriend") {
    $buddyfile = $A2B->config["webui"]["buddy_sip_file"];
    Realtime::create_trunk_config_file ("sip", $error_msg);
} else {
    $buddyfile = $A2B->config["webui"]["buddy_iax_file"];
    Realtime::create_trunk_config_file ("iax", $error_msg);
}

require_once __DIR__ . "/templates/main.php";

echo create_help(_("Click reload to commit changes to Asterisk"));
?>
<div class="row pb-3">
    <div class="col">
<?php if ($error_msg): ?>
        <p class="alert alert-danger"><?= $error_msg ?></p>
<?php else: ?>
        <p class="alert alert-success">
    <?php if ($action !== "reload" && $voip_type === "sipfriend"): ?>
            <?= sprintf(_("The SIP config file %s has been generated"), $buddyfile) ?>
    <?php elseif ($action !== "reload" && $voip_type === "iaxfriend"): ?>
            <?= sprintf(_("The IAX config file %s has been generated"), $buddyfile) ?>
    <?php elseif ($action == "reload"): ?>
            <?= _("Asterisk has been reloaded") ?>
    <?php endif ?>
        </p>
<?php endif ?>
    </div>
</div>

<div class="row pb-3 justify-content-center">
    <div class="col-auto">
        <a href="?action=reload&voip_type=<?= $voip_type ?>" class="btn btn-primary">
            <?= _("Click to reload your Asterisk server") ?>
        </a>
    </div>
</div>

<?php
require_once __DIR__ . "/templates/footer.php";
