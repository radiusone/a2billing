<?php

use A2billing\Admin;
use A2billing\Forms\FormHandler;
use A2billing\Forms\Validator;
use A2billing\Table;

/**
 * @var numeric-string $popup_select
 * @var string $popup_formname
 * @var string $popup_fieldname
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

$HD_Form = new FormHandler("cc_trunk", _("Trunk"), "id_trunk");

$HD_Form->list_query_order_columns = ["id_trunk"];
$HD_Form->update_query_conditions = ["id_trunk" => "%id"];

// Dependent Tables
$HD_Form->FG_FK_DELETE_ALLOWED = true;
$HD_Form->FG_FK_DELETE_CONFIRM = true;
$HD_Form->FG_FK_WARNONLY = true;
$HD_Form->FG_FK_TABLENAMES = ["cc_ratecard"];
$HD_Form->FG_FK_EDITION_CLAUSE = ["id_trunk"];
$HD_Form->delete_message_confirm_fk = _("You have some rates attached to this Trunk! Please comfirm that you really want to remove this Trunk? ");

$HD_Form->AddListTopButton("A2B_entity_trunk.php?form_action=ask-add", _("Add Trunk"), "hdd-network-fill");

$HD_Form->list_help_text = create_help(
    _("Trunk List.")
    . '<br/>'
    . _("Trunks can be modified by clicking the edit button")
);
$HD_Form->help_text = create_help(
    _("Trunks are used to terminate the call!<br>")
    . _("The trunk and ratecard is selected by the rating engine on the basis of the dialed digits.")
    . _("The trunk is used to dial out from your asterisk box which can be a zaptel interface or a voip provider.")
);

if ($popup_select) {
	$HD_Form->AddListValue(_("ID"), "id_trunk");
	$HD_Form->AddListValue(_("Label"), "trunkcode");
	$HD_Form->AddListSqlMapping(_("Provider"), "id_provider", new Table("cc_provider", ["id", "provider_name"]));
} else {
    $HD_Form->AddListValue(_("Label"), "trunkcode");
	$HD_Form->AddListValue(abbr(_("+"), _("Added prefix")), "trunkprefix");
	$HD_Form->AddListValue(abbr(_("-"), _("Removed prefix")), "removeprefix");
	$HD_Form->AddListValue(_("Tech"), "providertech");
	$HD_Form->AddListValue(_("Host"), "providerip");
    $HD_Form->AddListSqlMapping(_("Provider"), "id_provider", new Table("cc_provider", ["id", "provider_name"]));
	$HD_Form->AddListValue(abbr(_("Min"), _("Minutes used")), "secondusedreal", "get_minute");
	$HD_Form->AddListMapping(_("Status"), "status", getActivationList());
	$HD_Form->AddListValue(_("Max use"), "maxuse");
}

$HD_Form->search_form_enabled = true;
$HD_Form->search_form_title = _("Search");
$HD_Form->AddSearchTextInput(_("IP or Host"), 'providerip');
$HD_Form->AddSearchSqlSelectInput(_("Provider"), "id_provider", new Table("cc_provider", ["provider_name", "id"]));
$HD_Form->AddSearchSelectInput(_("Status"), "status", [_("Inactive"), _("Active")]);

if ($popup_select) {
	$HD_Form->FG_LIST_VIEW_PAGE_SIZE = 7;
    $HD_Form->AddListSelectActionButton("javascript:sendValue('|param|')");
} else {
	$HD_Form->FG_ENABLE_EDIT_BUTTON = true;
	$HD_Form->FG_ENABLE_DELETE_BUTTON = true;
	$HD_Form->FG_ENABLE_ADD_BUTTON = true;
}


$HD_Form->AddEditSqlSelect(
    _("VOIP PROVIDER"),
    "id_provider",
    new Table("cc_provider", ["provider_name", "id"]),
    [],
    "",
    [-1 => _("NOT DEFINED")]
);

$HD_Form->AddEditElement(
    _("LABEL"),
    "trunkcode",
    _("Unique and friendly name for the trunk"),
    ["maxlength" => 40],
    [Validator::class, "min4Chars"],
    _("Insert the trunkcode")
);

$HD_Form->AddEditElement(
    _("ADD PREFIX"),
    "trunkprefix",
    _("Add a prefix to the dialled digits."),
    ["maxlength" => 20],
    null,
    _("Insert the trunkprefix")
);

$HD_Form->AddEditElement(
    _("REMOVE PREFIX"),
    "removeprefix",
    _("Remove prefix from the dialed digits."),
    ["maxlength" => 20],
    null,
    _("Insert the removeprefix")
);

$HD_Form->AddEditElement(
    _("PROVIDER TECH"),
    "providertech",
    _("Technology used on the trunk (SIP,IAX2,ZAP,H323)"),
    ["maxlength" => 15],
    [Validator::class, "min3Chars"],
    _("Insert the providertech")
);

$HD_Form->AddEditElement(
    _("PROVIDER IP"),
    "providerip",
    _("Set the IP or URL of the VoIP provider. Alternatively, put in the name of a previously defined trunk in Asterisk or FreePBX. (MyVoiPTrunk, ZAP4G etc.) You can use the following tags to as variables: %dialingnumber%, %cardnumber%. ie g2/1644787890wwwwwwwwww%dialingnumber%"),
    ["maxlength" => 140],
    [Validator::class, "min1Char"],
    _("Insert the providerip")
);

$HD_Form->AddEditElement(
    _("ADDITIONAL PARAMETER"),
    "addparameter",
    _("Define any additional parameters that will be used when running the Dial Command in Asterisk. Use the following tags as variables  %dialingnumber%, %cardnumber%. ie 'D(ww%cardnumber%wwwwwwwwww%dialingnumber%)'"),
    ["maxlength" => 100],
    null,
    _("Insert Additional parameters")
);

$HD_Form->AddEditSqlSelect(
    _("FAILOVER TRUNK"),
    "failover_trunk",
    new Table("cc_trunk", ["trunkcode", "id_trunk"]),
    [],
    "",
    [-1 => _("NOT DEFINED")],
    _("You can define another trunk in case of failover!")
);

$inuse_attr = ["maxlength" => 30];
if ($form_action === "ask-add") {
    $inuse_attr["value"] = 0;
}
$HD_Form->AddEditElement(
    _("CURRENT CONNECTIONS"),
    "inuse",
    _("Updated to show the number of channels currently in use on this trunk.If there are no channels in use, and the system shows that there are, manually reset this field back to zero."),
    $inuse_attr,
    [Validator::class, "number"],
    _("Number of current connections"));

$maxuse_attr = ["maxlength" => 30];
if ($form_action === "ask-add") {
    $maxuse_attr["value"] = -1;
}
$HD_Form->AddEditElement(
    _("MAXIMUM CONNECTIONS"),
    "maxuse",
    _("The maximum number of channels available to this trunk. Set to -1 to have an unlimited number of channels"),
    $maxuse_attr,
    [Validator::class, "number"],
    _("Number of maximum simultaneous connections")
);

$HD_Form->AddEditSelect(
    _("IFMAXUSED"),
    "if_max_use",
    [_("Use failover trunk"), _("Use next trunk")],
    "",
    _("Specifies which trunk to use when the maximum number of connections is reached")
);

$HD_Form->AddEditSelect(
    _("STATUS"),
    "status",
    getActivationList(),
    "",
    _("Define if this trunk is active or not")
);

$HD_Form->add_message_intro = "";
