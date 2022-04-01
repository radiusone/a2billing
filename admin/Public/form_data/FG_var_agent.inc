<?php

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

if (! has_rights (ACX_ADMINISTRATOR)) {
    Header ("HTTP/1.0 401 Unauthorized");
    Header ("Location: PP_error.php?c=accessdenied");
    die();
}


getpost_ifset(array('id', 'login', 'passwd', 'active', 'location', 'id_tariffgroup', 'options', 'credit',
'currency', 'locale', 'commission', 'vat', 'banner', 'language', 'perms', 'lastname', 'firstname', 'address', 'city', 'state',
'country', 'zipcode', 'phone', 'email', 'fax'));


$FG_INSTANCE_NAME = gettext("Agent");
$HD_Form = new FormHandler("cc_agent", $FG_INSTANCE_NAME);

$HD_Form->no_debug();
$HD_Form -> FG_TABLE_DEFAULT_ORDER = " datecreation";
$HD_Form -> FG_TABLE_DEFAULT_SENS = "ASC";


$HD_Form ->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form ->FG_LIST_ADDING_BUTTON_LINK1 = "A2B_entity_agent.php?form_action=ask-add&groupID=0&section=".$_SESSION["menu_section"];
$HD_Form ->FG_LIST_ADDING_BUTTON_ALT1 = $HD_Form ->FG_LIST_ADDING_BUTTON_MSG1 = gettext("Add AGENT");
$HD_Form ->FG_LIST_ADDING_BUTTON_IMG1 = Images_Path ."/user_red.png" ;
$HD_Form -> FG_OTHER_BUTTON1_LINK = "A2B_agent_home.php?id=";
$HD_Form -> FG_OTHER_BUTTON1_IMG = Images_Path."/comment_edit.png";
$HD_Form -> FG_OTHER_BUTTON1 = true;
$HD_Form -> FG_OTHER_BUTTON1_ALT = gettext("Edit agent homepage");

$HD_Form -> FG_OTHER_BUTTON2 = true;
$HD_Form -> FG_OTHER_BUTTON2_LINK="A2B_entity_payment_agent.php?form_action=ask-add&agent_id=|col0|";
$HD_Form -> FG_OTHER_BUTTON2_IMG = Images_Path . "/money.png";
$HD_Form -> FG_OTHER_BUTTON2_ALT = gettext('Add Payment to this Agent');

$HD_Form -> FG_QUERY_WHERE_CLAUSE = $FG_TABLE_CLAUSE;

$language_list = getLanguagesList();


$right_list = array();
$right_list["1"] = array( gettext("VIEW CUSTOMERS"), "1");
$right_list["2"] = array( gettext("BILLING"), "2");
$right_list["3"] = array( gettext("RATECARD"), "4");
$right_list["4"] = array( gettext("CALL REPORT"), "8");
$right_list["5"] = array( gettext("MY ACCOUNT"), "16");
$right_list["6"] = array( gettext("SUPPORT"), "32");
$right_list["7"] = array( gettext("CREATE CUSTOMER"), "64");
$right_list["8"] = array( gettext("EDIT CUSTOMER"), "128");
$right_list["9"] = array( gettext("DELETE CUSTOMER"), "256");
$right_list["10"] = array( gettext("GENERATE CUSTOMER"), "512");
$right_list["11"] = array( gettext("SIGNUP"), "1024");
$right_list["12"] = array( gettext("VOIP CONF"), "2048");
$right_list["13"] = array( gettext("SEE CUSTOMERS CALLERID"), "4096");

$len_right_list = count($right_list);

$yesno = getYesNoList();


$currency_list=getCurrenciesList();
$currency_list_r = getCurrenciesRateList();
$currency_list_key = getCurrenciesKeyList() ;


