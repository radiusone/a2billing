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

Admin::checkPageAccess(Admin::ACX_ADMINISTRATOR);

$HD_Form = new FormHandler("cc_outbound_cid_group", _("Outbound CID Group"));

$HD_Form->FG_TABLE_DEFAULT_ORDER = "group_name";
$HD_Form->FG_TABLE_DEFAULT_SENS = "DESC";

$HD_Form ->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form ->FG_LIST_ADDING_BUTTON_IMG1 = get_image_path("server_connect.png") ;

// Code Here for Deleting the Dependent Records
// Dependent Tables
$HD_Form->FG_FK_DELETE_ALLOWED = true;
$HD_Form->FG_FK_DELETE_CONFIRM = true;
$HD_Form->FG_FK_WARNONLY = true;
$HD_Form->FG_FK_TABLENAMES = ["cc_outbound_cid_list"];
$HD_Form->FG_FK_EDITION_CLAUSE = ["outbound_cid_group"];
$HD_Form->FG_FK_DELETE_MESSAGE = _("You have some CID using this CID Group! Please comfirm that you really want to remove this CID Group ? ");

$HD_Form->list_help_text = create_help(_("CID Group list. CID can be chosen by customers through the customer interface."), 'ListCIDGroup');
$HD_Form->help_text = create_help(_("CID group offers customers a group of CID numbers which can be selected for a ratecard for outgoing calls"), 'EditCIDGroup');

$HD_Form->AddListValue(_("ID"), "id");
$HD_Form->AddListValue(_("DIDGROUP NAME"), "group_name");
$HD_Form->AddListValue(_("CREATION DATE"), "creationdate");
$HD_Form->FieldViewElement(["id", "group_name", "creationdate"]);

$HD_Form->FG_ENABLE_EDIT_BUTTON = true;
$HD_Form->FG_ENABLE_DELETE_BUTTON = true;
$HD_Form->FG_ENABLE_ADD_BUTTON = true;

$HD_Form->AddEditElement(
    _("CIDGROUPNAME"),
    "group_name",
    "",
    ["maxlength" => 70],
    [Validator::class, "min1Char"],
    _("Insert the CID Group Name ")
);

$HD_Form->FG_INTRO_TEXT_ADITION = "";

$HD_Form->FG_LOCATION_AFTER_ADD = "?id=";
$HD_Form->FG_LOCATION_AFTER_EDIT = "?id=";
$HD_Form->FG_LOCATION_AFTER_DELETE = "?id=";
