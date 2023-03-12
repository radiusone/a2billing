<?php

use A2billing\Admin;
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

$menu_section = 8;
// Common includes
require_once "../../common/lib/admin.defines.php";

set_time_limit(0);

Admin::checkPageAccess(Admin::ACX_DID);

$FG_DEBUG = 0;
$DBHandle  = DbConnect();
$my_max_file_size = (int) MY_MAX_FILE_SIZE_IMPORT;

$instance_table_tariffname = new Table("cc_didgroup", "id, didgroupname");
$FG_TABLE_CLAUSE = "";
$list_tariffname = $instance_table_tariffname  -> get_list ($DBHandle, $FG_TABLE_CLAUSE, ["didgroupname"]);
$nb_tariffname = count($list_tariffname);
$instance_table_country = new Table("cc_country", "id, countryname");
$list_countryname = $instance_table_country  -> get_list ($DBHandle, $FG_TABLE_CLAUSE, ["countryname"]);
$nb_countryname = count($list_countryname);

$smarty->display('main.tpl');

// #### HELP SECTION
echo $CC_help_import_did;

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
<b><?php echo gettext("New DID have to be imported from a CSV file.");?>.</b><br><br>
<form name="prefs" enctype="multipart/form-data" action="A2B_entity_did_import_analyse.php" method="post">
<table width="95%" border="0" cellspacing="2" align="center" class="records">

    <tr>
        <td colspan="2" align=center>
        <?php echo gettext("Choose a DIDGroup to use");?> :
        <select id="didgroup" NAME="didgroup" size="1"  class="form_input_select"  style="width=250">
            <option value=''><?php echo gettext("Choose a DIDGroup");?></option>

            <?php
            foreach ($list_tariffname as $recordset) {
            ?>
                <option class=input value='<?php  echo $recordset[0]?>-:-<?php  echo $recordset[1]?>' <?php if ($recordset[0]==$didgroup) echo "selected";?>><?php echo $recordset[1]?></option>
            <?php
            }
            ?>
        </select>
        <br>
        <br>
        <?php echo gettext("Choose a Country to use");?> :
        <select id="countryID" NAME="countryID" size="1" class="form_input_select" style="width=250">
            <option value=''><?php echo gettext("Choose a Country");?></option>

            <?php
            foreach ($list_countryname as $recordset) {
            ?>
                <option class=input value='<?php  echo $recordset[0]?>-:-<?php  echo $recordset[1]?>' <?php if ($recordset[0]== $countryID) echo "selected";?>><?php echo $recordset[1]?></option>
            <?php
                }
            ?>
        </select>
        <br><br>

    <?php echo gettext("These fields are mandatory");?><br>

<select id="bydefault" name="bydefault" multiple="multiple" size="2" class="form_input_select" width="40">
    <option value="bb1"><?php echo gettext("DID");?></option>
    <option value="bb2"><?php echo gettext("FIXRATE");?></option>
</select>
<br/><br/>

<?php echo gettext("Choose the additional fields to import from the CSV file");?>.<br>

<input id="search_sources" name="search_sources" value="nochange" type="hidden">
<table>
    <tbody><tr>
        <td>
            <select id="unselected_search_sources" name="unselected_search_sources" multiple="multiple" size="5" class="form_input_select" width="50">
                <option value=""><?php echo gettext("Unselected Fields...");?></option>
                <option value="activated"><?php echo gettext("activated");?></option>
                <option value="startingdate"><?php echo gettext("startingdate");?></option>
                <option value="expirationdate"><?php echo gettext("expirationdate");?></option>
                <option value="billingtype"><?php echo gettext("billingtype");?></option>
            </select>
        </td>

        <td>
            <a id="addsource" href="#"><img src="<?php echo Images_Path;?>/forward.png" alt="add source" title="add source" border="0"></a>
            <br>
            <a id="removesource" href="#"><img src="<?php echo Images_Path;?>/back.png" alt="remove source" title="remove source" border="0"></a>
        </td>
        <td>
            <select id="selected_search_sources" name="selected_search_sources" multiple="multiple" size="5" class="form_input_select" width="50">
                <option value=""><?php echo gettext("Selected Fields...");?></option>
            </select>
        </td>

        <td>
            <a id="movesourceup" href="#"><img src="<?php echo Images_Path;?>/up_black.png" alt="move up" title="move up" border="0"></a>
            <br>
            <a id="movesourcedown" href="#"><img src="<?php echo Images_Path;?>/down_black.png" alt="move down" title="move down" border="0"></a>
        </td>
    </tr>
</tbody>
</table>

    </td></tr>

    <tr>
      <td colspan="2">
        <div align="center"><span class="textcomment">

            <?php echo gettext("Use the example below  to format the CSV file. Fields are separated by [,] or [;]");?><br/>
            <?php echo gettext("(dot) . is used for decimal format.");?>
            <br/>
            <a href="importsamples.php?sample=did_Complex" target="superframe"><?php echo gettext("Complex Sample");?></a> -
            <a href="importsamples.php?sample=did_Simple" target="superframe"> <?php echo gettext("Simple Sample");?></a>
            </span></div>

            <center>
            <iframe name="superframe" src="importsamples.php?sample=did_Simple" BGCOLOR=white	width=600 height=80 marginWidth=10 marginHeight=10  frameBorder=1  scrolling=yes>

            </iframe>
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
            <input type="hidden" name="task" value="upload">
            <input id="the_file" name="the_file" type="file" size="50" onFocus=this.select() class="saisie1">
            <input type="submit"  value="Import DID" onFocus=this.select() class="form_input_button" name="submit1" id="sendtoupload">

            </p>
      </td>
    </tr>

</table>
</form>
</center>

<?php

$smarty->display('footer.tpl');
