<?php

use A2billing\Connection;
use PHPMailer\PHPMailer\PHPMailer;

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of A2Billing (http://www.a2billing.net/)
 *
 * A2Billing, Commercial Open Source Telecom Billing platform,
 * powered by Star2billing S.L. <http://www.star2billing.com/>
 *
 * @copyright   Copyright © 2004-2015 - Star2billing S.L.
 * @copyright   Copyright © 2022 RadiusOne Inc.
 * @author      Belaid Arezqui <areski@gmail.com>
 * @author      Michael Newton <mnewton@goradiusone.com>
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

function get_cardlength(): int
{
    $db = DbConnect();
    $len = $db->CacheGetOne(86400, "SELECT config_value FROM cc_config WHERE config_key = 'interval_len_cardnumber' LIMIT 1");
    if ($len) {
        $len = min(split_data($len));
    } else {
        $len = 10;
    }

    return $len;
}

/*
* function splitable_data
* used by parameter like interval_len_cardnumber : 8-10, 12-18, 20
* it will build an array with the different interval
*/
function split_data(?string $values): array
{
    $return = [];
    $values_array = explode(",", $values);
    foreach ($values_array as $value) {
        $minmax = explode("-", trim($value), 2);
        $minmax = array_filter($minmax, 'is_numeric');
        sort($minmax);
        if (count($minmax) > 1) {
            $return = array_merge($return, range($minmax[0], $minmax[1]));
        } else {
            $return[] = $minmax[0];
        }
    }
    sort($return);

    return empty($return) ? [10] : array_unique($return);
}

/*
 * a2b_round: specific function to use the same precision everywhere
 */
function a2b_round($number, $PRECISION = 6): float
{

    return round($number, $PRECISION);
}

/*
 * a2b_mail - function mail used in a2billing
 */
/**
 * @throws \PHPMailer\PHPMailer\Exception
 */
function a2b_mail($to, $subject, $mail_content, $from = 'root@localhost', $fromname = '', $contenttype = 'multipart/alternative')
{

    $mail = new PHPMailer(true);

    if (SMTP_SERVER) {
        $mail->Mailer = "smtp";
    } else {
        $mail->Mailer = "sendmail";
    }

    $mail->Host = SMTP_HOST;
    $mail->Username = SMTP_USERNAME;
    $mail->Password = SMTP_PASSWORD;
    $mail->Port = SMTP_PORT;
    $mail->SMTPSecure = SMTP_SECURE;
    $mail->CharSet = 'UTF-8';

    if (!empty(SMTP_USERNAME)) {
        $mail->SMTPAuth = true;
    }

    $mail->From = $from;
    $mail->FromName = $fromname;
    $mail->Subject = $subject;
    $mail->Body = nl2br($mail_content); //$HTML;
    $mail->AltBody = $mail_content; // Plain text body (for mail clients that cannot read 	HTML)
    // if ContentType = multipart/alternative -> HTML will be send
    $mail->ContentType = $contenttype;

    if (str_contains($to, ',')) {
        foreach (explode(',', $to) as $toemail) {
            $mail->addAddress($toemail);
        }
    } else {
        $mail->addAddress($to);
    }

    $mail->send();
}

/*
 * get_currencies
 */
function get_currencies($handle = null): array
{
    $handle = $handle ?? DbConnect();
    $currencies_list = [];
    $result = $handle->CacheGetAll(900, "SELECT currency, name, `value` FROM cc_currencies ORDER BY id");
    array_walk(
        $result,
        function ($v) use (&$currencies_list) {
            $currencies_list[$v["currency"]] = $v;
        }
    );

    // these are always at the top of the list
    $top_curr = [
        strtoupper(BASE_CURRENCY), 'USD', 'EUR', 'GBP', 'CAD', 'AUD', 'HKD',
        'JPY', 'NZD', 'SGD', 'TWD', 'PLN', 'SEK', 'DKK', 'CHF', 'COP', 'MXN', 'CLP',
    ];

    return array_replace(array_flip($top_curr), $currencies_list);
}

function getCurrenciesList(): array
{
    $currencies_list = get_currencies();
    array_walk(
        $currencies_list,
        fn (&$v, $k) => $v = [sprintf("%s (%s)", $v["name"], $v["value"]), $k]
    );

    return $currencies_list;
}

/**
 * Do Currency Conversion.
 *
 * @param array $currencies_list the List of currencies.
 * @param int|float $amount the amount to be converted.
 * @param string $from_cur Source Currency
 * @param string $to_cur Destination Currecny
 */
function convert_currency(array $currencies_list, $amount, string $from_cur, string $to_cur)
{
    if (!is_numeric($amount) || ($amount == 0)) {
        return 0;
    }
    if ($from_cur == $to_cur) {
        return $amount;
    }
    // EUR -> 1.19175 : MAD -> 0.10897
    // FROM -> 2 - TO -> 0.5 =>>>> multiply 4
    $mycur_tobase = $currencies_list[strtoupper($from_cur)]["value"];
    $mycur = $currencies_list[strtoupper($to_cur)]["value"];
    if ($mycur == 0) {
        return 0;
    }

    return $amount * ($mycur_tobase / $mycur);
}

/*
 * Write log into file
 */
function write_log($logfile, $output)
{
    // echo "<br>$output<br>";
    if (strlen($logfile) > 1) {
        $string_log = "[" . date("d/m/Y H:i:s") . "]:[$output]\n";
        error_log($string_log . "\n", 3, $logfile);
    }
}

/**
 * function sanitize_data
 * @param array|string $input
 * @return array|string
 */
function sanitize_data($input)
{
    if (is_array($input)) {
        $output = [];
        // Sanitize Array
        foreach ($input as $var => $val) {
            $output[$var] = sanitize_data($val);
        }
    } else {
        // Remove whitespaces (not a must though)
        $input = trim($input);
        $input = str_replace('--', '', $input);
        $input = str_replace('..', '', $input);
        $input = str_replace(';', '', $input);
        $input = str_replace('/*', '', $input);

        // Injection sql
        $input = str_ireplace('HAVING', '', $input);
        $input = str_ireplace('UNION', '', $input);
        $input = str_ireplace('SUBSTRING', '', $input);
        $input = str_ireplace('ASCII', '', $input);
        $input = str_ireplace('SHA1', '', $input);
        #MD5 is used by md5secret
        #$input = str_ireplace('MD5', '', $input);
        $input = str_ireplace('ROW_COUNT', '', $input);
        $input = str_ireplace('SELECT', '', $input);
        $input = str_ireplace('INSERT', '', $input);
        $input = str_ireplace('CASE WHEN', '', $input);
        $input = str_ireplace('INFORMATION_SCHEMA', '', $input);
        $input = str_ireplace('DROP', '', $input);
        $input = str_ireplace('RLIKE', '', $input);
        $input = str_ireplace(' IF', '', $input);
        $input = str_ireplace(' OR ', '', $input);
        $input = str_ireplace('\\', '', $input);
        //$input = str_ireplace('DELETE', '', $input);
        $input = str_ireplace('CONCAT', '', $input);
        $input = str_ireplace('WHERE', '', $input);
        $input = str_ireplace('UPDATE', '', $input);
        $input = str_ireplace(' or 1', '', $input);
        $input = str_ireplace(' or true', '', $input);
        //Permutation - in mailing admin/Public/A2B_entity_mailtemplate.php
        // we use url with key=$loginkey$
        $input = str_ireplace('=$', '+$', $input);
        $input = str_ireplace('=', '', $input);
        $input = str_ireplace('+$', '=$', $input);

        $input = strip_tags($input);

        $output = addslashes($input);
    }

    return $output;
}

/*
 * Sanitize all Post Get variables
 */
function sanitize_post_get()
{
    foreach ($_REQUEST as $key => $value) {
        $key = filter_var($key, FILTER_CALLBACK, ["options" => "sanitize_data"]);
        $value = filter_var($value, FILTER_CALLBACK, ["options" => "sanitize_data"]);
        $key = filter_var($key, FILTER_SANITIZE_STRING);
        if (is_array($value)) {
            foreach ($value as $subkey => $subvalue) {
                $subkey = filter_var($subkey, FILTER_SANITIZE_STRING);
                $subvalue = filter_var($subvalue, FILTER_SANITIZE_STRING);
                $value[$subkey] = $subvalue;
            }
        } else {
            $value = filter_var($value, FILTER_SANITIZE_STRING);
        }
        $_REQUEST[$key] = $value;
    }
}

/*
 * function getpost_ifset
 */
function getpost_ifset(array $test_vars, ?array &$data = null)
{
    foreach ($test_vars as $test_var) {
        if (!isset($_REQUEST[$test_var])) {
            continue;
        }
        $val = sanitize_data($_REQUEST[$test_var]);
        //rebuild the search parameter to filter character to format card number
        if ($test_var == 'username' || $test_var == 'filterprefix') {
            //rebuild the search parameter to filter character to format card number
            $filtered_char = [
                " ",
                "-",
                "_",
                "(",
                ")",
                "/",
                "\\",
            ];
            $val = str_replace($filtered_char, "", $val);
        }
        if (!is_null($data)) {
            $data[$test_var] = $val;
        } else {
            $GLOBALS[$test_var] = $val;
        }
    }
}

/**
 * Used as callback for list/form elements
 * @param $value
 * @param $currency
 * @return void
 * @noinspection PhpUnusedFunctionInspection
 */
function display_money($value, $currency = BASE_CURRENCY)
{
    echo number_format($value, 2, '.', ' ') . ' ' . strtoupper($currency);
}

/**
 * Used as callback for list view elements
 * @param $mydate
 * @return void
 * @noinspection PhpUnusedFunctionInspection
 */
function display_dateformat($mydate)
{
    echo get_dateformat($mydate);
}

function get_dateformat(string $mydate): string
{
    if (DB_TYPE === "mysql" && strlen($mydate) === 14) {
        // why is this here? MySQL does not return in this format and never has
        return DateTime::createFromFormat("YmdHis", $mydate)->format("Y-m-d H:i:s");
    }

    return $mydate;
}

/**
 * Used as callback for edit form elements
 * @noinspection PhpUnusedFunctionInspection
 */
function res_display_dateformat($mydate)
{
    if (DB_TYPE === "mysql" && strlen($mydate) === 14) {
        // why is this here? MySQL does not return in this format and never has
        return DateTime::createFromFormat("YmdHis", $mydate)->format("Y-m-d H:i:s");
    }

    return $mydate;
}

/**
 * Used as callback for list/form elements
 * @param $sessiontime
 * @return void
 */
function display_minute($sessiontime): void
{
    echo get_minute($sessiontime);
}

function get_minute($sessiontime)
{
    // see if this came in via post/get
    getpost_ifset(["resulttype"], $p);

    if (($p["resulttype"] ?? "min") === "min") {
        $sessiontime = sprintf("%02d:%02d", intval($sessiontime / 60), $sessiontime % 60);
    }

    return $sessiontime;
}

/**
 * Used as callback for list/form elements
 * @param $var
 * @return void
 * @noinspection PhpUnusedFunctionInspection
 */
function display_2dec($var)
{
    echo number_format($var, 2);
}

/**
 * Used as callback for list/form elements
 * @param $var
 * @return void
 * @noinspection PhpUnusedFunctionInspection
 */
function display_2dec_percentage($var)
{
    echo get_2dec_percentage($var);
}

function get_2dec_percentage($var): string
{
    if (isset ($var)) {
        return number_format($var, 2) . "%";
    } else {
        return "n/a";
    }
}

/**
 * Used as callback for list/form elements
 * @param float|int|string $amt
 * @return void
 * @noinspection PhpUnusedFunctionInspection
 */
function display_2bill($amt): void
{
    echo get_2bill($amt);
}

/**
 * Rounds and formats a currency amount
 * @param float|int|string $amt
 * @return string
 */
function get_2bill($amt): string
{
    if (class_exists("NumberFormatter")) {
        static $formatter = null;
        if (is_null($formatter)) {
            // TODO: set this based on user language, so decimals are properly displayed
            $formatter = NumberFormatter::create("en_CA", NumberFormatter::CURRENCY);
            $formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, 4);
            $formatter->setAttribute(NumberFormatter::ROUNDING_MODE, NumberFormatter::ROUND_HALFUP);
        }

        return $formatter->formatCurrency($amt, BASE_CURRENCY);
    }

    return sprintf("%0.3f %s", $amt, BASE_CURRENCY);
}

