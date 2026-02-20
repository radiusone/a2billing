<?php

use A2billing\A2Billing;
use A2billing\Admin;
use A2billing\Agent;
use A2billing\Customer;
use A2billing\Forms\FormBO;
use A2billing\Forms\FormHandler;
use A2billing\Forms\Validator;
use A2billing\Notification;
use A2billing\NotificationsDAO;
use A2billing\Table;

/**
 * @var A2Billing $A2B
 * @var string $popup_select
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
/**
 * @var A2Billing $A2B
 */

getpost_ifset(["id", "cardnumber_length"]);
/**
 * @var string $id
 * @var string $cardnumber_length
 */

$form_action ??= "list";

$HD_Form = new FormHandler(
    "cc_card",
    _("Card"),
    "cc_card.id",
    $form_action === "list" ? [
        "cc_card_group" => ["cc_card.id_group", "cc_card_group.id"],
        "cc_agent" => ["cc_card_group.id_agent", "cc_agent.id"],
    ] : []
);

if (is_agent()) {
    $HD_Form->list_query_conditions["id_agent"] = Agent::id();
} elseif (is_customer()) {
    // shouldn't be needed, but just in case
    $HD_Form->list_query_conditions["cc_card.id"] = Customer::id();
}
$HD_Form->search_session_key = 'entity_card_selection';
// todo: there are probably more than this
$HD_Form->foreign_keys = [
    "cc_card_history" => "id_cc_card",
    "cc_status_log" => "id_cc_card",
];
if ($A2B->config['webui']['delete_fk_card'] ?? true) {
    $HD_Form->fk_force_delete = true;
    $HD_Form->fk_confirm_delete = $HD_Form::CONFIRM_DELETE;
    $HD_Form->foreign_keys["cc_callerid"] = "id_cc_card";
    $HD_Form->foreign_keys["cc_iax_buddies"] = "id_cc_card";
    $HD_Form->foreign_keys["cc_sip_buddies"] = "id_cc_card";

    // WE WILL DELETE THE FK
    $HD_Form->delete_message_confirm_fk = _("You are going as well to remove all the SIP/IAX accounts & CallerIDs attached to this card! Please confirm that you really want to remove this card ? ");
} else {
    // WE JUST NOTIFY
    $HD_Form->delete_message_confirm_fk = _("You are going to remove a card that have SIP/IAX accounts and/or CallerIDs attached to it ! Please confirm that you really want to remove this card ? ");
}

if (!$popup_select && (is_admin() || Agent::allowed(Agent::ACX_GENERATE_CUSTOMER))) {
    $HD_Form->AddListTopButton("A2B_entity_card_multi.php", _("Generate Customers"), "people-fill");
}

if (!$popup_select && (is_admin() || Agent::allowed(Agent::ACX_CREATE_CUSTOMER))) {
    $HD_Form->AddListTopButton("A2B_entity_card.php?form_action=ask-add", _("Add Customer"), "person-fill");
}

$HD_Form->list_help_text = create_help(
    _("Customers are listed below by account number. Each row corresponds to one customer, along with information such as their call plan, credit remaining, etc.")
    . "<br/>"
    . is_admin() || Agent::allowed(Agent::ACX_VOIPCONF)
        ? _("The SIP and IAX buttons create SIP and IAX entries to allow direct VoIP connections to the Asterisk server without further authentication.")
        : ""
);

$HD_Form->help_text = create_help(
    _("Create and edit the properties of each customer. Click CONFIRM DATA at the bottom of the page to save changes.")
);

if (is_customer()) {
    $HD_Form->edit_help_text = create_help(_("Personal information.") . '<br>' . _("You can update your personal information here."));
    $HD_Form->FG_LOCATION_AFTER_EDIT = "A2B_info_card.php";
}

