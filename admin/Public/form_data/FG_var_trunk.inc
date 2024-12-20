<?php

use A2billing\Admin;
use A2billing\Forms\FormHandler;
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

Admin::checkPageAccess(Admin::ACX_ADMINISTRATOR);

$DBHandle  = DbConnect();

$HD_Form = new FormHandler("cc_trunk", _("Trunk"), "id_trunk");

$HD_Form->FG_TABLE_DEFAULT_ORDER = " id_trunk";
$HD_Form->update_query_conditions = ["id_trunk" => "%id"];
$HD_Form->FG_TABLE_DEFAULT_SENS = "ASC";

// Dependent Tables
$HD_Form->FG_FK_DELETE_ALLOWED = true;
$HD_Form->FG_FK_DELETE_CONFIRM = true;
$HD_Form->FG_FK_WARNONLY = true;
$HD_Form->FG_FK_TABLENAMES = ["cc_ratecard"];
$HD_Form->FG_FK_EDITION_CLAUSE = ["id_trunk"];
$HD_Form->FG_FK_DELETE_MESSAGE = _("You have some rates attached to this Trunk! Please comfirm that you really want to remove this Trunk? ");

$HD_Form->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form->FG_LIST_ADDING_BUTTON_LINK1 = "A2B_entity_trunk.php?form_action=ask-add";
$HD_Form->FG_LIST_ADDING_BUTTON_ALT1 = $HD_Form->FG_LIST_ADDING_BUTTON_MSG1 = _("Add Trunk");
$HD_Form->FG_LIST_ADDING_BUTTON_IMG1 = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAJvSURBVDjLpZPrS5NhGIf9W7YvBYOkhlkoqCklWChv2WyKik7blnNris72bi6dus0DLZ0TDxW1odtopDs4D8MDZuLU0kXq61CijSIIasOvv94VTUfLiB74fXngup7nvrnvJABJ/5PfLnTTdcwOj4RsdYmo5glBWP6iOtzwvIKSWstI0Wgx80SBblpKtE9KQs/We7EaWoT/8wbWP61gMmCH0lMDvokT4j25TiQU/ITFkek9Ow6+7WH2gwsmahCPdwyw75uw9HEO2gUZSkfyI9zBPCJOoJ2SMmg46N61YO/rNoa39Xi41oFuXysMfh36/Fp0b7bAfWAH6RGi0HglWNCbzYgJaFjRv6zGuy+b9It96N3SQvNKiV9HvSaDfFEIxXItnPs23BzJQd6DDEVM0OKsoVwBG/1VMzpXVWhbkUM2K4oJBDYuGmbKIJ0qxsAbHfRLzbjcnUbFBIpx/qH3vQv9b3U03IQ/HfFkERTzfFj8w8jSpR7GBE123uFEYAzaDRIqX/2JAtJbDat/COkd7CNBva2cMvq0MGxp0PRSCPF8BXjWG3FgNHc9XPT71Ojy3sMFdfJRCeKxEsVtKwFHwALZfCUk3tIfNR8XiJwc1LmL4dg141JPKtj3WUdNFJqLGFVPC4OkR4BxajTWsChY64wmCnMxsWPCHcutKBxMVp5mxA1S+aMComToaqTRUQknLTH62kHOVEE+VQnjahscNCy0cMBWsSI0TCQcZc5ALkEYckL5A5noWSBhfm2AecMAjbcRWV0pUTh0HE64TNf0mczcnnQyu/MilaFJCae1nw2fbz1DnVOxyGTlKeZft/Ff8x1BRssfACjTwQAAAABJRU5ErkJggg==";

$HD_Form->list_help_text = create_help(
    _("Trunk List.")
    . '<br/>'
    . _("Trunks can be modified by clicking the edit button"),
    'ListTrunk'
);
$HD_Form->help_text = create_help(
    _("Trunks are used to terminate the call!<br>")
    . _("The trunk and ratecard is selected by the rating engine on the basis of the dialed digits.")
    . _("The trunk is used to dial out from your asterisk box which can be a zaptel interface or a voip provider."),
    'EditTrunk'
);

if ($popup_select) {
	$HD_Form->AddListValue(_("ID"), "id_trunk");
	$HD_Form->AddListValue(_("Label"), "trunkcode");
	$HD_Form->AddListSqlMapping(_("Provider"), "id_provider", new Table("cc_provider", ["id", "provider_name"]));
	$HD_Form->FieldViewElement(["id_trunk", "trunkcode", "id_provider"]);
} else {
    $HD_Form->AddListValue(_("Label"), "trunkcode");
	$HD_Form->AddListValue(abbr(_("+"), _("Added prefix")), "trunkprefix");
	$HD_Form->AddListValue(abbr(_("-"), _("Removed prefix")), "removeprefix");
	$HD_Form->AddListValue(_("Tech"), "providertech");
	$HD_Form->AddListValue(_("Host"), "providerip");
    $HD_Form->AddListSqlMapping(_("Provider"), "id_provider", new Table("cc_provider", ["id", "provider_name"]));
	$HD_Form->AddListValue(abbr(_("Min"), _("Minutes used")), "secondusedreal", "display_minute");
	$HD_Form->AddListMapping(_("Status"), "status", getActivationList());
	$HD_Form->AddListValue(_("Max use"), "maxuse");
	$HD_Form->FieldViewElement([
        "trunkcode",
        "trunkprefix",
        "removeprefix",
        "providertech",
        "providerip",
        "id_provider",
        "secondusedreal",
        "status",
        "maxuse",
    ]);
}

