<?php

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

require_once "../common/lib/customer.defines.php";

if (!$A2B->config["signup"]['enable_signup']) {
    echo ("No Signup page!");
    exit;
}

$HD_Form = new FormHandler();

$HD_Form->init();

//check subscriber
$table_subscriber = new Table("cc_subscription_signup", "*");
$clause_subscriber = "enable = 1";
$result_subscriber = $table_subscriber->get_list(DbConnect(), $clause_subscriber);

// #### HEADER SECTION
$smarty->display('signup_header.tpl');

?>

<br/><br/>
<br/><br/>
<form id="myForm" method="post" name="myForm" action="signup.php">

<?= $HD_Form->csrf_inputs() ?>

<div align="center">
<table  style="width : 80%;" class="editform_table1">
   <tr>
       <th colspan="2" background="templates/default/images/background_cells.gif">
           <?php echo gettext("SELECT THE SERVICE THAT YOU WANT SUBSCRIBE") ?>
       </th>
   </tr>
   <tr height="20px">
        <td  colspan="2">
            &nbsp;
        </td>
    </tr>
    <tr height="20px">
        <td  class="form_head">
            &nbsp;<?php echo gettext("SERVICE") ?> :
        </td>
        <td class="tableBodyRight"  background="templates/default/images/background_cells.gif" width="70%">
            <table>
            <?php
            $i=0;
            foreach ($result_subscriber as $subscriber) {?>
                <tr>
                    <td><input type="radio" name="subscriber_signup" value="<?php echo $subscriber['id']; ?>" <?php if($i==0)echo"checked";?>   >  </td> <td><b> <?php echo $subscriber['label']; ?></b> </td>
                </tr>
                <tr>
                    <td>&nbsp; </td> <td><i> <?php echo $subscriber['description']; ?> </i> </td>
                </tr>
            <?php
            $i++;

            }?>
            </table>
        </td>
    </tr>
     <tr height="20px">
        <td  colspan="2">
            &nbsp;
        </td>
    </tr>
     <tr>
        <td colspan="2" align="right" class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
            <a class="cssbutton_big" onClick="javascript:document.myForm.submit();"  href="#">
                <img src="<?php echo Images_Path_Main;?>/icon_arrow_orange.gif"/>
                <?php echo gettext("SUBSCRIBE THIS SERVICE"); ?>
            </a>
        </td>
    </tr>

</table>
</div>
</form>

<br/><br/>
<br/><br/>

<?php

// #### FOOTER SECTION
$smarty->display('signup_footer.tpl');
