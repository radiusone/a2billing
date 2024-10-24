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

$menu_section = 2;
require_once "../../common/lib/admin.defines.php";

Admin::checkPageAccess(Admin::ACX_ADMINISTRATOR);

getpost_ifset(array('id'));

if (empty($id)) {
    header("Location: A2B_entity_agent.php");
}

$DBHandle  = DbConnect();

$agent = $DBHandle->GetRow("SELECT * FROM cc_agent WHERE id = ?", [$id]);

if (empty($agent)) {
    header("Location: A2B_entity_agent.php");
}

// #### HEADER SECTION
$smarty->display('main.tpl');
$lg_liste= getLanguages();
?>
<br/>
<br/>
<br/>
<table style="width : 80%;" class="editform_table1">
   <tr>
           <th colspan="2" background="../Public/templates/default/images/background_cells.gif">
               <?php echo gettext("AGENT INFO") ?>
           </th>
   </tr>
   <tr height="20px">
        <td  class="form_head">
            <?php echo gettext("LOGIN") ?> :
        </td>
        <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
            &nbsp;<?php echo $agent['login']?>
        </td>
    </tr>
    <tr height="20px">
        <td  class="form_head">
            <?php echo gettext("PASSWORD") ?> :
        </td>
        <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
            &nbsp;<?php echo $agent['passwd']?>
        </td>
    </tr>
    <tr height="20px">
        <td  class="form_head">
            <?php echo gettext("LAST NAME") ?> :
        </td>
        <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
            &nbsp;<?php echo $agent['lastname']?>
        </td>
    </tr>
    <tr height="20px">
        <td  class="form_head">
            <?php echo gettext("FIRST NAME") ?> :
        </td>
        <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
            &nbsp;<?php echo $agent['firstname']?>
        </td>

    </tr>

    <tr height="20px">
        <td  class="form_head">
            <?php echo gettext("ADDRESS") ?> :
        </td>
        <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
            &nbsp;<?php echo $agent['address']?>
        </td>

    </tr>

    <tr height="20px">
        <td  class="form_head">
            <?php echo gettext("ZIP CODE") ?> :
        </td>
        <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
            &nbsp;<?php echo $agent['zipcode']?>
        </td>
    </tr>

    <tr  height="20px">
        <td  class="form_head">
            <?php echo gettext("CITY") ?> :
        </td>
        <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
            &nbsp;<?php echo $agent['city']?>
        </td>

    </tr>

    <tr  height="20px">
        <td  class="form_head">
            <?php echo gettext("STATE") ?> :
        </td>
        <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
            &nbsp;<?php echo $agent['state']?>
        </td>

    </tr>

    <tr  height="20px">
        <td  class="form_head">
            <?php echo gettext("COUNTRY") ?> :
        </td>
        <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
            &nbsp;<?php echo $agent['country']?>
        </td>

    </tr>
    <tr  height="20px">
        <td  class="form_head">
            <?php echo gettext("EMAIL") ?> :
        </td>
        <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
            &nbsp;<?php echo $agent['email']?>
        </td>

    </tr>
    <tr  height="20px">
        <td  class="form_head">
            <?php echo gettext("PHONE") ?> :
        </td>
        <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
            &nbsp;<?php echo $agent['phone']?>
        </td>
    </tr>
    <tr  height="20px">
        <td  class="form_head">
            <?php echo gettext("FAX") ?> :
        </td>
        <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
            &nbsp;<?php echo $agent['fax']?>
        </td>
    </tr>
    <tr height="20px">
        <td  class="form_head">
            <?php echo gettext("BALANCE") ?> :
        </td>
        <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
            &nbsp;<?php echo number_format($agent['credit'],3)?>
        </td>
    </tr>
    <tr height="20px">
        <td  class="form_head">
            <?php echo gettext("CURRENCY") ?> :
        </td>
        <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
            &nbsp;<?php echo $agent['currency']?>
        </td>
      </tr>
      <tr height="20px">
        <td  class="form_head">
            <?php echo gettext("VAT") ?> :
        </td>
        <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
            &nbsp;<?php echo $agent['vat']." %"?>
        </td>
    </tr>
    <tr height="20px">
        <td  class="form_head">
            <?php echo gettext("LANGUAGE") ?> :
        </td>
        <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
            &nbsp;<?php echo $lg_liste[$agent['language']][0];?>
        </td>
    </tr>
 </table>
 <br/>
<div style="width : 80%; text-align : right; margin-left:auto;margin-right:auto;" >
     <a class="cssbutton_big"  href="A2B_entity_agent.php">
        <img src="<?php echo Images_Path_Main;?>/icon_arrow_orange.gif"/>
        <?php echo gettext("AGENT LIST"); ?>
    </a>
</div>
<?php

$smarty->display( 'footer.tpl');
