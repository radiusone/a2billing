#!/usr/bin/php -q
<?php

use A2billing\A2Billing;
use A2billing\ProcessHandler;
use A2billing\Table;

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

/***************************************************************************
 *            a2billing_archive_data_cront.php
 *
 *  Fri Oct 28 11:51:08 2005
 *  Copyright  2005  User
 *  ADD THIS SCRIPT IN A CRONTAB JOB
 *
    crontab -e
    0 12 * * * php /usr/local/a2billing/Cronjobs/a2billing_archive_data_cront.php

    field	 allowed values
    -----	 --------------
    minute	 		0-59
    hour		 	0-23
    day of month	1-31
    month	 		1-12 (or names, see below)
    day of week	 	0-7 (0 or 7 is Sun, or use names)

****************************************************************************/

set_time_limit(0);
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));

include (dirname(__FILE__) . "/lib/admin.defines.php");

// CHECK IF THE CRONT PROCESS IS ALREADY RUNNING
$pH= new ProcessHandler("/var/run/a2billing/a2billing_archive_data_cront_pid.php");
if ($pH->isActive()) {
    die(); // Already running!
} else {
    $pH->activate();
}

$A2B = new A2Billing($idconfig);
$logfile_cront_archive = $A2B->config['log-files']['cront_archive_data'] ?? "/tmp/a2billing_cront_archive_log";

write_log($logfile_cront_archive, basename(__FILE__) . ' line:' . __LINE__ . "[#### ARCHIVING DATA BEGIN ####]");

if (!$A2B->DbConnect()) {
    echo "[Cannot connect to the database]\n";
    write_log($logfile_cront_archive, basename(__FILE__) . ' line:' . __LINE__ . "[Cannot connect to the database]");
    exit;
}

$prior_x_month = $A2B->config["backup"]['archive_call_prior_x_month'];

$interval = "CURRENT_TIMESTAMP - INTERVAL ";
if ($A2B->config["database"]['dbtype'] == "postgres") {
    $interval .= "'$prior_x_month months'";
} else {
    $interval .= "$prior_x_month MONTH";
}

$func_fields = "sessionid, uniqueid, card_id, nasipaddress, starttime, stoptime, sessiontime, calledstation, sessionbill, id_tariffgroup, id_tariffplan, id_ratecard, id_trunk, sipiax, src, id_did, buycost, id_card_package_offer, real_sessiontime, dnid, terminatecauseid, destination, a2b_custom1, a2b_custom2";
(new Table("cc_call_archive"))->addRowsFromSelect($A2B->DBHandle, new Table("cc_call", $func_fields), ["starttime" => ["<=", $interval]]);
(new Table("cc_call"))->deleteRow($A2B->DBHandle, ["starttime" => ["<=", $interval]]);
write_log($logfile_cront_archive, basename(__FILE__) . ' line:' . __LINE__ . "[#### ARCHIVING DATA END ####]");
