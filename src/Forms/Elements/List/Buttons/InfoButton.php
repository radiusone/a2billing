<?php

namespace A2billing\Forms\Elements\List\Buttons;

use A2billing\Forms\Elements\ListButton;

class InfoButton extends ListButton
{
    public function __construct(string $link, string $name, string $icon = "search")
    {
        $this->link = $link;
        $this->name = $name;
        $this->icon = $icon;
        $this->label = sprintf(_("About this %s"), strtolower($this->name));
    }

    public function renderButton(int $id, array $row): string
    {
        return sprintf(
            '<a href="%s%d" title="%s"><span class="bi bi-16 text-info bi-%s" aria-hidden="true"></span></a>',
            htmlspecialchars($this->link),
            $id,
            htmlspecialchars($this->label),
            htmlspecialchars($this->icon)
        );
    }
}
