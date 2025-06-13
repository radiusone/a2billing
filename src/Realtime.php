<?php

namespace A2billing;

use ADOConnection;

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

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

class Realtime
{
    private ADOConnection $DBHandler;

    public function __construct()
    {
        $this->DBHandler = DbConnect();
    }

    /**
     * @param 'sip'|'iax' $type
     */
    public function create_trunk_config_file (string $type = "sip"): void
    {
        if (USE_REALTIME || ($type !== "sip" && $type !== "iax")) {
            return;
        }

        $sip_cols = [
            "name", "accountcode", "regexten", "amaflags", "callgroup", "callerid", "canreinvite", "context", "DEFAULTip",
            "dtmfmode", "fromuser", "fromdomain", "host", "insecure", "language", "mailbox", "md5secret", "nat", "deny",
            "permit", "mask", "pickupgroup", "port", "qualify", "restrictcid", "rtptimeout", "rtpholdtimeout", "secret",
            "type", "username", "disallow", "allow", "musiconhold", "regseconds", "ipaddr", "cancallforward", "fullcontact",
            "setvar", "lastms", "regserver", "defaultuser", "auth", "subscribemwi", "vmexten", "cid_number", "callingpres",
            "usereqphone", "incominglimit", "subscribecontext", "musicclass", "mohsuggest", "allowtransfer", "autoframing",
            "maxcallbitrate", "outboundproxy", "rtpkeepalive",
        ];
        $iax_cols = ["name", "accountcode", "regexten", "amaflags", "callerid", "context", "DEFAULTip", "host", "language",
            "mask", "port", "qualify", "secret", "username", "disallow", "allow", "regseconds", "ipaddr", "trunk", "dbsecret",
            "regcontext", "sourceaddress", "mohinterpret", "mohsuggest", "inkeys", "outkey", "cid_number", "sendani",
            "fullname", "auth", "maxauthreq", "encryption", "transfer", "jitterbuffer", "forcejitterbuffer", "codecpriority",
            "qualifysmoothing", "qualifyfreqok", "qualifyfreqnotok", "timezone", "adsi", "setvar", "type", "deny", "permit",
            "requirecalltoken", "maxcallnumbers", "maxcallnumbers_nonvalidated",
        ];

        if ($type === "iax") {
            $buddyfile = BUDDY_IAX_FILE;
            $table_name = "cc_iax_buddies";
            $cols = $iax_cols;
        } else {
            $buddyfile = BUDDY_SIP_FILE;
            $table_name = "cc_sip_buddies";
            $cols = $sip_cols;
        }

        $instance_table = new Table($table_name, $cols);
        $list_friend = $instance_table->getRows();
        // todo: once all queries are associative this won't be needed
        $list_friend = array_filter($list_friend, fn($k) => !is_numeric($k), ARRAY_FILTER_USE_KEY);

        if ($list_friend) {
            if (file_exists($buddyfile) && is_writable($buddyfile)) {
                foreach ($list_friend as $row) {
                    $line = "\n\n[$row[accountcode]]\n";
                    foreach ($row as $key => $value) {
                        if ($key === "allow") {
                            foreach(explode(",", $value) as $codec) {
                                $line .= "allow=$codec\n";
                            }
                        } else {
                            $line .= "$key=$value";
                        }
                    }
                    file_put_contents($buddyfile, $line);
                }
            }
        }
    }

    public function insert_voip_config (bool $sip, bool $iax, int $id_card, string $accountnumber, string $passui_secret): void
    {
        if (!$sip && !$iax) {
            return;
        }

        if (!USE_REALTIME) {
            if($sip && $iax) {
                $key = "sip_iax_changed";
            }
            elseif ($sip) {
                $key = "sip_changed";
            }
            else {
                $key = "iax_changed";
            }

            //check who
            if (is_admin()) {
                $who = Notification::$ADMIN;
                $who_id = $_SESSION["admin_id"];
            } elseif (is_agent()) {
                $who = Notification::$AGENT;
                $who_id = $_SESSION["agent_id"];
            } else {
                $who = Notification::$UNKNOWN;
                $who_id = -1;
            }
            NotificationsDAO::addNotification($key, Notification::$HIGH, $who, $who_id);
        } else {
            $_SESSION["is_sip_iax_change"] = 1;
            $_SESSION["is_sip_changed"] = (int)$sip;
            $_SESSION["is_iax_changed"] = (int)$iax;
        }

        $values = [
            "type" => FRIEND_TYPE,
            "allow" => str_replace(" ", "", FRIEND_ALLOW),
            "context" => FRIEND_CONTEXT,
            "nat" => FRIEND_NAT,
            "amaflags" => FRIEND_AMAFLAGS,
            "qualify" => FRIEND_QUALIFY,
            "host" => FRIEND_HOST,
            "dtmfmode" => FRIEND_DTMFMODE,
            "name" => $accountnumber,
            "accountcode" => $accountnumber,
            "regexten" => $accountnumber,
            "callerid" => "",
            "username" => $accountnumber,
            "secret" => $passui_secret,
            "id_cc_card" => $id_card,
        ];

        if ($sip) {
            (new Table("cc_sip_buddies"))->addRow($values);
        }

        if ($iax) {
            unset($values["dtmfmode"], $values["nat"]);
            (new Table("cc_iax_buddies"))->addRow($values);
        }
    }
}
