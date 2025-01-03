<?php

namespace A2billing\Forms;

use A2billing\A2bMailException;
use A2billing\Invoice;
use A2billing\InvoiceItem;
use A2billing\Mail;
use A2billing\Notification;
use A2billing\NotificationsDAO;
use A2billing\Realtime;
use A2billing\Receipt;
use A2billing\ReceiptItem;
use A2billing\Table;
use A2billing\Ticket;
use Exception;
use PhpAgi\AMI as AGI_AsteriskManager;

class FormBO
{
    /**
     * Function to add/modify cc_did_use and cc_did_destination if records existe
     */
    public static function is_did_in_use()
    {
        $FormHandler = FormHandler::GetInstance();
        $processed = $FormHandler->getProcessed();
        $row = (new Table("cc_did", "id_cc_card"))->getRow(
            $FormHandler->DBHandle,
            ["id_did" => $processed["id"], "releasedate" => null, "activated" => 1]
        );
        if (!empty($row)) {
            $FormHandler->FG_INTRO_TEXT_ASK_DELETION = sprintf(
                _("This DID is in use by customer ID %s, If you really want remove this DID, click on the delete button."),
                $row["id_cc_card"]
            );
        }
    }

    public static function did_use_delete(): void
    {
        $FormHandler = FormHandler::GetInstance();
        $processed = $FormHandler->getProcessed();
        $did_id = $processed['id'];
        (new Table("cc_did_use"))->updateRow(
            $FormHandler->DBHandle,
            ["releasedate" => "now()"],
            ["id_did" => $did_id, "releasedate" => null]
        );
        (new Table("cc_did_destination"))->deleteRow($FormHandler->DBHandle, ["id_cc_did" => $did_id]);
    }

    public static function add_did_use()
    {
        $FormHandler = FormHandler::GetInstance();
        $processed = $FormHandler->getProcessed();
        (new Table("cc_did_use"))->addRow(
            $FormHandler->DBHandle,
            ["id_did" => $FormHandler->QUERY_RESULT, "activated" => $processed["activated"] ?? 0]
        );
    }

    /**
     * Function create_status_log
     * @public
     */
    public static function create_status_log()
    {
        $FormHandler = FormHandler::GetInstance();
        $processed = $FormHandler->getProcessed();
        $status = $processed['status'];
        $oldstatus = $processed['oldstatus'];
        if ($oldstatus != $status) {
            if ($FormHandler -> QUERY_RESULT && !(is_object($FormHandler -> QUERY_RESULT)) )
                $id = $FormHandler -> QUERY_RESULT; // DEFINED BEFORE FG_ADDITIONAL_FUNCTION_AFTER_ADD
            else
                $id = $processed['id']; // DEFINED BEFORE FG_ADDITIONAL_FUNCTION_AFTER_ADD

            $value = "'$status','$id'";
            $func_fields = "status,id_cc_card";
            $func_table = 'cc_status_log';
            $id_name = "";
            $instance_table = new Table();
            $inserted_id = $instance_table -> Add_table ($FormHandler->DBHandle, $value, $func_fields, $func_table, $id_name);
        }
    }

    /**
     * Function create_sipiax_friends_reload
     * @public
     */
    public static function create_sipiax_friends_reload()
    {
        $FormHandler = FormHandler::GetInstance();
        self :: create_sipiax_friends();

        $as = new AGI_AsteriskManager();
        // && CONNECTING  connect($server=NULL, $username=NULL, $secret=NULL)
        $res =@  $as->connect(MANAGER_HOST,MANAGER_USERNAME,MANAGER_SECRET);
        if ($res) {
            $res = $as->Command('sip reload');
            $res = $as->Command('iax2 reload');
            // && DISCONNECTING
            $as->disconnect();
        } else {
            echo "Error : Manager Connection";
        }
    }

