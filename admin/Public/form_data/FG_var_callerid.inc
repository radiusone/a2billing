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

getpost_ifset(array('IDCust', 'id', 'cid', 'activated'));

$HD_Form = new FormHandler("cc_callerid", "CallerID");

$HD_Form->no_debug();

$HD_Form ->FG_LIST_ADDING_BUTTON2 = true;
$HD_Form ->FG_LIST_ADDING_BUTTON_LINK2 = "A2B_entity_callerid.php?form_action=ask-add&section=".$_SESSION["menu_section"];
$HD_Form ->FG_LIST_ADDING_BUTTON_ALT2 = $HD_Form ->FG_LIST_ADDING_BUTTON_MSG2 = gettext("Add CallerID");
$HD_Form ->FG_LIST_ADDING_BUTTON_IMG2 = Images_Path ."/page_white_add.png" ;

$HD_Form -> FG_OTHER_BUTTON3 = true;
$HD_Form -> FG_OTHER_BUTTON3_LINK = "A2B_entity_logrefill.php?form_action=ask-add&card_id=|col1|";
$HD_Form -> FG_OTHER_BUTTON3_IMG = Images_Path . "/brick_add.png";
$HD_Form -> FG_OTHER_BUTTON3_ALT = gettext('ADD REFILL');


$actived_list = getActivationTrueFalseList();
$yesno = getYesNoList();

$HD_Form -> AddViewElement(gettext("CALLERID"), "cid");
$HD_Form -> AddViewElement(gettext("ACCOUNT NUMBER"), "id_cc_card", true, 0, "", "lie_link", "cc_card", "username, id", "id='%id'", "%1", "A2B_entity_card.php");
$HD_Form -> AddViewElement(gettext("CUSTOMER NAME"), "id_cc_card", true, 0, "", "lie_link", "cc_card", "lastname, id, firstname", "id='%id'", "%1 %3", "A2B_entity_card.php");
$HD_Form -> AddViewElement(gettext("ACTIVATED"), "activated", true, 0, "", "list", $actived_list);

$HD_Form -> FieldViewElement ('cid, id_cc_card, id_cc_card, cc_callerid.activated, cc_callerid.id');

$HD_Form -> FG_LIST_VIEW_PAGE_SIZE = 10;

$HD_Form -> FG_ENABLE_ADD_BUTTON = true;
$HD_Form -> FG_ENABLE_EDIT_BUTTON = true;
$HD_Form -> FG_ENABLE_DELETE_BUTTON = true;

$HD_Form -> AddEditElement (gettext("CALLERID"),
    "cid",
    '$value',
    "",
    "",
    "INPUT",
    "size=20 maxlength=30",
    18, gettext("Insert the CallerID"), "", "", "", "", "", "", "");

$HD_Form->AddEditRadio(
    "activated",
    [[gettext("Yes"), "t"], [gettext("No"), "f"]],
    gettext("ACTIVATED"),
    "t",
    "",
    gettext("Choose if you want to activate this card")
);

$HD_Form -> AddEditElement (gettext("ID CARD"),
    "id_cc_card",
    "$value",
    gettext("Define the card number ID to use."),
    "",
    "POPUPVALUE",
    "size=30 maxlength=50",
    4, gettext("Insert the Id CardNumber"), "", "", "",
    ""
				, "",
    "A2B_entity_card.php?popup_select=1&", ", 'CardNumberSelection','width=550,height=330,top=20,left=100,scrollbars=1'");


$HD_Form -> FieldEditElement ('cid, activated, id_cc_card');


// Set the filter variables
$HD_Form -> FG_FILTER_ENABLE = true;
$HD_Form -> FG_FILTER_COLUMN = 'callerid';
$HD_Form -> FG_FILTER_LABEL = gettext('CALLERID');

if (isset($filterprefix)  &&  (strlen($filterprefix)>0)){
	if (strlen($HD_Form -> FG_QUERY_WHERE_CLAUSE)>0) $HD_Form -> FG_QUERY_WHERE_CLAUSE.=" AND ";
	$HD_Form -> FG_QUERY_WHERE_CLAUSE.="cid like '$filterprefix%'";
}

$HD_Form -> FG_OTHER_BUTTON2 = true;
$HD_Form -> FG_OTHER_BUTTON2_LINK="A2B_entity_payment.php?stitle=Payment_add&form_action=ask-add&card_id=|col1|";
$HD_Form -> FG_OTHER_BUTTON2_IMG = Images_Path . "/money.png";
$HD_Form -> FG_OTHER_BUTTON2_ALT = gettext('ADD PAYMENT');








$HD_Form -> FG_LOCATION_AFTER_ADD = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?atmenu=document&stitle=Document&wh=AC&id=";
$HD_Form -> FG_LOCATION_AFTER_EDIT = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?atmenu=document&stitle=Document&wh=AC&id=";
$HD_Form -> FG_LOCATION_AFTER_DELETE = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?atmenu=document&stitle=Document&wh=AC&id=";



