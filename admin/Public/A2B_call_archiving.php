<?php

use A2billing\Admin;
use A2billing\Forms\FormHandler;

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

$menu_section = 16;
require_once "../../common/lib/admin.defines.php";
/**
 * @var Smarty $smarty
 * @var FormHandler $HD_Form
 * @var string $popup_select
 * @var string $popup_formname
 * @var string $popup_fieldname
 */

Admin::checkPageAccess(Admin::ACX_MAINTENANCE);

getpost_ifset([
    "posted_search", "posted_archive", "enable_search_start_date", "search_start_date", "enable_search_end_date",
    "search_end_date", "enable_search_months", "search_months", "card_id", "id_provider", "id_tariffgroup",
    "id_trunk", "id_ratecard", "dst", "dsttype", "src", "srctype", "current_page", "order", "sens", "resulttype",
    "choose_currency", "calltype"
]);
/**
 * @var bool|string $posted_search whether the user has clicked the search button
 * @var bool|string $posted_archive whether the user has clicked the archive button
 * @var bool|string $enable_search_start_date
 * @var string $search_start_date
 * @var bool|string $enable_search_end_date
 * @var string $search_end_date
 * @var bool|string $enable_search_months
 * @var string $search_months
 * @var string $card_id
 * @var string $id_provider
 * @var string $id_tariffgroup
 * @var string $id_trunk
 * @var string $id_ratecard
 * @var string $dst
 * @var string $dsttype
 * @var string $src
 * @var string $srctype
 * @var string $current_page
 * @var string $order
 * @var string $sens
 * @var string $resulttype
 * @var string $choose_currency
 * @var string $calltype
 */

$current_page = (int)($current_page ?? 0);
$posted_search = (bool)($posted_search ?? false);
$posted_archive = (bool)($posted_archive ?? false);
$enable_search_start_date = (bool)($enable_search_start_date ?? false);
$enable_search_end_date = (bool)($enable_search_end_date ?? false);
$card_id = (int)$card_id ?? 0;
$id_provider = (int)$id_provider ?? 0;
$id_tariffgroup = (int)$id_tariffgroup ?? 0;
$id_trunk = (int)$id_trunk ?? 0;
$id_ratecard = (int)$id_ratecard ?? 0;

$HD_Form = new FormHandler(
    "cc_call LEFT OUTER JOIN cc_trunk ON cc_call.id_trunk = cc_trunk.id_trunk LEFT OUTER JOIN cc_card ON cc_call.card_id = cc_card.id",
    "Calls"
);

$HD_Form->init();

$HD_Form->no_debug();
$HD_Form->FG_TABLE_DEFAULT_ORDER = "starttime";
$HD_Form->FG_TABLE_DEFAULT_SENS = "DESC";
$HD_Form->FG_LIST_VIEW_PAGE_SIZE = 30;
$HD_Form->FG_QUERY_PRIMARY_KEY = "cc_call.id";
$HD_Form->CV_NO_FIELDS = _("No matching calls found; use the fields above to refine your search.");

$HD_Form->AddViewElement(_("Calldate"), "starttime", true, 19, "display_dateformat");
$HD_Form->AddViewElement(_("CalledNumber"), "calledstation", true, 30, "display_did");
$HD_Form->AddViewElement(_("Destination"), "destination", true, 30, "display_without_prefix");
$HD_Form->AddViewElement(_("Duration"), "sessiontime", true, 30, "display_minute");
$HD_Form->AddViewElement(_("Card Used"), "cc_card.username", true, 30, "display_customer_link");
$HD_Form->AddViewElement(_("Disposition"), "terminatecauseid", true, 30, "", "list", getDialStatusList());
$HD_Form->AddViewElement(_("IAX/SIP"), "sipiax", true, 0, "", "list", getYesNoList());
$HD_Form->AddViewElement(_("Cost"), "sessionbill", true, 30, "display_2bill");
$HD_Form->FieldViewElement('starttime, calledstation, destination, real_sessiontime, card_id, terminatecauseid, sipiax, sessionbill');

$param_condition = 'WHERE 1=1';
$params = [];

$SQLcmd = '';
$SQLcmd = do_field($SQLcmd, 'src', 'src');
$SQLcmd = do_field($SQLcmd, 'dst', 'calledstation');
if (!empty($src)) {
    build_query_safe($param_condition, 'src', 'src', $params);
}
if (!empty($dst)) {
    build_query_safe($param_condition, 'dst', 'calledstation', $params);
}

$date_clause='';
if ($enable_search_start_date && !empty($search_start_date)) {
    $date_clause .= " AND starttime >= '$search_start_date'";
    $param_condition .= " AND starttime >= ?";
    $params[] = $search_start_date;
}
if ($enable_search_end_date && !empty($search_end_date)) {
    $date_clause .= " AND starttime <= '$search_end_date 23:59:59'";
    $param_condition .= " AND starttime <= ?";
    $params[] = "$search_end_date 23:59:59";
}
if ($enable_search_months) {
    if (DB_TYPE == "postgres") {
        $date_clause .= " AND CURRENT_TIMESTAMP - interval '$search_months months' > starttime";
        $param_condition .= " AND starttime <= CURRENT_TIMESTAMP - INTERVAL ?";
        $params[] = "$search_months months";
    } else {
        $date_clause .= " AND NOW() - INTERVAL $search_months MONTH > starttime";
        $param_condition .= " AND starttime <= NOW() - INTERVAL ? MONTH";
        $params[] = $search_months;
    }
}

