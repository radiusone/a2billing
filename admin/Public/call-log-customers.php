<?php

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

require_once "../../common/lib/admin.defines.php";
/**
 * @var SmartyBC $smarty
 */

if (! has_rights ( ACX_CALL_REPORT )) {
    header ( "HTTP/1.0 401 Unauthorized" );
    header ( "Location: PP_error.php?c=accessdenied" );
    die ();
}

global $letter;

getpost_ifset ([
    'customer', 'sellrate', 'buyrate', 'entercustomer','entercustomer_num', 'enterprovider', 'entertariffgroup',
    'entertrunk', 'enterratecard', 'posted', 'Period', 'frommonth', 'fromstatsmonth', 'tomonth', 'tostatsmonth',
    'fromday', 'fromstatsday_sday', 'fromstatsmonth_sday', 'today', 'tostatsday_sday', 'tostatsmonth_sday', 
    'fromtime', 'totime', 'fromstatsday_hour', 'tostatsday_hour', 'fromstatsday_min', 'tostatsday_min', 'dsttype',
    'srctype', 'dnidtype', 'clidtype', 'channel', 'resulttype', 'stitle', 'atmenu', 'current_page', 'order', 'sens',
    'dst', 'src', 'dnid', 'clid', 'choose_currency', 'terminatecauseid', 'choose_calltype', 'download', 'file',
]);
/**
 * @var string $customer
 * @var string $sellrate
 * @var string $buyrate
 * @var string $entercustomer
 * @var string $entercustomer_num
 * @var string $enterprovider
 * @var string $entertariffgroup
 * @var string $entertrunk
 * @var string $enterratecard
 * @var string $posted
 * @var string $Period
 * @var string $frommonth
 * @var string $fromstatsmonth
 * @var string $tomonth
 * @var string $tostatsmonth
 * @var string $fromday
 * @var string $fromstatsday_sday
 * @var string $fromstatsmonth_sday
 * @var string $today
 * @var string $tostatsday_sday
 * @var string $tostatsmonth_sday
 * @var string $fromtime
 * @var string $totime
 * @var string $fromstatsday_hour
 * @var string $tostatsday_hour
 * @var string $fromstatsday_min
 * @var string $tostatsday_min
 * @var string $dsttype
 * @var string $srctype
 * @var string $dnidtype
 * @var string $clidtype
 * @var string $channel
 * @var string $resulttype
 * @var string $stitle
 * @var string $atmenu
 * @var string $current_page
 * @var string $order
 * @var string $sens
 * @var string $dst
 * @var string $src
 * @var string $dnid
 * @var string $clid
 * @var string $choose_currency
 * @var string $terminatecauseid
 * @var string $choose_calltype
 * @var string $download
 * @var string $file
 */
$current_page = (int)($current_page ?? 0);
$nodisplay = (bool)($_REQUEST["nodisplay"] ?? 0);
$posted = $posted ?? "0";

if (($download ?? "") === "file" && !empty($file)) {

    $value_de = base64_decode($file);
    if (str_contains($file, '/') || $value_de === false || str_contains($value_de, '..')) {
        exit;
    }

    $dl_full = MONITOR_PATH . "/" . $value_de;

    if (!is_readable($dl_full)) {
        echo _("ERROR: Cannot download file $dl_full, it does not exist.");
        exit ();
    }

    header("Content-Type: application/octet-stream");
    header("Content-Disposition: attachment; filename=$value_de");
    header("Content-Length: " . filesize($dl_full));
    header("Accept-Ranges: bytes");
    header("Pragma: no-cache");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-transfer-encoding: binary");

    readfile($dl_full);
    exit ();
}

$HD_Form = new FormHandler("cc_call", gettext("CDR"));
$HD_Form->setDBHandler(DbConnect());
$HD_Form->init();

$currencies_list = array_map(fn ($v) => array_reverse($v), getCurrenciesList());
$dialstatus_list = getDialStatusList();
$dialstatus_list_r = array_map(fn ($v) => array_reverse($v), $dialstatus_list);
$yesno = getYesNoList();
$calltype_list = [
    [0, _("STANDARD")],
    [1, _("SIP/IAX")],
    [2, _("DIDCALL")],
    [3, _("DID_VOIP")],
    [4, _("CALLBACK")],
    [5, _("PREDICT")],
    [6, _("AUTO DIALER")],
    [7, _("DID-ALEG")],
];

