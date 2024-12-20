<?php

use A2billing\Admin;
use A2billing\Forms\FormHandler;
use A2billing\Table;

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

$menu_section = 5;
require_once __DIR__ . "/../../common/lib/admin.defines.php";

Admin::checkPageAccess(Admin::ACX_CALL_REPORT);

$HD_Form = new FormHandler(
    "cc_card_package_offer",
    _("Package Usage"),
    "cc_card_package_offer.id",
    [
        "cc_card" => ["cc_card_package_offer.id_cc_card", "cc_card.id"],
        "cc_package_offer" => ["cc_card_package_offer.id_cc_package_offer", "cc_package_offer.id"]
    ]
);
$HD_Form->FG_QUERY_ORDERBY_COLUMNS = ["date_consumption"];
$HD_Form->FG_QUERY_DIRECTION = "DESC";
$HD_Form->FG_QUERY_GROUPBY_COLUMNS = ["id_cc_card", "id_cc_package_offer"];
$HD_Form->FG_LIST_VIEW_PAGE_SIZE = 25;

$HD_Form->AddListValue(_("Card number"), "username");
$HD_Form->AddListValue(_("Package"), "label");
$HD_Form->AddListValue(_("Minutes"), "SUM(used_secondes)", "display_minute");
$HD_Form->AddListValue(_("Calls"), "COUNT(*)");
$HD_Form->FieldViewElement(["username", "label", "SUM(used_secondes)", "COUNT(*)"]);

$HD_Form->FG_EXPORT_CSV = true;
$HD_Form->FG_EXPORT_XML = true;
$HD_Form->export_session_key = "pr_export_package_report";

$HD_Form->search_form_enabled = true;
$HD_Form->search_session_key = 'package_report_selection';
$HD_Form->search_form_title = gettext('Define specific criteria to search for call records');
$HD_Form->search_delete_enabled = false;

$HD_Form->AddSearchPopupInput(_("Card ID"), "id_cc_card", "A2B_entity_card.php");
$HD_Form->AddSearchSqlSelectInput(_("Package"), "cc_package_offer", "label,id", "", "", "", "id_cc_package_offer");
$HD_Form->AddSearchDateInput(_("Date"), "date_consumption");

$form_action = "list";
$HD_Form->prepare_list_subselection($form_action);
if (empty($HD_Form->list_query_conditions)) {
    $date = (new DateTime("-1 day"))->format("Y-m-d");
    $HD_Form->list_query_conditions["date_consumption"] = [">=", $date];
}

$list = $HD_Form->perform_action($form_action);

require_once __DIR__ . "/../templates/main.php";

$HD_Form->create_search_form();

$HD_Form->create_toppage($form_action);
$HD_Form->create_form("list", $list);

$table = new Table(
    "cc_card_package_offer",
    ["DATE(date_consumption) AS day", "SUM(used_secondes) AS used_secondes", "COUNT(*) AS nbcall"]
);
$list_total_day = $table->getRows(DbConnect(), $HD_Form->list_query_conditions, ["day"], "ASC", ["day"]);

if (count($list_total_day)):
    $mmax = max(array_column($list_total_day, "used_secondes"));
    $widthbar = 0;
    $totalminutes = array_sum(array_column($list_total_day, "used_secondes"));
    $totalcall = array_sum(array_column($list_total_day, "nbcall"));
    $averageminutes = $totalminutes / $totalcall;

?>

<table class="table table-striped caption-top">
    <caption><?= _("Package Usage Summary") ?></caption>
    <thead>
        <tr>
            <th><?= _( "Date" ) ?></th>
            <th><abbr title="<?= _( "Total call duration" ) ?>"><?= _( "Time" ) ?></abbr></th>
            <th style="width: 10vw"></th>
            <th><abbr title="<?= _( "Average call duration" ) ?>"><?= _( "Avg Time" ) ?></abbr></th>
            <th><?= _( "Calls" ) ?></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($list_total_day as $data): ?>
        <tr>
            <td><?= $data["day"] ?></td>
            <td><?= get_minute($data["used_secondes"]) ?></td>
            <td aria-hidden="true">
                <div style="width: <?= $mmax ? ($data["used_secondes"] / $mmax) * 100 : 0 ?>%; background: darkred">&nbsp;</div>
            </td>
            <td><?= get_minute(intval($data ["used_secondes"] / $data ["nbcall"])) ?></td>
            <td><?= $data["nbcall"] ?></td>
        </tr>
    <?php endforeach ?>
    </tbody>
    <tfoot class="table-group-divider">
        <tr>
            <th scope="row"><?= _( "TOTAL" ) ?></th>
            <td colspan="2"><?= get_minute($totalminutes) ?></td>
            <td><?= get_minute($averageminutes) ?></td>
            <td><?= $totalcall ?></td>
        </tr>
    </tfoot>
</table>
<?php endif ?>

<?php require_once __DIR__ . "/../templates/footer.php" ?>

