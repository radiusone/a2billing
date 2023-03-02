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

$menu_section = 6;
require_once "../../common/lib/admin.defines.php";

if (! has_rights (Admin::ACX_PACKAGEOFFER)) {
    Header ("HTTP/1.0 401 Unauthorized");
    Header ("Location: PP_error.php?c=accessdenied");
    die();
}

getpost_ifset(array('id', 'addrate', 'delallrate', 'addbatchrate', 'delrate', 'id_trunk', 'id_tariffplan','tag', 'prefix', 'destination', 'rbDestination', 'rbPrefix'));

if (empty($id)) {
    Header ("Location: A2B_entity_package.php");
}

$table_pack = new Table("cc_package_offer ","*");
$pack_clauses = "id = $id";
$result_pack=$table_pack ->get_list(DbConnect(), $pack_clauses);

if (!is_array($result_pack)|| sizeof($result_pack)!=1) {
    Header ("Location: A2B_entity_package.php");
}

if (isset($addbatchrate) && ($addbatchrate)) {
    $DBHandle = DbConnect();

    $rates_clauses = "";
    $table_rates = new Table("cc_ratecard"," DISTINCT COUNT(destination)");
    if (isset($id_trunk)) {
        $rates_clauses = " id_trunk = '{$id_trunk}'";
    }
    if (isset($id_tariffplan)) {
        $rates_clauses .= (isset($id_trunk)? 'AND':'') . " idtariffplan = '{$id_tariffplan}'";
    }
    if (isset($tag)) {
        $rates_clauses .= (!empty($rates_clauses)? 'AND':'') . " rc.tag = '{$tag}'";
    }
    if (isset($prefix)) {
        $rates_clauses .= (!empty($rates_clauses)? 'AND':'');
        switch ($rbPrefix) {
            case 1 : $rates_clauses .= " destination = '{$prefix}'"; break;
            case 2 : $rates_clauses .= " destination LIKE '{$prefix}%'"; break;
            case 3 : $rates_clauses .= " destination LIKE '%{$prefix}%'"; break;
            case 4 : $rates_clauses .= " destination LIKE '%{$prefix}'"; break;
            case 5 :
                    $arr_prefix = array();
                    if ( strpos($prefix,',') ) {
                        $single = explode(',', $prefix);

                        foreach ($single as $value) {
                            if ( strpos( $value, '-' ) ) {
                                $arr_prefix[] = explode( '-', $value );
                            } else {
                                $arr_prefix[] = $value;
                            }
                        }
                    } elseif ( strpos( $prefix,'-' ) ) {
                        $arr_prefix[] = explode( '-', $prefix );
                    } else {
                        $arr_prefix[] = $prefix;
                    }

                    if ( sizeof($arr_prefix,1) ) {
                        end( $arr_prefix );
                        $last_key = key( $arr_prefix );
                        foreach ($arr_prefix as $key=>$value) {
                            $OPL = ($key == $last_key)? '':' OR ';
                            if ( is_array( $value ) ) {
                                $rates_clauses .= " (destination BETWEEN '$value[0]' AND '$value[1]') $OPL";
                            } else {
                                $rates_clauses .= " destination = '$value' $OPL";
                            }
                        }
                    }
            break;
            default : $rates_clauses .= " destination = '{$prefix}'"; break;
        }
    }
    $QUERY = "SELECT {$id},id FROM cc_ratecard rc WHERE {$rates_clauses} GROUP BY destination";
    $table_rates -> SQLExec( $DBHandle, "INSERT IGNORE INTO cc_package_rate(package_id , rate_id) ({$QUERY})" );
    Header ("Location: A2B_package_manage_rates.php?id=$id");
}

if (isset($addrate) && is_numeric($addrate)) {
    $DBHandle = DbConnect();
    $add_rate_table = new Table("cc_package_rate", "*");
    $fields = " package_id , rate_id";
    $values = " $id , $addrate";
    $add_rate_table->Add_table($DBHandle, $values, $fields);
    Header ("Location: A2B_package_manage_rates.php?id=$id");
}

