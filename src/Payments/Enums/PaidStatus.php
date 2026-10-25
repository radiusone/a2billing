<?php

namespace A2billing\Payments\Enums;

enum PaidStatus: int
{
    case Unpaid = 0;
    case Paid = 1;

    public function label(): string
    {
        return match($this) {
            self::Unpaid => _("Unpaid"),
            self::Paid => _("Paid"),
        };
    }
}
