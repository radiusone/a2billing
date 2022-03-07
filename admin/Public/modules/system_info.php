<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of A2Billing (http://www.a2billing.net/)
 *
 * A2Billing, Commercial Open Source Telecom Billing platform,
 * powered by Star2billing S.L. <http://www.star2billing.com/>
 *
 * @copyright   Copyright (C) 2004-2015 - Star2billing S.L.
 * @author      Belaid Arezqui <areski@gmail.com>
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
require_once __DIR__ . "/../../lib/admin.defines.php";

if (!has_rights(ACX_DASHBOARD)) {
    Header("HTTP/1.0 401 Unauthorized");
    Header("Location: PP_error.php?c=accessdenied");
    die();
}

exec("uname -a 2> /dev/null", $output);

$distro_info = $output[0];
$info_tmp = explode(' ', $distro_info, 4);
$OS = $info_tmp[0] . ' ' . $info_tmp[2];

$info_tmp = explode(" - ", COPYRIGHT);
$UI = $info_tmp[0] . ' ' . $info_tmp[1];

$UI_path = substr(__DIR__, 0, strpos(__DIR__, "/admin"));

$DBHandle = DbConnect();
$rs = $DBHandle->Execute('SELECT VERSION()');
$rs = $rs->FetchRow();
$info_tmp = explode('-', $rs[0]);
$mysql = $info_tmp[1] . ' ' . $info_tmp[0];

$rs = $DBHandle->Execute('SELECT * FROM cc_version');
$rs = $rs->FetchRow();
$database = $rs[0];

$asterisk = str_replace('_','.',ASTERISK_VERSION);
$php = phpversion();
$server_name = $_SERVER['SERVER_NAME'];

?>
<div class="card-text">
<?= _("Operation System Version") ?>&nbsp;:&nbsp;<?= $OS ?><br/>
<?= _("Asterisk Version") ?>&nbsp;:&nbsp;<?= $asterisk ?><br/>
<?= _("PHP Version") ?>&nbsp;:&nbsp;<?= $php ?><br/>
<?= _("A2B Database Version") ?>&nbsp;:&nbsp;<?= $database ?><br/>
<?= _("User Interface") ?>&nbsp;:&nbsp;<?= $UI ?><br/>
<?= _("User Interface Path") ?>&nbsp;:&nbsp;<?= $UI_path ?><br/><br/>
<?= _("Server Name") ?>&nbsp;:&nbsp;<?= $server_name ?><br/>
<?= _("Database") ?>&nbsp;:&nbsp;<?= $mysql ?><br/>
</div>