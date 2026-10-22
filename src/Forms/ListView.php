<?php

namespace A2billing\Forms;

use A2billing\Forms\Elements\List\Buttons\ActionButton;
use A2billing\Forms\Elements\List\Buttons\DeleteButton;
use A2billing\Forms\Elements\List\Buttons\EditButton;
use A2billing\Forms\Elements\List\Buttons\InfoButton;
use A2billing\Forms\Elements\List\Buttons\TopButton;
use A2billing\Forms\Elements\List\Filter;
use A2billing\Forms\Elements\List\FilterInput;
use A2billing\Forms\Elements\ListElement;

final class ListView
{
    /** @var Filter|null $filter */
    private ?Filter $filter = null;

    /** @var TopButton[] $topButtons */
    private array $topButtons = [];

    /** @var ListElement[] */
    private array $columns = [];

    private ?InfoButton $infoButton = null;

    private ?EditButton $editButton = null;

    private ?DeleteButton $deleteButton = null;

    /** @var ActionButton[] */
    private array $actionButtons = [];

    /**
     * @param FormHandler $form
     */
    public function __construct(readonly private FormHandler $form)
    {}

    public function addFilterInput(FilterInput $input): self
    {
        $this->filter ??= new Filter();
        $this->filter->inputs[] = $input;

        return $this;
    }

    public function addFirstTopButton(TopButton $button): self
    {
        array_unshift($this->topButtons, $button);

        return $this;
    }

    public function addTopButton(TopButton $button): self
    {
        $this->topButtons[] = $button;

        return $this;
    }

    public function addColumn(ListElement $column): self
    {
        $this->columns[] = $column;

        return $this;
    }

    public function addInfoButton(InfoButton $button): self
    {
        $this->infoButton = $button;

        return $this;
    }

    public function addEditButton(EditButton $button): self
    {
        $this->editButton = $button;

        return $this;
    }

    public function addDeleteButton(DeleteButton $button): self
    {
        $this->deleteButton = $button;

        return $this;
    }

    public function addActionButton(ActionButton $button): self
    {
        $this->actionButtons[] = $button;

        return $this;
    }

    public function render(array $list): string
    {
        $form = $this->form;
        $processed = $form->getProcessed();
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

        $hasActionButtons = (
            $this->deleteButton || $this->infoButton || $this->editButton
            || count($this->actionButtons)
        );

        ob_start();
        require(__DIR__ . "/../../templates/ListView.inc.php");

        $output = ob_get_clean() ?: "Template error!";

        if (empty($list)) {
            $output .=  "<div class='row pb-3 justify-content-center'><div class='col-8'>$form->list_message_empty</div></div>";
        }

        return $output;
    }
}
