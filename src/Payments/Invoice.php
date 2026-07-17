<?php
namespace A2billing\Payments;

use A2billing\Connection;
use DateTime;
use Illuminate\Database\Query\Builder;

class Invoice extends PaymentDocument
{
    public const PAIDSTATUS_UNPAID = 0;
    public const PAIDSTATUS_PAID = 1;

    public int $paid_status = self::PAIDSTATUS_UNPAID;
    public string $reference = "";
    protected string $table = "cc_invoice";

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
        $value = Connection::getConnection("cc_invoice", "id", "id_card", "description", "title", "status", "paid_status", "date", "reference")
            ->where("id", $id)
            ->first();
        $this->id = (int)$value["id"];
        $this->card = (int)$value["id_card"];
        $this->description = $description ?? $value["description"];
        $this->title = $title ?? $value["title"];
        $this->status = $status ?? (int)$value["status"];
        $this->paid_status = $paid_status ?? (int)$value["paid_status"];
        $this->date = $date ?? $value["date"];
        $this->reference = $reference ?? $value["reference"] ?? "";
        $this->items = $this->loadItems();
    }

    public static function create(
        int     $card,
        string  $description = "",
        string  $title = "",
        ?string $reference = null,
        int     $status = self::STATUS_OPEN,
        int     $paid_status = self::PAIDSTATUS_UNPAID,
        ?string $date = null
    ): self
    {
        $instance = new self(null);
        $instance->card = $card;
        $instance->description = $description;
        $instance->title = $title;
        $instance->reference = $reference ?? self::generateReference();
        $instance->status = $status;
        $instance->paid_status = $paid_status;
        $instance->date = $date ?? (new DateTime())->format("Y-m-d H:i:s");

        return $instance;
    }

    public function save(): bool
    {
        $values = [
            "id_card" => $this->card,
            "description" => $this->description,
            "title" => $this->title,
            "status" => $this->status,
            "paid_status" => $this->paid_status,
            "date" => $this->date,
            "reference" => $this->reference,
        ];

        return $this->saveOrUpdate($values);
    }

    public function getReference(): string
    {
        return $this->reference;
    }

    public function getPaidStatus(): int
    {
        return $this->paid_status;
    }

    public function loadItems(): array
    {
        if (is_null($this->id)) {
            return [];
        }
        return Connection::getConnection("cc_invoice_item")
            ->where("id_invoice", $this->id)
            ->pluck("id")
            ->map(fn ($v) => new InvoiceItem($v))
            ->toArray();
    }

    public function loadDetailledItems(): array
    {
        $result = [];
        foreach ($this->items as $value) {
            /** @var PaymentDocumentItem $value */
            if (empty($value->getExtId()) || $value->getExtType() !== "CALLS") {
                $result[] = $value;
                continue;
            }

            $billing = Connection::getConnection("cc_billing_customer", "date", "start_date")
                ->where("id", $value->getExtId())
                ->first();
            if (!$billing) {
                continue;
            }

            $calls = Connection::getConnection("cc_call")
                ->where(["card_id" => $this->card, "stoptime" => ["<", $billing["date"]]])
                ->when(
                    !empty($billing["start_date"]),
                    fn (Builder $q) => $q->where("stoptime", ">=", $billing["start_date"])
                )
                ->get();
            foreach ($calls as $call) {
                $duration = get_timespan($call["sessiontiome"]);
                $item = InvoiceItem::create(
                    $this,
                    sprintf(_("Call to: %s, duration: %s"), $call['calledstation'], $duration),
                    $call['starttime'],
                    $call["sessionbill"],
                    $value["VAT"],
                    true // why is this true?
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

        return Connection::getConnection("cc_invoice_payment")
            ->leftJoin("cc_logpayment", "cc_invoice_payment.id_payment", "cc_logpayment.id")
            ->where("id_invoice", $this->id)
            ->orderBy("date")
            ->get()
            ->toArray();
    }

    public function delPayment($idpayment): bool
    {
        if (is_null($this->id)) {
            return false;
        }

        return Connection::getConnection("cc_invoice_payment")
            ->where(["id_invoice" => $this->id, "id_payment" => $idpayment])
            ->delete();
    }

    public function addPayment($idpayment): bool
    {
        if (is_null($this->id)) {
            return false;
        }

        return Connection::getConnection("cc_invoice_payment")
            ->insert(["id_invoice" => $this->id, "id_payment" => $idpayment]);
    }

    public function changeStatus(int $status): bool
    {
        if (is_null($this->id)) {
            return false;
        }
        $result = Connection::getConnection("cc_invoice")
            ->where("id", $this->id)
            ->update(["paid_status" => $status]);
        if ($this->paid_status !== $status) {
            foreach ($this->items as $item) {
                if ($item->getExtType() === "DID" && $item->getExtId()) {
                    Connection::getConnection("cc_did_use")
                        ->where(["id_did" => $item->getExtId(), "activated" => 1])
                        ->when(
                            $status,
                            fn (Builder $q) => $q->decrement("month_payed", 1, ["reminded" => 0]),
                            fn (Builder $q) => $q->increment("month_payed", 1, ["reminded" => 1])
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

    public static function generateReference(): string
    {
        $year = date("Y");
        $row = Connection::getConnection("cc_config", "cc_config.id", "config_value")
            ->leftJoin("cc_config_group", "cc_config.config_group_id", "cc_config_group.id")
            ->where(["config_key" => "next_number", "group_title" => "invoice"])
            ->first();
        $conf_id = $row["id"];
        $invoice_num = $row["config_value"];

        if (empty($invoice_num) || !str_starts_with($invoice_num, $year)) {
            $invoice_num = $year . "00000001";
        }

        $update = preg_replace_callback(
            "/^($year)(\d+)$/",
            fn ($m) => $m[1] . str_pad(intval($m[2]) + 1, 8, "0", STR_PAD_LEFT),
            $invoice_num
        );
        $table->updateRow(["config_value" => $update], ["id" => $conf_id]);

        return $invoice_num;
    }
}
