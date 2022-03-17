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
 *            currencies_update_yahoo.php
 *
 *  ADD THIS SCRIPT IN A CRONTAB JOB
 *
    crontab -e
    0 6 * * * php /usr/local/a2billing/Cronjobs/currencies_update_yahoo.php

    field	 allowed values
    -----	 --------------
    minute	 		0-59
    hour		 	0-23
    day of month	1-31
    month	 		1-12 (or names, see below)
    day of week	 	0-7 (0 or 7 is Sun, or use names)

    The sample above will run the script every day at 6AM

****************************************************************************/

set_time_limit(120);
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));

include (dirname(__FILE__) . "/lib/admin.defines.php");

// CHECK IF THE CRONT PROCESS IS ALREADY RUNNING

$prcHandler = new ProcessHandler("/var/run/a2billing/currencies_update_yahoo_pid.php");

if ($prcHandler->isActive()) {
    die(); // Already running!
} else {
    $prcHandler->activate();
}

$FG_DEBUG = 0;
$A2B = new A2Billing();
$A2B -> load_conf();

// DEFINE FOR THE DATABASE CONNECTION
define ("BASE_CURRENCY", strtoupper($A2B->config["global"]['base_currency']));

$A2B -> load_conf($idconfig);
$cron_logfile = $A2B->config['log-files']['cront_currency_update'] ?? "/tmp/a2billing_cront_currency_log";

write_log($cron_logfile, basename(__FILE__).' line:'.__LINE__."[#### START CURRENCY UPDATE ####]");

if (!$A2B -> DbConnect()) {
    echo "[Cannot connect to the database]\n";
    write_log($cron_logfile, basename(__FILE__).' line:'.__LINE__."[Cannot connect to the database]");
    exit;
}

$instance_table = new Table();
$A2B -> set_table ($instance_table);

$return = currencies_update_yahoo($A2B -> DBHandle, $A2B -> table);
write_log($cron_logfile, basename(__FILE__).' line:'.__LINE__.$return, 0);

die();
