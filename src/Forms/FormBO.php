<?php

namespace A2billing\Forms;

use A2billing\A2bMailException;
use A2billing\Customer;
use A2billing\Mail;
use A2billing\Notification;
use A2billing\NotificationsDAO;
use A2billing\Payments\Invoice;
use A2billing\Payments\InvoiceItem;
use A2billing\Payments\Receipt;
use A2billing\Payments\ReceiptItem;
use A2billing\Realtime;
use A2billing\Table;
use A2billing\Ticket;
use DateTimeImmutable;
use Exception;
use PhpAgi\AMI as AGI_AsteriskManager;

class FormBO
{
    /**
     * Run before DID deletion
     *
     * @var numeric-string|int $id
     * @return void
     */
    public static function is_did_in_use($did_id)
    {
        $form = FormHandler::GetInstance();
        $id_cc_card = (new Table("cc_did_use", "id_cc_card"))
            ->getValue(["id_did" => $did_id, "releasedate" => null, "activated" => 1]);
        if (!empty($row)) {
            $form->FG_INTRO_TEXT_ASK_DELETION = sprintf(
                _("This DID is in use by customer %s, If you really want remove this DID, click on the delete button."),
                Customer::getName($id_cc_card, false)
            );
        }
    }

    /**
     * Run after DID deletion
     *
     * @param numeric-string|int $did_id
     * @return void
     */
    public static function did_use_delete($did_id): void
    {
        $form = FormHandler::GetInstance();
        (new Table("cc_did_use"))
            ->updateRow(
                ["releasedate" => "CURRENT_TIMESTAMP"],
                ["id_did" => $did_id, "releasedate" => null]
        );
        (new Table("cc_did_destination"))->deleteRow(["id_cc_did" => $did_id]);
    }

    /**
     * Run after DID creation
     *
     * @param numeric-string|int $did_id
     * @return void
     */
    public static function add_did_use($did_id)
    {
        $form = FormHandler::GetInstance();
        $processed = $form->getProcessed();
        (new Table("cc_did_use"))
            ->addRow(["id_did" => $did_id, "activated" => $processed["activated"] ?? 0]);
    }

    /**
     * Run after card edit by admin or agent
     *
     * @param numeric-string|int $card_id
     * @return void
     */
    public static function create_status_log($card_id)
    {
        $form = FormHandler::GetInstance();
        $processed = $form->getProcessed();
        $status = $processed['status'];
        $oldstatus = $processed['oldstatus'];
        if ("$oldstatus" === "$status") {
            return;
        }
        (new Table("cc_status_log"))
            ->addRow(["status" => $status, "id_cc_card" => $card_id]);
    }

    /**
     * Run after ticket creation
     *
     * @param numeric-string|int $ticket_id
     * @return void
     */
    public static function ticket_add($ticket_id): void
    {
        $form = FormHandler::GetInstance();
        $processed = $form->getProcessed();
        $title = $processed['title'];
        $card_id = $processed['creator'];
        $priority = $processed['priority'];
        $description = $processed['description'];
        $component_id = $processed['id_component'];

        if ((int)$processed["creator_type"] === Ticket::CUSTOMER) {
            $table = new Table(
                "cc_card",
                ["username", "firstname", "lastname", "language", "email"]
            );
        } elseif ((int)$processed["creator_type"] === Ticket::AGENT) {
            $table = new Table(
                "cc_agent",
                ["login AS username", "firstname", "lastname", "language", "email"]
            );
        } elseif ((int)$processed["creator_type"] === Ticket::ADMIN) {
            $table = new Table(
                "cc_ui_authen",
                [
                    "userid AS id",
                    "login AS username",
                    "SUBSTRING(name FROM 1 FOR POSITION(' ' IN name) AS firstname",
                    "SUBSTRING(name FROM POSITION(' ' IN name) + 1) AS lastname",
                    "'en' AS language",
                    "email"
                ]
            );
        } else {
            return;
        }

        $result = $table->getRow(["id" => $card_id]);
        if (!empty($result["email"])) {
            $owner = $result['username'] . " (" . $result['firstname'] . " " . $result['lastname'] . ")";
            try {
                self::send_new_ticket_email(
                    $owner,
                    (int)$ticket_id,
                    $description,
                    (int)$priority,
                    $title,
                    $result["language"],
                    $result["email"]
                );
            } catch (Exception $e) {
                $form->FG_TEXT_ADITION_ERROR = $e->getMessage();
            }
        }

        $component_table = new Table(
            "cc_support_component",
            ["email", "language"],
            ["cc_support" => ["id_support", "cc_support.id"]]
        );
        $result = $component_table
            ->getRow(["cc_support_component.id" => $component_id]);

        if (!empty($result["email"])) {
            try {
                self::send_new_ticket_email(
                    $owner,
                    (int)$ticket_id,
                    $description,
                    (int)$priority,
                    $title,
                    $result["language"],
                    $result["email"]
                );
            } catch (Exception $e) {
                $form->FG_TEXT_ADITION_ERROR = $e->getMessage();
            }
        }
    }

