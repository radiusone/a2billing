<?php

namespace A2billing;

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

Class Notification {

    public static int $ADMIN = 0;
    public static int $AGENT = 1;
    public static int $CUST = 2;
    public static int $BATCH = 3;
    public static int $SOAPSERVER = 4;
    public static int $UNKNOWN = -1;

    public static int $LOW = 0;
    public static int $MEDIUM = 1;
    public static int $HIGH = 2;

    public static string $LINK_NONE = "none";
    public static string $LINK_TICKET_CUST = "ticket_cust";
    public static string $LINK_TICKET_AGENT = "ticket_agent";
    public static string $LINK_REMITTANCE = "remittance";
    public static string $LINK_DID_DESTINATION = "did_destination";
    public static string $LINK_CARD = "card";

    private int $id;
    private string $date;
    private string $key;
    private int $priority;
    private int $from_type;
    private int $from_id;
    private bool $new;
    private ?string $link_type;
    private ?int $link_id;

    public function __construct(
        int $id,
        string $date,
        string $key,
        int $priority,
        int $from_type,
        int $from_id,
        ?int $link_id = null,
        ?string $link_type = null,
        $new = true
    )
    {
        $this->id = $id;
        $this->date = $date;
        $this->priority = $priority;
        $this->from_type = $from_type;
        $this->from_id = $from_id;
        $this->key = $key;
        $this->new = $new;
        $this->link_id = $link_id;
        $this->link_type = $link_type;
    }

    /**
     * Get a list of valid keys (seems to be notification types)
     *
     * @return array
     */
    public static function getAllKeys(): array
    {
        return [
            "sip_iax_changed" => _("New SIP & IAX added : Friends conf have to be generated"),
            "sip_changed" => _("New SIP added : Sip Friends conf have to be generated"),
            "iax_changed" => _("New IAX added : IAX Friends conf have to be generated"),
            "ticket_added_agent" => _("New Ticket added by agent"),
            "ticket_added_cust" => _("New Ticket added by customer"),
            "did_destination_edited_cust" => _("DID Destination edited by customer"),
            "remittance_added_agent" => _("New Remittance request added"),
            "added_new_signup" => _("Added new sign-up")
        ];
    }
    public function getId(): int
    {
        return $this->id;
    }

    public function getNew(): bool
    {
        return $this->new;
    }

    public function getDate(): string
    {
        return $this->date;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }
    public function getPriorityMsg(): string
    {
        switch ($this->priority) {
            case self::$HIGH: return _("HIGH");
            case self::$MEDIUM: return _("MEDIUM");
            case self::$LOW:
            default: return _("LOW");
        }
    }

    public function getFromType(): string
    {
        return $this->from_type;
    }

    public function getLinkType(): string
    {
        return $this->link_type;
    }

    public function getLinkId(): int
    {
        return $this->link_id;
    }

    public function getFromDisplay(): string
    {
        switch ($this->from_type) {
            case self::$ADMIN: return sprintf(_("ADMIN: %s"), Admin::getName($this->from_id));
            case self::$AGENT: return sprintf(_("AGENT: %s"), Agent::getName($this->from_id, false));
            case self::$CUST: return sprintf(_("CUST: %s"), Customer::getName($this->from_id, false));
            case self::$BATCH: return _("BATCH");
            case self::$SOAPSERVER: return _("SOAP-SERVER");
            default: return _("UNKNOWN");
        }
    }

    public function getFromId(): int
    {
        return $this->from_id;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getKeyMsg(): string
    {
        $keys = self::getAllKeys();

        return array_key_exists($this->key,$keys) ? $keys[$this->key] : $this->key;
    }

    public function getUrl(): string
    {
        switch ($this->link_type ?? "") {
            case self::$LINK_REMITTANCE: return "A2B_info_remittance.php?id=" . $this->link_id;
            case self::$LINK_DID_DESTINATION: return "A2B_entity_did_destination.php?form_action=ask-edit&id=" . $this->link_id;
            case self::$LINK_TICKET_CUST:
            case self::$LINK_TICKET_AGENT: return "CC_ticket_view.php?id=" . $this->link_id;
            case self::$LINK_CARD: return "A2B_entity_card.php?form_action=ask-edit&id=" . $this->link_id;
            case self::$LINK_NONE:
            default: return "";
        }
    }
}