<?php

use A2billing\Agent;
use A2billing\Customer;
use A2billing\Forms\FormHandler;
use A2billing\Table;

$HD_Form = new FormHandler(
    "cc_call",
    gettext("CDR"),
    "cc_call.id",
    [
        "cc_trunk" => ["LEFT OUTER", "cc_call.id_trunk", "cc_trunk.id_trunk"],
        "cc_ratecard" => ["LEFT OUTER", "cc_call.id_ratecard", "cc_ratecard.id"],
        "cc_card" => ["LEFT OUTER", "cc_call.card_id", "cc_card.id"],
        "cc_card_group" => ["cc_card.id_group", "cc_card_group.id"],
    ]
);

$dialstatus_list = getDialStatusList();
$calltype_list = [
    _("STANDARD"),
    _("SIP/IAX"),
    _("DIDCALL"),
    _("DID_VOIP"),
    _("CALLBACK"),
    _("PREDICT"),
    _("AUTO DIALER"),
    _("DID-ALEG"),
];

$HD_Form->list_query_columns = array_filter([
    "cc_call.starttime",
    is_admin() || is_customer() || Agent::allowed(Agent::ACX_SEE_CUSTOMERS_CALLERID) ? "cc_call.src" : null,
    is_admin() || is_agent() ? "cc_call.dnid" : null,
    "cc_call.calledstation",
    "cc_call.destination AS dest",
    is_admin() ? "cc_ratecard.buyrate" : null,
    "cc_ratecard.rateinitial",
    "cc_call.sessiontime",
    is_admin() || is_agent() ? "cc_call.card_id" : null,
    is_admin() ? "cc_trunk.trunkcode" : null,
    "cc_call.terminatecauseid",
    "cc_call.sipiax",
    is_admin() ? "cc_call.buycost" : null,
    "cc_call.sessionbill",
    is_admin()
        ? "CASE WHEN cc_call.sessionbill != 0 THEN ((cc_call.sessionbill - cc_call.buycost) / cc_call.sessionbill) * 100 ELSE NULL END AS margin"
        : null,
    is_admin()
        ? "CASE WHEN cc_call.buycost != 0 THEN ((cc_call.sessionbill - cc_call.buycost) / cc_call.buycost) * 100 ELSE NULL END AS markup"
        : null,
    "cc_call.id",
    "cc_trunk.id_provider",
    "cc_trunk.id_trunk AS trunk_id",
]);

$DBHandle = DbConnect();

$HD_Form->AddListValue(_("Date"), "cc_call.starttime");
if (is_admin() || is_customer() || Agent::allowed(Agent::ACX_SEE_CUSTOMERS_CALLERID)) {
    $HD_Form->AddListValue(_("Caller ID"), "src", "format_phone_number");
}
if (is_admin() || is_agent()) {
    $HD_Form->AddListValue(_("DNID"), "dnid", "format_phone_number");
}
$HD_Form->AddListValue(_("Called number"), "calledstation", "format_phone_number");
$HD_Form->AddListSqlMapping(_("Destination"), "cc_call.destination", new Table("cc_prefix", ["prefix", "destination"]));
if (is_admin()) {
    $HD_Form->AddListValue(_("Buy rate"), "buyrate", "get_money_precise");
}
$HD_Form->AddListValue(_("Sell rate"), "rateinitial", "get_money_precise");
$HD_Form->AddListValue(_("Duration"), "sessiontime", "get_minute");
if (is_admin() || is_agent()) {
    $HD_Form->AddListValue(_("Account"), "card_id", [Customer::class, "getUsername"]);
}
if (is_admin()) {
    $HD_Form->AddListValue(_("Trunk"), "trunkcode");
}
$HD_Form->AddListMapping(_("Disposition"), "terminatecauseid", $dialstatus_list);
$HD_Form->AddListMapping(_("Call type"), "sipiax", $calltype_list);
if (is_admin()) {
    $HD_Form->AddListValue(_("Buy"), "buycost", "get_money_precise");
}
$HD_Form->AddListValue(_("Sell"), "sessionbill", "get_money_precise");
if (is_admin()) {
    $HD_Form->AddListValue(_("Margin"), "CASE WHEN cc_call.sessionbill != 0 THEN ((cc_call.sessionbill - cc_call.buycost) / cc_call.sessionbill) * 100 ELSE NULL END AS margin", "get_percent");
    $HD_Form->AddListValue(_("Markup"), "CASE WHEN cc_call.buycost != 0 THEN ((cc_call.sessionbill - cc_call.buycost) / cc_call.buycost) * 100 ELSE NULL END AS markup", "get_percent");
}
$HD_Form->FG_ENABLE_DELETE_BUTTON = is_admin() || (is_agent() && Agent::allowed(Agent::ACX_DELETE_CUSTOMER));
$HD_Form->FG_DELETE_BUTTON_LINK = "A2B_entity_call.php?form_action=ask-delete&id=";

