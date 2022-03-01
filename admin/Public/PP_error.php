<?php

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

require_once "../../common/lib/admin.defines.php";

session_destroy();
getpost_ifset(['err_type','c']);
/**
 * @var int $err_type
 * @var string $c
 */

if (!isset($err_type)) {
    $err_type = 0;
}
if (!isset($c))	{
    $c = 0;
}

//Error Type == 0 Mean Critical Error dont need to show left menu.
//Error Type == 1 Mean User generated error.and it will show menu to him too.
if ($err_type == 0) {
    $smarty->display('header.tpl');
} else {
    $smarty->display('main.tpl');
}

$error["0"] 			= gettext("ERROR : ACCESS REFUSED");
$error["syst"] 			= gettext("Sorry a problem occur on our system, please try later!");
$error["errorpage"] 	= gettext("There is an error on this page!");
$error["accessdenied"] 	= gettext("Sorry, you don't have access to this page !");
$error["ERR-0001"] 		= gettext("Invalid User Id !");
$error["ERR-0002"] 		= gettext("No such card number found. Please check your card number!");

?>

<div class="modal show d-block" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="authTitle" aria-hidden="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow">
            <div class="modal-header">
                <h4 class="modal-title" id="authTitle"><?= _("Error Page") ?></h4>
            </div>
            <div class="modal-body">
                <?= $error[$c] ?>
            </div>
            <div class="modal-footer">
                <a class="btn btn-primary" href="index.php"><?= _("Go to Login Page") ?></a>
            </div>
        </div>
    </div>
</div>

<?php
if ($err_type == 0) {
    echo '</body></html>';
} else {
    $smarty->display('footer.tpl');
}
