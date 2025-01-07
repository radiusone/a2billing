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

$HD_Form = new FormHandler(
    "cc_card",
    _("Card"),
    "cc_card.id",
    ["cc_card_group" => ["cc_card.id_group", "cc_card_group.id"]]
);

if (is_agent()) {
    $HD_Form->list_query_conditions["id_agent"] = $_SESSION["agent_id"] ?? 0;
}
$HD_Form->search_session_key = 'entity_card_selection';
$HD_Form->FG_FK_DELETE_CONFIRM = true;
$HD_Form->FG_FK_DELETE_ALLOWED = true;
$HD_Form->FG_FK_TABLENAMES = ['cc_iax_buddies', 'cc_sip_buddies', 'cc_callerid', 'cc_card_history', 'cc_status_log'];
$HD_Form->FG_FK_EDITION_CLAUSE = ["id_cc_card", "id_cc_card", "id_cc_card", "id_cc_card", "id_cc_card"];
// DEFINE IF WE WANT TO DELETE THE CARD OR NOT
$HD_Form->FG_FK_WARNONLY = !(DELETE_FK_CARD);

if (DELETE_FK_CARD) {
    // WE WILL DELETE THE FK
    $HD_Form->FG_FK_DELETE_MESSAGE = _("You are going as well to remove all the SIP/IAX accounts & CallerIDs attached to this card! Please confirm that you really want to remove this card ? ");
} else {
    // WE JUST NOTIFY
    $HD_Form->FG_FK_DELETE_MESSAGE = _("You are going to remove a card that have SIP/IAX accounts and/or CallerIDs attached to it ! Please confirm that you really want to remove this card ? ");
}

if (!$popup_select && (is_admin() || has_rights(Agent::ACX_GENERATE_CUSTOMER))) {
    $HD_Form->FG_LIST_ADDING_BUTTON1 = true;
    $HD_Form->FG_LIST_ADDING_BUTTON_LINK1 = "A2B_entity_card_multi.php";
    $HD_Form->FG_LIST_ADDING_BUTTON_ALT1 = $HD_Form->FG_LIST_ADDING_BUTTON_MSG1 = _("Generate Customers");
    $HD_Form->FG_LIST_ADDING_BUTTON_IMG1 = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAQCAYAAAABOs/SAAAAAXNSR0IArs4c6QAAAAZiS0dEAP8A/wD/oL2nkwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB9gJGBI4Of08ag4AAASESURBVDjLxZJbbBRVHIe/MzO7293e7LallIttBbQVaAsVqAoSI2gEIkGJplChiAkUQ1LBhAdREkUoRtAnUDBihBZQlJvhqhQtSgGhgG21xVJAKNDL9rLb2e7szBwfiA0pl8iT/8eTfN/vnP/5Ce5zFoxKeGdQvL1EVe1Iw5L4DUW/0KF9XHKqeen9eMT9yKOw/Fn95UqHRwgbMMKSQNCmKSBlXbvj/RSXMP/rpdTeB/NyvEsy+9nLPdHCqbkEiiqQ2A4wxw2NlxPdUYpo7bTQDYlhCmzFgWEKYZpWbk4/JtyJi4+NUs7fCB65NUfrHZzd1yqO8Kg0d1pYAkDBVpxoqiXa/QYjpixkYmoMV65eo3TzZjRNI6RF44nwudweheZO8zYuzmEWAcvuueqVT0XJEXlFDOstF26G9TEpWH0Io/4wAT1Ii6+DmCgPsY9OYN+K6cSPnMGw1NjbuLDeFh47bmz0rOI9obu+OCE13jd+2qteo/4wAzWNuYULeuRb351pBS+fUk1XAnGRJnGJ/QiYKvjq6DLd5ovTZml34g6sejk4NH2gvDVH6R0ctCL/CF4+RciVQFxiMkMeySAy6SHCzbXsacpsP7BpDTF0ILQIhOYiSjU4smU1314Z3tFW/9ttHL46/IbnfGbBWuOu5UqaXOI4e0N96sGm7VnZ2cPBGYNQNGS4m/fW/chPgQLP4uKllG9ewZ9VldRWVVJXU03f2btZdyDZc7V6J8/lJOJw3+ScspsfStew6vfcCvnwvD36hT3mbX+cOGmLy7at19z0Xbt/fS7Vn81AIpESjrUOYtfV6ez7dCSncXO6HYJdYATB1CG5awdGy24CwTb8AT8pQZNsJRaBoN/MEvKKTqKbDUWC8IbWw/P1nuD457cJw2K219l/Y2/59apL+Pd9zby3FtHqVqnWJeFuMA0wukFt2kbqAwfJyXiMAd4hlFXvpKKqHL/yEolDlpEWCc/2CZK3qJKWrtoFmhra0FFWaCoAfT1dU8fEXNr40dJcfm51c/xvid4sCbdL3m6YROlsHzdq9lHRCHqbIOSHkB/CATBbN5GdnoWlWGQlT8QSYXKHP4Hvry8xOiVVDTbfX3Qzp+Bxxnib1g6IDOT3lGtTxofb7ybXhERzeTha0cJgTadbh5AOhn5z1W3+azhEFC9kLARg8TPrGdQnE2mHMHSJERQcrwGl8TDfzNf5PP2L9T3BqhDK3eRlaZ9QX1ZKRPg6u0pO4IyWRMRBZBLEDgSfv4Pqxl8pPjQHgOKDc6hvOgfCheJQUCIEo9JCVFReR9EcqKrQelr9xut5RuvJ78ZXdg62y88pSnJOCg43OKOgKWYQZ1PmQvpoqstraTxSLq8crxUNx+rsi0drbGF3Kbp2gtSkVFr0K3R0NfHL2XIc3ny8aeNxR8P+tXsZHXdGT/XttF3pTxev23GirKfVDVsLozee6+/8qibrA9neOll1eLwIxYEtkZZp2mZ3wOGNPzMl5XT+m0/eCKa8ss7/L5dfvnd5WNqFQpgOaTtlsH2c7Gqeat2L+9/mH65EU5RAm4B6AAAAAElFTkSuQmCC";
}

