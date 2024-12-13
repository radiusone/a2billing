<?php

use A2billing\Agent;
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

if (! has_rights (Agent::ACX_ACCESS)) {
    Header ("HTTP/1.0 401 Unauthorized");
    Header ("Location: PP_error.php?c=accessdenied");
    die();
}

getpost_ifset(array('id','id_agent','date', 'amount,type', 'status'));

$HD_Form = new FormHandler("cc_remittance_request", _("Remittance Request Agent"));
$HD_Form->list_query_conditions["id_agent"] = $_SESSION["agent_id"];

$HD_Form -> FG_TABLE_DEFAULT_SENS = "DESC";

$HD_Form->AddListActionButton(
    "",
    _("CANCEL THIS REQUEST"),
    get_image_path("cancel.png"),
    "cancel_click",
    "status",
    0 // waiting
);

$status_list = getRemittanceStatus_List();
$type_list = getRemittanceType_List();
$HD_Form -> AddListValue(gettext("ID"), "cc_agent_commission.id");
$HD_Form -> AddListValue(gettext("DATE"), "date");
$HD_Form -> AddListValue(gettext("AMOUNT"), "amount", "display_money");
$HD_Form -> AddListMapping(gettext("TYPE"), "type", $type_list);
$HD_Form -> AddListMapping(gettext("STATUS"), "status", $status_list);

$HD_Form -> FieldViewElement ('cc_remittance_request.id, date, amount,type, status ');

$HD_Form -> CV_NO_FIELDS  = gettext("THERE ARE NO REMITTANCE REQUEST CREATED!");