if ($A2B->config['webui']['link_audio_file'] ?? false) {
    // TODO: figure out how this works, move it into this file with custom button
    $HD_Form->AddListValue(_("Audio"), "uniqueid", "get_monitorfile_link", [], false);
    $HD_Form->list_query_columns[] = 'cc_call.uniqueid';
}

$HD_Form->FG_LIST_VIEW_PAGE_SIZE = 25;

$HD_Form->CV_TITLE_TEXT = _("Call Logs");

$HD_Form->list_query_order_columns = ["cc_call.starttime"];
$HD_Form->list_query_order_direction = "DESC";

$HD_Form->search_form_enabled = true;
$HD_Form->search_session_key = 'call_log_selection';
$HD_Form->search_form_title = gettext('Define specific criteria to search for call records');

$HD_Form->AddSearchDateInput(_("Date"), "cc_call.starttime");
if (is_admin() || is_agent()) {
    $HD_Form->AddSearchPopupInput(_("Enter the customer ID"), "card_id", "A2B_entity_card.php");
}
if (is_admin()) {
    $HD_Form->AddSearchPopupInput(_("Enter the customer number"), "username", "A2B_entity_card.php", 2);
}
$HD_Form->AddSearchPopupInput(_("Call Plan"), "id_tariffgroup", "A2B_entity_tariffgroup.php", 2);
if (is_admin()) {
    $HD_Form->AddSearchPopupInput(_("Provider"), "id_provider", "A2B_entity_provider.php", 2);
    $HD_Form->AddSearchPopupInput(_("Trunk"), "cc_call.id_trunk", "A2B_entity_trunk.php", 2);
}
if (is_admin() || is_agent()) {
    $HD_Form->AddSearchPopupInput(_("Rate"), "id_ratecard", "A2B_entity_def_ratecard.php", 2);
}
$HD_Form->AddSearchTextInput(_("Called Number"), "calledstation");
if (is_admin() || Agent::allowed(Agent::ACX_SEE_CUSTOMERS_CALLERID)) {
    $HD_Form->AddSearchTextInput(_("Source Number"), "src");
}
if (is_admin() || is_agent()) {
    $HD_Form->AddSearchTextInput(abbr(_("DNID"), _("???")), "dnid");
}
$HD_Form->AddSearchSelectInput(_("Disposition"), "terminatecauseid", $dialstatus_list);
$HD_Form->AddSearchSelectInput(_("Call type"), "sipiax", $calltype_list);
/** TODO: find some way to intercept display of records to apply these options
$HD_Form->FG_FILTER_SEARCH_FORM_SELECT_INPUTS[] = [_("Currency"), false, "choose_currency", $currencies_list];
$HD_Form->FG_FILTER_SEARCH_FORM_SELECT_INPUTS[] = [_("Time unit"), false, "choose_timeunit", [["min", _("Minutes")], ["sec", _("Seconds")]]];
 */
$HD_Form->search_delete_enabled = false;

$HD_Form->prepare_list_subselection("list");
if (empty($HD_Form->list_query_conditions)) {
    $date = (new DateTime("-1 day"))->format("Y-m-d H:i:s");
    $HD_Form->list_query_conditions["cc_call.starttime"] = [">=", $date];
    $HD_Form->list_query_conditions["terminatecauseid"] = 1;
}
if (is_agent()) {
    $HD_Form->list_query_conditions["cc_card_group.id_agent"] = Agent::id();
} elseif (is_customer()) {
    $HD_Form->list_query_conditions["cc_call.card_id"] = Customer::id();
}