if (!$popup_select && (is_admin() || has_rights(Agent::ACX_CREATE_CUSTOMER))) {
    $HD_Form->FG_LIST_ADDING_BUTTON2 = true;
    $HD_Form->FG_LIST_ADDING_BUTTON_LINK2 = "A2B_entity_card.php?form_action=ask-add";
    $HD_Form->FG_LIST_ADDING_BUTTON_ALT2 = $HD_Form->FG_LIST_ADDING_BUTTON_MSG2 = _("Add Customer");
    $HD_Form->FG_LIST_ADDING_BUTTON_IMG2 = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAJ8SURBVDjLpZB/SFNRFMftj5IiW9saIQjGQrBiUagEFWLDydrUhAWzNNuWTZ1oKIUzf4RlzZY6sWyrLZXhfFszQ1eac2SQkYWW0yH0l1AWITSw1IXK+/beK0RBptCFD+fcyz2fc+4NARDyP6x5qInbVVEn5sw2SHdCL2ahQsiey4jhVW9IkBPDKbmfyibN6Rw8oLgrY0MnYaEofgcpPcitWldglLLQQhXqqSKdlIaNm8k8XDnBQWYMa2ZdgS5+O14YyzHVq8eQpQiFCTwUJ4YjX8SH+hh7wapNCQ0qMGdF/gh8/4SZN0Z87a+H13ENk89vwz85AiJ378xYq2ZLUEFjxv5B//t2TA87MT9KUNiZ3D9C4KFKMBz0Cbults1RxzVWoiAWv4ctCPieMsx/tKHzciwE8blPeCLz1jUFPAkRyhW35UWIPfB9noWjLBX2shQGOn898QsRSS/BET66xBWatq0ScE86NoUlORSRyYOYmJpH2xRQ7APy3gEXXgHnewCtsxPFRgXU9acgvyEMiEsOVS4LDsia0xJP6+EcWoLJCxS8JZE7QCK7j0RWFwmlmUCVU4lnviaMfnPD0K+B3CDAkfzwWkbwoTx6adqlxb1mFxS9VFeqo7KbxLkOEmdsVKyRoGu8AV0TjaBXreciDJ4cWhBgBN6KfaTffR3p6hZU988howM4aycht5KQWUgklx1Gj8+Clat7rIkW/P2IcWtB6Uhp1KJSeWsxTjEAJTW6agVHC/m441ZB51Ywxbo+xeoJaCbteWGVV6u5e9JcpsiE1i980eM5flLHAj/RuSCQZy7KaqNR585mOtOR3i//wUagLtdQ/KTH/hdr6PM/RhGjA91Gi1AAAAAASUVORK5CYII=";
}

$HD_Form->list_help_text = create_help(
    _("Customers are listed below by account number. Each row corresponds to one customer, along with information such as their call plan, credit remaining, etc.")
    . "<br/>"
    . is_admin() || (is_agent() && has_rights(Agent::ACX_VOIPCONF))
        ? _("The SIP and IAX buttons create SIP and IAX entries to allow direct VoIP connections to the Asterisk server without further authentication.")
        : ""
);

$HD_Form->help_text = create_help(
    _("Create and edit the properties of each customer. Click CONFIRM DATA at the bottom of the page to save changes.")
);

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
$show_list_temp = explode(',', $A2B->config["webui"]['card_show_field_list']);
foreach ($show_list_temp as $value) {
    $value = strtolower(trim($value));
    if ($value) {
        $show_list[] = $value;
    }
}

// force some in popup view
if ($popup_select) {
    foreach (array_reverse(["username", "lastname", "id_group", "credit"]) as $field) {
        if (!in_array($field, $show_list)) {
            array_unshift($show_list, $field);
        }
    }
}

