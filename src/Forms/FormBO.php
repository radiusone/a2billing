<?php
/**
 * @noinspection PhpUnused
 */

namespace A2billing\Forms;

use A2billing\A2bMailException;
use A2billing\Mail;
use A2billing\Notification;
use A2billing\NotificationsDAO;
use A2billing\Realtime;
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

    /**
     * Function add_card_refill_agent
     * @public
     */
    public static function add_card_refill_agent()
    {
        global $A2B;
        $FormHandler = FormHandler::GetInstance();
        $processed = $FormHandler->getProcessed();
        $credit = $processed['credit'];
        $card_id = $processed['card_id'];

        //check if enought credit
        $instance_table_agent = new Table("cc_agent", "credit, currency");
        $FG_TABLE_CLAUSE_AGENT = "id = ".$_SESSION['agent_id'] ;
        $agent_info = $instance_table_agent -> get_list ($FormHandler->DBHandle, $FG_TABLE_CLAUSE_AGENT);
        $credit_agent = $agent_info[0][0];

        if ($credit_agent >= $credit) {

            //Substract credit for agent
            $param_update_agent = "credit = credit - '".$credit."'";
            $instance_table_agent -> Update_table ($FormHandler -> DBHandle, $param_update_agent, $FG_TABLE_CLAUSE_AGENT, $func_table = null);

            // REFILL CARD
            $instance_table_card = new Table("cc_card");
            $param_update_card = "credit = credit + '".$credit."'";
            $clause_update_card = " id='$card_id'";
            $instance_table_card -> Update_table ($FormHandler->DBHandle, $param_update_card, $clause_update_card, $func_table = null);

            return true;
        }

        return false;
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

    public static function deletion_card_refill_agent()
    {
        $FormHandler = FormHandler::GetInstance();
        $processed = $FormHandler->getProcessed();
        //AFTER A DELETE YOU DON T HAVE ACCESS TO ANY FIELD AND YOU CAN ACCESS ONLY TO THE ID
        //SO YOU HAVE TO LOAD THE FIELD THAT YOU NEED
        $card_id = $processed['id'];
        $card_table = new Table('cc_card', 'credit');
        $card_clause = "id = ".$card_id;
        $card_result = $card_table -> get_list($FormHandler->DBHandle, $card_clause);

        $credit = $card_result[0][0];

        if ($credit>0 || $credit<0) {
            if ($credit>0) {
                $sign="+";
            } else {
                $sign="-";
            }
            $instance_table_agent = new Table("cc_agent");
            $param_update_agent = "credit = credit $sign '".abs($credit)."'";
            $clause_update_agent = " id='".$_SESSION['agent_id']."'";
            $instance_table_agent -> Update_table ($FormHandler->DBHandle, $param_update_agent, $clause_update_agent, $func_table = null);
            $field_insert = " credit, card_id, refill_type, description";
            $description = gettext("DELETION CARD REFILL");
            $correction = 0-$credit;
            $value_insert = "'$correction', '$card_id', 1 ,'$description' ";
            $instance_refill_table = new Table("cc_logrefill", $field_insert);
            $instance_refill_table -> Add_table ($FormHandler->DBHandle, $value_insert, null, null);
            if ($credit>0) {
                $table_transaction = new Table();
                $result_agent = $table_transaction -> SQLExec($FormHandler->DBHandle,"SELECT cc_card_group.id_agent FROM cc_card LEFT JOIN cc_card_group ON cc_card_group.id = cc_card.id_group WHERE cc_card.id = $card_id");

                if (is_array($result_agent)&& !is_null($result_agent[0]['id_agent']) && $result_agent[0]['id_agent']>0 ) {
                    // test if the agent exist and get its commission
                    $id_agent = $result_agent[0]['id_agent'];
                    $agent_table = new Table("cc_agent", "commission");
                    $agent_clause = "id = ".$id_agent;
                    $result_agent= $agent_table -> get_list($FormHandler->DBHandle, $agent_clause);

                    if (is_array($result_agent) && is_numeric($result_agent[0]['commission']) && $result_agent[0]['commission']>0) {
                        $field_insert = "id_payment, id_card, amount,description,id_agent";
                        $commission = a2b_round($credit * ($result_agent[0]['commission']/100));
                        $description_commission = gettext("CORRECT COMMISSION AFTER CARD DELETED!");
                        $description_commission.= "\nID CARD : ".$card_id;
                        $description_commission.= "\n AMOUNT: ".$credit;
                        $description_commission.= "\nCOMMISSION APPLIED: ".$result_agent[0]['commission'];
                        $value_insert = "'-1', '$card_id', '-$commission','$description_commission','$id_agent'";
                        $commission_table = new Table("cc_agent_commission", $field_insert);
                        $id_commission = $commission_table -> Add_table ($FormHandler->DBHandle, $value_insert, null, null,"id");
                        $table_agent = new Table('cc_agent');
                        $param_update_agent = "com_balance = com_balance - '".$commission."'";
                        $clause_update_agent = " id='".$id_agent."'";
                        $table_agent -> Update_table ($FormHandler->DBHandle, $param_update_agent, $clause_update_agent, $func_table = null);
                    }
                }
            }
        }
    }

    public static function creation_agent_refill()
    {
        $FormHandler = FormHandler::GetInstance();
        $processed = $FormHandler->getProcessed();
        $credit = $processed['credit'];

        if ($credit>0) {
            $field_insert = " credit,agent_id, description";
            $agent_id = $FormHandler -> QUERY_RESULT;
            $description = gettext("CREATION AGENT REFILL");
            $value_insert = "'$credit', '$agent_id', '$description' ";
            $instance_refill_table = new Table("cc_logrefill_agent", $field_insert);
            $instance_refill_table -> Add_table ($FormHandler->DBHandle, $value_insert, null, null);
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
            $reference = generate_invoice_reference();

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

    public static function processing_card_del_agent()
    {
        self::deletion_card_refill_agent();
    }

    public static function processing_card_add_agent()
    {
        self::create_sipiax_friends();
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

    public static function proccessing_billing_customer()
    {
        global $A2B;
        $FormHandler = FormHandler::GetInstance();
        $processed = $FormHandler->getProcessed();
        //find the last billing
        $card_id = $processed['id_card'];
        $date_bill=$processed['date'];

        //GET VAT
        $card_table = new Table('cc_card', 'vat, typepaid, credit');
        $card_clause = "id = ".$card_id;
        $card_result = $card_table -> get_list($FormHandler->DBHandle, $card_clause);

        if(!is_array($card_result)||empty($card_result[0]['vat'])||!is_numeric($card_result[0]['vat']))
            $vat=0;
        else
            $vat = $card_result[0][0];

        // FIND THE LAST BILLING
        $billing_table = new Table('cc_billing_customer', 'id,date');
        $clause_last_billing = "id_card = $card_id AND id != ".$FormHandler -> QUERY_RESULT;
        $result = $billing_table -> get_list($FormHandler->DBHandle, $clause_last_billing, "date", "desc");
        $call_table = new Table('cc_call', ' COALESCE(SUM(sessionbill),0)');
        $clause_call_billing ="card_id = $card_id AND ";
        $clause_charge = "id_cc_card = $card_id AND ";
        $desc_billing="";
        $desc_billing_postpaid="";
        $start_date =null;

        if (is_array($result) && !empty($result[0][0])) {
            $clause_call_billing .= "stoptime >= '" .$result[0][1]."' AND ";
            $clause_charge .= "creationdate >= '".$result[0][1]."' AND  ";
            $desc_billing = "Calls cost between the ".$result[0][1]." and  $date_bill" ;
            $desc_billing_postpaid="Amount for period between the ".date("Y-m-d", strtotime($result[0][1]))." and $date_bill";
            $start_date = $result[0][1];
        } else {
            $desc_billing = "Calls cost before the $date_bill" ;
            $desc_billing_postpaid="Amount for period before the $date_bill" ;
        }
        $lastpostpaid_amount = 0;
        $query_table = "cc_billing_customer LEFT JOIN cc_invoice ON cc_billing_customer.id_invoice = cc_invoice.id ";
        $query_table .= "LEFT JOIN (SELECT st1.id_invoice, TRUNCATE(SUM(st1.price),2) as total_price FROM cc_invoice_item AS st1 WHERE st1.type_ext ='POSTPAID' GROUP BY st1.id_invoice ) as items ON items.id_invoice = cc_invoice.id";
        $invoice_table = new Table($query_table, 'SUM( items.total_price) as total');
        $lastinvoice_clause = "cc_billing_customer.id_card = $card_id AND cc_invoice.paid_status=0 AND cc_billing_customer.id != ".$FormHandler -> QUERY_RESULT;
        $result_lastinvoice = $invoice_table ->get_list($FormHandler->DBHandle, $lastinvoice_clause);
        if (is_array($result_lastinvoice)&& !empty($result_lastinvoice[0][0])) {
            $lastpostpaid_amount = $result_lastinvoice [0][0];
        }
        $clause_call_billing .= "stoptime < '$date_bill' ";
        $clause_charge .= "creationdate < '$date_bill' ";


        $result =  $call_table -> get_list($FormHandler->DBHandle, $clause_call_billing);
        // COMMON BEHAVIOUR FOR PREPAID AND POSTPAID ... GENERATE A RECEIPT FOR THE CALLS OF THE MONTH
        if (is_array($result) && is_numeric($result[0][0])) {
            $amount_calls = $result[0][0];
            $amount_calls = ceil($amount_calls*100)/100;
            $date = date("Y-m-d h:i:s");
            /// create receipt
            $field_insert = "date, id_card, title, description,status";
            $title = gettext("SUMMARY OF CALLS");
            $description = gettext("Summary of the calls charged since the last billing");
            $value_insert = " '$date' , '$card_id', '$title','$description',1";
            $instance_table = new Table("cc_receipt", $field_insert);
            $id_receipt = $instance_table -> Add_table ($FormHandler->DBHandle, $value_insert, null, null,"id");
            if (!empty($id_receipt)&& is_numeric($id_receipt)) {
                $description = $desc_billing;
                $field_insert = "date, id_receipt,price,description,id_ext,type_ext";
                $instance_table = new Table("cc_receipt_item", $field_insert);
                $value_insert = " '$date' , '$id_receipt', '$amount_calls','$description','".$FormHandler -> QUERY_RESULT."','CALLS'";
                $instance_table -> Add_table ($FormHandler->DBHandle, $value_insert, null, null,"id");
            }
        }
        // GENERATE RECEIPT FOR CHARGE ALREADY CHARGED
        $table_charge = new Table("cc_charge", "*");
        $result =  $table_charge -> get_list($FormHandler->DBHandle, $clause_charge . " AND charged_status = 1");
        if (is_array($result)) {
            $field_insert = "date, id_card, title, description,status";
            $title = gettext("SUMMARY OF CHARGE");
            $date = date("Y-m-d h:i:s");
            $description = gettext("Summary of the charge charged since the last billing.");
            $value_insert = " '$date' , '$card_id', '$title','$description',1";
            $instance_table = new Table("cc_receipt", $field_insert);
            $id_receipt = $instance_table -> Add_table ($FormHandler->DBHandle, $value_insert, null, null,"id");
            if (!empty($id_receipt)&& is_numeric($id_receipt)) {
                foreach ($result as $charge) {
                    $description = gettext("CHARGE :").$charge['description'];
                    $amount = $charge['amount'];
                    $field_insert = "date, id_receipt,price,description,id_ext,type_ext";
                    $instance_table = new Table("cc_receipt_item", $field_insert);
                    $value_insert = " '".$charge['creationdate']."' , '$id_receipt', '$amount','$description','".$charge['id']."','CHARGE'";
                    $instance_table -> Add_table ($FormHandler->DBHandle, $value_insert, null, null,"id");
                }
            }
        }
        $total =0;
        $total_vat =0;
        // GENERATE INVOICE FOR CHARGE NOT YET CHARGED
        $table_charge = new Table("cc_charge", "*");
        $result =  $table_charge -> get_list($FormHandler->DBHandle, $clause_charge . " AND charged_status = 0 AND invoiced_status = 0");
        $last_invoice = null;
        if (is_array($result) && sizeof($result)>0) {
            $reference = generate_invoice_reference();
            $field_insert = "date, id_card, title ,reference, description,status,paid_status";
            $date = date("Y-m-d h:i:s");
            $title = gettext("BILLING CHARGES");
            $description = gettext("This invoice is for some charges unpaid since the last billing.")." ".$desc_billing_postpaid;
            $invoice_title = $title;
            $invoice_reference =$reference;
            $invoice_description = $description;
            $value_insert = " '$date' , '$card_id', '$title','$reference','$description',1,0";
            $instance_table = new Table("cc_invoice", $field_insert);
            $id_invoice = $instance_table -> Add_table ($FormHandler->DBHandle, $value_insert, null, null,"id");
            if (!empty($id_invoice)&& is_numeric($id_invoice)) {
                $last_invoice = $id_invoice;
                        foreach ($result as $charge) {
                            $description = gettext("CHARGE :").$charge['description'];
                            $amount = $charge['amount'];
                            $total = $total + $amount;
                            $total_vat =$total_vat + round($amount *(1+($vat/100)),2);
                            $field_insert = "date, id_invoice,price,vat,description,id_ext,type_ext";
                            $instance_table = new Table("cc_invoice_item", $field_insert);
                            $value_insert = " '".$charge['creationdate']."' , '$id_invoice', '$amount','$vat','$description','".$charge['id']."','CHARGE'";
                            $instance_table -> Add_table ($FormHandler->DBHandle, $value_insert, null, null,"id");
                        }
                    }
        }

        // behaviour postpaid
        if ($card_result[0]['typepaid']==1 && is_numeric($card_result[0]['credit']) && ($card_result[0]['credit']+$lastpostpaid_amount)<0) {

            //GENERATE AN INVOICE TO COMPLETE THE BALANCE
            if (!empty($last_invoice)) {
            $id_invoice = $last_invoice;
            } else {
            $reference = generate_invoice_reference();
            $field_insert = "date, id_card, title ,reference, description,status,paid_status";
            $date = date("Y-m-d h:i:s");
            $title = gettext("BILLING POSTPAID");
            $description = gettext("Invoice for POSTPAID");
            $invoice_title = $title;
            $invoice_reference =$reference;
            $invoice_description = $description;
            $value_insert = " '$date' , '$card_id', '$title','$reference','$description',1,0";
            $instance_table = new Table("cc_invoice", $field_insert);
            $id_invoice = $instance_table -> Add_table ($FormHandler->DBHandle, $value_insert, null, null,"id");
            }

            if (!empty($id_invoice)&& is_numeric($id_invoice)) {
                $last_invoice = $id_invoice;
                $description = $desc_billing_postpaid;
                $amount = abs($card_result[0]['credit']+$lastpostpaid_amount);
                $total = $total + $amount;
                $total_vat =$total_vat + round($amount *(1+($vat/100)),2);
                $field_insert = "date, id_invoice,price,vat,description,id_ext,type_ext";
                $instance_table = new Table("cc_invoice_item", $field_insert);
                $value_insert = " '$date' , '$id_invoice', '$amount','$vat','$description','".$FormHandler -> QUERY_RESULT."','POSTPAID'";
                $instance_table -> Add_table ($FormHandler->DBHandle, $value_insert, null, null,"id");
            }
        }
        if (!empty($last_invoice)) {
            $param_update_billing = "id_invoice = '".$last_invoice."'";
            $clause_update_billing = " id= ".$FormHandler -> QUERY_RESULT;
            $billing_table ->Update_table($FormHandler->DBHandle,$param_update_billing,$clause_update_billing);
        }
        //Send a mail for invoice to pay
        if (!empty($last_invoice)) {
            $total = round($total,2);
            $mail = new Mail(Mail::$TYPE_INVOICE_TO_PAY, $card_id);
            $mail->replaceInEmail(Mail::$INVOICE_REFERENCE_KEY, $invoice_reference);
            $mail->replaceInEmail(Mail::$INVOICE_TITLE_KEY, $invoice_title);
            $mail->replaceInEmail(Mail::$INVOICE_DESCRIPTION_KEY, $invoice_description);
            $mail->replaceInEmail(Mail::$INVOICE_TOTAL_KEY, $total);
            $mail->replaceInEmail(Mail::$INVOICE_TOTAL_VAT_KEY, $total_vat);
            $mail -> send();
        }

        //Update billing ...
        if (!empty($start_date)) {
                $param_update_billing = "start_date = '".$start_date."'";
                $clause_update_billing = " id= ".$FormHandler -> QUERY_RESULT;
                $billing_table ->Update_table($FormHandler->DBHandle,$param_update_billing,$clause_update_billing);
        }
    }


    public static function create_invoice_after_refill()
    {
        global $A2B;
        $FormHandler = FormHandler::GetInstance();
        $processed = $FormHandler->getProcessed();

        if ($processed['added_invoice']==1) {
            //CREATE AND UPDATE REF NUMBER
            $list_refill_type=getRefillType_List();
            $refill_type = $processed['refill_type'];
            $reference = generate_invoice_reference();
            $field_insert = "date, id_card, title ,reference, description";
            $date = $processed['date'];
            $card_id = $processed['card_id'];
            if ($refill_type!=0) {
                $title = $list_refill_type[$refill_type]." ".gettext("REFILL");
            } else {
                $title = gettext("REFILL");
            }
            $description = gettext("Invoice for refill");

            $value_insert = " '$date' , '$card_id', '$title','$reference','$description' ";
            $instance_table = new Table("cc_invoice", $field_insert);
            $id_invoice = $instance_table -> Add_table ($FormHandler->DBHandle, $value_insert, null, null,"id");
            //load vat of this card
            if (!empty($id_invoice)&& is_numeric($id_invoice)) {
                $amount = $processed['credit'];
                $description = $processed['description'];
                $card_table = new Table('cc_card', 'vat');
                $card_clause = "id = ".$card_id;
                $card_result = $card_table -> get_list($FormHandler->DBHandle, $card_clause);
                if(!is_array($card_result)||empty($card_result[0][0])||!is_numeric($card_result[0][0])) $vat=0;
                else $vat = $card_result[0][0];
                $field_insert = "date, id_invoice ,price,vat, description";
                $instance_table = new Table("cc_invoice_item", $field_insert);
                $value_insert = " '$date' , '$id_invoice', '$amount','$vat','$description' ";
                $instance_table -> Add_table ($FormHandler->DBHandle, $value_insert, null, null,"id");
            }
        }
    }


    public static function create_invoice_reference()
    {
        global $A2B;
        $FormHandler = FormHandler::GetInstance();
        $processed = $FormHandler->getProcessed();
        $id_invoice = $FormHandler -> QUERY_RESULT;
        //CREATE AND UPDATE REF NUMBER
        $reference = generate_invoice_reference();
        $instance_table_invoice = new Table("cc_invoice");
        $param_update_invoice = "reference = '".$reference."'";
        $clause_update_invoice = " id ='$id_invoice'";
        $instance_table_invoice-> Update_table ($FormHandler->DBHandle, $param_update_invoice, $clause_update_invoice, $func_table = null);

    }


    /**
     * Function create_refill
     * @public
     */
    public static function create_refill_after_payment()
    {
        $FormHandler = FormHandler::GetInstance();
        $processed = $FormHandler->getProcessed();
        if ((int)$processed['added_refill'] === 1) {
            $id_payment = $FormHandler->QUERY_RESULT;
            // CREATE REFILL
            $date = $processed['date'];
            $card_id = $processed['card_id'];
            $refill_type = (int)$processed['payment_type'];
            $description = $processed['description'];
            $card_result = (new Table("cc_card", "vat"))->getRow($FormHandler->DBHandle, ["id" => $card_id]);
            $vat = (int)($card_result["vat"] ?? 0);
            $credit = $processed['payment'] / (1 + $vat / 100);

            $insert_values = compact("date", "credit", "card_id", "refill_type", "description");
            (new Table("cc_logrefill"))->addRow($FormHandler->DBHandle, $insert_values, "id", $id_refill);

            // REFILL CARD - UPDATE CARD
            $insert_values = ["credit" => ["credit + ?", $credit]];
            (new Table("cc_card"))->updateRow($FormHandler->DBHandle, $insert_values, ["id" => $card_id]);

            // LINK THE REFILL TO THE PAYMENT .. UPADTE PAYMENT
            $insert_values = ["id_logrefill" => $id_refill];
            (new Table("cc_logpayment"))->updateRow($FormHandler->DBHandle, $insert_values, ["id" => $id_payment]);

            // Create invoice associated
            $list_refill_type = getRefillType_List();

            $insert_values = [
                "date" => $date,
                "id_card" => $card_id,
                "title" => trim(($list_refill_type[$refill_type] ?? "") . " " . _("REFILL")),
                "reference" => generate_invoice_reference(),
                "description" => gettext("Invoice for refill"),
                "status" => 1,
                "paid_status" => 1,
            ];
            (new Table("cc_invoice"))->addRow($FormHandler->DBHandle, $insert_values, "id", $id_invoice);

            //add payment to this invoice
            $insert_values = compact("id_invoice", "id_payment");
            (new Table("cc_invoice_payment"))->addRow($FormHandler->DBHandle, $insert_values);

            //load vat of this card
            if (!empty($id_invoice) && is_numeric($id_invoice)) {
                $insert_values = [
                    "date" => $date,
                    "id_invoice" => $id_invoice,
                    "price" => $credit,
                    "vat" => $vat,
                    "description" => $description
                ];
                (new Table("cc_invoice_item"))->addRow($FormHandler->DBHandle, $insert_values);
            }
        }

        if ($processed['added_commission']==1) {
            $card_id = $processed['card_id'];
            $table_transaction = new Table();
            $result_agent = $table_transaction -> SQLExec($FormHandler->DBHandle,"SELECT cc_card_group.id_agent FROM cc_card LEFT JOIN cc_card_group ON cc_card_group.id = cc_card.id_group WHERE cc_card.id = $card_id");

            if (is_array($result_agent)&& !is_null($result_agent[0]['id_agent']) && $result_agent[0]['id_agent']>0 ) {

                // test if the agent exist and get its commission
                $id_agent = $result_agent[0]['id_agent'];
                // update refill & payment to keep a trace of agent in the timeline
                $table_refill = new Table("cc_logrefill");
                $table_payment = new Table("cc_logpayment");
                $param_update = "agent_id = '".$id_agent."'";
                if (!empty($id_refill)) {
                    $clause_update_refill_agent = " id ='$id_refill'";
                    $table_refill-> Update_table ($FormHandler->DBHandle, $param_update, $clause_update_refill_agent, $func_table = null);
                }
                $clause_update_payment_agent = " id ='$id_payment'";
                $table_payment-> Update_table ($FormHandler->DBHandle, $param_update, $clause_update_payment_agent, $func_table = null);

                $agent_table = new Table("cc_agent", "commission");
                $agent_clause = "id = ".$id_agent;
                $result_agent= $agent_table -> get_list($FormHandler->DBHandle, $agent_clause);

                if (is_array($result_agent) && is_numeric($result_agent[0]['commission']) && $result_agent[0]['commission']>0) {
                    $field_insert = "id_payment, id_card, amount,description,id_agent";
                    $commission = a2b_round($processed['payment'] * ($result_agent[0]['commission']/100));
                    $description_commission = gettext("AUTOMATICALY GENERATED COMMISSION!");
                    $description_commission.= "\nID CARD : ".$card_id;
                    $description_commission.= "\nID PAYMENT : ".$id_payment;
                    $description_commission.= "\nPAYMENT AMOUNT: ".$amount_paid;
                    $description_commission.= "\nCOMMISSION APPLIED: ".$result_agent[0]['commission'];
                    $value_insert = "'".$id_payment."', '$card_id', '$commission','$description_commission','$id_agent'";
                    $commission_table = new Table("cc_agent_commission", $field_insert);
                    $id_commission = $commission_table -> Add_table ($FormHandler->DBHandle, $value_insert, null, null,"id");
                    $table_agent = new Table('cc_agent');
                    $param_update_agent = "com_balance = com_balance + '".$commission."'";
                    $clause_update_agent = " id='".$id_agent."'";
                    $table_agent -> Update_table ($FormHandler->DBHandle, $param_update_agent, $clause_update_agent, $func_table = null);
                }
            }
        }
    }

    public static function create_agent_refill()
    {
        global $A2B;
        $FormHandler = FormHandler::GetInstance();
        $processed = $FormHandler->getProcessed();

        if ($processed['added_refill']==1) {
            $id_payment = $FormHandler -> QUERY_RESULT;

            //CREATE REFILL
            $field_insert = "date, credit, agent_id ,refill_type, description";
            $date = $processed['date'];
            $credit = $processed['payment'];
            $agent_id = $processed['agent_id'];
            $refill_type= $processed['payment_type'];
            $description = $processed['description'];
            $value_insert = " '$date' , '$credit', '$agent_id','$refill_type', '$description' ";
            $instance_sub_table = new Table("cc_logrefill_agent", $field_insert);
            $id_refill = $instance_sub_table -> Add_table ($FormHandler->DBHandle, $value_insert, null, null,"id");

            //REFILL AGENT .. UPADTE AGENT
            $instance_table_agent = new Table("cc_agent");
            $param_update_agent = "credit = credit + '".$credit."'";
            $clause_update_agent = " id='$agent_id'";
            $instance_table_agent -> Update_table ($FormHandler->DBHandle, $param_update_agent, $clause_update_agent, $func_table = null);

            //LINK THE REFILL TO THE PAYMENT .. UPADTE PAYMENT
            $instance_table_pay = new Table("cc_logpayment_agent");
            $param_update_pay = "id_logrefill = '".$id_refill."'";
            $clause_update_pay = " id ='$id_payment'";
            $instance_table_pay-> Update_table ($FormHandler->DBHandle, $param_update_pay, $clause_update_pay, $func_table = null);
        }
    }


    /**
     * Function to edit the fields
     * @public
     */
    public static function create_sipiax_friends()
    {
        global $A2B;
        $FormHandler = FormHandler::GetInstance();
        $processed = $FormHandler->getProcessed();
        $id = $FormHandler -> QUERY_RESULT; // DEFINED BEFORE FG_ADDITIONAL_FUNCTION_AFTER_ADD
        $sip = stripslashes($processed['sip_buddy']);
        $iax = stripslashes($processed['iax_buddy']);

        // $FormHandler -> FG_QUERY_EXTRA_HIDDED - username, useralias, uipass, loginkey
        if (strlen($FormHandler -> REALTIME_SIP_IAX_INFO[0])>0) {
            $username 	= $FormHandler -> REALTIME_SIP_IAX_INFO[0];
            $uipass 	= $FormHandler -> REALTIME_SIP_IAX_INFO[2];
            $useralias 	= $FormHandler -> REALTIME_SIP_IAX_INFO[1];
        } else {
            $username 	= $processed['username'];
            $uipass 	= $processed['uipass'];
            $useralias 	= $processed['useralias'];
        }

        $instance_realtime = new Realtime();

        $instance_realtime -> insert_voip_config ($sip, $iax, $id, $username, $uipass);

        // Save info in table and in sip file
        if ($sip == 1) {
            $instance_realtime -> create_trunk_config_file ('sip');
        }

        // Save info in table and in iax file
        if ($iax == 1) {
            $instance_realtime -> create_trunk_config_file ('iax');
        }
    }

    public static function create_lock_card()
    {
        global $A2B;
        $FormHandler = FormHandler::GetInstance();
        $processed = $FormHandler->getProcessed();
        $id = $FormHandler -> QUERY_RESULT;
        if ($processed['block'] == 1) {
            $instance_sub_table = new Table("cc_card");
            $param_update_card = "lock_date = NOW()";
            $clause_update_card = "id = $id";
            $instance_sub_table -> Update_table ($FormHandler->DBHandle, $param_update_card, $clause_update_card, $func_table = null);
        }
    }

    public static function change_card_lock()
    {
        global $A2B;
        $FormHandler = FormHandler::GetInstance();
        $processed = $FormHandler->getProcessed();
        $instance_sub_table = new Table("cc_card", "block");
        $FG_TABLE_CLAUSE_CARD = "id = ".$processed['id'];
        $card_info = $instance_sub_table -> get_list ($FormHandler->DBHandle, $FG_TABLE_CLAUSE_CARD);
        if (is_array($result) && !empty($result[0][0])) {
            $card_lock_info = $card_info[0][0];

            if ($card_lock_info != $processed['block'] && $processed['block'] == 1) {
                $param_update_card = "lock_date = NOW()";
                $clause_update_card = "id = ".$processed['id'];
                $instance_sub_table -> Update_table ($FormHandler->DBHandle, $param_update_card, $clause_update_card, $func_table = null);
            }
        }
    }

    /**
     * Function to added new sign-ups in the notification
     * @public
     */
    public static function create_notification_signup()
    {
        global $A2B;
        $FormHandler = FormHandler::GetInstance();
        $id_card = $FormHandler -> QUERY_RESULT;
        NotificationsDAO::addNotification("added_new_signup",Notification::$MEDIUM,Notification::$CUST,$id_card,Notification::$LINK_CARD,$id_card);
    }
}