if (str_starts_with($SQLcmd, ' WHERE ')) {
    $HD_Form->FG_QUERY_WHERE_CLAUSE = substr($SQLcmd,6) . $date_clause;
} elseif (str_starts_with($date_clause, ' AND ')) {
    $HD_Form->FG_QUERY_WHERE_CLAUSE = substr($date_clause,5);
}

if (empty($HD_Form->FG_QUERY_WHERE_CLAUSE)) {
    $HD_Form->FG_QUERY_WHERE_CLAUSE=" starttime >= CURRENT_DATE";
}
if ($param_condition === 'WHERE 1=1') {
    $param_condition .= " AND starttime >= CURRENT_DATE";
}

if (!empty($card_id)) {
    $HD_Form->FG_QUERY_WHERE_CLAUSE.=" AND username='$card_id'";
    $param_condition .= " AND username = ?";
    $params[] = $card_id;
}
if ($_SESSION["is_admin"] == 1) {
    if ($id_provider > 0) {
        $HD_Form->FG_QUERY_WHERE_CLAUSE .= " AND cc_trunk.id_provider = '$id_provider'";
        $param_condition .= "cc_trunk.id_provider = ?";
        $params[] = $id_provider;
    }
    if ($id_trunk > 0) {
        $HD_Form->FG_QUERY_WHERE_CLAUSE .= " AND id_trunk = '$id_trunk'";
        $param_condition .= " AND id_trunk = ?";
        $params[] = $id_trunk;
    }
    if ($id_tariffgroup > 0) {
        $HD_Form->FG_QUERY_WHERE_CLAUSE .= " AND id_tariffgroup = '$id_tariffgroup'";
        $param_condition .= " AND id_tariffgroup = ?";
        $params[] = $id_tariffgroup;
    }
    if ($id_ratecard > 0) {
        $HD_Form->FG_QUERY_WHERE_CLAUSE .= " AND id_ratecard = '$id_ratecard'";
        $param_condition .= " AND id_ratecard = ?";
        $params[] = $id_ratecard;
    }

}

if (($calltype ?? "answered") === "answered") {
    $HD_Form->FG_QUERY_WHERE_CLAUSE .= " AND terminatecauseid=1 ";
    $param_condition .= " AND terminatecauseid = 1";
}

$archive_message = "";
if ($posted_archive === true) {
    $res = archive_data($param_condition, $params);
    if ($res) {
        $HD_Form->CV_NO_FIELDS = "The data has been successfully archived";
    } else {
        $archive_message = "There was an error archiving the data";
    }
}

$HD_Form->search_form_enabled = true;
$HD_Form->search_session_key = 'call_archive_selection';
$HD_Form->search_form_title = gettext('Define specific criteria to search for call records');
$HD_Form->search_date_enabled = true;
$HD_Form->search_date_text = _('Dates');
$HD_Form->search_date_column = "starttime";
$HD_Form->search_months_ago_enabled = true;
$HD_Form->search_months_ago_column = "starttime";
$HD_Form->search_delete_enabled = false;
$HD_Form->search_months_ago_text = _("Calls older than");

$HD_Form->AddSearchPopupInput("card_id", _("Enter the customer number"), "A2B_entity_card.php", 2);
$HD_Form->AddSearchPopupInput("id_tariffgroup", _("Call Plan"), "A2B_entity_tariffgroup.php", 2);
$HD_Form->AddSearchPopupInput("id_provider", _("Provider"), "A2B_entity_provider.php", 2);
$HD_Form->AddSearchPopupInput("id_trunk", _("Trunk"), "A2B_entity_trunk.php", 2);
$HD_Form->AddSearchPopupInput("id_ratecard", _("Rate"), "A2B_entity_def_ratecard.php", 2);

$HD_Form->AddSearchTextInput(_("Destination"), "dst", "dsttype");
$HD_Form->AddSearchTextInput(_("Source"), "src", "srctype");

$HD_Form->AddSearchSelectInput(_("Disposition"), "calltype", [["answered", _("Answered Only")], ["all", _("All Calls")]]);

if ($posted_search === true && $posted_archive === false) {
    $HD_Form->AddSearchButton(
        "posted_archive",
        "Archive Displayed Calls",
        "true", "btn-secondary",
        "return confirm('This action will archive the selected calls. Are you sure?')"
    );
}

$smarty->display('main.tpl');
$HD_Form->create_search_form();

if ($posted_archive === true) {
    print "<div align=\"center\">".$archive_message."</div>";
}

$form_action ??= "list";
//ask-add
$action ??= $form_action;

$list = $HD_Form->perform_action($form_action);

$HD_Form->create_form($form_action, $list) ;

$smarty->display('footer.tpl');

/*
 * Function use to archive data and call records
 * Insert in cc_call_archive and cc_card_archive on seletion criteria
 * Delete from cc_call and cc_card
 * Used in
 * 1. A2Billing_UI/Public/A2B_data_archving.php
 * 2. A2Billing_UI/Public/A2B_call_archiving.php
 */
function archive_data(string $where, array $params = []): bool
{
    $handle = DbConnect();
    $handle->Execute("INSERT INTO cc_call_archive SELECT * FROM cc_call $where", $params);
    $handle->Execute("DELETE FROM cc_call $where", $params);

    return $handle->CommitTrans();
}
