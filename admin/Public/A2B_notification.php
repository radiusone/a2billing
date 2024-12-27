<?php

use A2billing\Admin;
use A2billing\Notification;
use A2billing\NotificationsDAO;

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

require_once __DIR__ . "/../../common/lib/admin.defines.php";

getpost_ifset(["id", "page", "action", "ids"]);
/**
 * @var string|int $id
 * @var string|int $page
 * @var string $action
 * @var array $ids
 */

$id = (int)($id ?? 0);
$page = (int)($page ?? 1);
$ids ??= [];
$admin_id = (int)$_SESSION["admin_id"];

if (!empty($action)) {
    $result = false;
    switch ($action) {
        case "viewall":
            if (count($ids) > 0) {
                $values = [];
                foreach ($ids as $notification_id) {
                    if (!($result = NotificationsDAO::markNotificationRead((int)$notification_id, $admin_id))) {
                        break(2);
                    }
                }
            }
            break;
        case "view":
            if ($id > 0) {
                $result = NotificationsDAO::markNotificationRead($id, $admin_id);
            }
            break;
        case "delete":
            if ($id > 0 && has_rights(Admin::ACX_DELETE_NOTIFICATIONS)) {
                $result = NotificationsDAO::deleteNotification($id);
            }
            break;
        default:
            break;
    }
    header("Content-Type: application/json");
    echo json_encode($result);
    die();
}

$menu_section = 0;
require_once __DIR__ . "/../templates/main.php";
echo create_help(_("Notification: You can see below all notifications received about some event."));

$nb_per_page = 15;
$nb_total = NotificationsDAO::getNotificationCount();
$nb_page = ceil($nb_total / $nb_per_page);
$list = NotificationsDAO::getNotifications($admin_id, $page, $nb_per_page);

if ($nb_total === 0) {
    $empty = _("No Notifications");
    echo <<< HTML
    <div class="row">
        <div class="col">
            <strong>$empty</strong>
        </div>
    </div>
    HTML;
    require_once __DIR__ . "/../templates/footer.php";
    die();
}
?>

<?php if (NotificationsDAO::hasUnreadNotifications($admin_id)): ?>
<div class="row pb-3">
    <div class="col text-center">
        <button class="btn btn-sm btn-outline-primary" id="mark-notifications" type="button">
            <?= _("Mark all viewed") ?>
        </button>
    </div>
</div>
<?php endif ?>

<div class="row pb-3">
    <div class="col">
        <table class="table" id="notification-table" data-page="<?= $page ?>" data-delete-prompt="<?= _("Do you want delete this notification ?") ?>">
            <thead>
                <tr>
                    <th><?= _("DATE") ?></th>
                    <th><?= _("FROM") ?></th>
                    <th><?= _("SUBJECT") ?></th>
                    <th><?= _("PRIORITY") ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
