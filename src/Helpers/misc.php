<?php

use A2billing\Table;

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of A2Billing (http://www.a2billing.net/)
 *
 * A2Billing, Commercial Open Source Telecom Billing platform,
 * powered by Star2billing S.L. <http://www.star2billing.com/>
 *
 * @copyright   Copyright (C) 2004-2015 - Star2billing S.L.
 * @author      Belaid Arezqui <areski@gmail.com>
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

/*
 * a2b_round: specific function to use the same precision everywhere
 */
function a2b_round($number): float
{
    $PRECISION = 6;

    return round($number, $PRECISION);
}

/*
 * a2b_mail - function mail used in a2billing
 */
/**
 * @throws phpmailerException
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
    if (empty ($handle)) {
        $handle = DbConnect();
    }
    $currencies_list = [];
    $instance_table = new Table("cc_currencies", "id,currency,name,value");
    $result = $instance_table->get_list($handle, null, "id", null, null, null, null, 300);

    if (is_array($result)) {
        foreach ($result as $row) {
            $currencies_list[$row[1]] = [
                1 => $row[2],
                2 => $row[3],
            ];
        }
    }

    if (isset ($currencies_list)) {
        sort_currencies_list($currencies_list);
    }

    return $currencies_list;
}

function getCurrenciesList(): array
{
    $currency_list = [];
    $currencies_list = get_currencies();
    foreach ($currencies_list as $key => $cur_value) {
        $currency_list[$key] = [
            $cur_value[1] . ' (' . $cur_value[2] . ')',
            $key,
        ];
    }

    return $currency_list;
}

function getCurrenciesKeyList(): array
{
    $currency_list_key = [];
    $currencies_list = get_currencies();
    foreach ($currencies_list as $key => $cur_value) {
        $currency_list_key[$key][0] = $key;
    }

    return $currency_list_key;
}

function getCurrenciesRateList(): array
{
    $currency_list_r = [];
    $currencies_list = get_currencies();
    foreach ($currencies_list as $key => $cur_value) {
        $currency_list_r[$key] = [
            $key,
            $cur_value[1],
        ];
    }

    return $currency_list_r;
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
    $mycur_tobase = $currencies_list[strtoupper($from_cur)][2];
    $mycur = $currencies_list[strtoupper($to_cur)][2];
    if ($mycur == 0) {
        return 0;
    }

    return $amount * ($mycur_tobase / $mycur);
}

/*
 * sort_currencies_list
 */
function sort_currencies_list(array &$currencies_list): void
{
    $first_array = [
        strtoupper(BASE_CURRENCY
        ), 'USD', 'EUR', 'GBP', 'AUD', 'HKD', 'JPY', 'NZD', 'SGD', 'TWD', 'PLN', 'SEK', 'DKK', 'CHF', 'COP', 'MXN',
        'CLP',
    ];
    $currencies_list2 = [];
    foreach ($first_array as $element_first_array) {
        if (isset ($currencies_list[$element_first_array])) {
            $currencies_list2[$element_first_array] = $currencies_list[$element_first_array];
            unset ($currencies_list[$element_first_array]);
        }
    }
    $currencies_list = array_merge($currencies_list2, $currencies_list);
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

/*
 * function sanitize_tag
 */
function sanitize_tag($input)
{
    $search = [
        '@<script[^>]*?>.*?</script>@si', // Strip out javascript
        '@<[/!]*?[^<>]*?>@si', // Strip out HTML tags
        '@<style[^>]*?>.*?</style>@siU', // Strip style tags properly
        '@<![\s\S]*?--[ \t\n\r]*>@' // Strip multi-line comments
    ];

    return preg_replace($search, '', $input);
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

        $input = sanitize_tag($input);

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
function getpost_ifset($test_vars)
{
    if (!is_array($test_vars)) {
        $test_vars = [
            $test_vars,
        ];
    }
    foreach ($test_vars as $test_var) {
        if (isset($_REQUEST[$test_var])) {
            global $$test_var;
            $$test_var = sanitize_data($_REQUEST[$test_var]);
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
                $$test_var = str_replace($filtered_char, "", $$test_var);
            }
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
 * Used as callback for list/form elements
 * @param $mydate
 * @return void
 */
function display_dateformat($mydate)
{
    echo get_dateformat($mydate);
}

function get_dateformat(string $mydate): string
{
    if (DB_TYPE == "mysql" && strlen($mydate) === 14) {
        return DateTime::createFromFormat("YmdHis", $mydate)->format("Y-m-d H:i:s");
    }

    return $mydate;
}

/*
 * function res_display_dateformat
 */
function res_display_dateformat($mydate)
{
    if (DB_TYPE == "mysql") {
        if (strlen($mydate) == 14) {
            // YYYY-MM-DD HH:MM:SS 20300331225242
            $res = substr($mydate, 0, 4) . '-' . substr($mydate, 4, 2) . '-' . substr($mydate, 6, 2);
            $res .= ' ' . substr($mydate, 8, 2) . ':' . substr($mydate, 10, 2) . ':' . substr($mydate, 12, 2);

            return $res;
        }
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
    global $resulttype;
    if ((!isset ($resulttype)) || ($resulttype == "min")) {
        $minutes = sprintf("%02d", intval($sessiontime / 60)) . ":" . sprintf("%02d", $sessiontime % 60);
    } else {
        $minutes = $sessiontime;
    }
    return $minutes;
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
 * @param $var
 * @param $currency
 * @return void
 * @noinspection PhpUnusedFunctionInspection
 */
function display_2bill($var, $currency = BASE_CURRENCY): void
{
    echo get_2bill($var, $currency);
}

function get_2bill($var, $currency = BASE_CURRENCY): string
{
    global $currencies_list, $choose_currency;

    if (isset ($choose_currency) && strlen($choose_currency) == 3) {
        $currency = $choose_currency;
    }
    if ((!isset ($currencies_list)) || (!is_array($currencies_list))) {
        $currencies_list = get_currencies();
    }
    $var = $var / $currencies_list[strtoupper($currency)][2];

    return number_format($var, 3) . ' ' . strtoupper($currency);
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
    echo '<img alt="access recording" src="this image doesnt exist" height="18" /></a>';
}

/**
 * Used as callback for list/form elements
 * @param string $value
 * @return void
 * @noinspection PhpUnusedFunctionInspection
 */
function display_cdr_deletelink(string $value)
{
    echo "<a target=\"_blank\" href=\"A2B_entity_call.php?form_action=ask-delete&id=" . $value . "\">";
    echo '<img alt="delete this record" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAIhSURBVDjLlZPrThNRFIWJicmJz6BWiYbIkYDEG0JbBiitDQgm0PuFXqSAtKXtpE2hNuoPTXwSnwtExd6w0pl2OtPlrphKLSXhx07OZM769qy19wwAGLhM1ddC184+d18QMzoq3lfsD3LZ7Y3XbE5DL6Atzuyilc5Ciyd7IHVfgNcDYTQ2tvDr5crn6uLSvX+Av2Lk36FFpSVENDe3OxDZu8apO5rROJDLo30+Nlvj5RnTlVNAKs1aCVFr7b4BPn6Cls21AWgEQlz2+Dl1h7IdA+i97A/geP65WhbmrnZZ0GIJpr6OqZqYAd5/gJpKox4Mg7pD2YoC2b0/54rJQuJZdm6Izcgma4TW1WZ0h+y8BfbyJMwBmSxkjw+VObNanp5h/adwGhaTXF4NWbLj9gEONyCmUZmd10pGgf1/vwcgOT3tUQE0DdicwIod2EmSbwsKE1P8QoDkcHPJ5YESjgBJkYQpIEZ2KEB51Y6y3ojvY+P8XEDN7uKS0w0ltA7QGCWHCxSWWpwyaCeLy0BkA7UXyyg8fIzDoWHeBaDN4tQdSvAVdU1Aok+nsNTipIEVnkywo/FHatVkBoIhnFisOBoZxcGtQd4B0GYJNZsDSiAEadUBCkstPtN3Avs2Msa+Dt9XfxoFSNYF/Bh9gP0bOqHLAm2WUF1YQskwrVFYPWkf3h1iXwbvqGfFPSGW9Eah8HSS9fuZDnS32f71m8KFY7xs/QZyu6TH2+2+FAAAAABJRU5ErkJggg=="/></a>';
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

function get_customer_link($value): string
{
    $handle = DbConnect();
    $inst_table = new Table("cc_card", "id");
    $FG_TABLE_CLAUSE = "username = '$value'";
    $list_customer = $inst_table->get_list($handle, $FG_TABLE_CLAUSE, "", "", "", "", "", 10);
    $id = $list_customer[0][0];
    if ($id > 0) {
        return "<a href=\"A2B_entity_card.php?form_action=ask-edit&id=$id\">$value</a>";
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
function display_customer_id_link($id)
{
    $handle = DbConnect();
    $inst_table = new Table("cc_card", "username");
    $FG_TABLE_CLAUSE = "id = '$id'";
    $list_customer = $inst_table->get_list($handle, $FG_TABLE_CLAUSE, "", "", "", "", "", 10);
    $value = $list_customer[0][0];
    if ($id > 0) {
        echo "<a href=\"A2B_entity_card.php?form_action=ask-edit&id=$id\">$value</a>";
    } else {
        echo $value;
    }
}

function get_infocustomer_id($id): string
{
    $handle = DbConnect();
    $inst_table = new Table("cc_card", "username,firstname,lastname");
    $FG_TABLE_CLAUSE = "id = '$id'";
    $list_customer = $inst_table->get_list($handle, $FG_TABLE_CLAUSE, "", "", "", "", "", 10);
    if (is_array($list_customer)) {
        $value = $list_customer[0][1] . " " . $list_customer[0][2] . " (" . $list_customer[0][0] . ")";
    } else {
        $value = "";
    }
    if ($id > 0) {
        return "<a href=\"A2B_card_info.php?id=$id\">$value</a>";
    } else {
        return $value;
    }
}

function get_nameofadmin($id): string
{
    $handle = DbConnect();
    $inst_table = new Table("cc_ui_authen", "login,name");
    $FG_TABLE_CLAUSE = "userid = '$id'";
    $list_admin = $inst_table->get_list($handle, $FG_TABLE_CLAUSE, "", "", "", "", "", 10);
    if (is_array($list_admin)) {
        $value = $list_admin[0][1] . " (" . $list_admin[0][0] . ")";
    } else {
        $value = "";
    }

    return $value;
}

function get_nameofcustomer_id($id): string
{
    $handle = DbConnect();
    $inst_table = new Table("cc_card", "username,firstname,lastname");
    $FG_TABLE_CLAUSE = "id = '$id'";
    $list_customer = $inst_table->get_list($handle, $FG_TABLE_CLAUSE, "", "", "", "", "", 10);
    if (is_array($list_customer)) {
        $value = $list_customer[0][1] . " " . $list_customer[0][2] . " (" . $list_customer[0][0] . ")";
    } else {
        $value = "";
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
    $handle = DbConnect();
    $inst_table = new Table("cc_agent", "login,firstname,lastname");
    $FG_TABLE_CLAUSE = "id = '$id'";
    $list_agent = $inst_table->get_list($handle, $FG_TABLE_CLAUSE, "", "", "", "", "", 10);
    if (is_array($list_agent)) {
        $value = $list_agent[0][1] . " " . $list_agent[0][2] . " (" . $list_agent[0][0] . ")";
    } else {
        $value = "";
    }
    if ($id > 0) {
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
    $handle = DbConnect();
    $inst_table = new Table("cc_agent", "login,firstname,lastname");
    $FG_TABLE_CLAUSE = "id = '$id'";
    $list_agent = $inst_table->get_list($handle, $FG_TABLE_CLAUSE, "", "", "", "", "", 10);
    if (is_array($list_agent)) {
        $value = $list_agent[0][1] . " " . $list_agent[0][2] . " ( login: " . $list_agent[0][0] . ")";
    } else {
        $value = "";
    }

    return $value;
}

/*
 * function MDP_STRING
 */
function MDP_STRING($chrs = LEN_CARDNUMBER): string
{
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
function MDP_NUMERIC($chrs = LEN_CARDNUMBER): string
{
    $myrand = "";
    for ($i = 0; $i < $chrs; $i++) {
        $myrand .= mt_rand(0, 9);
    }

    return $myrand;
}

/*
 * function MDP
 */
function MDP($chrs = LEN_CARDNUMBER): string
{
    return MDP_NUMERIC($chrs);
}

/*
 * function generate_unique_value
 */
function generate_unique_value($table = "cc_card", $len = LEN_CARDNUMBER, $field = "username")
{
    $DBHandle_max = DbConnect();
    for ($k = 0; $k <= 200; $k++) {
        $card_gen = MDP($len);

        $query = "SELECT $field FROM $table WHERE $field = '$card_gen'";
        $resmax = $DBHandle_max->Execute($query);
        if ($resmax && !$resmax->RecordCount()) {
            return $card_gen;
        }
    }
    echo "ERROR : Impossible to generate a $field not yet used!<br>Perhaps check the LEN_CARDNUMBER (value:" . LEN_CARDNUMBER . ")";
    exit ();
}

/*
 * function gen_card_with_alias
 */
function gen_card_with_alias($table = "cc_card", $length_cardnumber = LEN_CARDNUMBER)
{
    $DBHandle = DbConnect();

    for ($k = 0; $k <= 200; $k++) {
        $card_gen = MDP($length_cardnumber);
        $alias_gen = MDP(LEN_ALIASNUMBER);

        $query = "SELECT username FROM $table WHERE username='$card_gen' OR useralias='$alias_gen'";
        $resmax = $DBHandle->Execute($query);
        if ($resmax && !$resmax->RecordCount()) {
            $arr_val[0] = $card_gen;
            $arr_val[1] = $alias_gen;

            return $arr_val;
        }
    }
    echo "ERROR : Impossible to generate a Cardnumber & Aliasnumber not yet used!<br>Perhaps check the LEN_CARDNUMBER  (value:" . LEN_CARDNUMBER . ") & LEN_ALIASNUMBER (value:" . LEN_ALIASNUMBER . ")";
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
    $handle = DbConnect();
    $instance_table = new Table();

    $QUERY = "SELECT id, gmttime, gmtzone, gmtoffset FROM cc_timezone ORDER by id";
    $result = $instance_table->SQLExec($handle, $QUERY, 1, 300);
    $timezone_list = [];

    if (is_array($result)) {
        foreach ($result as $row) {
            $timezone_list[$row[0]] = [
                1 => $row[1],
                2 => $row[2],
                3 => $row[3],
            ];
        }
    }

    return $timezone_list;
}

function get_date_with_offset($currDate, $number)
{
    $handle = DbConnect();
    $instance_table = new Table();
    $QUERY = "SELECT gmtoffset FROM cc_timezone WHERE gmttime = '" . SERVER_GMT . "'";
    $result = $instance_table->SQLExec($handle, $QUERY, 1, 300);
    $server_offset = $result[0][0];

    $timestamp = strtotime($currDate) - ($server_offset - $number);

    return date("Y-m-d H:i:s", $timestamp);
}

/*
 * Function use to archive data and call records
 * Insert in cc_call_archive and cc_card_archive on seletion criteria
 * Delete from cc_call and cc_card
 * Used in
 * 1. A2Billing_UI/Public/A2B_data_archving.php
 * 2. A2Billing_UI/Public/A2B_call_archiving.php
 */

function archive_data($condition, $entity = ""): int
{
    $handle = DbConnect();
    $instance_table = new Table();
    if (empty ($entity)) {
        return 1;
    }
    if ($entity == "card") {
        $func_fields = "id, creationdate, firstusedate, expirationdate, enableexpire, expiredays, username, useralias, uipass, credit, tariff, id_didgroup, activated, status, lastname, firstname, address, city, state, country, zipcode, phone, email, fax, inuse, simultaccess, currency, lastuse, nbused, typepaid, creditlimit, voipcall, sip_buddy, iax_buddy, language, redial, runservice, nbservice, id_campaign, num_trials_done, vat, servicelastrun, initialbalance, invoiceday, autorefill, loginkey, mac_addr, id_timezone, tag, voicemail_permitted, voicemail_activated, last_notification, email_notification, notify_email, credit_notification, id_group, company_name, company_website, VAT_RN, traffic, traffic_target, discount, restriction";
        $value = "SELECT $func_fields FROM cc_card $condition";
        $func_table = 'cc_card_archive';
        $id_name = "";
        $instance_table->Add_table($handle, $value, $func_fields, $func_table, $id_name, true);
        $fun_table = "cc_card";
        if (strpos($condition, 'WHERE') > 0) {
            $condition = str_replace("WHERE", "", $condition);
        }

        $instance_table->Delete_table($handle, $condition, $fun_table);
    } elseif ($entity == "call") {
        $value = "SELECT id, sessionid,uniqueid,card_id,nasipaddress,starttime,stoptime,sessiontime,calledstation,sessionbill,id_tariffgroup,id_tariffplan,id_ratecard,id_trunk,sipiax,src,id_did,buyrate,id_card_package_offer,real_sessiontime FROM cc_call $condition";
        $func_fields = "id, sessionid,uniqueid,card_id,nasipaddress,starttime,stoptime,sessiontime,calledstation,sessionbill,id_tariffgroup,id_tariffplan,id_ratecard,id_trunk,sipiax,src,id_did,buyrate,id_card_package_offer,real_sessiontime";
        $func_table = 'cc_call_archive';
        $id_name = "";
        $instance_table->Add_table($handle, $value, $func_fields, $func_table, $id_name, true);
        if (strpos($condition, 'WHERE') > 0) {
            $condition = str_replace("WHERE", "", $condition);
        }
        $fun_table = "cc_call";
        $instance_table->Delete_table($handle, $condition, $fun_table);
    }

    return 1;
}

/*
 * Function use to define exact sql statement for
 * different criteria selection
 */
function do_field($sql, $fld, $dbfld)
{
    $fldtype = $fld . 'type';
    global $$fld;
    global $$fldtype;

    if ($$fld) {
        if (strpos($sql, 'WHERE') > 0) {
            $sql = "$sql AND ";
        } else {
            $sql = "$sql WHERE ";
        }
        $sql = "$sql $dbfld";
        if (isset ($$fldtype)) {
            switch ($$fldtype) {
                case 1 :
                    $sql = "$sql='" . $$fld . "'";
                    break;
                case 2 :
                    $sql = "$sql LIKE '" . $$fld . "%'";
                    break;
                case 3 :
                    $sql = "$sql LIKE '%" . $$fld . "%'";
                    break;
                case 4 :
                    $sql = "$sql LIKE '%" . $$fld . "'";
            }
        } else {
            $sql = "$sql LIKE '%" . $$fld . "%'";
        }
    }

    return $sql;
}

// Update currency exchange rate list from finance.yahoo.com.
// To work around yahoo truncating to 4 decimal places before
// doing a division, leading to >10% errors with weak base_currency,
// we always request in a strong currency and convert ourselves.
// We use ounces of silver,  as if silver ever devalues significantly
// we'll all be pretty much boned anyway,  wouldn't you say?
function currencies_update_yahoo($DBHandle, $instance_table): string
{
    $strong_currency = 'EUR';
    // http://download.finance.yahoo.com/d/quotes.csv?s=USDEUR=X+USDGBP=X&f=sl1d1t1c1ohgv&e=.csv
    $url = "http://download.finance.yahoo.com/d/quotes.csv?s=";
    $return = "";

    $QUERY = "SELECT id, currency, basecurrency FROM cc_currencies ORDER BY id";
    $old_currencies = $instance_table->SQLExec($DBHandle, $QUERY);

    // we will retrieve a .CSV file e.g. USD to EUR and USD to CAD with a URL like:
    // http://download.finance.yahoo.com/d/quotes.csv?s=USDEUR=X+USDCAD=X&f=sl1d1t1c1ohgv
    if (is_array($old_currencies)) {
        $num_cur = count($old_currencies);
        for ($i = 0; $i < $num_cur; $i++) {
            // Finish and add termination ?
            if ($i + 1 == $num_cur) {
                $url .= $strong_currency . $old_currencies[$i][1] . "=X&f=sl1d1t1c1ohgv";
            } else {
                $url .= $strong_currency . $old_currencies[$i][1] . "=X+";
            }

            // Save the index of base_currency when we find it
            if (strcasecmp(BASE_CURRENCY, $old_currencies[$i][1]) == 0) {
                $index_base_currency = $i;
            }
        }

        // Check we found the index of base_currency
        if (!isset ($index_base_currency)) {
            return gettext("Can't find our base_currency in cc_currencies.") . ' ' . gettext('Currency update ABORTED.');
        }

        // Call wget to download the URL to the .CSV file
        $command = "wget '" . $url . "' -O /tmp/currencies.csv  2>&1";
        exec($command);
        // get the file with the currencies to update the database
        $currencies = file("/tmp/currencies.csv");

        $line_base_value = $currencies[$index_base_currency];
        $arr_value = explode(',', $line_base_value);
        if (!is_array($arr_value)) {
            return gettext('Error fetching currencies... Currency update ABORTED!');
        }
        $base_value = $arr_value[1];

        // Check our base_currency will still fund our addiction to tea and biscuits
        if (round($base_value, 5) < 0.00001) {
            return gettext('The base_currency is too small. Currency update ABORTED!');
        }

        // update each row we originally retrieved from cc_currencies
        $i = -1;
        foreach ($currencies as $line_currency) {
            $i++;
            $line_currency = trim($line_currency);
            $line_ex = explode(',', $line_currency);
            $currency = trim($line_ex[1]);

            if ($currency != 0) {
                $currency = $base_value / $currency;
            }

            //  extremely weak currencies are assigned the smallest value the schema permits
            if (round($currency, 5) < 0.00001) {
                $currency = '0.00001';
            }

            // if the currency is base_currency then set to exactly 1.00000
            if ($i == $index_base_currency) {
                $currency = 1;
            }

            $QUERY = "UPDATE cc_currencies SET value='$currency'";
            // if we've changed base_currency,  update each SQL row to reflect this
            if (BASE_CURRENCY != $old_currencies[$i][2]) {
                $QUERY .= ", basecurrency='" . BASE_CURRENCY . "'";
            }

            $QUERY .= " , lastupdate = CURRENT_TIMESTAMP WHERE id ='" . $old_currencies[$i][0] . "'";
            $instance_table->SQLExec($DBHandle, $QUERY, 0);

            if ($i > 200) {
                return $return;
            }
        }
        $return .= gettext('Success! All currencies are now updated.');
    }

    return $return;
}

function generate_invoice_reference(): string
{
    $handle = DbConnect();
    $year = date("Y");
    $invoice_conf_table = new Table('cc_invoice_conf', 'value');
    $conf_clause = "key_val = 'count_$year'";
    $result = $invoice_conf_table->get_list($handle, $conf_clause);

    if (is_array($result) && !empty ($result[0][0])) {
        $count = $result[0][0];
        if (!is_numeric($count)) {
            $count = 0;
        }
        $count++;
        $param_update_conf = "value ='" . $count . "'";
        $clause_update_conf = "key_val = 'count_$year'";
        $invoice_conf_table->Update_table($handle, $param_update_conf, $clause_update_conf);
    } else {
        //insert newcount
        $count = 1;
        $QUERY = "INSERT INTO cc_invoice_conf (key_val ,value) VALUES ( 'count_$year', '1');";
        $invoice_conf_table->SQLExec($handle, $QUERY);
    }

    return $year . sprintf("%08d", $count);
}

function check_demo_mode()
{
    if (DEMO_MODE) {
        if (strpos($_SERVER['HTTP_REFERER'], '?') === false) {
            header("Location: " . $_SERVER['HTTP_REFERER'] . "?msg=nodemo");
        } else {
            header("Location: " . $_SERVER['HTTP_REFERER'] . "&msg=nodemo");
        }

        die();
    }
}

function check_demo_mode_intro()
{
    if (DEMO_MODE) {
        header("Location: PP_intro.php?msg=nodemo");
        die();
    }
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

/*
 * function
 */
function check_cp(): int
{
    $randn = rand(1, 10);
    $ret_val = ($randn == 5) ? 1 : 0;

    $pos_star = strpos(COPYRIGHT, 'star2billing');
    if ($pos_star === false) {
        return $ret_val;
    }
    $pageURL = sprintf(
        "http%s://%s:%d%s",
        $_SERVER["HTTPS"] == "on" ? "s" : "",
        $_SERVER["SERVER_NAME"],
        $_SERVER["SERVER_PORT"],
        $_SERVER["REQUEST_URI"]
    );
    if (str_contains($pageURL, "?")) {
        $pageURL = substr($pageURL, 0, strpos($pageURL, '?'));
    }
    $pos = strpos($pageURL, 'phpsysinfo');

    if ($pos === false) {
        $footer_content = file_get_contents("templates/default/footer.tpl");
        $pos_copyright = strpos($footer_content, '$COPYRIGHT');
        if ($pos_copyright === false) {
            return $ret_val;
        }
    }

    return 0;
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

function get_login_button($DBHandle, $id): string
{
    $inst_table = new Table("cc_card", "useralias, uipass");
    $FG_TABLE_CLAUSE = "id = $id";
    $list_card_info = $inst_table->get_list($DBHandle, $FG_TABLE_CLAUSE);
    $username = $list_card_info[0][0];
    $password = $list_card_info[0][1];
    $link = CUSTOMER_UI_URL;

    if (strpos($link, 'index.php') !== false) {
        $link = substr($link, 0, strlen($link) - 9) . 'userinfo.php';
    } else {
        $link = $link . '/userinfo.php';
    }

    return '<div align="right" style="padding-right:20px;">
        <form action="' . $link . '" method="POST" target="_blank">
            <input type="hidden" name="done" value="submit_log"/>
            <input type="hidden" name="pr_login" value="' . $username . '"/>
            <input type="hidden" name="pr_password" value="'.$password.'"/>
            <a href="javascript:;" onclick="javascript:$(\'form\').submit();" > '.gettext("GO TO CUSTOMER ACCOUNT").'</a>
        </form>
    </div>';
}

function str_icontains(string $haystack, string $needle): bool
{
    if (function_exists("str_contains")) {
        return str_contains(strtolower($haystack), strtolower($needle));
    } else {
        return stripos($haystack, $needle) !== false;
    }
}