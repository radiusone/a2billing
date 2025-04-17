<?php

use A2billing\Admin;
use A2billing\Agent;
use A2billing\Forms\FormBO;
use A2billing\Forms\FormHandler;
use A2billing\Forms\Validator;
use A2billing\Table;

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

getpost_ifset(["form_action", "popup_select", "popup_formname", "popup_fieldname"]);
/**
 * @var string $form_action
 * @var string $popup_select
 * @var string $popup_formname
 * @var string $popup_fieldname
 */

$HD_Form = new FormHandler("cc_agent", _("Agent"));

$HD_Form->list_query_order_columns = ["datecreation"];

$HD_Form->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form->FG_LIST_ADDING_BUTTON_LINK1 = "A2B_entity_agent.php?form_action=ask-add&groupID=0";
$HD_Form->FG_LIST_ADDING_BUTTON_ALT1 = $HD_Form->FG_LIST_ADDING_BUTTON_MSG1 = _("Add AGENT");
$HD_Form->FG_LIST_ADDING_BUTTON_IMG1 = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAJfSURBVDjLpZNrSFNhGMelD34op67Lp4I+RKBREkUNIR2po7LOztlxbY1amstyXWw2m06b6Ra1llpqBJrlJdNFVyqxixZmIQ5npVE20DCkYqaDDBVy77+zDQbCmEUffvC8L/x/z/Pw8oYACPkfAl5mKmWl+cJFMMTzoNsUBnXsQqhk4qt/JVCrUosMVBQs2yJg5igWhUMbH4a0uKVQ7VWUzSnQswJc4II6LqT1Eg6NkI99GyPArF1M5hRoBZGkpTIPI60WdFYexO4NfKTGLoEidhl2rotEmXbXgqCCqj3LXa6P7Rjrvo7vr2thr8/B4P1ijPa3ojFjxURf3aHQoIJqxWrbuK0Jzp5bmHzbzGH11uP2ZlSnx/QEXcGaxM5/tnlrx5NMAaZ7ajD1/p6XyTc38FwjgFWY/KJRKOUFFJQnpfE7RFSNk6Ux5fiEvmPJaMnd7sVT/7J14ytDozMx+WJ9nCJylsCcIp03oNHWfpMwgOMD0PUSaKoFrlSAVJwDMRfCfe0ySPcrfGEY8iCBKq1LpEL9grYtjJGky4BHd3xwQVRagBIjcDofKMgGjh8AuVQCd4kJP9Nk5K6IPusX9J6MmnE+zANOnQAsRT7OFPjO+iwgOwNQK+FWSoAsFcYeF6IrJ3raL3hniCbjT40gSm6FqnIQLkg8XXWHQTT7QXRH4OYm8HT/IWfhajPBlruK+AX9DUf1dv3K3zOcYDSFBs4XB2SEZuCgGPQWxkxzGdOsV/hsVfPa5dI1TSLl8AArJ0M0iyGxBIOUBI4dLPrFMnI7QTHRyqasH76p5gX9jf/CH9NZtVjmGMuRAAAAAElFTkSuQmCC";

$HD_Form->help_text = create_help(_("Agents - this shows a list of all of the Agents who have access to the Agent interface"));

$HD_Form->AddListActionButton(
    "A2B_agent_home.php?id=",
    _("Edit agent homepage"),
    get_image_path("comment_edit.png")
);
$HD_Form->AddListActionButton(
    "A2B_entity_payment_agent.php?form_action=ask-add&agent_id=|col0|",
    _('Add Payment to this Agent'),
    "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAJ0SURBVDjLlZPdT9JRGMe5qFu2Lrt1a63LWv9ATRdN5xvLsnLRipzZpIVpigjyIs3XAOUHgopoWkggP5QXSRJwJQmtm/IlAWtt3XXTfubS+nZ+P1eby6ldPGdn5+zzfb7Pc57DA8DbL9rjrYxuVsXf7W5fuC2mYawpE7QRJZpDDfz/EngYVTN9qR4EPvlgXjCiKVCPWvou/0ACxDJjSbIwDefqMPxrEzC87IDUW4Pq8Vv8PQVaX7Qw5qQRgY9ePP0wDMeSFfWTUkxmPeiI61DlFOP6SAV/VwFtRMFQCwb4CdwW10IbVcK+aMHgohmPlwdBZ11oCctx1X5p/R8B9Uzzuum1ntj1Iv1tGRtb3zH2dgSa2eZtOOOCMizD5cGyzR0lGBNdx1TP5T96E4+4WttiWg6mYr3Ifk1DF1PBmxmHYlrGZkbFUDku2oSHOAFjolOuIpZ65rs5+MmKg9hWcJlZWB1UbsOhRjYz5r/MoSn4AKWWQg0nwFoyzndhijRobGWIq3XgPQU1sa2LqjCRHoc81IBK9w0OnvscRWQtBGFfEc4b8o7wNDMKOwnY3lDwZZ+h1idB/zsThpf6CezkstVN3yNwHFMrNGqCVRvlA2UQ6POkud1nTvE0EcVR1gU7JNSCnrPrWLRtw+RM7BKBXnJDP9eOYqogVNAj0Av0uTk7mtjov2+1p2yQ0hIYXnXCs+qEzF+HC9YSyIiIsK84XWTKP5tvPHdi11GupSXHW8JNW+FMAHdclSCCKDEX/iKdDgotRY17jTu31LhvHybT5RGPin5K3NWs1c0yW+lp0umc/T7b383NUdHJa44rSfJU+Qf54n/iNzi8zBtL0z1zAAAAAElFTkSuQmCC"
);

