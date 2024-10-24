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

getpost_ifset(array('id', 'provider_name', 'creationdate', 'description', 'popup_select'));

$HD_Form = new FormHandler("cc_provider", "Provider");

$HD_Form->no_debug();

$HD_Form ->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form ->FG_LIST_ADDING_BUTTON_LINK1 = "A2B_entity_provider.php?form_action=ask-add";
$HD_Form ->FG_LIST_ADDING_BUTTON_ALT1 = $HD_Form ->FG_LIST_ADDING_BUTTON_MSG1 = gettext("Add Provider");
$HD_Form ->FG_LIST_ADDING_BUTTON_IMG1 = Images_Path ."/world_add.png" ;



if ($popup_select)
	$HD_Form -> AddViewElement(gettext("ID"), "id");
$HD_Form -> AddViewElement(gettext("PROVIDER NAME"), "provider_name");
$HD_Form -> AddViewElement(gettext("CREATIONDATE"), "creationdate", true, 19);
$HD_Form -> AddViewElement(gettext("DESCRIPTION"), "description");
$HD_Form -> FieldViewElement (($popup_select ? 'id,' : '') . 'provider_name, creationdate, description');

$HD_Form -> FG_VIEW_TABLE_WITDH = '70%';

if ($popup_select) {
	$HD_Form -> FG_LIST_VIEW_PAGE_SIZE = 7;
	$HD_Form -> CV_FOLLOWPARAMETERS = "&popup_select=" . $popup_select . "&popup_formname=" . $popup_formname . "&popup_fieldname=" . $popup_fieldname;

	$HD_Form -> FG_OTHER_BUTTON1 = true;
	$HD_Form -> FG_OTHER_BUTTON1_ALT = '<font color="red">&lt;select&gt;</font>';
	$HD_Form -> FG_OTHER_BUTTON1_IMG = '';

	//echo $popup_select;
	if ($popup_select == 1)
		$HD_Form -> FG_OTHER_BUTTON1_LINK = "javascript:sendValue('|param|');";
	elseif ($popup_select == 2)
		$HD_Form -> FG_OTHER_BUTTON1_LINK = "javascript:sendValue('|col0|');";
} else {
	$HD_Form -> FG_ENABLE_EDIT_BUTTON = true;
	$HD_Form -> FG_ENABLE_DELETE_BUTTON = true;
	$HD_Form -> FG_ENABLE_ADD_BUTTON = true;
}

$HD_Form->AddEditElement(
    gettext("PROVIDER NAME"),
    "provider_name",
    "",
    "size=40 maxlength=40",
    0,
    gettext("Insert the provider name")
);

$HD_Form->AddEditTextarea(
    gettext("DESCRIPTION"),
    "description",
    "",
    "cols='50' rows='5'", null, gettext("Insert the description")
);

$HD_Form -> FG_LOCATION_AFTER_ADD = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";
$HD_Form -> FG_LOCATION_AFTER_EDIT = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";
$HD_Form -> FG_LOCATION_AFTER_DELETE = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?id=";
