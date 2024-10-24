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

require_once "../common/lib/customer.defines.php";
include './lib/support/classes/invoice.php';
include './lib/support/classes/invoiceItem.php';

if (! has_rights (Customer::ACX_INVOICES)) {
    Header ("HTTP/1.0 401 Unauthorized");
    Header ("Location: PP_error.php?c=accessdenied");
    die();
}

getpost_ifset(array('id'));

if (empty($id)) {
    Header ("Location: A2B_entity_invoice.php?section=13");
}

$invoice = new invoice($id);
if ($invoice->getCard() != $_SESSION["card_id"]) {
    Header ("HTTP/1.0 401 Unauthorized");
    Header ("Location: PP_error.php?c=accessdenied");
    die();
}
$items = $invoice->loadItems();

//load customer
$DBHandle  = DbConnect();
$card_table = new Table('cc_card', '*');
$card_clause = "id = ".$_SESSION["card_id"];
$card_result = $card_table -> get_list($DBHandle, $card_clause);
$card = $card_result[0];

if (empty($card)) {
    echo "Customer doesn't exist or is not correctly defined for this invoice !";
    die();
}
$smarty->display('main.tpl');
//Load invoice conf
$invoice_conf_table = new Table('cc_invoice_conf', 'value');
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

//country convert
$table_country= new Table('cc_country', 'countryname');
$country_clause = "countrycode = '".$card['country']."'";
$result = $table_country -> get_list($DBHandle, $country_clause);
$card_country = $result[0][0];

//Currencies check
$curr = $card['currency'];
$currencies_list = get_currencies();
if (!isset($currencies_list[strtoupper($curr)]["value"]) || !is_numeric($currencies_list[strtoupper($curr)]["value"])) {$mycur = 1;$display_curr=strtoupper(BASE_CURRENCY);} else {$mycur = $currencies_list[strtoupper($curr)]["value"];$display_curr=strtoupper($curr);}

function amount_convert($amount)
{
    global $mycur;

    return $amount/$mycur;
}

if (!$popup_select) {
?>
<a href="javascript:;" onClick="window.open('?popup_select=1&id=<?php echo $id ?>','','scrollbars=yes,resizable=yes,width=700,height=500')" > <img src="./templates/default/images/printer.png" title="Print" alt="Print" border="0"></a>
&nbsp;&nbsp;
<?php
} else {
?>
<P ALIGN="right"> <a href="javascript:window.print()"> <img src="./templates/default/images/printer.png" title="Print" alt="Print" border="0"> <?php echo gettext("Print"); ?></a> &nbsp; &nbsp;</P>
<?php
}
?>

<div class="invoice-wrapper">
  <table class="invoice-table">
  <thead>
  <tr class="one">
    <td class="one">
     <h1><?php echo gettext("INVOICE"); ?></h1>
     <div class="client-wrapper">
         <div class="company-name break"><?php echo $card['company_name'] ?></div>
         <div class="fullname"><?php echo $card['lastname']." ".$card['firstname'] ?></div>
           <div class="address"><span class="street"><?php echo $card['address'] ?></span> </div>
           <div class="zipcode-city"><span class="zipcode"><?php echo $card['zipcode'] ?></span> <span class="city"><?php echo $card['city'] ?></span></div>
          <div class="country break"><?php echo $card_country ?></div>
           <?php if (!empty($card['VAT_RN'])) { ?>
               <div class="vat-number"><?php echo gettext("VAT nr.")." : ".$card['VAT_RN']; ?></div>
           <?php } ?>
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
       <div class="phone"><?php echo gettext("tel").": ".$phone ?></div>
       <div class="fax"><?php echo gettext("fax").": ".$fax ?> </div>
       <div class="email"><?php echo gettext("mail").": ".$email ?></div>
       <div class="web"><?php echo $web ?></div>
       <div class="vat-number"><?php echo gettext("VAT nr.")." : ".$vat_invoice; ?></div>
     </div>
    </td>
  </tr>
  <tr class="two">
    <td colspan="3" class="invoice-details">
    <br/>
      <table class="invoice-details">
        <tbody><tr>
          <td class="one">
            <strong><?php echo gettext("Date"); ?></strong>
            <div><?php echo $invoice->getDate() ?></div>
          </td>
          <td class="two">
            <strong><?php echo gettext("Invoice number"); ?></strong>
            <div><?php echo $invoice->getReference() ?></div>
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
              <th style="text-align:left;"><?php echo gettext("Date"); ?></th>
              <th class="description"><?php echo gettext("Description"); ?></th>
              <th><?php echo gettext("Cost excl. VAT"); ?></th>
              <th><?php echo gettext("VAT"); ?></th>
              <th><?php echo gettext("Cost incl. VAT"); ?></th>
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
                    <?php echo number_format(amount_convert($item->getPrice()),2); ?>
                </td>
                <td align="right">
                    <?php echo number_format($item->getVAT(),2)."%"; ?>
                </td>
                <td align="right">
                    <?php echo number_format(amount_convert($item->getPrice())*(1+($item->getVAT()/100)),2); ?>
                </td>
            </tr>
             <?php  $i++;} ?>

        </tbody></table>
      </td>
    </tr>
    <?php
      $price_without_vat = 0;
      $price_with_vat = 0;
      $vat_array = array();
      foreach ($items as $item) {
        $price_without_vat = $price_without_vat + $item->getPrice();
        $price_with_vat = $price_with_vat + ($item->getPrice()*(1+($item->getVAT()/100)));
        if (array_key_exists("".$item->getVAT(),$vat_array)) {
          $vat_array[$item->getVAT()] = $vat_array[$item->getVAT()] + $item->getPrice()*($item->getVAT()/100) ;
        } else {
          $vat_array[$item->getVAT()] =  $item->getPrice()*($item->getVAT()/100) ;
        }
      }
    ?>
    <tr>
      <td colspan="3">
        <table class="total">
          <tbody><tr class="extotal">
            <td class="one"></td>
            <td class="two"><?php echo gettext("Subtotal excl. VAT:"); ?></td>
            <td class="three"><?php echo number_format(amount_convert($price_without_vat),2)." $display_curr"; ?></td>
          </tr>
          <?php foreach ($vat_array as $key => $val) { ?>
            <tr class="vat">
              <td class="one"></td>
              <td class="two"><?php echo gettext("VAT")."$key%:"; ?></td>
              <td class="three"><?php echo number_format(amount_convert($val),2)." $display_curr"; ?></td>
            </tr>
          <?php } ?>
          <tr class="inctotal">
            <td class="one"></td>
            <td class="two"><?php echo gettext("Total incl. VAT:") ?></td>
            <td class="three">
              <div class="inctotal inner">
                <?php echo number_format(amount_convert($price_with_vat),2)." $display_curr"; ?>
              </div>
             </td>
          </tr>
        </tbody></table>
      </td>
    </tr>
    <tr>
    <td colspan="3" class="additional-information">
      <div class="invoice-description">
      <?php echo $invoice->getDescription() ?>
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