    /**
     * Used internally to send emails
     *
     * @throws Exception
     */
    private static function send_new_ticket_email(string $owner, int $id_ticket, string $description, int $priority, string $title, string $language, string $email): void
    {
        $mail = new Mail(Mail::$TYPE_TICKET_NEW, null, $language);
        $mail->replaceInEmail(Mail::$TICKET_OWNER_KEY, $owner);
        $mail->replaceInEmail(Mail::$TICKET_NUMBER_KEY, $id_ticket);
        $mail->replaceInEmail(Mail::$TICKET_DESCRIPTION_KEY, $description);
        $mail->replaceInEmail(Mail::$TICKET_PRIORITY_KEY, Ticket::DisplayPriority($priority));
        $mail->replaceInEmail(Mail::$TICKET_STATUS_KEY,"NEW");
        $mail->replaceInEmail(Mail::$TICKET_TITLE_KEY, $title);
        $mail->send($email);
    }

    /**
     * Run after an agent refill
     *
     * @return void
     */
    public static function add_agent_refill()
    {
        $form = FormHandler::GetInstance();
        $processed = $form->getProcessed();
        $credit = $processed['credit'];
        $agent_id = $processed['agent_id'];

        //REFILL CARD .. UPADTE AGENT
        (new Table("cc_agent"))
            ->updateRow(["credit" => ["credit + ?", $credit]], ["id" => $agent_id]);
    }

    /**
     * Run after creation of an agent
     *
     * @param numeric-string|int $agent_id
     * @return void
     */
    public static function creation_agent_refill($agent_id)
    {
        $form = FormHandler::GetInstance();
        $processed = $form->getProcessed();
        $credit = $processed["credit"];

        if ($credit > 0) {
            $description = _("CREATION AGENT REFILL");
            (new Table("cc_log_refill_agent"))
                ->addRow(compact("credit", "agent_id", "description"));
        }
    }