if ($popup_select) {
	$HD_Form -> AddViewElement(gettext("ID"), "id");
	$HD_Form -> AddViewElement(gettext("LOGIN"), "login");
	$HD_Form -> AddViewElement(gettext("FIRSTNAME"), "firstname");
	$HD_Form -> AddViewElement(gettext("LASTNAME"), "lastname");
	$HD_Form -> AddViewElement("<acronym title=\"" . gettext("CURRENCY") . "\">" . gettext("CUR") . "</acronym>", "currency", true, 0, "", "list", $currency_list_key);
	$HD_Form -> AddViewElement(gettext("ACTIVE"), "active", true, 0, "", "list", $yesno);

	$HD_Form -> FieldViewElement ('id, login, passwd, lastname, currency, active');
} else {
	$HD_Form -> AddViewElement(gettext("ID"), "id", "sort");
	$HD_Form -> AddViewElement(gettext("DATE"), "datecreation", true, 19, "display_dateformat");
	$HD_Form -> AddViewElement(gettext("LOGIN"), "login");
	$HD_Form -> AddViewElement(gettext("PASSWORD"), "passwd");
	$HD_Form -> AddViewElement(gettext("FIRSTNAME"), "firstname");
	$HD_Form -> AddViewElement(gettext("LASTNAME"), "lastname");
	$HD_Form -> AddViewElement(gettext("CREDIT"), "credit");
	$HD_Form -> AddViewElement(gettext("COMMISSION"), "com_balance");
	$HD_Form -> AddViewElement("<acronym title=\"" . gettext("CURRENCY") . "\">" . gettext("CUR") . "</acronym>", "currency", true, 0, "", "list", $currency_list_key);
	$HD_Form -> AddViewElement(gettext("ACTIVE"), "active", true, 0, "", "list", $yesno);

	$HD_Form -> FieldViewElement ('id, datecreation, login, passwd, firstname, lastname, credit, com_balance, currency, active');
}

if ($popup_select) {
	$HD_Form -> FG_LIST_VIEW_PAGE_SIZE = 7;
	$HD_Form -> CV_FOLLOWPARAMETERS = "&popup_select=" . $popup_select . "&popup_formname=" . $popup_formname . "&popup_fieldname=" . $popup_fieldname;

	$HD_Form -> FG_OTHER_BUTTON1 = true;
	$HD_Form -> FG_OTHER_BUTTON1_ALT = '<font color="red">&lt;select&gt;</font>';
	$HD_Form -> FG_OTHER_BUTTON1_IMG = '';
	$HD_Form -> FG_OTHER_BUTTON2 = false;
	//echo $popup_select;
	if ($popup_select == 1) {
        $HD_Form->FG_OTHER_BUTTON1_LINK = "javascript:sendValue('|param|');";
    } elseif ($popup_select == 2) {
        $HD_Form->FG_OTHER_BUTTON1_LINK = "javascript:sendValue('|col0|');";
    }
} else {
	$HD_Form -> FG_ENABLE_INFO_BUTTON = true;
	$HD_Form -> FG_INFO_BUTTON_LINK = "A2B_agent_info.php?id=";
}

