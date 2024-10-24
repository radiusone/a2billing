<?php

use A2billing\Agent;
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

if (! has_rights (Agent::ACX_ACCESS)) {
    Header ("HTTP/1.0 401 Unauthorized");
    Header ("Location: PP_error.php?c=accessdenied");
    die();
}



getpost_ifset(array('voip_type', 'id_cc_card', 'cardnumber', 'id', 'name', 'amaflags', 'callgroup', 'accountcode', 'callerid', 'canreinvite', 'context', 'defaultip', 'dtmfmode',
'fromuser', 'fromdomain', 'host', 'insecure', 'language', 'mailbox', 'md5secret', 'nat', 'permit', 'deny', 'mask', 'pickupgroup', 'port', 'qualify',
'restrictcid', 'rtptimeout', 'rtpholdtimeout', 'secret', 'type', 'username', 'disallow', 'allow', 'musiconhold', 'regseconds', 'ipaddr', 'cancallforward', 'regexten','friend_id'));

getpost_ifset(array('defaultuser', 'subscribemwi', 'vmexten', 'cid_number', 'callingpres', 'usereqphone', 'incominglimit', 'subscribecontext', 'musicclass', 'mohsuggest',
'allowtransfer', 'autoframing', 'maxcallbitrate', 'outboundproxy', 'regserver', 'rtpkeepalive', 'dbsecret ', 'regcontext ', 'sourceaddress ', 'mohinterpret ',
'inkeys ', 'outkey ', ' sendani ', 'fullname ', ' maxauthreq ', 'encryption ', 'transfer ', 'jitterbuffer ', 'forcejitterbuffer ', 'codecpriority ', 'qualifysmoothing ',
'qualifyfreqok ', 'qualifyfreqnotok ', 'timezone', 'adsi'));


if ($voip_type=='sip') {
	if (!isset($form_action) || $form_action=="list") {
		$HD_Form = new FormHandler("cc_sip_buddies LEFT JOIN cc_card ON cc_card.id=cc_sip_buddies.id_cc_card LEFT JOIN cc_card_group ON cc_card.id_group=cc_card_group.id", "SIP Friend", 'cc_sip_buddies.id');
		$HD_Form -> FG_QUERY_WHERE_CLAUSE = " cc_card_group.id_agent=".$_SESSION['agent_id'] ;
        $HD_Form->list_query_conditions["cc_card_group.id_agent"] = $_SESSION["agent_id"];
	} else {
		$HD_Form = new FormHandler("cc_sip_buddies", "SIP Friend");
	}
	$table_name = "cc_sip_buddies";

} else {

	if (!isset($form_action) || $form_action=="list") {
		$HD_Form = new FormHandler("cc_iax_buddies LEFT JOIN cc_card ON cc_card.id=cc_iax_buddies.id_cc_card LEFT JOIN cc_card_group ON cc_card.id_group=cc_card_group.id", "IAX Friend", 'cc_iax_buddies.id');
		$HD_Form -> FG_QUERY_WHERE_CLAUSE = " cc_card_group.id_agent=".$_SESSION['agent_id'] ;
        $HD_Form->list_query_conditions["cc_card_group.id_agent"] = $_SESSION["agent_id"];
	} else {
		$HD_Form = new FormHandler("cc_iax_buddies", "IAX Friend");
	}
	$table_name = "cc_iax_buddies";
}


$HD_Form->no_debug();
$typepaid_list = getPaidTypeList();
$actived_list =  getActivationTrueFalseList();
$yesno = getYesNoList();



$HD_Form -> AddViewElement(gettext("CARDHOLDER"), "id_cc_card", true, 25, "", "lie", "cc_card", "lastname, firstname", "id='%id'", "%1 %2");
$HD_Form -> AddViewElement(gettext("USERNAME"), "username", true, 30, "display_customer_link");
$HD_Form -> AddViewElement(gettext("ACCOUNTCODE"), "accountcode");
$HD_Form -> AddViewElement(gettext("SECRET"), "secret");
$HD_Form -> AddViewElement(gettext("CALLERID"), "callerid");
$HD_Form -> AddViewElement(gettext("CONTEXT"), "context");
$HD_Form -> AddViewElement(gettext("DEFAULTIP"), "defaultip");