/**
 * Used as callback for list/form elements
 * @param string $phonenumber
 * @return int|void
 * @noinspection PhpUnusedFunctionInspection
 */
function display_without_prefix(string $phonenumber)
{
    if (str_starts_with($phonenumber, "011")) {
        echo substr($phonenumber, 3);

        return 1;
    }
    if (str_starts_with($phonenumber, "00")) {
        echo substr($phonenumber, 2);

        return 1;
    }
    echo $phonenumber;
}

/**
 * Used as callback for list/form elements
 * @param $value
 * @return false|void
 * @noinspection PhpUnusedFunctionInspection
 */
function display_monitorfile_link($value)
{
    $format_list = ['wav', 'gsm', 'mp3', 'sln', 'g723', 'g729'];
    $find_record = false;
    foreach ($format_list as $c_format) {
        $myfile = "/$value.$c_format";
        $dl_full = MONITOR_PATH . $myfile;
        if (file_exists($dl_full)) {
            $find_record = true;
            break;
        }
    }
    if (!$find_record) {
        return false;
    }

    $myfile = base64_encode($myfile);
    echo "<a target='_blank' href='call-log-customers.php?download=file&file=$myfile'>";
    echo '<img alt="access recording" src="" height="18" /></a>';
}

/**
 * Used as callback for list/form elements
 * @param string $value
 * @return void
 * @noinspection PhpUnusedFunctionInspection
 */
