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

if (! has_rights (ACX_ACCESS)) {
    Header ("HTTP/1.0 401 Unauthorized");
    Header ("Location: PP_error.php?c=accessdenied");
    die();
}




getpost_ifset(array('id', 'username', 'useralias', 'uipass', 'credit', 'language', 'tariff',
'id_didgroup','id_campaign', 'callback', 'activated','simultaccess', 'currency','typepaid', 'creditlimit',
'lastname', 'firstname', 'email', 'address','city', 'state', 'country', 'zipcode', 'phone', 'fax', 'inuse',
'cid', 'runservice', 'firstusedate','expirationdate', 'enableexpire', 'expiredays', 'sip_buddy',
'iax_buddy','popup_select', 'vat', 'autorefill', 'initialbalance', 'mac_addr', 'cardnumberlength_list',
'status',  'block', 'lock_pin', 'template_invoice', 'template_outstanding', 'description', 'voicemail_activated',
'voicemail_permitted','email_notification','credit_notification','notify_email', 'id_seria',
'company_name' , 'company_website' , 'vat_rn' , 'traffic' , 'traffic_target','discount','restriction' ));

if (!isset($form_action) || $form_action=="list" || $form_action=="addcredit") {
	$HD_Form = new FormHandler("cc_card LEFT JOIN cc_card_group ON cc_card.id_group=cc_card_group.id ", _("Card"), "cc_card.id");
	$HD_Form -> FG_QUERY_WHERE_CLAUSE = " cc_card_group.id_agent=".$_SESSION['agent_id'] ;
} else {
	$HD_Form = new FormHandler("cc_card", _("Card"));
}

$HD_Form->no_debug();
$HD_Form -> FG_TABLE_DEFAULT_SENS = "ASC";
$HD_Form -> search_session_key = 'entity_card_selection';
$HD_Form -> FG_FK_DELETE_CONFIRM = true;
$HD_Form -> FG_FK_DELETE_ALLOWED = true;
$HD_Form -> FG_FK_TABLENAMES = array('cc_iax_buddies', 'cc_sip_buddies', 'cc_callerid', 'cc_card_history');
$HD_Form -> FG_FK_EDITION_CLAUSE = array("id_cc_card", "id_cc_card", "id_cc_card", "id_cc_card");
// DEFINE IF WE WANT TO DELETE THE CARD OR NOT
$HD_Form -> FG_FK_WARNONLY = (DELETE_FK_CARD == true) ? false: true;

if (DELETE_FK_CARD) {
	// WE WILL DELETE THE FK
	$HD_Form -> FG_FK_DELETE_MESSAGE = gettext("You are going as well to remove all the SIP/IAX accounts & CallerIDs attached to this card! Please confirm that you really want to remove this card ? ");
} else {
	// WE JUST NOTIFY
	$HD_Form -> FG_FK_DELETE_MESSAGE = gettext("You are going to remove a card that have SIP/IAX accounts and/or CallerIDs attached to it ! Please confirm that you really want to remove this card ? ");
}

if (has_rights (ACX_GENERATE_CUSTOMER)) {
	$HD_Form ->FG_LIST_ADDING_BUTTON1 = true;
	$HD_Form ->FG_LIST_ADDING_BUTTON_LINK1 = "A2B_entity_card_multi.php?section=".$_SESSION["menu_section"];
	$HD_Form ->FG_LIST_ADDING_BUTTON_ALT1 = $HD_Form ->FG_LIST_ADDING_BUTTON_MSG1 = gettext("Generate Customers");
	$HD_Form ->FG_LIST_ADDING_BUTTON_IMG1 = Images_Path ."/user_multi_add.png" ;
}

if (has_rights (ACX_CREATE_CUSTOMER)) {
	$HD_Form -> FG_ENABLE_ADD_BUTTON = true;
	$HD_Form ->FG_LIST_ADDING_BUTTON2 = true;
	$HD_Form ->FG_LIST_ADDING_BUTTON_LINK2 = "A2B_entity_card.php?form_action=ask-add&atmenu=card&section=".$_SESSION["menu_section"];
	$HD_Form ->FG_LIST_ADDING_BUTTON_ALT2 = $HD_Form ->FG_LIST_ADDING_BUTTON_MSG2 = gettext("Add Customer");
	$HD_Form ->FG_LIST_ADDING_BUTTON_IMG2 = Images_Path ."/user_add.png" ;
}

$language_list = getLanguagesList();
$language_list_r = array_map("array_reverse", $language_list);
$discount_list = getDiscount_List();
$simultaccess_list = getCardAccess_List();
$simultaccess_list_r = array_map("array_reverse", $simultaccess_list);
$limits_notify_list = getLimitNotify_List($A2B);
array_unshift($limits_notify_list, [gettext("NOT DEFINED"), -1]);

