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

$menu_section = 6;
require_once "../../common/lib/admin.defines.php";

set_time_limit(0);

if (! has_rights (Admin::ACX_RATECARD)) {
    Header ("HTTP/1.0 401 Unauthorized");
    Header ("Location: PP_error.php?c=accessdenied");
    die();
}

$HD_Form = new FormHandler();
$HD_Form -> init();
$HD_Form->no_debug();
$HD_Form -> search_session_key = 'entity_ratecard_selection';

getpost_ifset(array('posted' ,'ratecard_source' ,'ratecard_destination', 'search_sources'));

if ($posted == 1) {

    $instance_table = new Table();
    $bool = false;
    $ratecard_src_val = $ratecard_source;
    if (!is_numeric($ratecard_src_val)) {
        if($bool) $msg .= "<br>";
        $bool = true;
        $msg .= gettext("No Source ratecard selected !");
    }

    $ratecard_des_val = $ratecard_destination;
    if (!is_numeric($ratecard_des_val)) {
        if($bool) $msg .= "<br>";
        $bool = true;
        $msg .= gettext("No Destination ratecard selected !");
    }

    if ($ratecard_des_val == $ratecard_src_val) {
        if($bool) $msg .= "<br>";
        $bool = true;
        $msg .= gettext("Source Ratecard Should be different from Destination ratecard!");
    }

    if ($search_sources != 'nochange') {
        $fieldtomerge= preg_split("/\t/", $search_sources);
        $fieldtomerge_sql = str_replace("\t", ", ", $search_sources);
        $fieldtomerge_sql = trim ($fieldtomerge_sql);
        //if (strlen($fieldtomerge_sql)>0) $fieldtomerge_sql = ', '.$fieldtomerge_sql;
    }

    if (!$bool) {
        $count = 0;
        $fields = "dialprefix, ";
        $fields .= $fieldtomerge_sql;
        $fields_array = preg_split('/,/', $fields);

        if (!empty($_SESSION['search_ratecard'])) {
            $condition .= " AND ".$_SESSION['search_ratecard'];
        }

        $sql = "select $fields from cc_ratecard where idtariffplan = $ratecard_src_val $condition order by dialprefix,id";
        $result  = $instance_table->SQLExec ($HD_Form -> DBHandle, $sql);
        $q = "";
        $q_update = "";
        for ($i=0; $i<count($result); $i++) {
            $Update = "";
            for ($k=0; $k<count($fields_array); $k++) {
                $val = $result[$i][$k];
                if ($k == 0) {
                    $dialprefix = $result[$i][$k];
                } else {
                    $Update .= ",";
                }
                    $Update .= "$fields_array[$k] = '$val'";
            }
            $replac_able = "dialprefix = '".$dialprefix."',";
            $Update = str_replace($replac_able, "",$Update);
            $sql_target = "select id from cc_ratecard where idtariffplan = $ratecard_des_val and dialprefix = $dialprefix and is_merged = 0 $condition order by dialprefix, id";
            //$q .= "<br>SQL Target". $sql_target;
            $result_target  = $instance_table->SQLExec ($HD_Form -> DBHandle, $sql_target);
            $id = $result_target[0][0];
           if (!empty($id)) {
                $count++;
                $Update1 = "update cc_ratecard set $Update, is_merged = 1 where id = $id";
                $result_updated  = $instance_table->SQLExec ($HD_Form -> DBHandle, $Update1);
           }
        }
        $reset_table = "update cc_ratecard set is_merged = 0";
        $result_reset  = $instance_table->SQLExec ($HD_Form -> DBHandle, $reset_table);

        if($count > 0)
            $msg = "Ratecard is successfully merged.";
        else
            $msg = "Ratecard is not merged, please try again with different search criteria.";
    }
    $_SESSION['search_ratecard'] = "";
}

