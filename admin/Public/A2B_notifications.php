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

$menu_section = 1;
require_once "../../common/lib/admin.defines.php";

Admin::checkPageAccess(Admin::ACX_CUSTOMER);

// #### HEADER SECTION
$smarty->display('main.tpl');

echo $CC_help_mail_notifications;

?>
<DIV ALIGN="CENTER">
<table align="center"  class="bgcolor_001" border="0" width="65%">
    <tr>
         <td>
        <?php if ( has_rights (Admin::ACX_ACXSETTING)) {
                echo gettext("All parameters relating to the Notifications module can be set using the Global Config available in System Settings menu.");
        ?>
                <br/>
                 <a href="A2B_entity_config.php?groupselect=notifications"><?php echo gettext("System Settings - Global Config")?></a>
        <?php
            } else {

                echo gettext("You don't have enough rights to enable or disable the Notifications modules. Ask your administrator");
            }
        ?>
         </td>
    </tr>
</table>
<br/>

<?php
// Load the list of values in the config table ! key=values_notifications
$key= "cron_notifications";
$DBHandle  = DbConnect();
$instance_config_table = new Table("cc_config", "id, config_value");
$QUERY = " config_key = '".$key."' ";
$return = null;
$return = $instance_config_table -> get_list($DBHandle, $QUERY);
$id_config = $return[0]["id"];

if (!is_null($return)&& (!empty($return)>0)) {
?>

<table align="center"  class="bgcolor_001" border="0" width="65%">
<tr>
    <td>
    <?php
        if($return[0]["config_value"]) echo gettext("Currently, the cron process of notifications is activated.");
        else echo gettext("Currently, the cron process of notification is deactivated.");
        echo '<br/>';
        echo gettext("Make sure that the cron files are correctly configured in the crontab!");
        echo '<br/>';

        if ( has_rights (Admin::ACX_ACXSETTING)) {
            echo gettext("Press");
            echo ' <a href="A2B_entity_config.php?form_action=ask-edit&id='.$id_config.'">';
            echo gettext("Modify") ."</a> ". gettext("to change enable or disable periodical notifications");
        } else {
            echo gettext("You don't have enough rights To Enable or Disable the process of notification. Ask your administrator");

        }
    ?>
     </td>
</tr>
</table>
<?php } ?>

<br/>

<?php

// Load the list of values in the config table ! key=values_notifications
$key= "values_notifications";
$DBHandle  = DbConnect();
$instance_config_table = new Table("cc_config", "id, config_value");
$QUERY = " config_key = '".$key."' ";
$return = null;
$return = $instance_config_table -> get_list($DBHandle, $QUERY);
$id_config = $return[0]["id"];

if (!is_null($return)&& (!empty($return)>0) ) {
    $values = explode(":",$return[0]["config_value"]);
?>
<table align="center"  class="bgcolor_001" border="0" width="65%">
    <tr>
        <td width="70%"><?php echo gettext("This box shows the possible values to choose from when the user receives a notification");?>
        <br/><br/>
        <?php
            if ( has_rights (Admin::ACX_ACXSETTING)) {
                    echo gettext("Press");
                    echo ' <a href="A2B_entity_config.php?form_action=ask-edit&id='.$id_config.'">';
                    echo gettext("Modify") ."</a> ". gettext("to change the values.");
            } else {
                    echo gettext("You don't have enough rights to modify the list of values. Ask your administrator");
            } ?>

                 </td>
                 <td align="center">

                         <select class="form_input_select" multiple="multiple" width="50">
                         <?php
                              foreach ($values as $val) {
                             echo '<option value="'.$val .'"> '.$val.'</option>';
                             }?>
                         </select>
                 </td>
            </tr>
</table>
        <?php }?>
<br/>

<?php
// Load the list of values in the config table ! key=values_notifications
$key= "delay_notifications";
$DBHandle  = DbConnect();
$instance_config_table = new Table("cc_config", "id, config_value");
$QUERY = " config_key = '".$key."' ";
$return = null;
$return = $instance_config_table -> get_list($DBHandle, $QUERY);
$id_config = $return[0]["id"];

if (!is_null($return)&& (!empty($return)>0)) {
?>
<table align="center"  class="bgcolor_001" border="0" width="65%">
<tr>
    <td>
    <?php
        $msg= gettext("Currently, the periodicity of notification is ").$return[0]["config_value"];
        if($return[0]["config_value"] == 1) $msg.=gettext(" day");
        else $msg.=gettext(" days");
        $msg.='.';
        echo $msg;

        echo '<br/>';
        if ( has_rights (Admin::ACX_ACXSETTING)) {
            echo gettext("Press");
            echo ' <a href="A2B_entity_config.php?form_action=ask-edit&id='.$id_config.'">';
            echo gettext("Modify") ."</a> ". gettext("to change the periodicity");
        } else {
            echo gettext("You don't have enough rights to modify the delay of notification. Ask your administrator");
        } ?>
     </td>
</tr>
</table>

<?php
}
?>
</DIV>
<?php
$smarty->display('footer.tpl');
