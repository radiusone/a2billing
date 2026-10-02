<?php

use A2billing\A2Billing;
use A2billing\Agent;
use A2billing\Connection;
use A2billing\Forms\FormHandler;
use Illuminate\Database\Connection as Db;

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of A2Billing (http://www.a2billing.net/)
 *
 * A2Billing, Commercial Open Source Telecom Billing platform,
 * powered by Star2billing S.L. <http://www.star2billing.com/>
 *
 * @copyright   Copyright © 2004-2015 - Star2billing S.L.
 * @copyright   Copyright © 2022-2025 RadiusOne Inc.
 * @author      Belaid Arezqui <areski@gmail.com>
 * @author      Michael Newton <mnewton@goradiusone.com>
 * @license     http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @package     A2Billing
 *
 * Software License Agreement (GNU Affero General Public License)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *
**/

$menu_section = 1;
require_once __DIR__ . "/../../common/lib/agent.defines.php";
require_once __DIR__ . "/../../common/form_data/FG_var_card.inc";
/**
 * @var A2Billing $A2B
 * @var FormHandler $HD_Form
 * @var array $cardstatus_list
 * @var array $language_list
 * @var string $cardnumber_length
 * @var string $popup_select
 * @var string $popup_formname
 * @var string $popup_fieldname
 */

Agent::checkPageAccess(Agent::ACX_CUSTOMER);
$form_action ??= "list";

if ($form_action === "ask-edit") {
    Agent::checkPageAccess(Agent::ACX_EDIT_CUSTOMER);
}

if ($form_action === "ask-delete") {
    Agent::checkPageAccess(Agent::ACX_DELETE_CUSTOMER);
}

// SECURTY CHECK FOR AGENT
if ($form_action !== "list" && !empty($id)) {
    $result_security = Connection::getConnection("cc_card")
        ->leftJoin("cc_card_group", "cc_card.id_group", "cc_card_group.id")
        ->where("cc_card.id", $id)
        ->value("cc_card_group.id_agent");
    if ($result_security != Agent::id()) {
        header("Location: A2B_entity_card.php?section=1");
        die();
    }
}
$HD_Form -> init();

/********************************* BATCH UPDATE ***********************************/
getpost_ifset(['batchupdate', 'check', 'type', 'mode']);

/**
 * @var string $batchupdate
 * @var array $check
 * @var array $type
 * @var array $mode
 */
$batchupdate ??= 0;
$check ??= [];
$type ??= [];
$mode ??= [];

// CHECK IF REQUEST OF BATCH UPDATE
if ($batchupdate == 1 && count($check)) {
    $HD_Form->prepare_list_subselection('list');

    $uf = [];
    getpost_ifset(
        [
            "upd_inuse",
            "upd_status",
            "upd_language",
            "upd_simultaccess",
            "upd_currency",
            "upd_enableexpire",
            "upd_expirationdate",
            "upd_expiredays",
            "upd_runservice"
        ],
        $uf
    );
    $update_fields = collect($uf)
        ->mapWithKeys(fn ($v, $k) =>[substr($k, 4) => $v])
        ->toArray();

    if (!empty($update_fields["expirationdate"])) {
        // html datetime input sends as 2022-02-21T13:40
        $update_fields["expirationdate"] = str_replace("T", " ", $update_fields["expirationdate"]);
    }

    $updates = [];
    foreach ($update_fields as $col => $val) {
        if (($mode["upd_$col"] ?? "1") === "1") {
            // Standard update mode
            $updates[$col] = $val;
        } elseif ($mode["upd_$col"] === "2") {
            // Mode 2 - Equal - Add - Subtract
            $val = preg_replace("/[^0-9.-]/", "", $val);
            $updates[$col] = match($type["upd_$col"] ?? "1") {
                "1" => $val,
                "2" => Connection::getConnection()->raw("`$col` + $val"),
                "3" => Connection::getConnection()->raw("`$col` - $val"),
            };
        }
    }

    $qb = $HD_Form->query_builder->clone();
    $qb->limit = null;
    $qb->joins = null;
    if (!$qb->update($updates)) {
        $update_msg = _('Could not perform the batch update!');
    } else {
        $update_msg = _('The batch update has been successfully perform!');
    }
}
/********************************* END BATCH UPDATE ***********************************/
getpost_ifset([
    'addcredit',
    'description',
]);
/**
 * @var string $addcredit
 * @var string $description
 */
if ($form_action === "addcredit" && !empty($addcredit) && !empty($id)) {
    $agent_info = Connection::getConnection("cc_card")
        ->select("cc_agent.id", "cc_agent.credit", "cc_agent.currency", "cc_agent.commission")
        ->leftJoin("cc_card_group", "cc_card.id_group", "cc_card_group.id")
        ->leftJoin("cc_agent", "cc_card_group.id_agent", "cc_agent.id")
        ->where("cc_card.id", $id)
        ->where("cc_agent.id", Agent::id())
        ->first();
    if ($agent_info) {
        $description ??= _("Refill executed");
        $credit_agent = $agent_info["credit"] ?? 0;
        $commission_amt = $agent_info["commission"] ?? 0;
        if ($credit_agent >= $addcredit) {
            $id_refill = null;
            Connection::getConnection()->transaction(function (Db $conn) use ($id, $addcredit, $description, &$id_refill, $commission_amt) {
                //Substract credit for agent
                $conn->table("cc_agent")
                    ->where("id", Agent::id())
                    ->decrement("credit", $addcredit);
                // Add credit to Customer
                $conn->table("cc_card")
                    ->where("id", $id)
                    ->increment("credit", $addcredit);
                $id_refill = $conn->table("cc_logrefill")
                    ->insertGetId(
                        [
                            "credit" => $addcredit,
                            "card_id" => $id,
                            "description" => $description,
                            "refill_type" => 3,
                            "agent_id" => Agent::id(),
                        ],
                        "id"
                    );
                if ($commission_amt) {
                    $commission = a2b_round($addcredit * ($commission_amt / 100));
                    $description_commission = __("GENERATED COMMISSION OF A CUSTOMER REFILLED BY AN AGENT!");
                    $description_commission.= "\nID CARD : $id";
                    $description_commission.= "\nID REFILL : $id_refill";
                    $description_commission.= "\nREFILL AMOUNT: $addcredit";
                    $description_commission.= "\nCOMMISSION APPLIED: $commission_amt";
                    $conn->table("cc_agent_commission")
                        ->insert([
                            "id_payment" => null,
                            "id_card" => $id,
                            "amount" => $commission,
                            "description" => $description_commission,
                            "id_agent" => Agent::id()
                        ],
                        );
                    $conn->table("cc_agent")
                        ->where("id", Agent::id())
                        ->increment("com_balance", $commission);
                }
            });

            $update_msg ='<span style="color:green; font-weight: bold">' . $description . '</span>';
        } else {
            $credit_cur = convert_currency($agent_info["credit"], $agent_info["currency"], BASE_CURRENCY);
            $update_msg ='<span style="font-weight: bold; color: red">' . gettext("You don't have enough credit to do this refill. You have ") . $credit_cur . ' ' . $agent_info["currency"] . ' </span>';
        }
    } else {
        $update_msg ='<span style="font-weight: bold; color: red">' . gettext("Impossible to refill this card ") . '</span>';
    }
}

if ($form_action === "addcredit") {
    $form_action = 'list';
}

$list = $HD_Form -> perform_action($form_action);


// #### HEADER SECTION
require_once __DIR__ . "/templates/main.php";



if ($popup_select) {
?>
<SCRIPT LANGUAGE="javascript">
<!-- Begin

function clear_textbox()
{
    if (document.theForm.cardnumber.value == "enter cardnumber")
        document.theForm.cardnumber.value = "";
}

function clear_textbox2()
{
    if (document.theForm.choose_list.value == "enter ID Card")
        document.theForm.choose_list.value = "";
}


function openURL(theLINK)
{
    // get the value of CARD ID
    cardid = document.theForm.choose_list.value;

    // get value of CARDNUMBER and concatenate if any of the values is numeric
    cardnumber = document.theForm.cardnumber.value;

    if ( (!IsNumeric(cardid)) && (!IsNumeric(cardnumber)) ){
        alert('CARD ID or CARDNUMBER must be numeric');
        return;
    }

    goURL = cardid + "&cardnumber=" +document.theForm.cardnumber.value;

    addcredit = 0;
    // get calue of credits
    addcredit = document.theForm.addcredit.value;

    description = '';
    // get calue of credits
    description = document.theForm.description.value;



    if ( (addcredit == 0) || (!IsNumeric(parseFloat(addcredit))) ){
        alert ('Please , Fill credit box with a numeric value');
        return;
    }

    // redirect browser to the grabbed value (hopefully a URL)
    self.location.href = theLINK + goURL + "&addcredit="+addcredit +"&description="+description;

    return false;

}
// End -->
</script>
<?php
}


// #### HELP SECTION
if ($form_action=='list' && !($popup_select>=1)) {
    echo create_help(gettext("Customers are listed below by card number. Each row corresponds to one customer, along with information such as their call plan, credit remaining, etc.<br>") .
        gettext("The SIP and IAX buttons create SIP and IAX entries to allow direct VoIP connections to the Asterisk server without further authentication."));

?>

<div class="row">
    <div class="col text-center">
        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#tohide1" aria-expanded="false" aria-controls="tohide1">
            <?= _("REFILL") ?>
        </button>
    </div>
</div>

<div id="tohide1" class="collapse">
    <form NAME="theForm">
       <table width="90%" border="0" align="center">
        <tr>
           <td align="left" width="5%">
           </td>
          <td align="left" width="35%" class="bgcolor_001">
               <table>
            <tr><td align="center">
               <?php echo gettext("CARD ID");?>	 :<input class="form_input_text" name="choose_list" onfocus="clear_textbox2();" size="18" maxlength="16" value="enter ID Card">
                    <a href="A2B_entity_card.php" data-uri-extra="&nodisplay=1" class="btn btn-primary popup_trigger" aria-label="open a popup to select an item">&gt;</a>
            </td></tr>
            </table>
        </td>
        <td  class="bgcolor_001" align="center">
            <table>
                <tr>
                    <td>
                        <?php echo gettext("CREDIT");?>&nbsp;:
                    </td>
                    <td>
                        <input class="form_enter" name="addcredit" size="18" maxlength="6" value=""> <?php echo strtoupper($A2B->config['global']['base_currency']); ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php echo gettext("DESCRIPTION");?>&nbsp;:
                    </td>
                    <td>
                        <textarea class="form_input_textarea" name="description" cols="40" rows="4"></textarea>
                    </td>
                </tr>

                <tr>
                    <td colspan="2" align="center">
                    <input class="form_input_button"
                TYPE="button" VALUE="<?php echo gettext("ADD CREDIT");?>" onClick="openURL('?form_action=addcredit&current_page=<?php echo $current_page?>&order=<?php echo $order?>&sens=<?php echo $sens?>&id=')">

                    </td>
                </tr>
            </table>

        </td>
        </tr>

      </table>
  </form>
</div>

<div class="row">
    <div class="col text-center">
        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#tohide" aria-expanded="false" aria-controls="tohide">
            <?= _("REFILL") ?>
            <?php if (!empty($_SESSION['entity_card_selection'])) echo gettext("search activated"); ?>
        </button>
    </div>
</div>

<div id="tohide2" class="collapse">

<?php
// #### CREATE SEARCH FORM
if ($form_action == "list") {
    $HD_Form -> create_search_form();
}
?>

</div>

<?php

/********************************* BATCH UPDATE ***********************************/
if ($form_action == "list" && (!($popup_select>=1))) {

    $FG_TABLE_CLAUSE = "";

?>
<!-- ** ** ** ** ** Part for the Update ** ** ** ** ** -->
<div class="row">
    <div class="col text-center">
        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#tohide" aria-expanded="false" aria-controls="tohide">
            <?= _("BATCH UPDATE") ?>
        </button>
    </div>
</div>

<div id="tohide" class="collapse">
<center>
<b>&nbsp;<?php echo $HD_Form -> FG_LIST_VIEW_ROW_COUNT ?> <?php echo gettext("cards selected!"); ?>&nbsp;<?php echo gettext("Use the options below to batch update the selected cards.");?></b>
       <table align="center" border="0" width="65%"  cellspacing="1" cellpadding="2">
        <tbody>
        <form name="updateForm" action="" method="post">
        <INPUT type="hidden" name="batchupdate" value="1">
        <tr>
          <td align="left" class="bgcolor_001" >
                  <input name="check[upd_inuse]" type="checkbox" <?php if ($check["upd_inuse"]=="on") echo "checked"?>>
          </td>
          <td align="left"  class="bgcolor_001">
                1)&nbsp;<?php echo gettext("In use"); ?>&nbsp;:
                <input class="form_input_text"  name="upd_inuse" size="10" maxlength="6" value="<?php if (isset($upd_inuse)) echo $upd_inuse; else echo '0';?>">
                <br/>
          </td>
        </tr>
        <tr>
          <td align="left"  class="bgcolor_001">
              <input name="check[upd_status]" type="checkbox" <?php if ($check["upd_status"]=="on") echo "checked"?> >
          </td>
          <td align="left" class="bgcolor_001">
                  2)&nbsp;<?php echo gettext("Status");?>&nbsp;:
                <select NAME="upd_status" size="1" class="form_input_select">
                <?php foreach ($cardstatus_list as $key => $cur_value) { ?>
                    <option value='<?php echo $cur_value[1] ?>' <?php if ($upd_status==$cur_value[1]) echo 'selected="selected"'?>><?php echo $cur_value[0] ?></option>
                <?php } ?>
                </select><br/>
          </td>
        </tr>

        <tr>
          <td align="left" class="bgcolor_001">
                  <input name="check[upd_language]" type="checkbox" <?php if ($check["upd_language"]=="on") echo "checked"?>>
          </td>
          <td align="left"  class="bgcolor_001">
                3)&nbsp;<?php echo gettext("Language");?>&nbsp;:
                <select NAME="upd_language" size="1" class="form_input_select">
                <?php foreach ($language_list as $key => $cur_value) { ?>
                    <option value='<?php echo $cur_value[1] ?>' <?php if ($upd_language==$cur_value[1]) echo 'selected="selected"'?>><?php echo $cur_value[0] ?></option>
                <?php } ?>
            </select>
          </td>
        </tr>
        <tr>
          <td align="left" class="bgcolor_001">
                  <input name="check[upd_simultaccess]" type="checkbox" <?php if ($check["upd_simultaccess"]=="on") echo "checked"?>>
          </td>
          <td align="left" class="bgcolor_001">
                4)&nbsp;<?php echo gettext("Access");?>&nbsp;:
                <select NAME="upd_simultaccess" size="1" class="form_input_select">
                    <option value='0'  <?php if ($upd_simultaccess==0) echo 'selected="selected"'?>><?php echo gettext("INDIVIDUAL ACCESS");?></option>
                    <option value='1'  <?php if ($upd_simultaccess==1) echo 'selected="selected"'?>><?php echo gettext("SIMULTANEOUS ACCESS");?></option>
            </select>
          </td>
        </tr>
        <tr>
          <td align="left" class="bgcolor_001">
                  <input name="check[upd_currency]" type="checkbox" <?php if ($check["upd_currency"]=="on") echo "checked"?>>
          </td>
          <td align="left"  class="bgcolor_001">
                5)&nbsp;<?php echo gettext("Currency");?>&nbsp;:
                <select NAME="upd_currency" size="1" class="form_input_select">
                <?php
                    foreach ($currencies_list as $key => $cur_value) {
                ?>
                    <option value='<?php echo $key ?>'  <?php if ($upd_currency==$key) echo 'selected="selected"'?>><?php echo $cur_value[1].' ('.$cur_value[2].')' ?></option>
                <?php } ?>
            </select>
          </td>
        </tr>

        <tr>
          <td align="left" class="bgcolor_001">
                  <input name="check[upd_enableexpire]" type="checkbox" <?php if ($check["upd_enableexpire"]=="on") echo "checked"?>>
          </td>
          <td align="left"  class="bgcolor_001">
                6)&nbsp;<?php echo gettext("Enable expire");?>&nbsp;:
                <select name="upd_enableexpire" class="form_input_select" >
                    <option value="0"  <?php if ($upd_enableexpire==0) echo 'selected="selected"'?>> <?php echo gettext("NO EXPIRY");?></option>
                    <option value="1"  <?php if ($upd_enableexpire==1) echo 'selected="selected"'?>> <?php echo gettext("EXPIRE DATE");?></option>
                    <option value="2"  <?php if ($upd_enableexpire==2) echo 'selected="selected"'?>> <?php echo gettext("EXPIRE DAYS SINCE FIRST USE");?></option>
                    <option value="3"  <?php if ($upd_enableexpire==3) echo 'selected="selected"'?>> <?php echo gettext("EXPIRE DAYS SINCE CREATION");?></option>
                </select>
          </td>
        </tr>
        <tr>
          <td align="left" class="bgcolor_001">
                  <input name="check[upd_expirationdate]" type="checkbox" <?php if ($check["upd_expirationdate"]=="on") echo "checked"?>>
          </td>
          <td align="left"  class="bgcolor_001">
                <?php
                    $begin_date = date("Y");
                    $begin_date_plus = date("Y") + 10;
                    $end_date = date("-m-d H:i:s");
                    $comp_date = "value='".$begin_date.$end_date."'";
                    $comp_date_plus = "value='".$begin_date_plus.$end_date."'";
                ?>
                7)&nbsp;<?php echo gettext("Expiry date");?>&nbsp;:
                 <input class="form_input_text"  name="upd_expirationdate" size="20" maxlength="30" <?php echo $comp_date_plus; ?>> <font class="version"><?php echo gettext("(Format YYYY-MM-DD HH:MM:SS)");?></font>
          </td>
        </tr>
        <tr>
          <td align="left" class="bgcolor_001">
                  <input name="check[upd_expiredays]" type="checkbox" <?php if ($check["upd_expiredays"]=="on") echo "checked"?>>
          </td>
          <td align="left"  class="bgcolor_001">
                8)&nbsp;<?php echo gettext("Expiration days");?>&nbsp;:
                <input class="form_input_text"  name="upd_expiredays" size="10" maxlength="6" value="<?php if (isset($upd_expiredays)) echo $upd_expiredays; else echo '0';?>">
                <br/>
        </td>
        </tr>
        <tr>
          <td align="left" class="bgcolor_001">
              <input name="check[upd_runservice]" type="checkbox" <?php if ($check["upd_runservice"]=="on") echo "checked"?>>
          </td>
          <td align="left"  class="bgcolor_001">
                 9)&nbsp;<?php echo gettext("Run service");?>&nbsp;:
                <font class="version">
                <input type="radio" NAME="type[upd_runservice]" value="1" <?php if ((!isset($type["upd_runservice"]))|| ($type["upd_runservice"]=='1') ) {?>checked<?php }?>>
                <?php echo gettext("Yes");?> <input type="radio" NAME="type[upd_runservice]" value="0" <?php if ($type["upd_runservice"]=='0') {?>checked<?php }?>><?php echo gettext("No");?>
                </font>
          </td>
        </tr>

        <tr>
            <td align="right" class="bgcolor_001"></td>
             <td align="right"  class="bgcolor_001">
                <input class="form_input_button"  value=" <?php echo gettext("BATCH UPDATE CARD");?>  " type="submit">
            </td>
        </tr>
        </form>
        </table>