$currency_list = array();
$currency_list_r = array();
$indcur=0;

$currencies_list = get_currencies();
foreach($currencies_list as $key => $cur_value) {
	$currency_list[$key]  = array( $cur_value[1].' ('.$cur_value[2].')', $key);
	$currency_list_r[$key]  = array( $key, $cur_value[1]);
	$currency_list_key[$key][0] = $key;
}

$restriction_list = getRestrictionList();
$cardstatus_list = getCardStatus_List();
$cardstatus_list_r = array_map("array_reverse", $cardstatus_list);
$cardstatus_list_acronym = getCardStatus_Acronym_List();
$typepaid_list = getPaidTypeList();
$expire_list = getCardExpire_List();
$yesno = getYesNoList();
$invoiceday_list = getInvoiceDay_List();

// Search for the field to display on the show list
$show_list=array();
$show_list_sizes=array();
$show_list_temp=array();

$show_list_temp = preg_split('/,/', $A2B->config["webui"]['card_show_field_list']);
foreach ($show_list_temp as $i => $value) {
	$a = strtolower(trim($value));
	$pos = strpos($a, ':');
	if (!($pos === false)) {
		$temp_ar = preg_split('/:/', $a);
		$b = $temp_ar[1];
		$a = strtolower(trim($temp_ar[0]));
		if (strlen($a)>1) {
		$show_list_sizes[$a] = $b;
		}
	}
	if (strlen($a)>1) {
		$show_list[$a] = $a;
	}
}
$show_fields=array();
$show_fields['id']=array(gettext("ID"), "cc_card.id", "3%", "center", "sort","", "", "", "", "", "", "");

$show_fields['username']=array("Account Number", "username", "14%", "center", "sort", "", "30", "", "", "", "", "display_customer_link");
if (!($popup_select>=1))
 $show_fields['useralias']=array("<acronym title=\"WEB UI LOGIN\">".gettext("LOGIN")."</acronym>", "useralias", "12%", "center", "sort","", "", "", "", "", "", "");

$show_fields['lastname']=array(gettext("LASTNAME"), "lastname", "7%", "center", "sort", "15","", "", "", "", "", "");
$show_fields['id_group']=array(gettext("GROUP"), "id_group", "6%", "center", "sort", "15", "lie", "cc_card_group", "name", "id='%id'", "%1","");
$show_fields['credit']=array("<acronym title=\"".gettext("BALANCE")."\">".gettext("BA")."</acronym>", "credit", "5%", "center", "sort", "", "", "", "", "", "", "display_2dec");

