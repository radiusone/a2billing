<?php

namespace A2billing\Forms\Elements\List\Buttons;

use A2billing\Forms\Elements\ListButton;

class TopButton extends ListButton
{
    public function __construct(string $link, string $label, string $icon = "")
    {
        $this->link = $link;
        $this->label = $label;
        $this->icon = $icon;
    }

    public function renderButton(int $id = 0, array $row = []): string
    {
        $h = <<< HTML
        <div class="col-auto ms-auto">
            <a href="%s" class="text-decoration-none">
                %s&nbsp;<span class="bi bi-16 bi-%s" aria-hidden="true"></span>
            </a>
        </div>
        HTML;

        return sprintf(
            $h,
            htmlspecialchars($this->link),
            htmlspecialchars($this->label),
            $this->icon
        );
    }
}
