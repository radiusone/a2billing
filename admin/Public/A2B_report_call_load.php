<?php

use A2billing\Admin;
use A2billing\Forms\FormHandler;
use A2billing\Table;
use Amenadiel\JpGraph\Graph\Graph;
use Amenadiel\JpGraph\Plot\BarPlot;
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
    ["cc_trunk" => ["cc_call.id_trunk", "cc_trunk.id_trunk"]]
);
$HD_Form->FG_LIST_VIEW_PAGE_SIZE = 5000;
$HD_Form->list_query_columns = ["starttime", "sessiontime"];

$DBHandle = DbConnect();
$instance_table_graph = new Table("cc_call", ["starttime", "sessiontime"]);

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

require_once __DIR__ . "/../templates/main.php";

$HD_Form->create_search_form();
$HD_Form->create_toppage($form_action);

$basic_chart = true;
require_once __DIR__ . "/../../common/page_modules/call_graph.php";

// create full day bargraph
$cols = ["SUBSTRING(starttime, 0, 10) AS date", "SUBSTRING(starttime, 12, 2) AS hour", "COUNT(id) AS call_count", "SUM(sessiontime) AS call_time"];
$call_list = (new Table($HD_Form->FG_QUERY_TABLE_NAME, $cols, $HD_Form->query_table_joins))
    ->getRows($HD_Form->DBHandle, $HD_Form->list_query_conditions, [], "ASC", ["HOUR(starttime)"]);
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
        array_sum(array_column($call_list, "call_count"))
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
if (!isset($hour_detail)) {
    require_once __DIR__ . "/../templates/footer.php";
    exit;
}

// create hour detail bargraph
$cols = [
    "SUBSTRING(starttime, 0, 10) AS date",
    "SUBSTRING(starttime, 12, 2) AS hour_start",
    "SUBSTRING(starttime, 15, 2) AS minute_start",
    "REPLACE(SUBSTRING(starttime, 15, 5), ':', '') AS ms_start",
    "SUBSTRING(starttime + INTERVAL sessiontime SECOND, 12, 2) AS hour_end",
    "SUBSTRING(starttime + INTERVAL sessiontime SECOND, 15, 2) AS minute_end",
    "REPLACE(SUBSTRING(starttime + INTERVAL sessiontime SECOND, 15, 5), ':', '') AS ms_end",
    "sessiontime"
];
// replace searched date with the specific time
$conditions = $HD_Form->list_query_conditions;
unset($conditions["starttime"]);
$conditions[] = [
    "SUB",
    ["starttime" => [[">=", "$starttime $hour_detail:00:00"], ["<=", "$starttime $hour_detail:59:59"]]]
];
$call_list = (new Table($HD_Form->FG_QUERY_TABLE_NAME, $cols, $HD_Form->query_table_joins))
    ->getRows($HD_Form->DBHandle, $conditions, ["starttime"]);

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
    $graph = createGraphBody($title);
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
require_once __DIR__ . "/../templates/footer.php";

function createBarGraph(array $data, string $title): Graph
{
    $graph = createGraphBody($title);

    $graph->yaxis->SetTickPositions(range(0, ceil(max($data) * 1.1)));
    $graph->xaxis->SetTickLabels(array_keys($data));

    $bplot = new BarPlot(array_values($data));
    $bplot->SetColor("yellow@0.3");
    $bplot->SetWeight(2);
    $bplot->SetFillColor('orange');
    $bplot->SetShadow();
    $bplot->value->SetFormat("%d");
    $bplot->value->SetAlign("center");
    $bplot->value->Show();
    $graph->Add($bplot);

    return $graph;
}

function createGraphBody($title): Graph {
    $graph = new Graph(800, 600);
    $graph->SetMargin(60, 60, 45, 90); //droit,gauche,haut,bas
    $graph->SetMarginColor('white');
    $graph->SetScale("textlin");
    $graph->SetFrame(false);
    $graph->SetBackgroundGradient('#FFFFFF', '#CDDEFF:0.8', GRAD_HOR, BGRAD_PLOT);
    $graph->tabtitle->Set($title);
    $graph->tabtitle->SetWidth(TABTITLE_WIDTHFULL);

    $graph->xgrid->Show();
    $graph->xgrid->SetColor('gray@0.5');
    $graph->ygrid->SetColor('gray@0.5');
    $graph->ygrid->SetFill(true, '#EFEFEF@0.5', '#CDDEFF@0.5');

    $graph->yaxis->scale->SetGrace(3);
    $graph->xaxis->SetLabelAngle(90);

    return $graph;
}

function graphToDataUri(Graph $graph): string {
    $resource = $graph->Stroke("__handle");
    if (is_resource($resource) || get_class($resource) === "GdImage") {
        ob_start();
        imagepng($resource);
        $img = ob_get_clean();

        return "data:image/png;base64," . base64_encode($img);
    }

    return "";
}
