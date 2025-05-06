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

$menu_section = 2;
require_once __DIR__ . "/../../common/lib/admin.defines.php";

Admin::checkPageAccess(Admin::ACX_ADMINISTRATOR);

getpost_ifset(["id", "action", "message", "id_msg", "type", "logo"]);
/**
 * @var numeric-string|null $id
 * @var string|null $action
 * @var string|null $message
 * @var numeric-string|null $id_msg
 * @var string|null $type
 * @var string|null $logo
 */
if (empty($id)) {
    header("Location: A2B_entity_agent.php");
}
$action ??= "";
$type ??= "";
$message ??= "";
$logo ??= 0;
$id_msg ??= null;

if (in_array($action, ["down", "up", "delete"]) && !is_numeric($id_msg)) {
    echo "false";
    die();
}
$DBHandle = DbConnect();

switch ($action) {
    case "add":
        $result_param = false;
        $count = $DBHandle->GetOne("SELECT COUNT(*) FROM cc_message_agent WHERE id_agent = ?", [$id]);
        if ($count !== false) {
            $result = $DBHandle->Execute(
                "INSERT INTO cc_message_agent (id_agent, type, message, order_display, logo) VALUES (?, ?, ?, ?, ?)",
                [$id, $type, $message, $count, $logo]
            );
            $result_param = $result ? "success" : "faild";
        }
        header("Location: A2B_agent_home.php?id=$id&result=$result_param");
        die();

    case "ask-edit":
        if (is_numeric($id_msg)) {
            $row = $DBHandle->GetRow("SELECT message, type, logo FROM cc_message_agent WHERE id = ?", [$id_msg]);
            if ($row) {
                $message = $row['message'];
                $type = $row['type'];
                $logo = $row['logo'];
                $action = "edit";
            }
        }
        break;

    case "edit":
        $result_param = false;
        if (is_numeric($id_msg)) {
            $result = $DBHandle->Execute(
                "UPDATE cc_message_agent SET type = ?, message = ?, logo = ? WHERE id = ?",
                [$type, $message, $logo, $id_msg]
            );
            $result_param = $result ? "success" : "faild";
        }
        header("Location: A2B_agent_home.php?id=$id&result=$result_param");
        die();

    case "delete":
        $order = $DBHandle->GetOne("SELECT order_display FROM cc_message_agent WHERE id = ?", [$id_msg]);
        if ($order !== false) {
            $result = $DBHandle->Execute("DELETE FROM cc_message_agent WHERE id = ?", [$id_msg]);
            $result = $DBHandle->Execute(
                "UPDATE cc_message_agent SET order_display = order_display - 1 WHERE id_agent = ? AND order_display > ?",
                [$id, $order]
            );
            die("true");
        }
        http_response_code(500);
        die("false");

    case "up":
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
            die("true");
        }
        http_response_code(500);
        die("false");

    case "down":
        $order = $DBHandle->GetOne("SELECT order_display FROM cc_message_agent WHERE id = ?", [$id_msg]);
        if ($order !== false) {
            $result = $DBHandle->Execute(
                "UPDATE cc_message_agent SET order_display = order_display - 1 WHERE id_agent = ? AND order_display = ?",
                [$id, $order + 1]
            );
            $result = $DBHandle->Execute(
                "UPDATE cc_message_agent SET order_display = order_display + 1 WHERE id_agent = ? AND order_display = ? AND id = ?",
                [$id, $order, $id_msg]
            );
            die("true");
        }
        http_response_code(500);
        die("false");

    case "":
        $action = "add";
        break;
}


$messages = $DBHandle->GetAll("SELECT * FROM cc_message_agent WHERE id_agent = ? ORDER BY order_display", [$id]);

require_once __DIR__ . "/../templates/main.php";
$message_types = getMsgTypeList();
$message_classes = ["alert-info", "alert-success", "alert-warning", "alert-danger"];
$message_logos = ["bi-info-circle-fill text-info", "bi-check-circle-fill text-success", "bi-exclamation-circle-fill text-warning", "bi-x-circle-fill text-danger"];
?>
<div class="row pb-3 align-items-center">
    <div class="col"><?= _("Use this form to add items to the agent's home page.") ?></div>
