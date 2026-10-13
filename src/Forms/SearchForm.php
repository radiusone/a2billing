<?php
namespace A2billing\Forms;

readonly class SearchForm
{

    public function __construct(
        private FormHandler $form,
        private array $processed,
        private bool $full_modal,
        private bool $with_hide_button,
    )
    {}

    public function __toString(): string
    {
        $form = $this->form;
        $processed = $this->processed;
        $with_hide_button = $this->with_hide_button;
        $full_modal = $this->full_modal;
        $action = http_build_query(array_filter([
            "order" => $processed["order"] ?? null,
            "sens" => $processed["sens"] ?? null,
            "current_page" => $processed["current_page"] ?? null,
        ]));

        ob_start();
        require(__DIR__ . "/../../templates/SearchHandler.inc.php");

        return ob_get_clean() ?: "Template error!";
    }
}
