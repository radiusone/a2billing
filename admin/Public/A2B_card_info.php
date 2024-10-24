<?php

use A2billing\Admin;

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

$menu_section = 1;
require_once "../../common/lib/admin.defines.php";

Admin::checkPageAccess(Admin::ACX_CUSTOMER);

getpost_ifset(array('id'));

if (empty($id)) {
    header("Location: A2B_entity_card.php");
}

$DBHandle  = DbConnect();

$card = $DBHandle->GetRow("SELECT * FROM cc_card WHERE id = ?", [$id]);

if (empty($card)) {
    header("Location: A2B_entity_card.php");
}

// #### HEADER SECTION
$smarty->display('main.tpl');

echo $CC_help_info_customer;

echo get_login_button ($id);

?>

<table width="95%" >
    <tr>
        <td valign="top" width="50%" >
            <table width="100%" class="editform_table1">
               <tr>
                       <th colspan="2" background="../Public/templates/default/images/background_cells.gif">
                           <?php echo gettext("ACCOUNT INFO") ?>
                       </th>
               </tr>
               <tr height="20px">
                    <td  class="form_head">
                        <?php echo gettext("STATUS") ?> :
                    </td>
                    <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
                        <?php
                        $list_typepaid = getPaidTypeList();
                        echo $list_typepaid[$card['typepaid']][0];?>
                    </td>
               </tr>
               <tr height="20px">
                    <td  class="form_head">
                        <?php echo gettext("ACCOUNT NUMBER") ?> :
                    </td>
                    <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
                        <?php echo $card['username']?>
                    </td>
               </tr>
               <tr height="20px">
                    <td  class="form_head">
                        <?php echo gettext("SERIAL NUMBER") ?> :
                    </td>
                    <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
                        <?php echo str_pad($card['serial'], $A2B->config["webui"]['card_serial_length'] , "0", STR_PAD_LEFT); ?>
                    </td>
               </tr>
               <tr height="20px">
                    <td  class="form_head">
                        <?php echo gettext("WEB ALIAS") ?> :
                    </td>
                    <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
                        <?php echo $card['useralias']?>
                    </td>
               </tr>
               <tr height="20px">
                    <td  class="form_head">
                        <?php echo gettext("WEB PASSWORD") ?> :
                    </td>
                    <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
                        <?php echo $card['uipass']?>
                    </td>
                </tr>
                   <tr height="20px">
                    <td  class="form_head">
                        <?php echo gettext("LANGUAGE") ?> :
                    </td>
                    <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
                        <?php echo $card['language']?>
                    </td>
                </tr>
                   <tr height="20px">
                    <td  class="form_head">
                        <?php echo gettext("STATUS") ?> :
                    </td>
                    <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
                        <?php
                        $list_status = getCardStatus_List();
                        echo $list_status[$card['status']][0];?>
                    </td>
                </tr>
                   <tr height="20px">
                    <td  class="form_head">
                        <?php echo gettext("CREATION DATE") ?> :
                    </td>
                    <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
                        <?php echo $card['creationdate']?>
                    </td>
                </tr>
                   <tr height="20px">
                    <td  class="form_head">
                        <?php echo gettext("EXPIRATION DATE") ?> :
                    </td>
                    <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
                        <?php echo $card['expirationdate']?>
                    </td>
                </tr>
                   <tr height="20px">
                    <td  class="form_head">
                        <?php echo gettext("FIRST USE DATE") ?> :
                    </td>
                    <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
                        <?php echo $card['firstusedate']?>
                    </td>
                </tr>
                   <tr height="20px">
                    <td  class="form_head">
                        <?php echo gettext("LAST USE DATE") ?> :
                    </td>
                    <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
                        <?php echo $card['lastuse']?>
                    </td>
                </tr>
                  <tr height="20px">
                    <td  class="form_head">
                        <?php echo gettext("CALLBACK") ?> :
                    </td>
                    <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
                        <?php echo $card['callback']?>
                    </td>
                </tr>
                  <tr height="20px">
                    <td  class="form_head">
                        <?php echo gettext("LOCK") ?> :
                    </td>
                    <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
                        <?php echo ($card['block'] ? gettext("LOCK") : gettext("UNLOCK")) ?>
                    </td>
                </tr>
                  <tr height="20px">
                    <td  class="form_head">
                        <?php echo gettext("LOCK PIN") ?> :
                    </td>
                    <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
                        <?php echo $card['lock_pin']?>
                    </td>
                </tr>
                  <tr height="20px">
                    <td  class="form_head">
                        <?php echo gettext("LOCK DATE") ?> :
                    </td>
                    <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
                        <?php echo $card['lock_date']?>
                    </td>
                </tr>
             </table>
        </td>

        <td valign="top" width="50%" >
            <table width="100%" class="editform_table1"  >
                <tr>
                    <th colspan="2" background="../Public/templates/default/images/background_cells.gif">
                         <?php echo gettext("CUSTOMER INFO") ?>
                     </th>

                </tr>
                <tr height="20px">
                    <td  class="form_head">
                        <?php echo gettext("LAST NAME") ?> :
                    </td>
                    <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
                        &nbsp;<?php echo $card['lastname']?>
                    </td>

                </tr>
                <tr height="20px">
                    <td  class="form_head">
                        <?php echo gettext("FIRST NAME") ?> :
                    </td>
                    <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
                        &nbsp;<?php echo $card['firstname']?>
                    </td>

                </tr>

                <tr height="20px">
                    <td  class="form_head">
                        <?php echo gettext("ADDRESS") ?> :
                    </td>
                    <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
                        &nbsp;<?php echo $card['address']?>
                    </td>

                </tr>

                <tr height="20px">
                    <td  class="form_head">
                        <?php echo gettext("ZIP CODE") ?> :
                    </td>
                    <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
                        &nbsp;<?php echo $card['zipcode']?>
                    </td>
                </tr>

                <tr  height="20px">
                    <td  class="form_head">
                        <?php echo gettext("CITY") ?> :
                    </td>
                    <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
                        &nbsp;<?php echo $card['city']?>
                    </td>

                </tr>

                <tr  height="20px">
                    <td  class="form_head">
                        <?php echo gettext("STATE") ?> :
                    </td>
                    <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
                        <?php echo $card['state']?>
                    </td>

                </tr>

                <tr  height="20px">
                    <td  class="form_head">
                        <?php echo gettext("COUNTRY") ?> :
                    </td>
                    <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
                        <?php echo $card['country']?>
                    </td>

                </tr>
                <tr  height="20px">
                    <td  class="form_head">
                        <?php echo gettext("EMAIL") ?> :
                    </td>
                    <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
                        <?php echo $card['email']?>
                    </td>

                </tr>
                <tr  height="20px">
                    <td  class="form_head">
                        <?php echo gettext("PHONE") ?> :
                    </td>
                    <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
                        <?php echo $card['phone']?>
                    </td>
                </tr>
                <tr  height="20px">
                    <td  class="form_head">
                        <?php echo gettext("FAX") ?> :
                    </td>
                    <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
                        <?php echo $card['fax']?>
                    </td>
                </tr>
            </table>
        </td>

    </tr>