function display_customer_link(string $value): void
{
    echo get_customer_link($value);
}

function get_customer_link($username): string
{
    $value = _("n/a");
    if (empty($username)) {

        return $value;
    }
    $handle = DbConnect();
    $id = $handle->CacheGetOne(60, "SELECT id FROM cc_card WHERE username = ?", [$username]);
    if ($id) {

        return "<a href=\"A2B_entity_card.php?form_action=ask-edit&id=$id\">$username</a>";
    } else {
        return $value;
    }
}

/**
 * Used as callback for list/form elements
 * @param string $value
 * @return void
 * @noinspection PhpUnusedFunctionInspection
 */
function display_customer_id_link($id): void
{
    $value = _("n/a");
    if ($id <= 0) {

        echo $value;
    }
    $handle = DbConnect();
    $username = $handle->CacheGetOne(60, "SELECT username FROM cc_card WHERE id = ?", [$id]);
    if ($username) {
        echo "<a href=\"A2B_entity_card.php?form_action=ask-edit&id=$id\">$username</a>";
    } else {
        echo $value;
    }
}

/**
 * Used as callback for list/form elements
 * @param string $value
 * @return void
 * @noinspection PhpUnusedFunctionInspection
 */
function display_customer_name_id_link($id): void
{
    $value = _("n/a");
    if ($id <= 0) {

        echo $value;
    }
    $handle = DbConnect();
    $row = $handle->CacheGetRow(60, "SELECT firstname, lastname, username FROM cc_card WHERE id = ?", [$id]);
    if ($row) {
        echo "<a href=\"A2B_entity_card.php?form_action=ask-edit&id=$id\" title=\"$row[username]\">$row[firstname] $row[lastname]</a>";
    } else {
        echo $value;
    }
}