$language_list = getLanguages();

$right_list = [
    Agent::ACX_CUSTOMER => _("VIEW CUSTOMERS"),
    Agent::ACX_BILLING => _("BILLING"),
    Agent::ACX_RATECARD => _("RATECARD"),
    Agent::ACX_CALL_REPORT => _("CALL REPORT"),
    Agent::ACX_MYACCOUNT => _("MY ACCOUNT"),
    Agent::ACX_SUPPORT => _("SUPPORT"),
    Agent::ACX_CREATE_CUSTOMER => _("CREATE_CUSTOMER"),
    Agent::ACX_EDIT_CUSTOMER => _("EDIT CUSTOMER"),
    Agent::ACX_DELETE_CUSTOMER => _("DELETE CUSTOMER"),
    Agent::ACX_GENERATE_CUSTOMER => _("GENERATE CUSTOMER"),
    Agent::ACX_SIGNUP => _("SIGNUP"),
    Agent::ACX_VOIPCONF => _("VOIP CONF"),
    Agent::ACX_SEE_CUSTOMERS_CALLERID => _("SEE CUSTOMERS CALLERID"),
];

$len_right_list = count($right_list);

$yesno = getYesNoList();
$currency_list = getCurrenciesList();

if ($popup_select) {
	$HD_Form->AddListValue(_("ID"), "id")
        ->AddListValue(_("LOGIN"), "login")
        ->AddListValue(_("FIRST NAME"), "firstname")
        ->AddListValue(_("LAST NAME"), "lastname")
        ->AddListValue(_("CURRENCY"), "currency")
        ->AddListMapping(_("ACTIVE"), "active", $yesno);
} else {
	$HD_Form->AddListValue(_("ID"), "id")
        ->AddListValue(_("DATE"), "datecreation")
        ->AddListValue(_("LOGIN"), "login")
        ->AddListValue(_("PASSWORD"), "pwd_encoded")
        ->AddListValue(_("FIRST NAME"), "firstname")
        ->AddListValue(_("LAST NAME"), "lastname")
        ->AddListValue(_("CREDIT"), "credit", "get_money")
        ->AddListValue(_("COMMISSION"), "com_balance", "get_money")
        ->AddListValue(_("CURRENCY"), "currency")
        ->AddListMapping(_("ACTIVE"), "active", $yesno);
}