$language_list = getLanguages();
$discount_list = getDiscount_List();
$simultaccess_list = getCardAccess_List();
$limits_notify_list = getLimitNotify_List($A2B);
$currency_list = getCurrencyValuesList();
$restriction_list = getRestrictionList();
$cardstatus_list = getCardStatus_List();
$typepaid_list = getPaidTypeList();
$expire_list = getCardExpire_List();
$yesno = getYesNoList();
$invoiceday_list = getInvoiceDay_List();
$serial_pad_length = $A2B->config["webui"]['card_serial_length'];

// The list view can be customized to only show certain fields, making things more complicated
// extract the user-defined list of fields we're showing
$show_list = [];
$show_list_temp = explode(',', $A2B->config["webui"]["card_show_field_list"]);
foreach ($show_list_temp as $value) {
    $value = strtolower(trim($value));
    if ($value) {
        $show_list[] = $value;
    }
}

// force some as first columns in popup view
if ($popup_select) {
    foreach (array_reverse(["username", "lastname", "id_group", "credit"]) as $field) {
        array_unshift($show_list, $field);
    }
    $show_list = array_unique($show_list);
}

// sensible default
if (count($show_list) === 0) {
    $show_list = ["username", "lastname", "id_group", "credit", "tariff", "status"];
}

// loop through the shown data and add the table cells
foreach ($show_list as $field) {
    switch ($field) {
        case "id":
            $HD_Form->AddListValue(_("ID"), "cc_card.id");
            break;
        case "username":
            $HD_Form->AddListValue(
                _("Account number"),
                "username",
                $popup_select ? null : [Customer::class, "getLinkFromUsername"]
            );
            break;
        case "lastname":
            $HD_Form->AddListValue(_("Last name"), "cc_card.lastname");
            break;
        case "id_group":
            $HD_Form->AddListValue(_("Group"), "cc_card_group.name");
            break;
        case "id_agent":
            if (is_admin()) {
                $HD_Form->AddListValue(_("Agent"), "cc_agent.id", [Agent::class, "getName"]);
            }
            break;
        case "credit":
            $HD_Form->AddListValue(_("Balance"), "cc_card.credit", "get_money");
            break;
        case "useralias":
            $HD_Form->AddListValue(_("Web login"), "useralias");
            break;
        case "id_seria":
            $HD_Form->AddListSqlMapping(_("Series"), "id_seria", new Table("cc_card_seria", ["id", "name"]));
            break;
        case "serial":
            $HD_Form->AddListValue(_("Serial #"), "serial", "str_pad", ["%X", (int)$serial_pad_length, 0, STR_PAD_LEFT]);
            break;
        case "tariff":
            $HD_Form->AddListSqlMapping(_("Call Plan"), "tariff", new Table("cc_tariffgroup", ["id", "tariffgroupname"]));
            break;
        case "status":
            $HD_Form->AddListMapping(_("Status"), "status", getCardStatus_Acronym_List());
            break;
        case "language":
            $HD_Form->AddListMapping(_("Language"), "cc_card.language", getLanguages());
            break;
        case "inuse":
            $HD_Form->AddListValue(_("In use"), "inuse");
            break;
        case "currency":
            $HD_Form->AddListMapping(_("Currency"), "cc_card.currency", $currency_list);
            break;
        case "sip_buddy":
            $HD_Form->AddListMapping(_("SIP"), "sip_buddy", $yesno);
            break;
        case "iax_buddy":
            $HD_Form->AddListMapping(_("AIX"), "iax_buddy", $yesno);
            break;
        case "nbused":
            $HD_Form->AddListValue(_("Calls"), "nbused");
            break;
        case "firstname":
            $HD_Form->AddListValue(_("First name"), "cc_card.firstname");
            break;
        case "email":
            $HD_Form->AddListValue(_("Email"), "cc_card.email");
            break;
        case "discount":
            $HD_Form->AddListValue(_("Discount"), "discount", "get_percent");
            break;
        case "callerid":
            $HD_Form->AddListValue(_("Caller ID"), "(SELECT GROUP_CONCAT(cid SEPARATOR '<br/>') FROM cc_callerid WHERE id_cc_card = cc_card.id) AS callerid");
            break;
        default:
            continue(2);
    }
}
$column_count = count($HD_Form->list_query_columns);

