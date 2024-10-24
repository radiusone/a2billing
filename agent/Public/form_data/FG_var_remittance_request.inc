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
$HD_Form -> FG_QUERY_WHERE_CLAUSE = "id_agent=".$_SESSION['agent_id'] ;
$HD_Form->list_query_conditions["id_agent"] = $_SESSION["agent_id"];

$HD_Form->no_debug();
$HD_Form -> FG_TABLE_DEFAULT_SENS = "DESC";

$HD_Form -> FG_OTHER_BUTTON2_LINK = "javascript:;";
$HD_Form -> FG_OTHER_BUTTON2_IMG = Images_Path."/cancel.png";
$HD_Form -> FG_OTHER_BUTTON2 = true;
$HD_Form -> FG_OTHER_BUTTON2_HTML_CLASS = "cancel_click";
$HD_Form -> FG_OTHER_BUTTON2_HTML_ID = "|col0|";
$HD_Form -> FG_OTHER_BUTTON2_CONDITION = '|col4| == "'.gettext('WAITING').'"';
$HD_Form -> FG_OTHER_BUTTON2_ALT = gettext("CANCEL THIS REQUEST");
$HD_Form -> FG_VIEW_TABLE_WITDH = "70%";

$status_list = getRemittanceStatus_List();
$type_list = getRemittanceType_List();
$HD_Form -> AddViewElement(gettext("ID"), "cc_agent_commission.id");
$HD_Form -> AddViewElement(gettext("DATE"), "date", true, 30);
$HD_Form -> AddViewElement(gettext("AMOUNT"), "amount", true, 0, "display_money");
$HD_Form -> AddViewElement(gettext("TYPE"), "type", true, 0, "", "list", $type_list);
$HD_Form -> AddViewElement(gettext("STATUS"), "status", true, 0, "", "list", $status_list);

$HD_Form -> FieldViewElement ('cc_remittance_request.id, date, amount,type, status ');

$HD_Form -> CV_NO_FIELDS  = gettext("THERE ARE NO REMITTANCE REQUEST CREATED!");
