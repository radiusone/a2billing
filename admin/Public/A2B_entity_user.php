<?php

use A2billing\A2Billing;
use A2billing\Forms\FormHandler;

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
require_once "./form_data/FG_var_user.inc";
/**
 * @var A2Billing $A2B
 * @var SmartyBC $smarty
 * @var FormHandler $HD_Form
 * @var string $popup_select
 * @var string $form_action
 * @var string $CC_help_admin_edit
 * @var string $CC_help_admin_list
 * @var string $groupID
 * @var string $id
 */

$HD_Form->init();

$HD_Form->FG_EDIT_BUTTON_LINK= "?form_action=ask-edit&groupID=$groupID&id=";
$HD_Form->FG_DELETE_BUTTON_LINK= "?form_action=ask-delete&groupID=$groupID&id=";

if (!empty($id)) {
    $HD_Form->FG_EDIT_QUERY_CONDITION = str_replace("%id", "$id", $HD_Form->FG_EDIT_QUERY_CONDITION);
}

$form_action = $form_action ?? "list";
$action = $action ?? $form_action;

$list = $HD_Form->perform_action($form_action);

$smarty->display('main.tpl');

if (!$popup_select) {
    echo $form_action === 'ask-add' ? $CC_help_admin_edit : $CC_help_admin_list;
} else {
?>
<script>
function sendValue(selvalue)
{
    const formname = <?= json_encode($popup_formname ?? "") ?>;
    const fieldname = <?= json_encode($popup_fieldname ?? "") ?>;
    $(`form[name='${formname}'] [name='${fieldname}']`, window.opener.document).val(selvalue);
    window.close();
}
</script>
<?php
}

$HD_Form->create_toppage ($form_action);

$HD_Form->create_form($form_action, $list);

$smarty->display('footer.tpl');