if ($popup_select) {
	$HD_Form->FG_LIST_VIEW_PAGE_SIZE = 7;
    $HD_Form->AddListSelectActionButton("javascript:sendValue('|param|')");
} elseif (has_rights (Admin::ACX_MODIFY_AGENTS)) {
	$HD_Form->FG_ENABLE_INFO_BUTTON = true;
	$HD_Form->FG_INFO_BUTTON_LINK = "A2B_info_agent.php?id=";
	$HD_Form->FG_ENABLE_EDIT_BUTTON = true;
	$HD_Form->FG_ENABLE_ADD_BUTTON = true;
	$HD_Form->FG_ENABLE_DELETE_BUTTON = true;

	$HD_Form->AddEditElement(
        _("LOGIN"),
        "login",
        "",
        ["maxlength" => 12],
        [Validator::class, "min4Chars"],
        _("Insert the login")
    );

    $HD_Form->AddEditElement(
        _("PASSWORD"),
        "pwd_encoded",
        "",
        ["maxlength" => 15],
        [Validator::class, "min4Chars"],
        _("Insert the new password to overwrite the old one")
    );

    $HD_Form->AddEditSelect(
        _("PERMISSION"),
        "perms",
        $right_list,
        "",
        _("To select several rights in the above ListBox, hold the CTRL key down while clicking the items"),
        ["size" => $len_right_list, "multiple" => "multiple"]
    );

    $HD_Form->AddEditRadio(
        _("ACTIVE"),
        "active",
        getYesNoList(),
        "1",
        _("Enable agent"),
        [],
        _("Choose if you want to active this agent")
    );

    $balance_attr = ["maxlength" => 30];
    $comm_attr = ["readonly" => "readonly"];
	if ($form_action =="ask-add" || $form_action=="add") {
        $balance_attr["value"] = 0;
		$comm_attr["value"] = 0;
	} else {
        $balance_attr["readonly"] = "readonly";
	}

    $HD_Form->AddEditElement(
        _("BALANCE"),
        "credit",
        _("currency : ") . strtoupper(BASE_CURRENCY),
        $balance_attr,
        [Validator::class, "number"],
        _("amount of credit")
    );

    $HD_Form->AddEditElement(
        _("COMMISSION BALANCE"),
        "com_balance",
        _("currency : ") . strtoupper(BASE_CURRENCY),
        $comm_attr,
        null,
        _("amount of commission")
    );

    $HD_Form->AddEditSelect(_("CURRENCY"), "currency", $currency_list, "", _("Currency used at the customer end."));

    $HD_Form->AddEditElement(
        _("COMMISSION PERCENTAGE"),
        "commission",
        _("Commission in percent"),
        ["maxlength" => 20],
        [Validator::class, "percentage"],
        _("Insert the commision"),
        "",
        "NO-NULL"
    );

    $HD_Form->AddEditElement(
        _("THRESHOLD REMITTANCE"),
        "threshold_remittance",
        _("currency : ") . strtoupper(BASE_CURRENCY),
        ["maxlength" => 20],
        [Validator::class, "number"],
        _("threshold remittance"),
        "",
        "NO"
    );

    $HD_Form->AddEditElement(
        _("VAT"),
        "vat",
        _("VAT"),
        ["maxlength" => 20],
        [Validator::class, "number"],
        _("Insert the VAT"),
        "",
        "NO-NULL"
    );

    $HD_Form->AddEditSelect(_("LANGUAGE"), "language", $language_list);

    $HD_Form->AddEditElement(
        _("COMPANY"),
        "company",
        "",
        ["maxlength" => 50],
        null,
        _("Insert the company")
    );

    $HD_Form->AddEditElement(
        _("LAST NAME"),
        "lastname",
        "",
        ["maxlength" => 50],
        null,
        _("Insert the lastname")
    );

    $HD_Form->AddEditElement(
        _("FIRST NAME"),
        "firstname",
        "",
        ["maxlength" => 50],
        null,
        _("Insert the firstname")
    );

    $HD_Form->AddEditElement(
        _("ADDRESS"),
        "address",
        "",
        ["maxlength" => 250],
        null,
        _("Insert the address")
    );

    $HD_Form->AddEditElement(
        _("CITY"),
        "city",
        "",
        ["maxlength" => 20],
        null,
        _("Insert the city")
    );

    $HD_Form->AddEditElement(
        _("STATE"),
        "state",
        "",
        ["maxlength" => 40],
        null,
        _("Insert the state")
    );

    $HD_Form->AddEditElement(
        _("COUNTRY"),
        "country",
        "",
        ["maxlength" => 40],
        null,
        _("Insert the country")
    );

    $HD_Form->AddEditElement(
        _("ZIPCODE"),
        "zipcode",
        "",
        ["maxlength" => 7],
        null,
        _("Insert the zip code")
    );

    $HD_Form->AddEditElement(
        _("EMAIL"),
        "email",
        "",
        ["maxlength" => 70],
        [Validator::class, "email"],
        _("Insert the email"),
        "",
        "NO"
    );

    $HD_Form->AddEditElement(
        _("PHONE"),
        "phone",
        "",
        ["maxlength" => 20],
        null,
        _("Insert the telephone number")
    );

    $HD_Form->AddEditElement(
        _("FAX"),
        "fax",
        "",
        ["maxlength" => 20],
        null,
        _("Insert the fax number")
    );

    $HD_Form->AddEditTextarea(
        _("BANK INFO"),
        "bank_info",
        "",
        ["rows" => 4],
        _("Insert the bank info of this agent")
    );

    $HD_Form->AddEditHasMany(
        _("Call plans"),
        new Table(
            "cc_tariffgroup",
            ["cc_tariffgroup.id", "cc_tariffgroup.tariffgroupname"],
            ["cc_agent_tariffgroup" => ["cc_tariffgroup.id", "cc_agent_tariffgroup.id_tariffgroup"]]
        ),
        "id_tariffgroup",
        "id_agent",
        "",
        null,
        false,
        true,
        new Table("cc_agent_tariffgroup")
    );

	$HD_Form->FG_ADDITIONAL_FUNCTION_AFTER_ADD = [FormBO::class, "creation_agent_refill"];
	$HD_Form->FG_INTRO_TEXT_ADITION = '';
}
