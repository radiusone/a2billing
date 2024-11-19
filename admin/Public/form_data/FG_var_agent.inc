<?php

use A2billing\Admin;
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

Admin::checkPageAccess(Admin::ACX_ADMINISTRATOR);

getpost_ifset(["form_action", "popup_select", "popup_formname", "popup_fieldname"]);
/**
 * @var string $form_action
 * @var string $popup_select
 * @var string $popup_formname
 * @var string $popup_fieldname
 */

$HD_Form = new FormHandler("cc_agent", _("Agent"));

$HD_Form->FG_TABLE_DEFAULT_ORDER = "datecreation";
$HD_Form->FG_TABLE_DEFAULT_SENS = "ASC";

$HD_Form->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form->FG_LIST_ADDING_BUTTON_LINK1 = "A2B_entity_agent.php?form_action=ask-add&groupID=0";
$HD_Form->FG_LIST_ADDING_BUTTON_ALT1 = $HD_Form->FG_LIST_ADDING_BUTTON_MSG1 = _("Add AGENT");
$HD_Form->FG_LIST_ADDING_BUTTON_IMG1 = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAJfSURBVDjLpZNrSFNhGMelD34op67Lp4I+RKBREkUNIR2po7LOztlxbY1amstyXWw2m06b6Ra1llpqBJrlJdNFVyqxixZmIQ5npVE20DCkYqaDDBVy77+zDQbCmEUffvC8L/x/z/Pw8oYACPkfAl5mKmWl+cJFMMTzoNsUBnXsQqhk4qt/JVCrUosMVBQs2yJg5igWhUMbH4a0uKVQ7VWUzSnQswJc4II6LqT1Eg6NkI99GyPArF1M5hRoBZGkpTIPI60WdFYexO4NfKTGLoEidhl2rotEmXbXgqCCqj3LXa6P7Rjrvo7vr2thr8/B4P1ijPa3ojFjxURf3aHQoIJqxWrbuK0Jzp5bmHzbzGH11uP2ZlSnx/QEXcGaxM5/tnlrx5NMAaZ7ajD1/p6XyTc38FwjgFWY/KJRKOUFFJQnpfE7RFSNk6Ux5fiEvmPJaMnd7sVT/7J14ytDozMx+WJ9nCJylsCcIp03oNHWfpMwgOMD0PUSaKoFrlSAVJwDMRfCfe0ySPcrfGEY8iCBKq1LpEL9grYtjJGky4BHd3xwQVRagBIjcDofKMgGjh8AuVQCd4kJP9Nk5K6IPusX9J6MmnE+zANOnQAsRT7OFPjO+iwgOwNQK+FWSoAsFcYeF6IrJ3raL3hniCbjT40gSm6FqnIQLkg8XXWHQTT7QXRH4OYm8HT/IWfhajPBlruK+AX9DUf1dv3K3zOcYDSFBs4XB2SEZuCgGPQWxkxzGdOsV/hsVfPa5dI1TSLl8AArJ0M0iyGxBIOUBI4dLPrFMnI7QTHRyqasH76p5gX9jf/CH9NZtVjmGMuRAAAAAElFTkSuQmCC";
$HD_Form->FG_OTHER_BUTTON1_LINK = "A2B_agent_home.php?id=";
$HD_Form->FG_OTHER_BUTTON1_IMG = get_image_path("comment_edit.png");
$HD_Form->FG_OTHER_BUTTON1 = true;
$HD_Form->FG_OTHER_BUTTON1_ALT = _("Edit agent homepage");

