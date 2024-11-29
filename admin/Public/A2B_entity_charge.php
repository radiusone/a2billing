<?php

use A2billing\Admin;

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of A2Billing (http://www.a2billing.net/)
 *
 * A2Billing, Commercial Open Source Telecom Billing platform,
 * powered by Star2billing S.L. <http://www.star2billing.com/>
 *
 * @copyright   Copyright © 2004-2015 - Star2billing S.L.
 * @copyright   Copyright © 2022 RadiusOne Inc.
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

$menu_section = 10;
require_once __DIR__ . "/../../common/lib/admin.defines.php";
require_once __DIR__ . "/form_data/FG_var_charge.inc";

Admin::checkPageAccess(Admin::ACX_BILLING);

$HD_Form_c->init();

// To fix internal links due $_SERVER["PHP_SELF"] from parent include that fakes them
if ($wantinclude == 1) {
    $HD_Form_c->FG_EDITION_LINK = "A2B_entity_charge.php?form_action=ask-edit&id=";
    $HD_Form_c->FG_DELETION_LINK = "A2B_entity_charge.php?form_action=ask-delete&id=";
}

if ($id != "" || !is_null($id)) {
    $HD_Form_c->FG_EDITION_CLAUSE = str_replace("%id", "$id", $HD_Form_c->FG_EDITION_CLAUSE);
}

$form_action ??= "list";
$list = $HD_Form_c->perform_action($form_action);

if ($wantinclude != 1) {
    // #### HEADER SECTION
    require_once __DIR__ . "/../templates/main.php";

    // #### HELP SECTION
    echo create_help(_("Extra charges allow the billing of one-off or re-occurring monthly charges. These may be used as setup or service charges, etc...") .
        _("Charges will appear to the user with the description you attach. Each charge that you create for a user will decrement his account."), 'AddCharge');
}

// #### TOP SECTION PAGE
$HD_Form_c->create_toppage($form_action);

$HD_Form_c->create_form($form_action, $list);

if ($wantinclude != 1) {
    require_once __DIR__ . "/../templates/footer.php";
}