// need these in a fixed position for action button links
$HD_Form->AddListHiddenValue("cc_card.id AS id_")
    ->AddListHiddenValue("username AS username_")
    ->AddListHiddenValue("useralias AS alias_");

$HD_Form->AddListFilter("username", _("Account Number"));

$HD_Form->FG_ENABLE_ADD_BUTTON = !is_customer();
$HD_Form->FG_ENABLE_EDIT_BUTTON = !$popup_select && (Admin::allowed(Admin::ACX_MODIFY_CUSTOMERS) || Agent::allowed(Agent::ACX_EDIT_CUSTOMER) || Customer::allowed(Customer::ACX_PERSONALINFO));
$HD_Form->FG_ENABLE_DELETE_BUTTON = !$popup_select && (Admin::allowed(Admin::ACX_MODIFY_CUSTOMERS) || Agent::allowed(Agent::ACX_DELETE_CUSTOMER));

if ($popup_select) {
    // popup window
    $HD_Form->FG_LIST_VIEW_PAGE_SIZE = 6;
    switch ($popup_select) {
        case 1: $HD_Form->AddListSelectActionButton("javascript:sendValue('|param|')"); break;
        // username is always first column on popups
        case 2: $HD_Form->AddListSelectActionButton("javascript:sendValue('|col0|')"); break;
        // populate 2 fields, for A2B_entity_friend.php
        case 3: $HD_Form->AddListSelectActionButton("javascript:sendValue('|param|', '|col0|', 'accountcode')"); break;
    }
} elseif ($form_action === "list") {
    $HD_Form->FG_LIST_VIEW_PAGE_SIZE = 10;
    $HD_Form->search_form_enabled = true;

    $HD_Form->search_form_title = _("Define specific criteria to search for cards created.");
    $HD_Form->AddSearchDateInput(_("Creation date"), "creationdate");
    $HD_Form->AddSearchDateInput(_("First use date"), "firstusedate");
    $HD_Form->AddSearchTextInput(_("Account number"), "username");
    $HD_Form->AddSearchTextInput(_("Email"), "cc_card.email");
    $HD_Form->AddSearchTextInput(_("Last name"), "cc_card.lastname");
    $HD_Form->AddSearchTextInput(_("First name"), "cc_card.firstname");
    $HD_Form->AddSearchTextInput(_("Login"), "useralias");
    $HD_Form->AddSearchTextInput(_("Company"), "company_name");
    $HD_Form->AddSearchComparisonInput(_("Customer ID"), "cc_card.id");
    $HD_Form->AddSearchComparisonInput(_("Serial"), "serial");
    $HD_Form->AddSearchComparisonInput(_("Balance"), "cc_card.credit");
    $HD_Form->AddSearchComparisonInput(_("In use"), "inuse");

    $HD_Form->AddSearchSqlSelectInput(_("Card series"), "id_seria", new Table("cc_card_seria", ["name", "id"]));
    $HD_Form->AddSearchSelectInput(_("Language"), "cc_card.language", $language_list);
    $HD_Form->AddSearchSqlSelectInput(_("Call plan"), "tariff", new Table("cc_tariffgroup", ["tariffgroupname", "id"]));
    $HD_Form->AddSearchSelectInput(_("Status"), "status", $cardstatus_list);
    $HD_Form->AddSearchSelectInput(_("Access"), "simultaccess", $simultaccess_list);
    $HD_Form->AddSearchSqlSelectInput(_("Group"), "id_group", new Table("cc_card_group", ["name", "id"]));
    $HD_Form->AddSearchSelectInput(_("Currency"), "cc_card.currency", getCurrenciesList());
    $HD_Form->AddSearchSelectInput(_("Account type"), "typepaid", $typepaid_list);
    $HD_Form->AddSearchSqlSelectInput(_("Agent"), "id_agent", new Table("cc_agent", ["CONCAT(firstname, ' ', lastname)", "id"]));

    $HD_Form->FG_ENABLE_INFO_BUTTON = true;
    $HD_Form->FG_INFO_BUTTON_LINK = "A2B_info_card.php?id=";
    $FN_COLUMN_ID = "col" . ($column_count);//get numbers of rows for needed fields
    $FN_COLUMN_USERNAME = "col" . ($column_count + 1);
    $FN_COLUMN_ALIAS = "col" . ($column_count + 2);

    if (!empty($A2B->config["webui"]['buddy_sip_file']) && (is_admin() || Agent::allowed(Agent::ACX_VOIPCONF))) {
        $HD_Form->AddListActionButton(
            "A2B_entity_friend.php?voip_type=sip&form_action=add_sip&id_cc_card=|$FN_COLUMN_ID|&cardnumber=|$FN_COLUMN_USERNAME|&useralias=|$FN_COLUMN_ALIAS|",
            _("Auto Create SIP Config"),
            "data:image/gif;base64,R0lGODlhFwALAKEDAAAAAJycnPPZ3f///yH5BAEAAAMALAAAAAAXAAsAAAItXI6pa+IPozyyWgqBFhrwDWGPNzoe+YgmSHaRWnJmBbs2HVh6mO97YFgIhYMCADs="
        );
    }
    if (!empty($A2B->config["webui"]['buddy_iax_file']) && (is_admin() || Agent::allowed(Agent::ACX_VOIPCONF))) {
        $HD_Form->AddListActionButton(
            "A2B_entity_friend.php?voip_type=iax&form_action=add_iax&id_cc_card=|$FN_COLUMN_ID|&cardnumber=|$FN_COLUMN_USERNAME|&useralias=|$FN_COLUMN_ALIAS|",
            _("Auto Create IAX Config"),
            "data:image/gif;base64,R0lGODlhFwALAKEDAAAAAJycnNnz4f///yH5BAEAAAMALAAAAAAXAAsAAAIvXI6pa+IPozyyWgqBFhrwD2GZ45XgIz4et51oIK2mGcLZNrtCavV8XwkYFkTioAAAOw=="
        );
    }
    if (is_admin()) {
        $HD_Form->AddListActionButton(
            "A2B_entity_payment.php?form_action=ask-add&card_id=|$FN_COLUMN_ID|",
            _("Add Payment to this Card"),
            "",
            "cash-stack",
            "text-success"
        );
        $HD_Form->AddListActionButton(
            "A2B_entity_subscriber.php?form_action=ask-add&id=|$FN_COLUMN_ID|",
            _("Add New Subscription to this Card"),
            "",
            "journal-plus"
        );
        $HD_Form->AddListActionButton(
            "A2B_mass_mail.php?form_action=ask-add&id=|$FN_COLUMN_ID|",
            _("Email to this Customer"),
            "",
            "envelope"
        );
    }
} elseif (Admin::allowed(Admin::ACX_MODIFY_CUSTOMERS) || Agent::allowed(Agent::ACX_EDIT_CUSTOMER) || Customer::allowed(Customer::ACX_PERSONALINFO)) {
    if (empty($cardnumber_length) || !is_numeric($cardnumber_length)) {
        $cardnumber_length = min($A2B->cardnumber_range);
    }
    $array_card_generated = gen_card_with_alias($cardnumber_length);

    $acct_attr = ["readonly" => "readonly"];
    if ($form_action === "ask-add") {
        $acct_attr["value"] = $array_card_generated[0];
    }
    if (!is_customer()) {
        $HD_Form->AddEditSection(_("Customer Information"));

        $HD_Form->AddEditElement(
            _("Account number"),
            "username",
            '',
            $acct_attr,
            [Validator::class, "numeric"],
            _("Insert the account number")
        );

        $login_attr = ["maxlength" => 40];
        if ($form_action === "ask-add") {
            $login_attr["value"] = $array_card_generated[1];
        }
        $HD_Form->AddEditElement(
            _("Login"),
            "useralias",
            "",
            $login_attr,
            [Validator::class, "min5Chars"],
            _("Insert the webui login")
        );

        $pass_attr = ["maxlength" => 20];
        if ($form_action === "ask-add") {
            $pass_attr["value"] = generate_random_value("#####XXXXXXXXXXXXXXX");
        }
        $HD_Form->AddEditElement(
            _("Password"),
            "uipass",
            _("Password for customer to access to the web interface and view the balance."),
            $pass_attr,
            null,
            _("Insert the password")
        );

        $balance_attr = ["maxlength" => 30];
        if ($form_action === "ask-add") {
            $balance_attr["value"] = 0;
        } else {
            $balance_attr["readonly"] = "readonly";
        }
        $HD_Form->AddEditElement(
            _("Balance"),
            "credit",
            _("currency : ") . strtoupper(BASE_CURRENCY),
            $balance_attr,
            [Validator::class, "number"],
            _("Insert the number of credit")
        );

        $HD_Form->AddEditSqlSelect(
            _("Group"),
            "id_group",
            new Table("cc_card_group", ["name", "id"]),
            is_admin() ? [] : ["id_agent" => Agent::id()]
        );

        $HD_Form->AddEditSqlSelect(
            _("Card series"),
            "id_seria",
            new Table("cc_card_seria", ["name", "id"]),
            [],
            "",
            ["" => _("NOT DEFINED")]
        );
    }

    $HD_Form->AddEditSection(_("Personal Information"));

    $HD_Form->AddEditElement(
        _("Last name"),
        "lastname",
        "",
        ["maxlength" => 50],
        [Validator::class, "min2Chars"],
        _("Insert the lastname of the customers"),
        "NO"
    );

    $HD_Form->AddEditElement(
        _("First name"),
        "firstname",
        "",
        ["maxlength" => 50],
        [Validator::class, "min2Chars"],
        _("Insert the firstname of the customers"),
        "NO"
    );

    $HD_Form->AddEditElement(
        _("Email"),
        "email",
        "",
        ["maxlength" => 70],
        [Validator::class, "email"],
        _("Insert the email"),
        "NO"
    );

    $HD_Form->AddEditElement(
        _("Address"),
        "address",
        "",
        ["maxlength" => 100],
        [Validator::class, "min3Chars"],
        _("Insert the address of the customers"),
        "NO"
    );

    $HD_Form->AddEditElement(
        _("City"),
        "city",
        "",
        ["maxlength" => 40],
        [Validator::class, "min3Chars"],
        _("Insert the city"),
        "NO"
    );

    $HD_Form->AddEditElement(
        _("Province/State"),
        "state",
        "",
        ["maxlength" => 40],
        [Validator::class, "min2Chars"],
        _("Insert the state"),
        "NO"
    );

    $HD_Form->AddEditSqlSelect(
        _("Country"),
        "country",
        new Table("cc_country", ["countryname", "countrycode"]),
        [],
        $form_action === "ask-add" ? "USA" : ""
    );

    $HD_Form->AddEditElement(
        _("Postal code"),
        "zipcode",
        "",
        ["maxlength" => 20],
        [Validator::class, "min3Chars"],
        _("Insert the zipcode"),
        "NO"
    );

    $HD_Form->AddEditElement(
        _("Phone number"),
        "phone",
        "",
        ["maxlength" => 20],
        [Validator::class, "min8DigitLike"],
        _("Insert the phone number of the customers"),
        "NO"
    );

    $HD_Form->AddEditElement(
        _("Fax number"),
        "fax",
        "",
        ["maxlength" => 20],
        null,
        _("Insert the fax number of the customers"),
        "NO"
    );

    $HD_Form->AddEditSelect(
        _("Timezone"),
        "id_timezone",
        get_timezones(),
        "",
        _("Leave blank to use system default") . " " . (new DateTime())->getTimezone()->getName(),
    );

    if (is_customer()) {
        if (ACT_VOICEMAIL) {
            $HD_Form->AddEditRadio(
                gettext("Activate voicemail"),
                "voicemail_activated",
                getYesNoList(),
                "0",
                gettext("Customer can enable the voicemail for this card."),
                [],
                gettext("Customer can choose to enable the voicemail for this card")
            );
        }

        return;
    }

    $HD_Form->AddEditElement(
        _("Company name"),
        "company_name",
        "",
        ["maxlength" => 50],
        null,
        _("Insert Company name of this customer"),
        "NO"
    );

    $HD_Form->AddEditElement(
        _("Company website"),
        "company_website",
        "",
        ["maxlength" => 60],
        null,
        _("Insert the company website of this customer"),
        "NO"
    );

    $HD_Form->AddEditSection(_("Customer Status"));

    if ($form_action !== "edit" && is_admin()) {
        $payment_attr = ($form_action === "ask-add") ? [] : ["disabled" => "disabled"];
        $HD_Form->AddEditSelect(
            _("Account type"),
            "typepaid",
            $typepaid_list,
            "",
            "",
            $payment_attr
        );
    }

    if (is_admin() || $form_action === "add" || $form_action === "ask-add") {
        $HD_Form->AddEditSqlSelect(
            _("Call plan"),
            "tariff",
            new Table("cc_tariffgroup", ["tariffgroupname", "id"], is_admin() ? []
                : ["cc_agent_tariffgroup" => ["cc_tariffgroup.id", "cc_agent_tariffgroup.id_tariffgroup"]]),
            is_admin()
                ? []
                : ["cc_agent_tariffgroup.id_agent" => Agent::id()],
            "",
            [],
            _("Changing the call plan will result in the free minutes or free calls package being reset.")
        );
    }

    $HD_Form->AddEditSqlSelect(
        _("DID group"),
        "id_didgroup",
        new Table("cc_didgroup", ["didgroupname", "id"]),
        [],
        "",
        ["" => _("NOT DEFINED")]
    );

    $HD_Form->AddEditSelect(_("Language"), "language", $language_list);

    $HD_Form->AddEditSelect(
        _("Currency"),
        "currency",
        $currency_list,
        "", _("Currency used at the customer end.")
    );

    $HD_Form->AddEditSelect(_("Status"), "status", $cardstatus_list);

    $HD_Form->AddEditRadio(
        _("Lock"),
        "block",
        getYesNoList(),
        "0",
        _("Enable lock for this account."),
        [],
        _("Choose if you want to enable the lock on this account")
    );

    $lock_attr = ["maxlength" => 10];
    if ($form_action === "ask-add") {
        $lock_attr["value"] = 0;
    }
    $HD_Form->AddEditElement(
        _("Lock PIN"),
        "lock_pin",
        _("Code required to make the call if the lock is active."),
        $lock_attr,
        [Validator::class, "numeric"],
        "",
        "NO"
    );

    $HD_Form->AddEditSelect(
        _("Access"),
        "simultaccess",
        $simultaccess_list,
        "1"
    );

    $HD_Form->AddEditRadio(
        _("Run services"),
        "runservice",
        getYesNoList(),
        "0",
        _("Apply recurring service to this account."),
        [],
        _("Choose if you want to enable the service recurring on this account")
    );

    if (is_admin()) {
        $HD_Form->AddEditElement(
            _("Credit limit"),
            "creditlimit",
            _("Credit limit is only used for the postpaid account."),
            ["maxlength" => 20],
            [Validator::class, "numeric"],
            _("Insert the credit limit"),
            "NO-NULL"
        );
    }

    $HD_Form->AddEditSelect(
        _("Credit limit notification"),
        "credit_notification",
        $limits_notify_list,
        "",
        _("currency : ") . strtoupper(BASE_CURRENCY) . '<BR>' . _("Low credit limit to alert the customer")
    );

    $HD_Form->AddEditRadio(
        _("Enable email notifications"),
        "notify_email",
        getYesNoList(),
        "0",
        _("Enable the notification by mail for this account."),
        [],
        _("Choose if you want to enable the notification by email for this account")
    );

    $HD_Form->AddEditElement(
        _("Notification email"),
        "email_notification",
        "",
        ["maxlength" => 70],
        [Validator::class, "email"],
        _("Insert the email to notify this customer"),
        "NO"
    );

    $HD_Form->AddEditElement(
        _("First use date"),
        "firstusedate",
        "",
        ["maxlength" => 40, "readonly" => "readonly"],
        null,
        "the first use date",
        "NO-NULL",

    );

    $HD_Form->AddEditSelect(
        _("Account expiry"),
        "enableexpire",
        $expire_list,
        "",
        _("Select method of expiry for the account.")
    );

    $exp_attr = ["maxlength" => 40];
    if ($form_action === "ask-add") {
        $exp_attr["value"] = (new DateTime("+10 years"))->format("Y-m-d H:i:s");
    }
    $HD_Form->AddEditElement(
        _("Expiry date"),
        "expirationdate",
        _("please use the format YYYY-MM-DD HH:MM:SS. For instance, '2004-12-31 00:00:00'"),
        $exp_attr,
        [Validator::class, "dateTime"],
        _("Insert the expiration date"),
        "NO-NULL",

    );

    $days_attr = ["maxlength" => 6];
    if ($form_action === "ask-add") {
        $days_attr["value"] = 0;
    }
    $HD_Form->AddEditElement(
        _("Expiry days"),
        "expiredays",
        _("The number of days after which the account will expire."),
        $days_attr,
        [Validator::class, "numeric"],
        _("Insert the number of days after which the account will expire")
    );

    if (!empty($A2B->config["webui"]['buddy_sip_file']) && (is_admin() || Agent::allowed(Agent::ACX_VOIPCONF))) {
        $HD_Form->AddEditRadio(
            _("Create SIP config"),
            "sip_buddy",
            getYesNoList(),
            "0",
            _("Create the SIP config automatically"),
            [],
            _("Choose if you want to enable the SIP account")
        );
    }

    if (!empty($A2B->config["webui"]['buddy_iax_file']) && (is_admin() || Agent::allowed(Agent::ACX_VOIPCONF))) {
        $HD_Form->AddEditRadio(
            _("Create IAX config"),
            "iax_buddy",
            getYesNoList(),
            "0",
            _("Create the IAX config automatically"),
            [],
            _("Choose if you want to enable the IAX account")
        );
    }

    $HD_Form->AddEditElement(
        _("In use"),
        "inuse",
        _("Updated to show the number of concurrent calls in use by this customer. If there are no currently no calls, and the system shows that there are, manually reset this field back to zero."),
        ["maxlength" => 5],
        null,
        "",
        "NO"
    );

    $HD_Form->AddEditSection("Auto Refill");

    $HD_Form->AddEditRadio(
        _("Auto refill"),
        "autorefill",
        getYesNoList(),
        "0",
        _("Define if you want to authorize the autorefill to apply on this accout"),
        [],
        _("Choose if you want to enable the autorefill on this account")
    );

    $balance_attr = ["maxlength" => 30];
    if ($form_action === "ask-add") {
        $balance_attr["value"] = 0;
    }
    $HD_Form->AddEditElement(
        _("Initial balance"),
        "initialbalance",
        _("The initial balance is used by autorefill to reset the current balance to this amount"),
        $balance_attr,
        [Validator::class, "number"],
        _("Insert the amount of the initial balance")
    );

    $HD_Form->AddEditSection(_("Invoice Status"));

    $HD_Form->AddEditSelect(
        _("Invoice day"),
        "invoiceday",
        $invoiceday_list,
        "",
        _("Define the day of the month when the system will generate the customer invoice.")
    );

    $vat_attr = ["maxlength" => 6];
    if ($form_action === "ask-add") {
        $vat_attr["value"] = 0;
    }
    $HD_Form->AddEditElement(
        _("VAT/GST"),
        "vat",
        _("VAT to add on the invoice of this customer. it should be a decimal value '21' this will be for 21% of VAT!"),
        $vat_attr,
        [Validator::class, "number"],
        _("Insert the VAT")
    );

    $HD_Form->AddEditElement(
        _("VAT/GST registration number"),
        "vat_rn",
        "",
        ["maxlength" => 40],
        null,
        _("Insert the VAT registration number of this customer"),
        "NO"
    );

    $HD_Form->AddEditSelect(_("Discount"), "discount", $discount_list);

    $HD_Form->AddEditSection(_("Traffic Targets"));

    $HD_Form->AddEditElement(
        _("Monthly traffic target"),
        "traffic",
        "",
        ["maxlength" => 20],
        [Validator::class, "numeric"],
        _("Insert the traffic per month for this customer"),
        "NO"
    );

    $HD_Form->AddEditTextarea(
        _("Traffic target"),
        "traffic_target",
        "",
        ["rows" => 4],
        _("Insert the target traffic description")
    );

    $HD_Form->AddEditSection(_("Restricted Numbers"));

    $HD_Form->AddEditSelect(
        _("Restrict calls"),
        "restriction",
        $restriction_list,
        "0"
    );

    $HD_Form->AddEditHasMany(
        _("Call restriction list"),
        new Table("cc_restricted_phonenumber", ["id", "number"]),
        "number",
        "id_card",
        [Validator::class, "numeric"]
    );

    $HD_Form->AddEditSection("Caller ID / Customer History");
    $HD_Form->AddEditHasMany(
        _("Caller ID"),
        new Table("cc_callerid", ["id", "cid"]),
        "cid",
        "id_cc_card",
        [Validator::class, "min3Chars"]
    );

    $HD_Form->AddEditHasMany(
        _("Customer history"),
        new Table(
            "cc_card_history",
            ["id", "CONCAT('(', datecreated, ') ', description)"]
        ),
        "description",
        "id_cc_card",
        [Validator::class, "min3Chars"],
        true
    );

    $HD_Form->add_message_intro = "";
    $HD_Form->FG_ADDITIONAL_FUNCTION_BEFORE_EDITION = [FormBO::class, "change_card_lock"];

    if (is_admin()) {
        $HD_Form->FG_ADDITIONAL_FUNCTION_AFTER_ADD = [FormBO::class, "processing_card_add"];
    } else {
        $HD_Form->FG_ADDITIONAL_FUNCTION_AFTER_ADD = [FormBO::class, "processing_card_add_agent"];
        $HD_Form->FG_ADDITIONAL_FUNCTION_AFTER_DELETE = [FormBO::class, "processing_card_del_agent"];
    }
    $HD_Form->FG_ADDITIONAL_FUNCTION_AFTER_EDITION = [FormBO::class, "create_status_log"];

    if ($form_action === "ask-edit" || $form_action === "delete") {
        $result_card = (new Table("cc_card", ["status", "sip_buddy", "iax_buddy"]))
            ->getRow(["id" => $id]);

        if ($form_action === "ask-edit") {
            $HD_Form->FG_EDIT_FORM_HIDDEN_INPUTS["oldstatus"] = $result_card["status"] ?? "";
        } elseif (!USE_REALTIME) {
            $sip_buddy = $result_card['sip_buddy'] ?? "";
            $iax_buddy = $result_card['iax_buddy'] ?? "";
            if ($sip_buddy && $iax_buddy) {
                $key = "sip_iax_changed";
            } elseif ($sip_buddy) {
                $key = "sip_changed";
            } elseif ($iax_buddy) {
                $key = "iax_changed";
            } else {
                $key = "";
            }

            $who = Notification::$UNKNOWN;
            $who_id = "";
            if (is_admin()) {
                $who = Notification::$ADMIN;
                $who_id = Admin::id();
            } elseif (is_agent()) {
                $who = Notification::$AGENT;
                $who_id = Agent::id();
            }
            NotificationsDAO::addNotification($key, Notification::$HIGH, $who, $who_id);
        }
    }
}
