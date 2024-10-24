<?php

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

require_once "../../common/lib/admin.defines.php";

getpost_ifset(array (
    'OldPassword',
    'NewPassword'
));

$DBHandle = DbConnect();

if ($form_action == "ask-modif") {
    $table_old_pwd = new Table("cc_ui_authen", " login");
    $OldPwd_encoded = hash('whirlpool', $OldPassword);
    $clause_old_pwd = "login = '" . $_SESSION["pr_login"] . "' AND pwd_encoded = '" . $OldPwd_encoded . "'";
    $result_old_pwd = $table_old_pwd->get_list($DBHandle, $clause_old_pwd);

    if (!empty ($result_old_pwd)) {
        $instance_sub_table = new Table('cc_ui_authen');
        $NewPwd_encoded = hash('whirlpool', $NewPassword);
        $QUERY = "UPDATE cc_ui_authen SET  pwd_encoded= '" . $NewPwd_encoded . "' WHERE ( login = '" . $_SESSION["pr_login"] . "' ) ";
        $result = $instance_sub_table->SQLExec($DBHandle, $QUERY, 0);
    } else {
        $OldPasswordFaild = true;
    }
}

// #### HEADER SECTION
$smarty->display('main.tpl');
?>
<script>
$(function() {
    $("#checkpassword").on('click', function () {
        var np = $("#NewPassword");
        var cnp = $("#CNewPassword");

        if (!np.val()) {
            alert('<?php echo gettext("No value in New Password entered")?>');
            np.focus();
            return false;
        }
        if (!cnp.val()) {
            alert('<?php echo gettext("No Value in Confirm New Password entered")?>');
            cnp.focus();
            return false;
        }
        if (np.val().length < 5) {
            alert('<?php echo gettext("Password length should be greater than or equal to 5")?>');
            np.focus();
            return false;
        }
        if (np.val() !== cnp.val()) {
            alert('<?php echo gettext("Value mismatch, New Password should be equal to Confirm New Password")?>');
            np.focus();
            return false;
        }
        return true;
    });
    $("#NewPassword").focus();
});
</script>

<?php

if ($form_action == "ask-modif") {

    if (isset ($result)) {
?>
<script language="JavaScript">
alert("<?php echo gettext("Your password is updated successfully.")?>");
</script>
<?php
    } elseif (isset ($OldPasswordFaild)) {
?>
<script language="JavaScript">
alert("<?php echo gettext("Wrong old password.")?>");
</script>
<?php
    } else {
?>
<script language="JavaScript">
alert("<?php echo gettext("System is failed to update your password.")?>");
</script>
<?php
    }
}
?>
<br>
<form method="post" action="<?php  echo $_SERVER["PHP_SELF"]?>" name="frmPass">
    <input type="hidden" name="form_action" value="ask-modif"/>
<center>
<table class="changepassword_maintable" align=center>
<tr class="bgcolor_009">
    <td align=left colspan=2><b><font color="#ffffff">- <?php echo gettext("Change Password")?>&nbsp; -</b></td>
</tr>
<tr>
    <td align="center" colspan=2>&nbsp;<p class="liens"><?php echo gettext("Do not use \" or = characters in your password");?></p></td>
</tr>
<tr>
    <td align=right><font class="fontstyle_002"><?php echo gettext("Old Password")?>&nbsp; :</font></td>
    <td align=left><input id="OldPassword" name="OldPassword" type="password" class="form_input_text" ></td>
</tr>
<tr>
    <td align=right><font class="fontstyle_002"><?php echo gettext("New Password")?>&nbsp; :</font></td>
    <td align=left><input id="NewPassword" name="NewPassword" type="password" class="form_input_text" ></td>
</tr>
<tr>
    <td align=right><font class="fontstyle_002"><?php echo gettext("Confirm Password")?>&nbsp; :</font></td>
    <td align=left><input id="CNewPassword" name="CNewPassword" type="password" class="form_input_text" ></td>
</tr>
<tr>
    <td align=left colspan=2>&nbsp;</td>
</tr>
<tr>
    <td align=center colspan=2 ><input type="submit" id="checkpassword" name="submitPassword" value="&nbsp;<?php echo gettext("Save")?>&nbsp;" class="form_input_button">&nbsp;&nbsp;<input type="reset" name="resetPassword" value="&nbsp;Reset&nbsp;" class="form_input_button" > </td>
</tr>
<tr>
    <td align=left colspan=2>&nbsp;</td>
</tr>

</table>
</center>
</form>

<br><br><br>

<?php

// #### FOOTER SECTION
$smarty->display('footer.tpl');
