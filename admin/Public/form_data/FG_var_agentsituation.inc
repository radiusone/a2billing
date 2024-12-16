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

$HD_Form = new FormHandler(
    "cc_agent",
    _("Agent"),
    "cc_agent.id",
    [
        "cc_agent_commission" => ["cc_agent.id", "cc_agent_commission.id_agent"],
        "cc_logrefill" => ["cc_agent.id", "cc_logrefill.agent_id"],
        "cc_logrefill_agent" => ["cc_agent.id", "cc_logrefill_agent.agent_id"],
        "cc_logpayment_agent" => ["cc_agent.id", "cc_logpayment_agent.agent_id"],
    ]
);

$HD_Form->AddListValue(_("Agent"), "id","display_linktoagent");
$HD_Form->AddListValue(_("Credit"), "credit", "display_money");
$HD_Form->AddListValue(abbr(_("Bal"), _("Commission balance")), "com_balance", "display_money");
$HD_Form->AddListValue(abbr(_("Total"), _("Total commission accrued")), "total", "display_money");
$HD_Form->AddListValue(abbr(_("Refills"), _("Total customer refill amount")), "refills", "display_money");
$HD_Form->AddListValue(abbr(_("Agent refills"), _("Total agent refill amount")), "agent_refills", "display_money");
$HD_Form->AddListValue(abbr(_("Payments"), _("Total payments taken")), "payments", "display_money");
$HD_Form->FieldViewElement([
    "cc_agent.id",
    "cc_agent.credit",
    "cc_agent.com_balance",
    "SUM(cc_agent_commission.amount) AS total",
    "SUM(cc_logrefill.credit) AS refills",
    "SUM(cc_logrefill_agent.credit) AS agent_refills",
    "SUM(cc_logpayment_agent.payment) AS payments",
]);

$HD_Form->search_form_enabled = true;
$HD_Form->search_session_key = "agent_situation";
$HD_Form->search_form_title = _("Define specific criteria to search for agents.");
$HD_Form->search_delete_enabled = false;
$HD_Form->AddSearchTextInput(_("LOGIN"), "login","logintype");
$HD_Form->AddSearchTextInput(_("LASTNAME"),"lastname","lastnametype");
$HD_Form->AddSearchTextInput(_("FIRSTNAME"),"firstname","firstnametype");

$HD_Form->FG_ENABLE_EDIT_BUTTON = false;
$HD_Form->FG_ENABLE_DELETE_BUTTON = false;
$HD_Form->FG_ENABLE_ADD_BUTTON = false;

$HD_Form->FG_LOCATION_AFTER_ADD = "?id=";
$HD_Form->FG_LOCATION_AFTER_EDIT = "?id=";
$HD_Form->FG_LOCATION_AFTER_DELETE = "?id=";
