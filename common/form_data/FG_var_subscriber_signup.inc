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

$HD_Form = new FormHandler("cc_subscription_signup", _("Subscription in Signup"));

$HD_Form->list_query_order_direction = "DESC";

$HD_Form->AddListTopButton(null, null, "box-seam");

$HD_Form->help_text = create_help(_("SIGNUP SUBSCRIBER - You can make create a list of subscribers that the customers can subscribe."));

$activated_list = getActivationList();

$HD_Form->AddListValue(_("Label"), "label");
$HD_Form->AddListMapping(_("Activity"), "enable", $activated_list);
$HD_Form->AddListValue(_("Description"), "description");
$HD_Form->AddListSqlMapping(_("Service"), "id_subscription", new Table("cc_subscription_service", ["id", "label"]));
$HD_Form->AddListSqlMapping(_("Call plan"), "id_callplan", new Table("cc_tariffgroup", ["id", "tariffgroupname"]));

$HD_Form->list_message_empty  = _("NO SUBSCRIPTION IN SIGNUP CREATED!");

$HD_Form->FG_ENABLE_ADD_BUTTON = true;
$HD_Form->FG_ENABLE_EDIT_BUTTON = true;
$HD_Form->FG_ENABLE_DELETE_BUTTON = true;

$HD_Form->AddEditElement(
    _("LABEL"),
    "label",
    "",
    ["maxlength" => 40],
    [Validator::class, "min3Chars"],
    _("Insert the provider name")
);

$HD_Form->AddEditRadio(
    _("ACTIVATED"),
    "enable",
    getYesNoList(),
    "1",
    "",
    [],
    _("Choose if you want to activate this subscriber in the signup")
);

$CURRENCY = strtoupper(BASE_CURRENCY);
$HD_Form->AddEditSqlSelect(
    _("SUBSCRIPTION OPTIONS"),
    "id_subscription",
    new Table("cc_subscription_service", ["CONCAT(label, ' - ', fee, '$CURRENCY'", "id"]),
    [],
    "",
    [-1 => _("NONE")]
);

$HD_Form->AddEditTextarea(
    _("DESCRIPTION"),
    "description",
    "",
    ["rows" => 4],
    _("Insert the description")
);

$HD_Form->AddEditSqlSelect(
    _("CALL PLAN"),
    "id_callplan",
    new Table("cc_tariffgroup", ["tariffgroupname", "id"]),
    [],
    "",
    [],
    _("Define the callplan to associate to your Subscription")
);