</table>

<br/>

<table width="95%">
    <tr>

        <td valign="top" width="50%" >
            <table width="100%" class="editform_table1">
               <tr>
                       <th colspan="2" background="../Public/templates/default/images/background_cells.gif">
                           <?php echo gettext("ACCOUNT STATUS") ?>
                       </th>
               </tr>
               <tr height="20px">
                    <td  class="form_head">
                        <?php echo gettext("BALANCE") ?> :
                    </td>
                    <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
                        &nbsp;<?php echo $card['credit']?>
                    </td>
                </tr>
                <tr height="20px">
                    <td  class="form_head">
                        <?php echo gettext("CURRENCY") ?> :
                    </td>
                    <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
                        <?php echo $card['currency']?>
                    </td>
                  </tr>
               <tr height="20px">
                    <td  class="form_head">
                        <?php echo gettext("CREDIT LIMIT") ?> :
                    </td>
                    <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
                        <?php echo $card['creditlimit']?>
                    </td>
                </tr>
                   <tr height="20px">
                    <td  class="form_head">
                        <?php echo gettext("AUTOREFILL") ?> :
                    </td>
                    <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
                        <?php echo $card['autorefill']?>
                    </td>
                </tr>
                   <tr height="20px">
                    <td  class="form_head">
                        <?php echo gettext("INVOICE DAY") ?> :
                    </td>
                    <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
                        <?php echo $card['invoiceday']?>
                    </td>
                </tr>
             </table>
        </td>

        <td valign="top" width="50%" >
            <table width="100%" class="editform_table1"  >
                <tr>
                    <th colspan="2" background="../Public/templates/default/images/background_cells.gif">
                         <?php echo gettext("COMPANY INFO") ?>
                     </th>

                </tr>
                <tr height="20px">
                    <td  class="form_head">
                        <?php echo gettext("COMPANY NAME") ?> :
                    </td>
                    <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
                        &nbsp;<?php echo $card['company_name']?>
                    </td>
                </tr>
                <tr height="20px">
                    <td  class="form_head">
                        <?php echo gettext("COMPANY WEBSITE") ?> :
                    </td>
                    <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
                        &nbsp;<?php echo $card['company_website']?>
                    </td>

                </tr>

                <tr height="20px">
                    <td  class="form_head">
                        <?php echo gettext("VAT REGISTRATION NUMBER") ?> :
                    </td>
                    <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
                        &nbsp;<?php echo $card['vat_rn']?>
                    </td>

                </tr>

                <tr height="20px">
                    <td  class="form_head">
                        <?php echo gettext("TRAFFIC PER MONTH") ?> :
                    </td>
                    <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
                        &nbsp;<?php echo $card['traffic']?>
                    </td>
                </tr>

                <tr  height="20px">
                    <td  class="form_head">
                        <?php echo gettext("TARGET TRAFIC") ?> :
                    </td>
                    <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%">
                        &nbsp;<?php echo $card['traffic_target']?>
                    </td>

                </tr>
            </table>
        </td>
    </tr>
