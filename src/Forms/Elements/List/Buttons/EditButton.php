<?php

namespace A2billing\Forms\Elements\List\Buttons;

use A2billing\Forms\Elements\ListButton;

class EditButton extends ListButton
{
    public function __construct(
        string $link,
        string $name,
        string $icon = "pencil-fill",
        ?string $label = null,
        protected string $condition = "",
    )
    {
        $this->name = $name;
        $this->link = $link;
        $this->icon = $icon;
        $this->label = $label ?? sprintf(_("Edit this %s"), strtolower($this->name));
    }

    public function renderButton(int $id, array $row = []): string
    {
        if (!$this->checkCondition($id, $row)) {
            return "";
        }

        return sprintf(
            '<a href="%s%d" title="%s"><span class="bi bi-16 text-body bi-%s" aria-hidden="true"></span></a>',
            htmlspecialchars($this->link),
            $id,
            htmlspecialchars($this->label),
            htmlspecialchars($this->icon)
        );
    }
}