/**
 * Used as callback for list elements
 * @param string $value
 * @return void
 * @noinspection PhpUnusedFunctionInspection
 */
function display_infocustomer_id($id): void
{
    echo get_infocustomer_id($id);
}

function get_infocustomer_id($id): string
{
    $value = _("n/a");
    if ($id <= 0) {

        return $value;
    }
    $handle = DbConnect();
    $row = $handle->CacheGetRow(60, "SELECT username, firstname, lastname FROM cc_card WHERE id = ?", [$id]);
    if ($row) {
        $value = sprintf("%s %s (%s)", $row["firstname"], $row["lastname"], $row["username"]);

        return "<a href=\"A2B_card_info.php?id=$id\">$value</a>";
    } else {
        return $value;
    }
}

function get_nameofadmin($id): string
{
    $value = _("n/a");
    if ($id <= 0) {

        return $value;
    }
    $handle = DbConnect();
    $row = $handle->CacheGetRow(60, "SELECT login, name FROM cc_ui_authen WHERE userid = ?", [$id]);
    if ($row) {
        $value = sprintf("%s (%s)", $row["name"], $row["login"]);
    }

    return $value;
}

function get_nameofcustomer_id($id): string
{
    $value = _("n/a");
    if ($id <= 0) {

        return $value;
    }
    $handle = DbConnect();
    $row = $handle->CacheGetRow(60, "SELECT username, firstname, lastname FROM cc_card WHERE id = ?", [$id]);
    if (is_array($row)) {
        $value = sprintf("%s %s (%s)", $row["firstname"], $row["lastname"], $row["username"]);
    }

    return $value;
}

/**
 * Used as callback for list/form elements
 * @param $id
 * @return void
 * @noinspection PhpUnusedFunctionInspection
 */
function display_linktoagent($id): void
{
    echo get_linktoagent($id);
}

