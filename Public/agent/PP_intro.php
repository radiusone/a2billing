<?php

use A2billing\Agent;
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

require_once __DIR__ . "/../../common/lib/agent.defines.php";
require_once __DIR__ . "/templates/main.php";

$DBHandle = DbConnect();
$table_message = new Table("cc_message_agent");
$messages = $table_message->getRows($DBHandle, ["id_agent" => $_SESSION["agent_id"]], ["order_display"]);
$message_types = ["alert-info", "alert-success", "alert-warning", "alert-danger"];
$message_logos = ["bi-info-circle-fill text-info", "bi-check-circle-fill text-success", "bi-exclamation-circle-fill text-warning", "bi-x-circle-fill text-danger"];
?>

<div class="row pb-3">
    <div class="col">
    <?php foreach ($messages as $message): ?>
        <div class="alert <?= $message_types[$message["type"]] ?> d-flex align-items-center">
            <?php if ($message["logo"]): ?>
            <div class="bi bi-32 <?= $message_logos[$message["type"]] ?> flex-shrink-0 me-2" aria-hidden="true"></div>
            <?php endif ?>
            <div class="flex-grow-1 mx-2">
                <?= $message["message"] ?>
            </div>
        </div>
    <?php endforeach ?>
    </div>
</div>

<div class="row pb-3 justify-content-center">
    <div class="col-auto text-center">
        <img src="../common/images/logo/a2billing.png" alt=""/>
        <p>A2Billing is licensed under <a href="https://www.fsf.org/licensing/licenses/agpl-3.0.html" target="_blank">AGPL 3</a>.</p>
    </div>
</div>

<?php
require_once __DIR__ . "/templates/footer.php";
