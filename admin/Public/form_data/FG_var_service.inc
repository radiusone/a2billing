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

Admin::checkPageAccess(Admin::ACX_ADMINISTRATOR);

$HD_Form = new FormHandler("cc_service", "Service");

$HD_Form->FG_TABLE_DEFAULT_SENS = "DESC";

$HD_Form->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form->FG_LIST_ADDING_BUTTON_LINK1 = "A2B_entity_service.php?form_action=ask-add";
$HD_Form->FG_LIST_ADDING_BUTTON_ALT1 = $HD_Form->FG_LIST_ADDING_BUTTON_MSG1 = _("Add RECURRING SERVICE");
$HD_Form->FG_LIST_ADDING_BUTTON_IMG1 = get_image_path("cog_add.png") ;

$period_list = array_map(
    fn ($v) => [sprintf(_("Every %d days"), $v), $v],
    array_combine(range(1, 60), range(1, 60))
);
$period_list[1][0]  = _("Daily");

$actived_list = getActivationList();

$rule_list = [
    [_("No rule"), 0],
    [_("Apply service if card not used in last X days"), 1],
    [_("Apply service if card has been used in the last X Days"), 2],
    [_("Apply Service every Y days"), 3]
];

$stopmode_list = [
    [_("Never"), 0],
    [_("Account balance below zero"), 1],
    [_("Max number of cycle reach"), 2],
];

$operatemode_list = [
    [_("Allow be charged into negative balance"), 0],
    [_("Do partial charge but never go into negative balance"), 1],
];

$usegroup_list = [
    [_("List of groups below"), 0],
    [_("Any group"), 1],
];

$HD_Form->AddListValue(_("Name"), "name");
$HD_Form->AddListValue(_("Amount"), "amount", "display_money");
$HD_Form->AddListValue(_("Period"), "period");
$HD_Form->AddListMapping(_("Status"), "status", $actived_list);
$HD_Form->AddListValue(_("Runs"), "numberofrun");
$HD_Form->AddListValue(_("Last run"), "datelastrun");
$HD_Form->AddListValue(_("Cards affected"), "totalcardperform");
$HD_Form->AddListValue(_("Total credit"), "totalcredit", "display_money");
$HD_Form->FieldViewElement([
    "name",
    "amount",
    "period",
    "status",
    "numberofrun",
    "datelastrun",
    "totalcardperform",
    "totalcredit",
]);

$HD_Form->FG_ENABLE_ADD_BUTTON = true;
$HD_Form->FG_ENABLE_EDIT_BUTTON = true;
$HD_Form->FG_ENABLE_DELETE_BUTTON = true;
$HD_Form->FG_ENABLE_INFO_BUTTON = true;
$HD_Form->FG_INFO_BUTTON_LINK = "javascript:window.open('CC_entity_service_details.php?displayfooter=0&popup_select=1&displayheader=0&id=|param|','','scrollbars=yes,resizable=yes,width=600,height=350')";

$HD_Form->AddEditElement(
    _("NAME"),
    "name",
    "",
    "size=35 maxlength=30",
    3, // [Validator::class, "min4Chars"]
    _("Insert the service name")
);

$HD_Form->AddEditElement(
    _("AMOUNT CREDIT"),
    "amount",
    _("Set the amount of credit to remove from the card. 0 will remove all credit from the card use -1 for DID recurrent service."),
    "size=30 maxlength=30",
    12, // [Validator::class, "number"]
    _("Insert the number of credit to remove from the cards.")
);

$HD_Form->AddEditSelect(
    _("RULE"),
    "rule",
    $rule_list,
    "",
    _("where X - day number (X) in days <br>	and Y = number set in period field")
);

$HD_Form->AddEditElement(
    _("DAY NUMBER (X)"),
    "daynumber",
    _("Set the number of days to apply to the rule above."),
    "size=20 maxlength=3",
    4, // [Validator::class, "numeric"]
    _("Insert the daynumber"),
    "",
    "NO"
);

$HD_Form->AddEditSelect(
    _("PERIOD (Y)"),
    "period",
    $period_list,
    "",
    _("Interval to apply service")
);

$HD_Form->AddEditSelect(_("STOP MODE"), "stopmode", $stopmode_list);

$HD_Form->AddEditElement(
    _("MAX NUMBER OF CYCLES BY CARD"),
    "maxnumbercycle",
    _("Set the maximum number of times that the service should be applied."),
    "size=20 maxlength=3",
    4, // [Validator::class, "numeric"]
    _("Insert the max number of cycle"),
    "",
    "NO-NULL"
);

$HD_Form->AddEditSelect(_("STATUS"), "status", $actived_list);

$HD_Form->AddEditElement(
    _("EMAIL TO SEND REPORT"),
    "emailreport",
    "",
    "size=30 maxlength=30",
    1, // [Validator::class, "email"]
    _("Insert the email to send the report"),
    "",
    "NO-NULL"
);

$HD_Form->AddEditSqlSelect(
    _("CALL PLAN"),
    "dialplan",
    "cc_tariffgroup",
    "tariffgroupname,id",
    "",
    "",
    [0, _("ANY")]
);

$HD_Form->AddEditSelect(
    _("OPERATE MODE"),
    "operate_mode",
    $operatemode_list,
    "",
    _("If you want to charge only part of sum in case if current balance less then amount, select second option. Will be charged only part of amount, cause balance to be 0.0")
);

$HD_Form->AddEditSelect(
    _("SKIP GROUP"),
    "use_group",
    $usegroup_list,
    "",
    _("Select first option(default) to manualy select groups or select second option to use service for any group, discarding list below")
);

$numrow = (new Table("cc_card_group"))->countRows($HD_Form->DBHandle);

if ($numrow > 0) {
    $HD_Form->AddEditSqlSelect(
        _("CARD GROUP"),
        "service",
        "cards_services",
        "name,id",
        "",
        "",
        [],
        "%1 : (%2)",
        "",
        "size='10' multiple='multiple'",
        "", [
            "table" => "cc_cardgroup_service",
            "name" => "id_card_group",
            "tables" => "cc_card_group LEFT JOIN cc_cardgroup_service ON cc_card_group.id=cc_cardgroup_service.id_card_group",
            "columns" => "DISTINCT cc_card_group.name, cc_card_group.id",
            "where" => "cc_cardgroup_service.id_service = %id",
            "format" => "%2 - (%1)",
            "fk" => "id_service",
        ]
    );
}

$HD_Form->FG_LOCATION_AFTER_ADD = "?id=";
$HD_Form->FG_LOCATION_AFTER_EDIT = "?id=";
$HD_Form->FG_LOCATION_AFTER_DELETE = "?id=";
