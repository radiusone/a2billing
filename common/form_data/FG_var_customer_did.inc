<?php

use A2billing\Customer;
use A2billing\Forms\FormBO;
use A2billing\Forms\FormHandler;
use A2billing\Table;

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

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
/**
 * @var string $form_action
 */

$HD_Form = new FormHandler("cc_did", "DID");
$HD_Form->list_query_conditions["iduser"] = $HD_Form->update_query_conditions["iduser"] = Customer::id();
$HD_Form->list_query_order_columns = ["did"];
$HD_Form->list_query_order_direction = "DESC";
$HD_Form->FG_ADDITIONAL_FUNCTION_BEFORE_DELETE = [FormBO::class, "is_did_in_use"];
$HD_Form->add_message_intro = _("Be sure to note any one-time and recurring charges before adding this DID to your account");
$HD_Form->delete_message_intro = sprintf(
    _("If you want to remove this DID from your account, press the %s button"),
    _("Delete")
);
$HD_Form->AddListTopButton("A2B_entity_did.php?form_action=ask-add", _("Add DID"), "telephone-inbound-fill");

$HD_Form->list_help_text = create_help(_("Inbound DIDs can be added and removed from your account here. DID destinations can be set separately."));

$HD_Form->AddListFilter("did", _("DID"));

$yesno = getYesNoList();

$HD_Form->AddListValue(_("DID"), "did", "format_phone_number");
$HD_Form->AddListValue(_("Start date"), "startingdate");
$HD_Form->AddListSqlMapping(_("Country"), "id_cc_country", new Table("cc_country", ["id", "countryname"]));
$HD_Form->AddListMapping(_("Active"), "activated", $yesno);
$HD_Form->AddListValue(_("Minutes used"), "secondusedreal", "get_minute");
$HD_Form->AddListValue(_("Monthly rate"), "fixrate");
$HD_Form->AddListValue(_("Per minute rate"), "selling_rate");

$HD_Form->FG_ENABLE_ADD_BUTTON = true;
$HD_Form->FG_ENABLE_EDIT_BUTTON = false;
$HD_Form->FG_ENABLE_DELETE_BUTTON = true;

if ($form_action === "ask-add" || $form_action === "add") {
    $HD_Form->AddEditSqlSelect(
        _("Add a new DID"),
        "did_id",
        new Table(
            "cc_did",
            ["id", "did", "fixrate", "connection_charge", "selling_rate", "aleg_retail_connect_charge", "aleg_retail_cost_min"]
        ),
        ["id_cc_didgroup" => $_SESSION["id_didgroup"], "reserved" => 0],
        "",
        ["" => ""],
        "",
        [],
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

            return [$text, $row["id"]];
        }
    );
} elseif ($form_action === "ask-delete" || $form_action === "delete") {
    $HD_Form->AddEditElement(_("DID"), "did");
}