$HD_Form->FG_OTHER_BUTTON2 = true;
$HD_Form->FG_OTHER_BUTTON2_LINK="A2B_entity_payment_agent.php?form_action=ask-add&agent_id=|col0|";
$HD_Form->FG_OTHER_BUTTON2_IMG = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAJ0SURBVDjLlZPdT9JRGMe5qFu2Lrt1a63LWv9ATRdN5xvLsnLRipzZpIVpigjyIs3XAOUHgopoWkggP5QXSRJwJQmtm/IlAWtt3XXTfubS+nZ+P1eby6ldPGdn5+zzfb7Pc57DA8DbL9rjrYxuVsXf7W5fuC2mYawpE7QRJZpDDfz/EngYVTN9qR4EPvlgXjCiKVCPWvou/0ACxDJjSbIwDefqMPxrEzC87IDUW4Pq8Vv8PQVaX7Qw5qQRgY9ePP0wDMeSFfWTUkxmPeiI61DlFOP6SAV/VwFtRMFQCwb4CdwW10IbVcK+aMHgohmPlwdBZ11oCctx1X5p/R8B9Uzzuum1ntj1Iv1tGRtb3zH2dgSa2eZtOOOCMizD5cGyzR0lGBNdx1TP5T96E4+4WttiWg6mYr3Ifk1DF1PBmxmHYlrGZkbFUDku2oSHOAFjolOuIpZ65rs5+MmKg9hWcJlZWB1UbsOhRjYz5r/MoSn4AKWWQg0nwFoyzndhijRobGWIq3XgPQU1sa2LqjCRHoc81IBK9w0OnvscRWQtBGFfEc4b8o7wNDMKOwnY3lDwZZ+h1idB/zsThpf6CezkstVN3yNwHFMrNGqCVRvlA2UQ6POkud1nTvE0EcVR1gU7JNSCnrPrWLRtw+RM7BKBXnJDP9eOYqogVNAj0Av0uTk7mtjov2+1p2yQ0hIYXnXCs+qEzF+HC9YSyIiIsK84XWTKP5tvPHdi11GupSXHW8JNW+FMAHdclSCCKDEX/iKdDgotRY17jTu31LhvHybT5RGPin5K3NWs1c0yW+lp0umc/T7b383NUdHJa44rSfJU+Qf54n/iNzi8zBtL0z1zAAAAAElFTkSuQmCC";
$HD_Form->FG_OTHER_BUTTON2_ALT = _('Add Payment to this Agent');

$language_list = getLanguagesList();

$right_list = [
    [_("VIEW CUSTOMERS"), Agent::ACX_CUSTOMER],
    [_("BILLING"), Agent::ACX_BILLING],
    [_("RATECARD"), Agent::ACX_RATECARD],
    [_("CALL REPORT"), Agent::ACX_CALL_REPORT],
    [_("MY ACCOUNT"), Agent::ACX_MYACCOUNT],
    [_("SUPPORT"), Agent::ACX_SUPPORT],
    [_("CREATE_CUSTOMER"), Agent::ACX_CREATE_CUSTOMER],
    [_("EDIT CUSTOMER"), Agent::ACX_EDIT_CUSTOMER],
    [_("DELETE CUSTOMER"), Agent::ACX_DELETE_CUSTOMER],
    [_("GENERATE CUSTOMER"), Agent::ACX_GENERATE_CUSTOMER],
    [_("SIGNUP"), Agent::ACX_SIGNUP],
    [_("VOIP CONF"), Agent::ACX_VOIPCONF],
    [_("SEE CUSTOMERS CALLERID"), Agent::ACX_SEE_CUSTOMERS_CALLERID],
];

$len_right_list = count($right_list);

$yesno = getYesNoList();


$currency_list = $currency_list_key = getCurrenciesList();
array_walk($currency_list_key, fn (&$v, $k)  => $v = [$k]);

if ($popup_select) {
	$HD_Form->AddViewElement(_("ID"), "id");
	$HD_Form->AddViewElement(_("LOGIN"), "login");
	$HD_Form->AddViewElement(_("FIRSTNAME"), "firstname");
	$HD_Form->AddViewElement(_("LASTNAME"), "lastname");
	$HD_Form->AddViewElement("<abbr title=\"" . _("CURRENCY") . "\">" . _("CUR") . "</abbr>", "currency", true, 0, "", "list", $currency_list_key);
	$HD_Form->AddViewElement(_("ACTIVE"), "active", true, 0, "", "list", $yesno);

	$HD_Form->FieldViewElement ('id, login, passwd, lastname, currency, active');
} else {
	$HD_Form->AddViewElement(_("ID"), "id", "sort");
	$HD_Form->AddViewElement(_("DATE"), "datecreation", true, 19);
	$HD_Form->AddViewElement(_("LOGIN"), "login");
	$HD_Form->AddViewElement(_("PASSWORD"), "passwd");
	$HD_Form->AddViewElement(_("FIRSTNAME"), "firstname");
	$HD_Form->AddViewElement(_("LASTNAME"), "lastname");
	$HD_Form->AddViewElement(_("CREDIT"), "credit");
	$HD_Form->AddViewElement(_("COMMISSION"), "com_balance");
	$HD_Form->AddViewElement("<abbr title=\"" . _("CURRENCY") . "\">" . _("CUR") . "</abbr>", "currency", true, 0, "", "list", $currency_list_key);
	$HD_Form->AddViewElement(_("ACTIVE"), "active", true, 0, "", "list", $yesno);

	$HD_Form->FieldViewElement ('id, datecreation, login, passwd, firstname, lastname, credit, com_balance, currency, active');
}