</table>
<br/>
<table width="95%">
    <tr>
     <td valign="top" width="50%" >
        <?php
        $callerid_result = $DBHandle->GetAll("SELECT * FROM cc_callerid WHERE id_cc_card = ?", [$id]);
        if ($callerid_result) {
        ?>
          <table width="100%" class="editform_table1">
        <tr>
           <th colspan="2" background="../Public/templates/default/images/background_cells.gif">
                    <?php echo gettext("CALLER-ID LIST ") ?>
           </th>
        </tr>
                <tr class="form_head">
                   <td class="tableBody"  width="15%" align="center" style="padding: 2px;">
                                <?php echo gettext("CID"); ?>
                   </td>
                   <td class="tableBody"  width="20%" align="center" style="padding: 2px;">
                            <?php echo gettext("ACTIVATED"); ?>
                   </td>
               </tr>
           <?php
            $i=0;
            foreach ($callerid_result as $callerid) {
                if($i%2==0) $bg="#fcfbfb";
                else  $bg="#f2f2ee";
           ?>
            <tr bgcolor="<?php echo $bg; ?>"  >
                <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%" align="center">
                  <?php echo $callerid['cid']; ?>
                </td>

                <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%" align="center">
                  <?php echo ($callerid['activated']=="t"?"Active":"Inactive"); ?>
                </td>
            </tr>
           <?php
           $i++;
           }
           ?>
        </table>
        <?php
          }
        ?>
        </td>

        <td valign="top" width="50%" >
        <?php
        $speeddial_result = $DBHandle->GetAll("SELECT * FROM cc_speeddial WHERE id_cc_card = ?", [$id]);
        if ($speeddial_result) {
        ?>
        <table width="100%" class="editform_table1">
           <tr>
            <th colspan="3" background="../Public/templates/default/images/background_cells.gif">
                       <?php echo gettext("SPEED-DIAL LIST ") ?>
            </th>
           </tr>
                      <tr class="form_head">
                        <td class="tableBody"  width="15%" align="center" style="padding: 2px;">
                                 <?php echo gettext("PHONE"); ?>
                        </td>
                        <td class="tableBody"  width="20%" align="center" style="padding: 2px;">
                                <?php echo gettext("NAME"); ?>
                        </td>
                        <td class="tableBody"  width="15%" align="center" style="padding: 2px;">
                                <?php echo gettext("SPEEDDIAL"); ?>
                        </td>
                   </tr>
                   <?php
                $i=0;
                foreach ($speeddial_result as $speeddial) {
                    if($i%2==0) $bg="#fcfbfb";
                    else  $bg="#f2f2ee";
                ?>
                    <tr bgcolor="<?php echo $bg; ?>"  >
                        <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%" align="center">
                                  <?php echo $speeddial['phone']; ?>
                        </td>
                        <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%" align="center">
                                  <?php echo $speeddial['name']; ?>
                        </td>
                        <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%"  align="center">
                                  <?php echo $speeddial['speeddial']; ?>
                        </td>
                    </tr>
                <?php
                $i++;
                }
                ?>
        </table>
            <?php
              }
            ?>
        </td>
    </tr>
