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

$menu_section = 17;
require_once "../../common/lib/admin.defines.php";

Admin::checkPageAccess(Admin::ACX_MAIL);

getpost_ifset(array (
    'id',
    'languages',
    'subject',
    'mailtext',
    'translate_data',
    'id_language',
    'mailtype'
));

$handle = DbConnect();
$instance_table = new Table();

// #### HEADER SECTION
$smarty->display('main.tpl');

if (isset ($translate_data) && $translate_data == 'translate') {
    $check = false;
    $QUERY = "SELECT id FROM cc_templatemail WHERE mailtype = '$mailtype' AND id_language = '$languages'";
    $result = $instance_table->SQLExec($handle, $QUERY);
    if (is_array($result) && count($result) > 0) {
        $check = true;
    }

    if ($check) {
        $param_update = "subject = '$subject', messagetext = '$mailtext'";
        $clause = "mailtype = '$mailtype' AND id_language = '$languages'";
        $func_table = 'cc_templatemail';
        $instance_table->Update_table($handle, $param_update, $clause, $func_table);
    } else {
        $fromemail = '';
        $fromname = '';
        $QUERY = "SELECT fromemail, fromname, mailtype FROM cc_templatemail WHERE mailtype = '$mailtype' AND id_language = 'en'";
        $result = $instance_table->SQLExec($handle, $QUERY);
        if (is_array($result)) {
            if (count($result) > 0) {
                $fromemail = $result[0][0];
                $fromname = $result[0][1];
                $mailtype = $result[0][2];
            }
        }

        $value = "'$languages', '$subject', '$mailtext', '$mailtype','$fromemail','$fromname'";
        $func_fields = "id_language, subject, messagetext, mailtype, fromemail, fromname";
        $func_table = 'cc_templatemail';
        $id_name = "id";
        $instance_table->Add_table($handle, $value, $func_fields, $func_table, $id_name);
    }
}

// Query to get mail template information
$QUERY = "SELECT id, mailtype, subject, messagetext, id_language FROM cc_templatemail WHERE mailtype = '$mailtype'";
if (isset ($languages))
    $QUERY .= " and id_language = '$languages'";
$mail = $instance_table->SQLExec($handle, $QUERY);

// #### HELP SECTION
echo $CC_help_list_misc;

// Query to get all languages with ids
$QUERY = "SELECT code, name FROM cc_iso639 ORDER BY code";
$result = $instance_table->SQLExec($handle, $QUERY);
if (is_array($result)) {
    $num_cur = count($result);
    for ($i = 0; $i < $num_cur; $i++) {
        $languages_list[$result[$i][0]] = array (
            0 => $result[$i][0],
            1 => $result[$i][1]
        );
    }
}

?>
<FORM name="theForm" action="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL) ?>" METHOD="POST">
<INPUT name="mailtype" value="<?php echo $mailtype; ?>" type="hidden">

<?= $HD_Form->csrf_inputs() ?>

<table cellspacing="2" class="addform_table1">
    <TBODY>
    <TR>
        <TD width="%25" valign="middle" class="form_head"> <?php echo gettext('Language');?> </TD>
        <TD width="%75" valign="top" class="tableBodyRight" background="../Public/templates/default/images/background_cells.gif" class="text">
            <select NAME="languages" size="1" class="form_input_select" onChange="form.submit()">
            <?php
                foreach ($languages_list as $key => $lang_value) {
            ?>
            <option value='<?php echo $lang_value[0];?>'
                <?php
                if ($mail[0][4] != '') {
                    if ($lang_value[0]==$mail[0][4]) {print "selected";}
                } else {
                    if ($lang_value[0]==$languages) {print "selected";}
                }?>><?php echo $lang_value[1]; ?></option>
            <?php }?>
            </select>
            <span class="liens">
        </span>
        </TD>
    </TR>

    <TR>
        <TD width="%25" valign="middle" class="form_head"> <?php echo gettext('Subject');?> </TD>
        <TD width="%75" valign="top" class="tableBodyRight" background="../Public/templates/default/images/background_cells.gif" class="text">
        <INPUT class="form_input_text" name="subject"  size=30 maxlength=30 value="<?php echo $mail[0][2]?>">
        <span class="liens">
        </span>
        </TD>
    </TR>

    <TR>
        <TD width="%25" valign="middle" class="form_head"> <?php echo gettext('Mail Text');?> </TD>
        <TD width="%75" valign="top" class="tableBodyRight" background="../Public/templates/default/images/background_cells.gif" class="text">
        <TEXTAREA class="form_input_textarea" name="mailtext" cols=60 rows=12><?php echo $mail[0][3]?></TEXTAREA>
        <span class="liens">
        </span>
        </TD>
    </TR>
    </table>
    <TABLE cellspacing="0" class="editform_table8">
    <tr>
     <td colspan="2" class="editform_dotted_line">&nbsp; </td>
    </tr>

    <tr>
        <td width="50%" class="text_azul"><span class="tableBodyRight"><?php echo gettext('Once you have completed the form above, click on the Translate button.');?></span></td>
        <td width="50%" align="right" class="text">
    <input class="form_input_button" TYPE="submit" name="translate_data" VALUE="translate">
        </td>
    </tr>

    </TABLE>
    <INPUT type="hidden" name="id" value="<?php echo $id?>">
</form>

<?php

// #### FOOTER SECTION
$smarty->display('footer.tpl');