$HD_Form->no_debug();
$HD_Form->FG_QUERY_TABLE_NAME = "cc_call LEFT OUTER JOIN cc_trunk ON cc_call.id_trunk = cc_trunk.id_trunk LEFT OUTER JOIN cc_ratecard ON cc_call.id_ratecard = cc_ratecard.id LEFT OUTER JOIN cc_card ON cc_call.card_id = cc_card.id";
$HD_Form->FG_QUERY_COLUMN_LIST = 'cc_call.starttime, cc_call.src, cc_call.dnid, cc_call.calledstation, cc_call.destination AS dest, cc_ratecard.buyrate, cc_ratecard.rateinitial, cc_call.sessiontime, cc_call.card_id, cc_trunk.trunkcode, cc_call.terminatecauseid, cc_call.sipiax, cc_call.buycost, cc_call.sessionbill, CASE WHEN cc_call.sessionbill != 0 THEN ((cc_call.sessionbill - cc_call.buycost) / cc_call.sessionbill) * 100 ELSE NULL END AS margin, CASE WHEN cc_call.buycost != 0 THEN ((cc_call.sessionbill - cc_call.buycost) / cc_call.buycost) * 100 ELSE NULL END AS markup, cc_call.id, cc_trunk.id_provider, cc_trunk.id_trunk AS trunk_id';

$DBHandle = DbConnect ();

$HD_Form->AddViewElement(_("Date"), "starttime", true, "19", "display_dateformat");
$HD_Form->AddViewElement(_("CallerID"), "src");
$HD_Form->AddViewElement(_("DNID"), "dnid");
$HD_Form->AddViewElement(_("Phone Number"), "calledstation");
$HD_Form->AddViewElement(_("Destination"), "dest", true, "15", null, "lie", "cc_prefix", "destination,prefix", "prefix='%id'", "%1");
$HD_Form->AddViewElement(_("Buy Rate"), "buyrate", true, "30", "display_2bill");
$HD_Form->AddViewElement(_("Sell Rate"), "rateinitial", true, "30", "display_2bill");
$HD_Form->AddViewElement(_("Duration"), "sessiontime", true, "30", "display_minute");
$HD_Form->AddViewElement(_("Account"), "card_id", true, "", "", "lie_link", "cc_card", "username,id", "id='%id'", "%1", "A2B_entity_card.php");
$HD_Form->AddViewElement(_("Trunk"), "trunkcode");
$HD_Form->AddViewElement(_("Disposition"), "terminatecauseid", true, "", null, "list", $dialstatus_list);
$HD_Form->AddViewElement(_("CallType"), "sipiax", true, "", null, "list", $calltype_list);
$HD_Form->AddViewElement(_("Buy"), "buycost", true, "30", "display_2bill");
$HD_Form->AddViewElement(_("Sell"), "sessionbill", true, "30", "display_2bill");
$HD_Form->AddViewElement(_("Margin"), "margin", true, "30", "display_2dec_percentage");
$HD_Form->AddViewElement(_("Markup"), "markup", true, "30", "display_2dec_percentage");

$HD_Form->FG_ENABLE_DELETE_BUTTON = true;
$HD_Form->FG_DELETE_BUTTON_LINK = "A2B_entity_call.php?form_action=ask-delete&id=";

if (LINK_AUDIO_FILE) {
    // TODO: figure out how this works, move it into this file with custom button
    $HD_Form->AddViewElement("", "uniqueid", false, "30", "display_monitorfile_link", "", "", "", "", "");
    $HD_Form->FG_QUERY_COLUMN_LIST .= ', cc_call.uniqueid';
}

$HD_Form->FG_LIST_VIEW_PAGE_SIZE = 25;

$HD_Form->CV_TITLE_TEXT = _("Call Logs");

$order = $HD_Form->FG_TABLE_DEFAULT_ORDER = $order ?? "cc_call.starttime";
$sens = $HD_Form->FG_TABLE_DEFAULT_SENS = $sens ?? "DESC";

// EXPORT
$HD_Form->FG_EXPORT_CSV = true;
$HD_Form->FG_EXPORT_XML = true;
$HD_Form->FG_EXPORT_SESSION_VAR = "pr_export_entity_call";
$ord = implode(",", $HD_Form->FG_QUERY_ORDERBY_COLUMNS);
$_SESSION[$HD_Form->FG_EXPORT_SESSION_VAR] = "SELECT $HD_Form->FG_QUERY_COLUMN_LIST FROM $HD_Form->FG_QUERY_TABLE_NAME WHERE $HD_Form->FG_QUERY_WHERE_CLAUSE ORDER BY $ord $HD_Form->FG_QUERY_DIRECTION";

/************************/