if (!($popup_select>=1)) {
	$serial_pad_length=$A2B->config["webui"]['card_serial_length'];
	$show_fields['id_seria']=array(gettext("SERIA"), "id_seria", "*", "center", "sort", "15", "lie", "cc_card_seria", "name","id='%id'", "%1","");
	$show_fields['serial']=array("<acronym title=\"".gettext("SERIAL NUMBER")."\">SERIAL#</acronym>", "serial", "*","center", "sort","","eval",'str_pad("%C",'.$serial_pad_length.' , "0", STR_PAD_LEFT)');
	$show_fields['tariff']=array("<acronym title=\"".gettext("Call Plan")."\">".gettext('PLAN')."</acronym>",
	       "tariff", "8%", "center", "sort", "15", "lie", "cc_tariffgroup", "tariffgroupname", "id='%id'", "%1");
	$show_fields['status']=array(gettext("STATUS"), "status", "4%", "center", "sort", "", "list", $cardstatus_list_acronym);
	$show_fields['language']=array(gettext("LG"), "language", "3%", "center", "sort");
	$show_fields['inuse']=array(gettext("USE"), "inuse", "3%", "center", "sort");
	$show_fields['currency']=array("<acronym title=\"".gettext("CURRENCY")."\">".gettext("CUR")."</acronym>", "currency", "4%", "center", "sort", "", "list", $currency_list_key);
	$show_fields['sip_buddy']=array(gettext("SIP"), "sip_buddy", "2%", "center", "sort", "", "list", $yesno);
	$show_fields['iax_buddy']=array(gettext("IAX"), "iax_buddy", "2%", "center", "sort", "", "list", $yesno);
	$show_fields['nbused']=array("<acronym title=\"AMOUNT OF CALL DONE\">".gettext("ACD")."</acronym>", "nbused", "3%", "center", "sort");
	$show_fields['firstname']=array(gettext("FIRSTNAME"), "firstname", "7%", "center", "sort", "15");
	$show_fields['email']=array(gettext("EMAIL"), "email","","center", "sort", "15");
	$show_fields['discount']=array("<acronym title=\"DISCOUNT\">".gettext("DISC")."</acronym>", "discount","","center", "sort", "15","30", "", "", "", "", "display_percentage");
	$show_fields['callerid']=array("<acronym title=\"CALLER IDENTIFICATION NUMBER\">".gettext("CID")."</acronym>", "callerid","","center", "sort", "15");
	$FG_COL_QUERY = 'DISTINCT ';
}else{
	$show_list_temp=array('cc_card.id', 'username', 'lastname', 'id_group',  'credit');
	foreach ($show_list_temp as $i => $value) {
                $show_list[$value]=$value;
	}

	$FG_COL_QUERY='DISTINCT ';
}
$FG_COL_QUERY_FIELD='';
$index=0;
foreach ($show_list as $i => $value) {
	if (array_key_exists($i,$show_fields)) {
		$param = $show_fields[$i];
		$param = $param+array("","","","","","");#expand array to max length
		$size = "*";
		$param7=$param[7];
		if ($param[6]=="eval"){
			$param7 = str_replace('%C', '%'.$index, $param7);//because of dynamic nature need set current field for eval
		};
		if (array_key_exists($i,$show_list_sizes)) {
			if ((strlen(trim($show_list_sizes[$i])==0))&&(strlen(trim($param[2])>0))){
				$size=$param[2];
			}else{
				$size=$show_list_sizes[$i];
			}
		}
		$HD_Form -> AddViewElement($param[0], $param[1], $param[4], $param[5], $param[11], $param[6], $param7,
            $param[8], $param[9], $param[10], $param[12]);
		if ($value=='callerid') {
			$FG_COL_QUERY_FIELD = $FG_COL_QUERY_FIELD .', (select GROUP_CONCAT(cid SEPARATOR \'<br>\') from cc_callerid where id_cc_card=cc_card.id) as callerid';
		} else {
			$FG_COL_QUERY_FIELD = $FG_COL_QUERY_FIELD .', '.$value;
		}
		$index = $index + 1;
	}
}
if (strlen($FG_COL_QUERY_FIELD)>2){
	$FG_COL_QUERY_FIELD=substr( $FG_COL_QUERY_FIELD,2);
}
$FG_COL_QUERY_COUNT = count(preg_split('/,/',$FG_COL_QUERY_FIELD));
$FG_COL_QUERY_FIELD=$FG_COL_QUERY_FIELD.', cc_card.id as id_,username as username_,useralias as alias_ ';
$HD_Form -> FieldViewElement ($FG_COL_QUERY.' '.$FG_COL_QUERY_FIELD);
$pos = strpos($FG_COL_QUERY_FIELD, "id");
$FG_COL_QUERY_FIELD=substr_replace($FG_COL_QUERY_FIELD,"cc_card.",$pos,0);
$HD_Form -> FieldViewElement ($FG_COL_QUERY.' '.$FG_COL_QUERY_FIELD);

$HD_Form -> FG_LIST_VIEW_PAGE_SIZE = 10;

// Code here for adding the fields in the Export File
$HD_Form -> FieldExportElement(explode(",", $A2B->config['webui']['card_export_field_list']));
if (!($popup_select>=1)) $HD_Form -> FG_EXPORT_CSV = false;
if (!($popup_select>=1)) $HD_Form -> FG_EXPORT_XML = false;
$HD_Form -> FG_EXPORT_SESSION_VAR = "pr_export_entity_card";


if (($popup_select>=1))	$HD_Form ->FG_LIST_VIEW_PAGE_SIZE = 7;


$HD_Form -> CV_FOLLOWPARAMETERS= "&popup_select=".$popup_select."&popup_formname=".$popup_formname."&popup_fieldname=".$popup_fieldname;

// Search form
if (!($popup_select>=1))	 $HD_Form -> search_form_enabled = true;

$HD_Form -> search_form_title = gettext('Define specific criteria to search for cards created.');
$HD_Form -> search_date_enabled = true;
$HD_Form -> search_date_text = gettext('CREATION DATE');

$HD_Form -> search_date2_enabled = true;
$HD_Form -> search_date2_text = gettext('FIRST USE DATE');
$HD_Form -> search_date2_column = 'firstusedate';


