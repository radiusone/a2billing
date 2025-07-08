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
            "popup_select" => $processed["popup_select"] ?? null,
            "popup_formname" => $processed["popup_formname"] ?? null,
            "popup_fieldname" => $processed["popup_fieldname"] ?? null,
        ];
        for ($i = 0; $i < count($form->list_filters); $i++) {
            $query_params["filterprefix$i"] = $processed["filterprefix$i"] ?? null;
        }
        foreach ($processed as $k => $v) {
            if ($k !== "csrf_token" && !array_key_exists($k, $query_params)) {
                $query_params[$k] = $v;
            }
        }
        foreach($form->CV_FOLLOWPARAMETERS as $k => $v) {
            $query_params[$k] = $v;
        }
        $query_params = array_unique(
            array_filter($query_params, fn ($v) => !is_null($v) && $v !== "")
        );
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
