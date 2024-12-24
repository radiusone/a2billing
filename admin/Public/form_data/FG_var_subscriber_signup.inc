<?php

use A2billing\Admin;
use A2billing\Forms\FormHandler;
use A2billing\Forms\Validator;
use A2billing\Table;

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

$HD_Form = new FormHandler("cc_subscription_signup", _("Subscription in Signup"));

$HD_Form->FG_TABLE_DEFAULT_SENS = "DESC";

$HD_Form->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form->FG_LIST_ADDING_BUTTON_IMG1 = get_image_path("add.png");

$HD_Form->help_text = create_help(_("SIGNUP SUBSCRIBER - You can make create a list of subscribers that the customers can subscribe."));

$activated_list = getActivationList();

$HD_Form->AddListValue(_("Label"), "label");
$HD_Form->AddListMapping(_("Activity"), "enable", $activated_list);
$HD_Form->AddListValue(_("Description"), "description");
$HD_Form->AddListSqlMapping(_("Service"), "id_subscription", new Table("cc_subscription_service", ["id", "label"]));
$HD_Form->AddListSqlMapping(_("Call plan"), "id_callplan", new Table("cc_tariffgroup", ["id", "tariffgroupname"]));
$HD_Form->FieldViewElement (["label", "enable", "description", "id_subscription", "id_callplan"]);

$HD_Form->CV_NO_FIELDS  = _("NO SUBSCRIPTION IN SIGNUP CREATED!");

$HD_Form->FG_ENABLE_ADD_BUTTON = true;
$HD_Form->FG_ENABLE_EDIT_BUTTON = true;
$HD_Form->FG_ENABLE_DELETE_BUTTON = true;

$HD_Form->AddEditElement(
    _("LABEL"),
    "label",
    "",
    "size=30 maxlength=40",
    [Validator::class, "min3Chars"],
    _("Insert the provider name")
);

$HD_Form->AddEditRadio(
    _("ACTIVATED"),
    "enable",
    getYesNoList(),
    "1",
    "",
    "",
    _("Choose if you want to activate this subscriber in the signup")
);

$CURRENCY = strtoupper(BASE_CURRENCY);
$HD_Form->AddEditSqlSelect(
    _("SUBSCRIPTION OPTIONS"),
    "id_subscription",
    new Table("cc_subscription_service", ["id", "CONCAT(label, ' - ', fee, '$CURRENCY'"]),
    [],
    "",
    [-1 => _("NONE")]
);

$HD_Form->AddEditTextarea(
    _("DESCRIPTION"),
    "description",
    "",
    "cols='50' rows='4'",
    null,
    _("Insert the description")
);

$HD_Form->AddEditSqlSelect(
    _("CALL PLAN"),
    "id_callplan",
    new Table("cc_tariffgroup", ["id", "tariffgroupname"]),
    [],
    "",
    [],
    _("Define the callplan to associate to your Subscription")
);

$HD_Form->FG_LOCATION_AFTER_ADD = "?id=";
$HD_Form->FG_LOCATION_AFTER_EDIT = "?id=";
$HD_Form->FG_LOCATION_AFTER_DELETE = "?id=";
