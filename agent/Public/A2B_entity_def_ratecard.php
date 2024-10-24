<?php

use A2billing\Agent;

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

require_once "../../common/lib/agent.defines.php";
include './form_data/FG_var_def_ratecard.inc';

if (!has_rights(Agent::ACX_RATECARD)) {
    Header("HTTP/1.0 401 Unauthorized");
    Header("Location: PP_error.php?c=accessdenied");
    die();
}

getpost_ifset(array (
    'popup_select',
    'popup_formname',
    'popup_fieldname',
    'posted',
    'Period',
    'frommonth',
    'fromstatsmonth',
    'tomonth',
    'tostatsmonth',
    'fromday',
    'fromstatsday_sday',
    'fromstatsmonth_sday',
    'today',
    'tostatsday_sday',
    'tostatsmonth_sday',
    'current_page',
    'removeallrate',
    'removetariffplan',
    'definecredit',
    'IDCust',
    'mytariff_id',
    'destination',
    'dialprefix',
    'buyrate1',
    'buyrate2',
    'buyrate1type',
    'buyrate2type',
    'rateinitial1',
    'rateinitial2',
    'rateinitial1type',
    'rateinitial2type',
    'id_trunk',
    "check",
    "type",
    "mode"
));

$HD_Form->init();

if (!isset ($form_action))
    $form_action = "list"; //ask-add
if (!isset ($action))
    $action = $form_action;

if (is_string($tariffgroup) && strlen(trim($tariffgroup)) > 0) {
    [$mytariffgroup_id, $mytariffgroupname, $mytariffgrouplcrtype] = preg_split('/-:-/', $tariffgroup);
    $_SESSION["mytariffgroup_id"] = $mytariffgroup_id;
    $_SESSION["mytariffgroupname"] = $mytariffgroupname;
    $_SESSION["tariffgrouplcrtype"] = $mytariffgrouplcrtype;
} else {
    $mytariffgroup_id = $_SESSION["mytariffgroup_id"];
    $mytariffgroupname = $_SESSION["mytariffgroupname"];
    $mytariffgrouplcrtype = $_SESSION["tariffgrouplcrtype"];
}

if (($form_action == "list") && ($HD_Form->search_form_enabled) && ($_POST['posted_search'] == 1) && is_numeric($mytariffgroup_id)) {
    if (!empty ($HD_Form->FG_QUERY_WHERE_CLAUSE)) {
        $HD_Form->FG_QUERY_WHERE_CLAUSE .= ' AND ';
    }

    $HD_Form->FG_QUERY_WHERE_CLAUSE = "idtariffplan='$mytariff_id'";
    $HD_Form->list_query_conditions["idtariffplan"] = $mytariff_id;

    /*
    SELECT t1.destination, min(t1.rateinitial), t1.dialprefix FROM cc_ratecard t1, cc_tariffplan t4, cc_tariffgroup t5,
    cc_tariffgroup_plan t6
    WHERE t4.id = t6.idtariffplan AND t6.idtariffplan=t1.idtariffplan AND t6.idtariffgroup = '3'
    GROUP BY t1.dialprefix
    */
}

$list = $HD_Form->perform_action($form_action);

// #### HEADER SECTION
$smarty->display('main.tpl');

// #### HELP SECTION
if (!$popup_select) {
    if (($form_action == 'ask-add') || ($form_action == 'ask-edit'))
        echo $CC_help_add_rate;
} else {
    echo $CC_help_def_ratecard;
}

// DISPLAY THE UPDATE MESSAGE
if (isset ($update_msg) && strlen($update_msg) > 0) {
    echo $update_msg;
}

if ($popup_select) {
?>
<SCRIPT LANGUAGE="javascript">
<!--
function sendValue(selvalue) {
    window.opener.document.<?php echo $popup_formname ?>.<?php echo $popup_fieldname ?>.value = selvalue;
    window.close();
}
-->
</script>
<?php
}

if (!$popup_select) {
    // #### CREATE SEARCH FORM
    if ($form_action == "list") {
        $HD_Form->create_search_form();
    }
}
?>

<br>
<?php

// #### TOP SECTION PAGE
$HD_Form->create_toppage($form_action);

$HD_Form->create_form($form_action, $list);
$HD_Form->setup_export();

// #### FOOTER SECTION
$smarty->display('footer.tpl');
