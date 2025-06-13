<?php

use A2billing\Agent;
use A2billing\Forms\FormHandler;
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
 * @copyright   Copyright © 2022-2025 RadiusOne Inc.
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

require_once __DIR__ . "/../../common/lib/agent.defines.php";
require_once __DIR__ . "/../../common/form_data/FG_var_friend.inc";
/**
 * @var FormHandler $HD_Form
 * @var string|null $form_action
 * @var string|null $voip_type
 */

Agent::checkPageAccess(Agent::ACX_VOIPCONF);
$form_action ??= "list";
$voip_type ??= "";

$HD_Form -> init();

/********************************* ADD SIP / IAX FRIEND ***********************************/
getpost_ifset(["id_cc_card", "cardnumber", "useralias"]);
/**
 * @var numeric-string|null $id_cc_card
 * @var string|null $cardnumber
 * @var string|null $useralias
 */

if (!empty($id_cc_card) && ($form_action === "add_sip" || $form_action === "add_iax")) {
    if ($form_action == "add_sip") {
        $friend_param_update = ["sip_buddy" => 1];
        $key = "sip_changed";
        $TABLE_BUDDY = 'cc_sip_buddies';
    } else {
        $friend_param_update = ["iax_buddy" => 1];
        $key = "iax_changed";
        $TABLE_BUDDY = 'cc_iax_buddies';
    }

    if (!USE_REALTIME) {
        $who = Notification::$AGENT;
        $who_id = $_SESSION['agent_id'];
        NotificationsDAO::addNotification($key,Notification::$HIGH,$who,$who_id);
    }

    $instance_table_friend = new Table('cc_card');
    $instance_table_friend->updateRow($friend_param_update, ["id" => $id_cc_card]);

    $instance_table_friend = new Table($TABLE_BUDDY);
    $list_friend = $instance_table_friend->getRows(["id_cc_card" => $id_cc_card]);

    if ($list_friend) {
        header("Location: A2B_entity_card.php?id=$id_cc_card");
        exit();
    }

    $form_action = "add";

    $_POST['accountcode'] = $_POST['username'] = $_POST['name'] = $_POST['cardnumber'] = $cardnumber;
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
    $_POST['secret'] = MDP_NUMERIC(5) . MDP_STRING(10) . MDP_NUMERIC(5);

    // for the getProcessed var
    $HD_Form->init();
}

$HD_Form->FG_EDIT_BUTTON_LINK	= "?form_action=ask-edit&voip_type=$voip_type&id=";
$HD_Form->FG_DELETE_BUTTON_LINK = "?form_action=ask-delete&voip_type=$voip_type&id=";

if (!USE_REALTIME) {
    // CHECK THE ACTION AND SET THE IS_SIP_IAX_CHANGE IF WE ADD/EDIT/REMOVE A RECORD
    if ($form_action === "add" || $form_action === "edit" || $form_action === "delete") {
        $_SESSION["is_sip_iax_change"] = 1;
        if ($voip_type === "sip") {
            $_SESSION["is_sip_changed"] = 1;
        } else {
            $_SESSION["is_iax_changed"] = 1;
        }
    }
}

$list = $HD_Form -> perform_action($form_action);

require_once __DIR__ . "/templates/main.php";
?>

<?php if ($form_action === "list" && !empty($_SESSION["is_sip_iax_change"])): ?>

<div class="row pb-3">
    <div class="col d-flex justify-content-around">
        <?php if (!empty($_SESSION["is_sip_changed"])): ?>
        <a href="CC_generate_friend_file.php?voip_type=sipfriend" class="btn btn-sm btn-outline-primary">
            <?= _("GENERATE ADDITIONAL_A2BILLING_SIP.CONF") ?>
        </a>
        <?php endif ?>
        <?php if (!empty($_SESSION["is_iax_changed"])): ?>
        <a href="CC_generate_friend_file.php?voip_type=iaxfriend" class="btn btn-sm btn-outline-primary">
            <?= _("GENERATE ADDITIONAL_A2BILLING_IAX.CONF") ?>
        </a>
        <?php endif ?>
    </div>
</div>

<?php endif ?>

<?php if ($form_action=='list'): ?>

<form method="get" class="form form-horizontal">
    <div class="row pb-3">
        <label for="voip_type" class="col-2 col-form-label"><?= _("CONFIGURATION TYPE") ?></label>
        <div class="col">
            <select name="voip_type" id="voip_type" class="form-select" onchange="this.form.submit()">
                <option value="iax" <?= $voip_type === "iax" ? "selected=\"selected\"" : ""?>><?= _("IAX")?></option>
                <option value="sip" <?= $voip_type === "sip" ? "selected=\"selected\"" : ""?>><?= _("SIP")?></option>
            </select>
        </div>
    </div>
</form>

<?php endif ?>

<?php
$HD_Form->create_toppage($form_action);
$HD_Form->create_form($form_action, $list);

require_once __DIR__ . "/templates/footer.php";
