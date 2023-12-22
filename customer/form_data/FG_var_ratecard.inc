<?php

use A2billing\Customer;
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

if (! has_rights (Customer::ACX_ACCESS)) {
    Header ("HTTP/1.0 401 Unauthorized");
    Header ("Location: PP_error.php?c=accessdenied");
    die();
}

getpost_ifset(array('destination', 'dialprefix', 'rateinitial','posted','tariffplan'));


$HD_Form = new FormHandler("cc_callplan_lcr", _("Rates"), 'ratecard_id');

$HD_Form->no_debug();
$HD_Form -> FG_TABLE_DEFAULT_ORDER = "destination";
$HD_Form -> FG_TABLE_DEFAULT_SENS = "ASC";
$HD_Form -> FG_VIEW_TABLE_WITDH = "65%";
$HD_Form -> search_session_key = 'entity_ratecard_selection';




$HD_Form -> FG_QUERY_WHERE_CLAUSE = "tariffgroup_id = '".$_SESSION["tariff"]."' ";
$HD_Form->list_query_conditions["tariffgroup_id"] = $_SESSION["tariff"];
$HD_Form -> FG_QUERY_GROUPBY_COLUMNS = ["dialprefix"];

$HD_Form -> AddViewElement("<acronym title=\"DIALING PREFIX\">" . gettext("PREFIX") . "</acronym>", "dialprefix");
$HD_Form -> AddViewElement(gettext("DESTINATION"), "destination");
$HD_Form -> AddViewElement("<acronym title=\"SELLING RATE\">" . gettext("SR") . "</acronym>", "rateinitial");

$HD_Form -> FieldViewElement ('dialprefix, destination, MIN(rateinitial) as rateinitial');

$HD_Form -> CV_NO_FIELDS  = gettext("THERE IS NO RATECARD CREATED!");
