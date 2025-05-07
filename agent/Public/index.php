<?php

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

require_once __DIR__ . "/../../common/lib/agent.defines.php";

if (is_agent()) {
    // already logged in
    header("Location: PP_intro.php");
    die();
}

getpost_ifset(["error"]);
$error = (int)($error ?? 0);
$lang = $_SESSION["ui_language"] ?? "english";
?>
<!DOCTYPE html>
<html lang="<?= getenv("LANG") ?: "en_US" ?>">
<head>
    <link rel="shortcut icon" href="data:image/gif;base64,R0lGODlhIAAgAOMIAA9ztTSKwUef0Gyw2I/D4rLW6s7m8/X6/AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEKAAgALAAAAAAgACAAAAT+EB1Dhrg46y0GMQeCGBZnnh1Ioiw3HEQra0U5z7ZLFDHm5RtgZleobQgVk/BC5C13y2cxeYJyAIFhcakZbDOBAGCc9Ro7XCbvIsaO34WVgJj2GgRv9ztAwQbichphfwcDeW1jAgaIBQcHeG9mbQQHBYd5O3mNj5qOhgAvBpdvFZ2PARYAm5+hiIgABK+bArOrY5Sie3kDso60vrawE6NkvY+1nqQExHlwwM+FzK/Nwou4cxOQ1Hrbxd5+3WTdqNPh1OWgKp/h2syYjpW8zaix2+iJIPAGRYDL4+YA5uSbUOPeN4CDDObpALDhtg8KHTYTEOKOxIaKJEyIkbCjx48E2CREAAA7"/>
    <title>..:: <?= _("A2Billing Portal") ?> ::..</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <?php /* pages will all be served from /admin/Public, set path accordingly */?>
    <link href="../../common/lib/bootstrap/css/bootstrap.css" rel="stylesheet" type="text/css"/>
</head>
<body>
<form method="post" action="PP_intro.php">
    <input type="hidden" name="done" value="submit_log"/>
    <div class="modal show d-block" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="authTitle" aria-hidden="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow">
                <div class="modal-header">
                    <h3 class="modal-title" id="authTitle"><?= _("Authentication") ?></h3>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <?php if (!empty($error)): ?>
                            <div class="row pb-3">
                                <div class="col p-3 bg-danger bg-gradient text-white">
                                    <strong>
                                        <?php if ($error === 1): ?>
                                            <?= _("AUTHENTICATION REFUSED, please check your user/password!") ?>
                                        <?php elseif ($error === 2): ?>
                                            <?= _("INACTIVE ACCOUNT, Please activate your account!") ?>
                                        <?php elseif ($error === 3): ?>
                                            <?= _("BLOCKED ACCOUNT, Please contact the administrator!") ?>
                                        <?php endif ?>
                                    </strong>
                                </div>
                            </div>
                        <?php endif ?>
                        <div class="row pb-3">
                            <label class="col-4 col-form-label" for="pr_login"><?= _("User") ?></label>
                            <div class="col">
                                <input type="text" name="pr_login" id="pr_login" autofocus="autofocus" autocomplete="on" class="form-control"/>
                            </div>
                        </div>
                        <div class="row pb-3">
                            <label class="col-4 col-form-label" for="pr_password"><?= _("Password") ?></label>
                            <div class="col">
                                <input type="password" name="pr_password" id="pr_password" class="form-control"/>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <select name="ui_language" id="ui_language" class="form-select w-50" aria-label="<?= _("Select the application language") ?>">
                        <option value="english" <?php if ($lang === "english"): ?>selected="selected"<?php endif ?>>🇬🇧 <?= _("English") ?></option>
                        <option value="brazilian" <?php if ($lang === "brazilian"): ?>selected="selected"<?php endif ?>>🇧🇷 <?= _("Brazilian") ?></option>
                        <option value="romanian" <?php if ($lang === "romanian"): ?>selected="selected"<?php endif ?>>🇷🇴 <?= _("Romanian") ?></option>
                        <option value="french" <?php if ($lang === "french"): ?>selected="selected"<?php endif ?>>🇫🇷 <?= _("French") ?></option>
                        <option value="spanish" <?php if ($lang === "spanish"): ?>selected="selected"<?php endif ?>>🇪🇸 <?= _("Spanish") ?></option>
                        <option value="greek" <?php if ($lang === "greek"): ?>selected="selected"<?php endif ?>>🇬🇷 <?= _("Greek") ?></option>
                        <option value="italian" <?php if ($lang === "italian"): ?>selected="selected"<?php endif ?>>🇮🇹 <?= _("Italian") ?></option>
                        <option value="chinese" <?php if ($lang === "chinese"): ?>selected="selected"<?php endif ?>>🇨🇳 <?= _("Chinese") ?></option>
                    </select>
                    <button type="submit" class="btn btn-primary"><?= _("Log In") ?></button>
                </div>
            </div>
        </div>
    </div>
</form>
<script src="../../common/lib/bootstrap/js/bootstrap.js"></script>
<script src="../../common/lib/jquery/jquery.min.js"></script>
<script>
    $("#ui_language").on("change", function () {
        self.location.href = "?ui_language=" + $("#ui_language option:selected").val();
    });
</script>
</body>
</html>
