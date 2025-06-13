<?php

use A2billing\A2Billing;
use A2billing\Admin;
use PhpAgi\AMI;

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
/**
 * @var A2Billing $A2B
 */

Admin::checkPageAccess(Admin::ACX_MAINTENANCE);
require_once __DIR__ . "/templates/main.php";

getpost_ifset(["info"]);
$info ??= "summary";

$astman = new AMI();
$host = $A2B->config['global']['manager_host'] ?? "localhost";
$user = $A2B->config['global']['manager_username'] ?? "manager";
$pass = $A2B->config['global']['manager_secret'] ?? "secret";
$astman_connected = $astman->connect($host, $user, $pass);

$modes = [
    "summary" => [
        "label" => _("Summary"),
    ],
    "channels" => [
        "label" => _("Channels"),
        "items" => [
            _("Active Channel(s)") => "core show channels",
            _("SIP Channel(s)") => "pjsip show channels",
        ],
    ],
    "sip" => [
        "label" => _("SIP Info"),
        "items" => [
            _("SIP Endpoints") => "pjsip show endpoints",
            _("SIP Contacts") => "pjsip show contacts",
        ],
    ],
    "conferences" => [
        "label" => _("Conferences"),
        "items" => [
            _("Conference Info") => "confbridge list",
        ],
    ],
    "subscriptions" => [
        "label" => _("Subscriptions"),
        "items" => [
            _("Subscribe/Notify") => "core show hints"
        ],
    ],
    "voicemail" => [
        "label" => _("Voicemail Users"),
        "items" => [
            _("Voicemail users") => "voicemail show users",
        ],
    ],
    "codecs" => [
        "label" => _("Codecs"),
        "items" => [
            _("Codecs") => "core show translation",
        ],
    ],
    "all" => [
        "label" => _("Full Report"),
        "items" => [
            _("Version") => "core show version",
            _("Uptime") => "core show uptime",
            _("Active Channel(s)") => "core show channels",
            _("SIP Channel(s)") => "pjsip show channels",
            _("SIP Endpoints") => "pjsip show endpoints",
            _("SIP Contacts") => "pjsip show contacts",
            _("Codecs") => "core show translation",
            _("Subscribe/Notify") => "core show hints",
            _("Conference Info") => "confbridge list",
            _("Voicemail users") => "voicemail show users",
        ],
    ],
];
?>

<nav class="nav nav-pills nav-fill mb-3">
<?php foreach ($modes as $mode => $value): ?>
    <a
        class="nav-link <?= $info === $mode ? "active" : "" ?>"
        <?php if ($info === $mode): ?>aria-current="page" <?php endif?>
        href="?info=<?=$mode?>"
    >
        <?= htmlspecialchars($value["label"]) ?>
    </a>
<?php endforeach ?>
</nav>

<main class="container text-center">
    <div class="row mb-2">
        <div class="col">
            <h3><?= sprintf(_("Asterisk Info: %s"), $modes[$info]["label"]) ?></h3>
        </div>
    </div>
<?php if (!$astman_connected): ?>
    <div class="row mb-2">
        <div class="col">
            <h4 class="text-danger"><?= _("Asterisk Manager Error") ?></h4>
        </div>
    </div>
    <div class="row mb-2">
        <div class="col">
            <p><?= _("There was an error connecting to the Asterisk Manager Interface.") ?></p>
            <p><?= _("Ensure Asterisk is running, and that manager settings match those in Asterisk‘s manager.conf") ?></p>
        </div>
    </div>
<?php elseif ($info !== "summary"): ?>
    <?php foreach ($modes[$info]["items"] as $label => $command): ?>
    <div class="row mb-2">
        <div class="col">
            <h6 class="border-bottom"><?= htmlspecialchars($label) ?></h6>
        </div>
    </div>
    <div class="row mb-2">
        <div class="col">
            <pre class="text-start"><?= $astman->Command($command)["data"] ?? "" ?></pre>
        </div>
    </div>
    <?php endforeach ?>
<?php else: ?>
    <div class="row mb-2">
        <div class="col">
            <h6><?= htmlspecialchars(_("Summary")) ?></h6>
        </div>
    </div>
    <div class="row mb-2">
        <div class="col">
            <?= buildAsteriskInfo($astman) ?>
        </div>
    </div>
<?php endif ?>
    <div class="row mb-2">
        <div class="col text-center">
            <a class="btn btn-primary" href=""><?= $astman_connected ? _("Refresh") : _("Try Again") ?></a>
        </div>
    </div>
</main>

<?php
function buildAsteriskInfo(AMI $astman): string
{
    $uptime = nl2br($astman->Command("core show uptime")["data"] ?? "");
    $result = $astman->Command("pjsip show channels");
    $channels = sprintf(
        "Active channels: %d",
        preg_match("/Objects found: (\\d+)/", $result["data"] ?? "", $matches)
            ? $matches[1]
            : 0
    );

    $result = $astman->Command("pjsip show endpoints");
    $available_endpoints = sprintf(
        "Available endpoints: %d",
        preg_match_all("/\\bNot in use\\b/", $result["data"] ?? "") ?: 0
    );
    $unavailable_endpoints = sprintf(
        "Unavailable endpoints: %d",
        preg_match_all("/\\bUnavailable\\b/", $result["data"] ?? "") ?: 0
    );

    return <<< HTML
    <div class="container w-50 mx-auto">
        <div class="row mb-4">
            <div class="col">
                $uptime
            </div>
        </div>
        <div class="row mb-4">
            <div class="col">
                <p>$channels</p>
            </div>
        </div>
        <div class="row mb-4">
            <div class="col">
                <p>$available_endpoints</p>
            </div>
            <div class="col">
                <p>$unavailable_endpoints</p>
            </div>
        </div>
    </div>
    HTML;
}

require_once __DIR__ . "/templates/footer.php";
