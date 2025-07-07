<?php

use A2billing\Admin;
use A2billing\Forms\FormHandler;
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

/**
 * @var string $form_action
 */

$HD_Form = new FormHandler("cc_outbound_cid_list", _("CallerID"));

$HD_Form ->list_query_order_columns = ["cid"];
$HD_Form -> list_query_order_direction = "DESC";

$HD_Form->AddListTopButton(null, null, "telephone-outbound-fill");

$HD_Form->list_help_text = create_help(_("Outbound CID list. CID can be added by customers through the customer interface."));
$HD_Form->help_text = create_help(_("Outbound CID offers customers a number which will be selected randomly for a ratecard for outgoing calls"));

$HD_Form -> AddListValue(_("CID"), "cid");
$HD_Form -> AddListSqlMapping(_("CIDGROUP"), "outbound_cid_group", new Table("cc_outbound_cid_group", ["id", "group_name"]));
$HD_Form -> AddListMapping(_("STATUS"), "activated", getActivationList());

$HD_Form -> FG_ENABLE_ADD_BUTTON = true;
$HD_Form -> FG_ENABLE_EDIT_BUTTON = true;
$HD_Form -> FG_ENABLE_DELETE_BUTTON = true;
$HD_Form -> FG_SPLITABLE_FIELDS[] = "cid";

$HD_Form->AddEditTextarea(
    _("CID"),
    "cid",
    $form_action === "ask-add"
        ? _("Define the CallerIDs. You can define a range of CallerID. <br>80412340210-80412340218 would add all CID's between the range, whereas CIDs separated by a comma e.g. 80412340210,80412340212,80412340214 would only add the individual CID listed.")
        : _("Define the CallerIDs"),
    ["rows" => 4],
    _("Insert the CID")
);

$HD_Form->AddEditSqlSelect(
    _("CIDGROUP"),
    "outbound_cid_group",
    new Table("cc_outbound_cid_group", ["group_name", "id"])
);

$HD_Form->AddEditRadio(
    _("ACTIVATED"),
    "activated",
    getYesNoList(),
    "1",
    "",
    [],
    _("Choose if you want to activate this CallerID")
);

$HD_Form -> FG_INTRO_TEXT_EDITION = "";
$HD_Form -> FG_INTRO_TEXT_ADITION = "";
