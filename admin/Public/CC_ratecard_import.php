<?php

use A2billing\Table;

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

set_time_limit(0);

if (!has_rights(ACX_RATECARD)) {
	Header("HTTP/1.0 401 Unauthorized");
	Header("Location: PP_error.php?c=accessdenied");
	die();
}

$FG_DEBUG = 0;
$DBHandle = DbConnect();
$my_max_file_size = (int) MY_MAX_FILE_SIZE_IMPORT;

// GET CALLPLAN LIST
$instance_table_tariffname = new Table("cc_tariffplan", "id, tariffname");
$FG_TABLE_CLAUSE = "";
$list_tariffname = $instance_table_tariffname->get_list($DBHandle, $FG_TABLE_CLAUSE, "id", "DESC");
$nb_tariffname = count($list_tariffname);

// GET TRUNK LIST
$instance_table_trunk = new Table("cc_trunk", "id_trunk, trunkcode");
$FG_TABLE_CLAUSE = "";
$list_trunk = $instance_table_trunk->get_list($DBHandle, $FG_TABLE_CLAUSE, "id_trunk");
$nb_trunk = count($list_trunk);

$smarty->display('main.tpl');

echo $CC_help_import_ratecard;

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

    $("input#sendtoupload").on('click', function() {
        var file = $("#the_file");
        if (file.value().length < 2) {
            alert (<?= json_encode(gettext("Please, you must first select a file !")); ?>);
            file.focus();
            return false;
        }
        var tp = $("#tariffplan");
        if (tp.value().length < 1) {
            alert (<?= json_encode(gettext("Please, you must first select a ratecard !")); ?>);
            tp.focus();
            return false;
        }
        $("#task").val("upload");
        $("#prefs").submit()
        return true;
    }

    $("#selected_search_sources, #unselected_search_sources").on('change', function() {
        $("#selected_search_sources option:first, #unselected_search_sources option:first").prop("selected", false);
    });

    $("#movesourceup").on("click", function () {
        var select = $("#selected_search_sources");
        var options = select.children("option");
        var selectedOption = options.filter(":selected").first();
        var prev = selectedOption.prev("option");

        if (selectedOption.length && options.length >= 2) {
            if (prev.length) {
                selectedOption.insertBefore(prev);
            } else {
                selectedOption.appendTo(select);
            }
            resetHidden();
        }
    });

    $("#movesourcedown").on("click", function () {
        var select = $("#selected_search_sources");
        var options = select.children("option");
        var selectedOption = options.filter(":selected").first();
        var next = selectedOption.next("option");

        if (selectedOption.length && options.length >= 2) {
            if (next.length) {
                selectedOption.insertAfter(next);
            } else {
                selectedOption.prependTo(select);
            }
            resetHidden();
        }
    });

    function resetHidden() {
        var tmp = [];
        $("#selected_search_sources option").each(() => tmp.push(this.value));
        $("#search_sources").val(tmp.join("\t"));
    }
});
</script>

