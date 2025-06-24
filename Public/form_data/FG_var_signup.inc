<?php

use A2billing\Forms\FormBO;
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

getpost_ifset(["subscriber_signup", "key"]);
/**
 * @var numeric-string|null $subscriber_signup
 * @var string|null $key
 */
$HD_Form = new FormHandler("cc_card", "User");
$HD_Form->FG_ENABLE_ADD_BUTTON = true;

$form_action ??= "ask-add";
$groupid = null;
$tariffid = null;

$_SESSION["key"] = $agentkey = $key ?? $_SESSION["key"] ?? null;

if ($agentkey) {
	$table_key = new Table(
        "cc_agent_signup AS s",
        ["g.id AS id_group", "t.id_tariffgroup"],
        [
            "cc_agent_tariffgroup AS t" => ["s.id_agent", "t.id_agent"],
            "cc_card_group AS g" => ["s.id_group", "g.id"],
        ]
    );
	$data = $table_key->getRow(["code" => $agentkey]);
	if ($data) {
        // check if groupid and tarif in already in this agent
		$groupid = $data['id_group'] ?? 0;
		$tariffid = $data['id_tariffgroup'] ?? 0;
	}
}

$callPlan_list = [];
if (!$tariffid) {
	$callPlan_idlist = explode(",", $A2B->config["signup"]['callplan_id_list'] ?? "");
	$callPlan_list = (new Table("cc_tariffgroup", ["tariffgroupname", "id"]))
        ->getRows(["id" => ["in", array_map("trim", $callPlan_idlist)]]);

	if (!$callPlan_list) {
		exit (gettext("No Call Plan id are defined"));
	}
}

if (LANGUAGE_ENABLE)  {
    $language_list = getLanguages();
    $HD_Form->AddEditSelect(gettext("LANGUAGE"), "language", $language_list);
}

if (CURRENCY_ENABLE)  {
    $currency_list = [];
    $currencies_list = get_currencies();

    foreach ($currencies_list as $key => $cur_value) {
        $currency_list[$key] = $cur_value["name"] . ' (' . $cur_value["value"] . ')';
    }
    $HD_Form->AddEditSelect(gettext("CURRENCY"), "currency", $currency_list);
}

if (count($callPlan_list) && !$subscriber_signup) {
    $HD_Form->AddEditSelect(gettext("CALL PLAN"), "tariff", $callPlan_list);
}

if (LASTNAME_ENABLE)  {
    $HD_Form->AddEditElement(
        gettext("LASTNAME"),
        "lastname",
        "",
        ["maxlength" => 50],
        [Validator::class, "min2Chars"],
        gettext("Insert your lastname")
    );
}

if (FIRSTNAME_ENABLE)  {
    $HD_Form->AddEditElement(
        gettext("FIRSTNAME"),
        "firstname",
        "",
        ["maxlength" => 50],
        [Validator::class, "min2Chars"],
        gettext("Insert your firstname")
    );
}

$HD_Form->AddEditElement(
    gettext("EMAIL"),
    "email",
    "",
    ["maxlength" => 70],
    [Validator::class, "email"],
    gettext("Insert your email")
);

if (ADDRESS_ENABLE)  {
    $HD_Form->AddEditElement(
        gettext("ADDRESS"),
        "address",
        "",
        ["maxlength" => 100],
        [Validator::class, "min3Chars"],
        gettext("Insert your address")
    );
}

if (CITY_ENABLE)  {
    $HD_Form->AddEditElement(
        gettext("CITY"),
        "city",
        "",
        ["maxlength" => 40],
        [Validator::class, "min3Chars"],
        gettext("Insert your city")
    );
}

if (STATE_ENABLE)  {
    $HD_Form->AddEditElement(
        gettext("STATE/PROVINCE"),
        "state",
        "",
        ["maxlength" => 40],
        [Validator::class, "min2Chars"],
        gettext("Insert your state"),
        "",
        "NO"
    );
}

