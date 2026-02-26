<?php

use A2billing\Customer;
use A2billing\Forms\FormHandler;

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
 */

require_once __DIR__ . "/../common/lib/customer.defines.php";
require_once __DIR__ . "/../common/form_data/FG_var_def_ratecard.inc";
/**
 * @var FormHandler $HD_Form
 */

getpost_ifset(["letter", "filterprefix0", "filterprefix1"]);
$letter ??= "";

Customer::checkPageAccess(Customer::ACX_RATECARD);

$HD_Form->init();

$form_action ??= "list";
$list = $HD_Form->perform_action($form_action);

require_once __DIR__ . "/templates/main.php";

$HD_Form->create_toppage($form_action);
$HD_Form->create_search_form();
$HD_Form->create_form($form_action, $list);
if (!isset($filterprefix0) && !isset($filterprefix1)):
?>
<div class="list-pagination-container">
    <ul class="pagination pagination-sm justify-content-center">
        <nav aria-label="<?= _("Filter results by letter") ?>">
            <ul class="pagination pagination-sm justify-content-center m-0">
                <li class="page-item <?= $letter === "" ? "active" : "" ?>"><a class="page-link" href="?letter="><?= _("None") ?></a></li>
                <li class="page-item <?= $letter === "1" ? "active" : "" ?>"><a class="page-link" href="?letter=1"><?= _("0-9") ?></a></li>
                <li class="page-item <?= $letter === "a" ? "active" : "" ?>"><a class="page-link" href="?letter=a">A</a></li>
                <li class="page-item <?= $letter === "b" ? "active" : "" ?>"><a class="page-link" href="?letter=b">B</a></li>
                <li class="page-item <?= $letter === "c" ? "active" : "" ?>"><a class="page-link" href="?letter=c">C</a></li>
                <li class="page-item <?= $letter === "d" ? "active" : "" ?>"><a class="page-link" href="?letter=d">D</a></li>
                <li class="page-item <?= $letter === "e" ? "active" : "" ?>"><a class="page-link" href="?letter=e">E</a></li>
                <li class="page-item <?= $letter === "f" ? "active" : "" ?>"><a class="page-link" href="?letter=f">F</a></li>
                <li class="page-item <?= $letter === "g" ? "active" : "" ?>"><a class="page-link" href="?letter=g">G</a></li>
                <li class="page-item <?= $letter === "h" ? "active" : "" ?>"><a class="page-link" href="?letter=h">H</a></li>
                <li class="page-item <?= $letter === "i" ? "active" : "" ?>"><a class="page-link" href="?letter=i">I</a></li>
                <li class="page-item <?= $letter === "j" ? "active" : "" ?>"><a class="page-link" href="?letter=j">J</a></li>
                <li class="page-item <?= $letter === "k" ? "active" : "" ?>"><a class="page-link" href="?letter=k">K</a></li>
                <li class="page-item <?= $letter === "l" ? "active" : "" ?>"><a class="page-link" href="?letter=l">L</a></li>
                <li class="page-item <?= $letter === "m" ? "active" : "" ?>"><a class="page-link" href="?letter=m">M</a></li>
                <li class="page-item <?= $letter === "n" ? "active" : "" ?>"><a class="page-link" href="?letter=n">N</a></li>
                <li class="page-item <?= $letter === "o" ? "active" : "" ?>"><a class="page-link" href="?letter=o">O</a></li>
                <li class="page-item <?= $letter === "p" ? "active" : "" ?>"><a class="page-link" href="?letter=p">P</a></li>
                <li class="page-item <?= $letter === "q" ? "active" : "" ?>"><a class="page-link" href="?letter=q">Q</a></li>
                <li class="page-item <?= $letter === "r" ? "active" : "" ?>"><a class="page-link" href="?letter=r">R</a></li>
                <li class="page-item <?= $letter === "s" ? "active" : "" ?>"><a class="page-link" href="?letter=s">S</a></li>
                <li class="page-item <?= $letter === "t" ? "active" : "" ?>"><a class="page-link" href="?letter=t">T</a></li>
                <li class="page-item <?= $letter === "u" ? "active" : "" ?>"><a class="page-link" href="?letter=u">U</a></li>
                <li class="page-item <?= $letter === "v" ? "active" : "" ?>"><a class="page-link" href="?letter=v">V</a></li>
                <li class="page-item <?= $letter === "w" ? "active" : "" ?>"><a class="page-link" href="?letter=w">W</a></li>
                <li class="page-item <?= $letter === "x" ? "active" : "" ?>"><a class="page-link" href="?letter=x">X</a></li>
                <li class="page-item <?= $letter === "y" ? "active" : "" ?>"><a class="page-link" href="?letter=y">Y</a></li>
                <li class="page-item <?= $letter === "z" ? "active" : "" ?>"><a class="page-link" href="?letter=z">Z</a></li>
            </ul>
        </nav>
    </ul>
</div>
<?php
endif;
require_once __DIR__ . "/templates/footer.php";