$HD_Form -> AddSearchTextInput(gettext("ACCOUNT NUMBER"), 'username','usernametype');
$HD_Form -> AddSearchTextInput(gettext("EMAIL"), 'email','emailtype');
$HD_Form -> AddSearchTextInput(gettext("LASTNAME"),'lastname','lastnametype');
$HD_Form -> AddSearchTextInput(gettext("LOGIN"),'useralias','useraliastype');
$HD_Form -> AddSearchTextInput(gettext("MACADDRESS"),'mac_addr','macaddresstype');
$HD_Form -> AddSearchComparisonInput(gettext("CUSTOMER ID"),'id1','id1type','id2','id2type','cc_card.id');
$HD_Form -> AddSearchComparisonInput(gettext("SERIAL"),'serial1','serial1type','serial2','serial2type','serial');
$HD_Form -> AddSearchComparisonInput(gettext("CREDIT"),'credit1','credit1type','credit2','credit2type','credit');
$HD_Form -> AddSearchComparisonInput(gettext("INUSE"),'inuse1','inuse1type','inuse2','inuse2type','inuse');

$HD_Form -> AddSearchSqlSelectInput(gettext("SELECT SERIA"), "cc_card_seria", "id, name", "", "name", "ASC", "id_seria");
$HD_Form -> AddSearchSelectInput(gettext("SELECT LANGUAGE"), "language", $language_list_r);
$HD_Form -> AddSearchSqlSelectInput(gettext("SELECT TARIFF"), "cc_tariffgroup", "id, tariffgroupname", "", "tariffgroupname", "ASC", "tariff");
$HD_Form -> AddSearchSelectInput(gettext("SELECT STATUS"), "status", $cardstatus_list_r);
$HD_Form -> AddSearchSelectInput(gettext("SELECT ACCESS"), "simultaccess", $simultaccess_list_r);
$HD_Form -> AddSearchSqlSelectInput(gettext("SELECT GROUP"), "cc_card_group", "id, name", "", "name", "ASC", "id_group");
$HD_Form -> AddSearchSelectInput(gettext("SELECT CURRENCY"), "currency", $currency_list_r);


if (($popup_select>=1)) {

	$HD_Form -> FG_OTHER_BUTTON1 = true;
	$HD_Form -> FG_OTHER_BUTTON1_ALT = '<font color="red">&lt;select&gt;</font>';
	$HD_Form -> FG_OTHER_BUTTON1_IMG = '';

	if ($popup_select==1) {
		$HD_Form -> FG_OTHER_BUTTON1_LINK = "javascript:sendValue('|param|');";
		//echo"<a title=\"Select Card\" href=\"javascript:sendValue('".$recordset[$FG_NB_TABLE_COL]."');\">&lt;Select Card&gt;</a>";
	} elseif ($popup_select==2) {
		$HD_Form -> FG_OTHER_BUTTON1_LINK = "javascript:sendValue('|col1|');";
		//echo"<a title=\"Select Card\" href=\"javascript:sendValue('".$recordset[1]."');\">&lt;Select Card&gt;</a>";
	}

} else {

	if (has_rights (ACX_EDIT_CUSTOMER)) {
		$HD_Form -> FG_ENABLE_EDIT_BUTTON = true;
	}

	$HD_Form -> FG_ENABLE_INFO_BUTTON = true;
	$HD_Form -> FG_INFO_BUTTON_LINK = "A2B_card_info.php?id=";

	if (has_rights (ACX_DELETE_CUSTOMER)) {
		$HD_Form -> FG_ENABLE_DELETE_BUTTON = true;
	}

	$FN_COLUMN_ID="col".($FG_COL_QUERY_COUNT);//get numbers of rows for needed fields
	$FN_COLUMN_USERNAME="col".($FG_COL_QUERY_COUNT+1);
	$FN_COLUMN_ALIAS="col".($FG_COL_QUERY_COUNT+2);

	if (has_rights (ACX_SIPCONF)) {
		$HD_Form -> FG_OTHER_BUTTON1 = true;
		$HD_Form -> FG_OTHER_BUTTON1_LINK="A2B_entity_friend.php?atmenu=sip&form_action=add_sip&id_cc_card=|$FN_COLUMN_ID|&cardnumber=|$FN_COLUMN_USERNAME|&useralias=|$FN_COLUMN_ALIAS|";
		$HD_Form -> FG_OTHER_BUTTON1_IMG = Images_Path . "/icon-sip.gif";
		$HD_Form -> FG_OTHER_BUTTON1_ALT=gettext("SIP buddy");
	}

	if (has_rights (ACX_IAXCONF)) {
		$HD_Form -> FG_OTHER_BUTTON2 = true;
		$HD_Form -> FG_OTHER_BUTTON2_LINK="A2B_entity_friend.php?atmenu=iax&form_action=add_iax&id_cc_card=|$FN_COLUMN_ID|&cardnumber=|$FN_COLUMN_USERNAME|&useralias=|$FN_COLUMN_ALIAS|";
		$HD_Form -> FG_OTHER_BUTTON2_IMG = Images_Path . "/icon-iax.gif";
		$HD_Form -> FG_OTHER_BUTTON2_ALT=gettext("IAX buddy");
	}
}

