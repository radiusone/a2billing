<?php

use A2billing\Table;

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

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

require_once __DIR__ . "/../../common/lib/admin.defines.php";
/**
 * @var string $form_action
 */

getpost_ifset(["OldPassword", "NewPassword", "NewPassword2"]);
/**
 * @var string $OldPassword
 * @var string $NewPassword
 * @var string $NewPassword2
 */

$DBHandle = DbConnect();
$msg = "";

if ($form_action == "ask-modif") {
    if ($OldPassword === "" || $NewPassword === "" || strlen($NewPassword) < 8 || $NewPassword !== $NewPassword2) {
        $msg = '<p class="alert alert-danger">' . _("Check entries, ensure new password is at least 8 characters") . '</p>';
    } else {
        $table = new Table("cc_ui_authen");
        $result = $table->getRow(["login" => $_SESSION["pr_login"]]);

        if ($result && password_verify($OldPassword, $result["pwd_encoded"])) {
            $result = $table->updateRow(
                ["pwd_encoded" => password_hash($NewPassword, PASSWORD_DEFAULT)],
                ["login" => $_SESSION["pr_login"]]
            );
            if ($result) {
                $msg = '<p class="alert alert-success">' . _("Your password has been updated") . '</p>';
            } else {
                $msg = '<p class="alert alert-danger">' . _("An error occurred while updating the password") . '</p>';
            }
        } else {
            $msg = '<p class="alert alert-danger">' . _("Old password was not correct") . '</p>';
        }
    }
}

require_once __DIR__ . "/templates/main.php";
?>
<div class="row pb-3 align-items-center" role="alert">
    <div class="col">
        <?= $msg ?>
    </div>
</div>
<form method="post" id="pwdchange">
    <div class="row mb-3">
        <label class="col-3 col-form-label" for="OldPassword"><?= _("Old Password") ?></label>
        <div class="col">
            <input type="password" id="OldPassword" name="OldPassword" class="form-control" required="required" autocomplete="current-password"/>
            <div class="form-text invalid-feedback"></div>
        </div>
    </div>
    <div class="row mb-3">
        <label class="col-3 col-form-label" for="NewPassword"><?= _("New Password") ?></label>
        <div class="col">
            <input type="password" id="NewPassword" name="NewPassword" class="form-control" required="required" minlength="8" maxlength="32" autocomplete="new-password"/>
            <div class="form-text invalid-feedback"></div>
        </div>
    </div>
    <div class="row mb-3">
        <label class="col-3 col-form-label" for="NewPassword2"><?= _("Confirm New Password") ?></label>
        <div class="col">
            <input type="password" id="NewPassword2" name="NewPassword2" class="form-control" required="required" minlength="8" maxlength="32" autocomplete="new-password" data-invalid="<?= _("New and old passwords must match") ?>"/>
            <div class="form-text invalid-feedback"></div>
        </div>
    </div>
    <div class="row my-4">
        <div class="col-auto ms-auto">
            <button type="submit" class="btn btn-primary" name="form_action" value="ask-modif"><?= _("Change Password") ?></button>
        </div>
    </div>
</form>

<script>
document.getElementById("pwdchange").addEventListener("submit", function(e) {
    /** @var {HTMLInputElement} */
    let newp = document.getElementById("NewPassword");
    /** @var {HTMLInputElement} */
    let conf = document.getElementById("NewPassword2");
    if (newp.value !== conf.value) {
        conf.value = "";
        conf.ariaInvalid = "true";
        conf.classList.add("is-invalid");
        conf.nextElementSibling.textContent = conf.dataset.invalid;
        e.stopPropagation();
        e.preventDefault();
        conf.focus();
    } else {
        conf.ariaInvalid = "false";
        conf.classList.remove("is-invalid");
        conf.nextElementSibling.textContent = "";
    }
});
</script>

<?php
require_once __DIR__ . "/templates/footer.php";
