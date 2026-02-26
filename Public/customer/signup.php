<?php

use A2billing\A2Billing;
use A2billing\Forms\FormHandler;
use A2billing\Table;

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
 */

require_once __DIR__ . "/../../common/lib/customer.defines.php";
require_once __DIR__ . "/../../common/form_data/FG_var_signup.inc";
/**
 * @var A2Billing $A2B
 * @var FormHandler $HD_Form
 */

if (!$A2B->config["signup"]['enable_signup']) {
    http_response_code(404);
    exit;
}

$form_action ??= "ask-add";

getpost_ifset(["subscriber_signup"]);
/**
 * @var string|null $subscriber_signup
 */
$subscriber_signup ??= "";

if (!is_numeric($subscriber_signup)) {
    //check subscriber_signup
    $check_subscriber = (new Table("cc_subscription_signup"))->countRows();
    if ($check_subscriber) {
        header("Location: signup_service.php");
        die();
    }
}

$HD_Form->init();

$list = $HD_Form->perform_action($form_action);

if ($form_action === "add") {
    unset ($_SESSION["cardnumber_signup"]);
    $_SESSION["language_code"] = $_POST["language"];
    $_SESSION["cardnumber_signup"] = $maxi;
    $_SESSION["id_signup"] = $HD_Form->QUERY_RESULT;
    Header("Location: signup_confirmation.php");
}

// #### HEADER SECTION
require_once __DIR__ . "/templates/signup_header.php";

// #### TOP SECTION PAGE
$HD_Form->create_toppage($form_action);

$HD_Form->create_form($form_action, $list);

// #### FOOTER SECTION
require_once __DIR__ . "/templates/footer.php";