$HD_Form -> FG_FILTER_ENABLE = true;
$HD_Form -> FG_FILTER_COLUMN = 'cardnumber';
$HD_Form -> FG_FILTER_LABEL = 'account number';


if (isset($filterprefix)  &&  (strlen($filterprefix)>0)) {
	if (strlen($HD_Form -> FG_QUERY_WHERE_CLAUSE)>0) $HD_Form -> FG_QUERY_WHERE_CLAUSE.=" AND ";
	$HD_Form -> FG_QUERY_WHERE_CLAUSE.="username like '$filterprefix%'";
}

$HD_Form -> FG_ENABLE_EDIT_BUTTON = true;
$HD_Form -> FG_ENABLE_DELETE_BUTTON = true;

$maxi	= '';
$maxi2	= '';
$maxi3	= '';

if (isset($form_action) && $form_action=="ask-add") {
	if (!is_numeric($cardnumberlength_list)) 	$cardnumberlength_list = min($A2B->cardnumber_range);
	$array_card_generated  = gen_card_with_alias("cc_card", $cardnumberlength_list);
	$maxi = "value='".$array_card_generated[0]."'";
	$maxi2 = "value='".$array_card_generated[1]."'";
	$pass = MDP_NUMERIC(10);
	$maxi3 = "value='".$pass."'";
	$default_initbalance = "value='0'";
}

$HD_Form->AddEditElement(
    gettext("ACCOUNT NUMBER"),
    "username",
    '',
    "size=30 $maxi readonly  maxlength=40",
    4,
    gettext("Insert the account number"),
    gettext("Customer Information"))
;


$HD_Form->AddEditElement(
    gettext("WEBUI LOGIN"),
    "useralias",
    "",
    "size=20 $maxi2 maxlength=40",
    4,
    gettext("Insert the webui login")
);


$HD_Form->AddEditElement(
    gettext("WEBUI PASSWORD"),
    "uipass",
    gettext("Password for customer to access to the web interface and view the balance."),
    "size=20 $maxi3 maxlength=20",
    null,
    gettext("Insert the password")
);

$balance_delete="";
if (isset($form_action) && $form_action=="ask-delete") {
    $HD_Form->AddEditElement(
        gettext("BALANCE"),
        "credit",
        gettext("currency : ") . strtoupper(BASE_CURRENCY),
        "size=30 maxlength=30 ",
        12,
        gettext("Insert the number of credit")
    );

	$balance_delete = ",credit";
}

$HD_Form->AddEditSqlSelect(
    "id_group",
    gettext("CUSTOMER GROUP"),
    "cc_card_group", "name,id",
    "cc_card_group.id_agent='$_SESSION[agent_id]'"
);

$HD_Form->AddEditSqlSelect(
    "id_seria",
    gettext("SERIA"),
    "cc_card_seria",
    "name,id",
    "",
    "",
    '<option value="-1" selected="selected">' . gettext("NOT DEFINED") . '</option>'
);

$HD_Form->AddEditElement(
    gettext("LAST NAME"),
    "lastname",
    "",
    "size=30 maxlength=50",
    11,
    gettext("Insert the lastname of the customers"),
    gettext("Personal Information"),
    "NO"
);

$HD_Form->AddEditElement(
    gettext("FIRST NAME"),
    "firstname",
    "",
    "size=30 maxlength=50",
    11,
    gettext("Insert the firstname of the customers"),
    "",
    "NO"
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
    gettext("ADDRESS"),
    "address",
    "",
    "size=30 maxlength=100",
    0,
    gettext("Insert the address of the customers"),
    "",
    "NO")
;


$HD_Form->AddEditElement(
    gettext("CITY"),
    "city",
    "",
    "size=30 maxlength=40",
    0,
    gettext("Insert the city"),
    "",
    "NO"
);


$HD_Form->AddEditElement(
    gettext("STATE/PROVINCE"),
    "state",
    "",
    "size=30 maxlength=40",
    11,
    gettext("Insert the state"),
    "",
    "NO"
);

$HD_Form->AddEditSqlSelect(
    "country",
    gettext("COUNTRY"),
    "cc_country",
    "countryname,countrycode"
);

$HD_Form->AddEditElement(
    gettext("ZIP/POSTAL CODE"),
    "zipcode",
    "",
    "size=30 maxlength=20",
    0,
    gettext("Insert the zipcode"),
    "",
    "NO"
);


