<?php

use A2billing\Admin;
use A2billing\Forms\FormHandler;

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

getpost_ifset(["inuse", "did", "action", "posted_search"]);
/**
 * @var string $inuse
 * @var string $did
 * @var string $action
 * @var string $posted_search
 */
$HD_Form = new FormHandler(
    "cc_did_use",
    _("DID usage"),
    "cc_did_use.id",
    ["cc_did" => ["cc_did_use.id_did", "cc_did.id"]]
);
$HD_Form->FG_TABLE_DEFAULT_ORDER = "reservationdate";
$HD_Form->FG_TABLE_DEFAULT_SENS = "DESC";

if ((!empty($posted_search) && !empty($inuse)) || empty($posted_search)) {
    $HD_Form->list_query_conditions[] = ["SUB", ["releasedate" => [[null], ["<", "1984-01-01"]]], "OR"];
    $HD_Form->list_query_conditions["cc_did_use.activated"] = 1;
    $HD_Form->AddListActionButton(
        "A2B_entity_did_use.php?action=ask_release&did=|col5|",
        _("Release this did"),
        "data:image/gif;base64,R0lGODlhIQALAKEDAPPt2ZycnAAAAP///yH+FUNyZWF0ZWQgd2l0aCBUaGUgR0lNUAAh+QQBAAADACwAAAAAIQALAAACQVyOqctoAKOctMZjs5YYCAF9IBh+nimeZDd6EemGLgwDLGnGZzmiMDt7CWU0nS1AVClTLWaus4lSoNKqzdDIah0FADs="
    );
}
if (($action ?? "") !== "ask_release") {
    $HD_Form->help_text = create_help(_("List the DIDs currently in use with the customer id and their destination number <br/> You can use the search option to show the usage of a given DID or all DIDs"));
}

$status_list = [_("Free"), _("Reserved")];

$HD_Form->AddListValue(_("DID"), "did", "display_phone_number");
$HD_Form->AddListValue(_("Card"), "id_cc_card", "display_customer_id_link");
$HD_Form->AddListValue(_("Reservation date"), "reservationdate");
$HD_Form->AddListValue(_("Liberation date"), "releasedate");
$HD_Form->AddListMapping(_("Status"), "cc_did_use.activated", $status_list);
$HD_Form->FieldViewElement (["did", "id_cc_card", "reservationdate", "releasedate", "cc_did_use.activated", "id_did"]);

$HD_Form->CV_NO_FIELDS = _("No DIDs in use");

$HD_Form->AddSearchTextInput(_("DID"), "did");
$HD_Form->AddSearchSelectInput(_("Status"), "inuse", [_("All DIDs"), _("DIDs in use")]);