$HD_Form -> search_form_enabled = true;
$HD_Form -> search_date_text = gettext("Start Date / Month");
$HD_Form -> FG_FILTER_SEARCH_2_TIME_TEXT = gettext("Start Date / Day");
$HD_Form -> FG_FILTER_SEARCH_2_TIME_FIELD = 'startdate';
$HD_Form -> AddSearchTextInput(gettext("TAG"), 'tag','tagtype');
$HD_Form -> AddSearchTextInput(gettext("DESTINATION"), 'destination','destinationtype');
$HD_Form -> AddSearchTextInput(gettext("PREFIX"),'dialprefix','dialprefixtype');
$HD_Form -> AddSearchComparisonInput(gettext("BUYRATE"),'buyrate1','buyrate1type','buyrate2','buyrate2type','buyrate');
$HD_Form -> AddSearchComparisonInput(gettext("RATE INITIAL"),'rateinitial1','rateinitial1type','rateinitial2','rateinitial2type','rateinitial');
$HD_Form -> prepare_list_subselection('list');
$HD_Form -> AddSearchSqlSelectInput('SELECT TRUNK', "cc_trunk", "id_trunk, trunkcode, providerip", "", "trunkcode", "ASC", "id_trunk");
$_SESSION['search_ratecard'] = $HD_Form -> FG_QUERY_WHERE_CLAUSE;

/*************************************************************/

$instance_table_tariffname = new Table("cc_tariffplan", "id, tariffname");

$con = "";

$list_tariffname = $instance_table_tariffname  -> get_list ($HD_Form->DBHandle, $con, ["tariffname"]);

$nb_tariffname = count($list_tariffname);

$smarty->display('main.tpl');

//echo $CC_help_import_ratecard;
// #### CREATE SEARCH FORM
    $HD_Form -> create_search_form();