function get_linktoagent($id): string
{
    $value = _("n/a");
    if ($id <= 0) {

        return $value;
    }
    $handle = DbConnect();
    $row = $handle->CacheGetRow(60, "SELECT login, firstname, lastname FROM cc_agent WHERE id = ?", [$id]);
    if (is_array($row)) {
        $value = sprintf("%s %s (%s)", $row["firstname"], $row["lastname"], $row["login"]);

        return "<a href=\"A2B_entity_agent.php?form_action=ask-edit&id=$id\">$value</a>";
    } else {
        return $value;
    }
}

/**
 * Used as callback for list/form elements
 * @param $id
 * @return void
 * @noinspection PhpUnusedFunctionInspection
 */
function display_nameofagent($id): void
{
    echo get_nameofagent($id);
}

function get_nameofagent($id): string
{
    $value = _("n/a");
    if ($id <= 0) {

        return $value;
    }
    $handle = DbConnect();
    $row = $handle->CacheGetRow(60, "SELECT login, firstname, lastname FROM cc_agent WHERE id = ?", [$id]);
    if ($row) {
        $value = sprintf(_("%s %s (login: %s)"), $row["firstname"], $row["lastname"], $row["login"]);
    }

    return $value;
}

/**
 * Used as callback for list elements
 * @param string $did
 * @return void
 * @noinspection PhpUnusedFunctionInspection
 */
function display_did(string $did): void
{
    echo get_formatted_did($did);
}

/**
 * Used as callback for list/form elements
 * @param string $did
 * @return void
 */
function get_formatted_did(string $did): string
{
    $value = $did;
    if (empty($did) || !is_numeric($did)) {

        return $value;
    }
    $handle = DbConnect();
    $cc = $handle->CacheGetOne(
        60,
        "SELECT countrycode FROM cc_did d LEFT JOIN cc_country c ON (c.id = d.id_cc_country) WHERE did = ? AND countryprefix = 1",
        [$did]
    );
    if ($cc) {
        return format_phone_number($value);
    }

    return $value;
}

/**
 * Used as callback for list elements
 * @param string $num
 * @return void
 * @noinspection PhpUnusedFunctionInspection
 */
function display_phone_number(string $num): void
{
    echo format_phone_number($num);
}

function format_phone_number(string $value): string
{
    if (preg_match("/^(1?)([2-9]\d\d)([2-9]\d\d)(\d\d\d\d)$/",$value, $matches)) {
        $value = "";
        if ($matches[1]) {
            $value = "1-";
        }
        $value .= "$matches[2]-$matches[3]-$matches[4]";
    }

    return $value;
}
/*
 * function MDP_STRING
 */
function MDP_STRING($chrs = 0): string
{
    if (empty($chrs)) {
        $chrs = get_cardlength();
    }

    $pwd = "";
    mt_srand((double)microtime() * 1000000);
    while (strlen($pwd) < $chrs) {
        $chr = chr(mt_rand(0, 255));
        if (preg_match("/^[0-9a-z]$/i", $chr)) {
            $pwd = $pwd . $chr;
        }
    }

    return strtolower($pwd);
}

/*
 * function MDP_NUMERIC
 */
function MDP_NUMERIC($chrs = 0): string
{
    if (empty($chrs)) {
        $chrs = get_cardlength();
    }

    $myrand = "";
    for ($i = 0; $i < $chrs; $i++) {
        $myrand .= mt_rand(0, 9);
    }

    return $myrand;
}

/*
 * function MDP
 */
function MDP($chrs = 0): string
{
    if (empty($chrs)) {
        $chrs = get_cardlength();
    }

    return MDP_NUMERIC($chrs);
}

/*
 * function generate_unique_value
 */
function generate_unique_value($table = "cc_card", $len = 0, $field = "username")
{
    $DBHandle_max = DbConnect();
    if (empty($len)) {
        $len = get_cardlength();
    }

    for ($k = 0; $k <= 200; $k++) {
        $card_gen = MDP($len);

        $query = "SELECT `$field` FROM `$table` WHERE `$field` = ?";
        $resmax = $DBHandle_max->Execute($query, [$card_gen]);
        if ($resmax && !$resmax->RecordCount()) {
            return $card_gen;
        }
    }
    echo "ERROR : Impossible to generate a $field not yet used!";
    exit ();
}

/*
 * function gen_card_with_alias
 */
