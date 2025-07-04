<?php

use A2billing\A2Billing;
use A2billing\Admin;
use A2billing\Agent;
use A2billing\Forms\FormHandler;
use A2billing\NotificationsDAO;
use A2billing\Notification;
use A2billing\Table;

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

$menu_section = 1;
require_once __DIR__ . "/../../common/lib/admin.defines.php";
/**
 * @var A2Billing $A2B
 */

require_once __DIR__ . "/../../common/form_data/FG_var_friend.inc";
/**
 * @var FormHandler $HD_Form
 * @var string $form_action
 */

Admin::checkPageAccess(Admin::ACX_CUSTOMER);

$HD_Form->init();
$form_action ??= "list";

/********************************* BATCH UPDATE ***********************************/
getpost_ifset(["batchupdate", "check", "type", "voip_type", "id_cc_card"]);
/**
 * @var numeric-string|null $batchupdate
 * @var array|null $check
 * @var array|null $type
 * @var array|null $mode
 * @var string|null $voip_type
 * @var numeric-string|null $id_cc_card
 */
$batchupdate ??= "0";
$check ??= [];
$type ??= [];
$mode ??= [];

// CHECK IF REQUEST OF BATCH UPDATE
if ($batchupdate === "1" && count($check)) {
    $HD_Form->prepare_list_subselection('list');
    $uf = [];
    getpost_ifset(["upd_callerid", "upd_context"], $uf);
    $update_fields = [];
    foreach ($uf as $k => $v) {
        $k = substr($k, 4);
        $update_fields[$k] = $v;
    }

    $updates = [];
    foreach (array_keys($check) as $ch) {
        // remove "upd_"
        $col = substr($ch, 4);
        $val = $update_fields[$col] ?? null;
        if (is_null($val)) {
            continue;
        }
        $updates[$col] = $val;
    }
    if (!(new Table($HD_Form->FG_QUERY_TABLE_NAME))->updateRow($updates, $HD_Form->list_query_conditions)) {
        $update_msg = _('Could not perform the batch update!');
    } else {
        $update_msg = _('The batch update has been successfully perform!');
    }
} elseif (!empty($id_cc_card) && ($form_action === "add_sip" || $form_action === "add_iax")) {
    /********************************* ADD SIP / IAX FRIEND ***********************************/
    getpost_ifset(["cardnumber", "useralias"]);
    /**
     * @var numeric-string|null $cardnumber
     * @var string|null $useralias
     */

    if ($form_action === "add_sip") {
        $friend_param_update = ["sip_buddy" => 1];
        $key = "sip_changed";
    } else {
        $friend_param_update = ["iax_buddy" => 1];
        $key = "iax_changed";
    }

    if (!USE_REALTIME) {
        $who = Notification::$ADMIN;
        $who_id = Admin::id();
        NotificationsDAO::addNotification($key, Notification::$HIGH, $who, $who_id);
    }

    (new Table("cc_card"))
        ->updateRow($friend_param_update, ["id" => $id_cc_card]);

    $list_friend = (new Table($HD_Form->FG_QUERY_TABLE_NAME))
        ->getRows(["id_cc_card" => $id_cc_card]);

    if (count($list_friend)) {
        header("Location: A2B_entity_card.php?voip_type=card&id=");
        exit();
    }

    $form_action = "add";

    $_POST['accountcode'] = $_POST['username'] = $_POST['name'] = $_POST['cardnumber'] = $cardnumber;
    $_POST['allow'] = $A2B->config['peer_friend']['allow'];
    $_POST['context'] = $A2B->config['peer_friend']['context'];
    $_POST['nat'] = $A2B->config['peer_friend']['nat'];
    $_POST['amaflags'] = $A2B->config['peer_friend']['amaflags'];
    $_POST['regexten'] = $cardnumber;
    $_POST['id_cc_card'] = $id_cc_card;
    $_POST['callerid'] = $useralias;
    $_POST['qualify'] = $A2B->config['peer_friend']['qualify'];
    $_POST['host'] = $A2B->config['peer_friend']['host'];
    $_POST['dtmfmode'] = $A2B->config['peer_friend']['dtmfmode'];
    $_POST['secret'] = strtr(base64_encode(bin2hex(random_bytes(16))), "+/=", "   ");

    // for the getProcessed var
    $HD_Form->init();
}

$HD_Form->FG_EDIT_BUTTON_LINK = "?form_action=ask-edit&voip_type=$voip_type&id=";
$HD_Form->FG_DELETE_BUTTON_LINK = "?form_action=ask-delete&voip_type=$voip_type&id=";

