<?php

namespace A2billing\Forms;

readonly class ViewForm
{
    /**
     * @param FormHandler $form
     * @param array $list the list of database records to be displayed
     */
    public function __construct(private FormHandler $form, private array $list)
    {}

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
        for ($i = 0; $i < count($form->filter?->inputs ?? []); $i++) {
            $query_params["filterprefix$i"] = $processed["filterprefix$i"] ?? null;
        }
        foreach ($processed as $k => $v) {
            if ($k !== "csrf_token") {
                $query_params[$k] ??= $v;
            }
        }
        foreach ($form->search_form_elements as $search) {
            // array_filter will remove empty search items but
            // this ensures the matching operator columns are also removed
            foreach ($search["operator"] ?? [] as $i => $op) {
                $in = $search["input"][$i];
                if (
                    ($processed[$in] ?? "") === ""
                    || (($search["type"] ?? "") === "DATE" && !array_key_exists("enable_$in", $processed))
                ) {
                    unset($query_params[$in], $query_params[$op]);
                }
            }
            // also don't include date fields that aren't enabled
        }
        foreach($form->CV_FOLLOWPARAMETERS as $k => $v) {
            $query_params[$k] = $v;
        }
        $query_params = array_filter($query_params, fn ($v) => !is_null($v) && $v !== "");
        $sort_params = $pagination_params = $query_params;
        $pagination_params["current_page"] = "%s";

        $hasActionButtons = (
            $form->listDeleteButton || $form->listInfoButton || $form->listEditButton
            || count($form->list_action_buttons)
        );

        ob_start();
        require(__DIR__ . "/../../templates/ViewHandler.inc.php");

        $output = ob_get_clean() ?: "Template error!";

        if (empty($list)) {
            $output .=  "<div class='row pb-3 justify-content-center'><div class='col-8'>$form->list_message_empty</div></div>";
        }

        return $output;
    }
}