$HD_Form->AddEditElement(
    gettext("PHONE NUMBER"),
    "phone",
    "",
    "size=30 maxlength=20",
    7,
    gettext("Insert the phone number of the customers"),
    "",
    "NO"
);


$HD_Form->AddEditElement(
    gettext("FAX NUMBER"),
    "fax",
    "",
    "size=30 maxlength=20",
    null,
    gettext("Insert the fax number of the customers"),
    "",
    "NO"
);

$HD_Form->AddEditElement(
    gettext("COMPANY NAME"),
    "company_name",
    "",
    "size=40 maxlength=50",
    null,
    gettext("Insert Company name of this customer"),
    "",
    "NO"
);


$HD_Form->AddEditElement(
    gettext("COMPANY WEBSITE"),
    "company_website",
    "",
    "size=40 maxlength=60",
    null,
    gettext("Insert the company website of this customer"),
    "",
    "NO"
);


$callplan_add = "";
if (isset($form_action) && ($form_action=="ask-add" || $form_action=="add")) {
    $HD_Form->AddEditSqlSelect(
        "tariff",
        gettext("CALL PLAN"),
        "cc_tariffgroup,cc_agent_tariffgroup",
        "tariffgroupname,id",
        "cc_tariffgroup.id = cc_agent_tariffgroup.id_tariffgroup AND cc_agent_tariffgroup.id_agent = '$_SESSION[agent_id]'"
    );
	$callplan_add = ",tariff";
}

$HD_Form->AddEditSqlSelect(
    "id_didgroup",
    gettext("DIDGROUP"),
    "cc_didgroup",
    "didgroupname,id",
    "",
    "",
    '<option value="-1" selected="selected">' . gettext("NOT DEFINED") . '</option>'
);

$timezone_list = get_timezones();
foreach($timezone_list as $key => $cur_value) {
	if(in_array(SERVER_GMT,$cur_value)){
		$timezone_list[$key]  = array( $cur_value[2], $key." selected");
	}else{
		$timezone_list[$key]  = array( $cur_value[2], $key);
	}
}


$HD_Form->AddEditSelect("id_timezone", $timezone_list, gettext("TIMEZONE"));

$HD_Form->AddEditSelect("language", $language_list, gettext("LANGUAGE"));

$HD_Form->AddEditSelect(
    "currency",
    $currency_list,
    gettext("CURRENCY"),
    gettext("Currency used at the customer end.")
);

$HD_Form->AddEditSelect("status", $cardstatus_list, gettext("STATUS"));

$HD_Form->AddEditRadio(
    "block",
    [[gettext("Yes"), "1"], [gettext("No"), "0"]],
    gettext("LOCK"),
    "0",
    gettext("Enable lock for this account."),
    gettext("Choose if you want to enable the lock on this account")
);

if ($form_action =="ask-add" || $form_action=="add") {
    $lockpin_default = "value = '0'";
}

$HD_Form->AddEditElement(
    gettext("LOCK PIN"),
    "lock_pin",
    gettext("Code required to make the call if the lock is active."),
    "size=20 maxlength=10 $lockpin_default",
    4,
    "",
    "",
    "NO"
);

$HD_Form->AddEditSelect(
    "simultaccess",
    $simultaccess_list,
    gettext("SIMULTANEOUS ACCESS"),
    "",
    1
);

$HD_Form->AddEditRadio(
    "runservice",
    [[gettext("Yes"), "1"], [gettext("No"), "0"]],
    gettext("RUN SERVICE"),
    "0",
    gettext("Apply recurring service to this account."),
    gettext("Choose if you want to enable the service recurring on this account")
);

$HD_Form->AddEditSelect(
    "credit_notification",
    $limits_notify_list,
    gettext("CREDIT LIMIT NOTIFICATION"),
    gettext("currency : ") . strtoupper(BASE_CURRENCY) . '<BR>' . gettext("Low credit limit to alert the customer")
);

$HD_Form->AddEditRadio(
    "notify_email",
    [[gettext("Yes"), "1"], [gettext("No"), "0"]],
    gettext("PERMITTED NOTIFICATIONS BY MAIL"),
    "0",
    gettext("Enable the notification by mail for this account."),
    gettext("Choose if you want to enable the notification by email for this account")
);

$HD_Form->AddEditElement(
    gettext("EMAIL NOTIFICATION"),
    "email_notification",
    "",
    "size=30 maxlength=70",
    1,
    gettext("Insert the email to notify this customer"),
    "",
    "NO"
);


