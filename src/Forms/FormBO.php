<?php

namespace A2billing\Forms;

use A2billing\A2bMailException;
use A2billing\Agent;
use A2billing\Connection;
use A2billing\Customer;
use A2billing\Mail;
use A2billing\Notification;
use A2billing\NotificationsDAO;
use A2billing\Payments\Invoice;
use A2billing\Payments\InvoiceItem;
use A2billing\Payments\PaymentDocument;
use A2billing\Payments\Receipt;
use A2billing\Payments\ReceiptItem;
use A2billing\Realtime;
use A2billing\Ticket;
use DateTimeImmutable;
use Exception;
use PhpAgi\AMI as AGI_AsteriskManager;

class FormBO
{
    /**
     * Run before DID deletion
     *
     * @param numeric-string|int $did_id
     * @return void
     */
    public static function is_did_in_use($did_id)
    {
        $form = FormHandler::GetInstance();
        $id_cc_card = Connection::getConnection("cc_did_use")
            ->where(["id_did" => $did_id, "releasedate" => null, "activated" => 1])
            ->value("id_cc_card");
        if (empty($id_cc_card)) {
            return;
        }
        if (is_customer()) {
            $destinations = Connection::getConnection("cc_did_destination", "destination")
                ->where(["id_cc_did" => $did_id, "id_cc_card" => Customer::id(), "activated" => 1])
                ->get();
            if (empty($destinations)) {
                return;
            }
            $form->delete_message_intro = sprintf(
            _("This DID is in use for the following %s. If you really want remove this DID, click on the delete button: %s"),
                ngettext(_("destination"), _("destinations"), count($destinations)),
                $destinations->implode("destination", ", ")
            );

            return;
        }
        $form->delete_message_intro = sprintf(
            _("This DID is in use by customer %s. If you really want remove this DID, click on the delete button."),
            Customer::getName($id_cc_card, false)
        );
    }

    /**
     * Run after DID deletion
     *
     * @param numeric-string|int $did_id
     * @return void
     */
    public static function did_use_delete($did_id): void
    {
        Connection::getConnection("cc_did_use")
            ->where(["id_did" => $did_id, "releasedate" => null])
            ->update(["releasedate" => Connection::getConnection()->raw("CURRENT_TIMESTAMP")]);
        Connection::getConnection("cc_did_destination")->where("id_cc_did", $did_id)->delete();
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
        Connection::getConnection("cc_did_use")
            ->insert(["id_did" => $did_id, "activated" => $processed["activated"] ?? 0]);
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
        Connection::getConnection("cc_status_log")
            ->insert(["status" => $status, "id_cc_card" => $card_id]);
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
            $qb = Connection::getConnection("cc_card", "username", "firstname", "lastname", "language", "email");
        } elseif ((int)$processed["creator_type"] === Ticket::AGENT) {
            $qb = Connection::getConnection("cc_agent", "login AS username", "firstname", "lastname", "language", "email");
        } elseif ((int)$processed["creator_type"] === Ticket::ADMIN) {
            $qb = Connection::getConnection("cc_ui_authen", "userid AS id", "login AS username")
                ->selectRaw("SUBSTRING(name FROM 1 FOR POSITION(' ' IN name) AS firstname")
                ->selectRaw("SUBSTRING(name FROM POSITION(' ' IN name) + 1) AS lastname")
                ->selectRaw("? AS language", ["en"])
                ->addSelect("email");
        } else {
            return;
        }

