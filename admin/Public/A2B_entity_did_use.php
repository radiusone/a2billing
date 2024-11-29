<?php

use A2billing\Admin;
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

$menu_section = 8;
require_once __DIR__ . "/../../common/lib/admin.defines.php";
require_once __DIR__ . "/form_data/FG_var_diduse.inc";
/**
 * @var FormHandler $HD_Form
 * @var Smarty $smarty
 * @var string $did
 * @var string $inuse
 * @var string $actionbtn
 * @var string $order
 * @var string $sens
 * @var string $current_page
 * @var string $posted
 */
Admin::checkPageAccess(Admin::ACX_DID);

$HD_Form->init();

$form_action ??= "list";

require_once __DIR__ . "/../templates/main.php";

// #### TOP SECTION PAGE
$HD_Form->create_toppage($form_action);

switch ($actionbtn) {
    case "release_did":
    echo create_help(_("Releasing DID put it in free stat and the user will not be monthly charged any more.."), 'ReleaseDID');
    ?>
    <FORM action="" id=form1 method=post name=form1>
        <INPUT type="hidden" name="did" value="<?php echo $did?>">
        <INPUT type="hidden" name="actionbtn" value="ask_release">
        <?= $HD_Form->csrf_inputs() ?>
        <br><br>
        <br><br>
        <TABLE cellspacing="0" class="delform_table5">
            <tr>
                <td width="434" class="text_azul"><?php echo gettext("If you really want release this DID , Click on the 	release button.")?>
                </td>
            </tr>
            <tr height="2">
                <td style="border-bottom: medium dotted rgb(255, 119, 102);">&nbsp; </td>
            </tr>
            <tr>
                    <td width="190" align="right" class="text"><INPUT title="<?php echo gettext("Release the DID ");?> " alt="<?php echo gettext("Release the DID "); ?>" hspace=2 id=submit22 name=submit22 src="<?= get_image_path("btn_release_did_94x20.gif") ?>" type="image"></td>
            </tr>
        </TABLE>
    </FORM>
<?php
    break;
    case "ask_release":
        (new Table("cc_did"))->updateRow($HD_Form->DBHandle, ["iduser" => 0, "reserved" => 0], ["id" => $did]);
        (new Table("cc_did_use"))->updateRow($HD_Form->DBHandle, ["releasedate" => "CURRENT_TIMESTAMP"], ["id_did" => $did, "activated" => 1]);
        (new Table("cc_did_use"))->addRow($HD_Form->DBHandle, ["activated" => 0, "id_did" => $did]);
        (new Table("cc_did_destination"))->deleteRow($HD_Form->DBHandle, ["id_cc_did" => $did]);
    break;
}

if (empty($actionbtn) || $actionbtn === "ask_release") {

echo create_help(_("List the DIDs currently in use with the customer id and their destination number <br/> You can use the search option to show the usage of a given DID or all DIDs"), 'DIDUsage');

$inuse ??= 1;
/*<!-- ** ** ** ** ** Part for the research ** ** ** ** ** -->*/?>
    <center>
    <FORM METHOD=POST name="myForm" ACTION="?order=<?php echo $order?>&sens=<?php echo $sens?>&current_page=<?php echo $current_page?>">
        <INPUT TYPE="hidden" NAME="posted" value="1">
        <INPUT TYPE="hidden" NAME="current_page" value="0">
        <?= $HD_Form->csrf_inputs() ?>

        <table class="bar-status" width="85%" border="0" cellspacing="1" cellpadding="2" align="center">
        <tbody>
        <tr>
            <td class="bgcolor_001" align="left" colspan="2">
                <?php echo gettext("Enter the DID id");?>: <INPUT TYPE="text" name="did" value="<?php echo $did?>" class="form_input_text">
            </td>
        </tr>
        <tr>
        <tr>
            <td class="bgcolor_004" align="left" ><font class="fontstyle_003">&nbsp;&nbsp;<?php echo gettext("Options");?></font>
            </td>
            <td class="bgcolor_005" align="center"><div align="left">
            <b><?php echo gettext("Show")?>:<?php echo gettext("Dids in use")?>
                <input name="inuse" type=radio value=1 <?php if ($inuse) {?>checked<?php } ?>>
                <?php echo gettext("All Dids")?> <input name="inuse" type="radio" value=0 <?php if (!$inuse) {?>checked<?php } ?>>

            </td>
        </tr>
        <tr>
                <td class="bgcolor_004" align="left" >
            </td>
            <td class="bgcolor_005" align="center" >
                <input type="image"  name="image16" align="top" border="0" src="<?= get_image_path("button-search.gif") ?>" />
              </td>
            </tr>
        </tbody></table>
    </FORM>
</center>
<?php

$list = $HD_Form->perform_action($form_action);

$HD_Form->create_form($form_action, $list) ;

}
require_once __DIR__ . "/../templates/footer.php";
