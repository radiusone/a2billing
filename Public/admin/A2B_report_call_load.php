<?php

use A2billing\Admin;
use A2billing\Connection;
use A2billing\Forms\FormHandler;
use Amenadiel\JpGraph\Plot\LinePlot;
use Amenadiel\JpGraph\Util\RGB;

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

Admin::checkPageAccess(Admin::ACX_CALL_REPORT);

getpost_ifset(["hour_detail", "hour_detail_type", "starttime"]);
/**
 * @var numeric-string|null $hour_detail
 * @var string|null $hour_detail_type
 */

$HD_Form = new FormHandler(
    "cc_call",
    "Call Load Report",
    "cc_call.id",
    Connection::getConnection("cc_call")
        ->leftJoin("cc_trunk", "cc_call.id_trunk", "cc_trunk.id_trunk")
);
$HD_Form->FG_LIST_VIEW_PAGE_SIZE = 5000;
$HD_Form->list_query_columns = ["starttime", "sessiontime"];

$hours = range(0, 23);
$hours = array_combine($hours, array_map(fn ($v) => sprintf("%02d:00 to %02d:00", $v, $v + 1), $hours));
$types = ["watch-call" => _("Watch Calls"), "fluctuation" => _("Fluctuation")];

$HD_Form->search_form_enabled = true;
$HD_Form->AddSearchSingleDateInput(_("Date"), "starttime");
$HD_Form->AddSearchPopupInput(_("Enter the customer ID"), "card_id", "A2B_entity_card.php");
$HD_Form->AddSearchPopupInput(_("Call Plan"), "id_tariffgroup", "A2B_entity_tariffgroup.php", 2);
$HD_Form->AddSearchPopupInput(_("Provider"), "id_provider", "A2B_entity_provider.php", 2);
$HD_Form->AddSearchPopupInput(_("Trunk"), "cc_call.id_trunk", "A2B_entity_trunk.php", 2);
$HD_Form->AddSearchPopupInput(_("Rate"), "id_ratecard", "A2B_entity_def_ratecard.php", 2);
$HD_Form->AddSearchTextInput(_("Called Number"), "calledstation");
$HD_Form->AddSearchSelectInput(_("Hour Details"), "hour_detail", $hours, null, false);
$HD_Form->AddSearchSelectInput(_("Hour Detail Type"), "hour_detail_type", $types, "watch-call", false);

$HD_Form->search_delete_enabled = false;

$HD_Form->prepare_list_subselection("list");
$starttime ??= (new DateTime())->format("Y-m-d");
if (empty($HD_Form->list_query_conditions)) {
    $HD_Form->list_query_conditions["cc_call.starttime"] = [">=", $starttime];
}

$form_action ??= "list";

$list = $HD_Form->perform_action($form_action);

require_once __DIR__ . "/templates/main.php";

$HD_Form->create_search_form();
$HD_Form->create_toppage($form_action);

$basic_chart = true;
require_once __DIR__ . "/../../common/page_modules/call_graph.php";

// create full day bargraph
$call_list = $HD_Form->query_builder
    ->clone()
    ->selectRaw("SUBSTRING(starttime, 0, 10) AS date")
    ->selectRaw("SUBSTRING(starttime, 12, 2) AS hour")
    ->selectRaw("COUNT(id) AS call_count")
    ->selectRaw("SUM(sessiontime) AS call_time")
    ->groupByRaw("HOUR(starttime)")
    ->get();
$graph_data = array_combine(
    array_map(fn ($v) => sprintf("%02d", $v), range(0, 23)),
    array_fill(0, 24, 0)
);
foreach ($call_list as $row) {
    $graph_data[$row["hour"]] = $row["call_count"];
}

$graph = createBarGraph(
    $graph_data,
    sprintf(
        "%s - %d calls",
        $starttime,
        $call_list->sum("call_count")
    )
);
?>
<div class="row">
    <div class="mb-3">
        <img
            src="<?= graphToDataUri($graph) ?>"
            alt=""
            data-graphdata="<?= htmlspecialchars(json_encode($graph_data)) ?>"
        />
    </div>
