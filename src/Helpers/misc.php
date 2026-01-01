<?php

use A2billing\Connection;
use A2billing\Table;
use Amenadiel\JpGraph\Graph\Graph;
use Amenadiel\JpGraph\Plot\BarPlot;
use PHPMailer\PHPMailer\PHPMailer;
use Random\RandomException;

/**
 * This file is part of A2Billing (http://www.a2billing.net/)
 *
 * A2Billing, Commercial Open Source Telecom Billing platform,
 * powered by Star2billing S.L. <http://www.star2billing.com/>
 *
 * @copyright   Copyright © 2004-2015 - Star2billing S.L.
 * @copyright   Copyright © 2022-2025 RadiusOne Inc.
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
    $len = (new Table("cc_config", ["config_value"]))
        ->getValue(["config_key" => "interval_len_cardnumber"]);
    if ($len) {
        $len = min(split_data($len) ?: [10]);
    } else {
        $len = 10;
    }

    return $len;
}

/**
 * Converts a range string to array, e.g. "1-4,7,9" => [1, 2, 3, 4, 7, 9]
 * @param string|null $values
 * @return int[]
 */
function split_data(?string $values): array
{
    $return = [];
    $values_array = explode(",", "$values");
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

    return array_unique($return);
}

/**
 * a2b_round: specific function to use the same precision everywhere
 *
 * @param int|float|null $number
 * @param int $PRECISION
 * @return float
 */
function a2b_round($number, int $PRECISION = 6): float
{

    return round($number ?? 0, $PRECISION);
}

/**
 * a2b_mail - function mail used in a2billing
 *
 * @throws \PHPMailer\PHPMailer\Exception
 */
