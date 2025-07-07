<?php

use A2billing\Customer;
use A2billing\Forms\FormBO;
use A2billing\Forms\FormHandler;
use A2billing\Forms\Validator;
use A2billing\Table;

/**
 * This file is part of A2Billing (http://www.a2billing.net/)
 *
 * A2Billing, Commercial Open Source Telecom Billing platform,
 * powered by Star2billing S.L. <http://www.star2billing.com/>
 *
 * @copyright   Copyright (C) 2004-2012 - Star2billing S.L.
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

$form_action ??= "list";
$HD_Form = new FormHandler(
    "cc_did_destination",
    _("DID Destination"),
    "cc_did_destination.id",
    $form_action === "list"
        ? ["cc_did" => ["cc_did.id", "cc_did_destination.id_cc_did"]]
        : []
);

$HD_Form->list_query_order_columns = ["destination"];
$HD_Form->list_query_order_direction = "DESC";
if (is_customer()) {
    $HD_Form->list_query_conditions["id_cc_card"] = Customer::id();
    $HD_Form->update_query_conditions["id_cc_card"] = Customer::id();
}

$HD_Form->AddListTopButton("A2B_entity_did_destination.php?form_action=ask-add", _("Add Destination"), "telephone-inbound-fill");

$HD_Form->help_text = create_help(_("DID number list with destinations."));

$activated_list = getActivationList();
$priority_list = range(1, 5);
$validated_list = [_("Pending"), _("Validated")];
$yesno_list = getYesNoList();

$HD_Form->AddListValue(_("DID"), "cc_did.did", "format_phone_number");
if (!is_customer()) {
    $HD_Form->AddListValue(_("Account Number"), "id_cc_card", [Customer::class, "getUsername"]);
}
$HD_Form->AddListValue(_("Destination"), "destination");
$HD_Form->AddListValue(_("Creation Date"), "cc_did_destination.creationdate");
$HD_Form->AddListMapping(_("Active"), "cc_did_destination.activated", $activated_list);
$HD_Form->AddListMapping(_("VoIP"), "voip_call", $yesno_list);
$HD_Form->AddListMapping(_("Priority"), "priority", $priority_list);
$HD_Form->AddListValue(_("Minutes Used"), "cc_did_destination.secondusedreal", "get_minute");
$HD_Form->AddListMapping(_("Validated"), "validated", $validated_list);

$HD_Form->FG_ENABLE_EDIT_BUTTON = true;
$HD_Form->FG_ENABLE_DELETE_BUTTON = true;
$HD_Form->FG_ENABLE_ADD_BUTTON = true;

$HD_Form->AddEditElement(
    _("Destination"),
    "destination",
    _("Enter the phone number you wish to call, or the VoIP client to reach. (ie: 347894999 or SIP/jeremy@182.212.1.45). If the call is VoIP, the VoIP_Call must be set to yes."),
    ["maxlength" => 120],
    [Validator::class, "min3Chars"],
    _("Insert the DID")
);

if (!is_customer() && $form_action !== "ask-edit" && $form_action !== "edit") {

    $HD_Form->AddEditPopup(
        _("Customer"),
        "id_cc_card",
        "A2B_entity_card.php",
        _("Select the Customer ID"),
        [],
        [Validator::class, "numeric"],
        _("Insert the Customer ID")
    );

    $HD_Form->AddEditSqlSelect(_("DID"), "id_cc_did", new Table("cc_did", ["did", "id"]));
} elseif ($form_action !== "ask-edit" && $form_action !== "edit") {
    $HD_Form->FG_ADD_QUERY_HIDDEN_INPUTS["id_cc_card"] = Customer::id();

    $HD_Form->AddEditSqlSelect(
        _("Use an existing DID"),
        "id_cc_did",
        new Table("cc_did", ["did", "id"]),
        ["iduser" => Customer::id()],
        "",
        ["" => ""],
        "",
        ["id" => "id_cc_did_old"],
        "",
        fn (array $row) => [$row["id"], format_phone_number($row["did"])]
    );

    $HD_Form->AddEditSqlSelect(
        _("Add a new DID"),
        "id_cc_did",
        new Table(
            "cc_did",
            ["id", "did", "fixrate", "connection_charge", "selling_rate", "aleg_retail_connect_charge", "aleg_retail_cost_min"]
        ),
        ["id_cc_didgroup" => $_SESSION["id_didgroup"], "reserved" => 0],
        "",
        ["" => ""],
        "",
        ["id" => "id_cc_did_new"],
        "",
        function (array $row) {
            $text = format_phone_number($row["did"]) . " (";
            if ($row["fixrate"] > 0) {
                $text .= sprintf(_("Monthly: %s"), get_money($row["fixrate"]));
                $text .= " ";
            }
            if ($row["connection_charge"] > 0) {
                $text .= sprintf(_("B-Leg connect: %s"), get_money($row["connection_charge"]));
                $text .= " ";
            }
            if ($row["selling_rate"] > 0) {
                $text .= sprintf(_("B-Leg rate: %s"), get_money($row["selling_rate"]));
                $text .= " ";
            }
            if ($row["aleg_retail_connect_charge"] > 0) {
                $text .= sprintf(_("A-Leg connect: %s"), get_money($row["aleg_retail_connect_charge"]));
                $text .= " ";
            }
            if ($row["aleg_retail_cost_min"] > 0) {
                $text .= sprintf(_("A-Leg rate: %s"), get_money($row["aleg_retail_cost_min"]));
                $text .= " ";
            }
            $text = str_replace(" ()", "", trim($text) . ")");

            return [$row["id"], $text];
        }
    );
}

$HD_Form->AddEditRadio(
    _("Activated"),
    "activated",
    $activated_list,
    "1",
    "",
    [],
    _("Choose if you want to activate this destination")
);

if (!is_customer()) {
    $HD_Form->AddEditRadio(_("Validated"), "validated", $validated_list, "1");
}
$HD_Form->AddEditSelect(_("Priority"), "priority", $priority_list);

$HD_Form->AddEditRadio(
    _("VoIP Call"),
    "voip_call",
    $yesno_list,
    "1",
    "",
    [],
    _("Choose if you want to not use the trunk and let the asterisk call directly the destination (ie, Destination : SIP/jeremy@182.212.1.45)")
);

// Set the filter variables
$HD_Form->AddListFilter("cc_did.did", _("DID"));

$HD_Form->FG_INTRO_TEXT_ADITION = "";

$HD_Form->FG_ADDITIONAL_FUNCTION_AFTER_ADD = [FormBO::class, "did_destination_add"];
$HD_Form->FG_ADDITIONAL_FUNCTION_AFTER_DELETE = [FormBO::class, "did_destination_del"];
