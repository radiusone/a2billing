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


getpost_ifset(array('IDCust', 'id', 'id_cc_card', 'cid', 'activated'));


$HD_Form = new FormHandler("cc_callerid", gettext("CallerID"));

$HD_Form->no_debug();


$actived_list = getActivationTrueFalseList();
$yesno = getYesNoList();


$HD_Form -> AddViewElement(gettext("CALLERID"), "cid");
$HD_Form -> AddViewElement(gettext("ACTIVATED"), "activated");


$HD_Form -> FieldViewElement ('cid, activated');

$HD_Form -> CV_TITLE_TEXT = gettext("CallerID list");
$HD_Form -> FG_LIST_VIEW_PAGE_SIZE = 10;
$HD_Form -> FG_VIEW_TABLE_WITDH = '70%';

// My Code for Where Cluase
if (strlen($HD_Form -> FG_QUERY_WHERE_CLAUSE)>0)
    $HD_Form -> FG_QUERY_WHERE_CLAUSE.=" AND ";

$HD_Form -> FG_QUERY_WHERE_CLAUSE.="id_cc_card =".$_SESSION["card_id"];

if (strlen($HD_Form -> FG_EDIT_QUERY_CONDITION)>0)
    $HD_Form -> FG_EDIT_QUERY_CONDITION.=" AND ";

$HD_Form -> FG_EDIT_QUERY_CONDITION.="id_cc_card =".$_SESSION["card_id"];


$HD_Form -> FG_ENABLE_ADD_BUTTON = true;
$HD_Form -> FG_ENABLE_EDIT_BUTTON = true;
$HD_Form -> FG_ENABLE_DELETE_BUTTON = true;


$HD_Form->AddEditElement(
    gettext("CALLERID"),
    "cid",
    "",
    "size=20 maxlength=30",
    18,
    gettext("Insert the CallerID")
);

$HD_Form->AddEditRadio(
    "activated",
    [[gettext("Yes"), "t"], [gettext("No"), "f"]],
    gettext("ACTIVATED"),
    "t",
    "",
    gettext("Choose if you want to activate this card")
);

$HD_Form -> FieldEditElement ('cid, activated');

if (isset($filterprefix)  &&  (strlen($filterprefix)>0)) {
    if (strlen($HD_Form -> FG_QUERY_WHERE_CLAUSE)>0)
        $HD_Form -> FG_QUERY_WHERE_CLAUSE.=" AND ";

    $HD_Form -> FG_QUERY_WHERE_CLAUSE.="cid like '$filterprefix%'";
}

$HD_Form -> FG_OTHER_BUTTON1_LINK="A2B_entity_card?form_action=ask-edit&id=";
$HD_Form -> FG_OTHER_BUTTON1_IMG = Images_Path."/icon-card.png";

$HD_Form -> FG_INTRO_TEXT_ADITION = gettext("You can add your home phone or cellphone number below to allow you to call in, be automatically recognized, and use your telephony services without having to enter your PIN.");

$HD_Form -> FG_LOCATION_AFTER_ADD = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?atmenu=document&id=";
$HD_Form -> FG_LOCATION_AFTER_EDIT = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?atmenu=document&id=";
$HD_Form -> FG_LOCATION_AFTER_DELETE = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?atmenu=document&id=";



