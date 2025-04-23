<?php

use A2billing\Admin;
use A2billing\Forms\FormHandler;
use A2billing\Table;
use Amenadiel\JpGraph\Graph\Graph;
use Amenadiel\JpGraph\Graph\PieGraph;
use Amenadiel\JpGraph\Plot\PiePlot3D;

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

getpost_ifset(["starttime", "relative_months"]);
/**
 * @var string|null $starttime
 * @var numeric-string|null $relative_months
 */

$HD_Form = new FormHandler(
    "cc_call",
    "Call Load Report",
    "cc_call.id",
    ["cc_trunk" => ["cc_call.id_trunk", "cc_trunk.id_trunk"]]
);
$HD_Form->FG_LIST_VIEW_PAGE_SIZE = 5000;
$HD_Form->list_query_columns = [
    "SUM(sessiontime) AS time",
    "SUM(sessionbill - buycost) AS profit",
    "SUM(sessionbill) AS revenue",
    "SUM(buycost) AS cost",
    "SUBSTRING(starttime, 1, 4) AS year",
    "SUBSTRING(starttime, 6, 2) AS month"
];

$DBHandle = DbConnect();

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

$HD_Form->prepare_list_subselection("list");

require_once __DIR__ . "/../templates/main.php";

$HD_Form->create_search_form();
$HD_Form->create_toppage("list");

$conditions = $HD_Form->list_query_conditions;
$end = new DateTimeImmutable($starttime ?? "this month");
$endtime = $end->format("Y-m-d");
$relative_months ??= "2";
$starttime = $end->modify("-$relative_months months")->format("Y-m-d");
unset($conditions["starttime"]);
$conditions[] = [
    "SUB",
    ["starttime" => [[">=", $starttime], ["<", $endtime]]]
];

$call_list = (new Table($HD_Form->FG_QUERY_TABLE_NAME, $HD_Form->list_query_columns, $HD_Form->query_table_joins))
    ->getRows($HD_Form->DBHandle, $conditions, ["month"], "DESC", ["month"]);

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
    $time_legend[] = sprintf("%s: %d min", $date, $call["time"]);
    $profit_data[] = $call["profit"];
    $profit_legend[] = sprintf("%s: %s", $date, get_money($call["profit"]));
    $revenue_data[] = $call["revenue"];
    $profit_legend[] = sprintf("%s: %s", $date, get_money($call["revenue"]));
    $cost_data[] = $call["cost"];
    $cost_legend[] = sprintf("%s: %s", $date, get_money($call["cost"]));
}

$time_graph = createPieChart($time_data, _("Traffic"), $time_legend);
$profit_graph = createPieChart($profit_data, _("Profit"), $profit_legend);
$revenue_graph = createPieChart($revenue_data, _("Revenue"), $revenue_legend);
$cost_graph = createPieChart($cost_data, _("Cost"), $cost_legend);
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

function graphToDataUri(Graph $graph): string {
    try {
        $resource = $graph->Stroke("__handle");
    } catch (Exception $e) {
        // todo: error message?
        return "";
    }
    if (is_resource($resource) || get_class($resource) === "GdImage") {
        ob_start();
        imagepng($resource);
        $img = ob_get_clean();

        return "data:image/png;base64," . base64_encode($img);
    }

    return "";
}