if (ACT_VOICEMAIL) {
    $HD_Form->AddEditRadio(
        "voicemail_permitted",
        [[gettext("Yes"), "1"], [gettext("No"), "0"]],
        gettext("PERMITTED VOICEMAIL"),
        "1",
        gettext("Enable the voicemail functionality for this account."),
        gettext("Choose if you want to enable the voicemail functionality for this account"),
    );

    $HD_Form->AddEditRadio(
        "voicemail_activated",
        [[gettext("Yes"), "1"], [gettext("No"), "0"]],
        gettext("ACTIVE VOICEMAIL SERVICE"),
        "0",
        gettext("Customer can enable the voicemail for this account."),
        gettext("Customer can choose to enable the voicemail for this account")
    );
}

$HD_Form->AddEditSqlSelect(
    "id_campaign",
    gettext("CAMPAIGN"),
    "cc_campaign",
    "name,id",
    "",
    "",
    '<option value="-1" selected="selected">' . gettext("NOT DEFINED") . '</option>'
);

$HD_Form->AddEditElement(
    gettext("FIRST USE DATE"),
    "firstusedate",
    "",
    "size=40 maxlength=40 readonly",
    null,
    "the first use date",
    "",
    "NO-NULL",
    "res_display_dateformat"
);


$HD_Form->AddEditSelect("enableexpire", $expire_list, gettext("ENABLE EXPIRY"), gettext("Select method of expiry for the account."));

$comp_zero = $comp_date_plus='';
if (isset($form_action) && $form_action=="ask-add") {
	$begin_date = date("Y");
	$begin_date_plus = date("Y") + 10;
	$end_date = date("-m-d H:i:s");
	$comp_date = "value='".$begin_date.$end_date."'";
	$comp_date_plus = "value='".$begin_date_plus.$end_date."'";
	$comp_zero = "value='0'";
}

$HD_Form->AddEditElement(
    gettext("EXPIRY DATE"),
    "expirationdate",
    gettext("please respect the format YYYY-MM-DD HH:MM:SS. For instance, '2004-12-31 00:00:00'"),
    "size=40 maxlength=40  $comp_date_plus",
    10,
    gettext("Insert the expiration date"),
    "",
    "NO-NULL",
    "res_display_dateformat"
);



$HD_Form->AddEditElement(
    gettext("EXPIRY DAYS"),
    "expiredays",
    gettext("The number of days after which the account will expire."),
    "size=10 maxlength=6 $comp_zero",
    4,
    gettext("Insert the number of days after which the account will expire")
);


if (!empty($A2B->config["webui"]['buddy_sip_file'])) {
    $HD_Form->AddEditRadio(
        "sip_buddy",
        [[gettext("Yes"), "1"], [gettext("No"), "0"]],
        gettext("CREATE SIP CONFIG"),
        "1",
        gettext("Create the SIP config automatically"),
        gettext("Choose if you want to enable the SIP account")
    );
}

if (!empty($A2B->config["webui"]['buddy_iax_file'])) {
    $HD_Form->AddEditRadio(
        "iax_buddy",
        [[gettext("Yes"), "1"], [gettext("No"), "0"]],
        gettext("CREATE IAX CONFIG"),
        "1",
        gettext("Create the IAX config automatically"),
        gettext("Choose if you want to enable the IAX account")
    );
}

$HD_Form->AddEditElement(
    gettext("MAC ADDRESS"),
    "mac_addr",
    gettext("FORMAT: 00-08-74-4C-7F-1D"),
    "size=20 maxlength=17",
    null,
    gettext("Insert the MAC address customers' device"),
    "",
    "NO"
);


$HD_Form->AddEditElement(
    gettext("IN USE"),
    "inuse",
    gettext("Updated to show the number of concurrent calls in use by this customer. If there are no currently no calls, and the system shows that there are, manually reset this field back to zero."),
    "size=5 maxlength=5",
    null,
    "",
    "",
    "NO"
);

$HD_Form->AddEditRadio(
    "autorefill",
    [[gettext("Yes"), "1"], [gettext("No"), "0"]],
    gettext("AUTOREFILL"),
    "0",
    gettext("Define if you want to authorize the autorefill to apply on this accout"),
    gettext("Choose if you want to enable the autorefill on this account"),
    gettext("AUTOREFILL")
);

$HD_Form->AddEditElement(
    gettext("INITIAL BALANCE"),
    "initialbalance",
    gettext("The initial balance is used by autorefill to reset the current balance to this amount"),
    "size=30 $default_initbalance maxlength=30",
    12,
    gettext("Insert the amount of the initial balance")
);



$HD_Form->AddEditSelect(
    "invoiceday",
    $invoiceday_list,
    gettext("INVOICE DAY"),
    gettext("Define the day of the month when the system will generate the customer invoice."),
    "",
    "",
    gettext("Invoice Status")
);