    /**
     * Run after user self-signup
     *
     * @param numeric-string|int $id_card
     * @return void
     * @throws \DateMalformedStringException
     * @throws A2bMailException
     */
    public static function processing_card_signup($id_card)
    {
        if (RELOAD_ASTERISK_IF_SIPIAX_CREATED) {
            self::create_sipiax_friends_reload($id_card);
        } else {
            self::create_sipiax_friends($id_card);
        }

        // create subscriptions
        global $A2B;
        $form = FormHandler::GetInstance();
        $processed = $form->getProcessed();
        $subscriber = $processed["subscriber_signup"];
        $sub = (new Table("cc_subscription_service", ["id", "fee", "label"]))
            ->getRow(["id" => $subscriber]);

        if (is_numeric($subscriber) && $sub && $sub["fee"] > 0) {
            $amount = $sub["fee"];
            $product_name = $sub["label"];

            $billdaybefor_anniversery = (int)$A2B->config['global']['subscription_bill_days_before_anniversary'];
            $start = new DateTimeImmutable();
            $startdate = $start->format("Y-m-d");
            $day_startdate = $start->format("j");

            $next = $start->modify("first day of next month");
            $lastday_of_next_month = $next->format("t");

            $limite_pay_date = $start->modify(" +$billdaybefor_anniversery days")->format("Y-m-d");

            if ($day_startdate > $lastday_of_next_month) {
                // eg subscription starts on March 31, can't bill on April 31
                $next_limite_pay_date = $next->modify("last day of this month");
            } else {
                $next_limite_pay_date = $start->modify("next month");
            }
            $next_bill_date = $next_limite_pay_date->modify("+$billdaybefor_anniversery days");

            (new Table("cc_card"))
                ->updateRow(["status" => 8], ["id" => $id_card]);
            (new Table("cc_card_subscription"))
                ->addRow(
                    [
                        "id_cc_card" => $id_card,
                        "id_subscription_fee" => $subscriber,
                        "product_name" => $product_name,
                        "paid_status" => 1,
                        "startdate" => $startdate,
                        "next_billing_date" => $next_bill_date,
                        "limit_pay_date" => $limite_pay_date,
                        "last_run" => $startdate
                    ],
                    "id",
                    $id_card_subscription
                );

            $reference = Invoice::generateReference();
            $title = _("SUBSCRIPTION INVOICE REMINDER");
            $description = sprintf(
                _("You have %d days to pay your subscription with this invoice (REF: %s) or the account will be automatically deactived"),
                $billdaybefor_anniversery,
                $reference
            );

            //CREATE INVOICE If a new card then just an invoice item in the last invoice
            $invoice = Invoice::create($id_card, $description, $title, $reference, Invoice::STATUS_CLOSED);
            if ($invoice->save()) {
                $description = "Subscription service";
                $date = $start->format("Y-m-d H:i:s");
                $item = InvoiceItem::create($invoice, $description, $date, $amount, 0, "SUBSCR", $id_card_subscription);
                $item->save();
            }

            //insert charge
            (new Table("cc_charge"))
                ->addRow(
                    [
                        "id_cc_card" => $id_card,
                        "amount" => $amount,
                        "chargetype" => 3,
                        "id_cc_card_subscription" => $sub["id"],
                        "invoiced_status" => 1,
                    ]
                );

            $mail = new Mail(Mail::$TYPE_SUBSCRIPTION_UNPAID,$id_card);
            // what's this???
            $mail -> replaceInEmail(Mail::$DAY_REMAINING_KEY,$day_remaining ?? "");
            $mail -> replaceInEmail(Mail::$INVOICE_REF_KEY,$reference);
            $mail -> replaceInEmail(Mail::$SUBSCRIPTION_FEE,get_money($amount));
            $mail -> replaceInEmail(Mail::$SUBSCRIPTION_ID,$sub['id']);
            $mail -> replaceInEmail(Mail::$SUBSCRIPTION_LABEL,$product_name);
            try {
                $mail -> send();
            } catch (A2bMailException $e) {
            }
        }

        // create notification
        NotificationsDAO::addNotification(
            "added_new_signup",
            Notification::$MEDIUM,
            Notification::$CUST,
            $id_card,
            Notification::$LINK_CARD,
            $id_card
        );
    }

    /**
     * Run after agent commission added
     *
     * @param numeric-string|int $agent_commission_id
     * @return void
     */
    public static function processing_commission_add($agent_commission_id)
    {
        $form = FormHandler::GetInstance();
        $processed = $form->getProcessed();
        $id_agent = $processed["id_agent"];
        if (!empty($id_agent)) {
            //update record with agent commission
            $table_agent = new Table('cc_agent', 'commission');
            $agent_com = $table_agent->getValue(["id" => $id_agent]) ?? 0;
            // todo: this should be a negative check?
            if (empty($agent_com)) {
                (new Table("cc_agent_commission"))
                    ->updateRow(
                        ["commission_percent" => $agent_com],
                        ["id" => $agent_commission_id]
                    );
            }
            $amount = $processed['amount'];
            $sign = $amount > 0 ? "+" : "-";
            $table_agent->updateRow(
                ["com_balance" => ["com_balance $sign ?", abs($amount)]],
                ["id" => $id_agent]
            );
        }
    }

    /**
     * Run after card creation by admin
     *
     * @param numeric-string|int $card_id
     * @return void
     */
    public static function processing_card_add($card_id)
    {
        self::create_sipiax_friends($card_id);

        $form = FormHandler::GetInstance();
        $processed = $form->getProcessed();
        $credit = $processed['credit'];

        if ($credit) {
            $description = _("CREATION CARD REFILL");
            (new Table("cc_logrefill"))
                ->addRow(compact("credit", "card_id", "description"));
        }

        self::create_lock_card($card_id);
    }

