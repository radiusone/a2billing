<?php

use A2billing\Admin;
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

$menu_section = 11;
require_once "../../common/lib/admin.defines.php";
include '../lib/support/classes/receipt.php';
include '../lib/support/classes/receiptItem.php';

Admin::checkPageAccess(Admin::ACX_INVOICING);

getpost_ifset(array('id','curr'));

if (empty($id)) {
Header ("Location: A2B_entity_invoice.php");
}

$receipt = new Receipt($id);
$items = $receipt->loadItems();
//load customer
$DBHandle  = DbConnect();
$card_table = new Table('cc_card','*');
$card_clause = "id = ".$receipt->getCard();
$card_result = $card_table -> get_list($DBHandle, $card_clause);
$card = $card_result[0];

if (empty($card)) {
    echo "Customer doesn't exist or is not correctly defined for this invoice !";
    die();
}
$smarty->display('main.tpl');
//Load invoice conf
$invoice_conf_table = new Table('cc_invoice_conf','value');
$conf_clause = "key_val = 'company_name'";
$result = $invoice_conf_table -> get_list($DBHandle, $conf_clause);
$company_name = $result[0][0];

$conf_clause = "key_val = 'address'";
$result = $invoice_conf_table -> get_list($DBHandle, $conf_clause);
$address = $result[0][0];

$conf_clause = "key_val = 'zipcode'";
$result = $invoice_conf_table -> get_list($DBHandle, $conf_clause);
$zipcode = $result[0][0];

$conf_clause = "key_val = 'city'";
$result = $invoice_conf_table -> get_list($DBHandle, $conf_clause);
$city = $result[0][0];

$conf_clause = "key_val = 'country'";
$result = $invoice_conf_table -> get_list($DBHandle, $conf_clause);
$country = $result[0][0];

$conf_clause = "key_val = 'web'";
$result = $invoice_conf_table -> get_list($DBHandle, $conf_clause);
$web = $result[0][0];

$conf_clause = "key_val = 'phone'";
$result = $invoice_conf_table -> get_list($DBHandle, $conf_clause);
$phone = $result[0][0];

$conf_clause = "key_val = 'fax'";
$result = $invoice_conf_table -> get_list($DBHandle, $conf_clause);
$fax = $result[0][0];

$conf_clause = "key_val = 'email'";
$result = $invoice_conf_table -> get_list($DBHandle, $conf_clause);
$email = $result[0][0];

$conf_clause = "key_val = 'vat'";
$result = $invoice_conf_table -> get_list($DBHandle, $conf_clause);
$vat_invoice = $result[0][0];

$conf_clause = "key_val = 'display_account'";
$result = $invoice_conf_table -> get_list($DBHandle, $conf_clause);
$display_account = $result[0][0];

//Currencies check

$currencies_list = get_currencies();
if (!isset($currencies_list[strtoupper($curr)]["value"]) || !is_numeric($currencies_list[strtoupper($curr)]["value"])) {$mycur = 1;$display_curr=strtoupper(BASE_CURRENCY);} else {$mycur = $currencies_list[strtoupper($curr)]["value"];$display_curr=strtoupper($curr);}

function amount_convert($amount)
{
    global $mycur;

    return $amount/$mycur;
}

if (!$popup_select) {
?>
<a id="rv_popupselect" href="#"> <img src="../Public/templates/default/images/printer.png" title="Print" alt="Print" border="0"></a>
&nbsp;&nbsp;
<?php if (strtoupper(BASE_CURRENCY)!=strtoupper($card['currency'])) { ?>

    <select id="currency" class="form_input_select" name="curr">
        <option value="<?php echo BASE_CURRENCY;?>" <?php if(BASE_CURRENCY==$curr) echo "selected";?>  ><?php echo gettext('SYSTEM CURRENCY')." : ".strtoupper(BASE_CURRENCY); ?> </option>
        <option value="<?php echo $card['currency'];?>" <?php if($card['currency']==$curr) echo "selected";?>   ><?php echo gettext('CUSTOMER CURRENCY')." : ".strtoupper($card['currency']); ?></option>
    </select>

<script>
$( function() {
    var id = <?= json_encode($id) ?>;
    var curr = <?= json_encode($curr ?? "") ?>;
    $("#currency").on("change", () => self.location.href = `?id=${id}&curr=${$(this).val())}`);
    $("a#rv_popupselect").on("click", () => window.open(`?popup_select=1&id=${id}&curr=${curr}`, '', 'scrollbars=yes,resizable=yes,width=700,height=500'));
})
</script>

<?php
    }
}
?>

