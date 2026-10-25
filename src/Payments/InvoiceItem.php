<?php
namespace A2billing\Payments;

use A2billing\Connection;
use A2billing\Payments\Enums\ItemType;

class InvoiceItem extends PaymentDocumentItem
{
    public ?int $invoice_id = null;
    protected string $table = "cc_invoice_item";

    public function __construct(
        ?int $id = null,
        ?string $desc = null,
        ?string $date = null,
        ?float $price = null,
        ?float $VAT = null,
        ?ItemType $type_ext = null,
        ?int $id_ext = null,
    )
    {
        if (is_null($id)) {
            return;
        }
        $result = Connection::getConnection("cc_invoice_item")
            ->where("id", $id)
            ->firstOrFail();
        $this->id = $id;
        $this->invoice_id = (int)$result["id_invoice"];
        $this->description = $desc ?? $result["description"];
        $this->date = $date ?? $result["date"];
        $this->price = $price ?? $result["price"];
        $this->vat = $VAT ?? $result["VAT"];
        $this->id_ext = $id_ext ?? $result["id_ext"];
        $this->type_ext = $type_ext ?? ItemType::tryFrom($result["type_ext"]) ?? ItemType::Other;
    }

    /**
     * @param Invoice|int $invoice
     * @param string $desc
     * @param string $date
     * @param float $price
     * @param float $VAT
     * @param ItemType $type_ext
     * @param int|null $id_ext
     * @return self
     */
    public static function create(
        Invoice|int $invoice,
        string $desc,
        string $date,
        float $price,
        float $VAT = 0,
        ItemType $type_ext = ItemType::Other,
        ?int $id_ext = null
    ): self
    {
        $instance = new self();
        $instance->invoice_id = $invoice instanceof Invoice ? $invoice->id : $invoice;
        $instance->description = $desc;
        $instance->date = $date;
        $instance->price = $price;
        $instance->vat = $VAT;
        $instance->type_ext = $type_ext;
        $instance->id_ext = $id_ext;

        return $instance;
    }

    public function save(): bool
    {
        $values = [
            "id_invoice" => $this->invoice_id,
            "description" => $this->description,
            "date" => $this->date,
            "price" => $this->price,
            "VAT" => $this->vat,
            "type_ext" => $this->type_ext,
            "id_ext" => $this->id_ext,
        ];

        return $this->saveOrUpdate($values);
    }
}
