<?php
namespace A2billing\Payments;

use A2billing\Connection;
use DateTime;

class ReceiptItem extends PaymentDocumentItem
{
    public ?int $receipt_id = null;
    protected string $table = "cc_receipt_item";

    public function __construct(
        ?int    $id = null,
        ?string $desc = null,
        ?string $date = null,
        ?float  $price = null,
        ?string $type_ext = null,
        ?int    $id_ext = null
    )
    {
        if (is_null($id)) {
            return;
        }
        $result = Connection::getConnection("cc_receipt_item")
            ->where("id", $id)
            ->first();
        $this->id = $id;
        $this->receipt_id = (int)$result["id_receipt"];
        $this->description = $desc ?? $result["description"];
        $this->date = $date ?? $result["date"];
        $this->price = $price ?? (float)$result["price"];
        $this->id_ext = $id_ext ?? $result["id_ext"];
        $this->type_ext = $type_ext ?? $result["type_ext"];
    }

    public static function create($receipt, string $desc, float $price, ?string $date = null, ?string $type_ext = null, ?int $id_ext = null): self
    {
        $instance = new self();
        $instance->receipt_id = $receipt instanceof Receipt ? $receipt->id : $receipt;
        $instance->description = $desc;
        $instance->date = $date ?? (new DateTime())->format("Y-m-d H:i:s");
        $instance->price = $price;
        $instance->type_ext = $type_ext;
        $instance->id_ext = $id_ext;

        return $instance;
    }

    public function save(): bool
    {
        $values = [
            "id_receipt" => $this->receipt_id,
            "description" => $this->description,
            "date" => $this->date,
            "price" => $this->price,
            "type_ext" => $this->type_ext,
            "id_ext" => $this->id_ext,
        ];

        return $this->saveOrUpdate($values);
    }
}
