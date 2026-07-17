<?php

use A2billing\Connection;
use A2billing\Forms\FormHandler;
use Amenadiel\JpGraph\Graph\Graph;
use Amenadiel\JpGraph\Graph\PieGraph;
use Amenadiel\JpGraph\Plot\PiePlot3D;

getpost_ifset(["starttime", "relative_months"]);
/**
 * @var string|null $starttime
 * @var numeric-string|null $relative_months
 */

$builder = Connection::getConnection("cc_call")
    ->leftJoin("cc_trunk", "cc_call.id_trunk", "cc_trunk.id_trunk")
    ->selectRaw("SUM(sessiontime) AS time")
    ->selectRaw("SUM(sessionbill - buycost) AS profit")
    ->selectRaw("SUM(sessionbill) AS revenue")
    ->selectRaw("SUM(buycost) AS cost")
    ->selectRaw("SUBSTRING(starttime, 1, 4) AS year")
    ->selectRaw("SUBSTRING(starttime, 6, 2) AS month");

$HD_Form = new FormHandler(
    "cc_call",
    "Call Load Report",
    builder: $builder
);

$months = array_combine(
    range(1, 12),
    array_map(
        fn ($v) => sprintf(ngettext("%d previous month", "%d previous months", $v), $v),
        range(1, 12)
    )
);
$HD_Form->search_form_enabled = true;
$HD_Form->AddSearchSingleDateInput(_("Start Date"), "starttime");
$HD_Form->AddSearchSelectInput(_("Compare months"), "relative_months", $months, 2, false);
$HD_Form->AddSearchPopupInput(_("Enter the customer ID"), "card_id", "A2B_entity_card.php");
$HD_Form->AddSearchPopupInput(_("Call plan"), "id_tariffgroup", "A2B_entity_tariffgroup.php", 2);
$HD_Form->AddSearchPopupInput(_("Provider"), "id_provider", "A2B_entity_provider.php", 2);
$HD_Form->AddSearchPopupInput(_("Trunk"), "cc_call.id_trunk", "A2B_entity_trunk.php", 2);
$HD_Form->AddSearchPopupInput(_("Rate"), "id_ratecard", "A2B_entity_def_ratecard.php", 2);
$HD_Form->AddSearchTextInput(_("Called number"), "calledstation");

$HD_Form->search_delete_enabled = false;

// rely on form code to add conditions based on search inputs
$HD_Form->prepare_list_subselection("list");

// get the builder which now has conditions set
$builder = $HD_Form->query_builder;

// remove the existing starttime condition
$key = array_find_key($builder->wheres, fn ($v, $k) => $v["column"] === "starttime");
unset($builder->wheres[$key]);
if ($key !== null) {
    array_splice($builder->bindings["where"], $key, 1);
}
// replace with a date range
$end = (new DateTimeImmutable($starttime ?? "this month"))->setTime(23, 59, 59);
$relative_months ??= "2";
$start = $end->modify("-$relative_months months")->setTime(0, 0);
$builder->whereBetween("starttime", [$start, $end]);

$call_list = $builder
    ->orderBy("year", "desc")
    ->orderBy("month", "desc")
    ->groupBy("month")
    ->get();
dump($call_list);
$time_data = [];
$time_legend = [];
$profit_data = [];
$profit_legend = [];
$revenue_data = [];
$revenue_legend = [];
$cost_data = [];
$cost_legend = [];
foreach ($call_list as $call) {
    $date = DateTime::createFromFormat("Ym", $call["year"] . $call["month"])->format("M Y");
    $time_data[] = $call["time"];
    $time_legend[] = sprintf("%s: %s min", $date, get_minute($call["time"]));
    $profit_data[] = $call["profit"];
    $profit_legend[] = sprintf("%s: %s", $date, get_money($call["profit"]));
    $revenue_data[] = $call["revenue"];
    $revenue_legend[] = sprintf("%s: %s", $date, get_money($call["revenue"]));
    $cost_data[] = $call["cost"];
    $cost_legend[] = sprintf("%s: %s", $date, get_money($call["cost"]));
}

$time_graph = createPieChart($time_data, _("Traffic"), $time_legend);
$profit_graph = createPieChart($profit_data, _("Profit"), $profit_legend);
$revenue_graph = createPieChart($revenue_data, _("Revenue"), $revenue_legend);
$cost_graph = createPieChart($cost_data, _("Cost"), $cost_legend);


function createPieChart(array $data, string $title, array $legend): Graph {
    $graph = new PieGraph(800, 300);
    $graph->SetShadow();

    $graph->title->Set($title);
    $graph->title->SetFont(FF_FONT1, FS_BOLD);

    $p1 = new PiePlot3D($data);
    $p1->ExplodeSlice(2);
    $p1->SetCenter(0.4, 0.4);
    $p1->SetLegends($legend);

    $graph->legend->SetColor('navy');
    $graph->legend->SetFillColor('gray@0.8');
    $graph->legend->SetLineWeight(1);
    $graph->legend->SetShadow('gray@0.4', 3);

    $graph->Add($p1);

    return $graph;
}