function gen_card_with_alias($length_cardnumber = null)
{
    $DBHandle = DbConnect();

    if (empty($length_cardnumber)) {
        $length_cardnumber = get_cardlength();
    }

    for ($k = 0; $k <= 200; $k++) {
        $card_gen = MDP($length_cardnumber);
        $alias_gen = MDP(LEN_ALIASNUMBER);

        $query = "SELECT username FROM cc_card WHERE username=? OR useralias=? OR username=? OR useralias=?";
        $resmax = $DBHandle->Execute($query, [$card_gen, $alias_gen, $alias_gen, $card_gen]);
        if ($resmax && !$resmax->RecordCount()) {
            $arr_val[0] = $card_gen;
            $arr_val[1] = $alias_gen;

            return $arr_val;
        }
    }
    echo "ERROR : Impossible to generate a Cardnumber & Aliasnumber not yet used!";
    exit();
}

/**
 * Validate the Uploaded Files.  Return the error string if any.
 *
 * @param string $the_file the file to validate
 * @param string $the_file_type the file type
 */
function validate_upload(string $the_file, string $the_file_type): string
{
    $allowed_types = [
        "text/plain",
        "text/x-comma-separated-values",
        "text/comma-separated-values",
        "text/csv",
        "text/x-csv",
        "application/vnd.ms-excel",
    ];

    $start_error = "\n<b>ERROR:</b>\n<ul>";
    $error = "";
    if ($the_file == "") {
        $error .= "\n<li>" . gettext("File size is greater than allowed limit.") . "\n</li>";
    } elseif ($the_file == "none") {
        $error .= "\n<li>" . gettext("You did not upload anything!") . "</li>";
    } elseif ($_FILES['the_file']['size'] == 0) {
        $error .= "\n<li>" . gettext("Failed to upload the file, The file you uploaded may not exist on disk.") . "!</li>";
    } elseif (!in_array($the_file_type, $allowed_types)) {
        $error .= "\n<li>$the_file_type " . gettext("file type is not allowed") . "\n</li>";
    }

    return ($error) ? $start_error . $error . "\n</ul>" : "";
}

function securitykey(string $key, string $data): string
{
    // RFC 2104 HMAC implementation for php.
    // Creates an md5 HMAC.
    // Eliminates the need to install mhash to compute a HMAC
    // Hacked by Lance Rushing

    $b = 64; // byte length for md5
    if (strlen($key) > $b) {
        $key = pack("H*", md5($key));
    }
    $key = str_pad($key, $b, chr(0x00));
    $ipad = str_pad('', $b, chr(0x36));
    $opad = str_pad('', $b, chr(0x5c));
    $k_ipad = $key ^ $ipad;
    $k_opad = $key ^ $opad;

    return md5($k_opad . pack("H*", md5($k_ipad . $data)));
}

/*
    public Function to show GMT DateTime.
*/
function get_timezones(): array
{
    $db = DbConnect();
    $result = $db->CacheGetAll(900, "SELECT id, gmttime, gmtzone, gmtoffset FROM cc_timezone ORDER by id");
    $timezone_list = [];

    if (is_array($result)) {
        foreach ($result as $row) {
            $timezone_list[$row["id"]] = [
                1 => $row["gmttime"],
                2 => $row["gmtzone"],
                3 => $row["gmtoffset"],
            ];
        }
    }

    return $timezone_list;
}

function get_date_with_offset($currDate, $user_offset = null)
{
    if (is_null($user_offset)) {
        $user_offset = $_SESSION["gmtoffset"] ?? 0;
    }
    $server_offset = 0;
    $handle = DbConnect();
    $row = $handle->CacheGetRow(300, "SELECT gmtoffset FROM cc_timezone WHERE gmttime = ?", [SERVER_GMT]);
    if (is_array($row)) {
        $server_offset = $row["gmtoffset"];
    }
    $timestamp = strtotime($currDate) - ($server_offset - $user_offset);

    return date("Y-m-d H:i:s", $timestamp);
}

/*
 * Apparently builds SQL out of global variables, typically populated by POST.
 * Used a lot, will have to wait to replace it.
 * A2b, A2b, how do I inject thee? Let me count the ways...
 */
function do_field($sql, $fld, $dbfld)
{
    $glob_value = str_replace("'", "\\'", $GLOBALS[$fld] ?? "");
    $glob_type = $GLOBALS[$fld . "type"];

    if ($glob_value) {
        $sql .= strpos($sql, "WHERE") ? " AND " : " WHERE ";
        switch ($glob_type) {
            case 1:
                $sql .= " $dbfld='$glob_value'";
                break;
            case 2:
                $sql .= " $dbfld LIKE '$glob_value%'";
                break;
            default:
                $sql .= " $dbfld LIKE '%$glob_value%'";
                break;
            case 4:
                $sql .= " $dbfld LIKE '%$glob_value'";
        }
    }

    return $sql;
}

