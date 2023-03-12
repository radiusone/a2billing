<?php

namespace A2billing;

class Admin extends User
{
    public const ACX_CUSTOMER = 1;
    public const ACX_BILLING = 2;
    public const ACX_RATECARD = 4;
    public const ACX_TRUNK = 8;
    public const ACX_CALL_REPORT = 16;
    public const ACX_CRONT_SERVICE = 32;
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

    private static array $open_pages = [
        "index.php",
        "logout.php",
        "PP_error.php",
    ];

    public static function allowed(int $rights): bool
    {
        if (($_SESSION["user_type"] ?? "") !== "ADMIN") {
            return false;
        }
        if (!has_rights($rights)) {
            return false;
        }

        return true;
    }

    public static function checkPageAccess(int $rights = 0): void
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
}