<?php

namespace A2billing\Forms;

class ViewForm
{
    private FormHandler $form;
    private array $processed;
    private array $list;
    private string $letter;
    private string $current_page;
    private int $popup_select;

    public function __construct(FormHandler $form, array $processed, array $list) {
        $this->form = $form;
        $this->processed = $processed;
        $this->list = $list;

        global $letter;
        global $current_page;
        global $popup_select;

        getpost_ifset(['letter', 'current_page', 'popup_select']);
        /**
         * @var string $letter
         * @var string $current_page
         * @var string $popup_select
         */
        $this->letter = $letter ?? "";
        $this->current_page = $current_page ?? "";
        $this->popup_select = (int)($popup_select ?? 0);
    }

    public function __toString(): string
    {
        $form = $this->form;
        $processed = $this->processed;
        $list = $this->list;
        $letter = $this->letter;
        $current_page = $this->current_page;
        $popup_select = $this->popup_select;

        if (empty($list)) {
            return "<div class='row pb-3 justify-content-center'><div class='col-8'>$form->CV_NO_FIELDS</div></div>";
        }

        $origlist = [];
        $hasActionButtons = (
            $form->FG_ENABLE_DELETE_BUTTON || $form->FG_ENABLE_INFO_BUTTON || $form->FG_ENABLE_EDIT_BUTTON || $form->FG_OTHER_BUTTON1
            || $form->FG_OTHER_BUTTON2 || $form->FG_OTHER_BUTTON3 || $form->FG_OTHER_BUTTON4 || $form->FG_OTHER_BUTTON5
        );

        ob_start();
        require(__DIR__ . "/../../templates/ViewHandler.inc.php");

        return ob_get_clean() ?: "Template error!";
    }
}
