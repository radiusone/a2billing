<?php
/**
 * @var \A2billing\Forms\FormHandler $HD_Form
 */
$nb_month ??= 6;
$checkdate = (new DateTime("first day of $nb_month months ago"))->format("Y-m-d");
$QUERY_INVOICE_ENOUGH_PAID = <<< SQL
    SELECT EXTRACT(MONTH FROM sub.invoice_date) AS mo, SUM(sub.has_paid) AS ct
    FROM (
        SELECT cc_invoice.date AS invoice_date, 
            IF (COALESCE(SUM(cc_invoice_item.price * (1 + (cc_invoice_item.vat / 100))), 0) <= COALESCE(SUM(cc_logpayment.payment), 0),
                1,
                0
            ) AS has_paid
        FROM cc_invoice 
            LEFT JOIN cc_invoice_item ON cc_invoice_item.id_invoice = cc_invoice.id 
            LEFT JOIN cc_invoice_payment ON cc_invoice_payment.id_invoice = cc_invoice.id
            LEFT JOIN cc_logpayment ON cc_invoice_payment.id_payment = cc_logpayment.id
            LEFT JOIN cc_card ON cc_invoice.id_card = cc_card.id
            LEFT JOIN cc_card_group ON cc_card.id_group = cc_card_group.id
        WHERE cc_invoice.date >= ?
            AND cc_invoice.date <= CURRENT_TIMESTAMP
            AND CASE ? WHEN 0 THEN 1=1 ELSE cc_card_group.id_agent = ? END
        GROUP BY cc_invoice.id
    ) AS sub
    GROUP BY EXTRACT(MONTH FROM sub.invoice_date)
    ORDER BY sub.invoice_date DESC
    SQL;
$result_invoice_enough_paid = $HD_Form->DBHandle->GetArray(
    $QUERY_INVOICE_ENOUGH_PAID,
    [$checkdate, is_agent() ? 1 : 0, $_SESSION["agent_id"] ?? 0]
) ?: [];

$QUERY_INVOICE_COUNT = <<< SQL
    SELECT EXTRACT(MONTH FROM cc_invoice.date) AS mo,
        COUNT(*) AS total_ct,
        SUM(CASE paid_status WHEN 0 THEN 1 ELSE 0 END) AS unpaid_ct,
        SUM(CASE paid_status WHEN 1 THEN 1 ELSE 0 END) AS paid_ct
    FROM cc_invoice
        LEFT JOIN cc_card ON cc_invoice.id_card = cc_card.id
        LEFT JOIN cc_card_group ON cc_card.id_group = cc_card_group.id
    WHERE cc_invoice.date >= ?
        AND cc_invoice.date <= CURRENT_TIMESTAMP
        AND CASE ? WHEN 0 THEN 1=1 ELSE cc_card_group.id_agent = ? END
    GROUP BY EXTRACT(MONTH FROM cc_invoice.date)
    ORDER BY cc_invoice.date DESC
    SQL;
$result_invoice_count = $HD_Form->DBHandle->GetArray(
    $QUERY_INVOICE_COUNT,
    [$checkdate, is_agent() ? 1 : 0, $_SESSION["agent_id"] ?? 0]
) ?: [];

$table_data = [];
for ($i = 0; $i <= $nb_month; $i++) {
    $dt = new DateTime("$i months ago");
    $mo = (int)$dt->format("m");
    $ct_row = array_values(
        array_filter($result_invoice_count, fn ($v) => (int)$v["mo"] === $mo)
    );
    $table_data[] = [
        $dt->format("F"),
        $ct_row[0]["total_ct"] ?? 0,
        array_values(
            array_filter($result_invoice_enough_paid, fn ($v) => (int)$v["mo"] === $mo)
        )[0]["ct"] ?? 0,
        $ct_row[0]["paid_ct"] ?? 0,
        $ct_row[0]["unpaid_ct"] ?? 0,
    ];
}
?>
<div class="d-flex justify-content-end">
    <table class="table table-sm caption-top w-50" style="table-layout:fixed">
        <caption><?= sprintf(_("Invoice Status last %d months"), $nb_month) ?></caption>
        <thead>
        <tr>
            <td></td>
            <th scope="col"><?= _("Invoices") ?></th>
            <th scope="col"><?= abbr(_("Enough"), _("Invoices with enough payment")) ?></th>
            <th scope="col"><?= _("Paid") ?></th>
            <th scope="col"><?= _("Unpaid") ?></th>
        </tr>
        </thead>
        <tbody class="table-group-divider">
        <?php foreach ($table_data as $row): ?>
            <tr>
                <th scope="row"><?= $row[0] ?></th>
                <td><?= $row[1] ?></td>
                <td class="table-success"><?= $row[2] ?></td>
                <td class="table-secondary"><?= $row[3] ?></td>
                <td class="table-danger"><?= $row[4] ?></td>
            </tr>
        <?php endforeach ?>
        </tbody>
    </table>
</div>