$HD_Form->FG_FILTER_SEARCH_FORM = true;
$HD_Form->FG_FILTER_SEARCH_SESSION_NAME = 'call_log_selection';
$HD_Form->FG_FILTER_SEARCH_TOP_TEXT = gettext('Define specific criteria to search for call records');
$HD_Form->FG_FILTER_SEARCH_1_TIME = true;
$HD_Form->FG_FILTER_SEARCH_1_TIME_TEXT = _('DATE');
$HD_Form->FG_FILTER_SEARCH_1_TIME_FIELD = "cc_call.starttime";

if ($_SESSION ["pr_groupID"] != 2 || !is_numeric($_SESSION ["pr_IDCust"])) {
    $HD_Form->AddSearchPopupInput("card_id", _("Enter the customer ID"), "A2B_entity_card.php");
    $HD_Form->AddSearchPopupInput("username", _("Enter the customer number"), "A2B_entity_card.php", 2);
    $HD_Form->AddSearchPopupInput("id_tariffgroup", _("Call Plan"), "A2B_entity_tariffgroup.php", 2);
    $HD_Form->AddSearchPopupInput("id_provider", _("Provider"), "A2B_entity_provider.php", 2);
    $HD_Form->AddSearchPopupInput("cc_call.id_trunk", _("Trunk"), "A2B_entity_trunk.php", 2);
    $HD_Form->AddSearchPopupInput("id_ratecard", _("Rate"), "A2B_entity_def_ratecard.php", 2);
}

$HD_Form->AddSearchTextInput(_("Phone number"), "destination", "dsttype");
$HD_Form->AddSearchTextInput(_("Caller ID"), "src", "srctype");
$HD_Form->AddSearchTextInput(_("DNID"), "dnid", "dnidtype");

$HD_Form->AddSearchSelectInput(_("Disposition"), "terminatecauseid", $dialstatus_list_r);
$HD_Form->AddSearchSelectInput(_("Call type"), "sipiax", $calltype_list);
/** TODO: find some way to intercept display of records to apply these options
$HD_Form->FG_FILTER_SEARCH_FORM_SELECT[] = [_("Currency"), false, "choose_currency", $currencies_list];
$HD_Form->FG_FILTER_SEARCH_FORM_SELECT[] = [_("Time unit"), false, "choose_timeunit", [["min", _("Minutes")], ["sec", _("Seconds")]]];
 */
$HD_Form->FG_FILTER_SEARCH_DELETE_ALL = false;

$form_action = $form_action ?? "list";
if (!$nodisplay) {
    $HD_Form->prepare_list_subselection('list');
    $list = $HD_Form->perform_action($form_action);
}

$smarty->display ( 'main.tpl' );

?>

<!-- ** ** ** ** ** Part for the research ** ** ** ** ** -->
<div class="row justify-content-center">
    <div class="col-auto">
        <button
                class="btn btn-sm <?= empty($_SESSION[$HD_Form->FG_FILTER_SEARCH_SESSION_NAME]) ? "btn-outline-primary" : "btn-primary" ?>"
                data-bs-toggle="modal"
                data-bs-target="#searchModal"
                title="<?= _("Search Calls") ?> <?= empty($_SESSION[$HD_Form->FG_FILTER_SEARCH_SESSION_NAME]) ? "" : "(" . _("search activated") . ")" ?>"
        >
            <?= _("Search Calls") ?>
        </button>
    </div>
</div>
    <div class="modal" id="searchModal" aria-labelledby="modal-title-search" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-title-search"><?= _("Search Customers") ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php $HD_Form->create_search_form() ?>
                </div>
                <!-- buttons are in the form
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" form="searchForm" class="btn btn-primary"><?= _("Search") ?></button>
                </div>
                -->
            </div>
        </div>
    </div>

<!-- ** ** ** ** ** Part to display the CDR ** ** ** ** ** -->
<?php
if (!$nodisplay) {
    $HD_Form->create_toppage($form_action);
    $HD_Form->create_form("list", $list);
}
?>
<!-- ** ** ** ** ** Part to display the GRAPHIC ** ** ** ** ** -->

<br>

<?php
$list_total_day = [];
if (!$nodisplay) {
    $QUERY = "SELECT DATE(cc_call.starttime) AS day, SUM(cc_call.sessiontime) AS calltime, SUM(cc_call.sessionbill) AS cost, COUNT(*) as nbcall,
            SUM(cc_call.buycost) AS buy, SUM(CASE WHEN cc_call.sessiontime > 0 THEN 1 ELSE 0 END) AS success_calls
            FROM $HD_Form->FG_QUERY_TABLE_NAME WHERE $HD_Form->FG_QUERY_WHERE_CLAUSE GROUP BY day ORDER BY day";

    $res = $HD_Form->DBHandle->Execute ( $QUERY );
    if ($res) {
        $list_total_day = $res->GetAll();
    }
}

