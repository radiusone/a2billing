<?php
namespace A2billing\Forms;

readonly class EditForm
{
    public function __construct(
        private FormHandler $form,
        private array $processed,
        private array $list,
    )
    {}

    public function __toString(): string
    {
        $form = $this->form;
        $processed = $this->processed;
        $list = $this->list;
        $db_data = $list[0];

        ob_start();
        require(__DIR__ . "/../../templates/EditForm.inc.php");

        return ob_get_clean() ?: "Template error!";
    }
}
