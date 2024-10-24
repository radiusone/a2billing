<?php

use A2billing\Agent;
use A2billing\Table;

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of A2Billing (http://www.a2billing.net/)
 *
 * A2Billing, Commercial Open Source Telecom Billing platform,
 * powered by Star2billing S.L. <http://www.star2billing.com/>
 *
 * @copyright   Copyright © 2004-2015 - Star2billing S.L.
 * @copyright   Copyright © 2022 RadiusOne Inc.
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

require_once "../../common/lib/agent.defines.php";
include './form_data/FG_var_card.inc';

if (! has_rights (Agent::ACX_CUSTOMER)) {
    Header ("HTTP/1.0 401 Unauthorized");
    Header ("Location: PP_error.php?c=accessdenied");
    die();
}

if ($form_action=="ask-edit") {
    if (! has_rights (Agent::ACX_EDIT_CUSTOMER)) {
        Header ("HTTP/1.0 401 Unauthorized");
        Header ("Location: PP_error.php?c=accessdenied");
        die();
    }
}

if ($form_action=="ask-delete") {
    if (! has_rights (Agent::ACX_DELETE_CUSTOMER)) {
        Header ("HTTP/1.0 401 Unauthorized");
        Header ("Location: PP_error.php?c=accessdenied");
        die();
    }
}

// SECURTY CHECK FOR AGENT
if ($form_action != "list" && isset($id)) {
    if (!empty($id)&& $id>0) {
        $table_agent_security = new Table("cc_card LEFT JOIN cc_card_group ON cc_card.id_group=cc_card_group.id ", " cc_card_group.id_agent");
        $clause_agent_security = "cc_card.id= ".$id;
        $result_security= $table_agent_security -> get_list ($HD_Form->DBHandle, $clause_agent_security);
        if ($result_security[0][0] != $_SESSION['agent_id']) {
            Header ("Location: A2B_entity_card.php?section=1");
            die();
        }
    }
}
$HD_Form -> init();

/********************************* BATCH UPDATE ***********************************/
getpost_ifset(array('popup_select', 'popup_formname', 'popup_fieldname', 'upd_inuse', 'upd_status', 'upd_language', 'upd_tariff', 'upd_credit', 'upd_credittype', 'upd_simultaccess', 'upd_currency', 'upd_typepaid', 'upd_creditlimit', 'upd_enableexpire', 'upd_expirationdate', 'upd_expiredays', 'upd_runservice', 'upd_runservice', 'batchupdate', 'check', 'type', 'mode', 'addcredit', 'cardnumber','description'));

// CHECK IF REQUEST OF BATCH UPDATE
if ($batchupdate == 1 && is_array($check)) {

    $HD_Form->prepare_list_subselection('list');

    $authorized_field = [
        "upd_inuse",
        "upd_status",
        "upd_language",
        "upd_simultaccess",
        "upd_currency",
        "upd_enableexpire",
        "upd_expirationdate",
        "upd_expiredays",
        "upd_runservice"
    ];

    // Array ( [upd_simultaccess] => on [upd_currency] => on )
    $i = 0;
    $update_sql = "UPDATE $HD_Form->FG_QUERY_TABLE_NAME SET";
    $update_params = [];
    foreach ($check as $ind_field => $ind_val) {
        if (!in_array($ind_field, $authorized_field)) {
            continue;
        }
        $myfield = (new Table())->quote_identifier(substr($ind_field,4));
        if ($i !== 0) {
            $update_sql .= ',';
        }
        $val = $$ind_field;

        // Standard update mode
        if (($mode[$ind_field] ?? 1) == 1) {
            $update_sql .= " $myfield = ?";
            if (!isset($type[$ind_field])) {
                $update_params[] = $val;
            } else {
                $update_params[] = $type[$ind_field];
            }
            // Mode 2 - Equal - Add - Subtract
        } elseif ($mode[$ind_field] == 2) {
            if (($type[$ind_field] ?? 1) == 1) {
                $update_sql .= " $myfield = ?";
            } elseif ($type[$ind_field] == 2) {
                $update_sql .= " $myfield = $myfield + ?";
            } elseif ($type[$ind_field] == 3) {
                $update_sql .= " $myfield = $myfield - ?";
            }
            $update_params[] = $val;
        }
        $i++;
    }

    $where = (new Table())->processWhereClauseArray($HD_Form->list_query_conditions, $update_params) ?: "1=1";
    $update_sql .= "WHERE $where";

    if (! $res = $HD_Form -> DBHandle -> Execute($update_sql, $update_params)) {
        $update_msg = '<center><font color="red"><b>'.gettext('Could not perform the batch update!').'</b></font></center>';
    } else {
        $update_msg = '<center><font color="green"><b>'.gettext('The batch update has been successfully perform!').'</b></font></center>';
    }

}
/********************************* END BATCH UPDATE ***********************************/

if (($form_action == "addcredit") && ($addcredit > 0) && ($id > 0 || $cardnumber > 0)) {

    $instance_table = new Table("cc_card", "username, id");

    if ($cardnumber>0) {
        /* CHECK IF THE CARDNUMBER IS ON THE DATABASE */
        $FG_TABLE_CLAUSE_card = "username='".$cardnumber."'";
        $list_tariff_card = $instance_table -> get_list ($HD_Form->DBHandle, $FG_TABLE_CLAUSE_card);
        if ($cardnumber == $list_tariff_card[0][0]) $id = $list_tariff_card[0][1];
    }

    if ($id > 0) {

        $instance_check_card_agent = new Table("cc_card LEFT JOIN cc_card_group ON cc_card.id_group=cc_card_group.id", " cc_card_group.id_agent");
        $FG_TABLE_CLAUSE_check = "cc_card.id= ".$id;
        $list_check= $instance_check_card_agent -> get_list ($HD_Form->DBHandle, $FG_TABLE_CLAUSE_check);
        if ($list_check[0][0] == $_SESSION['agent_id']) {

            //check if enought credit
            $instance_table_agent = new Table("cc_agent", "credit, currency");
            $FG_TABLE_CLAUSE_AGENT = "id = ".$_SESSION['agent_id'] ;
            $agent_info = $instance_table_agent -> get_list ($HD_Form->DBHandle, $FG_TABLE_CLAUSE_AGENT);
            $credit_agent = $agent_info[0][0];
            if ($credit_agent >= $addcredit) {
               //Substract credit for agent
                $param_update_agent = "credit = credit - '".$addcredit."'";
                $instance_table_agent -> Update_table ($HD_Form -> DBHandle, $param_update_agent, $FG_TABLE_CLAUSE_AGENT, $func_table = null);

               // Add credit to Customer
                $param_update .= "credit = credit + '".$addcredit."'";

                $FG_EDITION_CLAUSE = " id='$id'" ; // AND id_agent=".$_SESSION['agent_id'];

                $instance_table = new Table("cc_card", "username, id");
                $instance_table -> Update_table ($HD_Form -> DBHandle, $param_update, $FG_EDITION_CLAUSE, $func_table = null);

                $update_msg ='<b><font color="green">'.gettext("Refill executed ").'!</font></b>';
                $id_agent = $_SESSION['agent_id'];
                $field_insert = "date, credit, card_id, description, refill_type,agent_id";
                $value_insert = "now(), '$addcredit', '$id','$description','3','$id_agent'";
                $instance_sub_table = new Table("cc_logrefill", $field_insert);
                $id_refill = $instance_sub_table -> Add_table ($HD_Form -> DBHandle, $value_insert, null, null,'id');

                $agent_table = new Table("cc_agent", "commission");

                $agent_clause = "id = ".$id_agent;
                $result_agent= $agent_table -> get_list($HD_Form->DBHandle, $agent_clause);

                if (is_array($result_agent) && is_numeric($result_agent[0]['commission']) && $result_agent[0]['commission']>0) {
                    $field_insert = "id_payment, id_card, amount,description,id_agent";
                    $commission = a2b_round($addcredit * ($result_agent[0]['commission']/100));
                    $description_commission = gettext("GENERATED COMMISSION OF AN CUSTOMER REFILLED BY AN AGENT!");
                    $description_commission.= "\nID CARD : ".$id;
                    $description_commission.= "\nID REFILL : ".$id_refill;
                    $description_commission.= "\REFILL AMOUNT: ".$addcredit;
                    $description_commission.= "\nCOMMISSION APPLIED: ".$result_agent[0]['commission'];
                    $value_insert = "'-1', '$id', '$commission','$description_commission','$id_agent'";
                    $commission_table = new Table("cc_agent_commission", $field_insert);
                    $id_commission = $commission_table -> Add_table ($HD_Form -> DBHandle, $value_insert, null, null,"id");
                    $table_agent = new Table('cc_agent');
                    $param_update_agent = "com_balance = com_balance + '".$commission."'";
                    $clause_update_agent = " id='".$id_agent."'";
                    $table_agent -> Update_table ($HD_Form -> DBHandle, $param_update_agent, $clause_update_agent, $func_table = null);
                }


                if (!$id_refill) {
                    $update_msg ="<b>".$instance_sub_table -> errstr."</b>";
                }

            } else {

                $currencies_list = get_currencies();

                if (!isset($currencies_list[strtoupper($agent_info [0][1])]["value"]) || !is_numeric($currencies_list[strtoupper($agent_info [0][1])]["value"]))
                    $mycur = 1;
                else
                    $mycur = $currencies_list[strtoupper($agent_info [0][1])]["value"];

                $credit_cur = $agent_info[0][0] / $mycur;
                $credit_cur = round($credit_cur,3);

                $update_msg ='<b> <font color="red">'.gettext("You don't have enough credit to do this refill. You have ").$credit_cur.' '.$agent_info[0][1].' </font></b>';
            }

        } else {
                $update_msg ='<b><font color="red">'.gettext("Impossible to refill this card ").'</font></b>';
        }
    }
}

if ($form_action == "addcredit")
    $form_action='list';

if (!isset($form_action))  $form_action="list"; //ask-add
if (!isset($action)) $action = $form_action;


$list = $HD_Form -> perform_action($form_action);


// #### HEADER SECTION
$smarty->display('main.tpl');



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

function sendValue(selvalue)
{
    window.opener.document.<?php echo $popup_formname ?>.<?php echo $popup_fieldname ?>.value = selvalue;
    window.close();
}
// End -->
</script>
<?php
}


