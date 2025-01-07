<?php

use A2billing\A2Billing;
use A2billing\Admin;
use A2billing\Customer;
use A2billing\Forms\FormHandler;
use A2billing\Forms\Validator;

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

/**
 * @var A2Billing $A2B
 * @var string $form_action
 */
Admin::checkPageAccess(Admin::ACX_ADMINISTRATOR);

getpost_ifset(["voip_type", "friend_id"]);
/**
 * @var string|null $voip_type
 * @var string|null $friend_id
 */

$form_action ??= "list";
$voip_type ??= "sip";

if ($voip_type === "sip") {
	$HD_Form = new FormHandler("cc_sip_buddies", _("SIP Config"));
} else {
	$HD_Form = new FormHandler("cc_iax_buddies", _("IAX Config"));
}

$HD_Form->list_help_text = create_help(
    _("SIP and IAX Config will create a SIP or IAX entry on the Asterisk server, so that a customer can set up a SIP or IAX client to connect directly to the asterisk server without the need to enter an account and pin each time a call is made. When done, click on the CONFIRM DATA button, then click reload to apply the changes on the Asterisk server.<br>")
    . _("The customer must then enter the URL/IP address of the asterisk server into the SIP/IAX client, and use the Account Number and Secret word as the username and password.")
);

$HD_Form->help_text = create_help(
    _("Each SIP/IAX client is identified by a number of parameters.<br><br>")
    . _("More details on how to configure clients are on the Wiki")
);

$HD_Form->AddListValue(_("Cardholder"), "id_cc_card", [Customer::class, "getName"]);
$HD_Form->AddListValue(_("Username"), "username");
$HD_Form->AddListValue(_("Account code"), "accountcode");
$HD_Form->AddListValue(_("Secret"), "secret");
$HD_Form->AddListValue(_("Caller ID"), "callerid");
$HD_Form->AddListValue(_("Context"), "context");
$HD_Form->AddListValue(_("Default IP"), "defaultip");

$HD_Form->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form->FG_LIST_ADDING_BUTTON_LINK1 = "A2B_entity_friend.php?form_action=ask-add&voip_type=$voip_type";
$HD_Form->FG_LIST_ADDING_BUTTON_MSG1 = $voip_type === "sip" ? _("Add SIP Config") : _("Add IAX Config");
$HD_Form->FG_LIST_ADDING_BUTTON_IMG1 = get_image_path("telephone_add.png") ;

if (!empty($friend_id) && $friend_id !== "all") {
    $HD_Form->list_query_conditions["name"] = $friend_id;
}

$HD_Form->FG_ENABLE_EDIT_BUTTON = true;
$HD_Form->FG_ENABLE_DELETE_BUTTON = true;
$HD_Form->FG_ENABLE_ADD_BUTTON = true;

$adding = ($form_action === "ask-add" || $form_action === "add_sip" || $form_action === "add_iax");

$HD_Form->AddEditPopup(
    _("CARD ID"),
    "id_cc_card",
    "A2B_entity_card.php?popup_select=1&",
    _("Define the card number ID to use."),
    [],
    [Validator::class, "numeric"],
    _("Insert the card number ID")
);

$name_attr = ["maxlength" => 40];
if ($form_action === "ask-add") {
    $name_attr["value"] = generate_unique_value($HD_Form->FG_QUERY_TABLE_NAME);
}
$HD_Form->AddEditElement(
    _("NAME"),
    "name",
    "",
    $name_attr,
    [Validator::class, "min1Char"],
    _("Insert the name")
);


$HD_Form->AddEditElement(
    _("ACCOUNTCODE"),
    "accountcode",
    "",
    $name_attr,
    null,
    _("Insert the accountcode")
);


$HD_Form->AddEditElement(
    _("REGEXTEN"),
    "regexten",
    "",
    ["maxlength" => 20],
    null,
    _("Insert the regexten")
);


$HD_Form->AddEditElement(
    _("CALLERID"),
    "callerid",
    "",
    ["maxlength" => 60],
    null,
    _("Insert the callerid")
);


