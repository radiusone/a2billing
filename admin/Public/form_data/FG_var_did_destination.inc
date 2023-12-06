<?php

use A2billing\Forms\FormHandler;

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

getpost_ifset(['id']);
/**
 * @var string $id
 */

$form_action = $form_action ?? "list";
if ($form_action === "list") {
	$HD_Form = new FormHandler(
        "cc_did_destination dest LEFT JOIN cc_did ON cc_did.id = dest.id_cc_did",
        _("DID Destination"),
        'dest.id'
    );
} else {
	$HD_Form = new FormHandler("cc_did_destination", _("DID Destination"));
}

$HD_Form->no_debug();
$HD_Form->FG_TABLE_DEFAULT_ORDER = "destination";
$HD_Form->FG_TABLE_DEFAULT_SENS = "DESC";

$HD_Form->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form->FG_LIST_ADDING_BUTTON_LINK1 = "A2B_entity_did_destination.php?form_action=ask-add";
$HD_Form->FG_LIST_ADDING_BUTTON_MSG1 = _("Add Destination");
$HD_Form->FG_LIST_ADDING_BUTTON_IMG1 = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAM+SURBVBgZBcHLT1xVAMDh333MkxmYAYZOwbGGPqRaHq0gpIBGba3GR2KibjQmsHDXsHDjP2DSmBgTU7swUUk0ajemK1HapjYIoYRqi4DoUAsNjwIyw9wZ7tzHOff4fZpSCgAAgJHJ4UHgPNADZAATKAG3VKAufTb49c8AAACaUgqAkclhExgF3nwtdyaSjmWw7BpsD4R0EGxxfXNMSCGvCiHf/vLlHyoAmlKKkcnhEHCju/HJ/hdzz2E5EVw8li2NsIqwawco4RHDZtOdYHZjdk744pnv3rpS0gGA0VMNx/oHmrspSA9f1xBCpyrC2IQpuBG2nRhrpRCPJp6mO/t4h+f63wMYxbMPBoCP3zn2qjEv99mkSjUQVJwEBTeE5UB+vUxpf59IehetLk9fYxvXF2dav7k1etfoHT756bnm3hOaEWNTF6CaOCgT3N4yqDo6i+sVgmiRyKG/cWvz7ARFzKLkRENOv72yVG8CPbnaFu7YG+xEdZ4wDhMgWN32cJwqdVmFVT/OcrAHriIlIuR3XM48dgrfFe0m0BA3a1i1N9h2bZLxVva8JMViQF3GoSltsyO7sNy7RFSZ8n+7FPbiJGJJfE+kTKWUpinwXAtFDjMkGZv20WIJNpcFuqqlOVMlWR7EWvdxmMX37oNSCCE0U4qgYHlWS4ORIhntZG3HxPFDhKMRok0x7v27izOTIhOeIROdJ+JZlJ0yY1O/IEVQMoUvfl8pPGg5Es9x7eEkqfgRwkYO37FRRopwIk2tO0FbdomnjvfxSP1RbixcYXp+AqNa8XTfExd/XLopDiUymPY6pd0p0mkXU7iENEVENzAr1+hq60Tqks6DZ5GaT1/7aXTPyepfvXJ53HP9n8YXb/JsSxd1Rg3pREBdWFIbdkiGXIqVLUJagtePnwfggxe+4HBTB0BIB/Bd/91f83fm/lz5i3NtPbSmTA7EFY1GmQbdplgusrAxxYWrQwBcGB/i3vYcgKMppQB46fPnk8IXl4Uvz77XP2QisygVR9M1Fv75ltXiFKc7BjiaPUn+4R9Mzf3G2v3SJ5pSCgAAgP6Pet+QQr4vZdAeyCANAJSMatnTveoBIAqUgUvTFzc+/B+ww5qo63KzbgAAAABJRU5ErkJggg==";

$activated_list = getActivationList();

$billingtype_list = getBillingTypeList();
// -- billtype: 0 = fix per month + dialoutrate, 1= fix per month, 2 = dialoutrate, 3 = free

