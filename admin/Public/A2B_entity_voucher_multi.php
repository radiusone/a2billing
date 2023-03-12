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

$menu_section = 10;
require_once "../../common/lib/admin.defines.php";
include './form_data/FG_var_voucher.inc';

Admin::checkPageAccess(Admin::ACX_BILLING);

getpost_ifset(array('choose_list', 'addcredit', 'gen_id', 'cardnum', 'choose_currency', 'expirationdate', 'addcredit','tag_list'));

$nbvoucher = $choose_list;

if ($nbvoucher>0) {

        $FG_ADITION_SECOND_ADD_TABLE  = "cc_voucher";
        $FG_ADITION_SECOND_ADD_FIELDS = "voucher, credit, activated, tag, currency, expirationdate";
        $instance_sub_table = new Table($FG_ADITION_SECOND_ADD_TABLE, $FG_ADITION_SECOND_ADD_FIELDS);

        $gen_id = time();
        $_SESSION["IDfilter"]=$tag_list;

        for ($k=0;$k < $nbvoucher;$k++) {
            $vouchernum = generate_unique_value($FG_ADITION_SECOND_ADD_TABLE, LEN_VOUCHER, 'voucher');
            $FG_ADITION_SECOND_ADD_VALUE  = "'$vouchernum', '$addcredit', 't', '$tag_list', '$choose_currency', '$expirationdate'";

            $result_query = $instance_sub_table -> Add_table ($HD_Form -> DBHandle, $FG_ADITION_SECOND_ADD_VALUE, null, null);
        }
}

if (!isset($_SESSION["IDfilter"])) $_SESSION["IDfilter"]='NODEFINED';
$HD_Form -> FG_QUERY_WHERE_CLAUSE = "tag='".$_SESSION["IDfilter"]."'";

$HD_Form -> init();

if ($id!="" || !is_null($id)) {
    $HD_Form -> FG_EDIT_QUERY_CONDITION = str_replace("%id", "$id", $HD_Form -> FG_EDIT_QUERY_CONDITION);
}

if (!isset($form_action))  $form_action="list"; //ask-add
if (!isset($action)) $action = $form_action;

$list = $HD_Form -> perform_action($form_action);

// #### HEADER SECTION
$smarty->display('main.tpl');
// #### HELP SECTION
echo $CC_help_generate_voucher;

?>
<div align="center">
<table align="center" class="bgcolor_001" border="0" width="65%">
<tbody><tr>
<form name="theForm" action="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL) ?>">
    <?= $HD_Form->csrf_inputs() ?>
    <td align="left" width="75%">
        <strong>1)</strong>
        <select name="choose_list" size="1" class="form_input_select">
            <option value=""><?php echo gettext("Choose the number of vouchers to create");?></option>
            <option class="input" value="5"><?php echo gettext("5 Voucher");?></option>
            <option class="input" value="10"><?php echo gettext("10 Vouchers");?></option>
            <option class="input" value="50"><?php echo gettext("50 Vouchers");?></option>
            <option class="input" value="100"><?php echo gettext("100 Vouchers");?></option>
            <option class="input" value="200"><?php echo gettext("200 Vouchers");?></option>
            <option class="input" value="500"><?php echo gettext("500 Vouchers");?></option>
        </select>
        <br/>

        <strong>2)</strong>
        <?php echo gettext("Amount of credit");?> : 	<input class="form_input_text" name="addcredit" size="10" maxlength="10" >
        <br/>

        <strong>3)</strong>
        <select NAME="choose_currency" size="1" class="form_input_select">
        <?php
        foreach (get_currencies() as $key => $cur_value) {
        ?>
        <option value='<?php echo $key ?>'><?php echo $cur_value["name"].' ('.$cur_value["value"].')' ?></option>
        <?php } ?>
        </select>
        <br/>

        <?php
            $begin_date = date("Y");
            $begin_date_plus = date("Y") + 10;
            $end_date = date("-m-d H:i:s");
            $comp_date = "value='".$begin_date.$end_date."'";
            $comp_date_plus = "value='".$begin_date_plus.$end_date."'";
        ?>
        <strong>4)</strong>
        <?php echo gettext("Expiration date");?> : <input class="form_input_text"  name="expirationdate" size="40" maxlength="40" <?php echo $comp_date_plus; ?>> <?php echo gettext("(respect the format YYYY-MM-DD HH:MM:SS)");?>
        <br/>
        <strong>5)</strong>
        <?php echo gettext("Tag");?> : <input class="form_input_text"  name="tag_list" size="40" maxlength="40">
        </td>
        <td align="left" valign="bottom">
            <input class="form_input_button" value=" GENERATE VOUCHER " type="submit">
        </td>
</form>
</tr>
</tbody></table>
<br>
</div>

<?php

$HD_Form -> create_toppage ($form_action);

$HD_Form -> create_form($form_action, $list) ;

$_SESSION[$HD_Form->FG_EXPORT_SESSION_VAR]= "SELECT ". implode(",", $HD_Form -> FG_EXPORT_FIELD_LIST) ." FROM $HD_Form->FG_QUERY_TABLE_NAME";
if (strlen($HD_Form->FG_QUERY_WHERE_CLAUSE)>1) {
    $_SESSION[$HD_Form->FG_EXPORT_SESSION_VAR] .= " WHERE $HD_Form->FG_QUERY_WHERE_CLAUSE ";
}
if (!empty($HD_Form->FG_QUERY_ORDERBY_COLUMNS) && !empty($HD_Form->FG_QUERY_DIRECTION)) {
    $ord = implode(",", $HD_Form->FG_QUERY_ORDERBY_COLUMNS);
    $_SESSION[$HD_Form->FG_EXPORT_SESSION_VAR].= " ORDER BY $ord $HD_Form->FG_QUERY_DIRECTION";
}

// #### FOOTER SECTION
$smarty->display('footer.tpl');
