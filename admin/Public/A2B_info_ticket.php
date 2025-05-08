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

$menu_section = 4;
require_once __DIR__ . "/../../common/lib/admin.defines.php";

Admin::checkPageAccess(Admin::ACX_SUPPORT);

getpost_ifset(["result", "action", "status", "id", "comment_text"]);
/**
 * @var string|null $result
 * @var numeric-string|null $id
 * @var string|null $action
 * @var numeric-string|null $status
 * @var string $comment_text
 */

$ticket = Ticket::getTicket($id ?? 0);
if (!$ticket) {
    exit(_("Ticket ID not found"));
}

if (($action ?? "") === "change") {
    $ticket->setStatus($status);
    if ($comment_text) {
        $ticket->insertComment($comment_text, $_SESSION["agent_id"], Comment::ADMIN);
    }
    header("Location: A2B_info_ticket.php?id=$id&result=success");
    die();
}

require_once __DIR__ . "/../templates/main.php";
?>
    <div class="row pb-3 gx-5">
        <div class="col-6">
            <table class="table table-sm caption-top">
                <caption class="fw-bold fs-5">
                    <?= _("Ticket Info") ?>
                    <?php if ($ticket->getViewed(Ticket::ADMIN)):?>
                        <span class="badge text-bg-danger"><?= _("NEW") ?></span>
                    <?php endif ?>
                    <?php $ticket->markViewed(Ticket::ADMIN) ?>
                </caption>
                <tbody>
                <tr>
                    <th scope="row"><?= _("Ticket number") ?></th>
                    <td><?= $ticket->getId() ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Title") ?></th>
                    <td><?= $ticket->getTitle() ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Creator") ?></th>
                    <td><?= $ticket->getCreatorname() ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Priority") ?></th>
                    <td><?= $ticket->getPriorityDisplay() ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Date") ?></th>
                    <td><?= $ticket->getCreationdate() ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Component") ?></th>
                    <td><?= $ticket->getComponentname() ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Description") ?></th>
                    <td><?= $ticket->getDescription() ?></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
<?php if (($result ?? "") === "success"): ?>
    <div class="alert alert-success" role="alert">
        <p><?= _("Ticket updated successfully") ?></p>
    </div>
<?php endif ?>

    <div class="row pb-3 gx-5">
        <form action="?id=<?= $ticket->getId() ?>" method="post" class="col-6">
            <div class="mb-3">
                <label for="status" class="form-label form-label-sm"><?= _("Status") ?></label>
                <select name="status" id="status" class="form-select form-select-sm">
                    <?php foreach (Ticket::getPossibleStatus($ticket->getStatus(),true) as $state): ?>
                        <option value="<?= $state["id"] ?>"><?= $state["name"] ?></option>
                    <?php endforeach ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="comment_text" class="form-label form-label-sm"><?= _("Comment") ?></label>
                <textarea name="comment_text" id="comment_text" class="form-control form-control-sm" rows="5"></textarea>
            </div>
            <div class="my-4">
                <button type="submit" name="action" value="change" class="btn btn-sm btn-primary"><?= _("Update") ?></button>
            </div>
        </form>
    </div>

    <div class="row pb-3 gx-5">
        <div class="col">
            <table class="table table-sm caption-top">
                <caption class="fw-bold fs-5"><?= _("Comments") ?></caption>
                <thead>
                <tr>
                    <th scope="col"><?= _("Creator") ?></th>
                    <th scope="col"><?= _("Date") ?></th>
                    <th scope="col"><?= _("Comment") ?></th>
                    <th scope="col"><?= _("Status") ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($ticket->loadComments() as $comment): ?>
                    <tr>
                        <td><?= $comment->getCreatorname() ?></td>
                        <td><?= $comment->getCreationdate() ?></td>
                        <td><?= $comment->getDescription() ?></td>
                        <td>
                            <?php if($comment->getViewed(Comment::ADMIN)): ?>
                                <span class="badge text-bg-danger"><?= _("NEW") ?></span>
                            <?php else: ?>
                                <span class="badge text-bg-primary"><?= _("VIEWED") ?></span>
                            <?php endif ?>
                            <?php $comment->markViewed(Comment::ADMIN) ?>
                        </td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>

<?php

require_once __DIR__ . "/../templates/footer.php";
