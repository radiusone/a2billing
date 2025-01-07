<?php

use A2billing\Agent;
use A2billing\Customer;
use A2billing\Forms\FormHandler;
use A2billing\Forms\Validator;

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

if (! has_rights (Agent::ACX_ACCESS)) {
    Header ("HTTP/1.0 401 Unauthorized");
    Header ("Location: PP_error.php?c=accessdenied");
    die();
}


getpost_ifset(array('id', 'status', 'Period', 'frommonth', 'fromstatsmonth', 'tomonth', 'tostatsmonth', 'fromday', 'fromstatsday_sday', 'fromstatsmonth_sday', 'today', 'tostatsday_sday', 'tostatsmonth_sday', 'current_page', 'order', 'sens','popup_select'));

$HD_Form = new FormHandler("cc_epayment_log,cc_card LEFT JOIN cc_card_group ON cc_card.id_group=cc_card_group.id", "Payment", "cc_epayment_log.id");

//CLause to filter by agent
$HD_Form->list_query_conditions["cc_card.id"] = "`cc_epayment_log`.`cardid`";
$HD_Form->list_query_conditions["cc_card_group.id_agent"] = $_SESSION["agent_id"];

$HD_Form ->list_query_order_columns = ["cc_epayment_log.creationdate"];
$HD_Form -> list_query_order_direction = "DESC";
$HD_Form->update_query_conditions = ["cc_epayment_log.id" => "%id"];

$date_clause = "";

$lastdayofmonth = date("t", strtotime(($tostatsmonth ?? "2099-12").'-01'));
normalize_day_of_month($fromstatsday_sday, $fromstatsmonth_sday);
normalize_day_of_month($tostatsday_sday, $tostatsmonth_sday);
if ($Period=="Month"){
    $HD_Form->list_query_conditions[] = [
        "SUB",
        [
            "cc_epayment_log.creationdate" => [
                [">=", sprintf("%s-01", $fromstatsmonth ?? "1900-01")],
                ["<=", sprintf("%s-%s 23:59:59", $tostatsmonth ?? "2099-12", $lastdayofmonth)]
            ],
        ],
    ];
} else {
    $HD_Form->list_query_conditions[] = [
        "SUB",
        [
            "cc_epayment_log.creationdate" => [
                [">=", sprintf("%s-%02d", $fromstatsmonth_sday ?? "1900-01", $fromstatsday_sday ?? 1)],
                ["<=", sprintf("%s-%02d 23:59:59", $tostatsmonth_sday ?? "2099-12", $tostatsday_sday ?? 31)]
            ],
        ],
    ];
}

if($enteradmin != "") {
    $HD_Form->list_query_conditions["iduser"] = $enteradmin;
}

if($status >= 0 && $status != '') {
    $HD_Form->list_query_conditions["cc_epayment_log.status"] = $status;
}

$status_list = getPaymentStateList();


$HD_Form -> AddListValue(gettext("Id"), "ID");
$HD_Form -> AddListValue(gettext("Created At"), "creationdate");
$HD_Form -> AddListValue(gettext("Payment Method"), "paymentmethod");
$HD_Form -> AddListMapping(gettext("Status"), "status", $status_list);
$HD_Form -> AddListValue(gettext("Owner"), "cc_owner");
$HD_Form -> AddListValue(gettext("Amount"), "amount");
$HD_Form -> AddListValue(gettext("CardID"), "cardid");
$HD_Form -> AddListValue(gettext("Cardnumber"), "cardid", [Customer::class, "getUsername"]);

// added a parameter to append  FG_TABLE_ID  ( by default ) or disable 0.
$HD_Form -> FieldViewElement ('cc_epayment_log.id, cc_epayment_log.creationdate, paymentmethod, cc_epayment_log.status, cc_owner, amount, cardid, cardid ');

$HD_Form -> FG_LIST_VIEW_PAGE_SIZE = 25;

$HD_Form -> FG_ENABLE_EDIT_BUTTON = true;
$HD_Form -> FG_ENABLE_DELETE_BUTTON = true;
$HD_Form -> FG_ENABLE_ADD_BUTTON = false;

$HD_Form->AddEditElement(
    gettext("ID"),
    "id",
    gettext("Select the ID"),
    ["readonly" => "readonly"],
    [Validator::class, "numeric"],
    gettext("Insert the Id ")
);


$HD_Form->AddEditElement(
    gettext("DATE"),
    "creationdate",
    "",
    ["readonly" => "readonly"],
    [Validator::class, "dateTime"],
    gettext("Insert the creation date")
);


$HD_Form->AddEditElement(
    gettext("PAYMENT METHOD"),
    "paymentmethod",
    gettext("Enter the payment method "),
    ["maxlength" => 30],
    [Validator::class, "min1Char"],
    gettext("Insert the payment method")
);

$HD_Form->AddEditSelect(gettext("STATUS"), "status", $status_list);

$HD_Form->AddEditElement(
    gettext("OWNER"),
    "cc_owner",
    gettext("Enter the owner"),
    ["maxlength" => 30],
    [Validator::class, "number"],
    gettext("Insert the owner")
);


$HD_Form->AddEditElement(
    gettext("AMOUNT"),
    "amount",
    gettext("Enter the amount "),
    ["maxlength" => 30],
    [Validator::class, "number"],
    gettext("Insert the amount")
);

$HD_Form->AddEditPopup(
    gettext("CUSTOMER ID"),
    "card_id",
    "A2B_entity_card.php?popup_select=1&",
    gettext("Select the Customer ID"),
    [],
    [Validator::class, "numeric"],
    gettext("Insert the Customer ID")
);

$HD_Form -> FG_FILTER_ENABLE = true;
$HD_Form -> FG_FILTER_COLUMN = 'cardid';
$HD_Form -> FG_FILTER_LABEL = _("Card ID");

$HD_Form -> FG_INTRO_TEXT_EDITION= '';

$HD_Form -> FG_LOCATION_AFTER_EDIT = "?id=";
