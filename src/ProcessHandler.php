<?php

namespace A2billing;

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

readonly class ProcessHandler
{
    public function __construct(protected string|null $pidfile = null)
    {
    }

    /**
     * Checks if the current process is running, optionally activating it if not
     *
     * @param bool $activate
     * @return bool
     */
    public function isActive(bool $activate = true): bool
    {

        $ret = ($pid = $this->getPID()) && posix_kill($pid, 0);

        if ($ret === false && $activate) {
            $this->activate();
        }

        return $ret;
    }

    /**
     * Writes the PID for the current process to a pidfile
     *
     * @return void
     */
    public function activate(): void
    {
        if ($this->getPID() !== ($pid = getmypid())) {
            if (!file_put_contents($this->pidfile,$pid)) {
                die("Can not create pid file!\n");
            }
        }
    }

    /**
     * Get the PID for the current process
     *
     * @return int|null the PID or null on failure
     */
    public function getPID(): int|null
    {
        return intval(file_get_contents($this->pidfile)) ?: null;
    }
}
