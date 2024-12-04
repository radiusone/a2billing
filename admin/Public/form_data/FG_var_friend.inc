<?php

use A2billing\A2Billing;
use A2billing\Admin;
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

$HD_Form->AddListValue(_("Cardholder"), "id_cc_card", "display_customer_name_id_link");
$HD_Form->AddListValue(_("Username"), "username", "display_customer_link");
$HD_Form->AddListValue(_("Account code"), "accountcode");
$HD_Form->AddListValue(_("Secret"), "secret");
$HD_Form->AddListValue(_("Caller ID"), "callerid");
$HD_Form->AddListValue(_("Context"), "context");
$HD_Form->AddListValue(_("Default IP"), "defaultip");
$HD_Form->FieldViewElement([
    "id_cc_card",
    "username",
    "accountcode",
    "secret",
    "callerid",
    "context",
    "defaultip",
]);

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

$maxi = "";
$pass_secret = "";
$AMAFLAGS_default = "";
$TYPE_default = "";
$NAT_default = "";
$DTMFMODE_default = "";
$CANREINVITE_default = "";
$DISALLOW_default = "";
$ALLOW_default = "";
$HOST_default = "";
$CONTEXT_default = "";
$REGSECONDS_default = "";
$CANCALLFORWARD_default = "";
$TRUNK_default = "";

if ($form_action === "ask-add" || $form_action === "add_sip" || $form_action === "add_iax") {
    if ($form_action === "ask-add") {
        $card_gen = generate_unique_value($HD_Form->FG_QUERY_TABLE_NAME);
        $maxi = "value='$card_gen'";
        $pass = strtr(base64_encode(bin2hex(random_bytes(16))), "+/=", "   ");
        $pass_secret = "value='$pass'";
    }
	$AMAFLAGS_default= "value='".$A2B->config["peer_friend"]['amaflag']."'";
	$TYPE_default= "value='friend'";
	$NAT_default= "value='yes'";
	$DTMFMODE_default= "value='RFC2833'";
	$CANREINVITE_default= "value='yes'";
	$DISALLOW_default= "value='all'";
	$ALLOW_default= "value='ulaw,alaw,gsm,g729'";
	$HOST_default= "value='dynamic'";
	$CONTEXT_default= "value='" . $A2B->config["peer_friend"]['context']."'";
	$REGSECONDS_default= "value='0'";
	$CANCALLFORWARD_default= "value='yes'";
	$TRUNK_default= "value='yes'";
}

$HD_Form->AddEditPopup(
    _("CARD ID"),
    "id_cc_card",
    "A2B_entity_card.php?popup_select=1&",
    _("Define the card number ID to use."),
    "",
    4,
    _("Insert the card number ID")
);

$HD_Form->AddEditElement(
    _("NAME"),
    "name",
    "",
    "size=40 $maxi maxlength=40",
    9,
    _("Insert the name")
);


$HD_Form->AddEditElement(
    _("ACCOUNTCODE"),
    "accountcode",
    "",
    "size=20 $maxi maxlength=50",
    null,
    _("Insert the accountcode")
);


$HD_Form->AddEditElement(
    _("REGEXTEN"),
    "regexten",
    "",
    "size=20 maxlength=20",
    null,
    _("Insert the regexten")
);


$HD_Form->AddEditElement(
    _("CALLERID"),
    "callerid",
    "",
    "size=40 maxlength=60",
    null,
    _("Insert the callerid")
);


$HD_Form->AddEditElement(
    _("CID NUMBER"),
    "cid_number",
    "",
    "size=40 maxlength=40",
    null,
    _("Insert the cid number")
);


$HD_Form->AddEditElement(
    _("AMAFLAGS"),
    "amaflags",
    "(default : billing)",
    "size=10 maxlength=7 $AMAFLAGS_default",
    null,
    _("Insert the amaflags")
);


$HD_Form->AddEditElement(
    _("SECRET"),
    "secret",
    "",
    "size=40 maxlength=40 $pass_secret",
    null,
    _("Insert the secret")
);


$HD_Form->AddEditElement(
    _("QUALIFY"),
    "qualify",
    "",
    "size=8 maxlength=7",
    null,
    _("Insert the qualify")
);


