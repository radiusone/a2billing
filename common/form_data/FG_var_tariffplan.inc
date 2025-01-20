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

$form_action ??= "list";

$HD_Form = new FormHandler("cc_tariffplan", _("Ratecard"));

$HD_Form->list_query_order_columns = ["tariffname"];

$HD_Form->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form->FG_LIST_ADDING_BUTTON_IMG1 = get_image_path("table_multiple.png") ;

$HD_Form->FG_FK_DELETE_ALLOWED = true;
$HD_Form->FG_FK_DELETE_CONFIRM = true;
$HD_Form->FG_FK_TABLENAMES = ["cc_ratecard"];
$HD_Form->FG_FK_EDITION_CLAUSE = ["idtariffplan"];
$HD_Form->FG_FK_DELETE_MESSAGE = _("You are going as well to remove all the rates attached to this ratecard! Please comfirm that you really want to remove this ratecard and those rates ? ");

$HD_Form->add_help_text = $HD_Form->edit_help_text = create_help(
    _("A ratecard is a set of rates defined and applied according to the dialling prefix, for instance 441 & 442 : UK Landline.")
    . '<br/>'
    . _("Each ratecard may have as many rates as you wish, however, if a dialling prefix cannot be matched when a call is made, then the call will be terminated.")
    . '<br/>'
    . _('A ratecard has a "start date", an "expiry date" and a you can define a default trunk, but if no trunk is defined, the ratecard default trunk will be used.')
);
$HD_Form->list_help_text = create_help(
    _("List ratecards that have been created!<br>Ensure that a ratecard is added into the call plan under 'List Ratecard'")
);

$HD_Form->AddListValue(_("ID"), "id");
$HD_Form->AddListValue(_("Name"), "tariffname");
$HD_Form->AddListValue(_("Start date"), "startingdate");
$HD_Form->AddListValue(_("Expiry date"), "expirationdate");
$HD_Form->AddListSqlMapping(_("Trunk"), "id_trunk", new Table("cc_trunk", ["id_trunk", "trunkcode"]));
$HD_Form->AddListValue(_("Minutes used"), "secondusedreal", "get_minute");
$HD_Form->AddListValue(_("DNID prefix"), "dnidprefix");
$HD_Form->AddListValue(_("CID prefix"), "calleridprefix");

$HD_Form->FG_ENABLE_EDIT_BUTTON = true;
$HD_Form->FG_ENABLE_DELETE_BUTTON = true;
$HD_Form->FG_ENABLE_ADD_BUTTON = true;

$HD_Form->AddEditElement(
    _("TARIFFNAME"),
    "tariffname",
    "",
    ["maxlength" => 30],
    [Validator::class, "min4Chars"],
    _("Insert the tariff name ")
);

$date_attr = ["maxlength" => 40];
if ($form_action === "ask-add") {
    $date_attr["value"] = (new DateTime())->format("Y-m-d H:i:s");
}
$HD_Form->AddEditElement(
    _("START DATE"),
    "startingdate",
    _("Please use the format YYYY-MM-DD HH:MM:SS. For instance, '2004-12-31 00:00:00'"),
    $date_attr,
    [Validator::class, "dateTime"],
    _("Insert the starting date")
);

if ($form_action === "ask-add") {
    $date_attr["value"] = (new DateTime("+10 years"))->format("Y-m-d H:i:s");
}
$HD_Form->AddEditElement(
    _("EXPIRY DATE"),
    "expirationdate",
    _("Format YYYY-MM-DD HH:MM:SS. For instance, '2004-12-31 00:00:00'"),
    $date_attr,
    [Validator::class, "dateTime"],
    _("Insert the expiration date")
);

$HD_Form->AddEditSqlSelect(
    _("TRUNK"),
    "id_trunk",
    new Table("cc_trunk", ["id_trunk", "trunkcode"])
);

$HD_Form->AddEditTextarea(
    _("DESCRIPTION"),
    "description",
    "",
    ["rows" => 4],
    _("Insert the description")
);

$id_attr = ["maxlength" => 20];
if ($form_action === "ask-add") {
    $id_attr["value"] = "all";
}
$HD_Form->AddEditElement(
    _("DNID PREFIX"),
    "dnidprefix",
    _("Set the DNID rules to choose the ratecard 'dnidprefix', by default, matches all DNID. For instance, Set the DNIDPrefix  to 900540540 to choose this ratecard when the DNID is 900540540"),
    $id_attr,
    [Validator::class, "numericOrAll"],
    _("Insert the dnid prefix")
);

$HD_Form->AddEditElement(
    _("CALLERID PREFIX"),
    "calleridprefix",
    _("Set the CallerID rules to choose the ratecard 'calleridprefix', by default, matches all callerID. For instance, Set the calleridprefix to 900540540 to choose this ratecard when the CallerID is 900540540."),
    $id_attr,
    [Validator::class, "numericOrAll"],
    _("Insert the dnid prefix")
);

$HD_Form->FG_INTRO_TEXT_ADITION = "";