    /**
     * Run after card deleted by agent
     *
     * @param numeric-string|int $card_id
     * @return void
     */
    public static function processing_card_del_agent($card_id)
    {
        $credit = (new Table("cc_card"))->getValue(["id" => $card_id]);
        if ($credit != 0) {
            $sign = $credit > 0 ? "+" : "-";
            (new Table("cc_agent"))
                ->updateRow(["credit" => ["credit $sign ?", abs($credit)]], ["id" => $_SESSION["agent_id"]]);

            $description = gettext("DELETION CARD REFILL");
            $correction = 0 - $credit;
            (new Table("cc_logrefill"))
                ->addRow(["credit" => $correction, "card_id" => $card_id, "refill_type" => 1, "description" => $description]);
            if ($credit > 0) {
                $table = new Table(
                    "cc_card",
                    ["id_agent"],
                    ["cc_card_group" => ["cc_card.id_group", "cc_card_group.id"]]
                );
                $id_agent = $table->getValue(["cc_card.id" => $card_id]);

                if ($id_agent) {
                    // test if the agent exist and get its commission
                    $comm = (new Table("cc_agent", ["commission"]))->getValue(["id" => $id_agent]);
                    if ($comm) {
                        $commission = a2b_round($credit * ($comm / 100));
                        $description = sprintf(
                            "%s\n%s\n%s\n%s",
                            _("CORRECT COMMISSION AFTER CARD DELETED!"),
                            sprintf(_("Card ID: %s"), $card_id),
                            sprintf(_("Amount: %s"), get_money($credit)),
                            sprintf(_("Commission applied: %s"), get_percent($comm))
                        );
                        (new Table("cc_agent_commission"))
                            ->addRow(
                                [
                                    "id_payment" => -1,
                                    "id_card" => $card_id,
                                    "amount" => $commission * -1,
                                    "description" => $description,
                                    "id_agent" => $id_agent
                                ]
                            );

                        (new Table("cc_agent"))
                            ->updateRow(
                                ["com_balance" => ["com_balance - ?", $commission]],
                                ["id" => $id_agent]
                            );
                    }
                }
            }
        }
    }

    /**
     * Run after card creation by agent
     *
     * @param numeric-string|int $card_id
     * @return void
     */
    public static function processing_card_add_agent($card_id)
    {
        self::create_sipiax_friends($card_id);
        self::create_lock_card($card_id);
    }

    /**
     * Run after refill created
     * @return void
     */
    public static function processing_refill_add()
    {
        $form = FormHandler::GetInstance();
        $processed = $form->getProcessed();
        $credit = $processed["credit"];
        $card_id = $processed["card_id"];

        (new Table("cc_card"))
            ->updateRow(["credit" => ["credit + ?", $credit]], ["id" => $card_id]);

        //add invoice
        if (!$processed['added_invoice']) {
            return;
        }
        //CREATE AND UPDATE REF NUMBER
        $refills = getRefillType_List();
        $type = (int)$processed['refill_type'];
        $reference = Invoice::generateReference();
        $date = $processed['date'];
        $title = sprintf(_("%s REFILL"), $refills[$type] ?? "");
        $description = gettext("Invoice for refill");

        $invoice = Invoice::create($card_id, $description, $title, $reference, Invoice::STATUS_OPEN, Invoice::PAIDSTATUS_UNPAID, $date);
        //load vat of this card
        if ($invoice->save()) {
            $description = $processed['description'];
            $vat = (new Table("cc_card", ["vat"]))->getValue(["id" => $card_id]) ?? 0;
            $item = InvoiceItem::create($invoice, $description, $date, $credit, $vat);
            $item->save();
        }
    }

    /**
     * Run after DID destination creation
     *
     * @return void
     */
    public static function did_destination_add()
    {
        $form = FormHandler::GetInstance();
        $processed = $form->getProcessed();

        $id_cc_did = $processed['id_cc_did'];
        $id_cc_card = $processed['id_cc_card'];

        // 3 cases to handle :
        // the DID is released so we can purchase it
        // the DID is used by an other user, we might want to change
        // the DID is new nothing in cc_did_use

        $did_table = new Table(
            "cc_did_use",
            ["cc_did_use.id", "id_cc_card", "fixrate", "billingtype", "releasedate"],
            ["cc_did" => ["cc_did_use.id_did", "cc_did.id"]]
        );
        $did = $did_table->getRow(["id_did" => $id_cc_did], ["cc_did_use.id"], "desc");
        if ($did) {
            // check the id_cc_card, if id_cc_card is null it means it has been released
            // otherwise did_is used without a registered card
            $existing_owner_id = $did["id_cc_card"] ?: -1;
        } else {
            // No result, the DID hasnt been purchased yet
            $existing_owner_id = -2;
        }

        if ($existing_owner_id >= -2 && $existing_owner_id != $id_cc_card) {
            // The did ownership has changed and we need to update. (regardless of how it's billed)
            if ($did['billingtype'] == 0 || $did['billingtype'] == 1) {
                $rate = $did['fixrate'];
                (new Table("cc_charge"))
                    ->addRow(["id_cc_card" => $id_cc_card, "amount" => $rate, "chargetype" => 2, "id_cc_did" => $id_cc_did]);

                (new Table("cc_card"))
                    ->updateRow(["credit" => ["credit - ?", $rate]], ["id" => $id_cc_card]);
            }

            (new Table("cc_did"))
                ->updateRow(["iduser" => $id_cc_card, "reserved" => 1], ["id" => $id_cc_did]);

            (new Table("cc_did_use"))
                ->updateRow(["releasedate" => "CURRENT_TIMESTAMP"], ["id_did" => $id_cc_did, "activated" => 0]);

            // Should we do something special when billing != 0 or 1?
            (new Table("cc_did_use"))
                ->addRow(["activated" => 1, "id_cc_card" => $id_cc_card, "id_did" => $id_cc_did, "month_payed" => 1]);
        }
        // else existing_owner_id is already correctly set due to prior destinations on the same DID
    }

