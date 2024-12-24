<?php

use A2billing\Admin;
use A2billing\Forms\FormHandler;
use A2billing\Forms\Validator;

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
// TODO: what's this doing in the agent directory?
Admin::checkPageAccess(Admin::ACX_ADMINISTRATOR);

getpost_ifset(array('id', 'message', 'location', 'id_tariffgroup', 'options', 'credit',
'currency', 'locale', 'commission', 'vat', 'banner', 'language', 'perms', 'lastname', 'firstname', 'address', 'city', 'state',
'country', 'zipcode', 'phone', 'email', 'fax'));


$FG_INSTANCE_NAME = gettext("Agent");
$HD_Form = new FormHandler("cc_agent", $FG_INSTANCE_NAME);

$HD_Form->update_query_conditions = ["id" => $_SESSION["agent_id"]];

$language_list = getLanguages();

$yesno = getYesNoList();

$HD_Form -> FG_ENABLE_EDIT_BUTTON = true;

$HD_Form->AddEditElement(
    gettext("LAST NAME"),
    "lastname",
    "",
    ["maxlength" => 50],
    null,
    gettext("Insert the lastname")
);

$HD_Form->AddEditElement(
    gettext("FIRST NAME"),
    "firstname",
    "",
    ["maxlength" => 50],
    null,
    gettext("Insert the firstname")
);

$HD_Form->AddEditElement(
    gettext("ADDRESS"),
    "address",
    "",
    ["maxlength" => 250],
    null,
    gettext("Insert the address")
);

$HD_Form->AddEditElement(
    gettext("CITY"),
    "city",
    "",
    ["maxlength" => 20],
    null,
    gettext("Insert the city")
);

$HD_Form->AddEditElement(
    gettext("STATE"),
    "state",
    "",
    ["maxlength" => 40],
    null,
    gettext("Insert the state")
);

$HD_Form->AddEditElement(
    gettext("COUNTRY"),
    "country",
    "",
    ["maxlength" => 40],
    null,
    gettext("Insert the country")
);

$HD_Form->AddEditElement(
    gettext("ZIPCODE"),
    "zipcode",
    "",
    ["maxlength" => 7],
    null,
    gettext("Insert the zip code")
);

$HD_Form->AddEditElement(
    gettext("EMAIL"),
    "email",
    "",
    ["maxlength" => 70],
    [Validator::class, "email"],
    gettext("Insert the email"),
    "",
    "NO"
);

$HD_Form->AddEditElement(
    gettext("PHONE"),
    "phone",
    "",
    ["maxlength" => 20],
    null,
    gettext("Insert the telephone number")
);

$HD_Form->AddEditElement(
    gettext("FAX"),
    "fax",
    "",
    ["maxlength" => 20],
    null,
    gettext("Insert the fax number")
);


$HD_Form->AddEditElement(
    gettext("COMPANY"),
    "company",
    "",
    ["maxlength" => 50],
    null,
    gettext("Insert the company")
);

$HD_Form -> FG_INTRO_TEXT_EDITION= gettext("Modify your informations");
$HD_Form -> FG_LOCATION_AFTER_EDIT = "?message=success";