$HD_Form ->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form ->FG_LIST_ADDING_BUTTON_LINK1 = "A2B_entity_friend.php?form_action=ask-add&voip_type=sip&section=1";
if ($voip_type=='sip') {
	$HD_Form ->FG_LIST_ADDING_BUTTON_ALT1 = $HD_Form ->FG_LIST_ADDING_BUTTON_MSG1 = gettext("Add SIP Friend");
} else {
	$HD_Form ->FG_LIST_ADDING_BUTTON_ALT1 = $HD_Form ->FG_LIST_ADDING_BUTTON_MSG1 = gettext("Add IAX Friend");
}
$HD_Form ->FG_LIST_ADDING_BUTTON_IMG1 = Images_Path ."/telephone_add.png" ;



$HD_Form -> FieldViewElement ("id_cc_card, $table_name.name, accountcode, secret, callerid, context, defaultip");

$HD_Form -> FG_LIST_VIEW_PAGE_SIZE = 10;

if (isset($friend_id)  &&  (strlen($friend_id)>0)){
	if ($friend_id!='all'){
		if (strlen($HD_Form ->FG_QUERY_WHERE_CLAUSE)>0) $HD_Form ->FG_QUERY_WHERE_CLAUSE.=" AND ";
		$HD_Form ->FG_QUERY_WHERE_CLAUSE.="name='$friend_id'";
        $HD_Form->list_query_conditions["name"] = $friend_id;
	}
}


$HD_Form -> FG_ENABLE_ADD_BUTTON = true;
$HD_Form -> FG_ENABLE_EDIT_BUTTON = true;
$HD_Form -> FG_ENABLE_DELETE_BUTTON = true;




$maxi='';
if ($form_action=="ask-add") {
	$card_gen = generate_unique_value($HD_Form->FG_QUERY_TABLE_NAME);
	$maxi = "value='".$card_gen."'";
	$pass =  MDP(6);
	$pass_secret = "value='".$pass."'";
}


if ($form_action=="ask-add" || $form_action == "add_sip" || $form_action == "add_iax") {
	$AMAFLAGS_default= "value='".$A2B->config["peer_friend"]['amaflag']."'";
	$TYPE_default= "value='friend'";
	$NAT_default= "value='yes'";
	$DTMFMODE_default= "value='RFC2833'";
	$CANREINVITE_default= "value='yes'";
	$DISALLOW_default= "value='all'";
	$ALLOW_default= "value='ulaw,alaw,gsm,g729'";
	$HOST_default= "value='dynamic'";
	$CONTEXT_default= "value='".$A2B->config["peer_friend"]['context']."'";
	$REGSECONDS_default= "value='0'";
	$CANCALLFORWARD_default= "value='yes'";
	$TRUNK_default= "value='yes'";
}

$HD_Form->AddEditPopup(
    gettext("CARD ID"),
    "id_cc_card",
    "A2B_entity_card.php?popup_select=1&",
    gettext("Define the card number ID to use."),
    "", 4, gettext("Insert the card number ID")
);

$HD_Form->AddEditElement(
    gettext("NAME"),
    "name",
    "",
    "size=40 $maxi maxlength=40",
    9,
    gettext("Insert the name")
);


$HD_Form->AddEditElement(
    gettext("ACCOUNTCODE"),
    "accountcode",
    "",
    "size=20 $maxi maxlength=50",
    null,
    gettext("Insert the accountcode")
);


$HD_Form->AddEditElement(
    gettext("REGEXTEN"),
    "regexten",
    "",
    "size=20 maxlength=20",
    null,
    gettext("Insert the regexten")
);


$HD_Form->AddEditElement(
    gettext("CALLERID"),
    "callerid",
    "",
    "size=40 maxlength=60",
    null,
    gettext("Insert the callerid")
);


$HD_Form->AddEditElement(
    gettext("CID NUMBER"),
    "cid_number",
    "",
    "size=40 maxlength=40",
    null,
    gettext("Insert the cid number")
);


$HD_Form->AddEditElement(
    gettext("AMAFLAGS"),
    "amaflags",
    "(default : billing)",
    "size=10 maxlength=7 $AMAFLAGS_default",
    null,
    gettext("Insert the amaflags")
);


$HD_Form->AddEditElement(
    gettext("SECRET"),
    "secret",
    "",
    "size=40 maxlength=40 $pass_secret",
    null,
    gettext("Insert the secret")
);


$HD_Form->AddEditElement(
    gettext("QUALIFY"),
    "qualify",
    "",
    "size=8 maxlength=7",
    null,
    gettext("Insert the qualify")
);