    /**
     * Run after DID destination deletion
     * Release a DID and set the DID use correctly
     *
     * @param numeric-string|int $did_destination_id
     * @return void
     */
    public static function did_destination_del($did_destination_id)
    {
        $form = FormHandler::GetInstance();
        $db = $form->DBHandle;

        $QUERY_did = <<< SQL
            SELECT cc_did.id AS did_id, dg.dest_count AS destination_count
            FROM cc_did
            LEFT JOIN cc_did_destination ON cc_did_destination.id_cc_did = cc_did.id
            LEFT JOIN (
                SELECT st1.id, count(*) AS dest_count
                FROM cc_did AS st1
                INNER JOIN cc_did_destination AS st2 ON st2.id_cc_did = st1.id
                GROUP BY st1.id
            ) AS dg ON dg.id = cc_did.id
            WHERE cc_did_destination.id = ?
            SQL;
        // todo: this comment probably just means someone didn't know how joins work? left query as-is for now
        // Also possible to do FROM cc_did_destination AS dest1 JOIN cc_did JOIN cc_did_destination AS dest2 GROUP BY dest1.id, cc_did.id
        // To get the count but NULL and NO row behavoir is flaky no matter the types of joins used. Therefore using SubSelect.
        $result_did_dest = $db->GetArray($QUERY_did, [$did_destination_id]);

        if ($result_did_dest) {
            $row = $result_did_dest[0];
            if ($row["destination_count"] < 2) {
                // Only remove did from card if this is the LAST destination connecting the two.
                // < 2, not 1 because destination is deleted after this call.
                $choose_did = $row['did_id'];

                (new Table("cc_did"))
                    ->updateRow(["iduser" => 0, "reserved" => 0], ["id" => $choose_did]);

                (new Table("cc_did_use"))
                    ->updateRow(["releasedate" => "CURRENT_TIMESTAMP"], ["id_did" => $choose_did, "activated" => 1]);

                (new Table("cc_did_use"))
                    ->addRow(["activated" => 0, "id_did" => $choose_did]);
            }
        }
    }

