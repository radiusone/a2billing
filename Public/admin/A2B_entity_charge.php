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
 * @copyright   Copyright © 2004-2015 - Star2billing S.L.
 * @copyright   Copyright © 2022-2025 RadiusOne Inc.
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
require_once __DIR__ . "/../../common/form_data/FG_var_charge.inc";
/**
 * @var FormHandler $HD_Form_c named differently because of the page include nonsense
 * @var bool $wantinclude set in A2B_entity_did_billing which includes this page
 * @var numeric-string|null $id
 */

Admin::checkPageAccess(Admin::ACX_BILLING);

$HD_Form_c->init();

if (!empty($id)) {
    $HD_Form_c->update_query_conditions[$HD_Form_c->FG_QUERY_PRIMARY_KEY] = str_replace(
        "%id",
        $id,
        $HD_Form_c->update_query_conditions[$HD_Form_c->FG_QUERY_PRIMARY_KEY]
    );
}

$form_action ??= "list";
$list = $HD_Form_c->perform_action($form_action);

if (!$wantinclude) {
    // #### HEADER SECTION
    require_once __DIR__ . "/templates/main.php";
    // #### TOP SECTION PAGE
    $HD_Form_c->create_toppage($form_action);
}

$HD_Form_c->create_form($form_action, $list);

if (!$wantinclude) {
    require_once __DIR__ . "/templates/footer.php";
}
