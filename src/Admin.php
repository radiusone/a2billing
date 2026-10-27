<?php

namespace A2billing;

class Admin extends User
{
    /** @var int what it says on the tin */
    public const ACX_NOACCESS = 0;
    /** @var int view customer data */
    public const ACX_CUSTOMER = 2 ** 0;
    /** @var int view refills, commission, payments, etc */
    public const ACX_BILLING = 2 ** 1;
    /** @var int manage call plans, rate cards, and rates */
    public const ACX_RATECARD = 2 ** 2;
    /** @var int manage providers, trunks, and prefixes */
    public const ACX_TRUNK = 2 ** 3;
    /** @var int view reports */
    public const ACX_CALL_REPORT = 2 ** 4;
    /** @var int manage "recurring services" */
    public const ACX_CRONT_SERVICE = 2 ** 5;
    /** @var int view agents and other administrators */
    public const ACX_ADMINISTRATOR = 2 ** 6;
    /** @var int manage maintenance scripts, cron, etc */
    public const ACX_MAINTENANCE = 2 ** 7;
    /** @var int manage mail templates and send mail out */
    public const ACX_MAIL = 2 ** 8;
    /** @var int manage inbound and outbound DIDs */
    public const ACX_DID = 2 ** 9;
    /** @var int manage callback services, whatever that means */
    public const ACX_CALLBACK = 2 ** 10;
    /** @var int manage CIDs and groups */
    public const ACX_OUTBOUNDCID = 2 ** 11;
    /** @var int manage packages */
    public const ACX_PACKAGEOFFER = 2 ** 12;
    /** @var int unused */
    public const ACX_PREDICTIVE_DIALER = 2 ** 13;
    /** @var int manage and send invoices and receipts */
    public const ACX_INVOICING = 2 ** 14;
    /** @var int manage support boxes and tickets */
    public const ACX_SUPPORT = 2 ** 15;
    /** @var int view the dashboard */
    public const ACX_DASHBOARD = 2 ** 16;
    /** @var int manage global settings */
    public const ACX_ACXSETTING = 2 ** 17;
    /** @var int add, edit, delete refills */
    public const ACX_MODIFY_REFILLS = 2 ** 18;
    /** @var int add, edit, delete payments */
    public const ACX_MODIFY_PAYMENTS = 2 ** 19;
    /** @var int add, edit, delete users */
    public const ACX_MODIFY_CUSTOMERS = 2 ** 20;
    /** @var int delete notifications */
    public const ACX_DELETE_NOTIFICATIONS = 2 ** 21;
    /** @var int delete calls */
    public const ACX_DELETE_CDR = 2 ** 22;
    /** @var int add, edit, delete admins */
    public const ACX_MODIFY_ADMINS = 2 ** 23;
    /** @var int add, edit, delete agents */
    public const ACX_MODIFY_AGENTS = 2 ** 24;
    public const ACX_ALL_RIGHTS = 2 ** 32;

    /** @var array|string[] pages that don't require authentication */
    private static array $open_pages = [
        "index.php",
        "login.php",
        "logout.php",
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
            header("Location: index.php?c=accessdenied");
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
        $row = Connection::getConnection("cc_ui_authen", "name", "login")
            ->where("userid", $id)
            ->first();
        if ($row) {
            return sprintf("%s (%s)", $row["name"], $row["login"]);
        }

        return $na;
    }

    /**
     * Get the logged in admin's ID
     *
     * @return int
     */
    public static function id(): int
    {
        return intval($_SESSION["admin_id"] ?? 0);
    }

    /**
     * @param string $user
     * @param string $pass
     * @return false|array<string,string>
     */
    public static function checkLogin(string $user, string $pass): array|false
    {
        $user = trim($user);
        $pass = trim($pass);

        if (empty($user) || empty($pass)) {
            return false;
        }

        $row = Connection::getConnection("cc_ui_authen", "userid", "perms", "confaddcust", "groupid", "login", "pwd_encoded")
            ->where("login", $user)
            ->first();

        if ($row && password_verify($pass, $row["pwd_encoded"])) {
            return (array)$row;
        }

        return false;
    }
}
