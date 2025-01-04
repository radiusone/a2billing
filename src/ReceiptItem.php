<?php
namespace A2billing;

use A2billing\Payments\PaymentDocumentItem;

class ReceiptItem extends PaymentDocumentItem
{
    public ?int $receipt_id = null;

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
        $db = DbConnect();
        $result = (new Table("cc_receipt_item"))->getRow($db, ["id" => $id]);
        $this->id = $id;
        $this->receipt_id = (int)$result["id_receipt"];
        $this->description = $desc ?? $result["description"];
        $this->date = $date ?? $result["date"];
        $this->price = $price ?? (float)$result["price"];
        $this->id_ext = $id_ext ?? $result["id_ext"];
        $this->type_ext = $type_ext ?? $result["type_ext"];
    }

    public static function create($receipt, string $desc, string $date, float $price, ?string $type_ext = null, ?int $id_ext = null): self
    {
        $instance = new self();
        $instance->receipt_id = $receipt instanceof Receipt ? $receipt->id : $receipt;
        $instance->description = $desc;
        $instance->date = $date;
        $instance->price = $price;
        $instance->type_ext = $type_ext;
        $instance->id_ext = $id_ext;

        return $instance;
    }

    public function save(): bool
    {
        $table = new Table("cc_receipt_item");
        $values = [
            "id_receipt" => $this->receipt_id,
            "description" => $this->description,
            "date" => $this->date,
            "price" => $this->price,
            "type_ext" => $this->type_ext,
            "id_ext" => $this->id_ext,
        ];
        $db = DbConnect();
        if ($this->id) {
            return $table->updateRow($db, $values, ["id" => $this->id]);
        } else {
            $id = null;
            $result = $table->addRow($db, $values, "id", $id);
            $this->id = $id;

            return $result;
        }
    }
}
