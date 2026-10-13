<?php
namespace A2billing\Forms;

readonly class DeleteForm
{
    public function __construct(
        private FormHandler $form,
        private array $processed,
        private array $list,
        private string $form_action,
    )
    {}

    public function __toString(): string
    {
        $form = $this->form;
        $processed = $this->processed;
        $list = $this->list;
        $form_action = $this->form_action;
        $db_data = $list[0];

        ob_start();
        require(__DIR__ . "/../../templates/DelForm.inc.php");

        return ob_get_clean() ?: "Template error!";
    }
}
