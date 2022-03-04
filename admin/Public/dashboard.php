<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of A2Billing (http://www.a2billing.net/)
 *
 * A2Billing, Commercial Open Source Telecom Billing platform,
 * powered by Star2billing S.L. <http://www.star2billing.com/>
 *
 * @copyright   Copyright (C) 2004-2015 - Star2billing S.L.
 * @author      Belaid Arezqui <areski@gmail.com>
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

if (!has_rights(ACX_DASHBOARD)) {
    Header("HTTP/1.0 401 Unauthorized");
    Header("Location: PP_error.php?c=accessdenied");
    die();
}

//month view
$st = new DateTime('midnight first day of this month -6 months 15 days');
$checkdate_month = $st->format("Y-m-d");
$mingraph_month = $st->format("U");
$maxgraph_month = (new DateTime('midnight first day of next month'))->format("U");

//day view
$checkdate_day = (new DateTime('midnight -10 days'))->format("Y-m-d");
$mingraph_day = (new DateTime('midnight -10 days -12 hours'))->format("U");
$maxgraph_day = (new DateTime('midnight +1 day'))->format("U");

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
    put_display($A2B->config["dashboard"]["customer_info_enabled"], gettext("ACCOUNTS INFO"), ["./modules/customers_numbers.php", "./modules/customers_lastmonth.php"], $boxes);
}
if ( !empty($A2B->config["dashboard"]["refill_info_enabled"]) && $A2B->config["dashboard"]["refill_info_enabled"]!="NONE") {
    put_display($A2B->config["dashboard"]["refill_info_enabled"], gettext("REFILLS INFO"), ["./modules/refills_lastmonth.php"], $boxes);
}
if ( !empty($A2B->config["dashboard"]["payment_info_enabled"]) && $A2B->config["dashboard"]["payment_info_enabled"]!="NONE") {
    put_display($A2B->config["dashboard"]["payment_info_enabled"], gettext("PAYMENTS INFO"), ["./modules/payments_lastmonth.php"], $boxes);
}
if ( !empty($A2B->config["dashboard"]["call_info_enabled"]) && $A2B->config["dashboard"]["call_info_enabled"]!="NONE") {
    put_display($A2B->config["dashboard"]["call_info_enabled"], gettext("CALLS INFO TODAY"), ["./modules/calls_counts.php", "./modules/calls_lastmonth.php"], $boxes);
}
if ( !empty($A2B->config["dashboard"]["system_info_enable"]) && $A2B->config["dashboard"]["system_info_enable"]!="NONE") {
    put_display($A2B->config["dashboard"]["system_info_enable"], gettext("SYSTEM INFO"), ["./modules/system_info.php"], $boxes);
}

$smarty->display('main.tpl');

?>
<div class="row">
<?php foreach ($boxes as $col): ?>
    <div class="col-4">
        <?php foreach ($col as $box): ?>
        <div class="card">
            <div class="card-body">
                <h4 class="card-title"><?= $box["title"] ?></h4>
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
        .width(() => Math.min($(this).parent("div").width(), $(this).parent("div").innerWidth) - 10)
        .height(() => Math.floor($(this).width() / 2))
        .on("plothover", function (event, pos, item) {
            if (item) {
                if (previousPoint !== item.datapoint) {
                    let y;
                    const format = $(this).data("dataFormat");
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
                const graph_data = [];
                for (let i = 0; i < data.data.length; i++) {
                    graph_data[i] = [parseInt(data.data[i][0]), data.data[i][1]];
                }
                graph.data("dataFormat", data.format);
                plot_graph(graph_data, graph_max, graph);
            }
        );
    });

    $('.period_graph').on('change', function () {
        const graph = $($(this).data("graph"));
        graph.data("period", $(this).val());
        graph.data("xformat", $(this).val() === "month" ? "%b" : "%d-%m");
        $(".update_graph", graph.parent()).filter(":checked").click();
    });

    $(".period_graph[value=day]").click().change();
    $(".update_graph[checked=checked]").click();

    function plot_graph(data, max, graph) {
        const d = data;
        const period_val = graph.data("period");
        const max_data = (max + 5 - (max % 5));
        const min_month = <?= $mingraph_month * 1000 ?>;
        const max_month = <?= $maxgraph_month * 1000 ?>;
        const min_day = <?= $mingraph_day * 1000 ?>;
        const max_day = <?= $maxgraph_day * 1000 ?>;
        const min_graph = period_val === "month" ? min_day : min_month;
        const max_graph = period_val === "month" ? max_day : max_month;
        const bar_width = period_val === "month" ? 28 * 24 * 60 * 60 * 1000 : 24 * 60 * 60 * 1000;

        $.plot(
            graph,
            [{
                data: d,
                bars: {show: true, barWidth: bar_width, align: "centered"}
            }],
            {
                xaxis: {mode: "time", timeformat: graph.data("xformat"), ticks: 6, min: min_graph, max: max_graph},
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
