<?php

namespace A2billing\Payments;

abstract class PaymentDocument
{
    public const STATUS_OPEN = 0;
    public const STATUS_CLOSED = 1;

    public ?int $id = null;

    public string $title = "";

    public string $description = "";

    public int $card = 0;

    public string $date = "";

    public int $status = self::STATUS_OPEN;

    public string $username = "";

    /** @var PaymentDocumentItem[] */
    public array $items = [];

    abstract public function save():bool;
    abstract public function loadItems(): array;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getCard(): ?int
    {
        return $this->card;
    }

    public function getDate(): string
    {
        return substr($this->date, 0, 10);
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getStatusDisplay(): string
    {
        switch ($this->status) {
            case self::STATUS_OPEN:
                return _("OPEN");
            case self::STATUS_CLOSED:
                return _("CLOSED");
            default:
                return "";
        }
    }
}
