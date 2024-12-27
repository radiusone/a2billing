<?php

use A2billing\Forms\FormHandler;

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

$HD_Form = new FormHandler(
    "cc_call",
    _("DID billing"),
    "cc_did.id",
    [
        "cc_did" => ["INNER", "cc_did.id", "cc_call.id_did"],
        "cc_card" => ["INNER", "cc_card.id", "cc_call.card_id"]
    ]
);

$HD_Form->FG_TABLE_DEFAULT_ORDER = "date";
$HD_Form->FG_TABLE_DEFAULT_SENS = "DESC";
$HD_Form->list_query_conditions["sipiax"] = ["IN", [2, 3]];

$HD_Form->help_text = create_help(
    _("DID list and billing list.")
    . _("You will see which customers have used your DIDs in past months and the traffic (amount of seconds).")
);

$HD_Form->AddListValue(_("DATE"), "DATE(starttime) AS date");
$HD_Form->AddListValue(_("DID"), "cc_did.did");
$HD_Form->AddListValue(_("NB_CALL"), "COUNT(*) AS nb_call");
$HD_Form->AddListValue(_("TOTAL_DURATION"), "SUM(sessiontime) AS t_time", "get_minute");
$HD_Form->AddListValue(_("TOTAL_COST"), "SUM(sessionbill) AS t_cost", "get_money");
$HD_Form->AddListValue(_("CARDNUMBER"), "username");

$HD_Form->FieldViewElement([
    "DATE(starttime) AS date",
    "cc_did.did",
    "COUNT(*) AS nb_call",
    "SUM(sessiontime) AS t_time",
    "sum(sessionbill) AS t_cost",
    "username",
]);

$HD_Form->FG_QUERY_GROUPBY_COLUMNS = ["cc_card.username", "cc_did.did", "date"];

$HD_Form->FG_ENABLE_EDIT_BUTTON = false;
$HD_Form->FG_ENABLE_DELETE_BUTTON = false;
$HD_Form->FG_ENABLE_ADD_BUTTON = false;

$HD_Form->FG_FILTER_ENABLE = true;
$HD_Form->FG_FILTER_COLUMN = "username";
$HD_Form->FG_FILTER_LABEL = "cardnumber";

$HD_Form->FG_FILTER2_ENABLE = true;
$HD_Form->FG_FILTER2_COLUMN = "cc_did.did";
$HD_Form->FG_FILTER2_LABEL = "did";

$HD_Form->FG_LOCATION_AFTER_ADD = "?id=";
$HD_Form->FG_LOCATION_AFTER_EDIT = "?id=";
$HD_Form->FG_LOCATION_AFTER_DELETE = "?id=";

