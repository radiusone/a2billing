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


getpost_ifset(array('id', 'label ','id_subscription','enable'));


$HD_Form = new FormHandler("cc_subscription_signup", "Subscription in Signup");

$HD_Form->no_debug();


$HD_Form -> FG_TABLE_DEFAULT_ORDER = "id";
$HD_Form -> FG_TABLE_DEFAULT_SENS = "DESC";



$HD_Form ->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form ->FG_LIST_ADDING_BUTTON_LINK1 = "A2B_entity_subscriber_signup.php?form_action=ask-add&section=".$_SESSION["menu_section"];
$HD_Form ->FG_LIST_ADDING_BUTTON_ALT1 = $HD_Form ->FG_LIST_ADDING_BUTTON_MSG1 = gettext("Add New Subscription in Signup");
$HD_Form ->FG_LIST_ADDING_BUTTON_IMG1 = Images_Path ."/add.png" ;


$activated_list = getActivationList();

$HD_Form -> AddViewElement(gettext("LABEL"), "label");
$HD_Form -> AddViewElement(gettext("ACTIVITY"), "enable", true, 0, "", "list", $activated_list);
$HD_Form -> AddViewElement(gettext("DESCRIPTION"), "description", true, 150);
$HD_Form -> AddViewElement(gettext("SERVICE"), "id_subscription", true, 15, "", "lie", "cc_subscription_service", "label", "id='%id'", "%1");
$HD_Form -> AddViewElement("<acronym title=\"" . gettext("CALL PLAN") . "\">" . gettext('PLAN') . "</acronym>", "id_callplan", true, 20, "", "lie", "cc_tariffgroup", "tariffgroupname", "id='%id'", "%1");
$HD_Form -> FieldViewElement ( 'label, enable, description, id_subscription, id_callplan');

$HD_Form -> CV_NO_FIELDS  = gettext("NO SUBSCRIPTION IN SIGNUP CREATED!");

$HD_Form -> FG_ENABLE_ADD_BUTTON = true;
$HD_Form -> FG_ENABLE_EDIT_BUTTON = true;
$HD_Form -> FG_ENABLE_DELETE_BUTTON = true;

$HD_Form->AddEditElement(
    gettext("LABEL"),
    "label",
    "",
    "size=30 maxlength=40",
    0,
    gettext("Insert the provider name")
);

$HD_Form->AddEditRadio(
    "enable",
    [[gettext("Yes"), "1"], [gettext("No"), "0"]],
    gettext("ACTIVATED"),
    "1",
    "",
    gettext("Choose if you want to activate this subscriber in the signup")
);

$CURRENCY = strtoupper(BASE_CURRENCY);
$HD_Form->AddEditSqlSelect(
    "id_subscription",
    gettext("SUBSCRIPTION OPTIONS"),
    "cc_subscription_service",
    "label,id,fee",
    "",
    "",
    '<option value="-1" selected="selected">' . gettext("NONE") . '</option>',
    "",
    "%1 - %3 $CURRENCY (id:%2)"
);

$HD_Form->AddEditTextarea(
    "description",
    gettext("DESCRIPTION"),
    "cols='50' rows='4'",
    gettext("Insert the description")
);

$HD_Form->AddEditSqlSelect(
    "id_callplan",
    gettext("CALL PLAN"),
    "cc_tariffgroup",
    "tariffgroupname,id",
    "",
    "",
    "",
    gettext("Define the callplan to associate to your Subscription")
);

$HD_Form -> FieldEditElement ('label,enable,id_subscription, description, id_callplan');


$HD_Form -> FG_LOCATION_AFTER_ADD = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?atmenu=document&stitle=Document&wh=AC&id=";
$HD_Form -> FG_LOCATION_AFTER_EDIT = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?atmenu=document&stitle=Document&wh=AC&id=";
$HD_Form -> FG_LOCATION_AFTER_DELETE = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?atmenu=document&stitle=Document&wh=AC&id=";