    /**
     * Run after creating a recurring billing item
     *
     * @param numeric-string|int $new_billing
     * @return void
     * @throws A2bMailException
     */
    public static function proccessing_billing_customer($new_billing)
    {
        $form = FormHandler::GetInstance();
        $processed = $form->getProcessed();
        //find the last billing
        $card_id = $processed["id_card"];
        $date_bill = $processed["date"];
        $date = date("Y-m-d h:i:s");

        //GET VAT
        $card_table = new Table("cc_card", ["vat", "typepaid", "credit"]);
        $card_result = $card_table->getRow(["id" => $card_id]);
        $vat = $card_result[0] ?? 0;

        // FIND THE LAST BILLING for this card
        $last_billing_date = (new Table("cc_billing_customer", ["date"]))
            ->getValue(
                ["id_card" => $card_id, "id" => ["!=", $new_billing]],
                ["date"],
                "desc"
            );
        $call_conditions = ["card_id" => $card_id, "stop_time" => ["<", $date_bill]];
        $charge_conditions = ["id_cc_card" => $card_id, "creationdate" => ["<", $date_bill], "charged_status" => 1];
        $start_date = null;

        if ($last_billing_date) {
            $call_conditions["stoptime"] = [">=", $last_billing_date];
            $charge_conditions["creationdate"] = [">=", $last_billing_date];
            $desc_billing = sprintf(_("Call costs between %s and %s"), $last_billing_date, $date_bill);
            $desc_billing_postpaid = sprintf(_("Charges between %s and %s"), substr($last_billing_date, 0, 10), $date_bill);
            $start_date = $last_billing_date;
        } else {
            $desc_billing = sprintf(_("Calls cost before %s"), $date_bill);
            $desc_billing_postpaid = sprintf(_("Amount for period before %s"), $date_bill);
        }

        $invoice_table = new Table(
            "cc_billing_customer",
            ["SUM(items.total_price) AS total"],
            [
                "cc_invoice" => ["cc_billing_customer.id_invoice", "cc_invoice.id"],
                "(SELECT id_invoice, ROUND(SUM(price), 2) AS total_price FROM cc_invoice_item WHERE type_ext = 'POSTPAID' GROUP BY id_invoice ) AS items" => ["cc_invoice.id", "items.id_invoice"],
            ]
        );
        $lastpostpaid_amount = $invoice_table->getValue(
            [
                "cc_billing_customer.id_card" => $card_id,
                "cc_invoice.paid_status" => Invoice::PAIDSTATUS_UNPAID,
                "cc_billing_customer.id" => ["!=", $new_billing]
            ],
            ["cc_invoice.date"],
            "desc"
        ) ?? 0;

        $call_table = new Table("cc_call", ["COALESCE(SUM(sessionbill), 0)"]);
        $amount_calls = $call_table->getValue($call_conditions);
        // COMMON BEHAVIOUR FOR PREPAID AND POSTPAID ... GENERATE A RECEIPT FOR THE CALLS OF THE MONTH
        if ($amount_calls) {
            /// create receipt
            $title = _("SUMMARY OF CALLS");
            $description = _("Summary of the calls charged since the last billing");
            $receipt = Receipt::create($card_id, $description, $title, Receipt::STATUS_CLOSED);
            if ($receipt->save()) {
                $item = ReceiptItem::create($receipt, $desc_billing, $amount_calls, $date, "CALLS", $new_billing);
                $item->save();
            }
        }

        // GENERATE RECEIPT FOR CHARGE ALREADY CHARGED
        $charges_table = new Table("cc_charge", ["id", "amount", "description", "creationdate"]);
        $charges = $charges_table->getRows($charge_conditions);
        if (count($charges)) {
            $title = _("SUMMARY OF CHARGES");
            $description = _("Summary of the charge charged since the last billing.");
            $receipt = Receipt::create($card_id, $description, $title, Receipt::STATUS_CLOSED);
            if ($receipt->save()) {
                foreach ($charges as $charge) {
                    $item = ReceiptItem::create($receipt, $charge["description"], $charge["amount"], $charge["creationdate"], "CHARGE", $charge["id"]);
                    $item->save();
                }
            }
        }

        $total = 0;
        $total_vat = 0;
        // GENERATE INVOICE FOR CHARGE NOT YET CHARGED
        $charge_conditions["charged_status"] = 0;
        $charge_conditions["invoiced_status"] = 0;
        $charges = (new Table("cc_charge"))->getRows($charge_conditions);
        $invoice = new Invoice(null);
        if (count($charges)) {
            $reference = Invoice::generateReference();
            $title = _("BILLING CHARGES");
            $description = _("This invoice is for some charges unpaid since the last billing.")." ".$desc_billing_postpaid;
            $invoice = Invoice::create($card_id, $description, $title, $reference, Invoice::STATUS_CLOSED);
            if ($invoice->save()) {
                foreach ($charges as $charge) {
                    $item = InvoiceItem::create($invoice, $charge["description"], $date, $charge["amount"], $vat, "CHARGE", $charge["id"]);
                    $item->save();
                    $total += round($charge["amount"], 2);
                    $total_vat += round($charge["amount"] + ($charge["amount"] * $vat / 100), 2);
                }
            }
        }

        // behaviour postpaid
        if ($card_result["typepaid"] == 1 && $card_result["credit"] + $lastpostpaid_amount < 0) {

            //GENERATE AN INVOICE TO COMPLETE THE BALANCE
            if (empty($invoice->id)) {
                $reference = Invoice::generateReference();
                $title = gettext("BILLING POSTPAID");
                $description = gettext("Invoice for POSTPAID");
                $invoice = Invoice::create($card_id, $description, $title, $reference, Invoice::STATUS_CLOSED);
                $invoice->save();
            }

            if (!empty($invoice->id)) {
                $description = $desc_billing_postpaid;
                $amount = abs($card_result["credit"] + $lastpostpaid_amount);
                $total += $amount;
                $total_vat =$total_vat + round($amount *(1+($vat/100)),2);
                $item = InvoiceItem::create($invoice, $description, $date, $amount, $vat, "POSTPAID", $new_billing);
                $item->save();
            }
        }

        if (!empty($invoice->id)) {
            $values = ["id_invoice" => $invoice->id];
            if ($start_date) {
                $values["start_date"] = $start_date;
            }
            (new Table("cc_billing_customer"))->updateRow($values, ["id" => $new_billing]);

            //Send a mail for invoice to pay
            $total = round($total,2);
            $mail = new Mail(Mail::$TYPE_INVOICE_TO_PAY, $card_id);
            $mail->replaceInEmail(Mail::$INVOICE_REFERENCE_KEY, $invoice->reference);
            $mail->replaceInEmail(Mail::$INVOICE_TITLE_KEY, $invoice->title);
            $mail->replaceInEmail(Mail::$INVOICE_DESCRIPTION_KEY, $invoice->description);
            $mail->replaceInEmail(Mail::$INVOICE_TOTAL_KEY, $total);
            $mail->replaceInEmail(Mail::$INVOICE_TOTAL_VAT_KEY, $total_vat);
            $mail->send();
        }
    }