</center>
</div>
<!-- ** ** ** ** ** Part for the Update ** ** ** ** ** -->
<?php
} // END if ($form_action == "list")
?>


<?php  if ( !USE_REALTIME && isset($_SESSION["is_sip_iax_change"]) && $_SESSION["is_sip_iax_change"]) { ?>
      <table  border="0" align="center" cellpadding="0" cellspacing="0" >
        <TR><TD style="border-bottom: medium dotted #ED2525" align="center"> <?php echo gettext("Changes detected on SIP/IAX Friends");?></TD></TR>
        <TR><FORM NAME="sipfriend">
            <td height="31" class="bgcolor_013" style="padding-left: 5px; padding-right: 3px;" align="center">
            <font color=white><b>
            <?php  if ( isset($_SESSION["is_sip_changed"]) && $_SESSION["is_sip_changed"] ) { ?>
            SIP : <input class="form_input_button"  TYPE="button" VALUE="<?php echo gettext("GENERATE ADDITIONAL_A2BILLING_SIP.CONF");?>"
            onClick="self.location.href='./CC_generate_friend_file.php?voip_type=sipfriend';">
            <?php }
            if ( isset($_SESSION["is_iax_changed"]) && $_SESSION["is_iax_changed"] ) { ?>
            IAX : <input class="form_input_button"  TYPE="button" VALUE="<?php echo gettext("GENERATE ADDITIONAL_A2BILLING_IAX.CONF");?>"
            onClick="self.location.href='./CC_generate_friend_file.php?voip_type=iaxfriend';">
            <?php } ?>
            </b></font></td></FORM>
        </TR>
</table>
<?php  } // endif is_sip_iax_change

}elseif (!($popup_select>=1)) echo create_help(gettext("Create and edit the properties of each customer. Click <b>CONFIRM DATA</b> at the bottom of the page to save changes."));