?>
<script>
$(function() {
    $("a#addsource").on('click', function () {
        $("#unselected_search_sources option:selected").appendTo($("#selected_search_sources"));
        resetHidden();
    });

    $("a#removesource").on('click', function () {
        $("#selected_search_sources option:selected").appendTo($("#unselected_search_sources"));
        resetHidden();
    });

    $("#selected_search_sources, #unselected_search_sources").on('change', function() {
        $("#selected_search_sources option:first, #unselected_search_sources option:first").prop("selected", false);
    });

    function resetHidden() {
        var tmp = [];
        $("#selected_search_sources option").each(() => tmp.push(this.value));
        $("#search_sources").val(tmp.join("\t"));
    }
});
</script>
<br/>
<div align="center">
        <table width="95%" border="0" cellspacing="2" align="center" class="editform_table1">
              <form name="prefs" action="" method="post">
                <?= $HD_Form->csrf_inputs() ?>

                <?php if ($posted) {?>
                <tr>
                    <td align="center" colspan="2">
                        <table width="100%">
                            <tr>
                                <td align="center" width = "90%"><?php echo $msg;?></td>
                                <td width="10%" align="right"><?php echo $count?> <?php echo gettext("Record(s)");?></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <?php }?>
                <tr>
                    <td width="30%" valign="middle" class="form_head"><?php echo gettext("RATECARD SOURCE");?> :</td>
                      <td width="70%" valign="top" class="tableBodyRight">
                          <select id="ratecard_source" NAME="ratecard_source" size="1"  style="width=250" class="form_input_select">
                            <option value=''><?php echo gettext("SOURCE RATECARD");?></option>
                            <?php foreach ($list_tariffname as $recordset) {?>
                                <option class=input value='<?php  echo $recordset[0]?>' <?php if ($recordset[0]==$tariffplan) echo "selected";?>><?php echo $recordset[1]?></option>
                            <?php }?>
                        </select>
                     </td>
                </tr>
                <tr>
                    <td width="30%" valign="middle" class="form_head"><?php echo gettext("RATECARD TO UPDATE");?> :</td>
                      <td width="70%" valign="top" class="tableBodyRight">
                          <select id="ratecard_destination" NAME="ratecard_destination" size="1"  style="width=250" class="form_input_select">
                            <option value=''><?php echo gettext("DESTINATION RATECARD");?></option>
                            <?php foreach ($list_tariffname as $recordset) {?>
                                <option class=input value='<?php  echo $recordset[0]?>' <?php if ($recordset[0]==$tariffplan) echo "selected";?>><?php echo $recordset[1]?></option>
                            <?php }?>
                        </select>
                     </td>
                </tr>

                <tr>
                    <td width="30%" valign="middle" class="form_head"><?php echo gettext("Choose fields to merge");?> :</td>
                    <td width="70%" valign="top" class="tableBodyRight">
                        <input id="search_sources" name="search_sources" value="nochange" type="hidden">
                        <table>
                            <tbody><tr>
                                <td>
                                    <select id="unselected_search_sources" name="unselected_search_sources" multiple="multiple" size="9" width="50" class="form_input_select">
                                        <option value=""><?php echo gettext("Unselected Fields...");?></option>
                                        <option value="destination"><?php echo gettext("destination");?></option>
                                        <option value="buyrate"><?php echo gettext("buyrate");?></option>
                                        <option value="rateinitial"><?php echo gettext("rateinitial");?></option>
                                        <option value="buyrateinitblock"><?php echo gettext("buyrateinitblock");?></option>
                                        <option value="buyrateincrement"><?php echo gettext("buyrateincrement");?></option>
                                        <option value="id_trunk"><?php echo gettext("trunk");?></option>
                                        <option value="initblock"><?php echo gettext("initblock");?></option>
                                        <option value="billingblock"><?php echo gettext("billingblock");?></option>
                                        <option value="connectcharge"><?php echo gettext("connectcharge");?></option>
                                        <option value="disconnectcharge"><?php echo gettext("disconnectcharge");?></option>
                                        <option value="stepchargea"><?php echo gettext("stepchargea");?></option>
                                        <option value="chargea"><?php echo gettext("chargea");?></option>
                                        <option value="timechargea"><?php echo gettext("timechargea");?></option>
                                        <option value="billingblocka"><?php echo gettext("billingblocka");?></option>

                                        <option value="stepchargeb"><?php echo gettext("stepchargeb");?></option>
                                        <option value="chargeb"><?php echo gettext("chargeb");?></option>
                                        <option value="timechargeb"><?php echo gettext("timechargeb");?></option>
                                        <option value="billingblockb"><?php echo gettext("billingblockb");?></option>

                                        <option value="stepchargec"><?php echo gettext("stepchargec");?></option>
                                        <option value="chargec"><?php echo gettext("chargec");?></option>
                                        <option value="timechargec"><?php echo gettext("timechargec");?></option>
                                        <option value="billingblockc"><?php echo gettext("billingblockc");?></option>

                                        <option value="startdate"><?php echo gettext("startdate");?></option>
                                        <option value="stopdate"><?php echo gettext("stopdate");?></option>

                                        <option value="starttime"><?php echo gettext("starttime");?></option>
                                        <option value="endtime"><?php echo gettext("endtime");?></option>
                                        <option value="tag"><?php echo gettext("tag");?></option>
                                        <option value="rounding_calltime"><?php echo gettext("rounding calltime");?></option>
                                        <option value="rounding_threshold"><?php echo gettext("rounding threshold");?></option>
                                         <option value="additional_block_charge"><?php echo gettext("additional block charge");?></option>
                                        <option value="additional_block_charge_time"><?php echo gettext("additional block charge time");?></option>
                                    </select>
                                </td>

                                <td>
                                    <a id="addsource" href="#"><img src="<?php echo Images_Path;?>/forward.png" alt="add source" title="add source" border="0"></a>
                                    <br>
                                    <a id="removesource" href="#"><img src="<?php echo Images_Path;?>/back.png" alt="remove source" title="remove source" border="0"></a>
                                </td>
                                <td>
                                    <select id="selected_search_sources" name="selected_search_sources" multiple="multiple" size="9" width="50" class="form_input_select">
                                        <option value=""><?php echo gettext("Selected Fields...");?></option>
                                    </select>
                                </td>
                            </tr>
                        </tbody></table>
                    </td>
                </tr>
                <tr>
                     <td colspan="2" style="border-bottom: medium dotted rgb(102, 119, 102);">&nbsp; </td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td align="right">
                    <input class="form_input_button" name="submit"  TYPE="submit" VALUE="MERGE"></td>
                </tr>
                <input type="hidden" name="posted" value="1">
              </form>
            </table>
</div>

<?php
    $smarty->display('footer.tpl');
