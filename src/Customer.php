<?php

namespace A2billing;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Exception;

class Customer extends User
{
    public const ACX_ACCESS = 1;
    public const ACX_PASSWORD = 2;
    public const ACX_SIP_IAX = 4;
    public const ACX_CALL_HISTORY = 8;
    public const ACX_PAYMENT_HISTORY = 16;
    public const ACX_VOUCHER = 32;
    public const ACX_INVOICES = 64;
    public const ACX_DID = 128;
    public const ACX_SPEED_DIAL = 256;
    public const ACX_RATECARD = 512;
    public const ACX_SIMULATOR = 1024;
    public const ACX_CALL_BACK = 2048;
    public const ACX_WEB_PHONE = 4096;
    public const ACX_CALLER_ID = 8192;
    public const ACX_SUPPORT = 16384;
    public const ACX_NOTIFICATION = 32768;
    public const ACX_AUTODIALER = 65536;
    public const ACX_PERSONALINFO = 131072;
    public const ACX_SEERECORDING = 262144;


    /** @var array|string[] pages that don't require authentication */
    private static array $open_pages = [
        "index.php",
        "logout.php",
        "PP_error.php",
        "A2B_info_card.php",
    ];

    /**
     * Check a user's permission bits
     *
     * @param int|null $rights the rights to check, or null to just check if user is admin
     * @return bool
     */
    public static function allowed(?int $rights): bool
    {
        if (($_SESSION["user_type"] ?? "") !== "CUST") {
            return false;
        }
        if (!is_null($rights) && !(intval($_SESSION["rights"] ?? 0) & $rights)) {
            return false;
        }

        return true;
    }

    /**
     * Check a user's right to access a page, and redirect to HTTP error page if not allowed
     *
     * @param int|null $rights any additional user rights to check
     * @return void
     */
    public static function checkPageAccess(?int $rights = null): void
    {
        $page = basename($_SERVER["PHP_SELF"]);
        if (
            !in_array($page, self::$open_pages)
            && !self::allowed($rights)
        ) {
            header("HTTP/1.0 401 Unauthorised");
            header("Location: PP_error.php?c=accessdenied");
            die();
        }
    }

    /**
     * Get a customer name from its ID
     *
     * @param numeric-string|null $id
     * @param bool $as_link return an HTML string with a link to the user edit page
     * @return string
     */
    public static function getName(?string $id, bool $as_link = true): string
    {
        $na = _("n/a");
        if (empty($id) || !is_numeric($id)) {
            return $na;
        }
        $row = (new Table("cc_card", ["username", "firstname", "lastname"]))
            ->getRow(["id" => $id]);
        if (!$row) {
            return $na;
        }
        if ($as_link) {
            return sprintf(
                "<a href=\"A2B_entity_card.php?form_action=ask-edit&amp;id=%d\" title=\"%s\">%s %s</a>",
                $id,
                htmlspecialchars($row["username"]),
                htmlspecialchars($row["firstname"]),
                htmlspecialchars($row["lastname"])
            );
        }

        return sprintf("%s %s (%s)", $row["firstname"], $row["lastname"], $row["username"]);
    }

    /**
     * Get a customer username from its ID
     *
     * @param numeric-string|null $id
     * @param bool $as_link return an HTML string with a link to the user edit page
     * @return string
     */
    public static function getUsername(?string $id, bool $as_link = true): string
    {
        $na = _("n/a");
        if (empty($id) || !is_numeric($id)) {
            return $na;
        }
        $row = (new Table("cc_card", ["username", "firstname", "lastname"]))
            ->getRow(["id" => $id]);
        if (!$row) {
            return $na;
        }
        if ($as_link) {
            return sprintf(
                "<a href=\"A2B_entity_card.php?form_action=ask-edit&amp;id=%d\">%s</a>",
                $id,
                htmlspecialchars($row["username"])
            );
        }

        return $row["username"];
    }

    /**
     * Get the logged in customer's ID
     *
     * @return int
     */
    public static function id(): int
    {
        return intval($_SESSION["card_id"] ?? 0);
    }

    /**
     * Get an HTML link to the customer info page
     *
     * @param numeric-string|null $id
     * @param bool $as_link
     * @return string
     */
    public static function getInfoLink(?string $id): string
    {
        $na = _("n/a");
        if (empty($id) || !is_numeric($id)) {
            return $na;
        }
        $row = (new Table("cc_card", ["username", "firstname", "lastname"]))
            ->getRow(["id" => $id]);
        if (!$row) {
            return $na;
        }
        return sprintf(
            "<a href=\"A2B_info_card.php?id=%d\">%s %s (%s)</a>",
            $id,
            htmlspecialchars($row["firstname"]),
            htmlspecialchars($row["lastname"]),
            htmlspecialchars($row["username"])
        );
    }

    /**
     * Takes a date in system timezone and applies the user timezone to it
     *
     * @param string|DateTimeInterface $date
     * @return DateTimeInterface
     */
    public static function date($date): DateTimeInterface
    {
        if ($date instanceof DateTimeInterface) {
            $date = clone $date;
        } else {
            try {
                $date = new DateTimeImmutable($date);
            } catch (Exception $e) {
                $date = new DateTimeImmutable();
            }
        }

        try {
            $user_zone = new DateTimeZone($_SESSION["zone"]);
        } catch (Exception $e) {
            return $date;
        }

        return $date->setTimezone($user_zone);
    }

    /**
     * @param string $user
     * @param string $pass
     * @return false|array<string,string>
     * @todo store passwords properly
     */
    public static function checkLogin(string $user, string $pass)
    {
        $user = trim($user);
        $pass = trim($pass);

        if (empty($user) || empty($pass)) {
            return false;
        }

        $table = new Table(
            "cc_card",
            ["username", "credit", "status", "cc_card.id", "id_didgroup", "tariff", "vat", "zone", "voicemail_permitted", "voicemail_activated", "users_perms", "currency", "uipass"],
            [
                "cc_timezone" => ["id_timezone", "cc_timezone.id"],
                "cc_card_group" => ["id_group", "cc_card_group.id"]
            ]
        );
        $row = $table->getRow([["SUB", ["email" => $user, "useralias" => $user], "OR"]]);

        return (in_array($row["status"] ?? "", ["t", 1, 8]) && "$row[uipass]" === "$pass")
            ? $row
            : false;
    }
}
