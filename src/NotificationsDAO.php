<?php

namespace A2billing;

use Illuminate\Database\Query\Builder;

/**
 * This file is part of A2Billing (http://www.a2billing.net/)
 *
 * A2Billing, Commercial Open Source Telecom Billing platform,
 * powered by Star2billing S.L. <http://www.star2billing.com/>
 *
 * @copyright   Copyright © 2004-2015 - Star2billing S.L.
 * @copyright   Copyright © 2022-2025 RadiusOne Inc.
 * @author      Belaid Rachid <rachid.belaid@gmail.com>
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

class NotificationsDAO
{
    /**
     * Save a new notification
     *
     * @param string $key
     * @param int $priority
     * @param int $from_type
     * @param int $from_id
     * @param int|null $link_type
     * @param int|null $link_id
     * @return bool
     */
    public static function addNotification(
        string $key,
        int $priority,
        int $from_type,
        int $from_id = 0,
        int $link_type = null,
        int $link_id = null
    ): bool
    {

        return Connection::getConnection("cc_notification")->insert([
            "key_value" => $key,
            "priority" => $priority,
            "from_type" => $from_type,
            "from_id" => $from_id,
            "link_type" => $link_type,
            "link_id" => $link_id
        ]);
    }

    /**
     * Delete a notification
     *
     * @param int $id
     * @return bool
     */
    public static function deleteNotification(int $id): bool
    {
        if (Connection::getConnection("cc_notification_admin")->where(["id_notification" => $id])->delete()) {
            if (Connection::getConnection("cc_notification")->where(["id" => $id])->delete()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Mark a notification as read by a particular admin
     *
     * @param int $notification_id
     * @param int $admin_id
     * @return bool
     */
    public static function markNotificationRead(int $notification_id, int $admin_id = 0): bool
    {
        return Connection::getConnection("cc_notification_admin")
            ->insert(
                [
                    "id_notification" => $notification_id,
                    "id_admin" => $admin_id,
                    "viewed" => 1
                ]
            );
    }

    /**
     * Get the total number of notifications in the system
     * @return int
     */
    public static function getNotificationCount(): int
    {
        return Connection::getConnection("cc_notification")->count();
    }

    /**
     * Check if there are notifications the admin hasn't read
     *
     * @param int $admin_id
     * @return bool
     */
    public static function hasUnreadNotifications(int $admin_id): bool
    {
        return Connection::getConnection("cc_notification")
            ->leftJoin("cc_notification_admin", "cc_notification.id", "cc_notification_admin.id_notification")
            ->where("cc_notification_admin.id_admin", $admin_id)
            ->whereNull("viewed")
            ->exists();
    }

    /**
     * Get the list of notifications
     *
     * @param int $admin_id if > 0, each notification will be marked as read/unread for the given admin
     * @param int $current_page
     * @param int $page_count
     * @return array<Notification>
     */
    public static function getNotifications(int $admin_id = 0, int $current_page = 0, int $page_count = 10): array
    {
        return Connection::getConnection("cc_notification")
            ->leftJoin("cc_notification_admin", "cc_notification.id", "cc_notification_admin.id_notification")
            ->when($admin_id, fn (Builder $q) => $q->where("cc_notification_admin.id_admin", $admin_id))
            ->orderBy("date", "DESC")
            ->orderBy("id", "DESC")
            ->limit($page_count)
            ->offset(($current_page - 1) * $page_count)
            ->get()
            ->map(fn ($record) => new Notification(
                (int)$record["id"],
                $record["date"],
                $record["key_value"],
                (int)$record["priority"],
                (int)$record["from_type"],
                (int)$record["from_id"],
                (int)$record["link_id"],
                $record["link_type"],
                empty($record["viewed"])
            ))
            ->toArray();
    }
}
