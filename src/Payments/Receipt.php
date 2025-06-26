<?php
namespace A2billing\Payments;

use A2billing\Table;
use DateTime;

class Receipt extends PaymentDocument
{
    protected string $table = "cc_receipt";

    public function __construct(?int $id = null, ?string $desc = null, ?string $title = null)
    {
        if (is_null($id)) {
            return;
        }
        $value = (new Table(
            "cc_receipt",
            ["cc_receipt.id", "id_card", "description", "title", "date", "cc_receipt.status", "username"],
            ["cc_card" => ["cc_receipt.id_card", "cc_card.id"]]
        ))
            ->getRow(["cc_receipt.id" => $id]);
        $this->id = (int)$value["id"];
        $this->card = (int)$value["id_card"];
        $this->date = $value["date"];
        $this->description = $desc ?? $value["description"];
        $this->title = $title ?? $value["title"];
        $this->status = (int)$value["status"];
        $this->username = $value["username"];
        $this->items = $this->loadItems();
    }

    public static function create(
        int     $card,
        string  $description = "",
        string  $title = "",
        int     $status = self::STATUS_OPEN,
        ?string $date = null
    ): self
    {
        $instance = new self(null);
        $instance->card = $card;
        $instance->description = $description;
        $instance->title = $title;
        $instance->status = $status;
        $instance->date = $date ?? (new DateTime())->format("Y-m-d H:i:s");

        return $instance;
    }

    public function save(): bool
    {
        $values = [
            "id_card" => $this->card,
            "description" => $this->description,
            "title" => $this->title,
            "date" => $this->date,
            "status" => $this->status,
        ];

        return $this->saveOrUpdate($values);
    }

    public function loadItems(): array
    {
        if (is_null($this->id)) {
            return [];
        }

        $result = [];
        $instance_sub_table = new Table("cc_receipt_item", ["id"]);
        $return = $instance_sub_table->getColumn("id", "", ["id_receipt" => $this->id]);
        foreach ($return as $id) {
            $result[] = new ReceiptItem($id);
        }

        return $result;
    }

    /**
     * @param int $begin
     * @param int $nb
     * @return list<ReceiptItem>
     */
    public function loadDetailedItems(int $begin = 0, int $nb = 5000): array
    {
        if (is_null($this->id)) {
            return [];
        }
        $result = [];
        foreach ($this->items as $value) {
            if (empty($value->getExtId()) || $value->getExtType() !== "CALLS") {
                $result[] = $value;
                continue;
            }

            $billing = (new Table("cc_billing_customer", ["date", "start_date"]))
                ->getRow(["id" => $value->getExtId()]);
            if (count($billing) === 0) {
                continue;
            }

            $conditions = ["card_id" => $this->card, "stoptime" => ["<", $billing["date"]]];
            if (!empty($billing["start_date"])) {
                $conditions["stoptime"] = [">=", $billing["start_date"]];
            }

            $calls = (new Table("cc_call"))->getRows($conditions, ["date"], "desc", [], $nb, $begin);
            foreach ($calls as $call) {
                $duration = get_timespan($call["sessiontiome"]);
                $item = ReceiptItem::create(
                    $this,
                    sprintf(_("Call to: %s, duration: %s"), $call['calledstation'], $duration),
                    $call["sessionbill"],
                    $call['starttime'],
                    true // what does true mean? original code was just copied from invoice.php including fields that don't exist here :(
                );
                $result[] = $item;
            }
        }

        return $result;
    }

    public function nbDetailedItems(): int
    {
        return count($this->loadDetailedItems());
    }

    public function insertReceiptItem(string $desc, string $price, ?string $date = null): bool
    {
        if (is_null($this->id)) {
            return false;
        }
        $item = ReceiptItem::create($this, $desc, $price, $date);

        return $item->save();
    }
}
