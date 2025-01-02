<?php
namespace A2billing;

use DateTime;

class Receipt
{
    public ?int $id;
    public string $title = "";
    public string $description = "";
    public int $card = 0;
    public string $date = "";
    public string $username = "";
    public array $items = [];

    public function __construct(?int $id = null, ?string $desc = null, ?string $title = null)
    {
        if (is_null($id)) {
            return;
        }
        $DBHandle = DbConnect();
        $value = (new Table(
            "cc_receipt",
            ["cc_receipt.id", "id_card", "description", "title", "date", "username"],
            ["cc_card" => ["cc_receipt.id_card", "cc_card.id"]]
        ))
            ->getRow($DBHandle, ["cc_receipt.id" => $id]);
        $this->id = (int)$value["id"];
        $this->card = (int)$value["id_card"];
        $this->date = $value["date"];
        $this->description = $desc ?? $value["description"];
        $this->title = $title ?? $value["title"];
        $this->username = $value["username"];
        $this->items = $this->loadItems();
    }


    public static function create(
        int     $card,
        string  $description = "",
        string  $title = "",
        ?string $date = null
    ): self
    {
        $instance = new self(null);
        $instance->card = $card;
        $instance->description = $description;
        $instance->title = $title;
        $instance->date = $date ?? (new DateTime())->format("Y-m-d H:i:s");

        return $instance;
    }

    public function save(): bool
    {
        $table = new Table("cc_receipt");
        $values = [
            "id_card" => $this->card,
            "description" => $this->description,
            "title" => $this->title,
            "date" => $this->date,
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

    public function loadItems(): array
    {
        if (is_null($this->id)) {
            return [];
        }

        $result = [];
        $DBHandle = DbConnect();
        $instance_sub_table = new Table("cc_receipt_item", ["id"]);
        $return = $instance_sub_table->getColumn($DBHandle, "id", "", ["id_receipt" => $this->id]);
        foreach ($return as $id) {
            $result[] = new ReceiptItem($id);
        }

        return $result;
    }

    public function loadDetailedItems($begin = 0, $nb = 5000)
    {
        if (is_null($this->id)) {
            return [];
        }
        $result = [];
        $count = 0;
        $DBHandle = DbConnect();
        foreach ($this->items as $value) {
            if (empty($value['id_ext']) || $value['type_ext'] !== "CALLS") {
                $result[] = $value;
                $count++;
                continue;
            }

            $billing = (new Table("cc_billing_customer", ["date", "start_date"]))
                ->getRow($DBHandle, ["id" => $value["id_ext"]]);
            if (count($billing) === 0) {
                continue;
            }

            $conditions = ["card_id" => $this->card, "stoptime" => ["<", $billing["date"]]];
            if (!empty($billing["start_date"])) {
                $conditions["stoptime"] = [">=", $billing["start_date"]];
            }

            $calls = (new Table("cc_call"))->getRows($DBHandle, $conditions, ["date"], "desc", [], $nb, $begin);
            foreach ($calls as $call) {
                $duration = get_timespan($call["sessiontiome"]);
                $item = ReceiptItem::create(
                    $this,
                    sprintf(_("Call to: %s, duration: %s"), $call['calledstation'], $duration),
                    $call['starttime'],
                    $call["sessionbill"],
                    true // what does true mean? original code was just copied from invoice.php including fields that don't exist here :(
                );
                $result[] = $item;
                $count += count($calls);
            }
        }
        $result["count"] = $count;

        return $result;
    }

    function nbDetailedItems(): int
    {
        $result = $this->loadDetailedItems();

        return $result["count"] ?? 0;
    }

    public function sumItemsPrice(): float
    {
        return array_sum(array_column($this->items, "price"));
    }

    public function insertReceiptItem(string $desc, string $price, ?string $date = null): bool
    {
        if (is_null($this->id)) {
            return false;
        }
        $date ??= (new DateTime())->format("Y-m-d H:i:s");
        $item = ReceiptItem::create($this, $desc, $date, $price);

        return $item->save();
    }
}