    /**
     * Function add_card_refill
     * @public
     */
    public static function add_card_refill()
    {

        global $A2B;
        $FormHandler = FormHandler::GetInstance();
        $processed = $FormHandler->getProcessed();
        $credit = $processed['credit'];
        $card_id = $processed['card_id'];

        // REFILL CARD
        $instance_table_card = new Table("cc_card");
        $param_update_card = "credit = credit + '".$credit."'";
        $clause_update_card = " id='$card_id'";
        $instance_table_card -> Update_table ($FormHandler->DBHandle, $param_update_card, $clause_update_card, $func_table = null);
    }


    public static function ticket_add(): void
    {
        $FormHandler = FormHandler::GetInstance();
        $id_ticket = $FormHandler->QUERY_RESULT;
        $processed = $FormHandler->getProcessed();
        $title = $processed['title'];
        $card_id = $processed['creator'];
        $priority = $processed['priority'];
        $description = $processed['description'];
        $component_id = $processed['id_component'];

        if ($processed["creator_type"] == Ticket::CUSTOMER) {
            $table = new Table(
                "cc_card",
                ["username", "firstname", "lastname", "language", "email"]
            );
        } elseif ($processed["creator_type"] == Ticket::AGENT) {
            $table = new Table(
                "cc_agent",
                ["login AS username", "firstname", "lastname", "language", "email"]
            );
        } elseif ($processed["creator_type"] == Ticket::ADMIN) {
            $table = new Table(
                "cc_ui_authen", [
                "login AS username",
                "SUBSTRING(name FROM 1 FOR POSITION(' ' IN name) AS firstname",
                "SUBSTRING(name FROM POSITION(' ' IN name) + 1) AS lastname",
                "'en' AS language",
                "email"
            ]);
        } else {
            return;
        }
        $result = $table->getRow($FormHandler->DBHandle, ["id" => $card_id]);

        $owner = $result[0]['username']." (".$result[0]['firstname']." ".$result[0]['lastname'].")";

        try {
            self::send_new_ticket_email(
                $owner,
                (int)$id_ticket,
                $description,
                (int)$priority,
                $title,
                $result["language"],
                $result["email"]
            );
        } catch (Exception $e) {
            $FormHandler->FG_TEXT_ADITION_ERROR = $e->getMessage();
        }

        $component_table = new Table(
            "cc_support_component",
            ["email", "language"],
            ["cc_support" => ["id_support", "cc_support.id"]]
        );
        $result = $component_table
            ->getRow($FormHandler->DBHandle, ["cc_support_component.id" => $component_id]);

        try {
            self::send_new_ticket_email(
                $owner,
                (int)$id_ticket,
                $description,
                (int)$priority,
                $title,
                $result["language"],
                $result["email"]
            );
        } catch (Exception $e) {
            $FormHandler->FG_TEXT_ADITION_ERROR = $e->getMessage();
        }
    }

    /**
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

    public static function add_agent_refill()
    {
        global $A2B;
        $FormHandler = FormHandler::GetInstance();
        $processed = $FormHandler->getProcessed();
        $credit = $processed['credit'];
        $agent_id = $processed['agent_id'];

        //REFILL CARD .. UPADTE AGENT
        $instance_table_agent = new Table("cc_agent");
        $param_update_agent = "credit = credit + '".$credit."'";
        $clause_update_agent = " id='$agent_id'";
        $instance_table_agent -> Update_table ($FormHandler->DBHandle, $param_update_agent, $clause_update_agent, $func_table = null);
    }

    public static function creation_card_refill()
    {
        $FormHandler = FormHandler::GetInstance();
        $processed = $FormHandler->getProcessed();
        $credit = $processed['credit'];

        if ($credit>0) {
            $field_insert = " credit, card_id, description";
            $card_id = $FormHandler -> QUERY_RESULT;
            $description = gettext("CREATION CARD REFILL");
            $value_insert = "'$credit', '$card_id', '$description' ";
            $instance_refill_table = new Table("cc_logrefill", $field_insert);
            $instance_refill_table -> Add_table ($FormHandler->DBHandle, $value_insert, null, null);
        }
    }

    /**
     * Run after creation of an agent
     *
     * @return void
     */
    public static function creation_agent_refill()
    {
        $FormHandler = FormHandler::GetInstance();
        $processed = $FormHandler->getProcessed();
        $credit = $processed["credit"];

        if ($credit > 0) {
            $agent_id = $FormHandler->QUERY_RESULT;
            $description = gettext("CREATION AGENT REFILL");
            (new Table("cc_log_refill_agent"))
                ->addRow($FormHandler->DBHandle, compact("credit", "agent_id", "description"));
        }
    }