if (COUNTRY_ENABLE)  {
    $HD_Form->AddEditSqlSelect(
        gettext("COUNTRY"),
        "country",
        new Table("cc_country", ["countrycode", "countryname"]),
        [],
        $form_action === "ask-add" ? "CAN" : ""
    );
    $country_editFG = "country,";
}

if (ZIPCODE_ENABLE)  {
    $HD_Form->AddEditElement(
        gettext("ZIP/POSTAL CODE"),
        "zipcode",
        "",
        ["maxlength" => 20],
        [Validator::class, "min3Chars"],
        gettext("Insert your zipcode")
    );
}

if (TIMEZONE_ENABLE)  {
    $HD_Form->AddEditSelect(
        gettext("TIMEZONE"),
        "id_timezone",
        get_timezones()
    );
}

if (PHONE_ENABLE)  {
    $HD_Form->AddEditElement(
        gettext("PHONENUMBER"),
        "phone",
        "",
        ["maxlength" => 20],
        [Validator::class, "min8DigitLike"],
        gettext("Insert your phone number")
    );
}

if (FAX_ENABLE)  {
    $HD_Form->AddEditElement(
        gettext("FAXNUMBER"),
        "fax",
        "",
        ["maxlength" => 20],
        null,
        gettext("Insert your fax number")
    );
}

if (COMP_ENABLE)  {
    $HD_Form->AddEditElement(
        gettext("COMPANY NAME"),
        "company_name",
        "",
        ["maxlength" => 50],
        null,
        gettext("Insert Company name of this customer"),
        "",
        "NO"
    );
}

if (COMP_WEB_ENABLE)  {
    $HD_Form->AddEditElement(
        gettext("COMPANY WEBSITE"),
        "company_website",
        "",
        ["maxlength" => 60],
        null,
        gettext("Insert the company website of this customer"),
        "",
        "NO"
    );
}

if (VAT_RN_ENABLE)  {
    $HD_Form->AddEditElement(
        gettext("VAT REGISTRATION NUMBER "),
        "VAT_RN",
        "",
        ["maxlength" => 40],
        null,
        gettext("Insert the VAT registration number of this customer"),
        "",
        "NO"
    );
}

if (TRAFFIC_ENABLE)  {
    $HD_Form->AddEditElement(
        gettext("TRAFFIC PER MONTH"),
        "traffic",
        "",
        ["maxlength" => 20],
        [Validator::class, "numeric"],
        gettext("Insert the traffic per month for this customer"),
        "",
        "NO"
    );
}

if (TRAFFIC_TARGET_ENABLE)  {
    $HD_Form->AddEditTextarea(
        gettext("TARGET TRAFFIC"),
        "traffic_target",
        "",
        ["rows" => 4],
        gettext("Insert the target traffic description")
    );
}

if (CAPTCHA_ENABLE) {
    $HD_Form->AddEditElement(
        gettext("VERIFICATION"),
        "captchacode",
        "",
        ["maxlength" => 6],
        [Validator::class, "captcha"],
        gettext("Insert code from above picture")
    );
}