function generate_invoice_reference(): string
{
    $handle = DbConnect();
    $year = date("Y");
    $count = $handle->GetOne("SELECT value FROM cc_invoice_conf WHERE key_val = ?", ["count_$year"]);

    if ($count !== false) {
        if (!is_numeric($count)) {
            $count = 0;
        }
        $count++;
        $handle->Execute("UPDATE cc_invoice_conf SET value=? WHERE key_val=?", [$count, "count_$year"]);
    } else {
        //insert newcount
        $count = 1;
        $handle->Execute("INSERT INTO cc_invoice_conf(`value`, `key_val`) VALUES(?, ?)", [$count, "count_$year"]);
    }

    return $year . sprintf("%08d", $count);
}

/**
 * Checks the day of month for date related forms and reduces the day to the last valid day of the month if too large.
 *
 * @param null|int|string &$day day from '01' to '31'
 * @param null|string $year_month: 'xxxx-mm'
 * @return int normalized day
 */
function normalize_day_of_month(&$day, ?string $year_month = "")
{
    if (!empty($year_month)) {
        $check_date = DateTime::createFromFormat("Y-m-d", "$year_month-01");
        $day = min($day, $check_date->format("t"));
    }
    return $day;
}

/**
 * Get the last day of the month
 *
 * @param string|int $month
 * @param string|int $year
 * @param string $format
 * @return string
 */
function lastDayOfMonth($month = null, $year = null, string $format = 'd-m-Y'): string
{
    if (empty($month)) {
        $month = date('m');
    }
    if (empty($year)) {
        $year = date('Y');
    }
    $format = str_replace("d", "t", $format);
    $date = sprintf("%04d-%02d-01", $year, $month);

    return DateTime::createFromFormat("Y-m-d", $date)->format($format);
}

function get_login_button($id): string
{
    $handle = DbConnect();
    $row = $handle->GetRow("SELECT useralias, uipass FROM cc_card WHERE id=?", [$id]);
    if ($row === false) {
        return "";
    }
    $username = htmlspecialchars($row["useralias"]);
    $password = htmlspecialchars($row["uipass"]);
    $link = CUSTOMER_UI_URL;

    if (strpos($link, 'index.php') !== false) {
        $link = substr($link, 0, strlen($link) - 9) . 'userinfo.php';
    } else {
        $link .= '/userinfo.php';
    }
    $link = htmlspecialchars($link);
    $label = htmlspecialchars(_("GO TO CUSTOMER ACCOUNT"));

    return <<< HTML
    <div align="right" style="padding-right:20px;">
        <form action="$link" method="POST" target="_blank">
            <input type="hidden" name="done" value="submit_log"/>
            <input type="hidden" name="pr_login" value="$username"/>
            <input type="hidden" name="pr_password" value="$password"/>
            <a href="#" onclick="$('form').trigger('submit');" >$label</a>
        </form>
    </div>
HTML;
}

function str_icontains(string $haystack, string $needle): bool
{
    if (function_exists("str_contains")) {
        return str_contains(strtolower($haystack), strtolower($needle));
    } else {
        return stripos($haystack, $needle) !== false;
    }
}

function DbConnect(): ADOConnection
{
    return Connection::GetDBHandler();
}

