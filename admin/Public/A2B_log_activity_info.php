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

$menu_section = 16;
require_once "../../common/lib/admin.defines.php";

Admin::checkPageAccess(Admin::ACX_ADMINISTRATOR);

getpost_ifset(array('id'));

if (empty($id)) {
    header("Location: A2B_entity_log_viewer.php");
}

$DBHandle  = DbConnect();

$log_table = new Table('cc_system_log', '*');
$log_clause = "id = ".$id;
$log_result = $log_table -> get_list($DBHandle, $log_clause);
$log = $log_result[0];

if (empty($log)) {
    header("Location: A2B_entity_log_viewer.php");
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
               <?php echo gettext("LOG ACTIVITY INFO") ?>
           </th>
   </tr>
   <tr height="20px">
        <td  class="form_head" width="30%">
            <?php echo gettext("ID") ?> :
        </td>
        <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
            <?php  echo $log['id'];?>
        </td>
   </tr>
   <tr height="20px">
        <td  class="form_head">
            <?php echo gettext("USER") ?> :
        </td>
        <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
            <?php echo get_nameofadmin($log['iduser']);?>
        </td>
   </tr>
       <tr height="20px">
        <td  class="form_head">
            <?php echo gettext("LOG-LEVEL") ?> :
        </td>
        <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
            <?php echo $log['loglevel']?>
        </td>
    </tr>
        <tr height="20px">
        <td  class="form_head">
            <?php echo gettext("ACTION") ?> :
        </td>
        <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
            <?php echo $log['action']?>
        </td>
    </tr>
    <tr height="20px">
        <td  class="form_head">
            <?php echo gettext("DESCRIPTION") ?> :
        </td>
        <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
            <?php echo $log['description']?>
        </td>
    </tr>
       <tr height="20px">
        <td  class="form_head">
            <?php echo gettext("TABLENAME") ?> :
        </td>
        <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
            <?php echo $log['tablename']?>
        </td>
    </tr>
    <tr height="20px">
        <td  class="form_head">
            <?php echo gettext("IPADDRESS") ?> :
        </td>
        <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
            <?php echo $log['ipaddress']?>
        </td>
    </tr>
    <tr height="20px">
        <td  class="form_head">
            <?php echo gettext("CREATION DATE") ?> :
        </td>
        <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
            <?php echo $log['creationdate']?>
        </td>
    </tr>
    <tr height="20px">
        <td  class="form_head">
            <?php echo gettext("DATA") ?> :
        </td>
        <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
            <?php echo $log['data']?>
        </td>
    </tr>
 </table>
 <br/>
<div style="width : 80%; text-align : right; margin-left:auto;margin-right:auto;" >
     <a class="cssbutton_big"  href="A2B_entity_log_viewer.php">
        <img src="<?php echo Images_Path_Main;?>/icon_arrow_orange.gif"/>
        <?php echo gettext("LOG LIST"); ?>
    </a>
</div>
<?php

$smarty->display( 'footer.tpl');