$HD_Form->search_form_enabled = true;
$HD_Form->search_form_title = _("Search");
$HD_Form->AddSearchTextInput(_("IP or Host"), 'providerip');
$HD_Form->AddSearchSqlSelectInput(_("Provider"), "cc_provider", "id,provider_name", "", "provider_name", "ASC", "id_provider");
$HD_Form->AddSearchSelectInput(_("Status"), "status", [[0, _("Inactive")], [1, _("Active")]]);

if ($popup_select) {
	$HD_Form->FG_LIST_VIEW_PAGE_SIZE = 7;
	$HD_Form->CV_FOLLOWPARAMETERS = "&popup_select=$popup_select&popup_formname=$popup_formname&popup_fieldname=$popup_fieldname";
    $HD_Form->AddListSelectActionButton("javascript:sendValue('|param|')");
} else {
	$HD_Form->FG_ENABLE_EDIT_BUTTON = true;
	$HD_Form->FG_ENABLE_DELETE_BUTTON = true;
	$HD_Form->FG_ENABLE_ADD_BUTTON = true;
}

$def_value_inuse = "";
$def_value_maxuse = "";
if (isset($form_action) && $form_action === "ask-add") {
	$def_value_inuse = "value='0'";
	$def_value_maxuse = "value='-1'";
}

$HD_Form->AddEditSqlSelect(
    _("VOIP PROVIDER"),
    "id_provider",
    "cc_provider",
    "provider_name,id",
    "",
    "",
    [-1, _("NOT DEFINED")]
);

$HD_Form->AddEditElement(
    _("LABEL"),
    "trunkcode",
    _("Unique and friendly name for the trunk"),
    "size=45 maxlength=40",
    3, // [Validator::class, "min4Chars"],
    _("Insert the trunkcode")
);

$HD_Form->AddEditElement(
    _("ADD PREFIX"),
    "trunkprefix",
    _("Add a prefix to the dialled digits."),
    "size=30 maxlength=20",
    null,
    _("Insert the trunkprefix")
);

$HD_Form->AddEditElement(
    _("REMOVE PREFIX"),
    "removeprefix",
    _("Remove prefix from the dialed digits."),
    "size=30 maxlength=20",
    null,
    _("Insert the removeprefix")
);

$HD_Form->AddEditElement(
    _("PROVIDER TECH"),
    "providertech",
    _("Technology used on the trunk (SIP,IAX2,ZAP,H323)"),
    "size=20 maxlength=15",
    0, // [Validator::class, "min3Chars"],
    _("Insert the providertech")
);

$HD_Form->AddEditElement(
    _("PROVIDER IP"),
    "providerip",
    _("Set the IP or URL of the VoIP provider. Alternatively, put in the name of a previously defined trunk in Asterisk or FreePBX. (MyVoiPTrunk, ZAP4G etc.) You can use the following tags to as variables: %dialingnumber%, %cardnumber%. ie g2/1644787890wwwwwwwwww%dialingnumber%"),
    "size=80 maxlength=140",
    9, // [Validator::class, "min1Char"],
    _("Insert the providerip")
);

$HD_Form->AddEditElement(
    _("ADDITIONAL PARAMETER"),
    "addparameter",
    _("Define any additional parameters that will be used when running the Dial Command in Asterisk. Use the following tags as variables  %dialingnumber%, %cardnumber%. ie 'D(ww%cardnumber%wwwwwwwwww%dialingnumber%)'"),
    "size=60 maxlength=100",
    null,
    _("Insert Additional parameters")
);

$HD_Form->AddEditSqlSelect(
    _("FAILOVER TRUNK"),
    "failover_trunk",
    "cc_trunk",
    "trunkcode,id_trunk",
    "",
    "",
    [-1, _("NOT DEFINED")],
    "%1",
    _("You can define another trunk in case of failover!")
);

$HD_Form->AddEditElement(
    _("CURRENT CONNECTIONS"),
    "inuse",
    _("Updated to show the number of channels currently in use on this trunk.If there are no channels in use, and the system shows that there are, manually reset this field back to zero."),
    "size=30 $def_value_inuse maxlength=30",
    12, // [Validator::class, "number"],
    _("Number of current connections"));

$HD_Form->AddEditElement(
    _("MAXIMUM CONNECTIONS"),
    "maxuse",
    _("The maximum number of channels available to this trunk. Set to -1 to have an unlimited number of channels"),
    "size=30 $def_value_maxuse maxlength=30",
    12, // [Validator::class, "number"],
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

$HD_Form->FG_INTRO_TEXT_ADITION = "";
$HD_Form->FG_LOCATION_AFTER_EDIT = "?id=";
$HD_Form->FG_LOCATION_AFTER_DELETE = "?id=";
