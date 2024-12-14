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

$menu_section = 17;
require_once __DIR__ . "/../../common/lib/admin.defines.php";
require_once "./form_data/FG_var_mailtemplate.inc";
/**
 * @var FormHandler $HD_Form
 */

Admin::checkPageAccess(Admin::ACX_MAIL);

getpost_ifset(["popup_select", "form_action", "action", "id"]);
/**
 * @var string $popup_select
 * @var string $form_action
 * @var string $action
 * @var numeric-string|null $id
 */

if ($action === "load") {
    $DBHandle=DbConnect();
    if (!empty($id)) {
        $result = (new Table("cc_templatemail", "messagetext, fromemail, fromname, subject"))
            ->getRow($DBHandle, ["id" => $id]);
        header("Content-Type: application/json");
        echo json_encode($result);
    }
    die();
}

$HD_Form->init();

$form_action ??= "list";
$list = $HD_Form->perform_action($form_action);

require_once __DIR__ . "/../templates/main.php";

if ($form_action === "list" && !$popup_select) {
    $HD_Form->create_search_form();
}
$HD_Form->create_toppage ($form_action);
$HD_Form->create_form($form_action, $list) ;

require_once __DIR__ . "/../templates/footer.php";
?>
<script>
    function sendValue(selvalue) {
        $.getJSON(
            "A2B_entity_mailtemplate.php",
            {id: selvalue, action: "load"},
            function(data){
                window.opener.document.getElementById('msg_mail').value = data.messagetext;
                window.opener.document.getElementById('from').value = data.fromemail;
                window.opener.document.getElementById('fromname').value = data.fromname;
                window.opener.document.getElementById('subject').value = data.subject;
                window.close();
            });
    }
</script>
