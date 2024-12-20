<?php
namespace A2billing\Forms;

class SearchForm
{
    private FormHandler $form;
    private array $processed;
    private bool $with_hide_button;
    private bool $full_modal;

    public function __construct(FormHandler $form, array $processed, bool $full_modal, bool $with_hide_button) {
        $this->form = $form;
        $this->processed = $processed;
        $this->with_hide_button = $with_hide_button;
        $this->full_modal = $full_modal;
    }

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
