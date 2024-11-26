<?php

use A2billing\Admin;
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

Admin::checkPageAccess(Admin::ACX_ADMINISTRATOR);

getpost_ifset(array('id','id_agent','date', 'amount,type', 'status'));

if (!isset($form_action) || $form_action=="list") {
	$HD_Form = new FormHandler("cc_remittance_request LEFT JOIN cc_agent ON cc_agent.id=id_agent", "Remittance Request Agent", 'cc_remittance_request.id');
} else {
	$HD_Form = new FormHandler("cc_remittance_request", "Remittance Request Agent");
}

$HD_Form -> FG_TABLE_DEFAULT_SENS = "DESC";

$HD_Form->AddListActionButton(
    "",
    _("ACCEPT THIS REQUEST"),
    get_image_path("accept.png"),
    "accept_click",
    "status",
    0 // waiting
);
$HD_Form->AddListActionButton(
    "",
    _("REFUSE THIS REQUEST"),
    get_image_path("cancel.png"),
    "refuse_click",
    "status",
    0 // waiting
);

$status_list = getRemittanceStatus_List();
$status_revert_list = array_map("array_reverse", $status_list);
$type_list = getRemittanceType_List();
$type_revert_list = array_map("array_reverse", $type_list);
$HD_Form -> AddViewElement(gettext("ID"), "cc_agent_commission.id");
$HD_Form -> AddViewElement(gettext("AGENT"), "id_agent", true, 30, "display_linktoagent");
$HD_Form -> AddViewElement(gettext("DATE"), "date", true, 30);
$HD_Form -> AddViewElement(gettext("AMOUNT"), "amount", true, 0, "display_money");
$HD_Form -> AddViewElement(gettext("TYPE"), "type", true, 0, "", "list", $type_list);
$HD_Form -> AddViewElement(gettext("STATUS"), "status", true, 0, "", "list", $status_list);

$HD_Form -> FieldViewElement ('cc_remittance_request.id, id_agent, date, amount,type, status ');

$HD_Form -> search_form_enabled = true;
$HD_Form -> search_session_key = 'remittance_agent_selection';
$HD_Form -> search_form_title = gettext('Define specific criteria to search for remittances requests created.');
$HD_Form -> search_date_enabled = true;
$HD_Form -> search_date_text = gettext('DATE');
$HD_Form -> search_date_column = 'date';
$HD_Form -> search_delete_enabled =false;
$HD_Form -> AddSearchTextInput(gettext("LOGIN"), 'login','logintype');
$HD_Form -> AddSearchTextInput(gettext("LASTNAME"),'lastname','lastnametype');
$HD_Form -> AddSearchTextInput(gettext("FIRSTNAME"),'firstname','firstnametype');
$HD_Form -> AddSearchSelectInput(gettext("SELECT REQUEST TYPE"), "type", $type_revert_list);
$HD_Form -> AddSearchSelectInput(gettext("SELECT REQUEST STATUS"), "status", $status_revert_list);

$HD_Form -> CV_NO_FIELDS  = gettext("THERE ARE NO REMITTANCE REQUEST CREATED!");
