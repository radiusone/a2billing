<?php

use A2billing\Agent;
use A2billing\Connection;
use A2billing\Customer;
use A2billing\Forms\FormHandler;

$builder = Connection::getConnection()
    ->table("cc_card_history")
    ->leftJoin("cc_card", "cc_card_history.id_cc_card", "cc_card.id")
    ->leftJoin("cc_card_group", "cc_card.id_group", "cc_card_group.id")
    ->orderBy("cc_card_history.datecreated", "desc");

$HD_Form = new FormHandler(
    "cc_card_history",
    _("Card History"),
    builder: $builder
);

$HD_Form->AddListValue(_("Account number"), "cc_card.username", [Customer::class, "getLinkFromUsername"]);
$HD_Form->AddListValue(_("Date"), "cc_card_history.datecreated");
$HD_Form->AddListValue(_("Description"), "cc_card_history.description");

$HD_Form->FG_LIST_VIEW_PAGE_SIZE = 25;

$HD_Form->search_form_enabled = true;
$HD_Form->search_delete_enabled = false;
$HD_Form->search_session_key = 'card_history_selection';
$HD_Form->search_form_title = gettext('Define specific criteria to search for card history');

if (is_admin()) {
    $HD_Form->AddSearchPopupInput(_("Enter the customer ID"), "cc_card_history.id_cc_card", "A2B_entity_card.php");
}
$HD_Form->AddSearchDateInput(_("Date"), "cc_card_history.datecreated");

$HD_Form->prepare_list_subselection("list");
if (empty($HD_Form->query_builder->wheres)) {
    $date = new DateTime("-1 month");
    $HD_Form->query_builder
        ->where("cc_card_history.datecreated", ">=", $date);
}
if (is_agent()) {
    $HD_Form->query_builder->where("cc_card_group.id_agent", Agent::id());
} elseif (is_customer()) {
    $HD_Form->query_builder->where("cc_card_history.id_cc_card", Customer::id());
}