$priority_list = array_map(fn ($v) => [$v, $v], range(1, 5));
$validated_list = [[_("Pending"), "0"], [_("Validated"), "1"]];
$yesno_list = [[_("Yes"), "1"], [_("No"), "0"]];

$HD_Form->AddViewElement(_("Destination"), "destination");
$HD_Form->AddViewElement(_("Creation Date"), "dest.creationdate", true, 16, "display_dateformat");
$HD_Form->AddViewElement(_("DID"), "did", true, 15, "display_did");
$HD_Form->AddViewElement(_("Account Number"), "id_cc_card", true, 0, "display_customer_id_link");
$HD_Form->AddViewElement(_("Active"), "dest.activated", true, 0, "", "list", $activated_list);
$HD_Form->AddViewElement(_("VoIP"), "voip_call", true, 0, "", "list", $yesno_list);
$HD_Form->AddViewElement(_("Priority"), "priority", true, 0, "", "list", $priority_list);
$HD_Form->AddViewElement(_("Minutes Used"), "dest.secondusedreal", true, 30, "display_minute");
$HD_Form->AddViewElement(_("Validated"), "validated", true, 0, "", "list", $validated_list);

// added a parameter to append  FG_TABLE_ID  ( by default ) or disable 0.
$HD_Form->FieldViewElement ('destination, cc_did_destination.creationdate, cc_did.did, id_cc_card, cc_did_destination.activated, voip_call, priority, cc_did_destination.secondusedreal, validated');

$HD_Form->FG_ENABLE_EDIT_BUTTON = true;
$HD_Form->FG_ENABLE_DELETE_BUTTON = true;
$HD_Form->FG_ENABLE_ADD_BUTTON = true;

$HD_Form->AddEditElement(
    _("Destination"),
    "destination",
    _("Enter the phone number you wish to call, or the VoIP client to reach. (ie: 347894999 or SIP/jeremy@182.212.1.45). If the call is VoIP, the VoIP_Call must be set to yes."),
    "maxlength=120",
    0,
    _("Insert the DID")
);

if ($form_action !== "ask-edit" && $form_action !== "edit") {

    $HD_Form->AddEditPopup(
        _("Customer"),
        "id_cc_card",
        "A2B_entity_card.php?popup_select=1&",
        _("Select the Customer ID"),
        "", 4, _("Insert the Customer ID")
    );

    $HD_Form->AddEditSqlSelect("id_cc_did", _("DID"), "cc_did", "did,id");
}

$HD_Form->AddEditRadio(
    _("Activated"),
    "activated",
    $activated_list,
    "1",
    "", "",
    _("Choose if you want to activate this card")
);

$HD_Form->AddEditRadio(_("Validated"), "validated", $validated_list, "1", "", "");

$HD_Form->AddEditSelect("priority", $priority_list, _("Priority"));

$HD_Form->AddEditRadio(
    _("VoIP Call"),
    "voip_call",
    $yesno_list,
    "1",
    "", "",
    _("Choose if you want to not use the trunk and let the asterisk call directly the destination (ie, Destination : SIP/jeremy@182.212.1.45)")
);

if ($form_action !== "ask-edit" && $form_action !== "edit") {
	$HD_Form->FieldEditElement ('destination, id_cc_card, id_cc_did, activated, validated, priority, voip_call');
} else {
	$HD_Form->FieldEditElement ('destination, activated, validated, priority, voip_call');
}

// Set the filter variables
$HD_Form->FG_FILTER_ENABLE = true;
$HD_Form->FG_FILTER_COLUMN = 'cc_did.did';
$HD_Form->FG_FILTER_LABEL = _("DID");

$HD_Form->FG_INTRO_TEXT_ADITION = '';

$HD_Form->FG_ADDITIONAL_FUNCTION_AFTER_ADD = 'did_destination_add';
$HD_Form->FG_ADDITIONAL_FUNCTION_AFTER_DELETE = 'did_destination_del';

$HD_Form->FG_LOCATION_AFTER_ADD = "?form_action=list&id=";
$HD_Form->FG_LOCATION_AFTER_EDIT = "?form_action=list&id=";
$HD_Form->FG_LOCATION_AFTER_DELETE = "?form_action=list&id=";