        $owner = "";
        $result = $qb->where("id", $card_id)->first();
        if ($result && !empty($result["email"])) {
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
                $form->add_message_error = $e->getMessage();
            }
        }

        $result = Connection::getConnection("cc_support_component", "email", "language")
            ->leftJoin("cc_support", "id_support", "cc_support.id")
            ->where("cc_support_component.id", $component_id)
            ->first();

        if ($result && !empty($result["email"])) {
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
                $form->add_message_error = $e->getMessage();
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
        Connection::getConnection("cc_agent")
            ->where("id", $agent_id)
            ->increment("credit", $credit);
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
            Connection::getConnection("cc_log_refill_agent")
                ->insert(compact("credit", "agent_id", "description"));
        }
    }

    /**
     * Run after user self-signup
     *
     * @param numeric-string|int $id_card
     * @return void
     * @throws A2bMailException
     */
    public static function processing_card_signup($id_card)
    {
        global $A2B;

        if ($A2B->config["signup"]['reload_asterisk_if_sipiax_created'] ?? false) {
            self::create_sipiax_friends_reload($id_card);
        } else {
            self::create_sipiax_friends($id_card);
        }

        // create subscriptions
        $form = FormHandler::GetInstance();
        $processed = $form->getProcessed();
        $subscriber = $processed["subscriber_signup"];
        $sub = Connection::getConnection("cc_subscription_service", "id", "fee", "label")
            ->where("id", $subscriber)
            ->first();

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

            Connection::getConnection("cc_card")
                ->where("id", $id_card)
                ->update(["status" => 8]);
            $id_card_subscription = Connection::getConnection("cc_card_subscription")
                ->insertGetId(
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
                    "id"
                );

            $reference = Invoice::generateReference();
            $title = _("SUBSCRIPTION INVOICE REMINDER");
            $description = sprintf(
                _("You have %d days to pay your subscription with this invoice (REF: %s) or the account will be automatically deactived"),
                $billdaybefor_anniversery,
                $reference
            );

            //CREATE INVOICE If a new card then just an invoice item in the last invoice
            $invoice = Invoice::create($id_card, $description, $title, $reference, PaymentDocument::STATUS_CLOSED);
            if ($invoice->save()) {
                $description = "Subscription service";
                $date = $start->format("Y-m-d H:i:s");
                $item = InvoiceItem::create($invoice, $description, $date, $amount, 0, "SUBSCR", $id_card_subscription);
                $item->save();
            }

            //insert charge
            Connection::getConnection("cc_charge")
                ->insert([
                    "id_cc_card" => $id_card,
                    "amount" => $amount,
                    "chargetype" => 3,
                    "id_cc_card_subscription" => $sub["id"],
                    "invoiced_status" => 1,
                ]);

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
            $agent_com = Connection::getConnection("cc_agent")
                ->where("id", $id_agent)
                ->value("commission") ?? 0;
            // todo: this should be a negative check?
            if (empty($agent_com)) {
                Connection::getConnection("cc_agent_commission")
                    ->where("id", $agent_commission_id)
                    ->update(["commission_percent" => $agent_com]);
            }
            $amount = $processed['amount'];
            Connection::getConnection("cc_agent")
                ->where("id", $id_agent)
                ->increment("com_balance", $amount);
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
            Connection::getConnection("cc_logrefill")
                ->insert(compact("credit", "card_id", "description"));
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
        $credit = Connection::getConnection("cc_card")
            ->where("id", $card_id)
            ->value("credit");
        if ($credit != 0) {
            Connection::getConnection("cc_agent")
                ->where("id", Agent::id())
                ->increment("credit", $credit);

            $description = gettext("DELETION CARD REFILL");
            $correction = 0 - $credit;
            Connection::getConnection("cc_logrefill")
                ->insert(["credit" => $correction, "card_id" => $card_id, "refill_type" => 1, "description" => $description]);
            if ($credit > 0) {
                $id_agent = Connection::getConnection("cc_card")
                    ->leftJoin("cc_card_group", "cc_card.id_group", "cc_card_group.id")
                    ->where("cc_card.id", $card_id)
                    ->value("id_agent");

                if ($id_agent) {
                    // test if the agent exist and get its commission
                    $comm = Connection::getConnection("cc_agent")
                        ->where("id", $id_agent)
                        ->value("commission");
                    if ($comm) {
                        $commission = a2b_round($credit * ($comm / 100));
                        $description = sprintf(
                            "%s\n%s\n%s\n%s",
                            _("CORRECT COMMISSION AFTER CARD DELETED!"),
                            sprintf(_("Card ID: %s"), $card_id),
                            sprintf(_("Amount: %s"), get_money($credit)),
                            sprintf(_("Commission applied: %s"), get_percent($comm))
                        );
                        Connection::getConnection("cc_agent_commission")
                            ->insert(
                                [
                                    "id_payment" => -1,
                                    "id_card" => $card_id,
                                    "amount" => $commission * -1,
                                    "description" => $description,
                                    "id_agent" => $id_agent
                                ]
                            );

                        Connection::getConnection("cc_agent")
                            ->where("id", $id_agent)
                            ->decrement("com_balance", $commission);
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

        Connection::getConnection("cc_card")
            ->where("id", $card_id)
            ->increment("credit", $credit);

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

        $invoice = Invoice::create($card_id, $description, $title, $reference, PaymentDocument::STATUS_OPEN, Invoice::PAIDSTATUS_UNPAID, $date);
        //load vat of this card
        if ($invoice->save()) {
            $description = $processed['description'];
            $vat = Connection::getConnection("cc_card")->where("id", $card_id)->value("vat") ?? 0;
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

        $did = Connection::getConnection("cc_did_use", "cc_did_use.id", "id_cc_card", "fixrate", "billingtype", "releasedate")
            ->leftJoin("cc_did", "cc_did_use.id_did", "cc_did.id")
            ->where("id_did", $id_cc_did)
            ->orderBy("cc_did_use.id", "DESC")
            ->first();
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
                Connection::getConnection("cc_charge")
                    ->insert(["id_cc_card" => $id_cc_card, "amount" => $rate, "chargetype" => 2, "id_cc_did" => $id_cc_did]);

                Connection::getConnection("cc_card")
                    ->where("id", $id_cc_card)
                    ->decrement("credit", $rate);
            }

            Connection::getConnection("cc_did")
                ->where("id", $id_cc_did)
                ->update(["iduser" => $id_cc_card, "reserved" => 1]);

            Connection::getConnection("cc_did_use")
                ->where(["id_did" => $id_cc_did, "activated" => 0])
                ->update(["releasedate" => Connection::getConnection()->raw("CURRENT_TIMESTAMP")]);

            // Should we do something special when billing != 0 or 1?
            Connection::getConnection("cc_did_use")
                ->insert(["activated" => 1, "id_cc_card" => $id_cc_card, "id_did" => $id_cc_did, "month_payed" => 1]);
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
        $row = Connection::getConnection("cc_did_destination AS t1", "t1.id_cc_did AS did_id")
            ->selectRaw("COUNT(*) AS destination_count")
            ->leftJoin("cc_did_destination AS t2", "t1.id_cc_did", "t2.id_cc_did")
            ->where("t2.id", $did_destination_id)
            ->first();

        if ($row && $row["destination_count"] < 2) {
            // Only remove did from card if this is the LAST destination connecting the two.
            // < 2, not 1 because destination is deleted after this call.
            $choose_did = $row['did_id'];

            Connection::getConnection("cc_did")
                ->where("id", $choose_did)
                ->update(["iduser" => 0, "reserved" => 0]);

            Connection::getConnection("cc_did_use")
                ->where(["id_did" => $choose_did, "activated" => 1])
                ->update(["releasedate" => Connection::getConnection()->raw("CURRENT_TIMESTAMP")]);

            Connection::getConnection("cc_did_use")
                ->insert(["activated" => 0, "id_did" => $choose_did]);
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
        $card_result = Connection::getConnection("cc_card", "vat", "typepaid", "credit")
            ->where("id", $card_id)
            ->first();
        $vat = $card_result["vat"] ?? 0;

        // FIND THE LAST BILLING for this card
        $last_billing_date = Connection::getConnection("cc_billing_customer")
            ->where("id_card", $card_id)
            ->where("id", "!=", $new_billing)
            ->orderBy("date", "DESC")
            ->value("date");
        $call_qb = Connection::getConnection("cc_call")
            ->selectRaw("COALESCE(SUM(sessionbill), 0) AS amt")
            ->where("card_id", $card_id)
            ->where("stop_time", "<", $date_bill);
        $charge_qb = Connection::getConnection("cc_charge", "id", "amount", "description", "creationdate")
            ->where("id_cc_card", $card_id)
            ->where("creationdate", "<", $date_bill)
            ->where("charged_status", 1);
        $start_date = null;

        if ($last_billing_date) {
            $call_qb->where("stoptime", ">=", $last_billing_date);
            $charge_qb->where("creationdate", ">=", $last_billing_date);
            $desc_billing = sprintf(_("Call costs between %s and %s"), $last_billing_date, $date_bill);
            $desc_billing_postpaid = sprintf(_("Charges between %s and %s"), substr($last_billing_date, 0, 10), $date_bill);
            $start_date = $last_billing_date;
        } else {
            $desc_billing = sprintf(_("Calls cost before %s"), $date_bill);
            $desc_billing_postpaid = sprintf(_("Amount for period before %s"), $date_bill);
        }

        $lastpostpaid_amount = Connection::getConnection("cc_billing_customer")
            ->selectRaw("SUM(items.total_price) AS total")
            ->leftJoin("cc_invoice", "cc_billing_customer.id_invoice", "cc_invoice.id")
            ->leftJoinSub(
                Connection::getConnection("cc_invoice_item", "id_invoice")
                    ->selectRaw("ROUND(SUM(price), 2) AS total_price")
                    ->where("type_ext", "POSTPAID")
                    ->groupBy("id_invoice"),
                "items",
                "cc_invoice.id",
                "items.id_invoice"
            )
            ->where("cc_billing_customer.id_card", $card_id)
            ->where("cc_invoice.paid_status", Invoice::PAIDSTATUS_UNPAID)
            ->where("cc_billing_customer.id", "!=", $new_billing)
            ->orderBy("cc_invoice.date", "DESC")
            ->value("total") ?? 0;

        $amount_calls = $call_qb->value("amt");

        // COMMON BEHAVIOUR FOR PREPAID AND POSTPAID ... GENERATE A RECEIPT FOR THE CALLS OF THE MONTH
        if ($amount_calls) {
            /// create receipt
            $title = _("SUMMARY OF CALLS");
            $description = _("Summary of the calls charged since the last billing");
            $receipt = Receipt::create($card_id, $description, $title, PaymentDocument::STATUS_CLOSED);
            if ($receipt->save()) {
                $item = ReceiptItem::create($receipt, $desc_billing, $amount_calls, $date, "CALLS", $new_billing);
                $item->save();
            }
        }

        // GENERATE RECEIPT FOR CHARGE ALREADY CHARGED
        $charges = $charge_qb->clone()->get();
        if (count($charges)) {
            $title = _("SUMMARY OF CHARGES");
            $description = _("Summary of the charge charged since the last billing.");
            $receipt = Receipt::create($card_id, $description, $title, PaymentDocument::STATUS_CLOSED);
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
        $charge_qb->where("charged_status", 0);
        $charge_qb->where("invoiced_status", 0);
        $charges = $charge_qb->get();
        $invoice = new Invoice(null);
        if (count($charges)) {
            $reference = Invoice::generateReference();
            $title = _("BILLING CHARGES");
            $description = _("This invoice is for some charges unpaid since the last billing.")." ".$desc_billing_postpaid;
            $invoice = Invoice::create($card_id, $description, $title, $reference, PaymentDocument::STATUS_CLOSED);
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
                $invoice = Invoice::create($card_id, $description, $title, $reference, PaymentDocument::STATUS_CLOSED);
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
            Connection::getConnection("cc_billing_customer")
                ->where("id", $new_billing)
                ->update($values);

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
            $vat = Connection::getConnection("cc_card")
                ->where("id", $card_id)
                ->value("vat") ?? 0;
            $credit = $amount / (1 + $vat / 100);

            $insert_values = compact("date", "credit", "card_id", "refill_type", "description");
            $id_refill = Connection::getConnection("cc_logrefill")
                ->insertGetId($insert_values, "id");

            // REFILL CARD - UPDATE CARD
            Connection::getConnection("cc_card")
                ->where("id", $card_id)
                ->increment("credit", $credit);

            // LINK THE REFILL TO THE PAYMENT .. UPADTE PAYMENT
            $insert_values = ["id_logrefill" => $id_refill];
            Connection::getConnection("cc_logpayment")
                ->where("id", $id_payment)
                ->update($insert_values);

            // Create invoice associated
            $refills = getRefillType_List();
            $title = sprintf(_("%s REFILL"), $refills[$refill_type] ?? "");
            $reference = Invoice::generateReference();
            $description = gettext("Invoice for refill");
            $invoice = Invoice::create($card_id, $description, $title, $reference, PaymentDocument::STATUS_CLOSED, Invoice::PAIDSTATUS_PAID, $date);
            if ($invoice->save()) {
                //add payment to this invoice
                Connection::getConnection("cc_invoice_payment")
                    ->insert(["id_invoice" => $invoice->id, "id_payment" => $id_payment]);
                $item = InvoiceItem::create($invoice, $description, $date, $credit, $vat);
                $item->save();
            }
        }

        if (!$processed["added_commission"]) {
            return;
        }
        $id_agent = Connection::getConnection("cc_card")
            ->leftJoin("cc_card_group", "cc_card.id_group", "cc_card_group.id")
            ->where("cc_card.id", $card_id)
            ->value("id_agent");

        if ($id_agent) {
            // update refill & payment to keep a trace of agent in the timeline
            if (!empty($id_refill)) {
                Connection::getConnection("cc_logrefill")
                    ->where("id", $id_refill)
                    ->update(["agent_id" => $id_agent]);
            }
            Connection::getConnection("cc_logpayment")
                ->where("id", $id_payment)
                ->update(["agent_id" => $id_agent]);

            $comm = Connection::getConnection("cc_agent")
                ->where("id", $id_agent)
                ->value("commission") ?? 0;

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
                Connection::getConnection("cc_agent_commission")
                    ->insert($insert_values);
                Connection::getConnection("cc_agent")
                    ->where("id", $id_agent)
                    ->increment("com_balance", $commission);
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
        $id_refill = Connection::getConnection("cc_logrefill_agent")
            ->insertGetId(
                compact("date", "credit", "agent_id", "refill_type", "description"),
                "id"
            );

        //REFILL AGENT .. UPADTE AGENT
        Connection::getConnection("cc_agent")
            ->where("id", $agent_id)
            ->increment("credit", $credit);

        //LINK THE REFILL TO THE PAYMENT .. UPADTE PAYMENT
        Connection::getConnection("cc_logpayment_agent")
            ->where("id", $id_payment)
            ->update(["id_logrefill" => $id_refill]);
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

        $username = $form->REALTIME_SIP_IAX_INFO["username"] ?? $processed['username'];
        $uipass = $form->REALTIME_SIP_IAX_INFO["secret"] ?? $processed['uipass'];

        Realtime::insert_voip_config((bool)$sip, (bool)$iax, $card_id, $username, $uipass);

        // Save info in table and in sip file
        if ($sip) {
            Realtime::create_trunk_config_file();
        }

        // Save info in table and in iax file
        if ($iax) {
            Realtime::create_trunk_config_file("iax");
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
        Connection::getConnection("cc_card")
            ->where("id", $card_id)
            ->update(["lock_date" => Connection::getConnection()->raw("CURRENT_TIMESTAMP")]);
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
        $card_lock_info = Connection::getConnection("cc_card")
            ->where("id", $card)
            ->value("block");
        if ($card_lock_info != $processed["block"] && $processed["block"] == 1) {
            Connection::getConnection("cc_card")
                ->where("id", $card)
                ->update(["lock_date" => Connection::getConnection()->raw("CURRENT_TIMESTAMP")]);
        }
    }
}
