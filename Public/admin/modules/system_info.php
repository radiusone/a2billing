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
require_once __DIR__ . "/../../../common/lib/admin.defines.php";

Admin::checkPageAccess(Admin::ACX_DASHBOARD);

$os_file = "/etc/os-release";
$release = is_readable($os_file) ? parse_ini_file($os_file) : [];
$OS = $release["PRETTY_NAME"] ?? null;
$version = $release["VERSION_ID"] ?? null;
$name = $release["ID"] ?? null;

if (!$OS && is_executable("/usr/bin/lsb_release")) {
    $OS = trim(exec("lsb_release -s -d"));
} elseif (is_readable("/etc/redhat-release")) {
    $OS = file_get_contents("/etc/redhat-release");
}

if ($OS && $version && $name === "debian") {
    $debian_file = "/etc/debian_version";
    if (is_readable($debian_file)) {
        $debian_version = file_get_contents($debian_file);
        $OS = str_replace($version, trim($debian_version), $OS);
    }
}

$kernel = exec("uname -r");

$UI = COPYRIGHT;
$UI_path = substr(__DIR__, 0, strrpos(__DIR__, "Public/admin/modules"));
$mysql = Connection::getConnection()->select("SELECT VERSION()")[0][0];
$database = Connection::getConnection()->table("cc_version")->value("version");
$asterisk = str_replace("Asterisk ", "", exec("asterisk -V"));
$php = phpversion();
$server_name = $_SERVER["SERVER_NAME"];

?>
<div class="card-text small">
    <strong><?= _("Server Name") ?>:</strong>&nbsp;<?= $server_name ?><br/>
    <?php if ($OS): ?><strong><?= _("Operating System") ?>:</strong>&nbsp;<?= $OS ?><br/><?php endif ?>
    <strong><?= _("Kernel Version") ?>:</strong>&nbsp;<?= $kernel ?><br/>
    <strong><?= _("Asterisk Version") ?>:</strong>&nbsp;<?= $asterisk ?><br/>
    <strong><?= _("PHP Version") ?>:</strong>&nbsp;<?= $php ?><br/>
    <strong><?= _("Database Version") ?>:</strong>&nbsp;<?= $mysql ?><br/>
    <strong><?= _("A2B Database Version") ?>:</strong>&nbsp;<?= $database ?><br/>
    <strong><?= _("User Interface Path") ?>:</strong>&nbsp;<?= $UI_path ?><br/>
    <strong><?= _("Copyright") ?>:</strong>&nbsp;<?= $UI ?><br/>
</div>
