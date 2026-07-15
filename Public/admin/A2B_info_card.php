<?php

use A2billing\Admin;
use A2billing\Connection;

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
require_once __DIR__ . "/../../common/lib/admin.defines.php";

Admin::checkPageAccess(Admin::ACX_CUSTOMER);

getpost_ifset(["id"]);
/**
 * @var numeric-string|null $id
 */

if (empty($id)) {
    header("Location: A2B_entity_card.php");
}

$card = Connection::getConnection("cc_card")
    ->where("id", $id)
    ->first();
if (empty($card)) {
    header("Location: A2B_entity_card.php");
}

$callerid = Connection::getConnection("cc_callerid", "cid", "activated")
    ->where("id_cc_card", $id)
    ->get();
$speeddial = Connection::getConnection("cc_speeddial", "speeddial", "name", "phone")
    ->where("id_cc_card", $id)
    ->get();
$voipconf = Connection::getConnection("cc_sip_buddies", "type", "username", "secret")
    ->where("id_cc_card", $id)
    ->union(
            Connection::getConnection("cc_iax_buddies", "type", "username", "secret")
                ->where("id_cc_card", $id)
    )
    ->get();
$subscriptions = Connection::getConnection("cc_card_subscription AS cs", "cs.id", "cs.startdate", "product_name", "fee")
    ->leftJoin("cc_subscription_service AS ss", "cs.id_subscription_fee", "ss.id")
    ->where("id_cc_card", $id)
    ->orderBy("startdate", "DESC")
    ->get();
$payments = Connection::getConnection("cc_logpayment", "id", "date", "payment", "description", "id_logrefill")
    ->where("card_id", $id)
    ->orderBy("date", "DESC")
    ->limit(10)
    ->get();
$refills = Connection::getConnection("cc_logrefill", "id", "date", "credit", "description")
    ->where("card_id", $id)
    ->orderBy("date", "DESC")
    ->limit(10)
    ->get();
$dids = Connection::getConnection("cc_did_destination", "did", "cc_did.activated", "voip_call")
    ->leftJoin("cc_did", "id_cc_did", "cc_did.id")
    ->where("cc_did_destination.id_cc_card", $id)
    ->get();

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
                    <td><?= getActivationList()[$cid["activated"]] ?></td>
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
        <?php if ($subscriptions): ?>
        <table class="table table-sm table-striped caption-top">
            <caption class="fw-bold fs-5"><?= _("Current Subscriptions") ?></caption>
            <thead>
                <tr>
                    <th scope="col"><?= _("ID") ?></th>
                    <th scope="col"><?= _("Date") ?></th>
                    <th scope="col"><?= _("Subscription") ?></th>
                    <th scope="col"><?= _("Fee") ?></th>
                    <th scope="col"><?= _("Links") ?></th>
                </tr>
            </thead>
            <tbody class="table-group-divider">
                <?php foreach ($subscriptions as $sub): ?>
                <tr>
                    <td><?= $sub["id"] ?></td>
                    <td><?= get_readable_date($sub["startdate"]) ?></td>
                    <td><?= $sub["product_name"] ?></td>
                    <td><?= get_money($sub["fee"]) ?></td>
                    <td>
                        <a href="A2B_entity_subscriber.php?form_action=ask-edit&id=<?= $sub["id"]?>">
                            <div class="bi bi-16 bi-link" aria-label="<?= _("Link to subscription") ?>" title="<?= _("Link to subscription") ?>"></div>
                        </a>
                        <a href="A2B_entity_subscriber.php?form_action=ask-delete&id=<?= $sub["'id"]?>">
                            <div class="bi bi-16 bi-x-circle-fill text-danger" aria-label="<?= _("Delete subscription") ?>" title="<?= _("Delete subscription") ?>"></div>
                        </a>
                    </td>
                </tr>
                <?php endforeach ?>
            </tbody>
        </table>
        <?php else: ?>
        <!-- <p><?= _("No subscriptions for this account") ?></p> -->
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