// #### HELP SECTION
if ($form_action=='list' && !($popup_select>=1)) {
    echo $CC_help_list_customer;

?>
<script language="JavaScript" src="javascript/card.js"></script>


<div class="toggle_hide2show">
<center><a href="#" target="_self" class="toggle_menu"><img class="toggle_hide2show" src="<?php echo KICON_PATH; ?>/toggle_hide2show.png" onmouseover="this.style.cursor='hand';" HEIGHT="16"> <font class="fontstyle_002"><?php echo gettext("REFILL");?> </font></a></center>
    <div class="tohide" style="display:none;">
    <form NAME="theForm">
       <table width="90%" border="0" align="center">
        <tr>
           <td align="left" width="5%"><img src="<?php echo KICON_PATH; ?>/pipe.gif">
           </td>
          <td align="left" width="35%" class="bgcolor_001">
               <table>
            <tr><td align="center">
               <?php echo gettext("CARD ID");?>	 :<input class="form_input_text" name="choose_list" onfocus="clear_textbox2();" size="18" maxlength="16" value="enter ID Card">
                    <a href="A2B_entity_card.php" data-uri-extra="&nodisplay=1" class="badge bg-primary popup_trigger" aria-label="open a popup to select an item">&gt;</a>
                       <?php echo gettext("or");?>
            </td></tr>
            <tr><td align="center">
                &nbsp; <?php echo gettext("CARDNUMBER");?>&nbsp;:<input class="form_input_text" name="cardnumber" onfocus="clear_textbox();" size="18" maxlength="16" value="enter cardnumber">
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
                TYPE="button" VALUE="<?php echo gettext("ADD CREDIT");?>" onClick="openURL('<?php echo filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)?>?form_action=addcredit&current_page=<?php echo $current_page?>&order=<?php echo $order?>&sens=<?php echo $sens?>&id=')">

                    </td>
                </tr>
            </table>

        </td>
        </tr>

      </table>
      </form>
    </div>
</div>
<div class="toggle_hide2show">
<center><a href="#" target="_self" class="toggle_menu"><img class="toggle_hide2show" src="<?php echo KICON_PATH; ?>/toggle_hide2show.png" onmouseover="this.style.cursor='hand';" HEIGHT="16"> <font class="fontstyle_002"><?php echo gettext("SEARCH CARDS");?> </font></a><?php if (!empty($_SESSION['entity_card_selection'])) { ?>&nbsp;(<font style="color:#EE6564;" > <?php echo gettext("search activated"); ?> </font> ) <?php } ?> </center>
    <div class="tohide" style="display:none;">

<?php
// #### CREATE SEARCH FORM
if ($form_action == "list") {
    $HD_Form -> create_search_form();
}
?>

    </div>
</div>

<?php

/********************************* BATCH UPDATE ***********************************/
if ($form_action == "list" && (!($popup_select>=1))) {

    $FG_TABLE_CLAUSE = "";

?>
<!-- ** ** ** ** ** Part for the Update ** ** ** ** ** -->
<div class="toggle_hide2show">
<center><a href="#" target="_self" class="toggle_menu"><img class="toggle_hide2show" src="<?php echo KICON_PATH; ?>/toggle_hide2show.png" onmouseover="this.style.cursor='hand';" HEIGHT="16"> <font class="fontstyle_002"><?php echo gettext("BATCH UPDATE");?> </font></a></center>
    <div class="tohide" style="display:none;">

<center>
<b>&nbsp;<?php echo $HD_Form -> FG_LIST_VIEW_ROW_COUNT ?> <?php echo gettext("cards selected!"); ?>&nbsp;<?php echo gettext("Use the options below to batch update the selected cards.");?></b>
       <table align="center" border="0" width="65%"  cellspacing="1" cellpadding="2">
        <tbody>
        <form name="updateForm" action="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)?>" method="post">
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
                <input type="radio" NAME="type[upd_runservice]" value="1" <?php if ((!isset($type[upd_runservice]))|| ($type[upd_runservice]=='1') ) {?>checked<?php }?>>
                <?php echo gettext("Yes");?> <input type="radio" NAME="type[upd_runservice]" value="0" <?php if ($type[upd_runservice]=='0') {?>checked<?php }?>><?php echo gettext("No");?>
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

}elseif (!($popup_select>=1)) echo $CC_help_create_customer;


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
$HD_Form->setup_export();

// #### FOOTER SECTION
if (!($popup_select>=1)) $smarty->display('footer.tpl');
