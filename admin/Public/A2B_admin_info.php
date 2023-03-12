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

$menu_section = 3;
require_once "../../common/lib/admin.defines.php";

Admin::checkPageAccess(Admin::ACX_ADMINISTRATOR);

getpost_ifset(array('id','groupID'));

if(!is_numeric($groupID) || ($groupID != 0 && $groupID != 1)) $groupID =0;

if (empty($id)) {
    header("Location: A2B_entity_user.php?groupID=$groupID");
}

$DBHandle  = DbConnect();
$admin = $DBHandle->GetRow("SELECT * FROM cc_ui_authen WHERE userid = ?", [$id]);
if (!$admin) {
    header("Location: A2B_entity_user.php?groupID=$groupID");
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
               <?php echo gettext("ADMIN INFO") ?>
           </th>
   </tr>
   <tr height="20px">
        <td  class="form_head">
            <?php echo gettext("LOGIN") ?> :
        </td>
        <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
            &nbsp;<?php echo $admin['login']?>
        </td>
    </tr>
    <tr height="20px">
        <td  class="form_head">
            <?php echo gettext("NAME") ?> :
        </td>
        <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
            &nbsp;<?php echo $admin['name']?>
        </td>
    </tr>

    <tr height="20px">
        <td  class="form_head">
            <?php echo gettext("ADDRESS") ?> :
        </td>
        <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
            &nbsp;<?php echo $admin['direction']?>
        </td>

    </tr>

    <tr height="20px">
        <td  class="form_head">
            <?php echo gettext("ZIP CODE") ?> :
        </td>
        <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
            &nbsp;<?php echo $admin['zipcode']?>
        </td>
    </tr>

    <tr  height="20px">
        <td  class="form_head">
            <?php echo gettext("CITY") ?> :
        </td>
        <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
            &nbsp;<?php echo $admin['city']?>
        </td>

    </tr>

    <tr  height="20px">
        <td  class="form_head">
            <?php echo gettext("STATE") ?> :
        </td>
        <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
            &nbsp;<?php echo $admin['state']?>
        </td>

    </tr>

    <tr  height="20px">
        <td  class="form_head">
            <?php echo gettext("COUNTRY") ?> :
        </td>
        <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
            &nbsp;<?php echo $admin['country']?>
        </td>

    </tr>
    <tr  height="20px">
        <td  class="form_head">
            <?php echo gettext("EMAIL") ?> :
        </td>
        <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
            &nbsp;<?php echo $admin['email']?>
        </td>

    </tr>
    <tr  height="20px">
        <td  class="form_head">
            <?php echo gettext("PHONE") ?> :
        </td>
        <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
            &nbsp;<?php echo $admin['phone']?>
        </td>
    </tr>
    <tr  height="20px">
        <td  class="form_head">
            <?php echo gettext("FAX") ?> :
        </td>
        <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
            &nbsp;<?php echo $admin['fax']?>
        </td>
    </tr>

 </table>
 <br/>
<div style="width : 80%; text-align : right; margin-left:auto;margin-right:auto;" >
     <a class="cssbutton_big"  href="<?php echo "A2B_entity_user.php?groupID=$groupID" ?>">
        <img src="<?php echo Images_Path_Main;?>/icon_arrow_orange.gif"/>
        <?php echo gettext("AGENT LIST"); ?>
    </a>
</div>
<?php

$smarty->display( 'footer.tpl');