    public static function processing_card_signup()
    {
        $FormHandler = FormHandler::GetInstance();
        if (RELOAD_ASTERISK_IF_SIPIAX_CREATED) {
            self::create_sipiax_friends_reload();
        } else {
            self::create_sipiax_friends();
        }

        self::create_subscriptions();
        self::create_notification_signup();
    }

    public static function create_subscriptions()
    {
        global $A2B;
        $FormHandler = FormHandler::GetInstance();
        $processed = $FormHandler->getProcessed();
        $subscriber = $processed['subscriber_signup'];
        $table_subscription = new Table("cc_subscription_service", "*");
        $subscription_clause = "id = ".$subscriber;
        $result_sub = $table_subscription->get_list($FormHandler->DBHandle, $subscription_clause);

        if (is_numeric($subscriber) && is_array($result_sub) && $result_sub[0]['fee'] > 0) {

            $subscription = $result_sub[0];
            $billdaybefor_anniversery = $A2B->config['global']['subscription_bill_days_before_anniversary'];

            $unix_startdate = time();
            $startdate = date("Y-m-d",$unix_startdate);
            $day_startdate = date("j",$unix_startdate);
            $month_startdate = date("m",$unix_startdate);
            $year_startdate= date("Y",$unix_startdate);
            $lastday_of_startdate_month = lastDayOfMonth($month_startdate,$year_startdate,"j");

            $next_bill_date = strtotime("01-$month_startdate-$year_startdate + 1 month");
            $lastday_of_next_month= lastDayOfMonth(date("m",$next_bill_date),date("Y",$next_bill_date),"j");
            $limite_pay_date = date("Y-m-d",strtotime(" + $billdaybefor_anniversery day")) ;

            if ($day_startdate > $lastday_of_next_month) {
                $next_limite_pay_date = date ("$lastday_of_next_month-m-Y" ,$next_bill_date);
            } else {
                $next_limite_pay_date = date ("$day_startdate-m-Y" ,$next_bill_date);
            }

            $next_bill_date = date("Y-m-d",strtotime("$next_limite_pay_date - $billdaybefor_anniversery day")) ;

            $field_insert = " id_cc_card, id_subscription_fee, product_name, paid_status, startdate, next_billing_date, limit_pay_date, last_run";
            $card_id = $FormHandler -> QUERY_RESULT;

            $instance_table = new Table("cc_card", "");
            $QUERY = "UPDATE cc_card SET status=8 WHERE id=$card_id";
            $instance_table->SQLExec($FormHandler->DBHandle, $QUERY, 0);

            $product_name = $subscription['label'];
            $value_insert = "'$card_id', '$subscriber' ,'$product_name', 1 , '$startdate', '$next_bill_date','$limite_pay_date','$startdate'";
            $instance_subscription_table = new Table("cc_card_subscription", $field_insert);
            $id_card_subscription = $instance_subscription_table -> Add_table ($FormHandler->DBHandle, $value_insert, null, null, "id");
            $reference = Invoice::generateReference();

            //CREATE INVOICE If a new card then just an invoice item in the last invoice
            $field_insert = "date, id_card, title, reference, description, status, paid_status";
            $date = date("Y-m-d h:i:s");
            $title = gettext("SUBSCRIPTION INVOICE REMINDER");
            $description = "You have $billdaybefor_anniversery days to pay your subscription with this invoice (REF: $reference ) or the account will be automatically disactived \n\n";
            $value_insert = " '$date' , '$card_id', '$title','$reference','$description',1,0";
            $instance_table = new Table("cc_invoice", $field_insert);
            $id_invoice = $instance_table->Add_table($FormHandler->DBHandle, $value_insert, null, null, "id");

            if (!empty ($id_invoice) && is_numeric($id_invoice)) {
                $description = "Subscription service";
                $amount = $subscription['fee'];
                $vat = 0;
                $field_insert = "date, id_invoice, price, vat, description, id_ext, type_ext";
                $instance_table = new Table("cc_invoice_item", $field_insert);
                $value_insert = " '$date' , '$id_invoice', '$amount','$vat','$description','$id_card_subscription','SUBSCR'";
                if ($verbose_level >= 1)
                    echo "INSERT INVOICE ITEM : $field_insert =>	$value_insert \n";
                $instance_table->Add_table($FormHandler->DBHandle, $value_insert, null, null, "id");
            }

            $mail = new Mail(Mail::$TYPE_SUBSCRIPTION_UNPAID,$card_id );
            $mail -> replaceInEmail(Mail::$DAY_REMAINING_KEY,$day_remaining );
            $mail -> replaceInEmail(Mail::$INVOICE_REF_KEY,$reference);
            $mail -> replaceInEmail(Mail::$SUBSCRIPTION_FEE,$subscription['fee']);
            $mail -> replaceInEmail(Mail::$SUBSCRIPTION_ID,$subscription['id']);
            $mail -> replaceInEmail(Mail::$SUBSCRIPTION_LABEL,$subscription['product_name']);

            //insert charge
            $QUERY = "INSERT INTO cc_charge (id_cc_card, amount, chargetype, id_cc_card_subscription, invoiced_status) VALUES ('" . $card_id . "', '" . $subscription['fee']  . "', '3','" . $subscription['card_subscription_id'] . "',1)";
            $instance_table->SQLExec($FormHandler->DBHandle, $QUERY, 0);

            try {
                $mail -> send();
            } catch (A2bMailException $e) {
            }
        }
    }

