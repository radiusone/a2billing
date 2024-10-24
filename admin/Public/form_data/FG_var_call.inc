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

getpost_ifset(array('id', 'calledstation', 'id_cc_card'));

$HD_Form = new FormHandler("cc_call", "Call");

$HD_Form->no_debug();


$HD_Form -> AddViewElement(gettext("ID"), "id");
$HD_Form -> AddViewElement(gettext("CALLEDSTATION"), "calledstation");
$HD_Form -> AddViewElement(gettext("ACCOUNT NUMBER"), "card_id", true, 0, "", "lie_link", "cc_card", "username,id", "id='%id'", "%1", "A2B_entity_card.php");

$HD_Form -> FieldViewElement ('id, calledstation, card_id, id');

$HD_Form -> FG_LIST_VIEW_PAGE_SIZE = 10;

$HD_Form -> FG_ENABLE_ADD_BUTTON = false;
$HD_Form -> FG_ENABLE_EDIT_BUTTON = false;
$HD_Form -> FG_ENABLE_DELETE_BUTTON = true;

$HD_Form->AddEditElement(
    gettext("CALL ID"),
    "id",
    "",
    "size=10 maxlength=10",
    18
);

$HD_Form->AddEditPopup(
    gettext("CARD ID"),
    "card_id",
    "A2B_entity_card.php?popup_select=1&",
    gettext("Define the card number ID to use."),
    "", 4, gettext("Insert the Id CardNumber"),
);

$HD_Form->AddEditElement(
    gettext("CALLED NUMBER"),
    "calledstation",
    "",
    "size=40 maxlength=30",
    18
);


$HD_Form->AddEditElement(
    gettext("Source"),
    "src",
    "",
    "size=40 maxlength=30",
    18
);

$HD_Form->AddEditElement(
    gettext("Dnid"),
    "dnid",
    "",
    "size=40 maxlength=30",
    18
);

$HD_Form->AddEditElement(
    gettext("Duration"),
    "sessiontime",
    "",
    "size=40 maxlength=30",
    18
);

// Set the filter variables
$HD_Form -> FG_FILTER_ENABLE = true;
$HD_Form -> FG_FILTER_COLUMN = 'id';
$HD_Form -> FG_FILTER_LABEL = 'call ID';

$HD_Form -> FG_LOCATION_AFTER_DELETE = 'call-log-customers.php?nodisplay=1&posted=1';
$HD_Form -> FG_LOCATION_AFTER_ADD = $HD_Form -> FG_LOCATION_AFTER_DELETE;
$HD_Form -> FG_LOCATION_AFTER_EDIT = $HD_Form -> FG_LOCATION_AFTER_DELETE;