$HD_Form->AddEditElement(
    gettext("VAT"),
    "vat",
    gettext("VAT to add on the invoice of this customer. it should be a decimal value '21' this will be for 21% of VAT!"),
    "size=10 maxlength=6 value=0",
    12,
    gettext("Insert the number for the VAT")
);


$HD_Form->AddEditElement(
    gettext("VAT REGISTRATION NUMBER "),
    "vat_rn",
    "",
    "size=40 maxlength=40",
    null,
    gettext("Insert the VAT registration number of this customer"),
    "",
    "NO"
);

$HD_Form->AddEditSelect("discount", $discount_list, gettext("DISCOUNT"));

$HD_Form->AddEditElement(
    gettext("TRAFFIC PER MONTH"),
    "traffic",
    "",
    "size=30 maxlength=20",
    null,
    gettext("Insert the traffic per month for this customer"),
    gettext("TARGET TRAFFIC"),
    "NO"
);

$HD_Form->AddEditTextarea(
    "traffic_target",
    gettext("TARGET TRAFFIC"),
    "cols='50' rows='4'",
    gettext("Insert the target traffic description")
);

$HD_Form->AddEditSelect(
    "restriction",
    $restriction_list,
    gettext("RESTRICTION"),
    "",
    0,
    "",
    gettext("RESTRICTED NUMBERS")
);

$HD_Form->AddEditHasMany(
    gettext("RESTRICTED NUMBERS"),
    [
        "table" => "cc_restricted_phonenumber",
        "name" => "number",
        "columns" => "number,id_card",
        "where" => "id_card=%id",
        "fk" => "id_card",
        "regex" => 0,
    ]
);

$HD_Form->AddEditHasMany(
    gettext("CALLERID"),
    [
        "table" => "cc_callerid",
        "name" => "cid",
        "columns" => "cid,id_cc_card",
        "where" => "id_cc_card=%id",
        "fk" => "id_cc_card",
        "regex" => 0,
    ],
    false,
    gettext("CALLERID / CUSTOMER HISTORY")
);

$HD_Form->AddEditHasMany(
    gettext("CUSTOMER HISTORY"),
    [
        "table" => "cc_card_history",
        "name" => "description",
        "columns" => "description,id_cc_card,datecreated",
        "where" => "id_cc_card=%id",
        "fk" => "id_cc_card",
        "regex" => 0,
        "extra_col" => 2,
    ],
    true
);

$add_fields_voicemail = '';
if (ACT_VOICEMAIL)
	$add_fields_voicemail = 'voicemail_permitted, voicemail_activated, ';

$HD_Form -> FieldEditElement ('username, useralias, uipass'.$balance_delete.', id_group, id_seria, lastname, firstname, email, '.
		' address, city, state, country, zipcode, phone, fax,company_name, company_website '.
		$callplan_add.', id_didgroup, id_timezone, language, currency, status, block, lock_pin, simultaccess,  runservice, ' .
		' credit_notification, notify_email, email_notification, ' .$add_fields_voicemail.
		' id_campaign, ' .
		' firstusedate, enableexpire, expirationdate, expiredays, sip_buddy, iax_buddy, mac_addr, inuse, autorefill, initialbalance,' .
		' invoiceday, vat, vat_rn, discount, traffic, traffic_target, restriction' );


if(!isset($myusername)) $myusername='';

$HD_Form -> FG_INTRO_TEXT_ADITION = '';



$HD_Form -> FG_ADDITIONAL_FUNCTION_BEFORE_EDITION = 'change_card_lock';

$HD_Form -> FG_ADDITIONAL_FUNCTION_AFTER_ADD = 'processing_card_add_agent';
$HD_Form -> FG_ADDITIONAL_FUNCTION_AFTER_EDITION = 'create_status_log';
$HD_Form -> FG_ADDITIONAL_FUNCTION_AFTER_DELETE = 'processing_card_del_agent';

if (isset($form_action) && $form_action=='delete' && USE_REALTIME) {
	$_SESSION["is_sip_iax_change"]	= 1;
	$_SESSION["is_sip_changed"]		= 1;
	$_SESSION["is_iax_changed"]		= 1;
}

$HD_Form -> FG_LOCATION_AFTER_ADD = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?section=".$_SESSION["menu_section"]."&id=";
$HD_Form -> FG_LOCATION_AFTER_EDIT = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?section=".$_SESSION["menu_section"]."&id=";
$HD_Form -> FG_LOCATION_AFTER_DELETE = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?section=".$_SESSION["menu_section"]."&id=";