if (count($list_total_day)) {

    $mmax = 0;
    $totalcall = 0;
    $totalminutes = 0;
    $totalsuccess = 0;
    $totalfail = 0;
    $totalcost = 0;
    $totalbuycost = 0;
    foreach ($list_total_day as $data) {
        if ($mmax < $data [1]) {
            $mmax = $data [1];
        }
        $totalcall += $data [3];
        $totalminutes += $data [1];
        $totalcost += $data [2];
        $totalbuycost += $data [4];
        $totalsuccess += $data [5];
    }
    $max_fail = 0;
?>

<!-- END TITLE GLOBAL MINUTES //-->

<table border="0" cellspacing="0" cellpadding="0" width="95%">
    <tbody>
        <tr>
            <td bgcolor="#000000">
            <table border="0" cellspacing="1" cellpadding="2" width="100%">
                <tbody>
                    <tr>
                        <td align="center" class="bgcolor_019"></td>
                        <td class="bgcolor_020" align="center" colspan="10"><font
                            class="fontstyle_003"><?= _( "TRAFFIC SUMMARY" ) ?></font></td>
                    </tr>
                    <tr class="bgcolor_019">
                        <td align="center" class="bgcolor_020"><font class="fontstyle_003"><?= _( "DATE" ) ?></font></td>
                        <td align="center"><font class="fontstyle_003"><acronym
                            title="<?= _( "DURATION" ) ?>"><?= _( "DUR" ) ?></acronym></font></td>
                        <td align="center"><font class="fontstyle_003"><?= _( "GRAPHIC" ) ?></font></td>
                        <td align="center"><font class="fontstyle_003"><?= _( "CALLS" ) ?></font></td>
                        <td align="center"><font class="fontstyle_003"><acronym
                            title="<?= _( "AVERAGE LENGTH OF CALL" ) ?>"><?= _( "ALOC" ) ?></acronym></font></td>
                        <td align="center"><font class="fontstyle_003"><acronym
                            title="<?= _( "ANSWER SEIZE RATIO" ) ?>"><?= _( "ASR" ) ?></acronym></font></td>
                        <td align="center"><font class="fontstyle_003"><?= _( "SELL" ) ?></font></td>
                        <td align="center"><font class="fontstyle_003"><?= _( "BUY" ) ?></font></td>
                        <td align="center"><font class="fontstyle_003"><?= _( "PROFIT" ) ?></font></td>
                        <td align="center"><font class="fontstyle_003"><?= _( "MARGIN" ) ?></font></td>
                        <td align="center"><font class="fontstyle_003"><?= _( "MARKUP" ) ?></font></td>

                        <!-- LOOP -->
    <?php
    $i = 0;
    $j = 0;
    foreach ($list_total_day as $data) {
        $i = ($i + 1) % 2;
        $tmc = $data [1] / $data [3];

        if ((! isset ( $resulttype )) || ($resulttype == "min")) {
            $tmc = sprintf ( "%02d", intval ( $tmc / 60 ) ) . ":" . sprintf ( "%02d", $tmc % 60);
        } else {

            $tmc = intval ( $tmc );
        }

        if ((! isset ( $resulttype )) || ($resulttype == "min")) {
            $minutes = sprintf ( "%02d", intval ( $data [1] / 60 ) ) . ":" . sprintf ( "%02d", $data [1] % 60);
        } else {
            $minutes = $data [1];
        }
        if ($mmax > 0) {
            $widthbar = intval(($data [1] / $mmax) * 150);
        }
        ?>
        </tr>
                    <tr>
                        <td align="right" class="sidenav" nowrap="nowrap"><font
                            class="fontstyle_003"><?= $data [0] ?></font></td>
                        <td
                            align="right" nowrap="nowrap"><font class="fontstyle_006"><?= $minutes ?> </font></td>
                        <td
                            align="left" nowrap="nowrap" width="<?= $widthbar + 40 ?>">
                        <table cellspacing="0" cellpadding="0">
                            <tbody>
                                <tr>
                                    <td bgcolor="#e22424"><img
                                        src="<?= Images_Path ?>/spacer.gif"
                                        width="<?= $widthbar ?>" height="6"></td>
                                </tr>
                            </tbody>
                        </table>
                        </td>
                        <td
                            align="right" nowrap="nowrap"><font class="fontstyle_006"><?= $data [3] ?></font></td>
                        <td
                            align="right" nowrap="nowrap"><font class="fontstyle_006"><?= $tmc ?> </font></td>
                        <td
                            align="right" nowrap="nowrap"><font class="fontstyle_006"><?= get_2dec_percentage ( $data [5] * 100/ ($data [3]) ) ?> </font></td>
                        <!-- SELL -->
                        <td
                            align="right" nowrap="nowrap"><font class="fontstyle_006"><?= get_2bill ( $data [2] ) ?>
                        </font></td>
                        <!-- BUY -->
                        <td
                            align="right" nowrap="nowrap"><font class="fontstyle_006"><?= get_2bill ( $data [4] ) ?>
                        </font></td>
                        <!-- PROFIT -->
                        <td
                            align="right" nowrap="nowrap"><font class="fontstyle_006"><?= get_2bill ( $data [2] - $data [4] ) ?>
                        </font></td>
                        <td
                            align="right" nowrap="nowrap"><font class="fontstyle_006"><?php
                            if ($data [2] != 0) {
                                echo get_2dec_percentage ( (($data [2] - $data [4]) / $data [2]) * 100 );
                            } else {
                                echo "NULL";
                            }
                            ?>
                        </font></td>
                        <td
                            align="right" nowrap="nowrap"><font class="fontstyle_006"><?php
                            if ($data [4] != 0) {
                                echo get_2dec_percentage ( (($data [2] - $data [4]) / $data [4]) * 100 );
                            } else {
                                echo "NULL";
                            }
                            ?>
                        </font></td>
                 <?php
                    $j ++;
                }

                if ((! isset ( $resulttype )) || ($resulttype == "min")) {
                    $total_tmc = sprintf ( "%02d", intval ( ($totalminutes / $totalcall) / 60 ) ) . ":" . sprintf ( "%02d", ($totalminutes / $totalcall) % 60);
                    $totalminutes = sprintf ( "%02d", intval ( $totalminutes / 60 ) ) . ":" . sprintf ( "%02d", $totalminutes % 60);
                } else {
                    $total_tmc = intval ( $totalminutes / $totalcall );
                }

                ?>
                </tr>
                    <!-- END DETAIL -->

                    <!-- END LOOP -->

                    <!-- TOTAL -->
                    <tr bgcolor="bgcolor_019">
                        <td align="right" nowrap="nowrap"><font class="fontstyle_003"><?= _( "TOTAL" ) ?></font></td>
                        <td align="center" nowrap="nowrap" colspan="2"><font
                            class="fontstyle_003"><?= $totalminutes ?> </font></td>
                        <td align="center" nowrap="nowrap"><font class="fontstyle_003"><?= $totalcall ?></font></td>
                        <td align="center" nowrap="nowrap"><font class="fontstyle_003"><?= $total_tmc ?></font></td>
                        <td align="center" nowrap="nowrap"><font class="fontstyle_003"><?= get_2dec_percentage ( $totalsuccess*100 / $totalcall ) ?> </font></td>
                        <td align="center" nowrap="nowrap"><font class="fontstyle_003"><?= get_2bill ( $totalcost ) ?></font></td>
                        <td align="center" nowrap="nowrap"><font class="fontstyle_003"><?= get_2bill ( $totalbuycost ) ?></font></td>
                        <td align="center" nowrap="nowrap"><font class="fontstyle_003"><?= get_2bill ( $totalcost - $totalbuycost ) ?></font></td>
                        <td align="center" nowrap="nowrap"><font class="fontstyle_003"><?php
                            if ($totalcost != 0) {
                                echo get_2dec_percentage ( (($totalcost - $totalbuycost) / $totalcost) * 100 );
                            } else {
                                echo "NULL";
                            }
                            ?></font></td>
                        <td align="center" nowrap="nowrap"><font class="fontstyle_003"><?php
                            if ($totalbuycost != 0) {
                                echo get_2dec_percentage ( (($totalcost - $totalbuycost) / $totalbuycost) * 100 );
                            } else {
                                echo "NULL";
                            }
                            ?></font></td>
                    </tr>
                    <!-- END TOTAL -->

                </tbody>
            </table>
            <!-- END ARRAY GLOBAL //--></td>
        </tr>
    </tbody>
</table>

<?php } else { ?>
<center>
<h3><?= _( "No calls in your selection") ?>.</h3>
<?php  } ?>
</center>
<script>
$(function() {
    $("#archiveselect").on('change', () => this.form.submit());
});
</script>

<?php

$smarty->display('footer.tpl');
