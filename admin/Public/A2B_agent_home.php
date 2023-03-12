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

getpost_ifset(array('id','result','action','message','id_msg','type','logo'));

if (empty($id)) {
    header("Location: A2B_entity_agent.php");
}
if ($result == "success") {
    $message_action = gettext("Home updated successfully");
}

if (in_array($action ?? "", ["down", "up", "delete"]) && !is_numeric($id_msg ?? null)) {
    echo "false";
    die();
}
$DBHandle = DbConnect();

if (!empty($action)) {
    switch ($action) {
        case 'add' :
            $count = $DBHandle->GetOne("SELECT COUNT(*) FROM cc_message_agent WHERE id_agent = ?", [$id]);
            if ($count) {
                $result = $DBHandle->Execute(
                    "INSERT INTO cc_message_agent (id_agent, type, message, order_display, logo) VALUES (?, ?, ?, ?, ?)",
                    [$id, $type ?? "", $message ?? "", $count, $logo ?? 0]
                );
                $result_param = $result ? "success" : "faild";
                header("Location: A2B_agent_home.php?" . "id=" . $id . "&result=$result_param");
                die();
            }
            header("Location: A2B_agent_home.php?" . "id=" . $id . "&result=faild");
            die();
        case 'askedit' :
            if (is_numeric($id_msg)) {
                $row = $DBHandle->GetRow("SELECT * FROM cc_message_agent WHERE id = ?", [$id_msg]);
                if ($row) {
                    $message = stripslashes($row['message']);
                    $logo = $row['message'];
                    $type=$row['type'];
                    $logo=$row['logo'];
                    $action="edit";
                }
            }
            break;
        case 'edit' :
            if (is_numeric($id_msg)) {
                $result = $DBHandle->Execute(
                    "UPDATE cc_message_agent SET type = ?, message = ?, logo = ? WHERE id = ?",
                    [$type ?? "", $message ?? "", $logo ?? 0, $id_msg]
                );
                $result_param = $result ? "success" : "faild";
                header("Location: A2B_agent_home.php?" . "id=" . $id . "&result=$result_param");
                die();
            }
            header("Location: A2B_agent_home.php?" . "id=" . $id . "&result=faild");
            die();
        case 'delete' :
            $order = $DBHandle->GetOne("SELECT order_display FROM cc_message_agent WHERE id = ?", [$id_msg]);
            if ($order) {
                $result = $DBHandle->Execute("DELETE FROM cc_message_table WHERE id = ?", [$id_msg]);
                $result = $DBHandle->Execute(
                    "UPDATE cc_message_table SET order_display = order_display - 1 WHERE id_agent = ? AND order_display > ?",
                    [$id, $order]
                );
                echo "true";
                die();
            }
            echo "false";
            die();
        case 'up' :
            $order = $DBHandle->GetOne("SELECT order_display FROM cc_message_agent WHERE id = ?", [$id_msg]);
            if ($order) {
                $result = $DBHandle->Execute(
                    "UPDATE cc_message_agent SET order_display = order_display + 1 WHERE id_agent = ? AND order_display = ?",
                    [$id, $order - 1]
                );
                $result = $DBHandle->Execute(
                    "UPDATE cc_message_agent SET order_display = order_display - 1 WHERE id_agent = ? AND order_display = ? AND id = ?",
                    [$id, $order, $id_msg]
                );
                echo "true";
                die();
            }
            echo "false";
            die();
        case 'down':
            $order = $DBHandle->GetOne("SELECT order_display FROM cc_message_agent WHERE id = ?", [$id_msg]);
            if ($order) {
                $result = $DBHandle->Execute(
                    "UPDATE cc_message_agent SET order_display = order_display - 1 WHERE id_agent = ? AND order_display = ?",
                    [$id, $order + 1]
                );
                $result = $DBHandle->Execute(
                    "UPDATE cc_message_agent SET order_display = order_display + 1 WHERE id_agent = ? AND order_display = ? AND id = ?",
                    [$id, $order, $id_msg]
                );
                echo "true";
                die();
            }
            echo "false";
            die();
    }
}
//load home message agent
if(empty($action)) $action="add";

$messages = $DBHandle->GetRow("SELECT * FROM cc_message_agent WHERE id_agent = ? ORDER BY order_display", [$id]);

$smarty->display('main.tpl');
$message_types = getMsgTypeList();
?>