    /**
     * Run after invoice creation
     * Adds a reference number to the invoice
     *
     * @param numeric-string|int $id_invoice
     * @return void
     */
    public static function create_invoice_reference($id_invoice)
    {
        //CREATE AND UPDATE REF NUMBER
        $reference = Invoice::generateReference();
        $invoice = new Invoice($id_invoice);
        $invoice->reference = $reference;
        $invoice->save();
    }

    /**
     * Run after a payment is made
     *
     * @param numeric-string|int $id_payment
     * @return void
     */
    public static function create_refill_after_payment($id_payment)
    {
        $form = FormHandler::GetInstance();
        $processed = $form->getProcessed();
        $date = $processed["date"];
        $card_id = $processed["card_id"];
        $amount = $processed["payment"];

        if (!$processed["added_refill"] && !$processed["added_commission"]) {
            return;
        }

        if ($processed["added_refill"]) {
            // CREATE REFILL
            $refill_type = (int)$processed['payment_type'];
            $description = $processed['description'];
            $vat = (new Table("cc_card", "vat"))->getValue(["id" => $card_id]) ?? 0;
            $credit = $amount / (1 + $vat / 100);

            $insert_values = compact("date", "credit", "card_id", "refill_type", "description");
            (new Table("cc_logrefill"))->addRow($insert_values, "id", $id_refill);

            // REFILL CARD - UPDATE CARD
            $insert_values = ["credit" => ["credit + ?", $credit]];
            (new Table("cc_card"))->updateRow($insert_values, ["id" => $card_id]);

            // LINK THE REFILL TO THE PAYMENT .. UPADTE PAYMENT
            $insert_values = ["id_logrefill" => $id_refill];
            (new Table("cc_logpayment"))->updateRow($insert_values, ["id" => $id_payment]);

            // Create invoice associated
            $refills = getRefillType_List();
            $title = sprintf(_("%s REFILL"), $refills[$refill_type] ?? "");
            $reference = Invoice::generateReference();
            $description = gettext("Invoice for refill");
            $invoice = Invoice::create($card_id, $description, $title, $reference, Invoice::STATUS_CLOSED, Invoice::PAIDSTATUS_PAID, $date);
            if ($invoice->save()) {
                //add payment to this invoice
                (new Table("cc_invoice_payment"))
                    ->addRow(["id_invoice" => $invoice->id, "id_payment" => $id_payment]);
                $item = InvoiceItem::create($invoice, $description, $date, $credit, $vat);
                $item->save();
            }
        }

        if (!$processed["added_commission"]) {
            return;
        }
        $table = new Table("cc_card", "id_agent", ["cc_card_group" => ["cc_card.id_group", "cc_card_group.id"]]);
        $id_agent = $table->getValue(["cc_card.id" => $card_id]);

        if ($id_agent) {
            // update refill & payment to keep a trace of agent in the timeline
            if (!empty($id_refill)) {
                (new Table("cc_logrefill"))
                    ->updateRow(["agent_id" => $id_agent], ["id" => $id_refill]);
            }
            (new Table("cc_logpayment"))
                ->updateRow(["agent_id" => $id_agent], ["id" => $id_payment]);

            $comm = (new Table("cc_agent", ["commission"]))
                ->getValue(["id" => $id_agent]);

            if ($comm) {
                $commission = $amount * ($comm / 100);
                $description = sprintf(
                    "%s\n%s\n%s\n%s\n%s\n%s",
                    _("AUTOMATICALY GENERATED COMMISSION!"),
                    sprintf(_("Card ID: %s"), $card_id),
                    sprintf(_("Payment ID: %s"), $id_payment),
                    sprintf(_("Payment amount: %s"), get_money($amount)),
                    sprintf(_("Commission applied: %s"), get_percent($comm)),
                    sprintf(_("Commission paid: %s"), get_money($commission))
                );
                $insert_values = [
                    "id_payment" => $id_payment,
                    "id_card" => $card_id,
                    "amount" => $commission,
                    "description" => $description,
                    "id_agent" => $id_agent
                ];
                (new Table("cc_agent_commission"))->addRow($insert_values);
                (new Table("cc_agent"))
                    ->updateRow(
                        ["com_balance" => ["com_balance + ?", $commission]],
                        ["id" => $id_agent]
                    );
            }
        }
    }

