<?php

namespace A2billing;

class Admin extends User
{
    public const ACX_NOACCESS = 0;
    public const ACX_CUSTOMER = 1;
    public const ACX_BILLING = 2;
    public const ACX_RATECARD = 4;
    public const ACX_TRUNK = 8;
    public const ACX_CALL_REPORT = 16;
    public const ACX_CRONT_SERVICE = 32;
    /**
     * Meaning is unclear; seems like it was intended for permissions to view sub-admins and agents
     * but is also checked on pages that have nothing to do with that. I suspect poor naming has
     * led to it being used as a general check for whether or not a user is an administrator
     */
    public const ACX_ADMINISTRATOR = 64;
    public const ACX_MAINTENANCE = 128;
    public const ACX_MAIL = 256;
    public const ACX_DID = 512;
    public const ACX_CALLBACK = 1024;
    public const ACX_OUTBOUNDCID = 2048;
    public const ACX_PACKAGEOFFER = 4096;
    public const ACX_PREDICTIVE_DIALER = 8192;
    public const ACX_INVOICING = 16384;
    public const ACX_SUPPORT = 32768;
    public const ACX_DASHBOARD = 65536;
    public const ACX_ACXSETTING = 131072;
    public const ACX_MODIFY_REFILLS = 262144;
    public const ACX_MODIFY_PAYMENTS = 524288;
    public const ACX_MODIFY_CUSTOMERS = 1048576;
    public const ACX_DELETE_NOTIFICATIONS = 2097152;
    public const ACX_DELETE_CDR = 4194304;
    public const ACX_MODIFY_ADMINS = 8388608;
    public const ACX_MODIFY_AGENTS = 16777216;
    public const ACX_ALL_RIGHTS = 2 ^ 32 - 1;

    /** @var array|string[] pages that don't require authentication */
    private static array $open_pages = [
        "index.php",
        "logout.php",
        "PP_error.php",
    ];

    /**
     * Check a user's permission bits
     *
     * @param int|null $rights the rights to check, or null to just check if user is admin
     * @return bool
     */
    public static function allowed(?int $rights): bool
    {
        if (($_SESSION["user_type"] ?? "") !== "ADMIN") {
            return false;
        }
        if (intval($_SESSION["rights"] ?? 0) & self::ACX_ALL_RIGHTS) {
            return true;
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
     * Get an agent name from its ID
     *
     * @param numeric-string|null $id
     * @return string
     */
    public static function getName(?string $id): string
    {
        $na = _("n/a");
        if (empty($id)) {
            return $na;
        }
        $row = (new Table("cc_ui_authen", ["name", "login"]))
            ->getRow(["userid" => $id]);
        if ($row) {
            return sprintf("%s (%s)", $row["name"], $row["login"]);
        }

        return $na;
    }
}
