<?php

namespace A2billing;

use ADOConnection;
use Illuminate\Database\Capsule\Manager;
use PDO;

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of A2Billing (http://www.a2billing.net/)
 *
 * A2Billing, Commercial Open Source Telecom Billing platform,
 * powered by Star2billing S.L. <http://www.star2billing.com/>
 *
 * @copyright   Copyright © 2004-2015 - Star2billing S.L.
 * @copyright   Copyright © 2022-2025 RadiusOne Inc.
 * @author      Belaid Rachid <rachid.belaid@gmail.com>
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

class Connection
{
    private static ADOConnection $DBHandler;
    private static Manager $manager;

    private static function initDB(): void
    {
        if (DB_TYPE == "postgres") {
            $datasource = 'pgsql://' . USER . ':' . PASS . '@' . HOST . '/' . DBNAME;
        } else {
            $datasource = 'mysqli://' . USER . ':' . PASS . '@' . HOST . '/' . DBNAME;
        }

        $DBHandle = NewADOConnection($datasource);
        if (!$DBHandle) {
            die("Connection failed");
        }

        if (DB_TYPE === "mysql") {
            $DBHandle->Execute('SET AUTOCOMMIT=1');
            $DBHandle->Execute("SET NAMES 'UTF8'");
        }

        self::$DBHandler = $DBHandle;
    }

    public static function GetDBHandler(): ADOConnection
    {
        if (empty(self::$DBHandler)) {
            self::initDB();
        }

        return self::$DBHandler;
    }

    public static function getConnection(): \Illuminate\Database\Connection
    {
        if (!isset(self::$manager)) {
            $config = A2Billing::parseConfigurationFile();
            $conn = new Manager();
            $conn->addConnection([
                "driver" => "mysql",
                "host" => $config["database"]["hostname"] ?? "localhost",
                "port" => $config["database"]["port"] ?? 3306,
                "username" => $config["database"]["user"] ?? "a2billing",
                "password" => $config["database"]["password"] ?? "a2billing",
                "database" => $config["database"]["dbname"] ?? "a2billing",
                "charset" => "utf8mb4",
                "collation" => "utf8mb4_unicode_ci"
            ]);
            // match old defaults for now
            $conn->setFetchMode(PDO::FETCH_BOTH);
            $conn->setAsGlobal();
            self::$manager = $conn;
        }

        return self::$manager->getConnection();
    }
}