$HD_Form->AddEditElement(
    _("DISALLOW"),
    "disallow",
    _("need to disallow=all before we can use allow. ( default : all )"),
    "size=50 maxlength=40 $DISALLOW_default",
    null,
    _("Insert the disallow")
);


$HD_Form->AddEditElement(
    _("ALLOW"),
    "allow",
    _("Set allow codecs separated by a comma, e.g. gsm,alaw,ulaw ( default : ulaw,alaw,gsm,g729)"),
    "size=50 maxlength=40 $ALLOW_default",
    null,
    _("Insert the allow")
);


$HD_Form->AddEditElement(
    _("HOST"),
    "host",
    _("Use dynamic or set an IP ( default : dynamic )"),
    "size=40 maxlength=31 $HOST_default",
    null,
    _("Insert the host")
);


$HD_Form->AddEditElement(
    _("CONTEXT"),
    "context",
    _("set the context for this user (default : a2billing )"),
    "size=40 maxlength=40 $CONTEXT_default",
    null,
    _("Insert the context")
);


$HD_Form->AddEditElement(
    _("DEFAULTIP"),
    "defaultip",
    "",
    "size=20 maxlength=15",
    null,
    _("Insert the defaultip")
);


$HD_Form->AddEditElement(
    _("LANGUAGE"),
    "language",
    "",
    "size=10 maxlength=4",
    null,
    _("Insert the language")
);


$HD_Form->AddEditElement(
    _("PORT"),
    "port",
    "",
    "size=10 maxlength=5",
    null,
    _("Insert the port")
);


$HD_Form->AddEditElement(
    _("REGSECONDS"),
    "regseconds",
    "",
    "size=50 maxlength=40 $REGSECONDS_default",
    null,
    _("Insert the regseconds")
);


$HD_Form->AddEditElement(
    _("IPADDR"),
    "ipaddr",
    "",
    "size=20 maxlength=15",
    null,
    _("Insert the ipaddr")
);

$HD_Form->AddEditElement(
    _("MOHSUGGEST"),
    "mohsuggest",
    "",
    "size=20 maxlength=20",
    null,
    _("Insert the mohsuggest")
);


$HD_Form->AddEditElement(
    _("AUTH"),
    "auth",
    "",
    "size=20 maxlength=20",
    null,
    _("Insert the auth")
);


$HD_Form->AddEditElement(
    _("SETVAR"),
    "setvar",
    "",
    "size=60 maxlength=200",
    null,
    _("Insert the setvar")
);

$HD_Form->AddEditElement(
    _("TYPE"),
    "type",
    _("type = friend | peer | user ( default : friend )"),
    "size=10 maxlength=6 $TYPE_default",
    null,
    _("Insert the type")
);

$HD_Form->AddEditElement(
    _("DENY"),
    "deny",
    "",
    "size=60 maxlength=50",
    null,
    _("Insert the deny")
);

$HD_Form->AddEditElement(
    _("PERMIT"),
    "permit",
    "",
    "size=60 maxlength=50",
    null,
    _("Insert the permit")
);

