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

$HD_Form = new FormHandler("cc_call INNER JOIN cc_did ON cc_did.id = cc_call.id_did INNER JOIN cc_card ON cc_card.id = cc_call.card_id", "did billing", "cc_did.id");

$HD_Form->no_debug();
$HD_Form -> FG_TABLE_DEFAULT_ORDER = "date";
$HD_Form -> FG_TABLE_DEFAULT_SENS = "DESC";
$HD_Form -> FG_QUERY_WHERE_CLAUSE = "( sipiax = 2 OR sipiax = 3) ";
$HD_Form->list_query_conditions["sipiax"] = ["IN", [2, 3]];

$HD_Form -> AddViewElement(gettext("DATE"), "date(starttime) as date", false);
$HD_Form -> AddViewElement(gettext("DID"), "cc_did.did", false);
$HD_Form -> AddViewElement(gettext("NB_CALL"), "count(*)", false);
$HD_Form -> AddViewElement(gettext("TOTAL_DURATION"), "sum(sessiontime)", false);
$HD_Form -> AddViewElement(gettext("TOTAL_COST"), "sum(sessionbill)", false);
$HD_Form -> AddViewElement(gettext("CARDNUMBER"), "username", false);

// added a parameter to append  FG_TABLE_ID  ( by default ) or disable 0.
$HD_Form -> FieldViewElement ("DATE(starttime) as date, cc_did.did, count(*) as nb_call, sum(sessiontime) as t_time, sum(sessionbill) as t_cost, username ", 0 );

$HD_Form -> FG_QUERY_GROUPBY_COLUMNS = ["cc_card.username","cc_did.did","date"];

$HD_Form -> FG_ENABLE_EDIT_BUTTON = false;
$HD_Form -> FG_ENABLE_DELETE_BUTTON = false;
$HD_Form -> FG_ENABLE_ADD_BUTTON = false;

$HD_Form -> FG_FILTER_ENABLE = true;
$HD_Form -> FG_FILTER_COLUMN = 'username';
$HD_Form -> FG_FILTER_LABEL = 'cardnumber';

$HD_Form -> FG_FILTER2_ENABLE = true;
$HD_Form -> FG_FILTER2_COLUMN = 'cc_did.did';
$HD_Form -> FG_FILTER2_LABEL = 'did';

$HD_Form -> FG_LOCATION_AFTER_ADD = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";
$HD_Form -> FG_LOCATION_AFTER_EDIT = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";
$HD_Form -> FG_LOCATION_AFTER_DELETE = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";

