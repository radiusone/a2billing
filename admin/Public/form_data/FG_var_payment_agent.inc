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

getpost_ifset(array('id', 'agent_id', 'date', 'payment','description','added_refill','refill_type'));


if (!isset($form_action) || $form_action=="list") {
	$HD_Form = new FormHandler("cc_logpayment_agent LEFT JOIN cc_agent ON cc_agent.id=agent_id", "Payment Agent", 'cc_logpayment_agent.id');
} else {
	$HD_Form = new FormHandler("cc_logpayment_agent", "Agent Payment");
}

$HD_Form->no_debug();
$HD_Form -> FG_TABLE_DEFAULT_SENS = "DESC";

$HD_Form ->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form ->FG_LIST_ADDING_BUTTON_LINK1 = "A2B_entity_payment_agent.php?form_action=ask-add";
$HD_Form ->FG_LIST_ADDING_BUTTON_ALT1 = $HD_Form ->FG_LIST_ADDING_BUTTON_MSG1 = gettext("Add Payment");
$HD_Form ->FG_LIST_ADDING_BUTTON_IMG1 = Images_Path ."/money_add.png" ;

$list_refill_type=getRefillType_List();
$HD_Form -> AddViewElement(gettext("ID"), "cc_logpayment.id");
if (has_rights (Admin::ACX_ADMINISTRATOR)) {
	$HD_Form -> AddViewElement(gettext("AGENT"), "agent_id", true, 30, "display_linktoagent");
} else {
	$HD_Form -> AddViewElement(gettext("AGENT"), "agent_id", true, 30, "display_nameofagent");
}
$HD_Form -> AddViewElement(gettext("PAYMENT DATE"), "date", true, 30);
$HD_Form -> AddViewElement(gettext("PAYMENT AMOUNT"), "payment", true, 0, "display_money");
$HD_Form -> AddViewElement(gettext("PAYMENT TYPE"), "payment_type", true, 0, "", "list", $list_refill_type);
$HD_Form -> AddViewElement(gettext("REFILL"), "id_logrefill", true, 0, "", "lie_link", "cc_logrefill_agent", "TRUNCATE(credit,2), id", "id='%id'", "%1 " . strtoupper(BASE_CURRENCY) . " (id:%2)", "A2B_refill_info_agent.php");

$HD_Form -> FieldViewElement ('cc_logpayment_agent.id, agent_id, date, payment ,payment_type,id_logrefill');

$HD_Form -> FG_ENABLE_INFO_BUTTON = true;
$HD_Form -> FG_INFO_BUTTON_LINK = "A2B_payment_info_agent.php?id=";
$HD_Form -> FG_ENABLE_ADD_BUTTON = true;

if (!($popup_select>=1))	 $HD_Form -> search_form_enabled = true;
$HD_Form -> search_session_key = 'payment_agent_selection';
$HD_Form -> search_form_title = gettext('Define specific criteria to search for payments created.');
$HD_Form -> search_date_enabled = true;
$HD_Form -> search_date_text = gettext('DATE');
$HD_Form -> search_date_column = 'date';
$HD_Form -> search_delete_enabled =false;
$HD_Form -> AddSearchTextInput(gettext("LOGIN"), 'login','logintype');
$HD_Form -> AddSearchTextInput(gettext("LASTNAME"),'lastname','lastnametype');
$HD_Form -> AddSearchTextInput(gettext("FIRSTNAME"),'firstname','firstnametype');

if (has_rights (Admin::ACX_MODIFY_PAYMENTS)) {
	$HD_Form -> FG_ENABLE_EDIT_BUTTON = true;
	$HD_Form -> FG_ENABLE_DELETE_BUTTON = true;

	if ($form_action=="ask-add" && !empty($agent_id)) {
		$id_val = "value='$agent_id'";
	}

    $HD_Form->AddEditPopup(
        gettext("AGENT ID"),
        "agent_id",
        "A2B_entity_agent.php?popup_select=1&",
        gettext("Select the Agent ID"),
        $id_val,
        4, gettext("Insert the AGENT ID ")
    );

	if ($form_action=="ask-add") {
		$begin_date = date("Y");
		$end_date = date("-m-d H:i:s");
		$comp_date=$begin_date.$end_date;
		$comp_date="value='$comp_date'";
	}

    $HD_Form->AddEditElement(
        gettext("PAYMENT DATE"),
        "date",
        "",
        "size=40 maxlength=40  $comp_date",
        10,
        gettext("Insert the current date")
    );

    $HD_Form->AddEditElement(
        gettext("PAYMENT AMOUNT"),
        "payment",
        gettext("Enter the amount in the currency base : ") . BASE_CURRENCY,
        "size=60 maxlength=10",
        12,
        gettext("Insert the payment amount")
    );

    $HD_Form->AddEditTextarea(
        gettext("DESCRIPTION"),
        "description",
        "",
        "cols='50' rows='4'", null, gettext("Insert the description")
    );

	if ($form_action!="ask-edit" && $form_action!="edit") {
        $HD_Form->AddEditRadio(
            gettext("CREATE ASSOCIATE REFILL"),
            "added_refill",
            [[gettext("Yes"), "1"], [gettext("No"), "0"]],
            "0",
            gettext("Refill card account"), "",
            gettext("Choose if you want to create a refill with this payment account")
        );
	}

    $HD_Form->AddEditSelect(
        gettext("REFILL TYPE"),
        "payment_type",
        $list_refill_type,
        "", gettext("Define type for payment and refill,if created.")
    );

	$HD_Form -> FG_INTRO_TEXT_ADITION = '';
		
	$HD_Form -> FG_ADDITIONAL_FUNCTION_AFTER_ADD = 'create_agent_refill';

	$HD_Form -> FG_LOCATION_AFTER_ADD = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";
	$HD_Form -> FG_LOCATION_AFTER_EDIT = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";
	$HD_Form -> FG_LOCATION_AFTER_DELETE = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";
}
