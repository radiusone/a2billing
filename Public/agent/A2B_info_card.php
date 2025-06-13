<?php

use A2billing\Agent;

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

$menu_section = 1;
require_once __DIR__ . "/../../common/lib/agent.defines.php";

Agent::checkPageAccess(Agent::ACX_CUSTOMER);

getpost_ifset(["id"]);
/**
 * @var numeric-string|null $id
 */

if (empty($id)) {
    header("Location: A2B_entity_card.php");
}

$DBHandle  = DbConnect();
$card = $DBHandle->GetRow("SELECT * FROM cc_card WHERE id = ?", [$id]);

if (empty($card)) {
    header("Location: A2B_entity_card.php");
}

$callerid = $DBHandle->GetAll("SELECT cid, activated FROM cc_callerid WHERE id_cc_card = ?", [$id]);
$speeddial = $DBHandle->GetAll("SELECT speeddial, name, phone FROM cc_speeddial WHERE id_cc_card = ?", [$id]);
$voipconf = $DBHandle->GetAll(
    "SELECT 'SIP' AS type, username, secret FROM cc_sip_buddies WHERE id_cc_card = ? UNION SELECT 'IAX' AS type, username, secret FROM cc_iax_buddies WHERE id_cc_card = ?",
    [$id, $id]
);
$payments = $DBHandle->GetAll("SELECT id, date, payment, description, id_logrefill FROM cc_logpayment WHERE card_id = ? ORDER BY date DESC LIMIT 10", [$id]);
$refills = $DBHandle->GetAll("SELECT id, date, credit, description FROM cc_logrefill WHERE card_id = ? ORDER BY date DESC LIMIT 10", [$id]);
$dids = $DBHandle->GetAll(
    "SELECT did, destination, cc_did.activated, voip_call FROM cc_did_destination LEFT JOIN cc_did ON cc_did_destination.id_cc_did = cc_did.id WHERE cc_did_destination.id_cc_card = ?",
    [$id]
);

require_once __DIR__ . "/templates/main.php";

echo get_login_button ($id);
?>

