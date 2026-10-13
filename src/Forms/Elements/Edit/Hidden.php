<?php

namespace A2billing\Forms\Elements\Edit;

class Hidden extends Input
{
    public function __construct(string $name, mixed $value)
    {
        parent::__construct("", $name);
        $this->attributes["type"] = "hidden";
        $this->value = $value;
    }

    public function render(int|string $i, mixed $value): string
    {
        return is_null($this->value) ? "" : $this->renderElement();
    }
}