if ($voip_type=='sip') {


    $HD_Form->AddEditElement(
        _("USERNAME"),
        "username",
        "",
        "size=50 $maxi maxlength=40",
        null,
        _("Insert the username")
    );


    $HD_Form->AddEditElement(
        _("MD5SECRET"),
        "md5secret",
        "",
        "size=60 maxlength=50",
        null,
        _("Insert the md5secret")
    );


    $HD_Form->AddEditElement(
        _("NAT"),
        "nat",
        _("nat = yes | no | never | route ( default : yes )"),
        "size=60 maxlength=50 $NAT_default",
        null,
        _("Insert the nat")
    );


    $HD_Form->AddEditElement(
        _("DTMFMODE"),
        "dtmfmode",
        _("dtmfmode = RFC2833 | INFO | INBAND | AUTO ( default : RFC2833 )"),
        "size=10 maxlength=7 $DTMFMODE_default",
        null,
        _("Insert the dtmfmode")
    );



    $HD_Form->AddEditElement(
        _("CANREINVITE"),
        "canreinvite",
        _("canreinvite : yes | no ( default : yes )"),
        "size=20 maxlength=10 $CANREINVITE_default",
        null,
        _("Insert the canreinvite")
    );




    $HD_Form->AddEditElement(
        _("CALLGROUP"),
        "callgroup",
        "",
        "size=10 maxlength=10",
        null,
        _("Insert the callgroup")
    );




    $HD_Form->AddEditElement(
        _("FROMUSER"),
        "fromuser",
        "",
        "size=40 maxlength=40",
        null,
        _("Insert the fromuser")
    );


    $HD_Form->AddEditElement(
        _("FROMDOMAIN"),
        "fromdomain",
        "",
        "size=40 maxlength=40",
        null,
        _("Insert the fromdomain")
    );


    $HD_Form->AddEditElement(
        _("INSECURE"),
        "insecure",
        "",
        "size=20 maxlength=30",
        null,
        _("Insert the insecure")
    );


    $HD_Form->AddEditElement(
        _("MAILBOX"),
        "mailbox",
        "",
        "size=60 maxlength=50",
        null,
        _("Insert the mailbox")
    );



    $HD_Form->AddEditElement(
        _("MASK"),
        "mask",
        "",
        "size=60 maxlength=50",
        null,
        _("Insert the mask")
    );


    $HD_Form->AddEditElement(
        _("PICKUPGROUP"),
        "pickupgroup",
        "",
        "size=15 maxlength=10",
        null,
        _("Insert the pickupgroup")
    );


    $HD_Form->AddEditElement(
        _("RESTRICTCID"),
        "restrictcid",
        "",
        "size=7 maxlength=1",
        null,
        _("Insert the restrictcid")
    );


    $HD_Form->AddEditElement(
        _("RTPTIMEOUT"),
        "rtptimeout",
        "",
        "size=7 maxlength=3",
        null,
        _("Insert the rtptimeout")
    );


    $HD_Form->AddEditElement(
        _("RTPHOLDTIMEOUT"),
        "rtpholdtimeout",
        "",
        "size=7 maxlength=3",
        null,
        _("Insert the rtpholdtimeout")
    );


    $HD_Form->AddEditElement(
        _("MUSICONHOLD"),
        "musiconhold",
        "",
        "size=50 maxlength=40",
        null,
        _("Insert the musiconhold")
    );


    $HD_Form->AddEditElement(
        _("CANCALLFORWARD"),
        "cancallforward",
        _("cancallforward = yes | no ( default : yes )"),
        "size=7 maxlength=3 $CANCALLFORWARD_default",
        null,
        _("Insert the cancallforward")
    );


    $HD_Form->AddEditElement(
        _("DEFAULTUSER"),
        "defaultuser",
        "",
        "size=10 maxlength=10"
    );


    $HD_Form->AddEditElement(
        _("SUBSCRIBEMWI"),
        "subscribemwi",
        "",
        "size=10 maxlength=10"
    );


    $HD_Form->AddEditElement(
        _("VMEXTEN"),
        "vmexten",
        "",
        "size=20 maxlength=20"
    );


    $HD_Form->AddEditElement(
        _("CALLINGPRES"),
        "callingpres",
        "",
        "size=20 maxlength=20"
    );


    $HD_Form->AddEditElement(
        _("USEREQPHONE"),
        "usereqphone",
        "",
        "size=10 maxlength=10"
    );


    $HD_Form->AddEditElement(
        _("INCOMINGLIMIT"),
        "incominglimit",
        "",
        "size=10 maxlength=10"
    );


    $HD_Form->AddEditElement(
        _("SUBSCRIBECONTEXT"),
        "subscribecontext",
        "",
        "size=40 maxlength=40"
    );


    $HD_Form->AddEditElement(
        _("MUSICCLASS"),
        "musicclass",
        "",
        "size=20 maxlength=20"
    );


    $HD_Form->AddEditElement(
        _("ALLOWTRANSFER"),
        "allowtransfer",
        "",
        "size=20 maxlength=20"
    );


    $HD_Form->AddEditElement(
        _("AUTOFRAMING"),
        "autoframing",
        "",
        "size=10 maxlength=10"
    );


    $HD_Form->AddEditElement(
        _("MAXCALLBITRATE"),
        "maxcallbitrate",
        "",
        "size=15 maxlength=15"
    );


    $HD_Form->AddEditElement(
        _("OUTBOUNDPROXY"),
        "outboundproxy",
        "",
        "size=40 maxlength=40"
    );


    $HD_Form->AddEditElement(
        _("REGSERVER"),
        "regserver",
        "",
        "size=20 maxlength=20"
    );


    $HD_Form->AddEditElement(
        _("RTPKEEPALIVE"),
        "rtpkeepalive",
        "",
        "size=15 maxlength=15"
    );

} else {

    $HD_Form->AddEditElement(
        _("TRUNK"),
        "trunk",
        _("iax trunking = yes | no ( default : yes )"),
        "size=7 maxlength=3 $TRUNK_default",
        null,
        _("Define the iax trunking")
    );


    $HD_Form->AddEditElement(
        _("DBSECRET"),
        "dbsecret",
        "",
        "size=40 maxlength=40"
    );


    $HD_Form->AddEditElement(
        _("REGCONTEXT"),
        "regcontext",
        "",
        "size=40 maxlength=40"
    );


    $HD_Form->AddEditElement(
        _("SOURCEADDRESS"),
        "sourceaddress",
        "",
        "size=20 maxlength=10"
    );

    $HD_Form->AddEditElement(
        _("MOHINTERPRET"),
        "mohinterpret",
        "",
        "size=20 maxlength=20"
    );


    $HD_Form->AddEditElement(
        _("INKEYS"),
        "inkeys",
        "",
        "size=40 maxlength=40"
    );


    $HD_Form->AddEditElement(
        _("OUTKEY"),
        "outkey",
        "",
        "size=40 maxlength=40"
    );

    $HD_Form->AddEditElement(
        _("SENDANI"),
        "sendani",
        "",
        "size=10 maxlength=10"
    );

    $HD_Form->AddEditElement(
        _("FULLNAME"),
        "fullname",
        "",
        "size=40 maxlength=40"
    );


    $HD_Form->AddEditElement(
        _("MAXAUTHREQ"),
        "maxauthreq",
        "",
        "size=15 maxlength=15"
    );


    $HD_Form->AddEditElement(
        _("ENCRYPTION"),
        "encryption",
        "",
        "size=20 maxlength=20"
    );


    $HD_Form->AddEditElement(
        _("TRANSFER"),
        "transfer",
        "",
        "size=10 maxlength=10"
    );


    $HD_Form->AddEditElement(
        _("JITTERBUFFER"),
        "jitterbuffer",
        "",
        "size=10 maxlength=10"
    );


    $HD_Form->AddEditElement(
        _("FORCEJITTERBUFFER"),
        "forcejitterbuffer",
        "",
        "size=10 maxlength=10"
    );


    $HD_Form->AddEditElement(
        _("CODECPRIORITY"),
        "codecpriority",
        "",
        "size=40 maxlength=40"
    );


    $HD_Form->AddEditElement(
        _("QUALIFYSMOOTHING"),
        "qualifysmoothing",
        "",
        "size=10 maxlength=10"
    );


    $HD_Form->AddEditElement(
        _("QUALIFYFREQOK"),
        "qualifyfreqok",
        "",
        "size=10 maxlength=10"
    );


    $HD_Form->AddEditElement(
        _("QUALIFYFREQNOTOK"),
        "qualifyfreqnotok",
        "",
        "size=10 maxlength=10"
    );


    $HD_Form->AddEditElement(
        _("TIMEZONE"),
        "timezone",
        "",
        "size=20 maxlength=20"
    );


    $HD_Form->AddEditElement(
        _("ADSI"),
        "adsi",
        "",
        "size=10 maxlength=10"
    );


    $HD_Form->AddEditElement(
        _("REQUIRE CALL TOKEN"),
        "requirecalltoken",
        _("No supported by Realtime"),
        "size=15 maxlength=20"
    );


    $HD_Form->AddEditElement(
        _("MAX CALL NUMBERS"),
        "maxcallnumbers",
        _("No supported by Realtime"),
        "size=10 maxlength=10"
    );


    $HD_Form->AddEditElement(
        _("MAX CALL NUMBERS NON VALIDATED"),
        "maxcallnumbers_nonvalidated",
        _("No supported by Realtime"),
        "size=10 maxlength=10"
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

