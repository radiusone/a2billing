<?php

use A2billing\Admin;
use A2billing\Forms\FormHandler;
use Amenadiel\JpGraph\Graph\Graph;

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

$menu_section = 5;
require_once __DIR__ . "/../../common/lib/admin.defines.php";
require_once __DIR__ . "/../../common/form_data/report_monthly.inc";
/**
 * @var FormHandler $HD_Form
 * @var array $time_data
 * @var Graph $time_graph
 * @var array $profit_data
 * @var Graph $profit_graph
 * @var array $revenue_data
 * @var Graph $revenue_graph
 * @var array $cost_data
 * @var Graph $cost_graph
 */

Admin::checkPageAccess(Admin::ACX_CALL_REPORT);

require_once __DIR__ . "/../templates/main.php";

$HD_Form->create_search_form();
$HD_Form->create_toppage("list");

?>
<div class="row">
    <div class="mb-3">
        <img
            src="<?= graphToDataUri($time_graph) ?>"
            alt=""
            data-graphdata="<?= htmlspecialchars(json_encode($time_data)) ?>"
        />
    </div>
</div>
<div class="row">
    <div class="mb-3">
        <img
            src="<?= graphToDataUri($profit_graph) ?>"
            alt=""
            data-graphdata="<?= htmlspecialchars(json_encode($profit_data)) ?>"
        />
    </div>
</div>
<div class="row">
    <div class="mb-3">
        <img
            src="<?= graphToDataUri($revenue_graph) ?>"
            alt=""
            data-graphdata="<?= htmlspecialchars(json_encode($revenue_data)) ?>"
        />
    </div>
</div>
<div class="row">
    <div class="mb-3">
        <img
            src="<?= graphToDataUri($cost_graph) ?>"
            alt=""
            data-graphdata="<?= htmlspecialchars(json_encode($cost_data)) ?>"
        />
    </div>
</div>

<?php
require_once __DIR__ . "/../templates/footer.php";
