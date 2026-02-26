<?php

use A2billing\A2bMailException;
use A2billing\Customer;
use A2billing\Forms\FormHandler;
use A2billing\Mail;
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

$menu_section = 8;
require_once __DIR__ . "/../common/lib/customer.defines.php";
require_once __DIR__ . "/../common/form_data/FG_var_customer_did.inc";
/**
 * @var FormHandler $HD_Form
 */

Customer::checkPageAccess(Customer::ACX_DID);

$HD_Form->init();

$form_action ??= "list";
$message = "";
if ($form_action === "add") {
    // we don't want to actually add a DID, so intercept this
    /** @var numeric-string|null $did_id */
    getpost_ifset(["did_id"]);
    $rate = (new Table("cc_did", ["fixrate"]))->getValue(["id" => $did_id]);
    (new Table("cc_charge"))
        ->addRow(["id_cc_card" => Customer::id(), "amount" => abs($rate), "chargetype" => 2, "id_cc_did" => $did_id]);
    (new Table("cc_did"))
        ->updateRow(["iduser" => Customer::id(), "reserved" => 1], ["id" => $did_id]);
    (new Table("cc_card"))
        ->updateRow(["credit" => ["credit - ?", abs($rate)]], ["id" => Customer::id()]);
    (new Table("cc_did_use"))
        ->updateRow(["releasedate" => "CURRENT_TIMESTAMP"], ["id_did" => $did_id, "activated" => 0]);
    (new Table("cc_did_use"))
        ->addRow(["activated" => 1, "id_cc_card" => Customer::id(), "id_did" => $did_id, "month_payed" => 1]);

    $message = _("The DID has been added to your account");
    $form_action = "list";
} elseif ($form_action === "delete") {
    // we don't want to actually delete a DID, so intercept this
    /** @var numeric-string|null $id */
    getpost_ifset(["id"]);
    (new Table("cc_did"))
        ->updateRow(["iduser" => 0, "reserved" => 0], ["id" => $id]);
    (new Table("cc_did_use"))
        ->updateRow(
            ["releasedate" => "CURRENT_TIMESTAMP", "activated" => 0],
            ["id_cc_card" => Customer::id(), "id_did" => $id, "activated" => 1]
        );
    // why ???
    (new Table("cc_did_use"))
        ->addRow(["id_did" => $id, "activated" => 0]);
    (new Table("cc_did_destination"))
        ->deleteRow(["id_cc_did" => $id, "id_cc_card" => Customer::id()]);

    // not sure why there's a mail for release but not add
    $did = (new Table("cc_did", ["did"]))->getValue(["id" => $id]);
    try {
        $mail = new Mail(Mail::$TYPE_DID_RELEASED,Customer::id());
        $mail->replaceInEmail(Mail::$DID_NUMBER_KEY, $did);
        $mail->send();
    } catch (A2bMailException $e) {
    }

    $message = _("The DID has been removed from your account");
    $form_action = "list";
}

$list = $HD_Form->perform_action($form_action);

require_once __DIR__ . "/templates/main.php";

if ($message) {
    echo <<< HTML
    <div class='row pb-3' id='create_actionfinish'><div class='col'><p class='alert alert-info'>$message</p></div></div>
    HTML;
}
$HD_Form->create_toppage($form_action);
$HD_Form->create_form($form_action, $list);

require_once __DIR__ . "/templates/footer.php";
