<?php

namespace A2billing\Forms\Elements\List\Buttons;

use A2billing\Forms\Elements\ListButton;

class ActionButton extends ListButton
{
    public function __construct(
        string $link,
        string $icon,
        string $label,
        string $condition =  "",
        protected string $class = "",
        protected string $image = "",
        protected array $data = [],
    )
    {
        $this->link = $link;
        $this->name = "";
        $this->icon = $icon;
        $this->label = $label;
        $this->condition = $condition;
    }

    public function renderButton(int $id, array $row): string
    {
        $this->data["primary-key"] = $id;
        $data = array_map(
            fn ($k, $v) => sprintf('data-%s="%s"', htmlspecialchars($k), htmlspecialchars($v)),
            array_keys($this->data),
            array_values($this->data),
        );
        if ($this->icon) {
            $contents = sprintf(
                '<span class="bi bi-16 text-body bi-%s" aria-hidden="true"></span>',
                htmlspecialchars($this->icon)
            );
        } elseif ($this->image) {
            $contents = sprintf(
                '<img src="%s" alt="%s"/>',
                htmlspecialchars($this->image),
                htmlspecialchars($this->label)
            );
        } else {
            $contents = $this->label;
        }

        if ($this->link) {
            $link = $this->replaceValues($this->link, $id, $row);

            return sprintf(
                '<a href="%s" title="%s" class="%s" %s>%s</a>',
                htmlspecialchars($link),
                htmlspecialchars($this->label),
                htmlspecialchars($this->class),
                implode(" ", $data),
                $contents
            );
        } else {
            return sprintf(
                '<button title="%s" class="btn align-baseline p-0 border-0 %s" %s>%s</button>',
                htmlspecialchars($this->label),
                htmlspecialchars($this->class),
                implode(" ", $data),
                $contents
            );
        }
    }
}
