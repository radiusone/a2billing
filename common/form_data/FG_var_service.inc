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

$HD_Form = new FormHandler("cc_service", _("Recurring Service"));

$HD_Form->list_query_order_direction = "DESC";

$HD_Form->AddListTopButton(null, null, "alarm-fill");

$HD_Form->add_help_text = create_help(
    _("Utility to apply a scheduled action on the account.<br>")
    . _("For example if you want to remove 10 cents everyday on each single account, it can be defined here, alternatively, if you now want to remove 1 credit every week but only 7 times on each account, the different rules/parameters below will define this.")
);
$HD_Form->help_text = create_help(
    _("Recurring services that decrement an account at timed intervals.")
);

$period_list = array_map(
    fn ($v) => sprintf(_("Every %d days"), $v),
    array_combine(range(1, 60), range(1, 60))
);
$period_list[1]  = _("Daily");

$actived_list = getActivationList();

$rule_list = [
    _("No rule"),
    _("Apply service if card not used in last X days"),
    _("Apply service if card has been used in the last X Days"),
    _("Apply Service every Y days"),
];

$stopmode_list = [
    _("Never"),
    _("Account balance below zero"),
    _("Max number of cycle reach"),
];

$operatemode_list = [
    _("Allow be charged into negative balance"),
    _("Do partial charge but never go into negative balance"),
];

$usegroup_list = [
    _("List of groups below"),
    _("Any group"),
];

$HD_Form->AddListValue(_("Name"), "name");
$HD_Form->AddListValue(_("Amount"), "amount", "get_money");
$HD_Form->AddListValue(_("Period"), "period");
$HD_Form->AddListMapping(_("Status"), "status", $actived_list);
$HD_Form->AddListValue(_("Runs"), "numberofrun");
$HD_Form->AddListValue(_("Last run"), "datelastrun");
$HD_Form->AddListValue(_("Cards affected"), "totalcardperform");
$HD_Form->AddListValue(_("Total credit"), "totalcredit", "get_money");

$HD_Form->FG_ENABLE_ADD_BUTTON = true;
$HD_Form->FG_ENABLE_EDIT_BUTTON = true;
$HD_Form->FG_ENABLE_DELETE_BUTTON = true;
$HD_Form->FG_ENABLE_INFO_BUTTON = true;
$HD_Form->FG_INFO_BUTTON_LINK = "A2B_info_service.php?id=";

$HD_Form->AddEditElement(
    _("NAME"),
    "name",
    "",
    ["maxlength" => 30],
    [Validator::class, "min4Chars"],
    _("Insert the service name")
);

$HD_Form->AddEditElement(
    _("AMOUNT CREDIT"),
    "amount",
    _("Set the amount of credit to remove from the card. 0 will remove all credit from the card use -1 for DID recurrent service."),
    ["maxlength" => 30],
    [Validator::class, "number"],
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
    ["maxlength" => 3],
    [Validator::class, "numeric"],
    _("Insert the daynumber"),
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
    ["maxlength" => 3],
    [Validator::class, "numeric"],
    _("Insert the max number of cycle"),
    "NO-NULL"
);

$HD_Form->AddEditSelect(_("STATUS"), "status", $actived_list);

$HD_Form->AddEditElement(
    _("EMAIL TO SEND REPORT"),
    "emailreport",
    "",
    ["maxlength" => 30],
    [Validator::class, "email"],
    _("Insert the email to send the report"),
    "NO-NULL"
);

$HD_Form->AddEditSqlSelect(
    _("CALL PLAN"),
    "dialplan",
    new Table("cc_tariffgroup", ["tariffgroupname", "id"]),
    [],
    "",
    [0 => _("ANY")]
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

$numrow = (new Table("cc_card_group"))->countRows();

if ($numrow > 0) {
    $HD_Form->AddEditHasMany(
        _("Card Group"),
        new Table(
            "cc_card_group",
            ["id", "name"],
            ["cc_cardgroup_service" => ["cc_card_group.id", "cc_cardgroup_service.id_card_group"]]
        ),
        "id_card_group",
        "id_service",
        null,
        false,
        true,
        new Table("cc_cardgroup_service")
    );
}
