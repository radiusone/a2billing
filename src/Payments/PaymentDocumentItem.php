<?php

namespace A2billing\Payments;

use A2billing\Table;

abstract class PaymentDocumentItem
{
    public ?int $id = null;

    public string $description = "";

    public string $date = "";

    public float $price = 0;

    public float $vat = 0;

    public ?int $id_ext = null;

    public ?string $type_ext = null;

    use Database;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrice(): float
    {
        return round($this->price, 2, PHP_ROUND_HALF_UP);
    }

    public function getVatRate(): float
    {
        return $this->vat;
    }

    public function getVatAmount(): float
    {
        return round($this->price * $this->vat / 100, 2, PHP_ROUND_HALF_UP);
    }

    public function getTotalPrice(): float
    {
        return round($this->price * (1 + $this->vat /100), 2, PHP_ROUND_HALF_UP);
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getDate(): string
    {
        return substr($this->date, 0, 10);
    }

    public function getExtType(): ?string
    {
        return $this->type_ext;
    }

    public function getExtId(): ?int
    {
        return $this->id_ext;
    }
}