if (!USE_REALTIME) {
    // CHECK THE ACTION AND SET THE IS_SIP_IAX_CHANGE IF WE ADD/EDIT/REMOVE A RECORD
    if ($form_action === "add" || $form_action === "edit" || $form_action === "delete") {
        $key = $voip_type === "sip" ? "sip_changed" : "iax_changed";
        if (is_admin()) {
            $who = Notification::$ADMIN;
            $id = Admin::id();
        } elseif (is_agent()) {
            $who = Notification::$AGENT;
            $id = Agent::id();
        } else {
            $who = Notification::$UNKNOWN;
            $id = -1;
        }
        NotificationsDAO::AddNotification($key, Notification::$HIGH, $who, $id);
    }
}

$list = $HD_Form->perform_action($form_action);

require_once __DIR__ . "/templates/main.php";

// #### HELP SECTION
if ($form_action === "list") {
    if (!USE_REALTIME) {
    ?>
<div class="row pb-3">
    <div class="col">
        <?= _("Link to Generate on SIP/IAX Friends") ?>
        <br/>
        <?= _("Realtime not active, you have to use the conf file for your system") ?>
    </div>
</div>
<div class="row pb-3">
    <div class="col d-flex justify-content-around">
        <a href="CC_generate_friend_file.php?voip_type=sipfriend" class="btn btn-sm btn-outline-primary">
            <?= _("GENERATE ADDITIONAL_A2BILLING_SIP.CONF") ?>
        </a>
        <a href="CC_generate_friend_file.php?voip_type=iaxfriend" class="btn btn-sm btn-outline-primary">
            <?= _("GENERATE ADDITIONAL_A2BILLING_IAX.CONF") ?>
        </a>
    </div>
</div>
    <?php
    } else { ?>
<div class="row pb-3">
    <div class="col-auto">
        <a href="CC_generate_friend_file.php?action=reload" class="btn btn-sm btn-outline-primary">
            <?= _("Reload Asterisk") ?>
        </a>
    </div>
</div>
    <?php
    }
?>
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

<!-- ** ** ** ** ** Part for the Update ** ** ** ** ** -->
<div class="row">
    <div class="col-auto">
        <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#batchUpdateModal">
            <?= _("Batch Update") ?>
        </button>
    </div>
</div>
<div class="modal" id="batchUpdateModal" aria-labelledby="modal-title-udpate" aria-hidden="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-title-update"><?= _("Batch Update") ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="container-fluid form-striped" name="updateForm" id="updateForm" action="" method="post">
                    <input type="hidden" name="batchupdate" value="1"/>
                    <?= $HD_Form->csrf_inputs() ?>

                    <div class="row mb-1">
                        <div class="col">
                            <?= $HD_Form->FG_LIST_VIEW_ROW_COUNT ?> <?= _("cards selected!") ?>
                            <?= _("Use the options below to batch update the selected cards.") ?>
                        </div>
                    </div>

                    <div class="row mb-1">
                        <div class="col-4">
                            <input name="check[upd_callerid]" type="checkbox" value="on" aria-label="check to enable updates to this field" <?php if (!empty($check["upd_callerid"])): ?> checked="checked"<?php endif ?> class="form-check-input"/>
                            <label class="form-label form-label-sm" for="upd_callerid">
                                <?= _("CallerID") ?>
                            </label>
                        </div>
                        <div class="col">
                            <input type="text" name="upd_callerid" id="upd_callerid" value="<?= $update_fields["callerid"] ?? "" ?>" class="form-control form-control-sm">
                        </div>
                    </div>

                    <div class="row mb-1">
                        <div class="col-4">
                            <input name="check[upd_context]" type="checkbox" value="on" aria-label="check to enable updates to this field" <?php if (!empty($check["upd_context"])): ?> checked="checked"<?php endif ?> class="form-check-input"/>
                            <label class="form-label form-label-sm" for="upd_context">
                                <?= _("Context") ?>
                            </label>
                        </div>
                        <div class="col">
                            <input type="text" name="upd_context" id="upd_context" value="<?= $update_fields["context"] ?? "" ?>" class="form-control form-control-sm">
                        </div>
                    </div>
                </form> <!-- .container-fluid -->
            </div> <!-- .modal-body -->
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= _("Close") ?></button>
                <button type="submit" form="updateForm" class="btn btn-primary"><?= _("Batch Update Settings") ?></button>
            </div>
        </div> <!-- .modal-content -->
    </div> <!-- .modal-dialog -->
</div> <!-- .modal -->
<!-- ** ** ** ** ** Part for the Update ** ** ** ** ** -->
<?php
}

$HD_Form->create_toppage ($form_action);
$HD_Form->create_form($form_action, $list) ;

require_once __DIR__ . "/templates/footer.php";
