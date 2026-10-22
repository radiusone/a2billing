<?php

namespace A2billing\Forms\Elements\List;

class Filter
{
    /** @var FilterInput[] $inputs */
    public array $inputs = [];

    public function renderForm(array $data, array $hidden = []): string
    {
        if (count($this->inputs) === 0) {
            return "";
        }

        $hidden_inputs = "";
        unset($hidden["current_page"]);
        $hidden["form_action"] = "list";
        foreach ($hidden as $name => $value) {
            if (
                empty($name)
                || preg_match("/^(id|filterprefix\d)$/", $name)
                || is_array($value)
            ) {
                continue;
            }
            $hidden_inputs .= sprintf(
                '<input type="hidden" name="%s" value="%s"/>',
                htmlspecialchars($name),
                htmlspecialchars($value),
            );
        }

        $filter_inputs = "";
        foreach ($this->inputs as $i => $input) {
            $filter_inputs .= $input->render($data["filterprefix$i"] ?? "", $i);
        }

        $button = htmlspecialchars(_("Apply Filter"));

        return <<< HTML
        <form method="get" action="" class="col">
            $hidden_inputs
            <div class="row align-items-end">
                $filter_inputs
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-primary">$button</button>
                </div>
            </div>
        </form>
        HTML;

    }
}
