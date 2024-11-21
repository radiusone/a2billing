<?php

namespace A2billing;

class Agent extends User
{
    public const ACX_ACCESS = 1;
    public const ACX_CUSTOMER = 1;
    public const ACX_BILLING = 2;
    public const ACX_RATECARD = 4;
    public const ACX_CALL_REPORT = 8;
    public const ACX_MYACCOUNT = 16;
    public const ACX_SUPPORT = 32;
    public const ACX_CREATE_CUSTOMER = 64;
    public const ACX_EDIT_CUSTOMER = 128;
    public const ACX_DELETE_CUSTOMER = 256;
    public const ACX_GENERATE_CUSTOMER = 512;
    public const ACX_SIGNUP = 1024;
    public const ACX_VOIPCONF = 2048;
    public const ACX_SEE_CUSTOMERS_CALLERID = 4096;

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
        if (($_SESSION["user_type"] ?? "") !== "AGENT") {
            return false;
        }
        if (!is_null($rights) && !has_rights($rights)) {
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
}
