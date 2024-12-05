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

/**
 * @var numeric-string|null $popup_select
 */
Admin::checkPageAccess(Admin::ACX_ADMINISTRATOR);

$HD_Form = new FormHandler("cc_logrefill_agent", "Agent Refill");

$HD_Form->FG_QUERY_ORDERBY_COLUMNS = ["date"];
$HD_Form->FG_TABLE_DEFAULT_SENS = "DESC";

$HD_Form->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form->FG_LIST_ADDING_BUTTON_LINK1 = "A2B_entity_logrefill_agent.php?form_action=ask-add";
$HD_Form->FG_LIST_ADDING_BUTTON_ALT1 = $HD_Form->FG_LIST_ADDING_BUTTON_MSG1 = _("Add Refill");
$HD_Form->FG_LIST_ADDING_BUTTON_IMG1 = get_image_path("time_add.png") ;

$list_refill_type = getRefillType_List();

$HD_Form->AddListValue(_("ID"), "id");
$HD_Form->AddListValue(_("Agent"), "agent_id", has_rights (Admin::ACX_ADMINISTRATOR) ? "display_linktoagent" : "display_nameofagent");
$HD_Form->AddListValue(_("Date"), "date");
$HD_Form->AddListMapping(_("Type"), "refill_type", $list_refill_type);
$HD_Form->AddListValue(_("Amount"), "credit", "display_money");
$HD_Form->FieldViewElement(["id", "agent_id", "date", "refill_type", "credit"]);

$HD_Form->FG_ENABLE_INFO_BUTTON = true;
$HD_Form->FG_INFO_BUTTON_LINK = "A2B_refill_info_agent.php?id=";
$HD_Form->FG_ENABLE_ADD_BUTTON = true;

if (!$popup_select) {
    $HD_Form->search_form_enabled = true;
    $HD_Form->search_session_key = 'refill_agent_selection';
    $HD_Form->search_form_title = _('Define specific criteria to search for refills created.');
    $HD_Form->search_date_enabled = true;
    $HD_Form->search_date_text = _('DATE');
    $HD_Form->search_date_column = 'date';
    $HD_Form->search_delete_enabled = false;
    $HD_Form->AddSearchPopupInput("agent_id", _("Agent"), "A2B_entity_agent.php");
    $HD_Form->AddSearchTextInput(_("Last name"), "lastname");
    $HD_Form->AddSearchTextInput(_("First name"), "firstname");
}

if (has_rights(Admin::ACX_MODIFY_REFILLS)) {
	$HD_Form->FG_ENABLE_EDIT_BUTTON = true;
	$HD_Form->FG_ENABLE_DELETE_BUTTON = true;

    $HD_Form->AddEditPopup(
        _("AGENT ID"),
        "agent_id",
        "A2B_entity_agent.php?popup_select=1&",
        _("Select the Agent ID"),
        "", 4, _("Insert the AGENT ID "),
    );

    $comp_date = "";
	if (($form_action ?? "") === "ask-add") {
		$comp_date=date("Y-m-d H:i:s");
		$comp_date="value='$comp_date'";
	}

    $HD_Form->AddEditElement(
        _("REFILL DATE"),
        "date",
        "",
        "size=40 maxlength=40  $comp_date",
        10,
        _("Insert the current date")
    );

    $HD_Form->AddEditElement(
        _("REFILL AMOUNT"),
        "credit",
        _("Enter the amount in the currency base : ") . BASE_CURRENCY,
        "size=60 maxlength=10",
        12,
        _("Insert the credit amount")
    );

    $HD_Form->AddEditTextarea(
        _("DESCRIPTION"),
        "description",
        "",
        "cols='50' rows='4'",
        null,
        _("Insert the description")
    );

    $HD_Form->AddEditSelect(
        _("REFILL TYPE"),
        "refill_type",
        $list_refill_type,
        "",
        _("Define type for payment and refill,if created.")
    );

	$HD_Form->FG_INTRO_TEXT_ADITION = "";
	$HD_Form->FG_ADDITIONAL_FUNCTION_AFTER_ADD = "add_agent_refill";

	$HD_Form->FG_LOCATION_AFTER_ADD = "?id=";
	$HD_Form->FG_LOCATION_AFTER_EDIT = "?id=";
	$HD_Form->FG_LOCATION_AFTER_DELETE = "?id=";

}