$HD_Form->AddEditElement(
    _("CID NUMBER"),
    "cid_number",
    "",
    ["maxlength" => 40],
    null,
    _("Insert the cid number")
);


$HD_Form->AddEditElement(
    _("AMAFLAGS"),
    "amaflags",
    "(default : billing)",
    ["maxlength" => 7] + ($adding ? ["value" => $A2B->config["peer_friend"]['amaflag']] : []),
    null,
    _("Insert the amaflags")
);

$pass_attr = ["maxlength" => 40];
if ($form_action === "ask-add") {
    $pass_attr["value"] = strtr(base64_encode(bin2hex(random_bytes(16))), "+/=", "   ");
}
$HD_Form->AddEditElement(
    _("SECRET"),
    "secret",
    "",
    $pass_attr,
    null,
    _("Insert the secret")
);


$HD_Form->AddEditElement(
    _("QUALIFY"),
    "qualify",
    "",
    ["maxlength" => 7],
    null,
    _("Insert the qualify")
);


$HD_Form->AddEditElement(
    _("DISALLOW"),
    "disallow",
    _("need to disallow=all before we can use allow. ( default : all )"),
    ["maxlength" => 40] + ($adding ? ["value" => "all"] : []),
    null,
    _("Insert the disallow")
);


$HD_Form->AddEditElement(
    _("ALLOW"),
    "allow",
    _("Set allow codecs separated by a comma, e.g. gsm,alaw,ulaw ( default : ulaw,alaw,gsm,g729)"),
    ["maxlength" => 40] + ($adding ? ["value" => "ulaw,alaw,gsm,g729"] : []),
    null,
    _("Insert the allow")
);


$HD_Form->AddEditElement(
    _("HOST"),
    "host",
    _("Use dynamic or set an IP ( default : dynamic )"),
    ["maxlength" => 31] + ($adding ? ["value" => "dynamic"] : []),
    null,
    _("Insert the host")
);


$HD_Form->AddEditElement(
    _("CONTEXT"),
    "context",
    _("set the context for this user (default : a2billing )"),
    ["maxlength" => 40] + ($adding ? ["value" => $A2B->config["peer_friend"]['context']] : []),
    null,
    _("Insert the context")
);


$HD_Form->AddEditElement(
    _("DEFAULTIP"),
    "defaultip",
    "",
    ["maxlength" => 15],
    null,
    _("Insert the defaultip")
);


$HD_Form->AddEditElement(
    _("LANGUAGE"),
    "language",
    "",
    ["maxlength" => 4],
    null,
    _("Insert the language")
);


$HD_Form->AddEditElement(
    _("PORT"),
    "port",
    "",
    ["maxlength" => 5],
    null,
    _("Insert the port")
);


$HD_Form->AddEditElement(
    _("REGSECONDS"),
    "regseconds",
    "",
    ["maxlength" => 40] + ($adding ? ["value" => 0] : []),
    null,
    _("Insert the regseconds")
);


$HD_Form->AddEditElement(
    _("IPADDR"),
    "ipaddr",
    "",
    ["maxlength" => 15],
    null,
    _("Insert the ipaddr")
);

$HD_Form->AddEditElement(
    _("MOHSUGGEST"),
    "mohsuggest",
    "",
    ["maxlength" => 20],
    null,
    _("Insert the mohsuggest")
);


$HD_Form->AddEditElement(
    _("AUTH"),
    "auth",
    "",
    ["maxlength" => 20],
    null,
    _("Insert the auth")
);


$HD_Form->AddEditElement(
    _("SETVAR"),
    "setvar",
    "",
    ["maxlength" => 200],
    null,
    _("Insert the setvar")
);

$HD_Form->AddEditElement(
    _("TYPE"),
    "type",
    _("type = friend | peer | user ( default : friend )"),
    ["maxlength" => 6] + ($adding ? ["value" => "friend"] : []),
    null,
    _("Insert the type")
);

