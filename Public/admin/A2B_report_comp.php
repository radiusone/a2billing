<?php

use A2billing\Admin;
use A2billing\Connection;
use A2billing\Forms\FormHandler;
use Amenadiel\JpGraph\Plot\LinePlot;

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

getpost_ifset(["starttime", "relative_days", "graph_type"]);
/**
 * @var string|null $starttime
 * @var numeric-string|null $relative_days
 * @var string|null $graph_type
 */

$builder = Connection::getConnection("cc_call", "sessiontime", "starttime")
    ->leftJoin("cc_trunk", "cc_call.id_trunk", "cc_trunk.id_trunk")
    ->selectRaw("sessionbill - buycost AS profit")
    ->selectRaw("sessionbill AS revenue")
    ->selectRaw("buycost AS cost")
    ->selectRaw("SUBSTRING(starttime, 1, 4) AS year")
    ->selectRaw("SUBSTRING(starttime, 6, 2) AS month")
    ->selectRaw("SUBSTRING(starttime, 9, 2) AS day")
    ->selectRaw("SUBSTRING(starttime, 12, 2) AS hour");

$HD_Form = new FormHandler(
    "cc_call",
    "Call Comparison Report",
    builder: $builder
);

$days = array_combine(
    range(1, 7),
    array_map(
        fn ($v) => sprintf(ngettext("%d previous day", "%d previous days", $v), $v),
        range(1, 7)
    )
);
$graphs = [
    "minutes" => _("Minutes by hour"),
    "calls" => _("Calls by hour"),
    "profits" => _("Profits by hour"),
    "revenues" => _("Revenue by hour"),
    "costs" => _("Costs by hour"),
];
$HD_Form->search_form_enabled = true;
$HD_Form->AddSearchSingleDateInput(_("Start Date"), "starttime");
$HD_Form->AddSearchSelectInput(_("Compare days"), "relative_days", $days, 2, false);
$HD_Form->AddSearchPopupInput(_("Enter the customer ID"), "card_id", "A2B_entity_card.php");
$HD_Form->AddSearchPopupInput(_("Call plan"), "id_tariffgroup", "A2B_entity_tariffgroup.php", 2);
$HD_Form->AddSearchPopupInput(_("Provider"), "id_provider", "A2B_entity_provider.php", 2);
$HD_Form->AddSearchPopupInput(_("Trunk"), "cc_call.id_trunk", "A2B_entity_trunk.php", 2);
$HD_Form->AddSearchPopupInput(_("Rate"), "id_ratecard", "A2B_entity_def_ratecard.php", 2);
$HD_Form->AddSearchTextInput(_("Called number"), "calledstation");
$HD_Form->AddSearchTextInput(_("Source number"), "src");
$HD_Form->AddSearchSelectInput(_("Graph type"), "graph_type", $graphs, "calls", false);
$HD_Form->search_session_key = "A2B_report_comp";
$HD_Form->search_delete_enabled = false;

$HD_Form->prepare_list_subselection("list");

// remove the existing starttime condition
$key = array_find_key($HD_Form->query_builder->wheres, fn ($v, $k) => $v["column"] === "starttime");
if ($key !== null) {
    unset($HD_Form->query_builder->wheres[$key]);
    array_splice($HD_Form->query_builder->bindings["where"], $key, 1);
}
// replace with a date range
$end = (new DateTimeImmutable($starttime ?? "now"))->setTime(23, 59, 59);
$relative_days ??= "2";
$start = $end->modify("-$relative_days days")->setTime(0, 0);
$HD_Form->query_builder->whereBetween("starttime", [$start, $end]);

require_once __DIR__ . "/templates/main.php";

$HD_Form->create_search_form();
$HD_Form->create_toppage("list");

require_once __DIR__ . "/../../common/page_modules/call_graph.php";

$call_list = $HD_Form->query_builder->orderBy("starttime", "desc")->get();

$graph_data = [];
$legends = [];
$max = 0;
foreach ($call_list as $call) {
    $day = intval($call["day"]);
    $hour = intval($call["hour"]);
    for ($i = 0; $i <= 23; $i++) {
        $graph_data[$day]["minutes"][$i] ??= 0;
        $graph_data[$day]["calls"][$i] ??= 0;
        $graph_data[$day]["profits"][$i] ??= 0;
        $graph_data[$day]["revenue"][$i] ??= 0;
        $graph_data[$day]["costs"][$i] ??= 0;
    }
    $graph_data[$day]["minutes"][$hour] += $call["sessiontime"];
    $graph_data[$day]["calls"][$hour]++;
    $graph_data[$day]["profits"][$hour] += $call["profit"];
    $graph_data[$day]["revenue"][$hour] += $call["revenue"];
    $graph_data[$day]["costs"][$hour] += $call["cost"];
    $legends[$day] = (new DateTime($call["starttime"]))->format("M j");
}

$graph = createBarGraphBody(_("Number of calls by hour"));
$graph->xaxis->SetTickLabels(array_map(fn ($v) => sprintf("%02d", $v), range(0, 23)));
$graph->legend->SetColor('navy');
$graph->legend->SetFillColor('gray@0.8');
$graph->legend->SetLineWeight(1);
$graph->legend->SetShadow('gray@0.4', 3);
$graph->legend->SetAbsPos(15, 130, 'right', 'bottom');

$graph_type ??= "calls";
$colours = ["green", "blue", "yellow", "red", "purple", "darkgreen", "brown"];
$c = 0;
foreach ($graph_data as $day => $data) {
    $plot = new LinePlot($data[$graph_type]);
    $plot->SetColor($colours[++$c]);
    $plot->SetWeight(2);
    $plot->SetLegend($legends[$day]);
    $graph->Add($plot);
}
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
require_once __DIR__ . "/templates/footer.php";

