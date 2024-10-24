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

Admin::checkPageAccess(Admin::ACX_BILLING);

getpost_ifset(array('id'));

if (empty($id)) {
    header("Location: A2B_entity_payment_agent.php");
}

$DBHandle  = DbConnect();

$payment_table = new Table('cc_logpayment_agent', '*');
$payment_clause = "id = ".$id;
$payment_result = $payment_table -> get_list($DBHandle, $payment_clause);
$payment = $payment_result[0];

if (empty($payment)) {
    header("Location: A2B_entity_payment_agent.php");
}

// #### HEADER SECTION
$smarty->display('main.tpl');
?>
<br/>
<br/>
<br/>
<table style="width : 80%;" class="editform_table1">
   <tr>
           <th colspan="2" background="../Public/templates/default/images/background_cells.gif">
               <?php echo gettext("PAYMENT INFO") ?>
           </th>
   </tr>
   <tr height="20px">
        <td  class="form_head">
            <?php echo gettext("AGENT") ?> :
        </td>
        <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
            <?php
            if (has_rights (Admin::ACX_ADMINISTRATOR)) {
                echo get_linktoagent($payment['agent_id']);
            } else {
                echo get_nameofagent($payment['agent_id']);
            }
            ?>
        </td>
   </tr>
   <tr height="20px">
        <td  class="form_head">
            <?php echo gettext("AMOUNT") ?> :
        </td>
        <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
            <?php echo $payment['payment']." ".strtoupper(BASE_CURRENCY);?>
        </td>
   </tr>
       <tr height="20px">
        <td  class="form_head">
            <?php echo gettext("CREATION DATE") ?> :
        </td>
        <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
            <?php echo $payment['date']?>
        </td>
    </tr>
   <tr height="20px">
        <td  class="form_head">
            <?php echo gettext("PAYMENT TYPE") ?> :
        </td>
        <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
            <?php
            $list_type = getRefillType_List();
            echo $list_type[$payment['payment_type']][0];?>
        </td>
   </tr>
   <tr height="20px">
        <td  class="form_head">
            <?php echo gettext("DESCRIPTION ") ?> :
        </td>
        <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
            <?php echo $payment['description']?>
        </td>
    </tr>
       <?php if (!empty($payment['id_logrefill'])) { ?>
       <tr height="20px">
        <td  class="form_head">
            <?php echo gettext("LINK REFILL") ?> :
        </td>
        <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
            <a href="A2B_refill_info_agent.php?id=<?php echo $payment['id_logrefill']?>"> <img src="<?php echo Images_Path."/link.png"?>" border="0" title="<?php echo gettext("Link to the refill")?>" alt="<?php echo  gettext("Link to the refill")?>"></a>
        </td>
    </tr>
       <?php } ?>

 </table>
 <br/>
<div style="width : 80%; text-align : right; margin-left:auto;margin-right:auto;" >
     <a class="cssbutton_big"  href="A2B_entity_payment_agent.php">
        <img src="<?php echo Images_Path_Main;?>/icon_arrow_orange.gif"/>
        <?php echo gettext("PAYMENTS AGENT LIST"); ?>
    </a>
</div>
<?php

$smarty->display( 'footer.tpl');