    public static function processing_commission_add()
    {
        $FormHandler = FormHandler::GetInstance();
        $processed = $FormHandler->getProcessed();
        $id_agent = $processed['id_agent'];
        $id = $FormHandler -> QUERY_RESULT;
        $type_com =  $processed['commission_type'];
        if (!empty($id_agent)) {
            //update record with agent commission
            $table_agent = new Table('cc_agent', 'commission');
            $agent_clause = "id = ".$id_agent;
            $agent_result = $table_agent -> get_list($FormHandler->DBHandle, $agent_clause);
            $agent_com = $agent_result[0][0];
            if (empty($agent_com) ) {
                $table_commission = new Table("cc_agent_commission");
                $param_update_commission = "commission_percent = $agent_com";
                $clause_update_commission = " id='".$id."'";
                $table_commission -> Update_table ($FormHandler->DBHandle, $param_update_commission, $clause_update_commission, $func_table = null);
            }
            $amount = $processed['amount'];
            if($amount>0)$sign="+";
            else $sign="-";
            $param_update_agent = "com_balance = com_balance $sign '".abs($amount)."'";
            $clause_update_agent = " id='".$id_agent."'";
            $table_agent -> Update_table ($FormHandler->DBHandle, $param_update_agent, $clause_update_agent, $func_table = null);
        }
    }

    public static function processing_card_add()
    {
        self::create_sipiax_friends();
        self::creation_card_refill();
        self::create_lock_card();
    }

    public static function processing_refill_add()
    {
        $FormHandler = FormHandler::GetInstance();
        self::add_card_refill();
        //add invoice
        self::create_invoice_after_refill();
    }