<div class="row pb-3 gx-5">
    <div class="col-6">
        <table class="table table-sm caption-top">
            <caption class="fw-bold fs-5"><?= _("Account Info") ?></caption>
            <tbody>
                <tr>
                    <th scope="row"><?= _("Status") ?></th>
                    <td><?= getPaidTypeList()[$card["typepaid"]] ?? $card["typepaid"] ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Account number") ?></th>
                    <td><?= $card["username"] ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Serial number") ?></th>
                    <td><?= str_pad($card["serial"] ?? "", $A2B->config["webui"]["card_serial_length"] ?? 10, "0", STR_PAD_LEFT) ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Web alias") ?></th>
                    <td><?= $card["useralias"] ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Web password") ?></th>
                    <td><?= $card["uipass"] ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Language") ?></th>
                    <td><?= $card["language"] ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Status") ?></th>
                    <td><?= getCardStatus_List()[$card["status"]] ?? $card["status"] ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Creation date") ?></th>
                    <td><?= get_readable_date($card["creationdate"]) ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Expiration date") ?></th>
                    <td><?= get_readable_date($card["expirationdate"]) ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("First use date") ?></th>
                    <td><?= get_readable_date($card["firstusedate"]) ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Last use date") ?></th>
                    <td><?= get_readable_date($card["lastuse"]) ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Lock state") ?></th>
                    <td><?= $card["block"] ? _("Locked") : _("Unlocked") ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Lock PIN") ?></th>
                    <td><?= $card["lock_pin"] ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Lock date") ?></th>
                    <td><?= get_readable_date($card["lock_date"]) ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="col-6">
        <table class="table table-sm caption-top">
            <caption class="fw-bold fs-5"><?= _("Customer Info") ?></caption>
            <tbody>
                <tr>
                    <th scope="row"><?= _("Last name") ?></th>
                    <td><?= $card["lastname"] ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("First name") ?></th>
                    <td><?= $card["firstname"] ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Address") ?></th>
                    <td><?= $card["address"] ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("City") ?></th>
                    <td><?= $card["city"] ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Region") ?></th>
                    <td><?= $card["state"] ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Postcode") ?></th>
                    <td><?= $card["zipcode"] ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Country") ?></th>
                    <td><?= $card["country"] ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Email") ?></th>
                    <td><?= $card["email"] ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Phone") ?></th>
                    <td><?= $card["phone"] ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Mobile/fax") ?></th>
                    <td><?= $card["fax"] ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="row pb-3 gx-5">
    <div class="col">
        <table class="table table-sm caption-top">
            <caption class="fw-bold fs-5"><?= _("Account status") ?></caption>
            <tbody>
                <tr>
                    <th scope="row"><?= _("Balance") ?></th>
                    <td><?= get_money($card["credit"]) ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Currency") ?></th>
                    <td><?= getCurrenciesList()[$card["currency"]] ?? $card["currency"] ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Credit limit") ?></th>
                    <td><?= get_money($card["creditlimit"]) ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Auto refill") ?></th>
                    <td><?= getYesNoList()[$card["autorefill"]] ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Invoice day") ?></th>
                    <td><?= $card["invoiceday"] ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="col">
        <table class="table table-sm caption-top">
            <caption class="fw-bold fs-5"><?= _("Company info") ?></caption>
            <tbody>
                <tr>
                    <th scope="row"><?= _("Company name") ?></th>
                    <td><?= $card["company_name"] ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Company website") ?></th>
                    <td><?= $card["company_website"] ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Tax number") ?></th>
                    <td><?= $card["vat_rn"] ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Traffic per month") ?></th>
                    <td><?= $card["traffic"] ?></td>
                </tr>
                <tr>
                    <th scope="row"><?= _("Traffic target") ?></th>
                    <td><?= $card["traffic_target"] ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="row pb-3 gx-5">
    <div class="col">
        <?php if ($callerid): ?>
        <table class="table table-sm table-striped caption-top">
            <caption class="fw-bold fs-5"><?= _("Caller IDs") ?></caption>
            <thead>
                <tr>
                    <th scope="col"><?= _("Caller ID") ?></th>
                    <th scope="col"><?= _("Activated") ?></th>
                </tr>
            </thead>
            <tbody class="table-group-divider">
                <?php foreach ($callerid as $cid): ?>
                <tr>
                    <td><?= $cid["cid"] ?></td>
                    <td><?= getActivationTrueFalseList()[$cid["activated"]] ?></td>
                </tr>
                <?php endforeach ?>
            </tbody>
        </table>
        <?php else: ?>
        <!-- <p><?= _("No caller IDs for this account") ?></p> -->
        <?php endif ?>
    </div>
    <div class="col">
        <?php if ($speeddial): ?>
        <table class="table table-sm table-striped caption-top">
            <caption class="fw-bold fs-5"><?= _("Speed Dials") ?></caption>
            <thead>
                <tr>
                    <th scope="col"><?= _("Speed dial") ?></th>
                    <th scope="col"><?= _("Number") ?></th>
                    <th scope="col"><?= _("Name") ?></th>
                </tr>
            </thead>
            <tbody class="table-group-divider">
                <?php foreach ($speeddial as $sd): ?>
                    <tr>
                        <td><?= $sd["speeddial"] ?></td>
                        <td><?= $sd["phone"] ?></td>
                        <td><?= $sd["name"] ?></td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
        <?php else: ?>
        <!-- <p><?= _("No speed dials for this account") ?></p> -->
        <?php endif ?>
    </div>
    <div class="col">
        <?php if ($voipconf): ?>
        <table class="table table-sm table-striped caption-top">
            <caption class="fw-bold fs-5"><?= _("VOIP Configuration") ?></caption>
            <thead>
                <tr>
                    <th scope="col"><?= _("Type") ?></th>
                    <th scope="col"><?= _("Username") ?></th>
                    <th scope="col"><?= _("Secret") ?></th>
                </tr>
            </thead>
            <tbody class="table-group-divider">
                <?php foreach ($voipconf as $voip): ?>
                <tr>
                    <td><?= $voip["type"] ?></td>
                    <td><?= $voip["username"] ?></td>
                    <td><?= $voip["secret"] ?></td>
                </tr>
                <?php endforeach ?>
            </tbody>
        </table>
        <?php else: ?>
        <!-- <p><?= _("No VOIP configurations for this account") ?></p> -->
        <?php endif ?>
    </div>
