<?php

namespace A2billing\Forms;

class Validator
{
    /**
     * validate_field 0
     *
     * @param string $value
     * @return string|true
     */
    public static function min3Chars(string $value)
    {
        return strlen($value) >= 3
            ?: sprintf(_("(must be at least %d characters)"), 3);
    }

    /**
     * validate_field 1
     *
     * @param string $value
     * @return bool|string
     */
    public static function email(string $value)
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) === $value
            ?: _("(must be a valid email address");
    }

    /**
     * validate_field 3
     *
     * @param string $value
     * @return bool|string
     */
    public static function min4Chars(string $value)
    {
        return strlen($value) >= 4
            ?: sprintf(_("(must be at least %d characters)"), 4);
    }

    /**
     * validate_field 4
     *
     * @param string $value
     * @return bool|string
     */
    public static function numeric(string $value)
    {
        return preg_match("/^[0-9]+$/", $value) === 1
            ?: _("(must consist only of numbers)");
    }

    /**
     * validate_field 5
     *
     * @param string $value
     * @return bool|string
     */
    public static function date(string $value)
    {
        return (
            preg_match("/^(19|20)[0-9]{2}(\\b)(0[1-9]|1[0-2])\\2(0[1-9]|[12][0-9]|3[01])$/", $value, $m)
            && \DateTime::createFromFormat("Y-m-d", "$m[1]-$m[3]-$m[4]")
        )
            ?: _("(must be a date in YYYY-MM-DD format)");
    }

    /**
     * validate_field 7
     *
     * @param string $value
     * @return bool|string
     */
    public static function min8DigitLike(string $value)
    {
        return preg_match("/^[0-9][0-9. \\/-]{6,}[0-9]$/", $value) === 1
            ?: _("(must be a number at least 8 digits long – can include dots, dashes, or spaces)");
    }

    /**
     * validate_field 8
     *
     * @param string $value
     * @return bool|string
     */
    public static function min5Chars(string $value)
    {
        return strlen($value) >= 5
            ?: sprintf(_("(must be at least %d characters)"), 5);
    }

    /**
     * validate_field 9
     *
     * @param string $value
     * @return bool|string
     */
    public static function min1Char(string $value)
    {
        return strlen($value) >= 1
            ?: _("(must be at least 1 character)");
    }

    /**
     * validate_field 10
     *
     * @param string $value
     * @return bool|string
     */
    public static function dateTime(string $value)
    {
        return (
            preg_match("/^((?:19|20)[0-9]{2})-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01]) ([01][0-9]|2[0-3]):([0-5][0-9])(:[0-5][0-9])?$/", $value, $m)
            && \DateTime::createFromFormat("Y-m-d H:i", "$m[1]-$m[2]-$m[3] $m[4]:$m[5]")
        )
            ?: _("(must be a date/time in YYYY-MM-DD hh:mm format)");

    }

    /**
     * validate_field 11
     *
     * @param string $value
     * @return bool|string
     */
    public static function min2Chars(string $value)
    {
        return strlen($value) >= 2
            ?: sprintf(_("(must be at least %d characters)"), 2);
    }

    /**
     * validate_field 12
     *
     * @param string $value
     * @return bool|string
     */
    public static function number(string $value)
    {
        return preg_match("/^-?[0-9]+(\\.?[0-9]+)?$/", $value) === 1
            ?: _("(must be a number – use . for decimals)");
    }

    /**
     * validate_field 13
     *
     * @param string $value
     * @return bool|string
     */
    public static function asteriskExtension(string $value)
    {
        return preg_match("/^(defaultprefix|[-,0-9]+|_(\[\\d+(-\\d+)?]|[0-9XZN])+[.!]?)$/", $value) === 1
            ?: _("(must be a number, Asterisk pattern, or the special value 'defaultprefix')");
    }

    /**
     * validate_field 14
     *
     * @param string $value
     * @return bool|string
     */
    public static function numericOrAll(string $value)
    {
        return preg_match("/^([0-9]+|all)$/", $value) === 1
            ?: _("(must be digits or the special value 'all')");
    }

    /**
     * validate_field 15
     *
     * @param string $value
     * @return bool|string
     */
    public static function time(string $value)
    {
        return preg_match("/^([01][0-9]|2[0-3]):([0-5][0-9])$/", $value) === 1
            ?: _("(must be a time in hh:mm format");
    }

    /**
     * validate_field 17
     *
     * @param string $value
     * @return bool|string
     */
    public static function min8Chars(string $value)
    {
        return strlen($value) >= 8
            ?: sprintf(_("(must be at least %d characters)"), 8);
    }

    /**
     * @param string $value
     * @return bool|string
     */
    public static function min8CharsOptional(string $value)
    {
        return $value === "" || strlen($value) >= 8
            ?: sprintf(_("(must be at least %d characters)"), 8);
    }

    /**
     * validate_field 18
     *
     * @param string $value
     * @return bool|string
     */
    public static function phoneNumber(string $value)
    {
        return preg_match("/^\\+[1-9][0-9]{5,14}$/", $value) === 1
            ?: _("(must be a phone number starting with +)");
    }

    /**
     * validate_field 19
     *
     * @param string $value
     * @return bool|string
     */
    public static function captcha(string $value)
    {
        return $value === (string)$_SESSION["captcha_code"]
            ?: _("(must be at least 6 letters and/or numbers)");
    }

    /**
     * validate_field 20
     *
     * @param string $value
     * @return bool|string
     */
    public static function timeSeconds(string $value)
    {
        return preg_match("/^([01][0-9]|2[0-3])(:[0-5][0-9]){2}$/", $value) === 1
            ?: _("(must be a time in hh:mm:ss format");
    }

    /**
     * validate_field 21
     *
     * @param string $value
     * @return bool|string
     */
    public static function percentage(string $value)
    {
        return (is_numeric($value) && $value >= 0 && $value <= 100)
            ?: _("(must be a number between 0 and 100 – use . for decimal)");
    }
}