$HD_Form->AddEditElement(
    gettext("DISALLOW"),
    "disallow",
    gettext("need to disallow=all before we can use allow. ( default : all )"),
    "size=50 maxlength=40 $DISALLOW_default",
    null,
    gettext("Insert the disallow")
);


$HD_Form->AddEditElement(
    gettext("ALLOW"),
    "allow",
    gettext("Set allow codecs separated by a comma, e.g. gsm,alaw,ulaw ( default : ulaw,alaw,gsm,g729)"),
    "size=50 maxlength=40 $ALLOW_default",
    null,
    gettext("Insert the allow")
);


$HD_Form->AddEditElement(
    gettext("HOST"),
    "host",
    gettext("Use dynamic or set an IP ( default : dynamic )"),
    "size=40 maxlength=31 $HOST_default",
    null,
    gettext("Insert the host")
);


$HD_Form->AddEditElement(
    gettext("CONTEXT"),
    "context",
    gettext("set the context for this user (default : a2billing )"),
    "size=40 maxlength=40 $CONTEXT_default",
    null,
    gettext("Insert the context")
);


$HD_Form->AddEditElement(
    gettext("DEFAULTIP"),
    "defaultip",
    "",
    "size=20 maxlength=15",
    null,
    gettext("Insert the defaultip")
);


$HD_Form->AddEditElement(
    gettext("LANGUAGE"),
    "language",
    "",
    "size=10 maxlength=4",
    null,
    gettext("Insert the language")
);


$HD_Form->AddEditElement(
    gettext("PORT"),
    "port",
    "",
    "size=10 maxlength=5",
    null,
    gettext("Insert the port")
);


$HD_Form->AddEditElement(
    gettext("REGSECONDS"),
    "regseconds",
    "",
    "size=50 maxlength=40 $REGSECONDS_default",
    null,
    gettext("Insert the regseconds")
);


$HD_Form->AddEditElement(
    gettext("IPADDR"),
    "ipaddr",
    "",
    "size=20 maxlength=15",
    null,
    gettext("Insert the ipaddr")
);

$HD_Form->AddEditElement(
    gettext("MOHSUGGEST"),
    "mohsuggest",
    "",
    "size=20 maxlength=20",
    null,
    gettext("Insert the mohsuggest")
);


$HD_Form->AddEditElement(
    gettext("AUTH"),
    "auth",
    "",
    "size=20 maxlength=20",
    null,
    gettext("Insert the auth")
);


$HD_Form->AddEditElement(
    gettext("SETVAR"),
    "setvar",
    "",
    "size=60 maxlength=200",
    null,
    gettext("Insert the setvar")
);

$HD_Form->AddEditElement(
    gettext("TYPE"),
    "type",
    gettext("type = friend | peer | user ( default : friend )"),
    "size=10 maxlength=6 $TYPE_default",
    null,
    gettext("Insert the type")
);

$HD_Form->AddEditElement(
    gettext("DENY"),
    "deny",
    "",
    "size=60 maxlength=50",
    null,
    gettext("Insert the deny")
);

$HD_Form->AddEditElement(
    gettext("PERMIT"),
    "permit",
    "",
    "size=60 maxlength=50",
    null,
    gettext("Insert the permit"));