$HD_Form->AddEditElement(
    _("DENY"),
    "deny",
    "",
    ["maxlength" => 50],
    null,
    _("Insert the deny")
);

$HD_Form->AddEditElement(
    _("PERMIT"),
    "permit",
    "",
    ["maxlength" => 50],
    null,
    _("Insert the permit")
);

if ($voip_type === "sip") {
    $HD_Form->AddEditElement(
        _("USERNAME"),
        "username",
        "",
        $name_attr,
        null,
        _("Insert the username")
    );


    $HD_Form->AddEditElement(
        _("MD5SECRET"),
        "md5secret",
        "",
        ["maxlength" => 50],
        null,
        _("Insert the md5secret")
    );


    $HD_Form->AddEditElement(
        _("NAT"),
        "nat",
        _("nat = yes | no | never | route ( default : yes )"),
        ["maxlength" => 50] + ($adding ? ["value" => "yes"] : []),
        null,
        _("Insert the nat")
    );


    $HD_Form->AddEditElement(
        _("DTMFMODE"),
        "dtmfmode",
        _("dtmfmode = RFC2833 | INFO | INBAND | AUTO ( default : RFC2833 )"),
        ["maxlength" => 7] + ($adding ? ["value" => "RFC2833"] : []),
        null,
        _("Insert the dtmfmode")
    );



    $HD_Form->AddEditElement(
        _("CANREINVITE"),
        "canreinvite",
        _("canreinvite : yes | no ( default : yes )"),
        ["maxlength" => 10] + ($adding ? ["value" => "yes"] : []),
        null,
        _("Insert the canreinvite")
    );




    $HD_Form->AddEditElement(
        _("CALLGROUP"),
        "callgroup",
        "",
        ["maxlength" => 10],
        null,
        _("Insert the callgroup")
    );




    $HD_Form->AddEditElement(
        _("FROMUSER"),
        "fromuser",
        "",
        ["maxlength" => 40],
        null,
        _("Insert the fromuser")
    );


    $HD_Form->AddEditElement(
        _("FROMDOMAIN"),
        "fromdomain",
        "",
        ["maxlength" => 40],
        null,
        _("Insert the fromdomain")
    );


    $HD_Form->AddEditElement(
        _("INSECURE"),
        "insecure",
        "",
        ["maxlength" => 30],
        null,
        _("Insert the insecure")
    );


    $HD_Form->AddEditElement(
        _("MAILBOX"),
        "mailbox",
        "",
        ["maxlength" => 50],
        null,
        _("Insert the mailbox")
    );



    $HD_Form->AddEditElement(
        _("MASK"),
        "mask",
        "",
        ["maxlength" => 50],
        null,
        _("Insert the mask")
    );


    $HD_Form->AddEditElement(
        _("PICKUPGROUP"),
        "pickupgroup",
        "",
        ["maxlength" => 10],
        null,
        _("Insert the pickupgroup")
    );


    $HD_Form->AddEditElement(
        _("RESTRICTCID"),
        "restrictcid",
        "",
        ["maxlength" => 1],
        null,
        _("Insert the restrictcid")
    );


    $HD_Form->AddEditElement(
        _("RTPTIMEOUT"),
        "rtptimeout",
        "",
        ["maxlength" => 3],
        null,
        _("Insert the rtptimeout")
    );


    $HD_Form->AddEditElement(
        _("RTPHOLDTIMEOUT"),
        "rtpholdtimeout",
        "",
        ["maxlength" => 3],
        null,
        _("Insert the rtpholdtimeout")
    );


    $HD_Form->AddEditElement(
        _("MUSICONHOLD"),
        "musiconhold",
        "",
        ["maxlength" => 40],
        null,
        _("Insert the musiconhold")
    );


    $HD_Form->AddEditElement(
        _("CANCALLFORWARD"),
        "cancallforward",
        _("cancallforward = yes | no ( default : yes )"),
        ["maxlength" => 3] + ($adding ? ["value" => "yes"] : []),
        null,
        _("Insert the cancallforward")
    );


    $HD_Form->AddEditElement(
        _("DEFAULTUSER"),
        "defaultuser",
        "",
        ["maxlength" => 10]
    );


    $HD_Form->AddEditElement(
        _("SUBSCRIBEMWI"),
        "subscribemwi",
        "",
        ["maxlength" => 10]
    );


    $HD_Form->AddEditElement(
        _("VMEXTEN"),
        "vmexten",
        "",
        ["maxlength" => 20]
    );


    $HD_Form->AddEditElement(
        _("CALLINGPRES"),
        "callingpres",
        "",
        ["maxlength" => 20]
    );


    $HD_Form->AddEditElement(
        _("USEREQPHONE"),
        "usereqphone",
        "",
        ["maxlength" => 10]
    );


    $HD_Form->AddEditElement(
        _("INCOMINGLIMIT"),
        "incominglimit",
        "",
        ["maxlength" => 10]
    );


    $HD_Form->AddEditElement(
        _("SUBSCRIBECONTEXT"),
        "subscribecontext",
        "",
        ["maxlength" => 40]
    );


    $HD_Form->AddEditElement(
        _("MUSICCLASS"),
        "musicclass",
        "",
        ["maxlength" => 20]
    );


    $HD_Form->AddEditElement(
        _("ALLOWTRANSFER"),
        "allowtransfer",
        "",
        ["maxlength" => 20]
    );


    $HD_Form->AddEditElement(
        _("AUTOFRAMING"),
        "autoframing",
        "",
        ["maxlength" => 10]
    );


    $HD_Form->AddEditElement(
        _("MAXCALLBITRATE"),
        "maxcallbitrate",
        "",
        ["maxlength" => 15]
    );


    $HD_Form->AddEditElement(
        _("OUTBOUNDPROXY"),
        "outboundproxy",
        "",
        ["maxlength" => 40]
    );


    $HD_Form->AddEditElement(
        _("REGSERVER"),
        "regserver",
        "",
        ["maxlength" => 20]
    );


    $HD_Form->AddEditElement(
        _("RTPKEEPALIVE"),
        "rtpkeepalive",
        "",
        ["maxlength" => 15]
    );

} else {

    $HD_Form->AddEditElement(
        _("TRUNK"),
        "trunk",
        _("iax trunking = yes | no ( default : yes )"),
        ["maxlength" => 3] + ($adding ? ["value" => "yes"] : []),
        null,
        _("Define the iax trunking")
    );


    $HD_Form->AddEditElement(
        _("DBSECRET"),
        "dbsecret",
        "",
        ["maxlength" => 40]
    );


    $HD_Form->AddEditElement(
        _("REGCONTEXT"),
        "regcontext",
        "",
        ["maxlength" => 40]
    );


    $HD_Form->AddEditElement(
        _("SOURCEADDRESS"),
        "sourceaddress",
        "",
        ["maxlength" => 10]
    );

    $HD_Form->AddEditElement(
        _("MOHINTERPRET"),
        "mohinterpret",
        "",
        ["maxlength" => 20]
    );


    $HD_Form->AddEditElement(
        _("INKEYS"),
        "inkeys",
        "",
        ["maxlength" => 40]
    );


    $HD_Form->AddEditElement(
        _("OUTKEY"),
        "outkey",
        "",
        ["maxlength" => 40]
    );

    $HD_Form->AddEditElement(
        _("SENDANI"),
        "sendani",
        "",
        ["maxlength" => 10]
    );

    $HD_Form->AddEditElement(
        _("FULLNAME"),
        "fullname",
        "",
        ["maxlength" => 40]
    );


    $HD_Form->AddEditElement(
        _("MAXAUTHREQ"),
        "maxauthreq",
        "",
        ["maxlength" => 15]
    );


    $HD_Form->AddEditElement(
        _("ENCRYPTION"),
        "encryption",
        "",
        ["maxlength" => 20]
    );


    $HD_Form->AddEditElement(
        _("TRANSFER"),
        "transfer",
        "",
        ["maxlength" => 10]
    );


    $HD_Form->AddEditElement(
        _("JITTERBUFFER"),
        "jitterbuffer",
        "",
        ["maxlength" => 10]
    );


    $HD_Form->AddEditElement(
        _("FORCEJITTERBUFFER"),
        "forcejitterbuffer",
        "",
        ["maxlength" => 10]
    );


    $HD_Form->AddEditElement(
        _("CODECPRIORITY"),
        "codecpriority",
        "",
        ["maxlength" => 40]
    );


    $HD_Form->AddEditElement(
        _("QUALIFYSMOOTHING"),
        "qualifysmoothing",
        "",
        ["maxlength" => 10]
    );


    $HD_Form->AddEditElement(
        _("QUALIFYFREQOK"),
        "qualifyfreqok",
        "",
        ["maxlength" => 10]
    );


    $HD_Form->AddEditElement(
        _("QUALIFYFREQNOTOK"),
        "qualifyfreqnotok",
        "",
        ["maxlength" => 10]
    );


    $HD_Form->AddEditElement(
        _("TIMEZONE"),
        "timezone",
        "",
        ["maxlength" => 20]
    );


    $HD_Form->AddEditElement(
        _("ADSI"),
        "adsi",
        "",
        ["maxlength" => 10]
    );


    $HD_Form->AddEditElement(
        _("REQUIRE CALL TOKEN"),
        "requirecalltoken",
        _("No supported by Realtime"),
        ["maxlength" => 20]
    );


    $HD_Form->AddEditElement(
        _("MAX CALL NUMBERS"),
        "maxcallnumbers",
        _("No supported by Realtime"),
        ["maxlength" => 10]
    );


    $HD_Form->AddEditElement(
        _("MAX CALL NUMBERS NON VALIDATED"),
        "maxcallnumbers_nonvalidated",
        _("No supported by Realtime"),
        ["maxlength" => 10]
    );
}


