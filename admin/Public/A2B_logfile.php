<?php

use A2billing\A2Billing;
use A2billing\Admin;

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

$menu_section = 16;
require_once __DIR__ . "/../../common/lib/admin.defines.php";

Admin::checkPageAccess(Admin::ACX_MAINTENANCE);

getpost_ifset(["nb", "view_log", "filter"]);
/**
 * @var numeric-string|null $nb
 * @var numeric-string|null $view_log
 * @var string|null $filter
 * @var A2Billing $A2B
 */

require_once __DIR__ . "/../templates/main.php";

// #### HELP SECTION
echo create_help(
    _("Browse your server log files.")
        . '<br/>'
        . _("This tool can be used to extract and present information from various logfiles."),
    'WatchLogFiles'
);

function array2drop_down(string $name, array $arr_value, string $currentvalue = ""): string
{
    $name = htmlspecialchars($name);
    $html = "<select name=\"$name\" id=\"$name\" class=\"form-select\">";
    foreach ($arr_value as $ind => $value) {
        $sel = $ind == $currentvalue ? "selected=\"selected\"" : "";
        $html .= "<option value=\"$ind\" $sel\">$value</option>";
    }
    $html .= "</select>";

    return $html;
}

$dir = new DirectoryIterator("/var/log/asterisk/");
foreach ($dir as $entry) {
    if ($entry->isFile() && $entry->isReadable()) {
        $arr_log[] = $entry->getRealPath();
    }
}
foreach ($A2B->config["log-files"] as $log_file) {
    if (file_exists($log_file) && is_readable($log_file)) {
        $arr_log[] = $log_file;
    }
}
$arr_log = array_unique($arr_log ?? []);
sort($arr_log);

$arr_nb = [25, 50, 100, 250, 500, 1000, 2500];
$arr_nb = array_combine(
    $arr_nb,
    array_map(fn ($v) => sprintf(_("%d lines"), $v), $arr_nb)
);

$nb ??= 50;
$view_log ??= "";
?>

<form method="get" class="row pb-3">
    <div class="col-auto">
        <label class="visually-hidden" for="view_log"><?= _("Select a log file to view") ?></label>
        <?= array2drop_down("view_log", $arr_log, $view_log ?? "") ?>
    </div>
    <div class="col-auto">
        <label class="visually-hidden" for="nb"><?= _("Select number of lines to display") ?></label>
        <?= array2drop_down("nb", $arr_nb, $nb) ?>
    </div>
    <div class="col-auto">
        <label class="visually-hidden" for="filter"><?= _("Filter lines by text") ?></label>
        <input type="text" class="form-control" name="filter" id="filter" value="<?= $filter ?? "" ?>" placeholder="<?= _("Filter") ?>"/>
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-sm btn-primary"><?= _("View") ?></button>
    </div>
</form>

<?php

if (isset($view_log)) {
    $f = $arr_log[$view_log];
    $arr = file($f, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $arr = array_reverse($arr);
    array_splice($arr, $nb);
    if (!empty($filter)) {
        $arr = array_filter(
            $arr,
            fn ($v) => str_contains(strtolower($v), strtolower($filter))
        );
    }
    array_walk($arr, fn (&$v) => $v = htmlspecialchars($v));
    printf(
        "<div class='row pb-3'><div class='col'><p>%s (%d Kb, last modified %s)</p><pre class='py-3'>%s</pre></div></div>",
        $f,
        filesize($f) / 1024,
        get_readable_date(filemtime($f)),
        implode("\n", $arr)
    );
}

require_once __DIR__ . "/../templates/footer.php";
