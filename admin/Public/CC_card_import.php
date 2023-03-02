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

$menu_section = 1;
// Common includes
require_once "../../common/lib/admin.defines.php";

set_time_limit(0);

if (! has_rights (Admin::ACX_CUSTOMER)) {
    Header ("HTTP/1.0 401 Unauthorized");
    Header ("Location: PP_error.php?c=accessdenied");
    die();
}

$FG_DEBUG = 0;
$DBHandle  = DbConnect();
$my_max_file_size = (int) MY_MAX_FILE_SIZE_IMPORT;

$smarty->display('main.tpl');

echo $CC_help_import_customer;

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
        $("#task").val("upload");
        $("#prefs").attr("action", "CC_card_import_analyse.php").submit()
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
    <b><?php echo gettext("New Cards have to be imported from a CSV file.");?>.</b><br><br>
    <form id="prefs" name="prefs" enctype="multipart/form-data"  method="post">
    <table width="95%" border="0" cellspacing="2" align="center" class="records">

        <tr>
        <td colspan="2" align=center>
        <?php echo gettext("These fields are mandatory");?><br>

        <select id="bydefault" name="bydefault" multiple="multiple" size="4" width="40" class="form_input_select">
            <option value="bb1"><?php echo gettext("username");?></option>
            <option value="bb2"><?php echo gettext("useralias");?></option>
            <option value="bb3"><?php echo gettext("uipass");?></option>
            <option value="bb4"><?php echo gettext("credit");?></option>
            <option value="bb5"><?php echo gettext("lastname");?></option>
            <option value="bb6"><?php echo gettext("firstname");?></option>
            <option value="bb7"><?php echo gettext("activated");?></option>
            <option value="bb8"><?php echo gettext("status");?></option>
        </select>
        <br/><br/>

        <?php echo gettext("Choose the additional fields to import from the CSV file");?>.<br>

        <input id="search_sources" name="search_sources" value="nochange" type="hidden">
        <table>
            <tr>
            <td>
                <select id="unselected_search_sources" name="unselected_search_sources" multiple="multiple" size="9" width="50" class="form_input_select">
                    <option value=""><?php echo gettext("Unselected Fields...");?></option>
                    <option value="creationdate"><?php echo gettext("creationdate");?></option>
                    <option value="firstusedate"><?php echo gettext("firstusedate");?></option>
                    <option value="expirationdate"><?php echo gettext("expirationdate");?></option>
                    <option value="enableexpire"><?php echo gettext("enableexpire");?></option>
                    <option value="expiredays"><?php echo gettext("expiredays");?></option>
                    <option value="tariff"><?php echo gettext("tariff");?></option>
                    <option value="id_didgroup"><?php echo gettext("id_didgroup");?></option>
                    <option value="id_group"><?php echo gettext("id_group");?></option>
                    <option value="address"><?php echo gettext("address");?></option>

                    <option value="city"><?php echo gettext("city");?></option>
                    <option value="state"><?php echo gettext("state");?></option>
                    <option value="country"><?php echo gettext("country");?></option>
                    <option value="zipcode"><?php echo gettext("zipcode");?></option>
                    <option value="phone"><?php echo gettext("phone");?></option>
                    <option value="email"><?php echo gettext("email");?></option>
                    <option value="fax"><?php echo gettext("fax");?></option>
                    <option value="inuse"><?php echo gettext("inuse");?></option>
                    <option value="simultaccess"><?php echo gettext("simultaccess");?></option>

                    <option value="currency"><?php echo gettext("currency");?></option>
                    <option value="lastuse"><?php echo gettext("lastuse");?></option>
                    <option value="nbused"><?php echo gettext("nbused");?></option>
                    <option value="typepaid"><?php echo gettext("typepaid");?></option>
                    <option value="creditlimit"><?php echo gettext("creditlimit");?></option>
                    <option value="voipcall"><?php echo gettext("voipcall");?></option>
                    <option value="sip_buddy"><?php echo gettext("sip_buddy");?></option>
                    <option value="iax_buddy"><?php echo gettext("iax_buddy");?></option>
                    <option value="language"><?php echo gettext("language");?></option>
                    <option value="redial"><?php echo gettext("redial");?></option>

                    <option value="nbservice"><?php echo gettext("nbservice");?></option>
                    <option value="id_campaign"><?php echo gettext("id_campaign");?></option>
                    <option value="num_trials_done"><?php echo gettext("num_trials_done");?></option>
                    <option value="callback"><?php echo gettext("callback");?></option>
                    <option value="vat"><?php echo gettext("vat");?></option>
                    <option value="servicelastrun"><?php echo gettext("servicelastrun");?></option>
                    <option value="initialbalance"><?php echo gettext("initialbalance");?></option>
                    <option value="invoiceday"><?php echo gettext("invoiceday");?></option>
                    <option value="autorefill"><?php echo gettext("autorefill");?></option>
                    <option value="loginkey"><?php echo gettext("loginkey");?></option>
                </select>
            </td>

            <td>
            <a href="#" id="addsource"><img src="<?php echo Images_Path;?>/forward.png" alt="add source" title="add source" border="0"></a>
            <br>
            <a href="#" id="removesource"><img src="<?php echo Images_Path;?>/back.png" alt="remove source" title="remove source" border="0"></a>
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
        </table>

                </td></tr>

                <tr>
                  <td colspan="2">
                    <div align="center"><span class="textcomment">

                      <?php echo gettext("Use the example below  to format the CSV file. Fields are separated by [,] or [;]");?><br/>
                      <?php echo gettext("(dot) . is used for decimal format.");?>
                      <br/>
                      <a href="importsamples.php?sample=Card_Complex" target="superframe"><?php echo gettext("Complex Sample");?></a> -
                      <a href="importsamples.php?sample=Card_Simple" target="superframe"> <?php echo gettext("Simple Sample");?></a>
                      </span></div>

                        <center>
                            <iframe name="superframe" src="importsamples.php?sample=Card_Simple" BGCOLOR=white	width=600 height=80 marginWidth=10 marginHeight=10  frameBorder=1  scrolling=yes>

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
                      <input type="hidden" id="task" name="task" value="upload">
                      <input id="the_file" name="the_file" type="file" size="50" class="saisie1">
                      <input type="button" id="sendtoupload" value="<?php echo gettext("Import Customer's");?>" class="form_input_button" name="submit1">

                      <br>
                      &nbsp; </p>
                  </td>
                </tr>

            </table>
    </form>
</center>

<?php

$smarty->display('footer.tpl');