</div>

<div class="row pb-3 gx-5">
    <div class="col">
        <?php if ($payments): ?>
        <table class="table table-sm table-striped caption-top">
            <caption class="fw-bold fs-5"><?= _("Recent Payments") ?></caption>
            <thead>
                <tr>
                    <th scope="col"><?= _("ID") ?></th>
                    <th scope="col"><?= _("Date") ?></th>
                    <th scope="col"><?= _("Amount") ?></th>
                    <th scope="col"><?= _("Description") ?></th>
                    <th scope="col"><?= _("Refill") ?></th>
                </tr>
            </thead>
            <tbody class="table-group-divider">
                <?php foreach ($payments as $pay): ?>
                <tr>
                    <td><?= $pay["id"] ?></td>
                    <td><?= get_readable_date($pay["date"]) ?></td>
                    <td><?= get_money($pay["payment"]) ?></td>
                    <td><?= $pay["description"] ?></td>
                    <td><?= get_refill_link($pay["id_logrefill"]) ?></td>
                </tr>
                <?php endforeach ?>
            </tbody>
        </table>
        <?php else: ?>
        <!-- <p><?= _("No payments for this account") ?></p> -->
        <?php endif ?>
    </div>
</div>

<div class="row pb-3 gx-5">
    <div class="col">
        <?php if ($refills): ?>
        <table class="table table-sm table-striped caption-top">
            <caption class="fw-bold fs-5"><?= _("Recent Refills") ?></caption>
            <thead>
                <tr>
                    <th scope="col"><?= _("ID") ?></th>
                    <th scope="col"><?= _("Date") ?></th>
                    <th scope="col"><?= _("Amount") ?></th>
                    <th scope="col"><?= _("Description") ?></th>
                </tr>
            </thead>
            <tbody class="table-group-divider">
                <?php foreach ($refills as $ref): ?>
                <tr>
                    <td><?= $ref["id"] ?></td>
                    <td><?= get_readable_date($ref["date"]) ?></td>
                    <td><?= get_money($ref["credit"]) ?></td>
                    <td><?= $ref["description"] ?></td>
                </tr>
                <?php endforeach ?>
            </tbody>
        </table>
        <?php else: ?>
        <!-- <p><?= _("No payments for this account") ?></p> -->
        <?php endif ?>
    </div>
</div>

<div class="row pb-3 gx-5">
    <div class="col">
        <?php if ($dids): ?>
        <table class="table table-sm table-striped caption-top">
            <caption class="fw-bold fs-5"><?= _("DIDs and Destinations") ?></caption>
            <thead>
                <tr>
                    <th scope="col"><?= _("DID") ?></th>
                    <th scope="col"><?= _("Destination") ?></th>
                    <th scope="col"><?= _("Activated") ?></th>
                    <th scope="col"><?= _("VoIP") ?></th>
                </tr>
            </thead>
            <tbody class="table-group-divider">
                <?php foreach ($dids as $did): ?>
                <tr>
                    <td><?= $did["did"] ?></td>
                    <td><?= $did["destination"] ?></td>
                    <td><?= getActivationList()[$did["activated"]] ?? $did["activated"] ?></td>
                    <td><?= $did["voip_call"] ?></td>
                </tr>
                <?php endforeach ?>
            </tbody>
        </table>
        <?php else: ?>
        <!-- <p><?= _("No DIDs for this account") ?></p> -->
        <?php endif ?>
    </div>
</div>

<div class="row pb-3 gx-5">
    <div class="col text-end">
        <a href="A2B_entity_card.php?form_action=list">
            <?= _("Return to card list") ?>
        </a>
    </div>
</div>

<?php
require_once __DIR__ . "/templates/footer.php";
