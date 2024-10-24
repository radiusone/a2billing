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

$menu_section = 16;
require_once "../../common/lib/admin.defines.php";
include './form_data/FG_var_log_viewer.inc';

Admin::checkPageAccess(Admin::ACX_MAINTENANCE);

$HD_Form -> init();

if (!isset($form_action))  $form_action="list"; //ask-add
if (!isset($action)) $action = $form_action;

$list = $HD_Form -> perform_action($form_action);

// #### HEADER SECTION
$smarty->display('main.tpl');

// #### HELP SECTION
echo $CC_help_list_log;

// #### TOP SECTION PAGE
$HD_Form -> create_toppage ($form_action);

if ($form_action=="list") {
?>

<FORM METHOD=POST name="myForm" ACTION="?order=<?php echo $order?>&sens=<?php echo $sens?>&current_page=<?php echo $current_page?>">
    <INPUT TYPE="hidden" NAME="posted" value=1>
    <INPUT TYPE="hidden" NAME="current_page" value=0>
    <?= $HD_Form->csrf_inputs() ?>
    <table class="bar-status" width="85%" border="0" cellspacing="1" cellpadding="2" align="center">
        <tbody>
        <tr>
            <td align="left" valign="top" class="bgcolor_004">
                <font class="fontstyle_003">&nbsp;&nbsp;<?php echo gettext("CUSTOMERS");?></font>
            </td>
            <td class="bgcolor_005" align="left">
            <table width="100%" border="0" cellspacing="0" cellpadding="0"><tr>
                <td class="fontstyle_searchoptions">
                    <?php echo gettext("ADMIN USER");?>: <INPUT TYPE="text" NAME="enteradmin" value="<?php echo $enteradmin?>" class="form_input_text">
                    <a href="A2B_entity_user.php" class="badge bg-primary popup_trigger" aria-label="open a popup to select an item">&gt;</a>
                </td>
            </tr></table></td>
        </tr>
        <tr>
            <td class="bgcolor_002" align="left">

                <input type="radio" name="Period" value="Month" <?php  if (($Period=="Month") || !isset($Period)) { ?>checked="checked" <?php  } ?>>
                <font class="fontstyle_003"><?php echo gettext("SELECT MONTH");?></font>
            </td>
              <td class="bgcolor_003" align="left">
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr><td class="fontstyle_searchoptions">
                  <input type="checkbox" name="frommonth" value="true" <?php  if ($frommonth) { ?>checked<?php }?>>
                <?php echo gettext("From");?> : <select name="fromstatsmonth" class="form_input_select">
                <?php
                    $monthname = array( gettext("January"), gettext("February"),gettext("March"), gettext("April"), gettext("May"), gettext("June"), gettext("July"), gettext("August"), gettext("September"), gettext("October"), gettext("November"), gettext("December"));
                    $year_actual = date("Y");
                    for ($i=$year_actual;$i >= $year_actual-1;$i--) {
                       if ($year_actual==$i) {
                        $monthnumber = date("n")-1; // Month number without lead 0.
                       } else {
                        $monthnumber=11;
                       }
                       for ($j=$monthnumber;$j>=0;$j--) {
                        $month_formated = sprintf("%02d",$j+1);
                           if ($fromstatsmonth=="$i-$month_formated")	$selected="selected";
                        else $selected="";
                        echo "<OPTION value=\"$i-$month_formated\" $selected> $monthname[$j]-$i </option>";
                       }
                    }
                ?>
                </select>
                </td><td  class="fontstyle_searchoptions">&nbsp;&nbsp;
                <input type="checkbox" name="tomonth" value="true" <?php  if ($tomonth) { ?>checked<?php }?>>
                <?php echo gettext("To");?> : <select name="tostatsmonth" class="form_input_select">
                <?php 	$year_actual = date("Y");
                    for ($i=$year_actual;$i >= $year_actual-1;$i--) {
                       if ($year_actual==$i) {
                        $monthnumber = date("n")-1; // Month number without lead 0.
                       } else {
                        $monthnumber=11;
                       }
                       for ($j=$monthnumber;$j>=0;$j--) {
                        $month_formated = sprintf("%02d",$j+1);
                           if ($tostatsmonth=="$i-$month_formated") $selected="selected";
                        else $selected="";
                        echo "<OPTION value=\"$i-$month_formated\" $selected> $monthname[$j]-$i </option>";
                       }
                    }
                ?>
                </select>
                </td></tr></table>
              </td>
        </tr>

        <tr>
            <td align="left" class="bgcolor_004">
                <input type="radio" name="Period" value="Day" <?php  if ($Period=="Day") { ?>checked="checked" <?php  } ?>>
                <font class="fontstyle_003"><?php echo gettext("SELECT DAY");?></font>
            </td>
              <td align="left" class="bgcolor_005">
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr><td class="fontstyle_searchoptions">
                  <input type="checkbox" name="fromday" value="true" <?php  if ($fromday) { ?>checked<?php }?>> <?php echo gettext("From");?> :
                <select name="fromstatsday_sday" class="form_input_select">
                    <?php
                    for ($i=1;$i<=31;$i++) {
                        if ($fromstatsday_sday==sprintf("%02d",$i)) $selected="selected";
                        else	$selected="";
                        echo '<option value="'.sprintf("%02d",$i)."\"$selected>".sprintf("%02d",$i).'</option>';
                    }
                    ?>
                </select>
                 <select name="fromstatsmonth_sday" class="form_input_select">
                <?php 	$year_actual = date("Y");
                    for ($i=$year_actual;$i >= $year_actual-1;$i--) {
                        if ($year_actual==$i) {
                            $monthnumber = date("n")-1; // Month number without lead 0.
                        } else {
                            $monthnumber=11;
                        }
                        for ($j=$monthnumber;$j>=0;$j--) {
                            $month_formated = sprintf("%02d",$j+1);
                            if ($fromstatsmonth_sday=="$i-$month_formated") $selected="selected";
                            else $selected="";
                            echo "<OPTION value=\"$i-$month_formated\" $selected> $monthname[$j]-$i </option>";
                        }
                    }
                ?>
                </select>
                </td><td class="fontstyle_searchoptions">&nbsp;&nbsp;
                <input type="checkbox" name="today" value="true" <?php  if ($today) { ?>checked<?php }?>>
                <?php echo gettext("To");?>  :
                <select name="tostatsday_sday" class="form_input_select">
                <?php
                    for ($i=1;$i<=31;$i++) {
                        if ($tostatsday_sday==sprintf("%02d",$i)) {$selected="selected";} else {$selected="";}
                        echo '<option value="'.sprintf("%02d",$i)."\"$selected>".sprintf("%02d",$i).'</option>';
                    }
                ?>
                </select>
                 <select name="tostatsmonth_sday" class="form_input_select">
                <?php 	$year_actual = date("Y");
                    for ($i=$year_actual;$i >= $year_actual-1;$i--) {
                        if ($year_actual==$i) {
                            $monthnumber = date("n")-1; // Month number without lead 0.
                        } else {
                            $monthnumber=11;
                        }
                        for ($j=$monthnumber;$j>=0;$j--) {
                            $month_formated = sprintf("%02d",$j+1);
                               if ($tostatsmonth_sday=="$i-$month_formated") $selected="selected";
                            else	$selected="";
                            echo "<OPTION value=\"$i-$month_formated\" $selected> $monthname[$j]-$i </option>";
                        }
                    }
                ?>
                </select>
                </td></tr></table>
              </td>
        </tr>
        <tr>
            <td class="bgcolor_002" align="left">
                <font class="fontstyle_003">&nbsp;&nbsp;<?php echo gettext("LOG LEVEL");?></font>
            </td>
            <td class="bgcolor_003" align="left">
            <select name="loglevel" style="width:90px;">
            <option value="0" <?php if ($loglevel == 0) echo "selected"?>>ALL</option>
            <option value="1" <?php if ($loglevel == 1) echo "selected"?>>Level-1</option>
            <option value="2" <?php if ($loglevel == 2) echo "selected"?>>Level-2</option>
            <option value="3" <?php if ($loglevel == 3) echo "selected"?>>Level-3</option>
            </select>
            </td>
        </tr>
        <tr>
            <td class="bgcolor_004" align="left" > </td>

            <td class="bgcolor_005" align="center" >
                <input type="image"  name="image16" align="top" border="0" src="<?php echo Images_Path;?>/button-search.gif" />

              </td>
        </tr>
    </tbody></table>
</FORM>

<?php
}
$HD_Form -> create_form($form_action, $list) ;

// #### FOOTER SECTION
$smarty->display('footer.tpl');
