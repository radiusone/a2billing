<?php

use A2billing\Table;

class invoice
{
    private $id;
    private $title;
    private $description;
    private $date;
    private $status;
    private $paid_status;
    private $card;
    private $username;
    private $reference;

    public function __construct($id)
    {
        $DBHandle = DbConnect();
        $instance_sub_table = new Table("cc_invoice", "*");
        $value = $instance_sub_table->getRow($DBHandle, ["id" => $id]);
        if (!is_null($value)) {
            $this->id = $value["id"];
            $this->card = $value["id_card"];
            $this->description = $value["description"];
            $this->title = $value["title"];
            $this->status = $value["status"];
            $this->paid_status = $value["paid_status"];
            $this->date = $value["date"];
            $this->reference = $value["reference"];
        }

        if (!is_null($this->card)) {
            $instance_sub_table = new Table("cc_card", "lastname, firstname,username");
            $value = $instance_sub_table->getRow($DBHandle, ["id" => $this->card]);

            if (!is_null($value)) {
                $this->username = $value["lastname"] . " " . $value["firstname"] . " " . "(" . $value["username"] . ")";
            }
        }

    }

    public function getId()
    {
        return $this->id;
    }

    public function getReference()
    {
        return $this->reference;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getCard()
    {
        return $this->card;
    }

    public function getPriority()
    {
        return $this->priority;
    }

    public function getStatus()
    {
        return $this->status;

    }
    public function getPaidStatus()
    {
        return $this->paid_status;

    }

    public function getDate()
    {
        return substr($this->date, 0, 10);
    }

    public function getUsernames()
    {
        return $this->username;
    }

    public function loadItems()
    {
        if (!is_null($this->id)) {
            $result = array ();
            $DBHandle = DbConnect();
            $instance_sub_table = new Table("cc_invoice_item");
            $return = $instance_sub_table->getRows($DBHandle, ["id_invoice" => $this->id]);
            $i = 0;
            foreach ($return as $value) {
                $comment = new InvoiceItem($value['id'], $value['description'], $value['date'], $value["price"], $value["VAT"],$value["type_ext"],$value["id_ext"]);
                $result[$i] = $comment;
                $i++;
            }
            return $result;
        } else {
            return null;
        }

    }

    public function loadDetailledItems()
    {
        if (!is_null($this->id)) {
            $result = array ();
            $DBHandle = DbConnect();
            $instance_sub_table = new Table("cc_invoice_item");
            $return = $instance_sub_table->getRows($DBHandle, ["id_invoice" => $this->id]);
            $i = 0;
            foreach ($return as $value) {
                if ($value['id_ext'] && $value['type_ext'] == "CALLS") {

                    $billing_table = new Table("cc_billing_customer", "date,start_date");
                    $billing_clause = "id = " . $value['id_ext'];
                    $result_billing = $billing_table->get_list($DBHandle, $billing_clause);
                    if (is_array($result_billing) && !empty ($result_billing[0]['date'])) {
                        $call_table = new Table("cc_call", "*");
                        $call_clause = " card_id = " . $this->card . " AND stoptime< '" . $result_billing[0]['date'] . "'";
                        if (!empty ($result_billing[0]['start_date'])) {
                            $call_clause .= " AND stoptime >= '" . $result_billing[0]['start_date'] . "'";
                        }
                        $return_calls = $call_table->get_list($DBHandle, $call_clause);
                        foreach ($return_calls as $call) {
                            $min = floor($call['sessiontime'] / 60);
                            $sec = $call['sessiontime'] % 60;
                            $item = new InvoiceItem(null, "CALL : " . $call['calledstation'] . " DURATION : " . $min . " min " . $sec . " sec", $call['starttime'], $call["sessionbill"], $value["VAT"], true);
                            $result[$i] = $item;
                            $i++;
                        }
                    }
                } else {
                    $item = new InvoiceItem($value['id'], $value['description'], $value['date'], $value["price"], $value["VAT"],$value["type_ext"],$value["id_ext"]);
                    $result[$i] = $item;
                    $i++;
                }
            }
            return $result;
        } else {
            return null;
        }
    }

    public function loadPayments()
    {
        if (!is_null($this->id)) {
            $DBHandle = DbConnect();
            $instance_sub_table = new Table(
                "cc_invoice_payment",
                "*",
                ["cc_logpayment" => ["cc_invoice_payment.id_payment", "cc_logpayment.id", "=", "NATURAL"]]
            );
            $result = $instance_sub_table->getRows($DBHandle, ["id_invoice" => $this->id], "date");
            return $result;
        } else {
            return null;
        }
    }

    public function delPayment($idpayment)
    {
        if (!is_null($this->id)) {
            $DBHandle = DbConnect();
            (new Table("cc_invoice_payment"))->deleteRow($DBHandle, ["id_invoice" => $this->id, "id_payment" => $idpayment]);
        } else {
            return null;
        }
    }

    public function addPayment($idpayment)
    {
        if (!is_null($this->id)) {
            $DBHandle = DbConnect();
            (new Table("cc_invoice_payment"))->addRow($DBHandle, ["id_invoice" => $this->id, "id_payment" => $idpayment]);
        } else {
            return null;
        }
    }

    public function changeStatus($status)
    {
        if (!is_null($this->id)) {
            $DBHandle = DbConnect();
            (new Table("cc_invoice"))->updateRow($DBHandle, ["paid_status" => $status], ["id" => $this->id]);
            if ($this->paid_status !=$status) {
                $items = $this -> loadItems();
                foreach ($items as $item) {
                    if ($item->getExtType() =="DID" && is_numeric($item->getExtId())) {
                        $did_table = new Table("cc_did_use", "*");
                        if($status == 0) $param = " reminded = 1, month_payed = month_payed-1";
                        else $param = " reminded = 0, month_payed = month_payed+1";
                        $QUERY = "UPDATE cc_did_use set $param WHERE id_did = '".$item->getExtId() ."' and activated = 1" ;
                        $did_table -> SQLExec ($DBHandle, $QUERY, 0);
                    }
                }
            }
        } else {
            return null;
        }
    }

    public function insertInvoiceItem($desc, $price, $VAT)
    {
        $DBHandle = DbConnect();
        (new Table("cc_invoice_item"))->addRow(
            $DBHandle,
            ["id_invoice" => $this->id, "description" => $desc, "price" => $price, "VAT" => $VAT],
            $return
        );
    }

    public static function getStatusDisplay($status)
    {
        switch ($status) {
            case 0 :
                return "OPEN";
            case 1 :
                return "CLOSE";

        }

    }

    public static function getPaidStatusDisplay($status)
    {
        switch ($status) {
            case 0 :
                return "UNPAID";
            case 1 :
                return "PAID";

        }
    }

}