    /*
     * static public function to add a new DID Destination and set the DID use & Charge correctly
     */
    public static function did_destination_add()
    {
        global $A2B;
        $FormHandler = FormHandler::GetInstance();
        $processed = $FormHandler->getProcessed();

        $instance_table = new Table();
        $id_cc_did = $processed['id_cc_did'];
        $id_cc_card = $processed['id_cc_card'];

        // 3 cases to handle :
        // the DID is released so we can purchase it
        // the DID is used by an other user, we might want to change
        // the DID is new nothing in cc_did_use

        $QUERY_DID = "SELECT cc_did_use.id, cc_did_use.id_cc_card, cc_did.fixrate, billingtype, releasedate ".
                     "FROM cc_did_use ".
                     "LEFT JOIN cc_did ON cc_did.id = cc_did_use.id_did ".
                     "WHERE id_did ='".$id_cc_did."'" .
                     "ORDER BY cc_did_use.id DESC";

        $result_did = $instance_table -> SQLExec($FormHandler->DBHandle, $QUERY_DID);

        if (is_array($result_did) && count($result_did)>0) {

            // check the id_cc_card, if id_cc_card is null it means it has been released
            if ((isset($result_did[0]['id_cc_card'])) && (strlen($result_did[0]['id_cc_card']) > 0)) {
                // echo("DID $did_id is in use by customer id:".$existing_owner_id);
                $existing_owner_id = $result_did[0]['id_cc_card'];

            } else {
                // did_use without a registered card
                // echo("DID $did_id has been freed");
                $existing_owner_id = -1;
            }
        } else {
            // No result, the DID hasnt been purchased yet
            $existing_owner_id = -2;
        }

        if ($existing_owner_id >= -2 && $existing_owner_id != $id_cc_card) {

            // The did ownership has changed and we need to update. (regardless of how it's billed)
            if ($result_did[0]['billingtype'] == 0 || $result_did[0]['billingtype'] == 1) {
                $rate = $result_did[0]['fixrate'];
                $QUERY1 = "INSERT INTO cc_charge (id_cc_card, amount, chargetype, id_cc_did) VALUES ".
                           "('" . $id_cc_card . "', '" . $rate . "', '2','" . $id_cc_did . "')";
                $result = $instance_table->SQLExec($FormHandler->DBHandle, $QUERY1, 0);

                $QUERY1 = "UPDATE cc_card set credit = credit -" . $rate . " where id = '" . $id_cc_card . "'";
                $result = $instance_table->SQLExec($FormHandler->DBHandle, $QUERY1, 0);
            }

            $QUERY1 = "UPDATE cc_did set iduser = " . $id_cc_card . ",reserved=1 where id = '" . $id_cc_did . "'";
            $result = $instance_table->SQLExec($FormHandler->DBHandle, $QUERY1, 0);

            $QUERY1 = "UPDATE cc_did_use set releasedate = now() where id_did = '" . $id_cc_did . "' and activated = 0";
            $result = $instance_table->SQLExec($FormHandler->DBHandle, $QUERY1, 0);

            // Should we do something special when billing != 0 or 1?
            $QUERY1 = "INSERT INTO cc_did_use (activated, id_cc_card, id_did, month_payed) values ('1','" . $id_cc_card . "','" . $id_cc_did . "', 1)";
            $result = $instance_table->SQLExec($FormHandler->DBHandle, $QUERY1, 0);
        }
        // else existing_owner_id is already correctly set due to prior destinations on the same DID
    }

    /*
     * static public function to release a DID and set the DID use correctly
     */
    public static function did_destination_del()
    {
        global $A2B;
        $FormHandler = FormHandler::GetInstance();
        $processed = $FormHandler->getProcessed();

        $instance_table = new Table();
        $did_destination_id = $processed['id'];

        $QUERY_did = "SELECT cc_did.id AS did_id, dg.dest_count AS destination_count ".
                     "FROM cc_did ".
                     "LEFT JOIN cc_did_destination ON cc_did_destination.id_cc_did = cc_did.id ".
                     "LEFT JOIN ( SELECT st1.id, count(*) AS dest_count ".
                                  "FROM cc_did AS st1 ".
                                  "INNER JOIN cc_did_destination AS st2 ON st2.id_cc_did = st1.id ".
                                  "GROUP BY st1.id ".
                                ") AS dg ON dg.id = cc_did.id ".
                     "WHERE cc_did_destination.id = '". $did_destination_id ."'";
        // Also possible to do FROM cc_did_destination AS dest1 JOIN cc_did JOIN cc_did_destination AS dest2 GROUP BY dest1.id, cc_did.id
        // To get the count but NULL and NO row behavoir is flaky no matter the types of joins used. Therefore using SubSelect.
        $result_did_dest = $instance_table -> SQLExec($FormHandler->DBHandle, $QUERY_did );

        if (is_array($result_did_dest) && !is_null($result_did_dest[0]['did_id'])) {
            if ($result_did_dest[0]['destination_count'] < 2) {
                // Only remove did from card if this is the LAST destination connecting the two.
                // < 2, not 1 because destination is deleted after this call.
                $choose_did = $result_did_dest[0]['did_id'];

                $QUERY = "UPDATE cc_did SET iduser = 0, reserved=0 WHERE id=$choose_did";
                $result = $instance_table->SQLExec($FormHandler->DBHandle, $QUERY, 0);

                $QUERY = "UPDATE cc_did_use SET releasedate = now() WHERE id_did =$choose_did and activated = 1";
                $result = $instance_table->SQLExec($FormHandler->DBHandle, $QUERY, 0);

                $QUERY = "INSERT INTO cc_did_use (activated, id_did) VALUES ('0','" . $choose_did . "')";
                $result = $instance_table->SQLExec($FormHandler->DBHandle, $QUERY, 0);
            }
        }
    }