if ($form_action === "add") {
    $array_card_generated = gen_card_with_alias();
    $cardnumber = $array_card_generated[0];
    $cardalias = $array_card_generated[1];
    $pass = generate_random_value("##########");
    $loginkey = generate_random_value("XXXXXXXXXXXXXXXXXXXX");

    $HD_Form->FG_ADD_QUERY_HIDDEN_INPUTS["iax_buddy"] = $A2B->config["signup"]['iax_account'] ? 1 : 0;
    $HD_Form->FG_ADD_QUERY_HIDDEN_INPUTS["sip_buddy"] = $A2B->config["signup"]['sip_account'] ? 1 : 0;
    $HD_Form->FG_ADD_QUERY_HIDDEN_INPUTS["expiredays"] = $A2B->config["signup"]['expiredays'];
    $HD_Form->FG_ADD_QUERY_HIDDEN_INPUTS["expirationdate"] = $A2B->config["signup"]['expirationdate'] ?? ""
        ?: (new DateTime("+10 years"))->format("Y-m-d H:i:s");
    $HD_Form->FG_ADD_QUERY_HIDDEN_INPUTS["typepaid"] = $A2B->config["signup"]['typepaid'];
    $HD_Form->FG_ADD_QUERY_HIDDEN_INPUTS["enableexpire"] = $A2B->config["signup"]['enableexpire'] ? 1 : 0;
    $HD_Form->FG_ADD_QUERY_HIDDEN_INPUTS["runservice"] = $A2B->config["signup"]['runservice'] ? 1 : 0;
    $HD_Form->FG_ADD_QUERY_HIDDEN_INPUTS["simultaccess"] = $A2B->config["signup"]['simultaccess'] ? 1 : 0;
    $HD_Form->FG_ADD_QUERY_HIDDEN_INPUTS["credit"] = $A2B->config["signup"]['credit'];
    $HD_Form->FG_ADD_QUERY_HIDDEN_INPUTS["username"] = $cardnumber;
    $HD_Form->FG_ADD_QUERY_HIDDEN_INPUTS["useralias"] = $cardalias;
    $HD_Form->FG_ADD_QUERY_HIDDEN_INPUTS["uipass"] = $pass;
    $HD_Form->FG_ADD_QUERY_HIDDEN_INPUTS["loginkey"] = $loginkey;
    $HD_Form->FG_ADD_QUERY_HIDDEN_INPUTS["status"] = $A2B->config["signup"]['activated']
        ? ($A2B->config["signup"]['activatedbyuser'] ? '1' : '3')
        : ($A2B->config["signup"]['activatedbyuser'] ? '2' : '3');

    if ($groupid) {
        $HD_Form->FG_ADD_QUERY_HIDDEN_INPUTS["id_group"] = $groupid;
    }

    if (!is_numeric($subscriber_signup)) {
        if (!empty($callPlan_idlist)) {
            $mytariff = implode(",", $callPlan_idlist);
            $HD_Form->FG_ADD_QUERY_HIDDEN_INPUTS["tariff"] = $mytariff;
        } elseif ($tariffid) {
            $HD_Form->FG_ADD_QUERY_HIDDEN_INPUTS["tariff"] = $tariffid;
        }
    }

    // If Service - find the callplan associate
    if (is_numeric($subscriber_signup)) {
        $table_subscribe = new Table("cc_subscription_signup", "id_callplan");
        $id_callplan = $table_subscribe->getValue(["id" => $subscriber_signup]);
        $HD_Form->FG_ADD_QUERY_HIDDEN_INPUTS["tariff"] = $id_callplan ?? "";
        $HD_Form->FG_ADD_FORM_HIDDEN_INPUTS["subscriber_signup"] = $subscriber_signup;
    }

    $HD_Form->REALTIME_SIP_IAX_INFO = ["username" => $cardnumber, "secret" => $pass];
    $HD_Form->FG_TEXT_ADITION_CONFIRMATION = _("Thanks for registering with us.<br>A email with your information has been sent to your account ") . $_POST["email"] . "<br><br><b>" . gettext("Your card number is ") . $cardnumber . "</b><br><b>" . gettext("Your password is ") . $pass . "</b>";
    $HD_Form->FG_ADDITIONAL_FUNCTION_AFTER_ADD = [FormBO::class, "processing_card_signup"];
}

$HD_Form->FG_INTRO_TEXT_ADITION = "";
$HD_Form->FG_ADD_PAGE_BOTTOM_TEXT = _("Click 'SIGNUP' to continue");
$HD_Form->FG_ADD_PAGE_SAVE_BUTTON_TEXT = _("SIGNUP");
