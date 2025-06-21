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
require_once __DIR__ . "/../../../common/lib/admin.defines.php";

Admin::checkPageAccess(Admin::ACX_DASHBOARD);

$distro_info = `uname -a`;
$info_tmp = explode(' ', $distro_info, 4);
$OS = $info_tmp[0] . ' ' . $info_tmp[2];
$UI = COPYRIGHT;
$UI_path = substr(__DIR__, 0, strrpos(__DIR__, "Public/admin/modules"));
$mysql = DbConnect()->ServerInfo()["version"];
$database = (new Table("cc_version", "version"))->getValue();
$asterisk = str_replace("Asterisk ", "", `asterisk -V`);
$php = phpversion();
$server_name = $_SERVER['SERVER_NAME'];

?>
<div class="card-text small">
    <strong><?= _("Server Name") ?>:</strong>&nbsp;<?= $server_name ?><br/>
    <strong><?= _("Operating System Version") ?>:</strong>&nbsp;<?= $OS ?><br/>
    <strong><?= _("Asterisk Version") ?>:</strong>&nbsp;<?= $asterisk ?><br/>
    <strong><?= _("PHP Version") ?>:</strong>&nbsp;<?= $php ?><br/>
    <strong><?= _("Database Version") ?>:</strong>&nbsp;<?= $mysql ?><br/>
    <strong><?= _("A2B Database Version") ?>:</strong>&nbsp;<?= $database ?><br/>
    <strong><?= _("User Interface Path") ?>:</strong>&nbsp;<?= $UI_path ?><br/>
    <strong><?= _("Copyright") ?>:</strong>&nbsp;<?= $UI ?><br/>
</div>