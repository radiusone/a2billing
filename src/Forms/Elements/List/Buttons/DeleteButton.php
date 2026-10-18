<?php

namespace A2billing\Forms\Elements\List\Buttons;

use A2billing\Forms\Elements\ListButton;

class DeleteButton extends ListButton
{
    public function __construct(
        string $link,
        string $name,
        string $icon = "trash3-fill",
        ?string $label = null,
        string $condition = "",
    )
    {
        $this->link = $link;
        $this->name = $name;
        $this->icon = $icon;
        $this->condition = $condition;
        $this->label = $label ?? sprintf(_("Delete this %s"), strtolower($this->name));
    }

    public function renderButton(int $id, array $row = []): string
    {
        if (!$this->checkCondition($id, $row)) {
            return "";
        }

        return sprintf(
            '<a href="%s%d" title="%s"><span class="bi bi-16 text-danger bi-%s" aria-hidden="true"></span></a>',
            htmlspecialchars($this->link),
            $id,
            htmlspecialchars($this->label),
            htmlspecialchars($this->icon)
        );
    }
}