    /**
     * Run after an agent refill is created
     *
     * @param numeric-string|int $id_payment
     * @return void
     */
    public static function create_agent_refill($id_payment)
    {
        $form = FormHandler::GetInstance();
        $processed = $form->getProcessed();

        if (!$processed["added_refill"]) {
            return;
        }
        $date = $processed["date"];
        $credit = $processed["payment"];
        $agent_id = $processed["agent_id"];
        $refill_type = $processed["payment_type"];
        $description = $processed["description"];
        //CREATE REFILL
        (new Table("cc_logrefill_agent"))
            ->addRow(
                compact("date", "credit", "agent_id", "refill_type", "description"),
                "id",
                $id_refill
            );

        //REFILL AGENT .. UPADTE AGENT
        (new Table("cc_agent"))
            ->updateRow(["credit" => ["credit + ?", $credit]], ["id" => $agent_id]);

        //LINK THE REFILL TO THE PAYMENT .. UPADTE PAYMENT
        (new Table("cc_logpayment_agent"))
            ->updateRow(["id_logrefill" => $id_refill], ["id" => $id_payment]);
    }

    /**
     * @param numeric-string|int $card_id
     * @return void
     */
    public static function create_sipiax_friends($card_id)
    {
        $form = FormHandler::GetInstance();
        $processed = $form->getProcessed();
        $sip = intval($processed['sip_buddy'] ?? 0);
        $iax = intval($processed['iax_buddy'] ?? 0);
        if (!$sip && !$iax) {
            return;
        }

        $username = $form->REALTIME_SIP_IAX_INFO[0] ?? $processed['username'];
        $uipass = $form->REALTIME_SIP_IAX_INFO[2] ?? $processed['uipass'];

        $instance_realtime = new Realtime();
        $instance_realtime->insert_voip_config((bool)$sip, (bool)$iax, $card_id, $username, $uipass);

        // Save info in table and in sip file
        if ($sip) {
            $instance_realtime->create_trunk_config_file();
        }

        // Save info in table and in iax file
        if ($iax) {
            $instance_realtime->create_trunk_config_file("iax");
        }
    }

    /**
     * @return void
     */
    public static function create_sipiax_friends_reload($card_id)
    {
        self::create_sipiax_friends($card_id);

        $as = new AGI_AsteriskManager();
        $res = $as->connect(MANAGER_HOST,MANAGER_USERNAME,MANAGER_SECRET);
        if ($res) {
            $as->Command('sip reload');
            $as->Command('iax2 reload');
            $as->disconnect();
        } else {
            echo "Error : Manager Connection";
        }
    }

    /**
     * Run after card creation
     * Updates the lock date if card was created locked
     *
     * @param numeric-string|int $card_id
     * @return void
     */
    public static function create_lock_card($card_id)
    {
        $form = FormHandler::GetInstance();
        $processed = $form->getProcessed();
        if (!$processed["block"]) {
            return;
        }
        (new Table("cc_card"))
            ->updateRow(["lock_date" => "CURRENT_TIMESTAMP"], ["id" => $card_id]);
    }

    /**
     * Run before card edit is processed
     * Updates the lock date if the card is being locked
     * @return void
     */
    public static function change_card_lock($card)
    {
        $form = FormHandler::GetInstance();
        $processed = $form->getProcessed();
        $instance_sub_table = new Table("cc_card", ["block"]);
        $card_lock_info = $instance_sub_table->getValue(["id" => $card]);
        if ($card_lock_info != $processed["block"] && $processed["block"] == 1) {
            $instance_sub_table
                ->updateRow(["lock_date" => "CURRENT_TIMESTAMP"], ["id" => $card]);
        }
    }
}