    /**
     * Run after creating a recurring billing item
     *
     * @return void
     * @throws A2bMailException
     */
    public static function proccessing_billing_customer()
    {
        $form = FormHandler::GetInstance();
        $processed = $form->getProcessed();
        //find the last billing
        $card_id = $processed["id_card"];
        $date_bill = $processed["date"];
        $new_billing = $form->QUERY_RESULT;
        $date = date("Y-m-d h:i:s");
        $db = $form->DBHandle;

        //GET VAT
        $card_table = new Table("cc_card", ["vat", "typepaid", "credit"]);
        $card_result = $card_table->getRow($db, ["id" => $card_id]);
        $vat = $card_result[0] ?? 0;

        // FIND THE LAST BILLING for this card
        $last_billing_date = (new Table("cc_billing_customer", ["date"]))
            ->getValue(
                $db,
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
            $db,
            [
                "cc_billing_customer.id_card" => $card_id,
                "cc_invoice.paid_status" => Invoice::PAIDSTATUS_UNPAID,
                "cc_billing_customer.id" => ["!=", $new_billing]
            ],
            ["cc_invoice.date"],
            "desc"
        ) ?? 0;

        $call_table = new Table("cc_call", ["COALESCE(SUM(sessionbill), 0)"]);
        $amount_calls = $call_table->getValue($db, $call_conditions);
        // COMMON BEHAVIOUR FOR PREPAID AND POSTPAID ... GENERATE A RECEIPT FOR THE CALLS OF THE MONTH
        if ($amount_calls) {
            /// create receipt
            $title = _("SUMMARY OF CALLS");
            $description = _("Summary of the calls charged since the last billing");
            $receipt = Receipt::create($card_id, $description, $title, Receipt::STATUS_CLOSED);
            if ($receipt->save()) {
                $item = ReceiptItem::create($receipt, $desc_billing, $date, $amount_calls, "CALLS", $new_billing);
                $item->save();
            }
        }

        // GENERATE RECEIPT FOR CHARGE ALREADY CHARGED
        $charges_table = new Table("cc_charge", ["id", "amount", "description", "creationdate"]);
        $charges = $charges_table->getRows($db, $charge_conditions);
        if (count($charges)) {
            $title = _("SUMMARY OF CHARGES");
            $description = _("Summary of the charge charged since the last billing.");
            $receipt = Receipt::create($card_id, $description, $title, Receipt::STATUS_CLOSED);
            if ($receipt->save()) {
                foreach ($charges as $charge) {
                    $item = ReceiptItem::create($receipt, $charge["description"], $charge["creationdate"], $charge["amount"], "CHARGE", $charge["id"]);
                    $item->save();
                }
            }
        }

        $total = 0;
        $total_vat = 0;
        // GENERATE INVOICE FOR CHARGE NOT YET CHARGED
        $charge_conditions["charged_status"] = 0;
        $charge_conditions["invoiced_status"] = 0;
        $charges = (new Table("cc_charge"))->getRows($db, $charge_conditions);
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
            (new Table("cc_billing_customer"))->updateRow($db, $values, ["id" => $new_billing]);

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

    public static function create_invoice_after_refill()
    {
        $form = FormHandler::GetInstance();
        $processed = $form->getProcessed();
        $db = $form->DBHandle;

        if (!$processed['added_invoice']) {
            return;
        }
        //CREATE AND UPDATE REF NUMBER
        $refills = getRefillType_List();
        $type = (int)$processed['refill_type'];
        $reference = Invoice::generateReference();
        $date = $processed['date'];
        $card_id = $processed['card_id'];
        $title = sprintf(_("%s REFILL"), $refills[$type] ?? "");
        $description = gettext("Invoice for refill");

        $invoice = Invoice::create($card_id, $description, $title, $reference, Invoice::STATUS_OPEN, Invoice::PAIDSTATUS_UNPAID, $date);
        //load vat of this card
        if ($invoice->save()) {
            $amount = $processed['credit'];
            $description = $processed['description'];
            $vat = (new Table("cc_card", ["vat"]))->getValue($db, ["id" => $card_id]) ?? 0;
            $item = InvoiceItem::create($invoice, $description, $date, $amount, $vat);
            $item->save();
        }
    }


    public static function create_invoice_reference()
    {
        $form = FormHandler::GetInstance();
        $id_invoice = $form->QUERY_RESULT;
        //CREATE AND UPDATE REF NUMBER
        $reference = Invoice::generateReference();
        $invoice = new Invoice($id_invoice);
        $invoice->reference = $reference;
        $invoice->save();
    }


    /**
     * Function create_refill
     * @public
     */
    public static function create_refill_after_payment()
    {
        $form = FormHandler::GetInstance();
        $processed = $form->getProcessed();
        $db = $form->DBHandle;
        $date = $processed["date"];
        $card_id = $processed["card_id"];
        $amount = $processed["payment"];
        $id_payment = $form->QUERY_RESULT;

        if (!$processed["added_refill"] && !$processed["added_commission"]) {
            return;
        }

        if ($processed["added_refill"]) {
            // CREATE REFILL
            $refill_type = (int)$processed['payment_type'];
            $description = $processed['description'];
            $vat = (new Table("cc_card", "vat"))->getValue($db, ["id" => $card_id]) ?? 0;
            $credit = $amount / (1 + $vat / 100);

            $insert_values = compact("date", "credit", "card_id", "refill_type", "description");
            (new Table("cc_logrefill"))->addRow($db, $insert_values, "id", $id_refill);

            // REFILL CARD - UPDATE CARD
            $insert_values = ["credit" => ["credit + ?", $credit]];
            (new Table("cc_card"))->updateRow($db, $insert_values, ["id" => $card_id]);

            // LINK THE REFILL TO THE PAYMENT .. UPADTE PAYMENT
            $insert_values = ["id_logrefill" => $id_refill];
            (new Table("cc_logpayment"))->updateRow($db, $insert_values, ["id" => $id_payment]);

            // Create invoice associated
            $refills = getRefillType_List();
            $title = sprintf(_("%s REFILL"), $refills[$refill_type] ?? "");
            $reference = Invoice::generateReference();
            $description = gettext("Invoice for refill");
            $invoice = Invoice::create($card_id, $description, $title, $reference, Invoice::STATUS_CLOSED, Invoice::PAIDSTATUS_PAID, $date);
            if ($invoice->save()) {
                //add payment to this invoice
                (new Table("cc_invoice_payment"))
                    ->addRow($db, ["id_invoice" => $invoice->id, "id_payment" => $id_payment]);
                $item = InvoiceItem::create($invoice, $description, $date, $credit, $vat);
                $item->save();
            }
        }

        if (!$processed["added_commission"]) {
            return;
        }
        $table = new Table("cc_card", "id_agent", ["cc_card_group" => ["cc_card.id_group", "cc_card_group.id"]]);
        $id_agent = $table->getValue($db, ["cc_card.id" => $card_id]);

        if ($id_agent) {
            // update refill & payment to keep a trace of agent in the timeline
            if (!empty($id_refill)) {
                (new Table("cc_logrefill"))
                    ->updateRow($db, ["agent_id" => $id_agent], ["id" => $id_refill]);
            }
            (new Table("cc_logpayment"))
                ->updateRow($db, ["agent_id" => $id_agent], ["id" => $id_payment]);

            $comm = (new Table("cc_agent", ["commission"]))
                ->getValue($db, ["id" => $id_agent]);

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
                (new Table("cc_agent_commission"))->addRow($db, $insert_values);
                (new Table("cc_agent"))
                    ->updateRow(
                        $db,
                        ["com_balance" => ["com_balance + ?", $commission]],
                        ["id" => $id_agent]
                    );
            }
        }
    }

