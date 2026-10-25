<?php

namespace A2billing\Forms\Elements;

abstract class ListButton
{
    protected string $link;
    protected string $name;
    protected string $icon;
    protected string $label;
    protected string $condition = "";

    protected function replaceValues(string $string, int $id, array $row): string
    {
        return preg_replace_callback(
            "/\\|([\w.]+)\\|/i",
            fn ($m) => $row[$m[1]] ?? "",
            str_replace(
                "|param|",
                $id,
                str_ends_with($string, "id=") ? "$string$id" : $string
            )
        );
    }

    protected function checkCondition(int $id, array $row): bool
    {
        if ($this->condition === "") {
            return true;
        }
        $condition = $this->replaceValues($this->condition, $id, $row);

        return boolval(eval("return $condition;"));
    }

    abstract public function renderButton(int $id, array $row): string;
}
