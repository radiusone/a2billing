<?php

use A2billing\A2Billing;
use A2billing\Admin;
use A2billing\Customer;
use A2billing\Forms\FormHandler;
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

$menu_section = 5;
require_once __DIR__ . "/../../common/lib/admin.defines.php";
/**
 * @var A2Billing $A2B
 */
require_once __DIR__ . "/../../common/form_data/report_calls.inc";
/**
 * @var FormHandler $HD_Form
 */

Admin::checkPageAccess(Admin::ACX_CALL_REPORT);

getpost_ifset (["download", "file"]);
/**
 * @var string $download
 * @var string $file
 */

if (($download ?? "") === "file" && !empty($file)) {

    $value_de = base64_decode($file);
    if (str_contains($file, '/') || $value_de === false || str_contains($value_de, '..')) {
        exit;
    }

    $dl_full = ($A2B->config['webui']['monitor_path'] ?? "") . "/" . $value_de;

    if (!is_readable($dl_full)) {
        echo _("ERROR: Cannot download file $dl_full, it does not exist.");
        exit ();
    }

    header("Content-Type: application/octet-stream");
    header("Content-Disposition: attachment; filename=$value_de");
    header("Content-Length: " . filesize($dl_full));
    header("Accept-Ranges: bytes");
    header("Pragma: no-cache");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-transfer-encoding: binary");

    readfile($dl_full);
    exit ();
}

$HD_Form->init();

$form_action ??= "list";

$list = $HD_Form->perform_action($form_action);

require_once __DIR__ . "/templates/main.php";

$HD_Form->create_search_form();
$HD_Form->create_toppage($form_action);
$HD_Form->create_form("list", $list);

require_once __DIR__ . "/../../common/page_modules/call_graph.php";
require_once __DIR__ . "/templates/footer.php";
