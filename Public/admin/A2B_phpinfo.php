<?php

use A2billing\Admin;

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

$menu_section = 16;
require_once __DIR__ . "/../../common/lib/admin.defines.php";

Admin::checkPageAccess(Admin::ACX_MAINTENANCE);
require_once __DIR__ . "/templates/main.php";

ob_start();
phpinfo(INFO_GENERAL | INFO_MODULES | INFO_ENVIRONMENT);
$html = ob_get_clean();

$dom = new DOMDocument();
$dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
$css = $dom->getElementsByTagName("style")->item(0)->textContent;
$body = $dom->saveHTML($dom->getElementsByTagName("body")->item(0));
$body = str_replace(["<body>", "</body>"], "", $body);

// CSS rules must be constrained so they will not mess with the whole page
$css = implode(
    "\n",
    array_map(
        fn ($v) => "div.phpinfo $v",
        explode("\n", trim($css))
    )
);
$css = str_replace("div.phpinfo body", "div.phpinfo", $css);

echo "<style>$css</style>";
echo "<div class=\"phpinfo\">$body</div>";

require_once(__DIR__ . "/templates/footer.php");
