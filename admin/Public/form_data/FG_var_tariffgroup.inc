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

/**
 * @var numeric-string|null $popup_select
 * @var string $popup_formname
 * @var string $popup_fieldname
 */

Admin::checkPageAccess(Admin::ACX_ADMINISTRATOR);

$HD_Form = new FormHandler("cc_tariffgroup", _("Call Plan"));

$HD_Form->FG_TABLE_DEFAULT_ORDER = "tariffgroupname";

$HD_Form->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form->FG_LIST_ADDING_BUTTON_IMG1 = get_image_path("server_add.png") ;

$HD_Form->list_help_text = create_help(
    _("List of Call Plans, a Call Plan is a collection of ratecards. You can click on edit to add new ratecards to the Call Plan")
);

$HD_Form->FG_FK_DELETE_ALLOWED = true;
$HD_Form->FG_FK_DELETE_CONFIRM = true;
$HD_Form->FG_FK_WARNONLY = true;
$HD_Form->FG_FK_TABLENAMES = ["cc_card"];
$HD_Form->FG_FK_EDITION_CLAUSE = ["tariff"];
$HD_Form->FG_FK_DELETE_MESSAGE = _("You have some cards using this Call Plan! Please comfirm that you really want to remove this Call Plan ? ");

// LCD :the cheapest Provider for a given destination number
$lcrtype_list = [_("LCR : According to the buyer price"), _("LCD : According to the seller price")];
$lcrtype_list_short = [_("LCR : buyer price"), _("LCD : seller price")];
$inter_prefix_list_short = [_("Keep prefix"), _("Remove prefix")];

$HD_Form->AddListValue(_("ID"), "id");
$HD_Form->AddListValue(_("Name"), "tariffgroupname");
if (!$popup_select) {
    $HD_Form->AddListValue(_("Creation date"), "creationdate");
}
$HD_Form->AddListMapping(abbr(_("LCR"), _("Least cost routing type")), "lcrtype", $lcrtype_list_short);
$HD_Form->AddListSqlMapping(_("Package"), "id_cc_package_offer", new Table("cc_package_offer", ["id", "label"]));
$HD_Form->AddListMapping(_("Prefix"), "removeinterprefix", $inter_prefix_list_short);
if ($popup_select) {
	$HD_Form->FG_LIST_VIEW_PAGE_SIZE = 7;

    $HD_Form->AddListSelectActionButton("javascript:sendValue('|param|')");
} else {
	$HD_Form->FG_ENABLE_EDIT_BUTTON = true;
	$HD_Form->FG_ENABLE_DELETE_BUTTON = true;
	$HD_Form->FG_ENABLE_ADD_BUTTON = true;
}

$HD_Form->AddEditElement(
    _("NAME"),
    "tariffgroupname",
    "",
    ["maxlength" => 20],
    [Validator::class, "min1Char"],
    _("Insert the Call Plan name ")
);

$HD_Form->AddEditSelect(_("LC TYPE"), "lcrtype", $lcrtype_list);

$HD_Form->AddEditSqlSelect(
    _("PACKAGE"),
    "id_cc_package_offer",
    new Table("cc_package_offer", ["id", "label"]),
    [],
    "",
    [-1 => _("NOT DEFINED")],
    _("Set the Package Group offer if you wish to use one with this Call Plan")
);

$HD_Form->AddEditRadio(
    _("REMOVE INTER PREFIX"),
    "removeinterprefix",
    getYesNoList(),
    "0",
    _("Remove the international access prefix (00 or 011) before matching the dialled digits with the rate card. E.G. If the dialled digits were 0044 for a call to the UK, only 44 would be delivered."),
    [],
    _("Define if the international prefix will be removed to check the rate.")
);

$numrow = (new Table("cc_tariffplan"))->countRows($HD_Form->DBHandle);
if ($numrow > 0) {
    $HD_Form->AddEditHasMany(
        _("Ratecard"),
        new Table(
            "cc_tariffplan",
            ["cc_tariffplan.id", "cc_tariffplan.tariffname"],
            ["cc_tariffgroup_plan" => ["cc_tariffplan.id", "cc_tariffgroup_plan.idtariffplan"]]
        ),
        "idtariffplan",
        "idtariffgroup",
        "",
        null,
        false,
        true,
        new Table("cc_tariffgroup_plan")
    );
}

$HD_Form->FG_INTRO_TEXT_EDITION = "";
$HD_Form->FG_INTRO_TEXT_ADITION = "";

$HD_Form->FG_LOCATION_AFTER_ADD = "?id=";
$HD_Form->FG_LOCATION_AFTER_EDIT = "?id=";
$HD_Form->FG_LOCATION_AFTER_DELETE = "?id=";
