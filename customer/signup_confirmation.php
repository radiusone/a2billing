<?php

use A2billing\Mail;
use A2billing\A2bMailException;

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

require_once "../common/lib/customer.defines.php";

if (!$A2B->config["signup"]['enable_signup']) {
    echo ("No Signup page!");
    exit;
}

if (!isset ($_SESSION["date_mail"]) || (time() - $_SESSION["date_mail"]) > 60) {
    $_SESSION["date_mail"] = time();
} else {
    sleep(3);
    echo gettext("Sorry the confirmation email has been sent already, multi-signup are not authorized! Please wait 2 minutes before making any other signup!");
    exit ();
}

if (!isset ($_SESSION["cardnumber_signup"]) || strlen($_SESSION["cardnumber_signup"]) <= 1) {
    echo gettext("Error : No User Created.");
    exit ();
}

$FG_DEBUG = 0;
$DBHandle = DbConnect();

$activatedbyuser = $A2B->config["signup"]['activatedbyuser'];

$lang_code = $_SESSION["language_code"];
if (!$activatedbyuser) {
    $mailtype = Mail :: $TYPE_SIGNUP;
} else {
    $mailtype = Mail :: $TYPE_SIGNUPCONFIRM;
}

try {
    $mail = new Mail($mailtype, $_SESSION["id_signup"], $_SESSION["language_code"]);
} catch (A2bMailException $e) {
    echo "<br>" . gettext("Error : No email Template Found");
    exit ();
}

$QUERY = "SELECT username, lastname, firstname, email, uipass, credit, useralias, loginkey FROM cc_card WHERE id=?";

$list = $DBHandle->GetRow($QUERY, [$_SESSION["id_signup"]]);

if ($list === false || $list === []) {
    echo "<br>" . gettext("Error : No such user found in database");
    exit ();
}

if ($FG_DEBUG == 1)
    echo "<br><b>BELOW THE CARD PROPERTIES </b><hr><br>";

list ($username, $lastname, $firstname, $email, $uipass, $credit, $cardalias, $loginkey) = $list;
if ($FG_DEBUG == 1)
    echo "<br># $username, $lastname, $firstname, $email, $uipass, $credit, $cardalias #<br>";

try {
    $mail->send();
} catch (A2bMailException $e) {
    $error_msg = $e->getMessage();
}

$smarty->display('signup_header.tpl');
?>

<blockquote>
    <div align="center"><br><br>
     <font color="#FF0000"><b><?php echo gettext("SIGNUP CONFIRMATION"); ?></b></font><br>
          <br/><br/>

    <?php if (!$activatedbyuser) { ?>
        <?php echo $lastname; ?> <?php echo $firstname ?>, <?php echo gettext("thank you for registering with us!");?><br>
        <?php echo gettext("An activation email has been sent to"); ?> <b><?php echo $email ?></b><br><br>
    <?php } else { ?>
          <?php echo $lastname ?> <?php echo $firstname ?>, <?php echo gettext("Thank you for registering with us !");?><br>
          <?php echo gettext("An email confirming your information has been sent to"); ?> <b><?php echo $email ?></b><br><br>
            <h3>
              <?php echo gettext("Your cardnumber is "); ?> <b><font color="#00AA00"><?php echo $username ?></font></b><br><br><br>
              <?php echo gettext("To login to your account :"); ?><br>
              <?php echo gettext("Your card alias (login) is "); ?> <b><font color="#00AA00"><?php echo $cardalias ?></font></b><br>
              <?php echo gettext("Your password is "); ?> <b><font color="#00AA00"><?php echo $uipass ?></font></b><br>
            </h3>
    <?php } ?>

</div>
</blockquote>

<br><br><br>
<br><br><br>

<?php

$smarty->display('signup_footer.tpl');