$field_list_edit = 'id_cc_card, name, accountcode, regexten, callerid, cid_number, amaflags, secret, qualify, disallow, allow, host, context, defaultip, language, port, regseconds, ipaddr, mohsuggest, auth, setvar, type, deny, permit';

if ($voip_type=='sip') {
	$field_list_edit .= ', username, md5secret, nat, dtmfmode, canreinvite, callgroup, fromuser, fromdomain, insecure, mailbox, mask, pickupgroup, restrictcid, rtptimeout, rtpholdtimeout, ' .
			'musiconhold,  cancallforward, defaultuser, subscribemwi, vmexten, callingpres, usereqphone, incominglimit, subscribecontext, musicclass, allowtransfer, ' .
			'autoframing, maxcallbitrate, outboundproxy, regserver, rtpkeepalive';
} else {
	$field_list_edit .= ', trunk, dbsecret, regcontext, sourceaddress, mohinterpret, inkeys, outkey,  sendani, fullname,  maxauthreq, encryption, transfer, jitterbuffer, ' .
			'forcejitterbuffer, codecpriority, qualifysmoothing, qualifyfreqok, qualifyfreqnotok, timezone, adsi, requirecalltoken, maxcallnumbers, maxcallnumbers_nonvalidated';
}

$HD_Form->FG_FILTER_ENABLE = true;
$HD_Form->FG_FILTER_COLUMN = 'name';
$HD_Form->FG_FILTER_LABEL = _('username');

$HD_Form->FG_INTRO_TEXT_EDITION= '';
$HD_Form->FG_INTRO_TEXT_ADITION = '';




$HD_Form->FG_LOCATION_AFTER_ADD = "?voip_type=$voip_type&id=";
$HD_Form->FG_LOCATION_AFTER_EDIT = "?voip_type=$voip_type&id=";
$HD_Form->FG_LOCATION_AFTER_DELETE = "?voip_type=$voip_type&id=";