if (has_rights (ACX_MODIFY_AGENTS) && !($popup_select)) {

	$HD_Form -> FG_ENABLE_EDIT_BUTTON = true;
	$HD_Form -> FG_ENABLE_ADD_BUTTON = true;
	$HD_Form -> FG_ENABLE_DELETE_BUTTON = true;


	$HD_Form->AddEditElement(
        gettext("LOGIN"),
        "login",
        "",
        "size=30 maxlength=12",
        3,
        gettext("Insert the login")
    );

    $HD_Form->AddEditElement(
        gettext("PASSWORD"),
        "passwd",
        "",
        "size=30 maxlength=15",
        3, gettext("Insert the password")
    );

    $HD_Form->AddEditSelect(
        "perms",
        $right_list,
        gettext("PERMISSION"),
        gettext("To select several rights in the above ListBox, hold the CTRL key down while clicking the items"),
        "",
        "size='$len_right_list' multiple='multiple'"
    );

    $HD_Form->AddEditRadio(
        "active",
        [[gettext("Yes"), "1"], [gettext("No"), "0"]],
        gettext("ACTIVE"),
        "1",
        gettext("Enable agent"),
        gettext("Choose if you want to active this agent")
    );

	if ($form_action =="ask-add" || $form_action=="add") {
		$balance_access = "value = '0'";
		$default_com_bal = "value='0'";
	} else {
		$balance_access = 'readonly';
        $default_com_bal = "";
	}

    $HD_Form->AddEditElement(
        gettext("BALANCE"),
        "credit",
        gettext("currency : ") . strtoupper(BASE_CURRENCY),
        "size=30 maxlength=30 $balance_access",
        12,
        gettext("amount of credit")
    );

    $HD_Form->AddEditElement(
        gettext("COMMISSION BALANCE"),
        "com_balance",
        gettext("currency : ") . strtoupper(BASE_CURRENCY),
        "readonly $default_com_bal",
        null,
        gettext("amount of commission")
    );

    $HD_Form->AddEditSelect("currency", $currency_list, gettext("CURRENCY"), gettext("Currency used at the customer end."));

    $HD_Form->AddEditElement(
        gettext("COMMISSION PERCENTAGE"),
        "commission",
        gettext("Commission in percent"),
        "size=20 maxlength=20",
        21,
        gettext("Insert the commision"),
        "",
        "NO-NULL"
    );

    $HD_Form->AddEditElement(
        gettext("THRESHOLD REMITTANCE"),
        "threshold_remittance",
        gettext("currency : ") . strtoupper(BASE_CURRENCY),
        "size=20 maxlength=20",
        12,
        gettext("treshold remittance")
    );

    $HD_Form->AddEditElement(
        gettext("VAT"),
        "vat",
        gettext("VAT"),
        "size=20 maxlength=20",
        12,
        gettext("Insert the VAT"),
        "",
        "NO-NULL"
    );

    $HD_Form->AddEditSelect("language", $language_list, gettext("LANGUAGE"));

    $HD_Form->AddEditElement(
        gettext("COMPANY"),
        "company",
        "",
        "size=60 maxlength=50",
        null,
        gettext("Insert the company")
    );

    $HD_Form->AddEditElement(
        gettext("LAST NAME"),
        "lastname",
        "",
        "size=60 maxlength=50",
        null,
        gettext("Insert the lastname")
    );

    $HD_Form->AddEditElement(
        gettext("FIRST NAME"),
        "firstname",
        "",
        "size=60 maxlength=50",
        null,
        gettext("Insert the firstname")
    );

    $HD_Form->AddEditElement(
        gettext("ADDRESS"),
        "address",
        "",
        "size=60 maxlength=250",
        null,
        gettext("Insert the address")
    );

    $HD_Form->AddEditElement(
        gettext("CITY"),
        "city",
        "",
        "size=20 maxlength=20",
        null,
        gettext("Insert the city")
    );

    $HD_Form->AddEditElement(
        gettext("STATE"),
        "state",
        "",
        "size=20 maxlength=40",
        null,
        gettext("Insert the state")
    );

    $HD_Form->AddEditElement(
        gettext("COUNTRY"),
        "country",
        "",
        "size=20 maxlength=40",
        null,
        gettext("Insert the country")
    );

    $HD_Form->AddEditElement(
        gettext("ZIPCODE"),
        "zipcode",
        "",
        "size=10 maxlength=7",
        null,
        gettext("Insert the zip code")
    );

    $HD_Form->AddEditElement(
        gettext("EMAIL"),
        "email",
        "",
        "size=30 maxlength=70",
        1,
        gettext("Insert the email"),
        "",
        "NO"
    );

    $HD_Form->AddEditElement(
        gettext("PHONE"),
        "phone",
        "",
        "size=30 maxlength=20",
        null,
        gettext("Insert the telephone number")
    );

    $HD_Form->AddEditElement(
        gettext("FAX"),
        "fax",
        "",
        "size=30 maxlength=20",
        null,
        gettext("Insert the fax number")
    );

    $HD_Form->AddEditTextarea(
        "bank_info",
        gettext("BANK INFO"),
        "cols='50' rows='4'",
        gettext("Insert the bank info of this agent")
    );

    $HD_Form->AddEditSqlSelect(
        "tariffgroup",
        gettext("CALL PLAN USABLE"),
        "cc_agent_tariffgroup",
        "label,id",
        "",
        "",
        "",
        "",
        "%1: (%2)",
        "size='10' multiple='multiple'",
        [
            "name" => "id_tariffgroup",
            "tables" => "cc_tariffgroup LEFT JOIN cc_agent_tariffgroup ON cc_tariffgroup.id=cc_agent_tariffgroup.id_tariffgroup",
            "columns" => "DISTINCT cc_tariffgroup.tariffgroupname, cc_tariffgroup.id",
            "where" => "cc_agent_tariffgroup.id_agent = %id",
            "format" => "%2 - (%1)",
        ]
    );

    $FG_QUERY_EDITION = 'login, passwd, perms, active, credit,com_balance, currency, commission,threshold_remittance, vat, language, company, lastname, firstname, address, city, state, country, zipcode, email, phone,  fax,bank_info';
    $HD_Form -> FieldEditElement ($FG_QUERY_EDITION);

	$HD_Form -> FG_EDIT_QUERY_CONDITION = " id='%id'";
	$HD_Form -> FG_ADDITIONAL_FUNCTION_AFTER_ADD = 'creation_agent_refill';
	$HD_Form -> FG_INTRO_TEXT_ADITION = '';
	$HD_Form -> FG_LOCATION_AFTER_ADD = "?atmenu=document&stitle=Document&wh=AC&id=";
	$HD_Form -> FG_LOCATION_AFTER_EDIT = "?atmenu=document&stitle=Document&wh=AC&id=";
	$HD_Form -> FG_LOCATION_AFTER_DELETE = "?atmenu=document&stitle=Document&wh=AC&id=";


}
