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

/***************************************************************************
 *            a2billing_batch_cache.php
 *
 *  Fri Oct 21 11:51 2008
 *  Copyright  2008  A2Billing
 *  ADD THIS SCRIPT IN A CRONTAB JOB
 *
 *  Description :
 *  This script will read the sqlite Database and import the CDR to the main DB
 *  The import is processed by block in order to optimize the queries
 *
 *
    crontab -e
    * / 15 * * * * php /usr/local/a2billing/Cronjobs/a2billing_batch_cache.php

    field	 allowed values
    -----	 --------------
    minute	 		0-59
    hour		 	0-23
    day of month	1-31
    month	 		1-12 (or names, see below)
    day of week	 	0-7 (0 or 7 is Sun, or use names)

    #Run command every 5 minutes during 6-13 hours
    * / 5 6-13 * * mon-fri test.script    !!! no space between * / 5

****************************************************************************/

set_time_limit(0);
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));

include (dirname(__FILE__) . "/../common/lib/admin.defines.php");

// CHECK IF THE CRONT PROCESS IS ALREADY RUNNING
$pH= new ProcessHandler("/var/run/a2billing/a2billing_batch_cache_pid.php");
if ($pH->isActive()) {
    die(); // Already running!
} else {
    $pH->activate();
}

$verbose_level = 1;
$nb_record = 100;
$wait_time = 10;

$A2B = new A2Billing($idconfig);
$logfile_cront_batch = $A2B->config['log-files']['cront_batch_process'] ?? "/tmp/a2billing_cront_batch_log";

write_log($logfile_cront_batch, basename(__FILE__) . ' line:' . __LINE__ . "[#### IMPORT CACHE CRONT START ####]");

if (!$A2B->DbConnect()) {
    if ($verbose_level >= 1) {
        echo "[Cannot connect to the database]\n";
    }
    write_log($logfile_cront_batch, basename(__FILE__) . ' line:' . __LINE__ . "[Cannot connect to the database]");
    exit;
}

$instance_table = new Table("cc_call");

if ($A2B->config["global"]['cache_enabled']) {
    if (empty ($A2B->config["global"]['cache_path'])) {
        if ($verbose_level >= 1) {
            echo "[Path to the cache is not defined]\n";
        }

        write_log($logfile_cront_batch, basename(__FILE__) . ' line:' . __LINE__ . "[Path to the cache is not defined]");
        exit;
    }

    if (!file_exists($A2B->config["global"]['cache_path'])) {
        if ($verbose_level >= 1) {
            echo "[File doesn't exist or permission denied]\n";
        }

        write_log($logfile_cront_batch, basename(__FILE__) . ' line:' . __LINE__ . "[File doesn't exist or permission denied]");
        exit;
    }

    // Open Sqlite
    $db = NewADOConnection("pdo");
    if ($db->Connect("sqlite:" . $A2B->config["global"]['cache_path'])) {

        for (;;) {
            // Select CDR
            $result = $db->Execute("SELECT rowid , * from cc_call limit $nb_record");
            if ($result) {
                $values = [];
                $delete_id = "";
                $i = 0;
                while($row = $result->FetchRow()) {
                    $delete_id .= $row['rowid'] . ",";
                    unset($row["rowid"]);
                    $values[] = $row;
                    $i++;
                }
                $delete_id = "(" . trim($delete_id, ",") . ")";
                $instance_table->addRows($values);

                $DELETE_QUERY = "DELETE FROM cc_call WHERE rowid in $delete_id";
                if ($verbose_level >= 1) {
                    echo "QUERY DELETE : [$DELETE_QUERY]\n";
                }
                $db->Execute($DELETE_QUERY);

            }
            echo "Waiting ....\n";
            sleep($wait_time);
        }

    } else {
        if ($verbose_level >= 1) {
            echo "[Error to connect to cache : " . $db->ErrorMsg() . "]\n";
        }
        write_log($logfile_cront_batch, basename(__FILE__) . ' line:' . __LINE__ . "[Error to connect to cache : " . $db->ErrorMsg() . "]\n");
    }

}

if ($verbose_level >= 1) {
    echo "#### END RECURRING SERVICES \n";
}

write_log($logfile_cront_batch, basename(__FILE__) . ' line:' . __LINE__ . "[#### BATCH PROCESS END ####]");
