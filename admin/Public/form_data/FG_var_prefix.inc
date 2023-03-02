<?php

use A2billing\A2Billing;
use A2billing\Admin;
use A2billing\Forms\FormHandler;

/**
 * @var A2Billing $A2B
 * @var string $form_action
 */

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

if (! has_rights (Admin::ACX_ADMINISTRATOR)) {
    header("HTTP/1.0 401 Unauthorized");
    header("Location: PP_error.php?c=accessdenied");
    die();
}

getpost_ifset(['id', 'destination','prefix','popup_select','popup_formname','popup_fieldname']);
/**
 * @var string $id
 * @var string $destination
 * @var string $prefix
 * @var string $popup_select
 * @var string $popup_formname
 * @var string $popup_fieldname
 */

$HD_Form = new FormHandler("cc_prefix", "prefix", "prefix");

$HD_Form->no_debug();
$HD_Form -> FG_TABLE_DEFAULT_ORDER = "destination";

$HD_Form -> FG_LIST_ADDING_BUTTON1 = true;
$HD_Form -> FG_LIST_ADDING_BUTTON_LINK1 = "A2B_entity_prefix.php?form_action=ask-add";
$HD_Form ->FG_LIST_ADDING_BUTTON_MSG1 = _("Add PREFIX");
$HD_Form -> FG_LIST_ADDING_BUTTON_IMG1 = Images_Path ."/world_add.png" ;

$HD_Form -> AddViewElement(_("Prefix"), "prefix");
$HD_Form -> AddViewElement(_("Destination"), "destination");
$HD_Form -> FieldViewElement ('prefix, destination');

if ($popup_select) {
	$HD_Form -> FG_LIST_VIEW_PAGE_SIZE = 7;
    $HD_Form -> CV_FOLLOWPARAMETERS = http_build_query(compact("popup_select", "popup_formname", "popup_fieldname"), "", "&amp;");
	$HD_Form -> FG_OTHER_BUTTON1 = true;
	$HD_Form -> FG_OTHER_BUTTON1_ALT = '<font color="red">&lt;select&gt;</font>';
	$HD_Form -> FG_OTHER_BUTTON1_IMG = '';
    $HD_Form->FG_OTHER_BUTTON1_LINK = $popup_select === "1" ? "javascript:sendValue('|param|');" : "javascript:sendValue('|col0|');";
} else {
	$HD_Form -> FG_ENABLE_EDIT_BUTTON = true;
	$HD_Form -> FG_ENABLE_DELETE_BUTTON = true;
	$HD_Form -> FG_ENABLE_ADD_BUTTON = true;
}

$HD_Form->AddEditElement(
    _("PREFIX"),
    "prefix",
    "",
    "size=30 maxlength=300",
    null,
    _("Insert the Prefixe")
);

$HD_Form->AddEditElement(
    _("DESTINATION"),
    "destination",
    "",
    "size=30 maxlength=300",
    null,
    _("Insert the Destination")
);

$HD_Form -> FieldEditElement ('prefix, destination');

$HD_Form -> FG_EDIT_QUERY_CONDITION = " prefix='%id'";

$HD_Form -> FG_FILTER_ENABLE = true;
$HD_Form -> FG_FILTER_COLUMN = 'prefix';
$HD_Form -> FG_FILTER_LABEL = 'prefix';

$HD_Form -> FG_INTRO_TEXT_EDITION= '';
$HD_Form -> FG_INTRO_TEXT_ADITION = '';
$HD_Form -> FG_LOCATION_AFTER_ADD = "?id=";
$HD_Form -> FG_LOCATION_AFTER_EDIT = "?id=";
$HD_Form -> FG_LOCATION_AFTER_DELETE = "?id=";
