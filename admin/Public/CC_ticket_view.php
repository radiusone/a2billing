<?php

use A2billing\Admin;
use A2billing\Comment;
use A2billing\Ticket;

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

$menu_section = 4;
require_once "../../common/lib/admin.defines.php";

Admin::checkPageAccess(Admin::ACX_SUPPORT);

getpost_ifset(["result", "action", "status", "id", "comment_text"]);
/**
 * @var string $result
 * @var string $action
 * @var string $status
 * @var string $id
 * @var string $comment_text
 */

if (($result ?? "") === "success") {
    $message = gettext("Ticket updated successfully");
}

if (empty($id)) {
    die(_("Ticket ID not found"));
}

if (!empty($action)) {
    switch ($action) {
        case "edit":
            $admin_id = $_SESSION["admin_id"];
            $ticket = Ticket::getTicket($id);
            if ($ticket) {
                if ($ticket->getStatus() !== (int)$status) {
                    $ticket->setStatus($status);
                }
                if ($comment_text) {
                    $ticket->insertComment($comment_text, $admin_id, Comment::ADMIN);
                }
                header("Location: CC_ticket_view.php?id=$id&result=success");
            }
            break;
        default:
            die("invalid action");
    }
    die("update error");
}

$ticket = new Ticket($id);
$comments = $ticket->loadComments();
$states = Ticket::getPossibleStatus($ticket->getStatus(), true);

$ticket->markViewed(Ticket::ADMIN);
foreach ($comments as $comment) {
    $comment->markViewed(Comment::ADMIN);
}

require_once __DIR__ . "/../templates/main.php";

?>
<div class="row pb-3">
    <div class="col">
        <h5><?= sprintf(_("Ticket %d"), $ticket->getId()) ?></h5>
        <h6><?= $ticket->getTitle() ?></h6>
        <span class="badge text-bg-danger"><?= $ticket->getViewed(Ticket::ADMIN) ? _("NEW") : "" ?></span>
    </div>
</div>
<div class="row pb-3 border-top">
    <div class="col">
        <strong><?= _("By") ?></strong>
        <br/>
        <?= $ticket->getCreatorname() ?>
    </div>
    <div class="col">
        <strong><?= _("Priority") ?></strong>
        <br/>
        <?= $ticket->getPriorityDisplay() ?>
    </div>
    <div class="col">
        <strong><?= _("Date") ?></strong>
        <br/>
        <?= $ticket->getCreationdate() ?>
    </div>
<?php if ($ticket->getComponentid()): ?>
    <div class="col">
        <strong><?= _("Component") ?></strong>
        <br/>
        <?= $ticket->getComponentname() ?>
    </div>
<?php endif ?>
</div>
<div class="row pb-3">
    <div class="col-4">
        <strong><?= _("Description") ?></strong>
        <br/>
        <?= $ticket->getDescription() ?>
    </div>
</div>
<form method="post" action="?id=<?= $ticket->getId() ?>">
    <input type="hidden" name="action" value="edit"/>
    <div class="row pb-3">
        <div class="col">
            <label for="status" class="form-label"><strong><?= _("Status") ?></strong></label>
            <select name="status" id="status" class="form-select" multiple="multiple" size="<?= count($states) ?>">
                <?php foreach ($states as $i => $option): ?>
                <option value="<?= $option["id"] ?>" <?= $i ? "" : "selected=\"selected\"" ?>><?= $option["name"] ?></option>
                <?php endforeach ?>
            </select>
        </div>
        <div class="col">
            <label for="comment_text" class="form-label"><strong><?= _("Comment") ?></strong></label>
            <textarea name="comment_text" id="comment_text" class="form-control" rows="<?= count($states) ?>"></textarea>
        </div>
        <div class="col-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary"><?= _("Update") ?></button>
        </div>
    </div>
</form>

<?php foreach ($comments as $comment): ?>
<div class="row w-75 pb-3 pt-1 border-top">
    <div class="col">
        <strong><?= _("By") ?></strong>
        <br/>
        <?= $comment->getCreatorname() ?>
    </div>
    <div class="col">
        <strong><?= _("Date") ?></strong>
        <br/>
        <?= $comment->getCreationdate() ?>
    </div>
    <div class="col-1">
        <span class="badge text-bg-danger"><?= $comment->getViewed(Comment::ADMIN) ? _("NEW") : "" ?></span>
    </div>
</div>
<div class="row pb-3">
    <div class="col">
        <pre><?= $comment->getDescription() ?></pre>
    </div>
</div>

<?php
endforeach;
require_once __DIR__ . "/../templates/footer.php";
