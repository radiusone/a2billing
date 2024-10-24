<?php

use A2billing\Agent;
use A2billing\Table;
use A2billing\NotificationsDAO;
use A2billing\Notification;

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

require_once "../../common/lib/agent.defines.php";
include './form_data/FG_var_friend.inc';

if (! has_rights (Agent::ACX_CUSTOMER)) {
    Header ("HTTP/1.0 401 Unauthorized");
    Header ("Location: PP_error.php?c=accessdenied");
    die();
}

if ($form_action=="add_sip" || $voip_type=="sip" || $form_action=="add_iax" || $voip_type=="iax") {
    if (! has_rights (Agent::ACX_VOIPCONF)) {
        Header ("HTTP/1.0 401 Unauthorized");
        Header ("Location: PP_error.php?c=accessdenied");
        die();
    }
}

$HD_Form -> init();

/********************************* ADD SIP / IAX FRIEND ***********************************/
getpost_ifset(array("id_cc_card", "cardnumber", "useralias"));

if ( (isset ($id_cc_card) && (is_numeric($id_cc_card)  != "")) && ( $form_action == "add_sip" || $form_action == "add_iax") ) {

    if ($form_action == "add_sip") {
        $friend_param_update=" sip_buddy='1' ";
        if (!USE_REALTIME) {
            $key = "sip_changed";
        }
    } else {
        $friend_param_update=" iax_buddy='1' ";
        if (!USE_REALTIME) {
            $key = "iax_changed";
        }
    }

    if (!USE_REALTIME) {
        $who= Notification::$AGENT;$who_id=$_SESSION['agent_id'];
        NotificationsDAO::AddNotification($key,Notification::$HIGH,$who,$who_id);
    }

    $instance_table_friend = new Table('cc_card');
    $instance_table_friend -> Update_table ($HD_Form -> DBHandle, $friend_param_update, "id='$id_cc_card'", $func_table = null);

    if ( $form_action == "add_sip" ) {
        $TABLE_BUDDY = 'cc_sip_buddies';
    } else {
        $TABLE_BUDDY = 'cc_iax_buddies';
    }
    $instance_table_friend = new Table($TABLE_BUDDY, '*');
    $list_friend = $instance_table_friend -> get_list ($HD_Form->DBHandle, "id_cc_card='$id_cc_card'");

    if (is_array($list_friend) && count($list_friend)>0) {
        Header ("Location: A2B_entity_card.php?id="); exit();
    }

    $form_action = "add";

    $_POST['accountcode'] = $_POST['username']= $_POST['name']= $_POST['cardnumber'] = $cardnumber;
    $_POST['allow'] = FRIEND_ALLOW;
    $_POST['context'] = FRIEND_CONTEXT;
    $_POST['nat'] = FRIEND_NAT;
    $_POST['amaflags'] = FRIEND_AMAFLAGS;
    $_POST['regexten'] = $cardnumber;
    $_POST['id_cc_card'] = $id_cc_card;
    $_POST['callerid'] = $useralias;
    $_POST['qualify'] = FRIEND_QUALIFY;
    $_POST['host'] = FRIEND_HOST;
    $_POST['dtmfmode'] = FRIEND_DTMFMODE;
    $_POST['secret'] = MDP_NUMERIC(5).MDP_STRING(10).MDP_NUMERIC(5);

    // for the getProcessed var
    $HD_Form->init();
}

$HD_Form -> FG_EDIT_BUTTON_LINK	= filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?form_action=ask-edit&voip_type=$voip_type&id=";
$HD_Form -> FG_DELETE_BUTTON_LINK = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?form_action=ask-delete&voip_type=$voip_type&id=";

if (!isset($form_action))  $form_action="list"; //ask-add
if (!isset($action)) $action = $form_action;

if (!USE_REALTIME) {
    // CHECK THE ACTION AND SET THE IS_SIP_IAX_CHANGE IF WE ADD/EDIT/REMOVE A RECORD
    if ($form_action == "add" || $form_action == "edit" || $form_action == "delete") {
        $_SESSION["is_sip_iax_change"]=1;
        if ($voip_type=='sip') {
            $_SESSION["is_sip_changed"]=1;
        } else {
            $_SESSION["is_iax_changed"]=1;
        }
    }
}

$list = $HD_Form -> perform_action($form_action);

// #### HEADER SECTION
$smarty->display('main.tpl');

// #### HELP SECTION
if ($form_action=='list') {
    echo $CC_help_sipfriend_list;

    if ( isset($_SESSION["is_sip_iax_change"]) && $_SESSION["is_sip_iax_change"]) { ?>
        <table  border="0" align="center" cellpadding="0" cellspacing="0" >
            <TR><TD style="border-bottom: medium dotted #ED2525" align="center"> <?php echo gettext("Changes detected on SIP/IAX Friends")?></TD></TR>
            <TR>
                <FORM NAME="sipfriend">
                <?= $HD_Form->csrf_inputs() ?>
                <td height="31" style="padding-left: 5px; padding-right: 3px;" align="center" class="bgcolor_013">
                <font color=white><b>
                <?php  if ( isset($_SESSION["is_sip_changed"]) && $_SESSION["is_sip_changed"] ) { ?>
                SIP : <input class="form_input_button"  TYPE="button" VALUE=" GENERATE ADDITIONAL_A2BILLING_SIP.CONF "
                onClick="self.location.href='./CC_generate_friend_file.php?voip_type=sipfriend';">
                <?php }
                if ( isset($_SESSION["is_iax_changed"]) && $_SESSION["is_iax_changed"] ) { ?>
                IAX : <input class="form_input_button"  TYPE="button" VALUE=" GENERATE ADDITIONAL_A2BILLING_IAX.CONF "
                onClick="self.location.href='./CC_generate_friend_file.php?voip_type=iaxfriend';">
                <?php } ?>
                </b></font></td>
                </FORM>
            </TR>
        </table>
    <?php  } // endif is_sip_iax_change

}else echo $CC_help_sipfriend_edit;

if ($form_action=='list') {
?>
<div align="center">
<table width="40%" border="0" align="center" cellpadding="0" cellspacing="1">
    <tr>
      <td  class="bgcolor_021">
      <table width="100%" border="0" cellspacing="1" cellpadding="0">
          <form name="form1" method="post" action="">
          <?= $HD_Form->csrf_inputs() ?>
          <tr>
            <td bgcolor="#FFFFFF" class="fontstyle_006" width="100%">&nbsp;<?php echo gettext("CONFIGURATION TYPE")?> </td>
            <td bgcolor="#FFFFFF" class="fontstyle_006" align="center">
               <select name="voip_type" id="col_configtype" onChange="window.document.form1.elements['PMChange'].value='Change';window.document.form1.submit();">
                 <option value="iax" <?php if($voip_type == "iax")echo "selected"?>><?php echo gettext("IAX")?></option>
                 <option value="sip" <?php if($voip_type == "sip")echo "selected"?>><?php echo gettext("SIP")?></option>
               </select>
              <input name="PMChange" type="hidden" id="PMChange">

            </td>
          </tr>
          </form>
      </table></td>
    </tr>
</table>
</div>

<?php
}

// #### TOP SECTION PAGE
$HD_Form -> create_toppage ($form_action);

$HD_Form -> create_form($form_action, $list) ;

// #### FOOTER SECTION
$smarty->display('footer.tpl');