<form action="<?php echo '?id='.$id ?>" method="post" >
    <input id="action" type="hidden" name="action" value="<?php echo $action;?>"/>
    <input id="id_msg" type="hidden" name="id_msg" value="<?php echo $id_msg;?>"/>
    <table class="epayment_conf_table">
        <tr>
            <td colspan="2">
                <font style="font-weight:bold; " ><?php echo gettext("MESSAGE : "); ?> </font>
             </td>
        </tr>
        <tr>
            <td colspan="2" align="center">
                <textarea id="wysiwyg" class="form_input_textarea" name="message" cols="100" rows="10"><?php echo $message;?></textarea>
             </td>
        </tr>
        <tr>
            <td colspan="2">
                <font style="font-weight:bold; " ><?php echo gettext("TYPE : "); ?></font>
                <select name="type">
                 <?php
                 foreach ($message_types as $msg_type) { ?>
                    <option value="<?php echo $msg_type[1];?>" <?php if($type==$msg_type[1]) echo "selected"?> > <?php echo $msg_type[0];?></option>
                    <?php
                     }
                  ?>
                </select>

                <input type="checkbox" value="1" name="logo" <?php if( $action!="edit" || $logo==1  ) echo "checked";?> /> <?php echo gettext("logo "); ?>
            </td>
        </tr>
        <tr>
            <td colspan="2" align="right">
                <input class="form_input_button" type="submit" value="<?php if($action!="edit")echo gettext("ADD");else echo gettext("UPDATE") ?>"/>
             </td>
        </tr>
    </table>
  </form>
<br/>
<div align="center">
<h1><?php echo gettext("Agent Home Page Preview")?> </h1>
<br/>
<?php
$size_msg = sizeof($messages);
if (!is_array($messages)) { ?>
<h3><?php echo gettext("No Message")?> </h3>
<?php
}?>
</div>
<?php
foreach ($messages as $message) {
    ?>
    <div id="msg" class="<?php echo $message_types[$message['type']][2];?>" style="margin-top:0px;position:relative;<?php if($message['logo']==0)echo 'background-image:none;padding-left:10px;'; ?>" >
        <?php if ($message['order_display']>0) { ?>
            <img id="<?php echo $message['id']; ?>" class="up" src="<?php echo Images_Path ?>/arrow_up.png"  border="0" style="position:absolute;right:60px;top:0;display:none;cursor:pointer"/>
         <?php } ?>
        <img id="<?php echo $message['id']; ?>" class="delete" src="<?php echo Images_Path ?>/delete.png"  border="0" style="position:absolute;right:40px;top:0;display:none;cursor:pointer"/>
            <img id="<?php echo $message['id']; ?>" class="edit" src="<?php echo Images_Path ?>/edit.png"  border="0" style="position:absolute;right:20px;top:0;display:none;cursor:pointer"/>
         <?php if ($message['order_display']<$size_msg-1) { ?>
            <img id="<?php echo $message['id']; ?>" class="down" src="<?php echo Images_Path ?>/arrow_down.png"  border="0" style="position:absolute;right:0px;top:0;display:none;cursor:pointer" />
         <?php } ?>
        <?php echo stripslashes($message['message']); ?>
    </div>

<?php
}

$smarty->display('footer.tpl');
?>

<script>
var id_agent = <?= json_encode($id) ?>;

$(function () {
    $('.msg_info, .msg_success, .msg_warning, .msg_error')
        .on('mouseover', e => $(this).children(".up,.down,.delete,.edit").show())
        .on('mouseout', e => $(this).children(".up,.down,.delete,.edit").hide());
    $('.delete').on('click', function () {
        if (confirm(<?php echo json_encode(gettext("Do you want delete this message ?")) ?>)) {
            $.get(
                "A2B_agent_home.php",
                {id: id_agent, id_msg: this.id, action: "delete"},
                function(data) {
                    if(data) {
                        window.location= "A2B_agent_home.php?id=" + id_agent + "&result=success";
                    }
                }
            );
        }
    });
    $('.up').on('click', function () {
        $.get(
            "A2B_agent_home.php",
            {id : id_agent, id_msg: this.id, action: "up"},
            function(data) {
                if(data) {
                    window.location = "A2B_agent_home.php?id=" + id_agent;
                }
            });
        });
    $('.down').on('click', function () {
        $.get(
            "A2B_agent_home.php",
            {id: id_agent, id_msg: this.id, action: "down"},
            function(data) {
                if(data) {
                    window.location= "A2B_agent_home.php?id="+id_agent;
                }
            });
        });
    $('.edit').on('click', e => window.location= "A2B_agent_home.php?id=" + id_agent + "&id_msg=" + this.id + "&action=askedit");
});
</script>