</div>

<?php
if ($hour_detail === "") {
    require_once __DIR__ . "/templates/footer.php";
    exit;
}

// create hour detail bargraph
// replace searched date with the specific time
$call_list = $HD_Form->query_builder
    ->clone()
    ->select("sessiontime")
    ->selectRaw("SUBSTRING(starttime, 0, 10) AS date")
    ->selectRaw("SUBSTRING(starttime, 12, 2) AS hour_start")
    ->selectRaw("SUBSTRING(starttime, 15, 2) AS minute_start")
    ->selectRaw("REPLACE(SUBSTRING(starttime, 15, 5), ':', '') AS ms_start")
    ->selectRaw("SUBSTRING(starttime + INTERVAL sessiontime SECOND, 12, 2) AS hour_end")
    ->selectRaw("SUBSTRING(starttime + INTERVAL sessiontime SECOND, 15, 2) AS minute_end")
    ->selectRaw("REPLACE(SUBSTRING(starttime + INTERVAL sessiontime SECOND, 15, 5), ':', '') AS ms_end")
    ->removeWhere("starttime")
    ->where("starttime", ">=", "$starttime $hour_detail:00:00")
    ->where("starttime", "<=", "$starttime $hour_detail:59:59")
    ->orderBy("starttime")
    ->get();
$empty_minutes = array_combine(
    array_map(fn ($v) => sprintf("%02d", $v), range(0, 59)),
    array_fill(0, 60, null)
);
$per_minute_data = [];
$change_data = [];
foreach ($call_list as $i => $row) {
    if ($row["hour_end"] > $row["hour_start"]) {
        $row["minute_end"] = 59;
    }
    $per_minute_data[$i] = $empty_minutes;
    for ($m = $row["minute_start"]; $m <= $row["minute_end"]; $m++) {
        $per_minute_data[$i][$m] = $i + 1;
    }
    $change_data[$row["ms_start"]] ??= 0;
    $change_data[$row["ms_start"]]++;
    $change_data[$row["ms_end"]] ??= 0;
    $change_data[$row["ms_end"]]--;
}
ksort($change_data);

$load = 0;
$change_load = [];
foreach ($change_data as $ms => $change) {
    $load += $change;
    $key = substr($ms, 0, 2) . ":" . substr($ms, 2, 2);
    $change_load[$key] = $load;
}

$title = sprintf(
    "%s - %s:00 - %d calls - %d max load",
    $starttime,
    $hour_detail,
    count($call_list),
    max($change_load)
);
if ($hour_detail_type === "fluctuation") {
    // this is a simple bar graph showing when each change in load occurred
    // load = how many calls are active at once
    $graph = createBarGraph($change_load, $title);
} else {
    // this is a line graph where each call gets a horizontal line covering its duration
    $graph = createBarGraphBody($title);
    $graph->yscale->SetIntScale();
    $graph->yaxis->SetLabelFormatString("%1d call");
    $graph->yaxis->HideFirstLastLabel();
    $graph->xaxis->SetTickLabels(array_keys($empty_minutes));
    $rgb = array_values((new RGB())->rgb_table);
    $lineweight = 500 / count($call_list);

    foreach ($per_minute_data as $i => $data) {
        $line = new LinePlot($data);
        $line->SetWeight($lineweight);
        $line->SetColor($rgb[($i + 20) % 436]);
        $graph->Add($line);
    }
}
?>
<div class="row">
    <div class="mb-3">
        <img
            src="<?= graphToDataUri($graph) ?>"
            alt=""
            data-graphdata="<?= htmlspecialchars(json_encode($per_minute_data)) ?>"
        />
    </div>
</div>

<?php
echo "xxx";
require_once __DIR__ . "/templates/footer.php";

