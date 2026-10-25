<?php

namespace A2billing\Payments\Enums;

enum Status: int
{
    case Open = 0;
    case Closed = 1;

    public function label(): string
    {
        return match($this) {
            self::Open => _("Open"),
            self::Closed => _("Closed"),
        };
    }
}
