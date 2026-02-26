<?php

require_once "../../common/lib/customer.defines.php";

$code = generate_random_value("XXXXXX");
$_SESSION["captcha_code"] = $code;
$seed = generate_random_value("######");

$captcha_gd = 1;

if ($captcha_gd) {
    include 'captcha_gd.php';
} else {
    include 'captcha_non_gd.php';
}

$captcha = new captcha();
$captcha->execute($code, $seed);
