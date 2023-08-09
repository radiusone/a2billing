<?php

use A2billing\Admin;
use A2billing\Realtime;
use PhpAgi\AMI as AGI_AsteriskManager;

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

require_once "../../common/lib/admin.defines.php";

getpost_ifset(array('action', 'voip_type'));

Admin::checkPageAccess(Admin::ACX_CUSTOMER);

$DBHandle  = DbConnect();

if ($action == "reload") {

    $as = new AGI_AsteriskManager();

    $res = $as->connect(MANAGER_HOST, MANAGER_USERNAME, MANAGER_SECRET);

    if ($res) {
        if ($voip_type == "sipfriend") {
            $res = $as->Command('sip reload');
        } elseif ($voip_type == "iaxfriend") {
            $res = $as->Command('iax2 reload');
        } else {
            $res = $as->Command('sip reload');
            $res = $as->Command('iax2 reload');
        }
        $actiondone=1;

        // && DISCONNECTING
        $as->disconnect();
    } else {
        $error_msg= "<br><center><b><font color=red>".gettext("Cannot connect to the asterisk manager!<br>Please check your manager configuration.")."</font></b></center>";
    }
} else {

    $instance_realtime = new Realtime();

    if ($voip_type == "sipfriend") {

        $buddyfile = BUDDY_SIP_FILE;
        $instance_realtime -> create_trunk_config_file ('sip');

        $_SESSION["is_sip_changed"]=0;
        if ($_SESSION["is_iax_changed"]==0) {
            $_SESSION["is_sip_iax_change"]=0;
        }
    } else {

        $buddyfile = BUDDY_IAX_FILE;
        $instance_realtime -> create_trunk_config_file ('iax');

        $_SESSION["is_iax_changed"]=0;
        if ($_SESSION["is_sip_changed"]==0) {
            $_SESSION["is_sip_iax_change"]=0;
        }
    }

}

$smarty->display('main.tpl');

echo $CC_help_sipfriend_reload;

?>
<center>
<table width="60%" border="0" align="center" cellpadding="0" cellspacing="0" >
<TR>
  <TD style="border-bottom: medium dotted #555555">&nbsp; </TD>
</TR>
<tr><FORM NAME="sipfriend">
    <td height="31" class="bgcolor_001" style="padding-left: 5px; padding-right: 3px;" align=center>
    <br><br>
    <b>
    <?php
        if (strlen($error_msg)>0) {
            echo $error_msg;
        } elseif ($action != "reload") {
            if ($voip_type == "sipfriend") {
                echo gettext("The sipfriend file has been generated : ").'<br/>'.$buddyfile;
            } else {
                echo gettext("The iaxfriend file has been generated : ").'<br/>'.$buddyfile;
            }
    ?>

    <br><br><br>
    <a href="<?php  echo "?voip_type=$voip_type&action=reload";?>"><img src="<?php echo Images_Path;?>/icon_refresh.gif" />
        <?php echo gettext("Click here to reload your asterisk server"); ?>
    </a>

    <?php
        } else {
            echo gettext("Asterisk has been reloaded.");
        }
    ?>
    <br><br><br>
    </b>
    </td></FORM>
  </tr>
</table>
</center>

<br><br><br>

<?php

$smarty->display('footer.tpl');
