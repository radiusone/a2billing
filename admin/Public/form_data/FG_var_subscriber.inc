<?php

use A2billing\Admin;
use A2billing\Forms\FormHandler;
use A2billing\Forms\Validator;
use A2billing\Table;

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of A2Billing (http://www.a2billing.net/)
 *
 * A2Billing, Commercial Open Source Telecom Billing platform,
 * powered by Star2billing S.L. <http://www.star2billing.com/>
 *
 * @copyright   Copyright (C) 2004-2012 - Star2billing S.L.
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

Admin::checkPageAccess(Admin::ACX_ADMINISTRATOR);

getpost_ifset(["id"]);
/**
 * @var numeric-string|null $id
 */

$form_action ??= "list";
$HD_Form = new FormHandler("cc_card_subscription", _("Subscriber"));

$HD_Form->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form->FG_LIST_ADDING_BUTTON_IMG1 = get_image_path("text_signature.png") ;

$HD_Form->help_text = create_help(_("SUBSCRIBER - You can make customers subscribe for any subscription and for a certain time."));

$HD_Form->AddListValue(_("Account"), "id_cc_card", "display_customer_id_link");
$HD_Form->AddListSqlMapping(_("Service"), "id_subscription_fee", new Table("cc_subscription_service", ["id", "label"]));
$HD_Form->AddListValue(_("Start date"), "startdate");
$HD_Form->AddListValue(_("Stop date"), "stopdate");
$HD_Form->AddListValue(_("Product"), "product_name");
$HD_Form->FieldViewElement (["id_cc_card", "id_subscription_fee", "startdate", "stopdate", "product_name"]);

$HD_Form->FG_ENABLE_EDIT_BUTTON = true;
$HD_Form->FG_ENABLE_DELETE_BUTTON = true;
$HD_Form->FG_ENABLE_ADD_BUTTON = true;

$comp_date = $comp_date_plus = $idd = "";
if ($form_action === "ask-add") {
	$end_date = date("Y-m-d H:i:s");
	$comp_date = "value='$end_date'";
    if (!empty($id)) {
        //set passed id if any (used if called from customer screen, id is passed to pr-epopluate value
        $idd = "value='$id'";
    }
	//Max value Mysql Timestamp can store is '2038-01-19 03:14:07' UTC
	$comp_date_plus = "value='2038-01-01 00:00:00'";
}

$HD_Form->AddEditPopup(
    _("CUSTOMER ID"),
    "id_cc_card",
    "A2B_entity_card.php?popup_select=1&",
    _("Select the Customer ID"),
    $idd,
    [Validator::class, "numeric"], _("Insert the Customer ID")
);

$HD_Form->AddEditSqlSelect(
    _("ID SUBSCRIPTION SERVICE"),
    "id_subscription_fee",
    "cc_subscription_service",
    "label,id",
    "",
    "",
    [-1, _("NOT DEFINED")]
);

$HD_Form->AddEditElement(
    _("START DATE"),
    "startdate",
    _("please use the format YYYY-MM-DD HH:MM:SS. For instance, '2004-12-31 00:00:00'"),
    "size=40 maxlength=40  $comp_date",
    10, // [Validator::class, "dateTime"],
    _("Insert the start date"),
    "",
    "NO-NULL",
    
);

$HD_Form->AddEditElement(
    _("STOP DATE"),
    "stopdate",
    _("please use the format YYYY-MM-DD HH:MM:SS. For instance, '2004-12-31 00:00:00'"),
    "size=40 maxlength=40  $comp_date_plus",
    10, // [Validator::class, "dateTime"],
    _("Insert the stop date"),
    "",
    "NO-NULL",
    
);

$HD_Form->AddEditTextarea(
    _("PRODUCT LABEL"),
    "product_name",
    "",
    "cols='50' rows='4'",
    null,
    _("Enter the name of the product wich is subscribed")
);

$HD_Form->FG_INTRO_TEXT_ADITION = _("Apply subscription to account");

$HD_Form->FG_LOCATION_AFTER_ADD = "?id=";
$HD_Form->FG_LOCATION_AFTER_EDIT = "?id=";
$HD_Form->FG_LOCATION_AFTER_DELETE = "?id=";
