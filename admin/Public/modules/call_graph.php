<?php

use A2billing\Forms\FormHandler;
use A2billing\Table;

/**
 * @var FormHandler $HD_Form
 */

$DBHandle = DbConnect();

$table = new Table(
    "cc_call",
    [
        "DATE(cc_call.starttime) AS day",
        "SUM(cc_call.sessiontime) AS calltime",
        "COUNT(*) AS nbcall",
        "SUM(cc_call.buycost + 0) AS buy",
        "SUM(cc_call.sessionbill + 0) AS sell",
        "SUM(CASE WHEN sessionbill != 0 THEN ((sessionbill - buycost) / sessionbill) * 100 ELSE 0 END) AS margin",
        "SUM(CASE WHEN buycost != 0 THEN ((sessionbill - buycost) / buycost) * 100 ELSE 0 END) AS markup",
        "SUM(CASE WHEN cc_call.sessiontime > 0 THEN 1 ELSE 0 END) AS success_calls",
    ]
);
$list_total_day = $table->getRows(
    $DBHandle,
    $HD_Form->list_query_conditions,
    ["day"],
    "ASC",
    ["day"]
);

if (!count($list_total_day)) {
    exit;
}
$mmax = max(array_column($list_total_day, "calltime"));
$totalcall = array_sum(array_column($list_total_day, "nbcall"));
$totalminutes = array_sum(array_column($list_total_day, "calltime"));
$totalsell = array_sum(array_column($list_total_day, "sell"));
$totalbuycost = array_sum(array_column($list_total_day, "buy"));
$totalsuccess = array_sum(array_column($list_total_day, "success_calls"));
$widthbar = 0;

$total_tmc = ($resulttype ?? "min") === "min"
    ? sprintf("%02d:%02d", ($totalminutes / $totalcall) / 60, ($totalminutes / $totalcall) % 60)
    : intval($totalminutes / $totalcall);

$totalminutes = sprintf("%02d:%02d", $totalminutes / 60, $totalminutes % 60);
?>

<table class="table table-striped caption-top">
    <caption><?= _("Traffic Summary") ?></caption>
    <thead>
    <tr>
        <th><?= _( "Date" ) ?></th>
        <th><abbr title="<?= _( "Call duration" ) ?>"><?= _( "Time" ) ?></abbr></th>
        <th style="width: 10vw"></th>
        <th><?= _( "Calls" ) ?></th>
        <th><abbr title="<?= _( "Average call length" ) ?>"><?= _( "Avg" ) ?></abbr></th>
        <th><abbr title="<?= _( "Answer sieze ratio" ) ?>"><?= _( "ASR" ) ?></abbr></th>
        <th><?= _( "Sell" ) ?></th>
        <th><?= _( "Buy" ) ?></th>
        <th><?= _( "Profit" ) ?></th>
        <th><?= _( "Margin" ) ?></th>
        <th><?= _( "Markup" ) ?></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($list_total_day as $data): ?>
        <tr>
            <td><?= $data["day"] ?></td>
            <td><?= get_minute($data["calltime"]) ?></td>
            <td aria-hidden="true">
                <div style="width: <?= $mmax ? ($data["calltime"] / $mmax) * 100 : 0 ?>%; background: darkred">&nbsp;</div>
            </td>
            <td><?= $data["nbcall"] ?></td>
            <td><?= get_minute(intval($data ["calltime"] / $data ["nbcall"])) ?></td>
            <td><?= get_percent($data["success_calls"] * 100 / ($data["nbcall"]) ) ?></td>
            <td><?= get_money_precise($data["sell"]) ?></td>
            <td><?= get_money_precise($data["buy"] ) ?></td>
            <td><?= get_money_precise($data["sell"] - $data["buy"]) ?></td>
            <td><?= get_percent($data["margin"]) ?></td>
            <td><?= get_percent($data["markup"]) ?></td>
        </tr>
    <?php endforeach ?>
    </tbody>
    <tfoot>
    <tr>
        <th scope="row"><?= _( "TOTAL" ) ?></th>
        <td colspan="2"><?= $totalminutes ?></td>
        <td><?= $totalcall ?></td>
        <td><?= $total_tmc ?></td>
        <td><?= get_percent($totalsuccess * 100 / $totalcall) ?></td>
        <td><?= get_money($totalsell) ?></td>
        <td><?= get_money($totalbuycost) ?></td>
        <td><?= get_money($totalsell - $totalbuycost) ?></td>
        <td><?= $totalsell ? get_percent((($totalsell - $totalbuycost) / $totalsell) * 100) : _("n/a") ?></td>
        <td><?= $totalbuycost ? get_percent((($totalsell - $totalbuycost) / $totalbuycost) * 100) : _("n/a")?></td>
    </tr>
    </tfoot>
</table>