function SetLocalLanguage(): void
{
    switch ($_SESSION["ui_language"] ?? "") {
        case "brazilian":
            $languageEncoding = "pt_BR.UTF-8";
            $slectedLanguage = "pt_BR";
            $charEncoding = "UTF-8";
            break;
        case "chinese":
            $languageEncoding = "zh_CN.UTF-8";
            $slectedLanguage = "zh_CN";
            $charEncoding = "UTF-8";
            break;
        case "spanish":
            $languageEncoding = "es_ES.iso88591";
            $slectedLanguage = "es_ES";
            $charEncoding = "UTF-8";
            break;
        case "french":
            $languageEncoding = "fr_FR.iso88591";
            $slectedLanguage = "fr_FR";
            $charEncoding = "iso-8859-1";
            break;
        case "german":
            $languageEncoding = "de_DE.iso88591";
            $slectedLanguage = "de_DE";
            $charEncoding = "iso-8859-1";
            break;
        case "italian":
            $languageEncoding = "it_IT.iso8859-1";
            $slectedLanguage = "it_IT";
            $charEncoding = "iso88591";
            break;
        case "polish":
            $languageEncoding = "pt_PT.iso88591";
            $slectedLanguage = "pl_PL";
            $charEncoding = "iso88591";
            break;
        case "romanian":
            $languageEncoding = "ro_RO.iso88591";
            $slectedLanguage = "ro_RO";
            $charEncoding = "iso88591";
            break;
        case "russian":
            $languageEncoding = "ru_RU.UTF-8";
            $slectedLanguage = "ru_RU";
            $charEncoding = "UTF-8";
            break;
        case "turkish":
            // issues with Turkish
            // http://forum.elxis.org/index.php?action=printpage%3Btopic=3090.0
            // http://bugs.php.net/bug.php?id=39993
            $languageEncoding = "tr_TR.UTF-8";
            $slectedLanguage = "tr_TR.UTF-8";
            $charEncoding = "UTF-8";
            break;
        case "urdu":
            $languageEncoding = "ur.UTF-8";
            $slectedLanguage = "ur_PK";
            $charEncoding = "UTF-8";
            break;
        case "ukrainian": // provided by Oleh Miniv  email: oleg-min@ukr.net
            $languageEncoding = "uk_UA.UTF8";
            $slectedLanguage = "uk_UA";
            $charEncoding = "UTF8";
            break;
        case "farsi":
            $languageEncoding = "fa_IR.UTF-8";
            $slectedLanguage = "fa_IR";
            $charEncoding = "UTF-8";
            break;
        case "greek":
            $languageEncoding = "el_GR.UTF-8";
            $slectedLanguage = "el_GR";
            $charEncoding = "UTF-8";
            break;
        case "indonesian":
            $languageEncoding = "id_ID.iso88591";
            $slectedLanguage = "id_ID";
            $charEncoding = "iso88591";
            break;
        default:
            $languageEncoding = "en_US.iso88591";
            $slectedLanguage = "en_US";
            $charEncoding = "iso88591";
            break;
    }

    setlocale(LC_TIME, $languageEncoding);
    putenv("LANG=$slectedLanguage");
    putenv("LANGUAGE=$slectedLanguage");
    setlocale(LC_ALL, $slectedLanguage);
    setlocale(LC_MESSAGES, $languageEncoding);

    textdomain("messages");
    bindtextdomain("messages", BINDTEXTDOMAIN);
    bind_textdomain_codeset("messages", $charEncoding);
    define("CHARSET", $charEncoding);
}

function create_help($text, $wiki = ""): string
{
    $db = DbConnect();
    $result = $db->CacheGetOne(86400, "SELECT config_value FROM cc_config WHERE config_key = 'show_help'");
    if ($result !== "1") {
        return "";
    }

    $text = htmlspecialchars($text);
    if (!empty($wiki)) {
        $wiki = htmlspecialchars(_("For further information please consult")) . ' <a target="_blank" href="http://www.asterisk2billing.org/documentation/">' . htmlspecialchars(_("the online documention")) . '</a>.<br/>';
    }
    $path = htmlspecialchars(Images_Path);

    return <<< HTML
<div class="toggle_show2hide">
    <div class="tohide" style="display:initial;">
        <div class="msg_info">
            $text<br/>$wiki
            <a href="#" target="_self" class="hide_help" style="float:right;">
                <img class="toggle_show2hide" src="$path/toggle_hide2show_on.png" onmouseover="this.style.cursor='hand'" HEIGHT="16" alt=""/>
            </a>
        </div>
    </div>
</div>
HTML;
}

function is_admin(): bool
{
    return ($_SESSION["user_type"] ?? "") === "ADMIN";
}

function is_agent(): bool
{
    return ($_SESSION["user_type"] ?? "") === "AGENT";
}

function is_customer(): bool
{
    return ($_SESSION["user_type"] ?? "") === "CUST";
}

/**
 * Return an array containing the selected entries from the given array
 *
 * @param array $arr
 * @param string ...$keys
 * @return array
 */
function extract_keys(array $arr, string ...$keys): array
{
    $ret = [];
    array_map(
        function ($v) use ($arr, &$ret) {
            if (array_key_exists($v, $arr)) {
                $ret[$v] = $arr[$v];
            }
        },
        $keys
    );

    return $ret;
}