if (isset($update_msg) && strlen($update_msg)>0) echo "<br/><center>$update_msg</center>";



// #### TOP SECTION PAGE
$HD_Form -> create_toppage ($form_action);
if (!$popup_select && $form_action == "ask-add") {
?>
<table width="70%" align="center" cellpadding="2" cellspacing="0">
    <script>
    function submitform()
    {
        document.cardform.submit();
    }
    </script>
    <form action="A2B_entity_card.php?form_action=ask-add&section=1" method="post" name="cardform">
    <tr>
        <td class="viewhandler_filter_td1">
        <span>

            <font class="viewhandler_filter_on"><?php echo gettext("Change the Card Number Length")?> :</font>
            <?= $HD_Form->csrf_inputs() ?>
            <select name="cardnumberlength_list" size="1" class="form_input_select" onChange="submitform()">
            <?php foreach ($A2B -> cardnumber_range as $value) { ?>
                <option value='<?php echo $value ?>'
                <?php if ($value == $cardnumberlength_list) echo "selected";
                ?>> <?php echo $value." ".gettext("Digits");?> </option>
            <?php } ?>
            </select>
        </span>
        </td>
    </tr>
    </form>
</table>
<?php
}

if ($form_action=='ask-edit') {
    echo get_login_button ($id);
}

$HD_Form -> create_form($form_action, $list) ;

// #### FOOTER SECTION
if (!($popup_select>=1)) require_once __DIR__ . "/templates/footer.php";
