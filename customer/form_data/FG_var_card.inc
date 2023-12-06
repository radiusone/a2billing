<?php

use A2billing\Customer;
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

if (! has_rights (Customer::ACX_ACCESS)) {
    Header ("HTTP/1.0 401 Unauthorized");
    Header ("Location: PP_error.php?c=accessdenied");
    die();
}

getpost_ifset(array('id', 'lastname', 'firstname', 'email', 'address','city', 'state', 'country', 'zipcode','id_timezone', 'phone', 'fax','message', 'voicemail_activated'));


$HD_Form = new FormHandler("cc_card", "Card");

$HD_Form->no_debug();
$HD_Form -> FG_TABLE_DEFAULT_SENS = "ASC";
$HD_Form -> FG_EDIT_QUERY_CONDITION = " id = ".$_SESSION["card_id"];

$HD_Form -> FG_INTRO_TEXT_EDITION = '';

$HD_Form -> FG_ENABLE_EDIT_BUTTON = true;
$HD_Form -> FG_ENABLE_ADD_BUTTON = false;


$HD_Form->AddEditElement(
    gettext("LASTNAME"),
    "lastname",
    "",
    "size=30 maxlength=50",
    11,
    gettext("Insert the lastname of the customers"),
    "",
    "NO"
);

$HD_Form->AddEditElement(
    gettext("FIRSTNAME"),
    "firstname",
    "",
    "size=30 maxlength=50",
    11,
    gettext("Insert the firstname of the customers"),
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
    "NO"
);


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

$HD_Form->AddEditSqlSelect(
    "id_timezone",
    gettext("TIMEZONE"),
    "cc_timezone",
    "gmtzone,id"
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

if (ACT_VOICEMAIL) {
    $HD_Form->AddEditRadio(
        gettext("ACTIVE VOICEMAIL SERVICE"),
        "voicemail_activated",
        [[gettext("Yes"), "1"], [gettext("No"), "0"]],
        "0",
        gettext("Customer can enable the voicemail for this card."), "",
        gettext("Customer can choose to enable the voicemail for this card")
    );
}

$add_fields_voicemail = '';
if (ACT_VOICEMAIL)
	$add_fields_voicemail = ', voicemail_activated ';


$HD_Form -> FieldEditElement ('lastname, firstname, address, city, state, country, zipcode,id_timezone, phone, fax '.$add_fields_voicemail);

$HD_Form -> FG_INTRO_TEXT_ADITION = '';
$HD_Form -> FG_TEXT_ADITION_CONFIRMATION = gettext("Your record has been updated.")."<br>";

$HD_Form -> FG_LOCATION_AFTER_EDIT = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?message=success";



