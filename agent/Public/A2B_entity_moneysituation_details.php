<?php

use A2billing\Agent;
use A2billing\Table;

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

require_once "../../common/lib/agent.defines.php";
include './form_data/FG_var_moneysituation_details.inc';

if (!has_rights(Agent::ACX_BILLING)) {
    Header("HTTP/1.0 401 Unauthorized");
    Header("Location: PP_error.php?c=accessdenied");
    die();
}

$DBHandle = DbConnect();

if (isset ($id)) {
    if (!empty ($id) && $id > 0) {
        $table_agent_security = new Table("cc_card LEFT JOIN cc_card_group ON cc_card.id_group=cc_card_group.id ", " cc_card_group.id_agent");
        $clause_agent_security = "cc_card.id= " . $id;
        $result_security = $table_agent_security->get_list($DBHandle, $clause_agent_security);
        if ($result_security[0][0] != $_SESSION['agent_id']) {
            Header("Location: A2B_entity_moneysituation.php?section=10");
            die();
        }
    }
}

$HD_Form->init();

if (!isset ($form_action))
    $form_action = "list"; //ask-add
if (!isset ($action))
    $action = $form_action;

$list = $HD_Form->perform_action($form_action);

// #### HEADER SECTION
$smarty->display('main.tpl');

// #### TOP SECTION PAGE
$HD_Form->create_toppage($form_action);

$HD_Form->create_form($form_action, $list);