</table>
<br/>
<table width="95%">
    <tr>
     <td valign="top" width="50%" >
        <?php
        $sip_buddies_result = $DBHandle->GetAll("SELECT * FROM cc_sip_buddies WHERE id_cc_card = ?", [$id]);
        if ($sip_buddies_result) {
        ?>
        <table width="100%" class="editform_table1">
           <tr>
               <th colspan="2" background="../Public/templates/default/images/background_cells.gif">
                       <?php echo gettext("SIP-CONFIG") ?>
               </th>
           </tr>
                      <tr class="form_head">
                    <td class="tableBody"  width="15%" align="center" style="padding: 2px;">
                             <?php echo gettext("USERNAME"); ?>
                    </td>
                    <td class="tableBody"  width="20%" align="center" style="padding: 2px;">
                            <?php echo gettext("SECRET"); ?>
                    </td>
                    </tr>
                   <?php
                $i=0;
                foreach ($sip_buddies_result as $sip_buddies) {
                    if($i%2==0) $bg="#fcfbfb";
                    else  $bg="#f2f2ee";
                ?>
                    <tr bgcolor="<?php echo $bg; ?>"  >
                        <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%" align="center">
                                  <?php echo $sip_buddies['username']; ?>
                        </td>
                        <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%"  align="center">
                                  <?php echo $sip_buddies['secret']; ?>
                        </td>
                    </tr>
                <?php
                $i++;
                }
                ?>
        </table>
            <?php
              }
            ?>
        </td>

        <td valign="top" width="50%" >
        <?php
        $iax_buddies_result = $DBHandle->GetAll("SELECT * FROM cc_iax_buddies WHERE id_cc_card = ?", [$id]);
        if ($iax_buddies_result) {
        ?>
        <table width="100%" class="editform_table1">
           <tr>
            <th colspan="2" background="../Public/templates/default/images/background_cells.gif">
                           <?php echo gettext("IAX-CONFIG") ?>
            </th>
               </tr>
               <tr class="form_head">
                    <td class="tableBody"  width="15%" align="center" style="padding: 2px;">
                                 <?php echo gettext("USERNAME"); ?>
                    </td>
                    <td class="tableBody"  width="20%" align="center" style="padding: 2px;">
                                <?php echo gettext("SECRET"); ?>
                    </td>
               </tr>
               <?php
                $i=0;
                foreach ($iax_buddies_result as $iax_buddies) {
                    if($i%2==0) $bg="#fcfbfb";
                    else  $bg="#f2f2ee";
               ?>
                    <tr bgcolor="<?php echo $bg; ?>"  >
                        <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%" align="center">
                                      <?php echo $iax_buddies['username']; ?>
                        </td>
                        <td class="tableBodyRight"  background="../Public/templates/default/images/background_cells.gif" width="70%"  align="center">
                                      <?php echo $iax_buddies['secret']; ?>
                        </td>
                    </tr>
                <?php
                $i++;
                }
                ?>
        </table>
            <?php
              }
            ?>
        </td>
    </tr>
</table>

<br/>

<div style="width : 90%; text-align : right; margin-left:auto;margin-right:auto;" >
     <a class="cssbutton_big"  href="A2B_entity_card.php">
        <img src="<?php echo Images_Path_Main;?>/icon_arrow_orange.gif"/>
        <?php echo gettext("CUSTOMERS LIST"); ?>
    </a>
</div>
<br/>
<?php

