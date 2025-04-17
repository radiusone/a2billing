<?php

use A2billing\Customer;
use A2billing\Forms\FormHandler;

$HD_Form = new FormHandler(
    "cc_card_history",
    _("Card History"),
    "cc_card_history.id",
    [
        "cc_card" => ["cc_card_history.id_cc_card", "cc_card.id"],
        "cc_card_group" => ["cc_card.id_group", "cc_card_group.id"],
    ]
);

$DBHandle = DbConnect();
$HD_Form->AddListValue(_("Account number"), "cc_card_history.id_cc_card", [Customer::class, "getUsername"]);
$HD_Form->AddListValue(_("Date"), "cc_card_history.datecreated");
$HD_Form->AddListValue(_("Description"), "cc_card_history.description");

$HD_Form->list_query_order_columns = ["cc_card_history.datecreated"];
$HD_Form->list_query_order_direction = "DESC";

$HD_Form->list_query_columns = ["cc_card_history.id_cc_card", "cc_card_history.datecreated", "cc_card_history.description"];
$HD_Form->FG_LIST_VIEW_PAGE_SIZE = 25;

$HD_Form->search_form_enabled = true;
$HD_Form->search_delete_enabled = false;
$HD_Form->search_session_key = 'card_history_selection';
$HD_Form->search_form_title = gettext('Define specific criteria to search for card history');

if (!is_customer()) {
    $HD_Form->AddSearchPopupInput(_("Enter the customer ID"), "cc_card_history.id_cc_card", "A2B_entity_card.php");
}
$HD_Form->AddSearchDateInput(_("Date"), "cc_card_history.datecreated");

$HD_Form->prepare_list_subselection("list");
if (empty($HD_Form->list_query_conditions)) {
    $date = (new DateTime("-1 month"))->format("Y-m-d H:i:s");
    $HD_Form->list_query_conditions["cc_card_history.datecreated"] = [">=", $date];
}
if (is_agent()) {
    $HD_Form->list_query_conditions["cc_card_group.id_agent"] = $_SESSION["agent_id"] ?? 0;
} elseif (is_customer()) {
    $HD_Form->list_query_conditions["cc_card_history.id_cc_card"] = $_SESSION["card_id"] ?? 0;
}
