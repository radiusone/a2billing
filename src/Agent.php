<?php

namespace A2billing;

class Agent extends User
{
    public const ACX_NOACCESS = 0;
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
        "PP_intro.php",
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
     * @param bool $as_link return an HTML string with a link to the agent edit page
     * @return string
     */
    public static function getName(?string $id, bool $as_link = true): string
    {
        $na = _("n/a");
        if (empty($id) || !is_numeric($id)) {
            return $na;
        }
        $row = (new Table("cc_agent", ["login", "firstname", "lastname"]))
            ->getRow(["id" => $id]);
        if (!$row) {
            return $na;
        }

        if ($as_link) {
            return sprintf(
                "<a href=\"A2B_entity_agent.php?form_action=ask-edit&amp;id=%d\" title=\"%s\">%s %s</a>",
                $id,
                htmlspecialchars($row["login"]),
                htmlspecialchars($row["firstname"]),
                htmlspecialchars($row["lastname"])
            );
        }

        return sprintf(_("%s %s (login: %s)"), $row["firstname"], $row["lastname"], $row["login"]);
    }


    /**
     * @param string $user
     * @param string $pass
     * @return false|string[]
     */
    public static function checkLogin(string $user, string $pass)
    {
        $user = trim($user);
        $pass = trim($pass);

        if (empty($user) || empty($pass)) {
            return false;
        }

        $table = new Table("cc_agent", ["id", "perms", "active", "currency", "vat", "pwd_encoded"]);
        $row = $table->getRow(["login" => $user]);

        if ($row) {
            if ($row["active"] !== "t" && $row["active"] !== "1") {
                return false;
            }
            if (password_verify($pass, $row["pwd_encoded"])) {
                return $row;
            }
            // fallback to ugly legacy authentication
            $filterpass = htmlspecialchars($pass);
            if (hash("whirlpool", $filterpass) === $row["pwd_encoded"] || $filterpass === $row["pwd_encoded"]) {
                $table->updateRow(
                    ["pwd_encoded" => password_hash($pass, PASSWORD_DEFAULT)],
                    ["login" => $user]
                );

                return $row;
            }
        }

        return false;
    }
}
