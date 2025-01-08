<?php

namespace A2billing\Forms;

class ViewForm
{
    private FormHandler $form;
    private array $list;

    /**
     * @param FormHandler $form
     * @param array $list the list of database records to be displayed
     */
    public function __construct(FormHandler $form, array $list) {
        $this->form = $form;
        $this->list = $list;
    }

    public function __toString(): string
    {
        $form = $this->form;
        $processed = $form->getProcessed();
        $list = $this->list;
        $popup_select = (int)($processed["popup_select"] ?? 0);

        $query_params = [
            "current_page" => $processed["current_page"] ?? null,
            "order" => $processed["order"] ?? null,
            "sens" => $processed["sens"] ?? null,
            "filterprefix" => $processed["filterprefix"] ?? null,
            "filterprefix2" => $processed["filterprefix"] ?? null,
            "popup_select" => $processed["popup_select"] ?? null,
            "popup_formname" => $processed["popup_formname"] ?? null,
            "popup_fieldname" => $processed["popup_fieldname"] ?? null,
        ];
        $query_params = array_filter($query_params, fn ($v) => !is_null($v));
        foreach($form->CV_FOLLOWPARAMETERS as $k => $v) {
            $query_params[$k] = $v;
        }
        $sort_params = $pagination_params = $query_params;
        $pagination_params["current_page"] = "%s";

        $hasActionButtons = (
            $form->FG_ENABLE_DELETE_BUTTON || $form->FG_ENABLE_INFO_BUTTON || $form->FG_ENABLE_EDIT_BUTTON
            || count($form->list_action_buttons)
        );

        ob_start();
        require(__DIR__ . "/../../templates/ViewHandler.inc.php");

        $output = ob_get_clean() ?: "Template error!";

        if (empty($list)) {
            $output .=  "<div class='row pb-3 justify-content-center'><div class='col-8'>$form->CV_NO_FIELDS</div></div>";
        }

        return $output;
    }
}
