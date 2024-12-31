<?php
namespace A2billing;

use DateTime;

class Invoice
{
    public const STATUS_OPEN = 0;
    public const STATUS_CLOSED = 1;
    public const PAIDSTATUS_UNPAID = 0;
    public const PAIDSTATUS_PAID = 1;

    public ?int $id = null;
    public string $title = "";
    public string $description = "";
    public string $date = "";
    public int $status = self::STATUS_OPEN;
    public int $paid_status = self::PAIDSTATUS_UNPAID;
    public string $card = "";
    public string $reference = "";
    /** @var InvoiceItem[] */
    public array $items = [];

    /**
     * Create a new empty invoice item or fetch one from the database
     *
     * @param int|null $id if provided, the database ID used to fetch and populate (other parameters will override DB)
     * @param string|null $description
     * @param string|null $title
     * @param string|null $reference
     * @param int|null $status
     * @param int|null $paid_status
     * @param string|null $date
     */
    public function __construct(
        ?int    $id,
        ?string $description = null,
        ?string $title = null,
        ?string $reference = null,
        ?int    $status = null,
        ?int    $paid_status = null,
        ?string $date = null
    )
    {
        if (is_null($id)) {
            return;
        }
        $DBHandle = DbConnect();
        $value = (new Table(
            "cc_invoice",
            ["id", "id_card", "description", "title", "status", "paid_status", "date", "reference"]
        ))
            ->getRow($DBHandle, ["id" => $id]);
        $this->id = (int)$value["id"];
        $this->card = (int)$value["id_card"];
        $this->description = $description ?? $value["description"];
        $this->title = $title ?? $value["title"];
        $this->status = $status ?? (int)$value["status"];
        $this->paid_status = $paid_status ?? (int)$value["paid_status"];
        $this->date = $date ?? $value["date"];
        $this->reference = $reference ?? $value["reference"];
        $this->items = $this->loadItems();
    }

    public static function create(
        int     $card,
        string  $description = "",
        string  $title = "",
        string  $reference = "",
        int     $status = self::STATUS_OPEN,
        int     $paid_status = self::PAIDSTATUS_UNPAID,
        ?string $date = null
    ): self
    {
        $instance = new self(null);
        $instance->card = $card;
        $instance->description = $description;
        $instance->title = $title;
        $instance->reference = $reference;
        $instance->status = $status;
        $instance->paid_status = $paid_status;
        $instance->date = $date ?? (new DateTime())->format("Y-m-d H:i:s");

        return $instance;
    }

    public function save(): bool
    {
        $table = new Table("cc_invoice");
        $values = [
            "id_card" => $this->card,
            "description" => $this->description,
            "title" => $this->title,
            "status" => $this->status,
            "paid_status" => $this->paid_status,
            "date" => $this->date,
            "reference" => $this->reference,
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

    public function getReference(): string
    {
        return $this->reference;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getCard(): string
    {
        return $this->card;
    }

    public function getStatus(): int
    {
        return $this->status;

    }
    public function getPaidStatus(): int
    {
        return $this->paid_status;

    }

    public function getDate(): string
    {
        return substr($this->date, 0, 10);
    }

    public function loadItems(): array
    {
        if (is_null($this->id)) {
            return [];
        }
        $result = [];
        $DBHandle = DbConnect();
        $instance_sub_table = new Table("cc_invoice_item", ["id"]);
        $return = $instance_sub_table->getRows($DBHandle, ["id_invoice" => $this->id]);
        foreach ($return as $value) {
            $result[] = new InvoiceItem($value["id"]);
        }

        return $result;
    }

    public function loadDetailledItems(): array
    {
        $result = [];
        foreach ($this->items as $value) {
            if (empty($value["id_ext"]) || $value["type_ext"] !== "CALLS") {
                $result[] = $value;
                continue;
            }

            $DBHandle = DbConnect();
            $billing = (new Table("cc_billing_customer", ["date", "start_date"]))
                ->getRow($DBHandle, ["id" => $value["id_ext"]]);
            if (count($billing) === 0) {
                continue;
            }

            $conditions = ["card_id" => $this->card, "stoptime" => ["<", $billing["date"]]];
            if (!empty($billing["start_date"])) {
                $conditions["stoptime"] = [">=", $billing["start_date"]];
            }
            $calls = (new Table("cc_call"))->getRows($DBHandle, $conditions);
            foreach ($calls as $call) {
                $duration = get_timespan($call["sessiontiome"]);
                $item = InvoiceItem::create(
                    $this,
                    sprintf(_("Call to: %s, duration: %s"), $call['calledstation'], $duration),
                    $call['starttime'],
                    $call["sessionbill"],
                    $value["VAT"],
                    true
                );
                $result[] = $item;
            }
        }
        return $result;
    }

    public function loadPayments(): ?array
    {
        if (empty($this->id)) {
            return null;
        }
        $DBHandle = DbConnect();
        $table = new Table(
            "cc_invoice_payment",
            "*",
            ["cc_logpayment" => ["NATURAL", "cc_invoice_payment.id_payment", "cc_logpayment.id"]]
        );
        return $table->getRows($DBHandle, ["id_invoice" => $this->id], ["date"]);
    }

    public function delPayment($idpayment): bool
    {
        if (is_null($this->id)) {
            return false;
        }
        $DBHandle = DbConnect();
        return (new Table("cc_invoice_payment"))
            ->deleteRow($DBHandle, ["id_invoice" => $this->id, "id_payment" => $idpayment]);
    }

    public function addPayment($idpayment): bool
    {
        if (is_null($this->id)) {
            return false;
        }
        $DBHandle = DbConnect();
        return (new Table("cc_invoice_payment"))
            ->addRow($DBHandle, ["id_invoice" => $this->id, "id_payment" => $idpayment]);
    }

    public function changeStatus(int $status): bool
    {
        if (is_null($this->id)) {
            return false;
        }
        $DBHandle = DbConnect();
        $result = (new Table("cc_invoice"))
            ->updateRow($DBHandle, ["paid_status" => $status], ["id" => $this->id]);
        if ($this->paid_status !== $status) {
            foreach ($this->items as $item) {
                if ($item->getExtType() === "DID" && $item->getExtId()) {
                    $result = (new Table("cc_did_use"))
                        ->updateRow(
                            $DBHandle,
                            [
                                "reminded" => $status ? 0 : 1,
                                "month_payed" => $status
                                    ? ["month_payed - ?", "1"]
                                    : ["month_payed + ?", "1"]
                            ],
                            ["id_did" => $item->getExtId(), "activated" => 1]
                        );
                }
            }
        }

        return $result;
    }

    public function insertInvoiceItem(string $desc, float $price, float $VAT, string $date = null): bool
    {
        if (is_null($this->id)) {
            return false;
        }
        $date ??= (new DateTime())->format("Y-m-d H:i:s");
        $item = InvoiceItem::create($this, $desc, $date, $price, $VAT);

        return $item->save();
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

    public function getPaidStatusDisplay(): string
    {
        switch ($this->paid_status) {
            case self::PAIDSTATUS_UNPAID:
                return _("UNPAID");
            case self::PAIDSTATUS_PAID:
                return _("PAID");
            default:
                return "";
        }
    }

}