if ($popup_select) {
	$HD_Form->FG_LIST_VIEW_PAGE_SIZE = 7;
	$HD_Form->CV_FOLLOWPARAMETERS = "&amp;popup_select=$popup_select&amp;popup_formname=$popup_formname&amp;popup_fieldname=$popup_fieldname";

	$HD_Form->FG_OTHER_BUTTON1 = true;
	$HD_Form->FG_OTHER_BUTTON1_ALT = '<span style="color:red">&lt;' . _("SELECT") . '&gt;</span>';
	$HD_Form->FG_OTHER_BUTTON2 = false;
	//echo $popup_select;
	if ($popup_select === "1") {
        $HD_Form->FG_OTHER_BUTTON1_LINK = "javascript:sendValue('|param|');";
    } elseif ($popup_select === "2") {
        $HD_Form->FG_OTHER_BUTTON1_LINK = "javascript:sendValue('|col0|');";
    }
} elseif (has_rights (Admin::ACX_MODIFY_AGENTS)) {
	$HD_Form->FG_ENABLE_INFO_BUTTON = true;
	$HD_Form->FG_INFO_BUTTON_LINK = "A2B_agent_info.php?id=";
	$HD_Form->FG_ENABLE_EDIT_BUTTON = true;
	$HD_Form->FG_ENABLE_ADD_BUTTON = true;
	$HD_Form->FG_ENABLE_DELETE_BUTTON = true;

	$HD_Form->AddEditElement(
        _("LOGIN"),
        "login",
        "",
        "size=30 maxlength=12",
        3,
        _("Insert the login")
    );

    $HD_Form->AddEditElement(
        _("PASSWORD"),
        "passwd",
        "",
        "size=30 maxlength=15",
        3, _("Insert the password")
    );

    $HD_Form->AddEditSelect(
        _("PERMISSION"),
        "perms",
        $right_list,
        "",
        _("To select several rights in the above ListBox, hold the CTRL key down while clicking the items"),
        "size='$len_right_list' multiple='multiple'"
    );

    $HD_Form->AddEditRadio(
        _("ACTIVE"),
        "active",
        [[_("Yes"), "1"], [_("No"), "0"]],
        "1",
        _("Enable agent"), "",
        _("Choose if you want to active this agent")
    );

	if ($form_action =="ask-add" || $form_action=="add") {
		$balance_access = "value = '0'";
		$default_com_bal = "value='0'";
	} else {
		$balance_access = 'readonly';
        $default_com_bal = "";
	}

    $HD_Form->AddEditElement(
        _("BALANCE"),
        "credit",
        _("currency : ") . strtoupper(BASE_CURRENCY),
        "size=30 maxlength=30 $balance_access",
        12,
        _("amount of credit")
    );

    $HD_Form->AddEditElement(
        _("COMMISSION BALANCE"),
        "com_balance",
        _("currency : ") . strtoupper(BASE_CURRENCY),
        "readonly $default_com_bal",
        null,
        _("amount of commission")
    );

    $HD_Form->AddEditSelect(_("CURRENCY"), "currency", $currency_list, "", _("Currency used at the customer end."));

    $HD_Form->AddEditElement(
        _("COMMISSION PERCENTAGE"),
        "commission",
        _("Commission in percent"),
        "size=20 maxlength=20",
        21,
        _("Insert the commision"),
        "",
        "NO-NULL"
    );

    $HD_Form->AddEditElement(
        _("THRESHOLD REMITTANCE"),
        "threshold_remittance",
        _("currency : ") . strtoupper(BASE_CURRENCY),
        "size=20 maxlength=20",
        12,
        _("threshold remittance"),
        "",
        "NO"
    );

    $HD_Form->AddEditElement(
        _("VAT"),
        "vat",
        _("VAT"),
        "size=20 maxlength=20",
        12,
        _("Insert the VAT"),
        "",
        "NO-NULL"
    );

    $HD_Form->AddEditSelect(_("LANGUAGE"), "language", $language_list);

    $HD_Form->AddEditElement(
        _("COMPANY"),
        "company",
        "",
        "size=60 maxlength=50",
        null,
        _("Insert the company")
    );

    $HD_Form->AddEditElement(
        _("LAST NAME"),
        "lastname",
        "",
        "size=60 maxlength=50",
        null,
        _("Insert the lastname")
    );

    $HD_Form->AddEditElement(
        _("FIRST NAME"),
        "firstname",
        "",
        "size=60 maxlength=50",
        null,
        _("Insert the firstname")
    );

    $HD_Form->AddEditElement(
        _("ADDRESS"),
        "address",
        "",
        "size=60 maxlength=250",
        null,
        _("Insert the address")
    );

    $HD_Form->AddEditElement(
        _("CITY"),
        "city",
        "",
        "size=20 maxlength=20",
        null,
        _("Insert the city")
    );

    $HD_Form->AddEditElement(
        _("STATE"),
        "state",
        "",
        "size=20 maxlength=40",
        null,
        _("Insert the state")
    );

    $HD_Form->AddEditElement(
        _("COUNTRY"),
        "country",
        "",
        "size=20 maxlength=40",
        null,
        _("Insert the country")
    );

    $HD_Form->AddEditElement(
        _("ZIPCODE"),
        "zipcode",
        "",
        "size=10 maxlength=7",
        null,
        _("Insert the zip code")
    );

    $HD_Form->AddEditElement(
        _("EMAIL"),
        "email",
        "",
        "size=30 maxlength=70",
        1,
        _("Insert the email"),
        "",
        "NO"
    );

    $HD_Form->AddEditElement(
        _("PHONE"),
        "phone",
        "",
        "size=30 maxlength=20",
        null,
        _("Insert the telephone number")
    );

    $HD_Form->AddEditElement(
        _("FAX"),
        "fax",
        "",
        "size=30 maxlength=20",
        null,
        _("Insert the fax number")
    );

    $HD_Form->AddEditTextarea(
        _("BANK INFO"),
        "bank_info",
        "",
        "cols='50' rows='4'", null, _("Insert the bank info of this agent")
    );

    $HD_Form->AddEditSqlSelect(
        _("CALL PLAN USABLE"),
        "tariffgroup",
        "cc_agent_tariffgroup",
        "label,id",
        "",
        "",
        [],
        "%1: (%2)",
        "",
        "size='10' multiple='multiple'",
        "", [
            "table" => "cc_agent_tariffgroup",
            "name" => "id_tariffgroup",
            "tables" => "cc_tariffgroup LEFT JOIN cc_agent_tariffgroup ON cc_tariffgroup.id=cc_agent_tariffgroup.id_tariffgroup",
            "columns" => "DISTINCT cc_tariffgroup.tariffgroupname, cc_tariffgroup.id",
            "where" => "cc_agent_tariffgroup.id_agent = %id",
            "format" => "%2 - (%1)",
            "fk" => "id_agent",
        ]
    );

	$HD_Form->FG_ADDITIONAL_FUNCTION_AFTER_ADD = 'creation_agent_refill';
	$HD_Form->FG_INTRO_TEXT_ADITION = '';
	$HD_Form->FG_LOCATION_AFTER_ADD = "?id=";
	$HD_Form->FG_LOCATION_AFTER_EDIT = "?id=";
	$HD_Form->FG_LOCATION_AFTER_DELETE = "?id=";
}
