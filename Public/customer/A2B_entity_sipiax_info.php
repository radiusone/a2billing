<?php

use A2billing\A2Billing;
use A2billing\Customer;
use A2billing\Table;

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
 */

require_once __DIR__ . "/../../common/lib/customer.defines.php";
/**
 * @var A2Billing $A2B
 */

Customer::checkPageAccess(Customer::ACX_SIP_IAX);

/***********************************************************************************/

getpost_ifset(["configtype"]);

$configtype ??= "SIP";

if ($configtype === "IAX") {
    $config_name = _("IAX Config");
    $config_file = _("iax.conf");
    $table = "cc_iax_buddies";
} else {
    $config_name = _("SIP Config");
    $config_file = _("sip.conf");
    $table = "cc_sip_buddies";
}

$sip_iax_data = (new Table($table, ["id", "username", "secret", "disallow", "allow", "type", "host", "context"]))
    ->getRow(["id_cc_card" => Customer::id()]);

//Additonal parameters
$additional_sip = explode("|", $A2B->config['sip-iax-info']['sip_additional_parameters'] ?? "");
$additional_iax = explode("|", $A2B->config['sip-iax-info']['iax_additional_parameters'] ?? "");

require_once __DIR__ . "/templates/main.php";

echo create_help(_("Configuration information for SIP and IAX clients"));
?>
<?php if (!$sip_iax_data): ?>
<div class="row mb-3">
    <div class="col">
        <p class="alert alert-info"><?= sprintf(_("The %s peer is not configured."), $configtype) ?></p>
    </div>
</div>
<?php endif ?>

<form id="configform" class="row mb-3">
    <label for="configtype" class="col-2 col-form-label-sm"><?= _("Configuration Type") ?></label>
    <div class="col-8">
        <select name="configtype" id="configtype" class="form-select form-select-sm w-100">
            <option <?= $configtype === "SIP" ? "selected=\"selected\"" : "" ?>>SIP</option>
            <option <?= $configtype === "IAX" ? "selected=\"selected\"" : "" ?>>IAX</option>
        </select>
    </div>
</form>

<script>
    document.getElementById("configtype").addEventListener("change", e => e.target.form.requestSubmit());
</script>

<?php
if (!$sip_iax_data) {
    require_once __DIR__ . "/templates/footer.php";

    return;
} ?>

<div class="row">
    <div class="col"><strong><?= (sprintf(_("%s URI"), $configtype)) ?>:</strong></div>
    <div class="col"><?= $A2B->config["sip-iax-info"]["sip_iax_info_host"] ?></div>
</div>
<div class="row">
    <div class="col"><strong><?= _("Username") ?>:</strong></div>
    <div class="col"><?= $sip_iax_data["username"] ?></div>
</div>
<div class="row mb-3">
    <div class="col"><strong><?= _("Password") ?>:</strong></div>
    <div class="col"><?= $sip_iax_data["secret"] ?></div>
</div>
<div class="row mb-3">
    <div class="col">
        <p><?= sprintf(_("To configure your Asterisk server, copy and paste this into your %s file"), $config_file) ?></p>
        <p>
            <textarea class="form-control w-100" rows="10">
<?php if ($configtype === "IAX"): ?>
[<?= $A2B->config["sip-iax-info"]["sip_iax_info_trunkname"]; ?>]
username=<?= $sip_iax_data["username"] ?>
type=friend
secret=<?= $sip_iax_data["secret"] ?>

host=<?= $A2B->config["sip-iax-info"]["sip_iax_info_host"] ?>

disallow=all
context=<?= $sip_iax_data["context"] ?> ; change for proper context
allow=<?= $A2B->config["sip-iax-info"]["sip_iax_info_allowcodec"] ?>

<?php foreach ($additional_iax as $v): ?>
<?= trim($v) ?>

<?php endforeach ?>
<?php else: ?>
[<?= $A2B->config["sip-iax-info"]["sip_iax_info_trunkname"]; ?>]
username=<?= $sip_iax_data["username"] ?>

type=friend
secret=<?= $sip_iax_data["secret"] ?>

host=<?= $A2B->config["sip-iax-info"]["sip_iax_info_host"] ?>

fromuser=<?= $sip_iax_data["username"] ?>

disallow=all
context=<?= $sip_iax_data["context"] ?> ; change for proper context
allow=<?= $A2B->config["sip-iax-info"]["sip_iax_info_allowcodec"] ?>

<?php foreach ($additional_sip as $v): ?>
<?= trim($v) ?>

<?php endforeach ?>
<?php endif ?>
            </textarea>
        </p>
    </div>
</div>

<?php
require_once __DIR__ . "/templates/footer.php";