</div>
<form action="?id=<?= $id ?>" method="post">
    <input id="action" type="hidden" name="action" value="<?= $action ?>"/>
    <input id="id_msg" type="hidden" name="id_msg" value="<?= $id_msg ?>"/>
    <div class="row mb-3">
        <label for="message" class="col-3 col-form-label"><?= _("Message") ?></label>
        <div class="col">
            <textarea name="message" id="message" class="form-control" rows="10"><?= $message ?></textarea>
        </div>
    </div>
    <div class="row mb-3">
        <label for="type" class="col-3 col-form-label"><?= _("Type") ?></label>
        <div class="col">
            <select name="type" id="type" class="form-select">
                <?php foreach ($message_types as $k => $message_type): ?>
                <option value="<?= $k ?>" <?php if ("$type" === "$k"): ?>selected="selected"<?php endif ?>><?= $message_type ?></option>
                <?php endforeach ?>
            </select>
        </div>
    </div>

    <div class="row mb-3">
        <label for="logo" class="col-3 col-form-label"><?= _("Display icon?") ?></label>
        <div class="col">
            <div class="form-check">
                <input name="logo" id="logo1" class="form-check-input" type="radio" value="1" <?php if ($logo): ?>checked="checked"<?php endif ?>/>
                <label for="logo1" class="form-check-label"><?= _("Yes") ?></label>
            </div>
            <div class="form-check">
                <input name="logo" id="logo0" class="form-check-input" type="radio" value="0" <?php if (!$logo): ?>checked="checked"<?php endif ?>/>
                <label for="logo0" class="form-check-label"><?= _("No") ?></label>
            </div>
        </div>
    </div>

    <div class="row my-4 justify-content-end">
        <div class="col-auto">
            <button class="btn btn-primary" type="submit"><?= _("Save") ?></button>
        </div>
    </div>
</form>

<h3 class="text-center"><?= _("Agent Home Page Preview") ?> </h3>
<?php if (!$messages): ?>
<h4><?= _("No Message") ?> </h4>
<?php endif ?>

<?php foreach ($messages as $message): ?>
<div class="alert <?= $message_classes[$message["type"]] ?> d-flex align-items-center">
    <?php if ($message["logo"]): ?>
    <div class="bi bi-32 <?= $message_logos[$message["type"]] ?> flex-shrink-0 me-2" aria-hidden="true"></div>
    <?php endif ?>
    <div class="flex-grow-1 mx-2">
        <?= $message["message"] ?>
    </div>
    <div class="flex-shrink-1 d-none mx-2 btn-container">
        <button class="btn btn-sm border-0 p-0 up" <?php if ($message["order_display"] < 1):?>disabled="disabled"<?php endif ?> data-id="<?= $message["id"] ?>">
            <span class="bi bi-16 bi-arrow-up-circle-fill" aria-label="<?= _("Move up") ?>"></span>
        </button>
        <button class="btn btn-sm border-0 p-0 delete" data-id="<?= $message["id"] ?>" data-msg="<?= _("Do you want delete this message ?") ?>">
            <span class="bi bi-16 bi-x-circle-fill" aria-label="<?= _("Delete") ?>"></span>
        </button>
        <button class="btn btn-sm border-0 p-0 edit" data-id="<?= $message["id"] ?>">
            <span class="bi bi-16 bi-pencil-fill" aria-label="<?= _("Edit") ?>"></span>
        </button>
        <button class="btn btn-sm border-0 p-0 down" <?php if ($message["order_display"] >= count($messages) - 1):?>disabled="disabled"<?php endif ?> data-id="<?= $message["id"] ?>">
            <span class="bi bi-16 bi-arrow-down-circle-fill" aria-label="<?= _("Move down") ?>"></span>
        </button>
    </div>
</div>

<?php endforeach ?>

<script>
const id_agent = <?= json_encode($id) ?>;

document.querySelectorAll("div.alert")?.forEach(function(el) {
    el.addEventListener("mouseenter", e => e.target.querySelector(".btn-container")?.classList.remove("d-none"));
    el.addEventListener("mouseleave", e => e.target.querySelector(".btn-container")?.classList.add("d-none"));
});
document.querySelectorAll("button.delete")?.forEach(function(el) {
    el.addEventListener("click", function () {
        if (!confirm(this.dataset.msg)) {
            return;
        }
        fetch(`A2B_agent_home.php?id=${id_agent}&id_msg=${this.dataset.id}&action=delete`)
            .then(() => window.location= `A2B_agent_home.php?id=${id_agent}&result=success`);
    });
});
document.querySelectorAll("button.up")?.forEach(function(el) {
    el.addEventListener("click", function () {
        fetch(`A2B_agent_home.php?id=${id_agent}&id_msg=${this.dataset.id}&action=up`)
            .then(() => window.location= `A2B_agent_home.php?id=${id_agent}&result=success`);
    });
});
document.querySelectorAll("button.down")?.forEach(function(el) {
    el.addEventListener("click", function () {
        fetch(`A2B_agent_home.php?id=${id_agent}&id_msg=${this.dataset.id}&action=down`)
            .then(() => window.location= `A2B_agent_home.php?id=${id_agent}&result=success`);
    });
});
document.querySelectorAll("button.edit")?.forEach(function(el) {
    el.addEventListener("click", function () {
        window.location= `A2B_agent_home.php?id=${id_agent}&id_msg=${this.dataset.id}&action=ask-edit`;
    });
});
</script>

<?php
require_once __DIR__ . "/../templates/footer.php";