if ($voip_type=='sip') {

    $HD_Form->AddEditElement(
        gettext("USERNAME"),
        "username",
        "",
        "size=50 $maxi maxlength=40",
        null,
        gettext("Insert the username"));

    $HD_Form->AddEditElement(
        gettext("MD5SECRET"),
        "md5secret",
        "",
        "size=60 maxlength=50",
        null,
        gettext("Insert the md5secret")
    );


    $HD_Form->AddEditElement(
        gettext("NAT"),
        "nat",
        gettext("nat = yes | no | never | route ( default : yes )"),
        "size=60 maxlength=50 $NAT_default",
        null,
        gettext("Insert the nat")
    );


    $HD_Form->AddEditElement(
        gettext("DTMFMODE"),
        "dtmfmode",
        gettext("dtmfmode = RFC2833 | INFO | INBAND | AUTO ( default : RFC2833 )"),
        "size=10 maxlength=7 $DTMFMODE_default",
        null,
        gettext("Insert the dtmfmode")
    );



    $HD_Form->AddEditElement(
        gettext("CANREINVITE"),
        "canreinvite",
        gettext("canreinvite : yes | no ( default : yes )"),
        "size=20 maxlength=10 $CANREINVITE_default",
        null,
        gettext("Insert the canreinvite")
    );




    $HD_Form->AddEditElement(
        gettext("CALLGROUP"),
        "callgroup",
        "",
        "size=10 maxlength=10",
        null,
        gettext("Insert the callgroup")
    );




    $HD_Form->AddEditElement(
        gettext("FROMUSER"),
        "fromuser",
        "",
        "size=40 maxlength=40",
        null,
        gettext("Insert the fromuser")
    );


    $HD_Form->AddEditElement(
        gettext("FROMDOMAIN"),
        "fromdomain",
        "",
        "size=40 maxlength=40",
        null,
        gettext("Insert the fromdomain")
    );


    $HD_Form->AddEditElement(
        gettext("INSECURE"),
        "insecure",
        "",
        "size=20 maxlength=30",
        null,
        gettext("Insert the insecure")
    );


    $HD_Form->AddEditElement(
        gettext("MAILBOX"),
        "mailbox",
        "",
        "size=60 maxlength=50",
        null,
        gettext("Insert the mailbox")
    );



    $HD_Form->AddEditElement(
        gettext("MASK"),
        "mask",
        "",
        "size=60 maxlength=50",
        null,
        gettext("Insert the mask")
    );


    $HD_Form->AddEditElement(
        gettext("PICKUPGROUP"),
        "pickupgroup",
        "",
        "size=15 maxlength=10",
        null,
        gettext("Insert the pickupgroup")
    );


    $HD_Form->AddEditElement(
        gettext("RESTRICTCID"),
        "restrictcid",
        "",
        "size=7 maxlength=1",
        null,
        gettext("Insert the restrictcid")
    );


    $HD_Form->AddEditElement(
        gettext("RTPTIMEOUT"),
        "rtptimeout",
        "",
        "size=7 maxlength=3",
        null,
        gettext("Insert the rtptimeout")
    );


    $HD_Form->AddEditElement(
        gettext("RTPHOLDTIMEOUT"),
        "rtpholdtimeout",
        "",
        "size=7 maxlength=3",
        null,
        gettext("Insert the rtpholdtimeout")
    );


    $HD_Form->AddEditElement(
        gettext("MUSICONHOLD"),
        "musiconhold",
        "",
        "size=50 maxlength=40",
        null,
        gettext("Insert the musiconhold")
    );


    $HD_Form->AddEditElement(
        gettext("CANCALLFORWARD"),
        "cancallforward",
        gettext("cancallforward = yes | no ( default : yes )"),
        "size=7 maxlength=3 $CANCALLFORWARD_default",
        null,
        gettext("Insert the cancallforward")
    );


    $HD_Form->AddEditElement(
        gettext("DEFAULTUSER"),
        "defaultuser",
        "",
        "size=10 maxlength=10"
    );


    $HD_Form->AddEditElement(
        gettext("SUBSCRIBEMWI"),
        "subscribemwi",
        "",
        "size=10 maxlength=10"
    );


    $HD_Form->AddEditElement(
        gettext("VMEXTEN"),
        "vmexten",
        "",
        "size=20 maxlength=20"
    );


    $HD_Form->AddEditElement(
        gettext("CALLINGPRES"),
        "callingpres",
        "",
        "size=20 maxlength=20"
    );


    $HD_Form->AddEditElement(
        gettext("USEREQPHONE"),
        "usereqphone",
        "",
        "size=10 maxlength=10"
    );


    $HD_Form->AddEditElement(
        gettext("INCOMINGLIMIT"),
        "incominglimit",
        "",
        "size=10 maxlength=10"
    );


    $HD_Form->AddEditElement(
        gettext("SUBSCRIBECONTEXT"),
        "subscribecontext",
        "",
        "size=40 maxlength=40"
    );


    $HD_Form->AddEditElement(
        gettext("MUSICCLASS"),
        "musicclass",
        "",
        "size=20 maxlength=20"
    );


    $HD_Form->AddEditElement(
        gettext("ALLOWTRANSFER"),
        "allowtransfer",
        "",
        "size=20 maxlength=20"
    );


    $HD_Form->AddEditElement(
        gettext("AUTOFRAMING"),
        "autoframing",
        "",
        "size=10 maxlength=10"
    );


    $HD_Form->AddEditElement(
        gettext("MAXCALLBITRATE"),
        "maxcallbitrate",
        "",
        "size=15 maxlength=15"
    );


    $HD_Form->AddEditElement(
        gettext("OUTBOUNDPROXY"),
        "outboundproxy",
        "",
        "size=40 maxlength=40"
    );


    $HD_Form->AddEditElement(
        gettext("REGSERVER"),
        "regserver",
        "",
        "size=20 maxlength=20"
    );


    $HD_Form->AddEditElement(
        gettext("RTPKEEPALIVE"),
        "rtpkeepalive",
        "",
        "size=15 maxlength=15"
    );

} else {

    $HD_Form->AddEditElement(
        gettext("TRUNK"),
        "trunk",
        gettext("iax trunking = yes | no ( default : yes )"),
        "size=7 maxlength=3 $TRUNK_default",
        null,
        gettext("Define the iax trunking")
    );


    $HD_Form->AddEditElement(
        gettext("DBSECRET"),
        "dbsecret",
        "",
        "size=40 maxlength=40"
    );


    $HD_Form->AddEditElement(
        gettext("REGCONTEXT"),
        "regcontext",
        "",
        "size=40 maxlength=40"
    );


    $HD_Form->AddEditElement(
        gettext("SOURCEADDRESS"),
        "sourceaddress",
        "",
        "size=20 maxlength=10"
    );

    $HD_Form->AddEditElement(
        gettext("MOHINTERPRET"),
        "mohinterpret",
        "",
        "size=20 maxlength=20"
    );


    $HD_Form->AddEditElement(
        gettext("INKEYS"),
        "inkeys",
        "",
        "size=40 maxlength=40"
    );


    $HD_Form->AddEditElement(
        gettext("OUTKEY"),
        "outkey",
        "",
        "size=40 maxlength=40"
    );

    $HD_Form->AddEditElement(
        gettext("SENDANI"),
        "sendani",
        "",
        "size=10 maxlength=10"
    );

    $HD_Form->AddEditElement(
        gettext("FULLNAME"),
        "fullname",
        "",
        "size=40 maxlength=40"
    );


    $HD_Form->AddEditElement(
        gettext("MAXAUTHREQ"),
        "maxauthreq",
        "",
        "size=15 maxlength=15"
    );


    $HD_Form->AddEditElement(
        gettext("ENCRYPTION"),
        "encryption",
        "",
        "size=20 maxlength=20"
    );


    $HD_Form->AddEditElement(
        gettext("TRANSFER"),
        "transfer",
        "",
        "size=10 maxlength=10"
    );


    $HD_Form->AddEditElement(
        gettext("JITTERBUFFER"),
        "jitterbuffer",
        "",
        "size=10 maxlength=10"
    );


    $HD_Form->AddEditElement(
        gettext("FORCEJITTERBUFFER"),
        "forcejitterbuffer",
        "",
        "size=10 maxlength=10"
    );


    $HD_Form->AddEditElement(
        gettext("CODECPRIORITY"),
        "codecpriority",
        "",
        "size=40 maxlength=40"
    );


    $HD_Form->AddEditElement(
        gettext("QUALIFYSMOOTHING"),
        "qualifysmoothing",
        "",
        "size=10 maxlength=10"
    );


    $HD_Form->AddEditElement(
        gettext("QUALIFYFREQOK"),
        "qualifyfreqok",
        "",
        "size=10 maxlength=10"
    );


    $HD_Form->AddEditElement(
        gettext("QUALIFYFREQNOTOK"),
        "qualifyfreqnotok",
        "",
        "size=10 maxlength=10"
    );


    $HD_Form->AddEditElement(
        gettext("TIMEZONE"),
        "timezone",
        "",
        "size=20 maxlength=20"
    );


    $HD_Form->AddEditElement(
        gettext("ADSI"),
        "adsi",
        "",
        "size=10 maxlength=10"
    );
}

$HD_Form -> FG_FILTER_ENABLE = true;
$HD_Form -> FG_FILTER_COLUMN = 'name';
$HD_Form -> FG_FILTER_LABEL = 'username';

$HD_Form -> FG_INTRO_TEXT_EDITION= '';
$HD_Form -> FG_INTRO_TEXT_ADITION = '';

$HD_Form -> FG_LOCATION_AFTER_ADD = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?voip_type=$voip_type&id=";
$HD_Form -> FG_LOCATION_AFTER_EDIT = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?voip_type=$voip_type&id=";
$HD_Form -> FG_LOCATION_AFTER_DELETE = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?voip_type=$voip_type&id=";