// loop through the shown data and add the table cells
foreach ($show_list as $field) {
    switch ($field) {
        case "id":
            $HD_Form->AddListValue(_("ID"), "cc_card.id");
            break;
        case "username":
            $HD_Form->AddListValue(_("Account number"), "cc_card.id", $popup_select ? null : [Customer::class, "getUsername"]);
            break;
        case "lastname":
            $HD_Form->AddListValue(_("Last name"), "lastname");
            break;
        case "id_group":
            $HD_Form->AddListValue(_("Group"), "cc_card_group.name");
            break;
        case "credit":
            $HD_Form->AddListValue(_("Balance"), "credit", "get_money");
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
            $HD_Form->AddListMapping(_("Language"), "language", getLanguages());
            break;
        case "inuse":
            $HD_Form->AddListValue(_("In use"), "inuse");
            break;
        case "currency":
            $HD_Form->AddListMapping(_("Currency"), "currency", $currency_list);
            break;
        case "sip_buddy":
            $HD_Form->AddListMapping(_("SIP"), "sip_buddy", $yesno);
            break;
        case "aix_buddy":
            $HD_Form->AddListMapping(_("AIX"), "aix_buddy", $yesno);
            break;
        case "nbused":
            $HD_Form->AddListValue(_("Calls"), "nbused");
            break;
        case "firstname":
            $HD_Form->AddListValue(_("First name"), "firstname");
            break;
        case "email":
            $HD_Form->AddListValue(_("Email"), "email");
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
$column_count = count($HD_Form->FG_QUERY_COLUMN_LIST);

// need these in a fixed position for action button links
$HD_Form->AddListHiddenValue("cc_card.id AS id_")
    ->AddListHiddenValue("username AS username_")
    ->AddListHiddenValue("useralias AS alias_");

$HD_Form->FG_FILTER_ENABLE = true;
$HD_Form->FG_FILTER_COLUMN = 'username';
$HD_Form->FG_FILTER_LABEL = _('Account Number');

$HD_Form->FG_ENABLE_ADD_BUTTON = true;
$HD_Form->FG_ENABLE_EDIT_BUTTON = !$popup_select && ((is_admin() && has_rights(Admin::ACX_MODIFY_CUSTOMERS)) || (is_agent() && has_rights(Agent::ACX_EDIT_CUSTOMER)));
$HD_Form->FG_ENABLE_DELETE_BUTTON = !$popup_select && ((is_admin() && has_rights(Admin::ACX_MODIFY_CUSTOMERS)) || (is_agent() && has_rights(Agent::ACX_DELETE_CUSTOMER)));

$HD_Form->FG_LOCATION_AFTER_ADD = "?id=";
$HD_Form->FG_LOCATION_AFTER_EDIT = "?id=";
$HD_Form->FG_LOCATION_AFTER_DELETE = "?id=";

$form_action ??= "list";

if ($popup_select) {
    // popup window
    $HD_Form->FG_LIST_VIEW_PAGE_SIZE = 6;
    $HD_Form->AddListSelectActionButton(
        $popup_select == 1 ? "javascript:sendValue('|param|', '|col1|')" : "javascript:sendValue('|col1|')",
    );
} elseif ($form_action === "list") {
    $HD_Form->export_session_key = "pr_export_entity_card";
    $HD_Form->FG_EXPORT_CSV = is_admin();
    $HD_Form->FG_EXPORT_XML = is_admin();
    // Code here for adding the fields in the Export File
    $HD_Form->FG_EXPORT_FIELD_LIST = explode(",", $A2B->config['webui']['card_export_field_list']);
    $HD_Form->FG_LIST_VIEW_PAGE_SIZE = 10;
    $HD_Form->search_form_enabled = true;

    $HD_Form->search_form_title = _('Define specific criteria to search for cards created.');
    $HD_Form->AddSearchDateInput(_("Creation date"), "creationdate");
    $HD_Form->AddSearchDateInput(_("First use date"), "firstusedate");
    $HD_Form->AddSearchTextInput(_("ACCOUNT NUMBER"), 'username');
    $HD_Form->AddSearchTextInput(_("EMAIL"), 'email');
    $HD_Form->AddSearchTextInput(_("LASTNAME"), 'lastname');
    $HD_Form->AddSearchTextInput(_("FIRSTNAME"), 'firstname');
    $HD_Form->AddSearchTextInput(_("LOGIN"), 'useralias');
    $HD_Form->AddSearchTextInput(_("MACADDRESS"), 'mac_addr');
    $HD_Form->AddSearchTextInput(_("COMPANY NAME"), 'company_name');
    $HD_Form->AddSearchComparisonInput(_("CUSTOMER ID"), 'id1', 'id2', 'cc_card.id');
    $HD_Form->AddSearchComparisonInput(_("SERIAL"), 'serial1', 'serial2', 'serial');
    $HD_Form->AddSearchComparisonInput(_("CREDIT"), 'credit1', 'credit2', 'credit');
    $HD_Form->AddSearchComparisonInput(_("INUSE"), 'inuse1', 'inuse2', 'inuse');

    $HD_Form->AddSearchSqlSelectInput(_("SELECT SERIE"), "id_seria", new Table("cc_card_seria", ["id", "name"]), "name");
    $HD_Form->AddSearchSelectInput(_("SELECT LANGUAGE"), "language", $language_list);
    $HD_Form->AddSearchSqlSelectInput(_("SELECT TARIFF"), "tariff", new Table("cc_tariffgroup", ["id", "tariffgroupname"]), "tariffgroupname");
    $HD_Form->AddSearchSelectInput(_("SELECT STATUS"), "status", $cardstatus_list);
    $HD_Form->AddSearchSelectInput(_("SELECT ACCESS"), "simultaccess", $simultaccess_list);
    $HD_Form->AddSearchSqlSelectInput(_("SELECT GROUP"), "id_group", new Table("cc_card_group", ["id", "name"]), "name");
    $HD_Form->AddSearchSelectInput(_("SELECT CURRENCY"), "currency", getCurrenciesList());
    $HD_Form->AddSearchSelectInput(_("SELECT PAYMENT TYPE"), "typepaid", $typepaid_list);

    $HD_Form->FG_ENABLE_INFO_BUTTON = true;
    $HD_Form->FG_INFO_BUTTON_LINK = "A2B_info_card.php?id=";
    $FN_COLUMN_ID = "col" . ($column_count);//get numbers of rows for needed fields
    $FN_COLUMN_USERNAME = "col" . ($column_count + 1);
    $FN_COLUMN_ALIAS = "col" . ($column_count + 2);

    if (!empty($A2B->config["webui"]['buddy_sip_file']) && (is_admin() || (is_agent() && has_rights(Agent::ACX_VOIPCONF)))) {
        $HD_Form->AddListActionButton(
            "A2B_entity_friend.php?voip_type=sip&form_action=add_sip&id_cc_card=|$FN_COLUMN_ID|&cardnumber=|$FN_COLUMN_USERNAME|&useralias=|$FN_COLUMN_ALIAS|",
            _("Auto Create SIP Config"),
            "data:image/gif;base64,R0lGODlhFwALAKEDAAAAAJycnPPZ3f///yH5BAEAAAMALAAAAAAXAAsAAAItXI6pa+IPozyyWgqBFhrwDWGPNzoe+YgmSHaRWnJmBbs2HVh6mO97YFgIhYMCADs="
        );
    }
    if (!empty($A2B->config["webui"]['buddy_iax_file']) && (is_admin() || (is_agent() && has_rights(Agent::ACX_VOIPCONF)))) {
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
            "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAJ0SURBVDjLlZPdT9JRGMe5qFu2Lrt1a63LWv9ATRdN5xvLsnLRipzZpIVpigjyIs3XAOUHgopoWkggP5QXSRJwJQmtm/IlAWtt3XXTfubS+nZ+P1eby6ldPGdn5+zzfb7Pc57DA8DbL9rjrYxuVsXf7W5fuC2mYawpE7QRJZpDDfz/EngYVTN9qR4EPvlgXjCiKVCPWvou/0ACxDJjSbIwDefqMPxrEzC87IDUW4Pq8Vv8PQVaX7Qw5qQRgY9ePP0wDMeSFfWTUkxmPeiI61DlFOP6SAV/VwFtRMFQCwb4CdwW10IbVcK+aMHgohmPlwdBZ11oCctx1X5p/R8B9Uzzuum1ntj1Iv1tGRtb3zH2dgSa2eZtOOOCMizD5cGyzR0lGBNdx1TP5T96E4+4WttiWg6mYr3Ifk1DF1PBmxmHYlrGZkbFUDku2oSHOAFjolOuIpZ65rs5+MmKg9hWcJlZWB1UbsOhRjYz5r/MoSn4AKWWQg0nwFoyzndhijRobGWIq3XgPQU1sa2LqjCRHoc81IBK9w0OnvscRWQtBGFfEc4b8o7wNDMKOwnY3lDwZZ+h1idB/zsThpf6CezkstVN3yNwHFMrNGqCVRvlA2UQ6POkud1nTvE0EcVR1gU7JNSCnrPrWLRtw+RM7BKBXnJDP9eOYqogVNAj0Av0uTk7mtjov2+1p2yQ0hIYXnXCs+qEzF+HC9YSyIiIsK84XWTKP5tvPHdi11GupSXHW8JNW+FMAHdclSCCKDEX/iKdDgotRY17jTu31LhvHybT5RGPin5K3NWs1c0yW+lp0umc/T7b383NUdHJa44rSfJU+Qf54n/iNzi8zBtL0z1zAAAAAElFTkSuQmCC"
        );
        $HD_Form->AddListActionButton(
            "A2B_entity_subscriber.php?form_action=ask-add&id=|$FN_COLUMN_ID|",
            _("Add New Subscription to this Card"),
            "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAJcSURBVDjLbZPfa85xFMdf36/nebbGBcLaMmtM2DybElsjQm5c+PEnoKSQCzfEnZtduLRdSJFc4UK5sEizSLtQNCYXJMXKlMeG7fv5cc5x8X02w06dzqdPn/M673M6n8TM2H/6WruZ3TaoYPQYhhlgRh5s1lUCwU18GLpxfjVAgfzBMYP15bZVyfjXCcxmkiCHKabwfeIX085QK7RQtRwAO8ptTcmujiZWNZSxnICa5lU1r758cR11tQW2HTjOXwDMlpTbm7n//B2VyhSmCoDOqDDD1Pg+OUXmPHOt2gJJoVRg7cZmWuuXIgJmiqohYogqUY3pLHDrzuP5AIaI8nF8klJaJMsygvNEze8jCygUSyxbWIOazQMAVJQoAecch7a25vJzdZgZ1wffEmqL/JP/R0EQRUIkSsrNx29wIRLFkKhEEoqlEj7mQ50XEKPiQ8ArWFpDUixCamiiqCpeErz8D0irQyREIWYRF4QsClkQXIi4KDgvuKlnfP50iZZ1A5R3j7PvXOeFvxWIElzABcnbEcOLEkWR6ac01r9h84YuVi5dy+DoXYZfP7nYfbJxcTq3heBzgI/KdBB8EFxUpn48YtP6TiQVOhv2Ikmgu9wDcCKdWRgfhegiLihZEELQXL4TKj+/UEwWsX/DKQDO7LnCmhUdALWzMxAxxAsu5J6FiHOK98q3yQqjY8/ofXgYgN4Hh3k/PgKQzc6ANGVLVweWJIgYUQytxsQdZHhkgJ6O7dx71U8pKfD05RBAX2Jm7DzSd9WMo/nPm7P/GFTP1A5BzQtIPMAPoH/48tjZ3wRCz4QMKdc8AAAAAElFTkSuQmCC"
        );
        $HD_Form->AddListActionButton(
            "A2B_mass_mail.php?form_action=ask-add&id=|$FN_COLUMN_ID|",
            _("Email to this Customer"),
            "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAITSURBVBgZpcHLThNhGIDh9/vn7/RApwc5VCmFWBPi1mvwAlx7BW69Afeu3bozcSE7E02ILjCRhRrds8AEbKVS2gIdSjvTmf+TYqLu+zyiqszDMCf75PnnnVwhuNcLpwsXk8Q4BYeSOsWpkqrinJI6JXVK6lSRdDq9PO+19vb37XK13Hj0YLMUTVVyWY//Cf8IVwQEGEeJN47S1YdPo4npDpNmnDh5udOh1YsZRcph39EaONpnjs65oxsqvZEyTaHdj3n2psPpKDLBcuOOGUWpZDOG+q0S7751ObuYUisJGQ98T/Ct4Fuo5IX+MGZr95jKjRKLlSxXxFxOEmaaN4us1Upsf+1yGk5ZKhp8C74H5ZwwCGO2drssLZZo1ouIcs2MJikz1oPmapHlaoFXH1oMwphyTghyQj+MefG+RblcoLlaJG/5y4zGCTMikEwTctaxXq/w9kuXdm9Cuzfh9acujXqFwE8xmuBb/hCwl1GKAnGccDwIadQCfD9DZ5Dj494QA2w2qtQW84wmMZ1eyFI1QBVQwV5GiaZOpdsPaSwH5HMZULi9UmB9pYAAouBQbMHHrgQcnQwZV/KgTu1o8PMgipONu2t5KeaNiEkxgAiICDMCCFeEK5aNauAOfoXx8KR9ZOOLk8P7j7er2WBhwWY9sdbDeIJnwBjBWBBAhGsCmiZxPD4/7Z98b/0QVWUehjkZ5vQb/Un5e/DIsVsAAAAASUVORK5CYII="
        );
    }
} elseif ((is_admin() && has_rights(Admin::ACX_MODIFY_CUSTOMERS)) || (is_agent() && has_rights(Agent::ACX_EDIT_CUSTOMER))) {
    if (empty($cardnumber_length) || !is_numeric($cardnumber_length)) {
        $cardnumber_length = min($A2B->cardnumber_range);
    }
    $array_card_generated = gen_card_with_alias($cardnumber_length);

    $acct_attr = ["readonly" => "readonly"];
    if ($form_action === "ask-add") {
        $acct_attr["value"] = $array_card_generated[0];
    }
    $HD_Form->AddEditElement(
        _("ACCOUNT NUMBER"),
        "username",
        '',
        $acct_attr,
        [Validator::class, "numeric"],
        _("Insert the account number"),
        _("Customer Information")
    );

    $login_attr = ["maxlength" => 40];
    if ($form_action === "ask-add") {
        $login_attr["value"] = $array_card_generated[1];
    }
    $HD_Form->AddEditElement(
        _("WEBUI LOGIN"),
        "useralias",
        "",
        $login_attr,
        [Validator::class, "min5Chars"],
        _("Insert the webui login")
    );

    $pass_attr = ["maxlength" => 20];
    if ($form_action === "ask-add") {
        $pass_attr["value"] = MDP_NUMERIC(5) . MDP_STRING(15);
    }
    $HD_Form->AddEditElement(
        _("WEBUI PASSWORD"),
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
        _("BALANCE"),
        "credit",
        _("currency : ") . strtoupper(BASE_CURRENCY),
        $balance_attr,
        [Validator::class, "number"],
        _("Insert the number of credit")
    );

    $HD_Form->AddEditSqlSelect(
        _("CUSTOMER GROUP"),
        "id_group",
        new Table("cc_card_group", ["id", "name"]),
        is_admin() ? [] : ["id_agent" => $_SESSION["agent_id"]]
    );

    $HD_Form->AddEditSqlSelect(
        _("SERIES"),
        "id_seria",
        new Table("cc_card_seria", ["id", "name"]),
        [],
        "",
        [-1 => _("NOT DEFINED")]
    );

    $HD_Form->AddEditElement(
        _("LAST NAME"),
        "lastname",
        "",
        ["maxlength" => 50],
        [Validator::class, "min2Chars"],
        _("Insert the lastname of the customers"),
        _("Personal Information"),
        "NO"
    );

    $HD_Form->AddEditElement(
        _("FIRST NAME"),
        "firstname",
        "",
        ["maxlength" => 50],
        [Validator::class, "min2Chars"],
        _("Insert the firstname of the customers"),
        "",
        "NO"
    );

    $HD_Form->AddEditElement(
        _("EMAIL"),
        "email",
        "",
        ["maxlength" => 70],
        [Validator::class, "email"],
        _("Insert the email"),
        "",
        "NO"
    );

    $HD_Form->AddEditElement(
        _("ADDRESS"),
        "address",
        "",
        ["maxlength" => 100],
        [Validator::class, "min3Chars"],
        _("Insert the address of the customers"),
        "",
        "NO"
    );

    $HD_Form->AddEditElement(
        _("CITY"),
        "city",
        "",
        ["maxlength" => 40],
        [Validator::class, "min3Chars"],
        _("Insert the city"),
        "",
        "NO"
    );

    $HD_Form->AddEditElement(
        _("STATE/PROVINCE"),
        "state",
        "",
        ["maxlength" => 40],
        [Validator::class, "min2Chars"],
        _("Insert the state"),
        "",
        "NO"
    );

    $HD_Form->AddEditSqlSelect(
        _("COUNTRY"),
        "country",
        new Table("cc_country", ["countrycode", "countryname"]),
        [],
        $form_action === "ask-add" ? "USA" : ""
    );

    $HD_Form->AddEditElement(
        _("ZIP/POSTAL CODE"),
        "zipcode",
        "",
        ["maxlength" => 20],
        [Validator::class, "min3Chars"],
        _("Insert the zipcode"),
        "",
        "NO"
    );

    $HD_Form->AddEditElement(
        _("PHONE NUMBER"),
        "phone",
        "",
        ["maxlength" => 20],
        [Validator::class, "min8DigitLike"],
        _("Insert the phone number of the customers"),
        "",
        "NO"
    );

    $HD_Form->AddEditElement(
        _("FAX NUMBER"),
        "fax",
        "",
        ["maxlength" => 20],
        null,
        _("Insert the fax number of the customers"),
        "",
        "NO"
    );

    $HD_Form->AddEditElement(
        _("COMPANY NAME"),
        "company_name",
        "",
        ["maxlength" => 50],
        null,
        _("Insert Company name of this customer"),
        "",
        "NO"
    );

    $HD_Form->AddEditElement(
        _("COMPANY WEBSITE"),
        "company_website",
        "",
        ["maxlength" => 60],
        null,
        _("Insert the company website of this customer"),
        "",
        "NO"
    );

    if ($form_action !== "edit" && is_admin()) {
        $payment_attr = ($form_action === "ask-add") ? [] : ["disabled" => "disabled"];
        $HD_Form->AddEditSelect(
            _("PAYMENT TYPE"),
            "typepaid",
            $typepaid_list,
            "",
            "",
            $payment_attr,
            "",
            _("Customer Status")
        );
    }

    if (is_admin() || $form_action === "add" || $form_action === "ask-add") {
        $HD_Form->AddEditSqlSelect(
            _("CALL PLAN"),
            "tariff",
            new Table("cc_tariffgroup", ["id", "tariffgroupname"], is_admin() ? []
                : ["cc_agent_tariffgroup" => ["cc_tariffgroup.id", "cc_agent_tariffgroup.id_tariffgroup"]]),
            is_admin()
                ? []
                : ["cc_agent_tariffgroup.id_agent" => $_SESSION["agent_id"]],
            "",
            [],
            _("Changing the call plan will result in the free minutes or free calls package being reset.")
        );
    }

    $HD_Form->AddEditSqlSelect(
        _("DIDGROUP"),
        "id_didgroup",
        new Table("cc_didgroup", ["id", "didgroupname"]),
        [],
        "",
        [-1 => _("NOT DEFINED")]
    );

    $tz_val = "";
    $timezone_list = get_timezones();
    if ($form_action === 'ask-add') {
        $tz_val = array_search(SERVER_GMT, array_column($timezone_list, 1));
    }
    $timezone_list = array_combine(
        array_keys($timezone_list),
        array_column($timezone_list, 2)
    );

    $HD_Form->AddEditSelect(
        _("TIMEZONE"),
        "id_timezone",
        $timezone_list,
        $tz_val
    );

    $HD_Form->AddEditSelect(_("LANGUAGE"), "language", $language_list);

    $HD_Form->AddEditSelect(
        _("CURRENCY"),
        "currency",
        $currency_list,
        "", _("Currency used at the customer end.")
    );

    $HD_Form->AddEditSelect(_("STATUS"), "status", $cardstatus_list);

    $HD_Form->AddEditRadio(
        _("LOCK"),
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
        _("LOCK PIN"),
        "lock_pin",
        _("Code required to make the call if the lock is active."),
        $lock_attr,
        [Validator::class, "numeric"],
        "",
        "",
        "NO"
    );

    $HD_Form->AddEditSelect(
        _("SIMULTANEOUS ACCESS"),
        "simultaccess",
        $simultaccess_list,
        "1"
    );

    $HD_Form->AddEditRadio(
        _("RUN SERVICE"),
        "runservice",
        getYesNoList(),
        "0",
        _("Apply recurring service to this account."),
        [],
        _("Choose if you want to enable the service recurring on this account")
    );

    if (is_admin()) {
        $HD_Form->AddEditElement(
            _("CREDIT LIMIT"),
            "creditlimit",
            _("Credit limit is only used for the postpaid account."),
            ["maxlength" => 20],
            [Validator::class, "numeric"],
            _("Insert the credit limit"),
            "",
            "NO-NULL"
        );
    }

    $HD_Form->AddEditSelect(
        _("CREDIT LIMIT NOTIFICATION"),
        "credit_notification",
        $limits_notify_list,
        "",
        _("currency : ") . strtoupper(BASE_CURRENCY) . '<BR>' . _("Low credit limit to alert the customer")
    );

    $HD_Form->AddEditRadio(
        _("PERMITTED NOTIFICATIONS BY MAIL"),
        "notify_email",
        getYesNoList(),
        "0",
        _("Enable the notification by mail for this account."),
        [],
        _("Choose if you want to enable the notification by email for this account")
    );

    $HD_Form->AddEditElement(
        _("EMAIL NOTIFICATION"),
        "email_notification",
        "",
        ["maxlength" => 70],
        [Validator::class, "email"],
        _("Insert the email to notify this customer"),
        "",
        "NO"
    );

    $HD_Form->AddEditSqlSelect(
        _("CAMPAIGN"),
        "id_campaign",
        new Table("cc_campaign", ["id", "name"]),
        [],
        "",
        [-1 => _("NOT DEFINED")]
    );

    $HD_Form->AddEditElement(
        _("FIRST USE DATE"),
        "firstusedate",
        "",
        ["maxlength" => 40, "readonly" => "readonly"],
        null,
        "the first use date",
        "",
        "NO-NULL",

    );

    $HD_Form->AddEditSelect(
        _("ENABLE EXPIRY"),
        "enableexpire",
        $expire_list,
        "",
        _("Select method of expiry for the account.")
    );

    $comp_zero = $comp_date_plus = '';
    if (isset($form_action) && $form_action === "ask-add") {
        $comp_zero = "value='0'";
        $comp_date_plus = 'value="' . (new DateTime("+10 years"))->format("Y-m-d H:i:s") . '"';
    }
    $exp_attr = ["maxlength" => 40];
    if ($form_action === "ask-add") {
        $exp_attr["value"] = (new DateTime("+10 years"))->format("Y-m-d H:i:s");
    }
    $HD_Form->AddEditElement(
        _("EXPIRY DATE"),
        "expirationdate",
        _("please use the format YYYY-MM-DD HH:MM:SS. For instance, '2004-12-31 00:00:00'"),
        $exp_attr,
        [Validator::class, "dateTime"],
        _("Insert the expiration date"),
        "",
        "NO-NULL",

    );

    $days_attr = ["maxlength" => 6];
    if ($form_action === "ask-add") {
        $days_attr["value"] = 0;
    }
    $HD_Form->AddEditElement(
        _("EXPIRY DAYS"),
        "expiredays",
        _("The number of days after which the account will expire."),
        $days_attr,
        [Validator::class, "numeric"],
        _("Insert the number of days after which the account will expire")
    );

    if (!empty($A2B->config["webui"]['buddy_sip_file']) && (is_admin() || has_rights(Agent::ACX_VOIPCONF))) {
        $HD_Form->AddEditRadio(
            _("CREATE SIP CONFIG"),
            "sip_buddy",
            getYesNoList(),
            "0",
            _("Create the SIP config automatically"),
            [],
            _("Choose if you want to enable the SIP account")
        );
    }

    if (!empty($A2B->config["webui"]['buddy_iax_file']) && (is_admin() || has_rights(Agent::ACX_VOIPCONF))) {
        $HD_Form->AddEditRadio(
            _("CREATE IAX CONFIG"),
            "iax_buddy",
            getYesNoList(),
            "0",
            _("Create the IAX config automatically"),
            [],
            _("Choose if you want to enable the IAX account")
        );
    }

    $HD_Form->AddEditElement(
        _("MAC ADDRESS"),
        "mac_addr",
        _("FORMAT: 00-08-74-4C-7F-1D"),
        ["maxlength" => 17],
        null,
        _("Insert the MAC address customers' device"),
        "",
        "NO"
    );

    $HD_Form->AddEditElement(
        _("IN USE"),
        "inuse",
        _("Updated to show the number of concurrent calls in use by this customer. If there are no currently no calls, and the system shows that there are, manually reset this field back to zero."),
        ["maxlength" => 5],
        null,
        "",
        "",
        "NO"
    );

    if (is_admin()) {
        $HD_Form->AddEditElement(
            _("MAX CONCURRENT CALLS"),
            "max_concurrent",
            _("Max concurrent calls (Feature not implemented)"),
            ["maxlength" => 5],
            null,
            "",
            "",
            "NO"
        );
    }

    $HD_Form->AddEditRadio(
        _("AUTOREFILL"),
        "autorefill",
        getYesNoList(),
        "0",
        _("Define if you want to authorize the autorefill to apply on this accout"),
        [],
        _("Choose if you want to enable the autorefill on this account"),
        _("AUTOREFILL")
    );

    $balance_attr = ["maxlength" => 30];
    if ($form_action === "ask-add") {
        $balance_attr["value"] = 0;
    }
    $HD_Form->AddEditElement(
        _("INITIAL BALANCE"),
        "initialbalance",
        _("The initial balance is used by autorefill to reset the current balance to this amount"),
        $balance_attr,
        [Validator::class, "number"],
        _("Insert the amount of the initial balance")
    );

    $HD_Form->AddEditSelect(
        _("INVOICE DAY"),
        "invoiceday",
        $invoiceday_list,
        "",
        _("Define the day of the month when the system will generate the customer invoice."),
        [],
        "",
        _("Invoice Status")
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
        _("VAT/GST REGISTRATION NUMBER "),
        "vat_rn",
        "",
        ["maxlength" => 40],
        null,
        _("Insert the VAT registration number of this customer"),
        "",
        "NO"
    );

    $HD_Form->AddEditSelect(_("DISCOUNT"), "discount", $discount_list);

    $HD_Form->AddEditElement(
        _("TRAFFIC PER MONTH"),
        "traffic",
        "",
        ["maxlength" => 20],
        [Validator::class, "numeric"],
        _("Insert the traffic per month for this customer"),
        _("TARGET TRAFFIC"),
        "NO"
    );

    $HD_Form->AddEditTextarea(
        _("TARGET TRAFFIC"),
        "traffic_target",
        "",
        ["rows" => 4],
        null,
        _("Insert the target traffic description")
    );

    $HD_Form->AddEditSelect(
        _("RESTRICTION"),
        "restriction",
        $restriction_list,
        "0",
        "",
        [],
        "",
        _("RESTRICTED NUMBERS")
    );

    $HD_Form->AddEditHasMany(
        _("RESTRICTED NUMBERS"),
        new Table("cc_restricted_phonenumber", ["id", "number"]),
        "number",
        "id_card",
        "",
        [Validator::class, "numeric"]
    );

    $HD_Form->AddEditHasMany(
        _("CALLERID"),
        new Table("cc_callerid", ["id", "cid"]),
        "cid",
        "id_cc_card",
        _("CALLERID / CUSTOMER HISTORY"),
        [Validator::class, "min3Chars"]
    );

    $HD_Form->AddEditHasMany(
        _("CUSTOMER HISTORY"),
        new Table(
            "cc_card_history",
            ["id", "CONCAT('(', datecreated, ') ', description)"]
        ),
        "description",
        "id_cc_card",
        "",
        [Validator::class, "min3Chars"],
        true
    );

    $HD_Form->FG_INTRO_TEXT_ADITION = "";
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
            ->getRow($HD_Form->DBHandle, ["id" => $id]);

        if ($form_action === "ask-edit") {
            $HD_Form->FG_EDIT_FORM_HIDDEN_INPUTS["oldstatus"] = $result_card["status"] ?? "";
        } elseif (USE_REALTIME) {
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
            $who_id = -1;
            if (is_admin()) {
                $who = Notification::$ADMIN;
                $who_id = $_SESSION["admin_id"];
            } elseif (is_agent()) {
                $who = Notification::$AGENT;
                $who_id = $_SESSION["agent_id"];
            }
            NotificationsDAO::addNotification($key, Notification::$HIGH, $who, $who_id);
        }
    }
}
