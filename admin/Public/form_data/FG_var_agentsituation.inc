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

$HD_Form = new FormHandler("cc_agent", _("Agent"));

$HD_Form->AddListValue(_("Agent"), "id","display_linktoagent");
$HD_Form->AddListValue(_("Credit"), "credit", "display_2bill");
$HD_Form->AddListValue("<abbr title=\"" . _("Commission balance") . "\">" . _("Bal") . "</abbr>", "com_balance", "display_2bill");
// todo: need a way to do a calculated column in tables
$HD_Form->AddViewElement("<abbr title=\"" . _("Total commission accrued") . "\">" . _("Total") . "</abbr>", "com_accrued", true, 15, "", "lie", "cc_agent_commission as t2", "TRUNCATE(SUM(t2.amount),2)", "t2.id_agent='%id'  ", "%1");
$HD_Form->AddViewElement("<abbr title=\"" . _("Total customer refill amount") . "\">" . _("Refills") . "</abbr>", "refill", true, 15, "", "lie", "cc_logrefill as t2", "TRUNCATE(SUM(t2.credit),2)", "t2.agent_id='%id' AND t2.refill_type=3 ", "%1");
$HD_Form->AddViewElement("<abbr title=\"" . _("Total agent refill amount") . "\">" . _("Agent refills") . "</abbr>", "refill", true, 15, "", "lie", "cc_logrefill_agent as t2", "TRUNCATE(SUM(t2.credit),2)", "t2.agent_id='%id' ", "%1");
$HD_Form->AddViewElement("<abbr title=\"" . _("Total payments taken") . "\">" . _("Payments") . "</abbr>", "payment", true, 15, "", "lie", "cc_logpayment_agent as t2", "TRUNCATE(SUM(t2.payment),2)", "t2.agent_id='%id' ", "%1");
$HD_Form->FieldViewElement (["id", "credit", "com_balance", "id", "id", "id", "id"]);

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