function a2b_mail($to, $subject, $mail_content, $from = 'root@localhost', $fromname = '', $contenttype = 'multipart/alternative')
{

    $mail = new PHPMailer(true);

    if ($A2B->config['global']['smtp_server'] ?? false) {
        $mail->Mailer = "smtp";
    } else {
        $mail->Mailer = "sendmail";
    }

    $mail->Host = $A2B->config['global']['smtp_host'] ?? null;
    $mail->Username = $A2B->config['global']['smtp_username'] ?? "";
    $mail->Password = $A2B->config['global']['smtp_password'] ?? "";
    $mail->Port = $A2B->config['global']['smtp_port'] ?? '25';
    $mail->SMTPSecure = $A2B->config['global']['smtp_secure'] ?? null;
    $mail->CharSet = 'UTF-8';
    $mail->SMTPAuth = !empty($mail->Username);
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

/**
 * @return array<string,array>
 */
function get_currencies(): array
{
    $currencies_list = [];
    $result = (new Table("cc_currencies", ["currency", "name", "value"]))->getRows();
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

/**
 * Do Currency Conversion.
 *
 * @param int|float $amount the amount to be converted.
 * @param string $from_cur Source Currency
 * @param string $to_cur Destination Currecny
 */
function convert_currency($amount, string $from_cur, string $to_cur)
{
    if (!is_numeric($amount) || ($amount == 0)) {
        return 0;
    }
    if ($from_cur == $to_cur) {
        return $amount;
    }
    $currencies_list = get_currencies();
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
function write_log(string $logfile, string $output)
{
    $result = true;
    if (!file_exists($logfile)) {
        $result = touch($logfile);
    }
    $string_log = sprintf(
        "[%s]:[%s]\n",
        (new DateTime())->format("d/m/Y H:i:s"),
        $output
    );
    if ($result && is_writable($logfile)) {
        error_log($string_log, 3, $logfile);
    } else {
        error_log($string_log);
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
        $val = $_REQUEST[$test_var];
        //rebuild the search parameter to filter character to format card number
        if ($test_var == 'username' || $test_var == 'filterprefix') {
            //rebuild the search parameter to filter character to format card number
            //todo: ???
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
 *
 * @param float|null $value
 * @param int|null $decimals
 * @param $currency
 * @return string
 */
function get_money(?float $value, ?int $decimals = null, $currency = BASE_CURRENCY): string
{
    $value ??= 0;
    if (class_exists("NumberFormatter")) {
        static $formatter = null;
        if (is_null($formatter)) {
            $formatter = NumberFormatter::create(
                getenv("LANG") ?: "en_US",
                NumberFormatter::CURRENCY
            );
            if (isset($decimals)) {
                // leave at locale default unless specified
                $formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, $decimals);
            }
            $formatter->setAttribute(NumberFormatter::ROUNDING_MODE, NumberFormatter::ROUND_HALFUP);
        }

        return $formatter->formatCurrency($value, $currency);
    }
    $decimals ??= 2;

    return sprintf("%0.{$decimals}f %s", $value, strtoupper($currency));
}

/**
 * Used as callback for list/form elements
 * @param $sessiontime
 * @return string
 */
function get_minute($sessiontime): string
{
    // todo: what is this?
    // see if this came in via post/get
    getpost_ifset(["resulttype"], $p);

    if (($p["resulttype"] ?? "min") === "min") {
        $sessiontime = sprintf("%02d:%02d", intval($sessiontime / 60), $sessiontime % 60);
    }

    return $sessiontime;
}

/**
 * Return time in "d h m" format
 *
 * @param int $sec
 * @param bool $include_seconds
 * @return string
 */
function get_timespan(int $sec, bool $include_seconds = false): string
{
    $days = intdiv($sec, 86400);
    $hours = intdiv($sec - ($days * 86400), 3600);
    $minutes = intdiv($sec - ($days * 86400) - ($hours * 3600), 60);
    $seconds = $sec - ($days * 86400) - ($hours * 3600) - ($minutes * 60);

    if ($days) {
        return $include_seconds
            ? sprintf("%dd %dh %dm %ds", $days, $hours, $minutes, $seconds)
            : sprintf("%dd %dh %dm", $days, $hours, $minutes);
    }

    if ($hours) {
        return $include_seconds
            ? sprintf("%dh %dm %ds", $hours, $minutes, $seconds)
            : sprintf("%dh %dm", $hours, $minutes);
    }

    return $include_seconds
        ? sprintf("%dm %ds", $minutes, $seconds)
        : sprintf("%dm", $minutes);
}

/**
 * Used as callback for list/form elements
 *
 * @param float|null $var
 * @return string
 */
function get_percent(?float $var): string
{
    if (isset ($var)) {
        return round($var, 2, PHP_ROUND_HALF_UP) . "%";
    } else {
        return "n/a";
    }
}

/**
 * Rounds and formats a currency amount to four decimal places
 * Used as callback for list/form elements
 *
 * @param float|int|string $amt
 * @return string
 */
function get_money_precise($amt): string
{
    return get_money($amt, 4);
}

/**
 * Used as callback for list/form elements
 * @param $value
 * @return string
 */
function get_monitorfile_link($value): string
{
    $MONITOR_PATH = (new Table("cc_config", ["config_value"]))
        ->getValue(["config_key" => "monitor_path"]) ?: "/";
    $format_list = ['wav', 'gsm', 'mp3', 'sln', 'g723', 'g729'];
    $find_record = false;
    foreach ($format_list as $c_format) {
        $myfile = "/$value.$c_format";
        $dl_full = $MONITOR_PATH . $myfile;
        if (file_exists($dl_full)) {
            $find_record = true;
            break;
        }
    }
    if (!$find_record) {
        return "";
    }

    $myfile = base64_encode($myfile);

    return <<< HTML
        <a target='_blank' href='A2B_report_calls.php?download=file&amp;file=$myfile'>
            <img alt="access recording" src="" height="18" />
        </a>
        HTML;
}

/**
 * Used as callback for list/form elements
 * @param string|int|null $id
 * @return string
 */
function get_refill_link(?int $id): string
{
    $credit = (new Table("cc_logrefill", ["credit"]))
        ->getValue(["id" => $id ?? 0]);

    return is_null($credit)
        ? htmlspecialchars(_("n/a"))
        : sprintf(
            "<a href=\"%s%d\">%s</a>",
            "A2B_info_refill.php?id=",
            $id,
            get_money(floatval($credit))
        );
}

/**
 * Used as callback for list/form elements
 * @param string|int|null $id
 * @return string
 */
function get_agent_refill_link(?int $id): string
{
    $credit = (new Table("cc_logrefill_agent", ["credit"]))
        ->getValue(["id" => $id ?? 0]);

    return is_null($credit)
        ? htmlspecialchars(_("n/a"))
        : sprintf(
            "<a href=\"%s%d\">%s</a>",
            "A2B_info_refill.php?type=agent&id=",
            $id,
            get_money(floatval($credit))
        );
}

/**
 * Used as callback for list elements
 * @param string $value
 * @return string
 */
function format_phone_number(?string $value): string
{
    $value = preg_replace("/^(00|011)/", "", $value ?? "");
    if (preg_match("/^(1?)([2-9]\d\d)([2-9]\d\d)(\d\d\d\d)$/",$value, $matches)) {
        $value = "";
        if ($matches[1]) {
            $value = "1-";
        }
        $value .= "$matches[2]-$matches[3]-$matches[4]";
    }

    return $value ?: _("n/a");
}

/**
 * @param string $format
 * @return string
 */
function generate_random_value(string $format): string
{
    $output = "";
    foreach (str_split($format) as $char) {
        if ($char === "#") {
            try {
                $output .= random_int(0, 9);
            } catch (RandomException) {
                $output .= rand(0, 9);
            }
        } elseif ($char = "X") {
            do {
                try {
                    $randint = random_int(48, 122);
                } catch (RandomException) {
                    $randint = rand(48, 122);
                }
                $chr = chr($randint);
            } while (!preg_match("/^[0-9a-z]$/i", $chr));
            $output .= $chr;
        } else {
            $output .= $char;
        }
    }

    return $output;
}

function generate_unique_value($table = "cc_card", $len = 0, $field = "username")
{
    if (empty($len)) {
        $len = get_cardlength();
    }

    for ($k = 0; $k <= 200; $k++) {
        $card_gen = generate_random_value(str_repeat("#", $len));

        $val = (new Table($table, [$field]))->getValue([$field => $card_gen]);
        if (empty($val)) {

            return $card_gen;
        }
    }
    echo "ERROR : Impossible to generate a $field not yet used!";
    exit ();
}

function gen_card_with_alias($length_cardnumber = null)
{
    global $A2B;

    $DBHandle = DbConnect();

    if (empty($length_cardnumber)) {
        $length_cardnumber = get_cardlength();
    }

    for ($k = 0; $k <= 200; $k++) {
        $card_gen = generate_random_value(str_repeat("#", $length_cardnumber));
        $alias_gen = generate_random_value(str_repeat("#", $A2B->config['global']['len_aliasnumber'] ?? 10));

        $query = "SELECT username FROM cc_card WHERE username=? OR useralias=? OR username=? OR useralias=?";
        $val = $DBHandle->GetOne($query, [$card_gen, $alias_gen, $alias_gen, $card_gen]);
        if (is_null($val)) {

            return [$card_gen, $alias_gen];
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

    $error = "";
    if (empty($the_file) || $the_file === "none") {
        $error = _("You did not upload anything!");
    } elseif (!file_exists($the_file) || !is_readable($the_file)) {
        $error = _("Failed to upload the file, The file you uploaded may not exist on disk.");
    } elseif (filesize($the_file) > MY_MAX_FILE_SIZE_IMPORT) {
        $error = _("File size is greater than allowed limit.");
    } elseif (!in_array($the_file_type, $allowed_types)) {
        $error = sprintf(_("File type %s is not allowed"), $the_file_type);
    }

    return $error ? sprintf(_("ERROR: %s"), $error) : "";
}

function get_timezones(): array
{
    return (new Table("cc_timezone", ["gmtzone", "id"]))->getColumn();
}

function get_login_button($id): string
{
    global $A2B;

    $row = (new Table("cc_card", ["useralias", "uipass"]))
        ->getRow(["id" => $id]);
    if (!$row) {
        return "";
    }
    $username = htmlspecialchars($row["useralias"]);
    $password = htmlspecialchars($row["uipass"]);
    $link = $A2B->config['global']['customer_ui_url'];

    if (str_ends_with($link, "index.php")) {
        $link = substr($link, 0, -9) . "A2B_info_card.php";
    } else {
        $link .= "/A2B_info_card.php";
    }
    $link = htmlspecialchars($link);
    $label = htmlspecialchars(_("GO TO CUSTOMER ACCOUNT"));

    return <<< HTML
        <form class="row" action="$link" method="POST" target="_blank">
            <div class="col d-flex justify-content-end">
                <input type="hidden" name="done" value="submit_log"/>
                <input type="hidden" name="pr_login" value="$username"/>
                <input type="hidden" name="pr_password" value="$password"/>
                <button class="btn btn-sm btn-primary" type="submit">$label</button>
            </div>    
        </form>
        HTML;
}

function DbConnect(): ADOConnection
{
    return Connection::GetDBHandler();
}

function create_help($text): string
{
    $result = (new Table("cc_config", "config_value"))
        ->getValue(["config_key" => "show_help"]);
    if ($result !== "1") {
        return "";
    }

    $wiki = htmlspecialchars(_("For further information please consult")) . ' <a target="_blank" href="https://web.archive.org/web/20120324185427/http%3A%2F%2Fwww.asterisk2billing.org%2Fdocumentation%2F">' . htmlspecialchars(_("the online documention")) . '</a>.';

    return <<< HTML
        <div class="alert alert-info dismissible fade show d-flex align-items-center">
            <div class="bi bi-question-circle flex-shrink-0 me-2 fs-1"><?= __("Help") ?></div>
            <div class="flex-grow-1 mx-2">
                <div class="mx-2">$text</div>
                <hr class="m-2"/>
                <div class="mx-2"><small>$wiki</small></div>
            </div>
            <button type="button" class="btn-close mb-auto" data-bs-dismiss="alert" aria-label="Close"></button>
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
 * Convert an array to a key=value string
 *
 * @param array $arr the array to process
 * @param callable|null $key_callback a callback to apply to each of the keys
 * @param callable|null $value_callback a callback to apply to each of the values
 * @param string $val_sep the separator between keys and values
 * @param string $pair_sep the separator between key/value pairs
 * @return string
 */
function array_kv(
    array $arr,
    ?callable $key_callback = null,
    ?callable $value_callback = null,
    string $val_sep = " = ",
    string $pair_sep = ", "
): string
{
    $keys = array_keys($arr);
    $vals = array_values($arr);

    if (is_callable($key_callback)) {
        $keys = array_map($key_callback, $keys);
    }
    if (is_callable($value_callback)) {
        $vals = array_map($value_callback, $vals);
    }

    return implode($pair_sep, array_map(fn ($k, $v) => "$k$val_sep$v", $keys, $vals));
}

/**
 * Convert an associative array to a string of HTML attributes in k="v" format
 *
 * @param array<string,mixed> $attributes
 * @return string
 */
function array_html_attr(array $attributes): string
{
    $return = "";
    foreach ($attributes as $attribute => $value) {
        $attribute = preg_replace(
            "/[^a-z0-9_.-]/",
            "",
            strtolower($attribute)
        );
        $value = htmlspecialchars("$value");
        $return .= "$attribute=\"$value\" ";
    }

    return trim($return);
}

function abbr(string $content, string $title, string $class = ""): string
{
    return sprintf(
        "<abbr title=\"%s\" class=\"%s\">%s</abbr>",
        htmlspecialchars($title),
        htmlspecialchars($class),
        htmlspecialchars($content)
    );
}

/**
 * Performs str_replace only if the test expression matches a fixed value
 *
 * @param $search
 * @param $replace
 * @param string $subject
 * @param $test
 * @param $match
 * @return string
 */
function str_replace_conditional($search, $replace, string $subject, $test, $match): string
{
    return $test == $match
        ? str_replace($search, $replace, $subject)
        : $subject;
}

function add(...$args): int
{
    return array_sum(array_map("intval", $args));
}

function sub(...$args): int
{
    $val = intval(array_shift($args));
    array_walk($args, function ($v) use (&$val) {$val -= (int)$v;});

    return $val;
}

function sub_money(...$args): string
{
    return get_money(sub(...$args));
}

/**
 * @param int|string $date
 * @return string
 */
function get_readable_date($date): string
{
    if (empty($date)) {
        return _("N/A");
    }
    try {
        return (new DateTime($date))->format("D, d M y H:i:s");
    } catch (Exception $e) {
        return _("N/A");
    }
}

/**
 * Take a jpgraph object and turn it into a data URI
 *
 * @param Graph $graph
 * @return string
 */
function graphToDataUri(Graph $graph): string {
    try {
        $resource = $graph->Stroke("__handle");
    } catch (Exception $e) {
        // todo: error message?
        return "";
    }
    if (is_resource($resource) || get_class($resource) === "GdImage") {
        ob_start();
        imagepng($resource);
        $img = ob_get_clean();

        return "data:image/png;base64," . base64_encode($img);
    }

    return "";
}

/**
 * @param array $data keys are ignored
 * @param Graph|null $graph if supplied, plot will be appended to this graph
 * @return BarPlot
 */
function createBarPlot(array $data, Graph $graph = null): BarPlot
{
    $bplot = new BarPlot(array_values($data));
    $bplot->SetColor("yellow@0.3");
    $bplot->SetWeight(2);
    $bplot->SetFillColor('orange');
    $bplot->SetShadow();
    $bplot->value->SetFormat("%d");
    $bplot->value->SetAlign("center");
    $bplot->value->Show();
    if ($graph) {
        $graph->Add($bplot);
    }

    return $bplot;
}

/**
 * @param array $data associative array of data
 * @param string $title the title of the graph, if creating one
 * @param Graph|null $graph the graph object will be created if not passed
 * @return Graph
 */
function createBarGraph(array $data, string $title, Graph $graph = null): Graph
{
    $graph ??= createBarGraphBody($title);

    $graph->yaxis->SetTickPositions(range(0, ceil(max($data) * 1.1)));
    $graph->xaxis->SetTickLabels(array_keys($data));

    createBarPlot($data, $graph);

    return $graph;
}

/**
 * Create a graph body, for applying plots to
 *
 * @param $title
 * @return Graph
 */
function createBarGraphBody($title): Graph {
    $graph = new Graph(800, 600);
    $graph->SetMargin(60, 60, 45, 90); //droit,gauche,haut,bas
    $graph->SetMarginColor('white');
    $graph->SetScale("textlin");
    $graph->SetFrame(false);
    $graph->SetBackgroundGradient('#FFFFFF', '#CDDEFF:0.8', GRAD_HOR, BGRAD_PLOT);
    $graph->tabtitle->Set($title);
    $graph->tabtitle->SetWidth(TABTITLE_WIDTHFULL);

    $graph->xgrid->Show();
    $graph->xgrid->SetColor('gray@0.5');
    $graph->ygrid->SetColor('gray@0.5');
    $graph->ygrid->SetFill(true, '#EFEFEF@0.5', '#CDDEFF@0.5');

    $graph->yaxis->scale->SetGrace(3);
    $graph->xaxis->SetLabelAngle(90);

    return $graph;
}