// We need to list all required columns as both tables have an 'id' column
$subscription_result = $DBHandle->GetAll(
    "SELECT cc_card_subscription.id, id_cc_card, cc_card_subscription.startdate, product_name, fee FROM cc_card_subscription,cc_subscription_service WHERE cc_card_subscription.id_subscription_fee = cc_subscription_service.id AND id_cc_card = ? ORDER BY startdate DESC LIMIT 10",
    [$id]
);
if ($subscription_result) {
?>
<table class="toppage_maintable">
    <tr>
        <td height="20" align="center">
            <font class="toppage_maintable_text">
              <?php echo gettext("Current Subscriptions"); ?>		  <br/>
            </font>
        </td>
    </tr>
</table>

<table width="95%"  cellspacing="2" cellpadding="2" border="0">

    <tr class="form_head">
        <td class="tableBody"  width="15%" align="center" style="padding: 2px;">
         <?php echo gettext("ID"); ?>
        </td>
        <td class="tableBody"  width="20%" align="center" style="padding: 2px;">
        <?php echo gettext("DATE"); ?>
        </td>
        <td class="tableBody"  width="38%" align="center" style="padding: 2px;">
        <?php echo gettext("SUBSCRIPTION"); ?>
        </td>
        <td class="tableBody"  width="10%" align="center" style="padding: 2px;">
        <?php echo gettext("FEE"); ?>
        </td>
        <td class="tableBody"  width="10%" align="center" style="padding: 2px;">
         <?php echo gettext("LINKS"); ?>
        </td>

    </tr><?php
        $i=0;
        foreach ($subscription_result as $subscription) {
            if($i%2==0) $bg="#fcfbfb";
            else  $bg="#f2f2ee";
    ?>
            <tr bgcolor="<?php echo $bg; ?>"  >
                <td class="tableBody" align="center">
                  <?php echo $subscription['id']; ?>
                </td>

                <td class="tableBody" align="center">
                  <?php echo $subscription['startdate']; ?>
                </td>

                <td class="tableBody"  align="center">
                  <?php echo $subscription['product_name']; ?>
                </td>

                <td class="tableBody"  align="center">
                  <?php echo $subscription['fee']; ?>
                </td>
                <td class="tableBody"  align="center">
                    <?php if (!empty($subscription['id'])) { ?>
                    <a href="A2B_entity_subscriber.php?form_action=ask-edit&id=<?php echo $subscription['id']?>"> <img src="<?php echo Images_Path."/link.png"?>" border="0" title="<?php echo gettext("Link to subscription")?>" alt="<?php echo  gettext("Link to subscription")?>"></a>
                    <a href="A2B_entity_subscriber.php?form_action=ask-delete&id=<?php echo $subscription['id']?>"> <img src="<?php echo Images_Path."/delete.png"?>" border="0" title="<?php echo gettext("Delete subscription")?>" alt="<?php echo  gettext("Delete subscription")?>"></a>
                    <?php } ?>
                </td>

            </tr>
        <?php
        $i++;
        }
        ?>
</table>
<?php
}
$payment_result = $DBHandle->GetAll("SELECT * FROM cc_logpayment WHERE card_id = ? ORDER BY date DESC LIMIT 10", [$id]);
if ($payment_result) {
?>
<table class="toppage_maintable">
    <tr>
        <td height="20" align="center">
            <font class="toppage_maintable_text">
              <?php echo gettext("Recent Payments"); ?>		  <br/>
            </font>
        </td>
    </tr>
</table>

<table width="95%"  cellspacing="2" cellpadding="2" border="0">

    <tr class="form_head">
        <td class="tableBody"  width="15%" align="center" style="padding: 2px;">
         <?php echo gettext("ID"); ?>
        </td>
        <td class="tableBody"  width="20%" align="center" style="padding: 2px;">
        <?php echo gettext("PAYMENT DATE"); ?>
        </td>
        <td class="tableBody"  width="15%" align="center" style="padding: 2px;">
        <?php echo gettext("PAYMENT AMOUNT"); ?>
        </td>
        <td class="tableBody"  width="30%" align="center" style="padding: 2px;">
        <?php echo gettext("DESCRIPTION"); ?>
        </td>
        <td class="tableBody"  width="15%" align="center" style="padding: 2px;">
        <?php echo gettext("ID REFILL"); ?>
        </td>

    </tr>

    <?php
        $i=0;
        foreach ($payment_result as $payment) {
            if($i%2==0) $bg="#fcfbfb";
            else  $bg="#f2f2ee";
    ?>
            <tr bgcolor="<?php echo $bg; ?>"  >
                <td class="tableBody" align="center">
                  <?php echo $payment['id']; ?>
                </td>

                <td class="tableBody" align="center">
                  <?php echo $payment['date']; ?>
                </td>

                <td class="tableBody"  align="center">
                  <?php echo $payment['payment']; ?>
                </td>
                <td class="tableBody"  align="center">
                  <?php echo $payment['description']; ?>
                </td>
                <td class="tableBody"  align="center">
                  <?php echo $payment['id_logrefill']; ?>
                </td>
            </tr>
        <?php
        $i++;
        }
        ?>
</table>
<?php
}
$refill_result = $DBHandle->GetAll("SELECT * FROM cc_logrefill WHERE card_id = ? ORDER BY date DESC LIMIT 10", [$id]);
if ($refill_result) {
?>
<table class="toppage_maintable">
    <tr>
        <td height="20" align="center">
            <font class="toppage_maintable_text">
             <?php echo gettext("Recent Refills"); ?>			  <br/>
            </font>
        </td>
    </tr>
</table>

<table width="95%"  cellspacing="2" cellpadding="2" border="0">

    <tr class="form_head">
        <td class="tableBody"  width="15%" align="center" style="padding: 2px;">
         <?php echo gettext("ID"); ?>
        </td>
        <td class="tableBody"  width="20%" align="center" style="padding: 2px;">
         <?php echo gettext("REFILL DATE"); ?>
        </td>
        <td class="tableBody"  width="15%" align="center" style="padding: 2px;">
         <?php echo gettext("REFILL AMOUNT"); ?>
        </td>
        <td class="tableBody"  width="40%" align="center" style="padding: 2px;">
         <?php echo gettext("DESCRIPTION"); ?>
        </td>

    </tr>

    <?php
        $i=0;
        foreach ($refill_result as $refill) {
            if($i%2==0) $bg="#fcfbfb";
            else  $bg="#f2f2ee";
    ?>
            <tr bgcolor="<?php echo $bg; ?>"  >
                <td class="tableBody" align="center">
                  <?php echo $refill['id']; ?>
                </td>

                <td class="tableBody" align="center">
                  <?php echo $refill['date']; ?>
                </td>

                <td class="tableBody"  align="center">
                  <?php echo $refill['credit']; ?>
                </td>
                <td class="tableBody"  align="center">
                  <?php echo $refill['description']; ?>
                </td>

            </tr>
        <?php
        $i++;
        }
        ?>
</table>
<?php
}
$did_destination_result = $DBHandle->GetAll(
    "SELECT * FROM cc_did_destination, cc_did WHERE cc_did_destination.id_cc_did = cc_did.id and cc_did_destination.id_cc_card = ?",
    [$id]
);
if ($did_destination_result) {
?>
<table class="toppage_maintable">
    <tr>
        <td height="20" align="center">
            <font class="toppage_maintable_text">
              <?php echo gettext("DIDs & DID Destination"); ?>		  <br/>
            </font>
        </td>
    </tr>
</table>

<table width="95%"  cellspacing="2" cellpadding="2" border="0">

    <tr class="form_head">
        <td class="tableBody"  width="15%" align="center" style="padding: 2px;">
                 <?php echo gettext("DID"); ?>
        </td>
        <td class="tableBody"  width="20%" align="center" style="padding: 2px;">
                <?php echo gettext("DESTINATION"); ?>
        </td>
                <td class="tableBody"  width="15%" align="center" style="padding: 2px;">
                 <?php echo gettext("ACTIVATED"); ?>
        </td>
            <td class="tableBody"  width="15%" align="center" style="padding: 2px;">
                 <?php echo gettext("VOIP"); ?>
        </td>
    </tr>

    <?php
        $i=0;
        foreach ($did_destination_result as $did_destination) {
            if($i%2==0) $bg="#fcfbfb";
            else  $bg="#f2f2ee";
    ?>
    <tr bgcolor="<?php echo $bg; ?>"  >
        <td class="tableBody" align="center">
                  <?php echo $did_destination['did']; ?>
        </td>
                <td class="tableBody" align="center">
                  <?php echo $did_destination['destination']; ?>
        </td>
        <td class="tableBody" align="center">
                  <?php echo ($did_destination['activated']=="1"?"Active":"Inactive"); ?>
        </td>
                <td class="tableBody" align="center">
                  <?php echo ($did_destination['voip_call']=="1"?"Active":"Inactive"); ?>
        </td>
    </tr>
    <?php
        $i++;
        }
    ?>
</table>
<?php
}
?>
<?php
if ( (sizeof($payment_result)>0 && $payment_result[0]!=null) ||
        (sizeof($call_result)>0 && $call_result[0]!=null) ||
        (sizeof($refill_result)>0 && $refill_result[0]!=null) ) {
?>
<br/>
<div style="width : 90%; text-align : right; margin-left:auto;margin-right:auto;" >
     <a class="cssbutton_big"  href="A2B_entity_card.php">
        <img src="<?php echo Images_Path_Main;?>/icon_arrow_orange.gif"/>
        <?php echo gettext("CUSTOMERS LIST"); ?>
    </a>
</div>
<br/>
<?php
}

$smarty->display( 'footer.tpl');