    public static function create_agent_refill()
    {
        $form = FormHandler::GetInstance();
        $processed = $form->getProcessed();
        $id_payment = $form->QUERY_RESULT;
        $db = $form->DBHandle;

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
                $db,
                compact("date", "credit", "agent_id", "refill_type", "description"),
                "id",
                $id_refill
            );

        //REFILL AGENT .. UPADTE AGENT
        (new Table("cc_agent"))
            ->updateRow($db, ["credit" => ["credit + ?", $credit]], ["id" => $agent_id]);

        //LINK THE REFILL TO THE PAYMENT .. UPADTE PAYMENT
        (new Table("cc_logpayment_agent"))
            ->updateRow($db, ["id_logrefill" => $id_refill], ["id" => $id_payment]);
    }

    /**
     * Function to edit the fields
     * @public
     */
    public static function create_sipiax_friends()
    {
        $FormHandler = FormHandler::GetInstance();
        $processed = $FormHandler->getProcessed();
        $id = $FormHandler -> QUERY_RESULT; // DEFINED BEFORE FG_ADDITIONAL_FUNCTION_AFTER_ADD
        $sip = stripslashes($processed['sip_buddy']);
        $iax = stripslashes($processed['iax_buddy']);

        // $FormHandler -> FG_QUERY_EXTRA_HIDDED - username, useralias, uipass, loginkey
        if (strlen($FormHandler -> REALTIME_SIP_IAX_INFO[0])>0) {
            $username 	= $FormHandler -> REALTIME_SIP_IAX_INFO[0];
            $uipass 	= $FormHandler -> REALTIME_SIP_IAX_INFO[2];
        } else {
            $username 	= $processed['username'];
            $uipass 	= $processed['uipass'];
        }

        $instance_realtime = new Realtime();

        $instance_realtime->insert_voip_config ($sip, $iax, $id, $username, $uipass);

        // Save info in table and in sip file
        if ($sip == 1) {
            $instance_realtime->create_trunk_config_file();
        }

        // Save info in table and in iax file
        if ($iax == 1) {
            $instance_realtime->create_trunk_config_file('iax');
        }
    }

    public static function create_lock_card()
    {
        $form = FormHandler::GetInstance();
        $processed = $form->getProcessed();
        $id = $form->QUERY_RESULT;
        $db = $form->DBHandle;
        if (!$processed["block"]) {
            return;
        }
        (new Table("cc_card"))
            ->updateRow($db, ["lock_date" => "CURRENT_TIMESTAMP"], ["id" => $id]);
    }

    public static function change_card_lock()
    {
        $form = FormHandler::GetInstance();
        $processed = $form->getProcessed();
        $db = $form->DBHandle;
        $card = $processed["id"];
        $instance_sub_table = new Table("cc_card", ["block"]);
        $card_lock_info = $instance_sub_table->getValue($db, ["id" => $card]);
        if ($card_lock_info != $processed["block"] && $processed["block"] == 1) {
            $instance_sub_table
                ->updateRow($db, ["lock_date" => "CURRENT_TIMESTAMP"], ["id" => $card]);
        }
    }

    /**
     * Function to added new sign-ups in the notification
     * @public
     */
    public static function create_notification_signup()
    {
        $form = FormHandler::GetInstance();
        $id_card = $form->QUERY_RESULT;
        NotificationsDAO::addNotification(
            "added_new_signup",
            Notification::$MEDIUM,
            Notification::$CUST,
            $id_card,
            Notification::$LINK_CARD,
            $id_card
        );
    }
}