<?php foreach ($list as $notification): /** @var Notification $notification */ ?>
                <tr
                    data-notification-id="<?= $notification->getId() ?>"
                    data-notification-new="<?= (int)$notification->getNew() ?>"
                    class="table-<?= $notification->getPriority() == 2 ? "danger" : ($notification->getPriority() == 1 ? "success" : "secondary") ?> <?= $notification->getNew() ? "fw-bold" : "" ?>"
                >
                    <td><?= $notification->getDate() ?></td>
                    <td><?= $notification->getFromDisplay() ?></td>
                    <td>
                        <?= $notification->getKeyMsg() ?>
    <?php if (($url = $notification->getUrl())): ?>
                        <a href="<?= $url ?>">
                            <img alt="link to notification" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAQAAAC1+jfqAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAADpSURBVCjPY/jPgB8y0EmBHXdWaeu7ef9rHuaY50jU3J33v/VdVqkdN1SBEZtP18T/L/7f/X/wf+O96kM3f9z9f+T/xP8+XUZsYAWGfsUfrr6L2Ob9J/X/pP+V/1P/e/+J2LbiYfEHQz+ICV1N3yen+3PZf977/9z/Q//X/rf/7M81Ob3pu1EXWIFuZvr7aSVBOx1/uf0PBEK3/46/gnZOK0l/r5sJVqCp6Xu99/2qt+v+T/9f+L8CSK77v+pt73vf65qaYAVqzPYGXvdTvmR/z/4ZHhfunP0p+3vKF6/79gZqzPQLSYoUAABKPQ+kpVV/igAAAABJRU5ErkJggg=="/>
                        </a>
    <?php endif ?>
                    </td>
                    <td><?= $notification->getPriorityMsg() ?></td>
                    <td>
    <?php if ($notification->getNew()): ?>
                        <span class="badge"><?= _("NEW") ?></span>
    <?php elseif (has_rights(Admin::ACX_DELETE_NOTIFICATIONS)): ?>
                        <button class="btn btn-sm delete_notification" type="button" data-notification-id="<?= $notification->getId() ?>" title="<?= _("Delete this Notification") ?>">
                            <img alt="" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAIhSURBVDjLlZPrThNRFIWJicmJz6BWiYbIkYDEG0JbBiitDQgm0PuFXqSAtKXtpE2hNuoPTXwSnwtExd6w0pl2OtPlrphKLSXhx07OZM769qy19wwAGLhM1ddC184+d18QMzoq3lfsD3LZ7Y3XbE5DL6Atzuyilc5Ciyd7IHVfgNcDYTQ2tvDr5crn6uLSvX+Av2Lk36FFpSVENDe3OxDZu8apO5rROJDLo30+Nlvj5RnTlVNAKs1aCVFr7b4BPn6Cls21AWgEQlz2+Dl1h7IdA+i97A/geP65WhbmrnZZ0GIJpr6OqZqYAd5/gJpKox4Mg7pD2YoC2b0/54rJQuJZdm6Izcgma4TW1WZ0h+y8BfbyJMwBmSxkjw+VObNanp5h/adwGhaTXF4NWbLj9gEONyCmUZmd10pGgf1/vwcgOT3tUQE0DdicwIod2EmSbwsKE1P8QoDkcHPJ5YESjgBJkYQpIEZ2KEB51Y6y3ojvY+P8XEDN7uKS0w0ltA7QGCWHCxSWWpwyaCeLy0BkA7UXyyg8fIzDoWHeBaDN4tQdSvAVdU1Aok+nsNTipIEVnkywo/FHatVkBoIhnFisOBoZxcGtQd4B0GYJNZsDSiAEadUBCkstPtN3Avs2Msa+Dt9XfxoFSNYF/Bh9gP0bOqHLAm2WUF1YQskwrVFYPWkf3h1iXwbvqGfFPSGW9Eah8HSS9fuZDnS32f71m8KFY7xs/QZyu6TH2+2+FAAAAABJRU5ErkJggg=="/>
                        </button>
    <?php endif ?>
                    </td>
                </tr>
<?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($nb_page > 1): ?>
<div class="row pb-3">
    <div class="col">
        <nav aria-label="<?= _("page navigation") ?>">
            <ul class="pagination justify-content-center">
                <li class="page-item <?= $page <= 1 ? "disabled" : "" ?>">
                    <a class="page-link" href="?page=1"><?= _("First") ?></a>
                </li>
                <li class="page-item <?= $page <= 1 ? "disabled" : "" ?>">
                    <a class="page-link" href="?page=<?= $page - 1 ?>"><?= _("Newer") ?></a>
                </li>
    <?php for ($i = 1; $i <= $nb_page; $i++): ?>
                <li class="page-item <?= $page === $i ? "active" : "" ?>" <?= $page === $i ? "aria-current=\"page\"" : "" ?>>
                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                </li>
    <?php endfor ?>
                <li class="page-item <?= $page >= $nb_page ? "disabled" : "" ?>">
                    <a class="page-link" href="?page=<?= $page + 1 ?>"><?= _("Older") ?></a>
                </li>
                <li class="page-item <?= $page >= $nb_page ? "disabled" : "" ?>">
                    <a class="page-link" href="?page=<?= $nb_page ?>"><?= _("Last") ?></a>
                </li>
            </ul>
        </nav>
    </div>
</div>
<?php endif ?>

<script>
document.getElementById("mark-notifications")?.addEventListener("click", function() {
    /** @var {HTMLTableElement} */
    let table = document.getElementById("notification-table");
    let body = new FormData();
    table.querySelectorAll("tr[data-notification-new='1']").forEach(el => body.append("ids[]", el.dataset.notificationId));
    fetch("A2B_notification.php?action=viewall", {method: "POST", body: body})
        .then(data => data.json())
        .then(result => result ? location.reload() : alert("error"));
});
document.querySelectorAll("button.delete_notification").forEach(el => el.addEventListener("click", function() {
    /** @var {HTMLTableElement} */
    let table = document.getElementById("notification-table");
    let prompt = table.dataset.deletePrompt;
    let body = new FormData();
    body.append("id", this.dataset.notificationId);
    if (confirm(prompt)) {
        fetch("A2B_notification.php?action=delete", {method: "POST", body: body})
            .then(data => data.json())
            .then(result => result ? location.reload() : alert("error"));
    }
}));
</script>
<?php
require_once __DIR__ . "/../templates/footer.php";