<div class="receipt-wrapper">
  <table class="receipt-table">
  <thead>
  <tr class="one">
    <td class="one">
     <h1><?php echo gettext("RECEIPT"); ?></h1>
     <div class="client-wrapper">
         <div class="company-name break"><?php echo $card['company_name'] ?></div>
         <div class="fullname"><?php echo $card['lastname']." ".$card['firstname'] ?></div>
           <div class="address"><span class="street"><?php echo $card['address'] ?></span> </div>
           <div class="zipcode-city"><span class="zipcode"><?php echo $card['zipcode'] ?></span> <span class="city"><?php echo $card['city'] ?></span></div>
          <div class="country break"><?php echo $card['country'] ?></div>
           <div class="vat-number"><?php echo gettext("VAT nr.")." : ".$card['VAT_RN']; ?></div>
     </div>
    </td>
    <td class="two">

    </td>
    <td class="three">
     <div class="supplier-wrapper">
       <div class="company-name"><?php echo $company_name ?></div>
       <div class="address"><span class="street"><?php echo $address ?></span> </div>
       <div class="zipcode-city"><span class="zipcode"><?php echo $zipcode ?></span> <span class="city"><?php echo $city ?></span></div>
       <div class="country break"><?php echo $country ?></div>
       <div class="phone"><?php echo $phone ?></div>
       <div class="fax"><?php echo $fax ?> </div>
       <div class="email"><?php echo $email ?></div>
       <div class="web"><?php echo $web ?></div>
     </div>
    </td>
  </tr>
  <tr class="two">
    <td colspan="3" class="receipt-details">
      <table class="receipt-details">
        <tbody><tr>
          <td class="one">
            <strong><?php echo gettext("Date"); ?></strong>
            <div><?php echo $receipt->getDate() ?></div>
          </td>

          <?php if ($display_account==1) { ?>
          <td class="three">
              <strong><?php echo gettext("Client Account Number"); ?></strong>
            <div><?php echo $card['username'] ?></div>
          </td>
          <?php } ?>
                 </tr>
      </tbody></table>
    </td>
  </tr>
  </thead>
  <tbody>
    <tr>
      <td colspan="3" class="items">
        <table class="items">
          <tbody>
          <tr class="one">
              <th style="text-align:left;" width="20%"><?php echo gettext("Date"); ?></th>
              <th class="description" width="60%"><?php echo gettext("Description"); ?></th>

              <th width="20%" ><?php echo gettext("Cost"); ?></th>
          </tr>
          <?php
          $i=0;
          foreach ($items as $item) { ?>
            <tr style="vertical-align:top;" class="<?php if($i%2==0) echo "odd"; else echo "even";?>" >
                <td style="text-align:left;">
                    <?php echo $item->getDate(); ?>
                </td>
                <td class="description">
                    <?php echo $item->getDescription(); ?>
                </td>
                <td align="right">
                    <?php echo number_format(round(amount_convert($item->getPrice()),2),2); ?>
                </td>

            </tr>
             <?php  $i++;} ?>

        </tbody></table>
      </td>
    </tr>
    <?php
        $price = 0;
        foreach ($items as $item) {
             $price = $price + $item->getPrice();
         }

         ?>

    <tr>
      <td colspan="3">
        <table class="total">
         <tbody>

         <tr class="inctotal">
           <td class="one"></td>
           <td class="two"><?php echo gettext("Total :") ?></td>
           <td class="three"><div class="inctotal"><div class="inctotal inner"><?php echo number_format(ceil(amount_convert($price)*100)/100,2)." $display_curr"; ?></div></div></td>
         </tr>
        </tbody></table>
      </td>
    </tr>
    <tr>
    <td colspan="3" class="additional-information">
      <div class="receipt-description">
      <?php echo $receipt->getDescription() ?>
     </div></td>
    </tr>
  </tbody>
  <tfoot>
    <tr>
      <td colspan="3" class="footer">
        <?php echo $company_name." | ".$address.", ".$zipcode." ".$city." ".$country." | VAT nr.".$vat_invoice; ?>
      </td>
    </tr>
  </tfoot>
  </table></div>

<?php
$smarty->display('footer.tpl');
