<?php
namespace A2billing;

class InvoiceItem
{
    public ?int $id = null;
    public string $description = "";
    public string $date = "";
    public float $price = 0;
    public float $VAT = 0;
    public ?int $id_ext = null;
    public string $type_ext = "";
    public ?int $invoice_id = null;

    /**
     * @param int|null $id
     * @param string|null $desc
     * @param string|null $date
     * @param float|null $price
     * @param float|null $VAT
     * @param string|null $type_ext
     * @param int|null $id_ext
     */
    public function __construct(?int $id = null, ?string $desc = null, ?string $date = null, ?float $price = null, ?float $VAT = null, ?string $type_ext = null, ?int $id_ext = null)
    {
        if (is_null($id)) {
            return;
        }
        $db = DbConnect();
        $result = (new Table("cc_invoice_item"))->getRow($db, ["id" => $id]);
        $this->invoice_id = $result["id_invoice"];
        $this->description = $desc ?? $result["desc"];
        $this->date = $date ?? $result["date"];
        $this->price = $price ?? $result["price"];
        $this->VAT = $VAT ?? $result["VAT"];
        $this->id_ext = $id_ext ?? $result["id_ext"];
        $this->type_ext = $type_ext ?? $result["type_ext"];
    }

    /**
     * @param Invoice|int|null $invoice
     * @param string $desc
     * @param string $date
     * @param float $price
     * @param float $VAT
     * @param $type_ext
     * @param $id_ext
     * @return self
     */
    public static function create($invoice, string $desc, string $date, float $price, float $VAT = 0, $type_ext = "", $id_ext = null): self
    {
        $instance = new self();
        $instance->invoice_id = $invoice instanceof Invoice ? $invoice->id : $invoice;
        $instance->description = $desc;
        $instance->date = $date;
        $instance->price = $price;
        $instance->VAT = $VAT;
        $instance->type_ext = $type_ext;
        $instance->id_ext = $id_ext;

        return $instance;
    }

    public function save(): bool
    {
        $table = new Table("cc_invoice_item");
        $values = [
            "id_invoice" => $this->invoice_id,
            "description" => $this->description,
            "date" => $this->date,
            "price" => $this->price,
            "VAT" => $this->VAT,
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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getExtId(): int
    {
        return $this->id_ext;
    }

    public function getExtType(): string
    {
        return $this->type_ext;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getVAT(): float
    {
        return $this->VAT;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getDate(): string
    {
        return substr($this->date, 0, 10);
    }
}
