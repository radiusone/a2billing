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

/**
 * @var numeric-string $popup_select
 * @var string $popup_fieldname
 * @var string $popup_formname
 */

$HD_Form = new FormHandler("cc_provider", _("Provider"));

$HD_Form->AddListTopButton(null, null, "outlet");

$HD_Form->help_text = create_help(
    _("This section will allow you to create and edit VOIP Providers for reporting purposes. ")
    . _("A provider is the company/person that provides you with termination.")
);

$HD_Form->AddListValue(_("Name"), "provider_name");
$HD_Form->AddListValue(_("Creation date"), "creationdate");
$HD_Form->AddListValue(_("Description"), "description");

if ($popup_select) {
    $HD_Form->AddListSelectActionButton("javascript:sendValue('|param|')");
} else {
	$HD_Form->FG_ENABLE_EDIT_BUTTON = true;
	$HD_Form->FG_ENABLE_DELETE_BUTTON = true;
	$HD_Form->FG_ENABLE_ADD_BUTTON = true;
}

$HD_Form->AddEditElement(
    _("PROVIDER NAME"),
    "provider_name",
    "",
    ["maxlength" => 40],
    [Validator::class, "min3Chars"],
    _("Insert the provider name")
);

$HD_Form->AddEditTextarea(
    _("DESCRIPTION"),
    "description",
    "",
    ["rows" => 5],
    _("Insert the description")
);