if (isset($delrate) && is_numeric($delrate)) {
    $DBHandle = DbConnect();
    $del_rate_table = new Table("cc_package_rate", "*");
    $CLAUSE = " package_id = " . $id . " AND rate_id = $delrate";
    $del_rate_table->Delete_table($DBHandle, $CLAUSE);
    Header ("Location: A2B_package_manage_rates.php?id=$id");
}

if (isset($delallrate) && ($delallrate)) {
    $DBHandle = DbConnect();
    $del_rate_table = new Table("cc_package_rate", "*");
    $CLAUSE = " package_id = " . $id;
    $del_rate_table->Delete_table($DBHandle, $CLAUSE);
    Header ("Location: A2B_package_manage_rates.php?id=$id");
}

$smarty->display('main.tpl');

//load rates
$DBHandle = DbConnect();

$table_rates = new Table("cc_package_rate JOIN cc_ratecard ON cc_ratecard.id = cc_package_rate.rate_id LEFT JOIN cc_prefix ON cc_prefix.prefix = cc_ratecard.destination ","DISTINCT cc_ratecard.id,cc_prefix.destination, cc_ratecard.dialprefix");
$rates_clauses = " cc_package_rate.package_id = $id";
$result_rates=$table_rates ->get_list(DbConnect(), $rates_clauses);

echo $CC_help_offer_package;

?>
<br/>

<script>
var win = null;
$("#addrate").on("click", e => win = window.open('A2B_entity_def_ratecard.php?popup_select=1&package=<?= $id ?>','','scrollbars=yes,resizable=yes,width=700,height=500'));
$("#delrate").on("click", function() {
    if ($('#rate').val()) {
        self.location.href= "A2B_package_manage_rates.php?id=<?= $id ?>&delrate=" + parseInt($('#rate').val());
    }
});
$("#delall").on("click", e => self.location.href= "A2B_package_manage_rates.php?id=<?= $id ?>&delallrate=true");
</script>

<TABLE class="invoice_table" >
    <tr class="form_invoice_head">
        <td widht="60%"><font color="#FFFFFF"><?php echo gettext("PACKAGE: "); ?></font><font color="#FFFFFF"><b><?php echo $result_pack[0]['label']; ?></b></font></td>
            <td width="40%"><font color="#FFFFFF"><?php echo gettext("DATE: "); ?> </font><font color="#EE6564"> <?php echo $result_pack[0]['creationdate']; ?></font></td>
    </tr>
    <tr>
            <td colspan="2">
            <?php echo gettext("PACKAGE TYPE"); ?>&nbsp;:&nbsp;<?php $pck_type = getPackagesTypeList(); echo $pck_type[$result_pack[0]['packagetype']][0]; ?>
        </td>
    </tr>
    <tr>
            <td colspan="2">
            <?php echo gettext("NUMBER"); ?>&nbsp;:&nbsp;<?php echo $result_pack[0]['freetimetocall']; ?>&nbsp;<?php $pck_type = getPackagesTypeList(); echo $pck_type[$result_pack[0]['packagetype']][0]; ?>&nbsp;<?php echo gettext('per') ?>
                <?php if($result_pack[0]['billingtype']==0) echo gettext("month"); else echo gettext("week"); ?>
            </td>
    </tr>
    <tr>
        <td align="center" colspan="2">
            <br/>
            <table>
                <tr>
                    <td align="center">
                        <?php echo gettext("RATES ASSIGNED"); ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <select id="rate" name="rate" size="10" style="width:250px;" class="form_input_select">
                            <?php foreach ($result_rates as $rate) { ?>
                            <option value="<?php echo $rate['id'] ?>"  ><?php echo $rate['destination'] ;?>&nbsp;:&nbsp;<?php echo $rate['dialprefix']; ?></option>
                            <?php } ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td align="center">
                        <a id="addrate" href="#"> <img src="../Public/templates/default/images/add.png" alt="<?php echo gettext("Add Rate"); ?>" border="0"></a>
                        <a id="delrate" href="#"> <img src="../Public/templates/default/images/del.png" alt="<?php echo gettext("Del Rate"); ?>" border="0"></a>
                        <a id="delall" href="#"> <img src="../Public/templates/default/images/delete.png" alt="<?php echo gettext("Del All Rate"); ?>" border="0"></a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>

</TABLE>

<?php
// #### FOOTER SECTION
$smarty->display('footer.tpl');
