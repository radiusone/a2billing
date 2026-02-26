<?php

use A2billing\Customer;
use A2billing\Table;

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

require_once __DIR__ . "/../../common/lib/customer.defines.php";

getpost_ifset(array('callback', 'called', 'calling'));

Customer::checkPageAccess(Customer::ACX_CALL_BACK);

$FG_DEBUG = 0;
$color_msg = 'red';


$status = (new Table("cc_card", ["status"]))->getValue(["username" => Customer::card()]);

if (!$status || ($status != "1" && $status != "8")) {
    Header("HTTP/1.0 401 Unauthorized");
    Header("Location: index.php?c=accessdenied");
    die();
}

if ($callback) {

    if (strlen($called)>1 && strlen($calling)>1 && is_numeric($called) && is_numeric($calling)) {

        $A2B -> cardnumber = Customer::card();

        if ($A2B -> callingcard_ivr_authenticate_light ($error_msg)) {

            $RateEngine = $A2B->rateEngine();
            // LOOKUP RATE : FIND A RATE FOR THIS DESTINATION

            $A2B -> agiconfig['accountcode'] = Customer::card();
            $A2B -> agiconfig['use_dnid'] = 1;
            $A2B -> agiconfig['say_timetocall'] = 0;
            $A2B -> extension = $A2B -> dnid = $A2B -> destination = $called;

            $resfindrate = $RateEngine->rate_engine_findrates($called, (int)$_SESSION["tariff"]);

            // IF FIND RATE
            if ($resfindrate!=0) {
                $res_all_calcultimeout = $RateEngine->rate_engine_all_calcultimeout($A2B->credit);
                if ($res_all_calcultimeout) {

                    // MAKE THE CALL
                    if ($RateEngine -> ratecard_obj[0]["rt_id_trunk"]!='-1') {
                        $usetrunk_prefix = "rt";
                        $RateEngine -> usedtrunk = $RateEngine -> ratecard_obj[0]["rt_id_trunk"];
                    } else {
                        $RateEngine -> usedtrunk = $RateEngine -> ratecard_obj[0]["tp_id_trunk"];
                        $usetrunk_prefix = "tp";
                    }

                    $prefix			= $RateEngine -> ratecard_obj[0][$usetrunk_prefix . "_trunkprefix"];
                    $tech 			= $RateEngine -> ratecard_obj[0][$usetrunk_prefix . "_providertech"];
                    $ipaddress 		= $RateEngine -> ratecard_obj[0][$usetrunk_prefix . "_providerip"];
                    $removeprefix 	= $RateEngine -> ratecard_obj[0][$usetrunk_prefix . "_removeprefix"];
                    $timeout		= $RateEngine -> ratecard_obj[0]['timeout'];
                    $failover_trunk	= $RateEngine -> ratecard_obj[0][$usetrunk_prefix . "_failover_trunk"];
                    $addparameter	= $RateEngine -> ratecard_obj[0][$usetrunk_prefix . "_addparameter_trunk"];

                    $destination = $called;
                    if (strncmp($destination, $removeprefix, strlen($removeprefix)) == 0) {
                        $destination= substr($destination, strlen($removeprefix));
                    }

                    $pos_dialingnumber = strpos($ipaddress, '%dialingnumber%' );
                    $ipaddress = str_replace("%cardnumber%", $A2B->cardnumber, $ipaddress);
                    $ipaddress = str_replace("%dialingnumber%", $prefix.$destination, $ipaddress);

                    $dialparams = '';
                    if ($pos_dialingnumber !== false) {
                        $dialstr = "$tech/$ipaddress".$dialparams;
                    } else {
                        if ($A2B->agiconfig['switchdialcommand'] == 1) {
                            $dialstr = "$tech/$prefix$destination@$ipaddress".$dialparams;
                        } else {
                            $dialstr = "$tech/$ipaddress/$prefix$destination".$dialparams;
                        }
                    }

                    //ADDITIONAL PARAMETER 			%dialingnumber%,	%cardnumber%
                    if (strlen($addparameter)>0) {
                        $addparameter = str_replace("%cardnumber%", $A2B->cardnumber, $addparameter);
                        $addparameter = str_replace("%dialingnumber%", $prefix.$destination, $addparameter);
                        $dialstr .= $addparameter;
                    }

                    $channel = $dialstr;
                    $exten = $calling;
                    $context = $A2B -> config["callback"]['context_callback'];
                    $id_server_group = $A2B -> config["callback"]['id_server_group'];
                    $priority =1;
                    $timeout = $A2B -> config["callback"]['timeout']*1000;
                    $application = '';
                    $callerid = $A2B -> config["callback"]['callerid'];
                    $account = Customer::card();

                    $uniqueid = generate_random_value("#####-XXXXXXX");
                    $status = 'PENDING';
                    $server_ip = 'localhost';
                    $num_attempt = 0;
                    $timeout = 30000;
                    $callback_time = "CURRENT_TIMESTAMP";

                    $variable = "CALLED=$called,CALLING=$calling,CBID=$uniqueid,LEG=".$A2B->cardnumber;

                    $res = (new Table("cc_callback_spool"))
                        ->addRow(compact(
                            "uniqueid", "status", "server_ip", "num_attempt", "channel", "exten", "context", "priority",
                            "variable", "id_server_group", "callback_time", "account", "callerid", "timeout"
                        ));

                    if (!$res) {
                        $error_msg= gettext("Cannot insert the callback request in the spool!");
                    } else {
                        $error_msg = gettext("Your callback request has been queued correctly!");
                        $color_msg = 'green';
                    }

                } else {
                    $error_msg = gettext("Error : You don t have enough credit to call you back!");
                }
            } else {
                $error_msg = gettext("Error : There is no route to call back your phonenumber!");
            }
        } else {
            // ERROR MESSAGE IS CONFIGURE BY THE callingcard_ivr_authenticate_light
        }
    } else {
        $error_msg = gettext("Error : You have to specify your phonenumber and the number you wish to call!");

    }
}
$customer = Customer::card();

require_once __DIR__ . "/templates/main.php";

echo create_help(gettext("Callback : Entre your phone number and the phone number you wish to call."));

?>
<br>
<center>
 <font class="fontstyle_007">
 <font face='Arial, Helvetica, sans-serif' size='2' color='<?php echo $color_msg; ?>'><b>
     <?php echo $error_msg ?>
 </b></font>
 <br><br>
  <?php echo gettext("You can initiate the callback by entering your phonenumber and the number you wish to call!");?>
  </font>
  </center>
   <table align="center" class="callback_maintable">
    <form name="theForm" action="" method="POST" >
    <INPUT type="hidden" name="callback" value="1">
    <tr class="bgcolor_001">
    <td align="left" valign="bottom">
            <br/>
            <font class="fontstyle_007"><?php echo gettext("Your Phone Number");?> :</font>
            <input class="form_input_text" name="called" value="<?php echo $called; ?>" size="30" maxlength="40" >
            <br/><br/>
            <font class="fontstyle_007"><?php echo gettext("The number you wish to call");?> :</font>
            <input class="form_input_text" name="calling" value="<?php echo $calling; ?>" size="30" maxlength="40">
            <br/><br/>
        </td>
        <td align="center" valign="middle">
        <input class="form_input_button"  value="[ <?php echo gettext("Click here to Place Call");?> ]" type="submit">
        </td>
    </tr>
    </form>
  </table>
  <br>
<br/><br><br/><br>
<?php

require_once __DIR__ . "/templates/footer.php";