<center>
		<b><?php echo gettext("New rate cards have to be imported from a CSV file.");?>.</b><br><br>
		<table width="95%" border="0" cellspacing="2" align="center" class="records">
			  <form id="prefs" name="prefs" enctype="multipart/form-data" action="CC_ratecard_import_analyse.php" method="post">
				<tr> 
                  <td colspan="2" align=center> 
				  <?php echo gettext("Choose the ratecard to import");?> :
				  <select id="tariffplan" NAME="tariffplan" size="1"  style="width=250" class="form_input_select">
								<option value=''><?php echo gettext("Choose a ratecard");?></option>
								<?php					 
								 foreach ($list_tariffname as $recordset){ 						 
								?>
									<option class=input value='<?php  echo $recordset[0]?>-:-<?php  echo $recordset[1]?>' <?php if ($recordset[0]==$tariffplan) echo "selected";?>><?php echo $recordset[1]?></option>                        
								<?php 	 }
								?>
						</select>	
						<br><br>
				   <?php echo gettext("Choose the trunk to use");?> :
						  <select id="trunk" NAME="trunk" size="1"  style="width=250" class="form_input_select">
						  		<OPTION  value="-1" selected><?php echo gettext("NOT DEFINED");?></OPTION>
								<?php					 
								 foreach ($list_trunk as $recordset){
								?>
									<option class=input value='<?php  echo $recordset[0]?>-:-<?php  echo $recordset[1]?>' <?php if ($recordset[0]==$trunk) echo "selected";?>><?php echo $recordset[1]?></option>                        
								<?php 	 }
								?>
						</select>
                      <br/><br>
				  		  
					<?php echo gettext("These fields are mandatory");?><br>

					<select id="bydefault" name="bydefault" multiple="multiple" size="4" width="40" class="form_input_select">
						<option value="bb1"><?php echo gettext("dialprefix");?></option>
						<option value="bb2"><?php echo gettext("destination");?></option>
						<option value="bb3"><?php echo gettext("selling rate");?></option>
					</select>
					<br/><br/>
					
					<?php echo gettext("Choose the additional fields to import from the CSV file");?>.<br>
					
					<input name="search_sources" value="nochange" type="hidden">
					<table>
					    <tbody><tr>
					        <td>
					            <select id="unselected_search_sources" name="unselected_search_sources" multiple="multiple" size="9" width="50" class="form_input_select">
									<option value=""><?php echo gettext("Unselected Fields...");?></option>
									<option value="buyrate"><?php echo gettext("buyrate");?></option>
									<option value="buyrateinitblock"><?php echo gettext("buyrate min duration");?></option>
									<option value="buyrateincrement"><?php echo gettext("buyrate billing block");?></option>
					
									<option value="initblock"><?php echo gettext("sellrate min duration");?></option>
									<option value="billingblock"><?php echo gettext("sellrate billing block");?></option>
									
									<option value="connectcharge"><?php echo gettext("connect charge");?></option>
									<option value="disconnectcharge"><?php echo gettext("disconnect charge");?></option>
									<option value="disconnectcharge_after"><?php echo gettext("disconnect charge threshold");?></option>
									
									<option value="minimal_cost"><?php echo gettext("minimum call cost");?></option> 
									
									<option value="stepchargea"><?php echo gettext("step charge a");?></option>
									<option value="chargea"><?php echo gettext("charge a");?></option>
									<option value="timechargea"><?php echo gettext("time charge a");?></option>
									<option value="billingblocka"><?php echo gettext("billing block a");?></option>
					
									<option value="stepchargeb"><?php echo gettext("step charge b");?></option>
									<option value="chargeb"><?php echo gettext("charge b");?></option>
									<option value="timechargeb"><?php echo gettext("time charge b");?></option>
									<option value="billingblockb"><?php echo gettext("billing block b");?></option>
					
									<option value="stepchargec"><?php echo gettext("step charge c");?></option>
									<option value="chargec"><?php echo gettext("charge c");?></option>
									<option value="timechargec"><?php echo gettext("time charge c");?></option>
									<option value="billingblockc"><?php echo gettext("billing block c");?></option>
					
									<option value="startdate"><?php echo gettext("start date");?></option>
									<option value="stopdate"><?php echo gettext("stop date");?></option>
									<option value="additional_grace"><?php echo gettext("additional grace");?></option>
									<option value="starttime"><?php echo gettext("start time");?></option>
									<option value="endtime"><?php echo gettext("end time");?></option>
									<option value="tag"><?php echo gettext("tag");?></option>
									<option value="rounding_calltime"><?php echo gettext("rounding calltime");?></option>
									<option value="rounding_threshold"><?php echo gettext("rounding threshold");?></option>
					 				<option value="additional_block_charge"><?php echo gettext("additional block charge");?></option>
									<option value="additional_block_charge_time"><?php echo gettext("additional block charge time");?></option>
									<option value="announce_time_correction"><?php echo gettext("announce time correction");?></option>
									
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
					
					        <td>
					            <a id="movesourceup" href="#"><img src="<?php echo Images_Path;?>/up_black.png" alt="move up" title="move up" border="0"></a>
					            <br>
					            <a id="movesourcedown" href="#"><img src="<?php echo Images_Path;?>/down_black.png" alt="move down" title="move down" border="0"></a>
					        </td>
					    </tr>
					</tbody></table>
		
				
				</td></tr>
				
				<tr>
				<td colspan="2" align="center">
				<?php echo gettext("Currency import as")?>&nbsp;: <input type="radio" name="currencytype" checked value="unit" > <?php echo gettext("Unit")?>&nbsp;&nbsp;
				<input type="radio" name="currencytype" value="cent"> <?php echo gettext("Cents")?>&nbsp;
				</td>
				</tr>
				<tr>
				<td colspan="2" align="center">&nbsp;
				
				</td>
				</tr>
                <tr> 
                  <td colspan="2"> 
                    <div align="center"><span class="textcomment"> 
                      

					  <?php echo gettext("Use the example below  to format the CSV file. Fields are separated by [,] or [;]");?><br/>
					  <?php echo gettext("(dot) . is used for decimal format.");?><br/>
					  <?php echo gettext("Note that Dial-codes expressed in REGEX format cannot be imported, and must be entered manually via the Add Rate page.");?>


					  <br/>
					  <a href="importsamples.php?sample=RateCard_Complex" target="superframe"><?php echo gettext("Complex Sample");?></a> -
					  <a href="importsamples.php?sample=RateCard_Simple" target="superframe"> <?php echo gettext("Simple Sample");?></a>
                      </span></div>


						<center>
							<iframe name="superframe" src="importsamples.php?sample=RateCard_Simple" BGCOLOR=white	width=600 height=80 marginWidth=10 marginHeight=10  frameBorder=1  scrolling=yes>

							</iframe>
                            </font>
						</center>
					  
                  </td>
                </tr>
                <tr> 
                  <td colspan="2"> 
                    <p align="center"><span class="textcomment"> 
                      <?php echo gettext("The maximum file size is ");?>
                      <?php echo $my_max_file_size / 1024?>
                      KB </span><br>
                      <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $my_max_file_size?>">
                      <input type="hidden" id="task" name="task" value="upload">
                      <input id="the_file" name="the_file" type="file" size="50" class="saisie1">
					  <input id="sendtoupload" type="button" value="Import RateCard" class="form_input_button" name="submit1">
					   </p>     
                  </td>
                </tr>
               
               
              </form>
            </table>
</center>

<?php


$smarty->display('footer.tpl');
