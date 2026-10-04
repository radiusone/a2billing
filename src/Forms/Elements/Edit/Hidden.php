<?php

namespace A2billing\Forms\Elements\Edit;

class Hidden extends Input
{
    public function __construct(string $name)
    {
        parent::__construct("", $name);
        $this->attributes["type"] = "hidden";
    }

    public function render(int|string $i, mixed $value): string
    {
        $this->value = $value;

        return $this->renderElement();
    }
}
