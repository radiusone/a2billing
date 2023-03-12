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

require_once "../../common/lib/admin.defines.php";

Admin::checkPageAccess(Admin::ACX_DASHBOARD);

//month view
$st = (new DateTime('midnight first day of this month'))->modify('-6 months -15 days');
$checkdate_month = $st->format("Y-m-d");
$mingraph_month = $st;
$maxgraph_month = (new DateTime('midnight first day of next month'));

//day view
$checkdate_day = (new DateTime('midnight -10 days'))->format("Y-m-d");
$mingraph_day = (new DateTime('midnight -10 days -12 hours'));
$maxgraph_day = (new DateTime('midnight +1 day'));

$boxes = ["left" => [], "center" => [], "right" => []];

function put_display($position, $title, $links, &$boxes)
{
    if ($position === "LEFT") {
        $boxes["left"][] = compact("title", "links");
    } elseif ($position === "CENTER") {
        $boxes["center"][] = compact("title", "links");
    } elseif ($position === "RIGHT") {
        $boxes["right"][] = compact("title", "links");
    }
}

if ( !empty($A2B->config["dashboard"]["customer_info_enabled"]) && $A2B->config["dashboard"]["customer_info_enabled"]!="NONE") {
    put_display($A2B->config["dashboard"]["customer_info_enabled"], gettext("Accounts"), ["./modules/customers_numbers.php", "./modules/customers_lastmonth.php"], $boxes);
}
if ( !empty($A2B->config["dashboard"]["refill_info_enabled"]) && $A2B->config["dashboard"]["refill_info_enabled"]!="NONE") {
    put_display($A2B->config["dashboard"]["refill_info_enabled"], gettext("Refills"), ["./modules/refills_lastmonth.php"], $boxes);
}
if ( !empty($A2B->config["dashboard"]["payment_info_enabled"]) && $A2B->config["dashboard"]["payment_info_enabled"]!="NONE") {
    put_display($A2B->config["dashboard"]["payment_info_enabled"], gettext("Payments"), ["./modules/payments_lastmonth.php"], $boxes);
}
if ( !empty($A2B->config["dashboard"]["call_info_enabled"]) && $A2B->config["dashboard"]["call_info_enabled"]!="NONE") {
    put_display($A2B->config["dashboard"]["call_info_enabled"], gettext("Calls"), ["./modules/calls_counts.php", "./modules/calls_lastmonth.php"], $boxes);
}
if ( !empty($A2B->config["dashboard"]["system_info_enable"]) && $A2B->config["dashboard"]["system_info_enable"]!="NONE") {
    put_display($A2B->config["dashboard"]["system_info_enable"], gettext("System"), ["./modules/system_info.php"], $boxes);
}

$smarty->display('main.tpl');

?>
<div class="row">
<?php foreach ($boxes as $col): ?>
    <div class="col-4">
        <?php foreach ($col as $box): ?>
        <div class="card mb-3">
            <h5 class="card-header text-center"><?= $box["title"] ?></h5>
            <div class="card-body">
                <?php foreach ($box["links"] as $link) require_once $link ?>
            </div>
        </div>
        <?php endforeach ?>
    </div>
<?php endforeach ?>
</div>

<script>
let previousPoint = null;
const curr = <?= json_encode($A2B->config["global"]["base_currency"]) ?>;

$(function () {
    $(".dashgraph")
        .width(function() {return Math.min($(this).parent("div").width(), $(this).parent("div").innerWidth) - 10;})
        .height(function() {return Math.floor($(this).width() / 2);})
        .on("plothover", function (event, pos, item) {
            if (item) {
                if (previousPoint !== item.datapoint) {
                    let y;
                    const format = $(this).data("tooltipFormat");
                    previousPoint = item.datapoint;
                    $("#tooltip").remove();
                    if (format === "time") {
                        y = item.datapoint[1].toFixed(0);
                        const hour = Math.floor(y / 3600);
                        const min = Math.floor(y / 60) % 60;
                        const sec = y % 60;
                        showTooltip(item.pageX, item.pageY, `${hour}h ${min}m ${sec}s<br/>(${y} sec)`);
                    } else if (format === "money") {
                        y = item.datapoint[1].toFixed(2);
                        showTooltip(item.pageX, item.pageY, y + " " + curr);
                    } else {
                        y = item.datapoint[1].toFixed(0);
                        showTooltip(item.pageX, item.pageY, y);
                    }
                }
            } else {
                $("#tooltip").remove();
                previousPoint = null;
            }
        });

    $('.update_graph').on('click', function() {
        const graph = $($(this).data("graph"));
        $.getJSON(
            $(this).data("uri") + "?t=" + Date.now(),
            {type: this.id, view_type: graph.data("period")},
            function(data) {
                const graph_max = data.max;
                const graph_data = data.data;
                graph.data("tooltipFormat", data.format);
                plot_graph(graph_data, graph_max, graph);
            }
        );
    });

    $('.period_graph').on('change', function () {
        const graph = $($(this).data("graph"));
        graph.data("period", $(this).val());
        $(".update_graph", graph.parent()).filter(":checked").click();
    });

    $(".dashgraph").data("period", "day").data("xformat", "%d-%m");
    $(".update_graph[checked=checked]").click();

    function plot_graph(data, max, graph) {
        const d = data;
        const period_val = graph.data("period");
        const max_data = (max + 5 - (max % 5));

        const min_month = <?= $mingraph_month->format("U") ?> * 1000; // <?= $mingraph_month->format("Y-m-d H:i:s") ?>

        const max_month = <?= $maxgraph_month->format("U") ?> * 1000; // <?= $maxgraph_month->format("Y-m-d H:i:s") ?>

        const min_day = <?= $mingraph_day->format("U") ?> * 1000; // <?= $mingraph_day->format("Y-m-d H:i:s") ?>

        const max_day = <?= $maxgraph_day->format("U") ?> * 1000; // <?= $maxgraph_day->format("Y-m-d H:i:s") ?>

        let min_graph = min_day;
        let max_graph = max_day;
        let bar_width = 22 * 60 * 60 * 1000;
        let time_format = "%d\n%b";
        if (period_val === "month") {
            min_graph = min_month;
            max_graph = max_month;
            bar_width *= 24;
            time_format = "%b";
        }

        $.plot(
            graph,
            [{
                data: d,
                bars: {show: true, barWidth: bar_width, align: "centered"}
            }],
            {
                xaxis: {autoscale: "none", mode: "time", timeformat: time_format, ticks: 6, min: min_graph, max: max_graph, timeBase: "milliseconds"},
                yaxis: {max: max_data, minTickSize: 1, tickDecimals: 0},
                selection: {mode: "y"},
                grid: {hoverable: true, clickable: true}
            }
        );
    }

    function showTooltip(x, y, contents) {
        $('<div id="tooltip">')
            .css({
                position: 'absolute',
                display: 'none',
                top: y + 5,
                left: x + 5,
                border: '1px solid #fdd',
                padding: '2px',
                backgroundColor: '#fee',
                opacity: 0.80
            })
            .html(contents)
            .appendTo("body")
            .fadeIn(200);
    }
});
</script>
<?php

$smarty->display('footer.tpl');
