<?php

use A2billing\Forms\FormHandler;

/**
 * @var string $form_action
 */

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

if (!has_rights(ACX_ADMINISTRATOR)) {
    header("HTTP/1.0 401 Unauthorized");
    header("Location: PP_error.php?c=accessdenied");
    die();
}

getpost_ifset([
    'id', 'groupID', 'userid', 'login','country','city', 'pwd_encoded', 'perms', 'name', 'direction',
    'zipcode', 'state', 'phone', 'fax', 'popup_select', 'popup_formname', 'popup_fieldname', 'email',
]);
/**
 * @var string $id
 * @var string $groupID
 * @var string $userid
 * @var string $login
 * @var string $country
 * @var string $city
 * @var string $pwd_encoded
 * @var string $perms
 * @var string $name
 * @var string $direction
 * @var string $zipcode
 * @var string $state
 * @var string $phone
 * @var string $fax
 * @var string $popup_select
 * @var string $popup_formname
 * @var string $popup_fieldname
 * @var string $email
 */
if ($groupID !== "0" && $groupID !== "1") {
    header("HTTP/1.0 400 Bad Request");
	die();
}
$subadmin = $groupID > 0;

$HD_Form = new FormHandler("cc_ui_authen", _($subadmin ? _("Sub-administrator") : _("Administrator")), "userid");

$HD_Form->no_debug();
$HD_Form->FG_TABLE_DEFAULT_ORDER = " datecreation";
$HD_Form->FG_TABLE_DEFAULT_SENS = "ASC";

$HD_Form->FG_LIST_ADDING_BUTTON1 = true;
$HD_Form->FG_LIST_ADDING_BUTTON_LINK1 = "A2B_entity_user.php?form_action=ask-add&groupID=$groupID&section=$_SESSION[menu_section]";
$HD_Form->FG_LIST_ADDING_BUTTON_MSG1 = $subadmin ? _("Add ADMIN ACL") : _("Add ADMINISTRATOR");
$HD_Form->FG_LIST_ADDING_BUTTON_IMG1 = $subadmin
    ? "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAJlSURBVDjLpZNrSJNRGMdlr7qat+zKaoYhjqYphRCWCtKFoCj60CeRpDB0djHUSMOZm0JhGEmlNhczXXunDgy7TNcVcsjGpk6dNsiJyyjWNg3ZnJfef69vMBBkFn34wXMu/995DocTBCDof1h1cvBJnM5RTsBVyYLzBgvfigjopbGDfyUwK+Nfu2RsTNcTDO5aAk4RC1/KQ2BqjetbU+AiOZip/xNyLndQSeCHmMBUIQFzTjDWFDiu0O0qzmJKU4OvPSmYuETAXhKM8WshsOYR0NZlRAUUtOXt+Dk99hYufSu+6x7D8fEAnLozmLEq0V3M8ww1F4QGFEhz+Aa3QQmHsQPeQZJGxdRuEwnp+SRjwCs0FpwIf3guwfayKBE+owxzI50M3oGn0JbuQW323vE7uac2rSpoFB6Pll/M0FjEofDZe2Go2ocu0VGG5dpjUWOEXpPlp72X5h/irhBIcrNYNunp5s+31gFTWmCsAfiQDWiOgXq2H1Q7H1TPSVCfmjBaHY4HFzJfNOQd5vgFZGHmo5n7bEBfQlPMBNGVCqgTQZGxWGjhwivbCKorHb/UybDf5UFekE76Bf3lu5ccz0uxpIgBOvgMlGoXPeZhvnkbHY7GbEMYnHVseKQb4OquQF+JYMEvMIsElFsroTfQL/TqCBYVOzHfsh0++RZ4mqIxJ98Kj2wzc7qtJhLTb6pguJ5A+QXDLZfLTGXxi45762G7TUs6BKtirWZjWByG/opkH52pWvEKEyphRK8oLan9aurkgCSGslRHYVTCwQjNkDgSpptcqrMwafZd2cGUyTZhRMDf+C/8Blefvm4GxFC9AAAAAElFTkSuQmCC"
    : "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAJUSURBVDjLpZNbaJJhHMZHUES51SoiqssWm5SMQekoFwvnYR4RP02dh/yMMhJpW3PaFOuzURfNi8GUDusw3QGDgl0k0Y3QcbjVqtsY3cVAu9uMwqf3ey+EwXBFFw/vgef58X94eesA1P2P1r284DDfDp/ajUhHPQZOCuBr3wXWrLv/VwAf64pFtM0YO3sUN1U7MOo+gr4OAdzSA2Cd1pENASGjGKO2JgyQ0A3TIRJuQJw5DF/HXhha91Q2BJw/3ojLaiHGr2gwwp6A/VgjrhqbYW0/CKZtJ0b6zmyvCRhU7ltdfH4XxfcT+P76AeYf9ePrs2tYmB1DVLP/56eHF7fUBCQcre9Kc5NYLmSx8nGKaJruS/NTuOMRFWpWaJP7tkql0ux4oBPlwj2sfnlKtfIhg8mBTojF4iei0+e2rQtQKpUNKpWKU6vVSKVS6OnpwcQQQ6XRaOidTqfj93HiFawBkOCmYDB4izfypmQyCavVimw2i0wmA5PJhOHhYXg8HnR3d1dkMtkggWyuAkjwEsuySKfTMBgMMBqNsNvtyOVyyOfzsFgs0Gq1sNlsiEajcLvdFblc3lcFLAw1/16eHUQ4HAbHcdTkcDhAJqPjMwwDr9cLl8sFv9+PYi6Kt/0t5SpgMdJSKb24Tg2JRIKCSCVq5iv19vYiEAggHo9T2I+XHOaCwkoV8PmxPzQfavrldDqh1+upkVcsFqP9+an4M+mPrq4uLERFZZLh1rzC0rSvnnRsIs/4ivRdIuOXFQoFePEhshYlEskbs9ks/Dbjq6/5G/9FfwAGy37p9rgYIQAAAABJRU5ErkJggg==";

$HD_Form->FG_QUERY_WHERE_CLAUSE = "groupid=$groupID";

$HD_Form->AddViewElement(_("USERID"), "UserID");
$HD_Form->AddViewElement(_("DATE"), "datecreation", true, 19, "display_dateformat");
$HD_Form->AddViewElement(_("LOGIN"), "login");

$HD_Form->FieldViewElement('userid, datecreation, login');

if ($popup_select) {
    $HD_Form->FG_OTHER_BUTTON1 = true;
    $HD_Form->FG_OTHER_BUTTON1_ALT = '<font color="red">&lt;select&gt;</font>';
    $HD_Form->FG_OTHER_BUTTON1_IMG = '';
    $HD_Form->FG_OTHER_BUTTON1_LINK = "javascript:sendValue('|param|');";
    exit;
}

$HD_Form->FG_ENABLE_ADD_BUTTON = true;
$HD_Form->FG_ENABLE_INFO_BUTTON = true;
$HD_Form->FG_INFO_BUTTON_LINK = "A2B_admin_info.php?groupID=$groupID&id=";

if (!has_rights(ACX_MODIFY_ADMINS)) {
    exit;
}

$HD_Form->FG_ENABLE_DELETE_BUTTON = true;
$HD_Form->FG_ENABLE_EDIT_BUTTON = true;

if ($form_action !== "ask-add" && $form_action !== "add") {
    $HD_Form->AddEditElement(
        _("USERID"),
        "userid",
        "",
        "size=8 READONLY maxlength=6",
        4,
        _("Insert the userid")
    );
}

$HD_Form->AddEditElement(
    _("LOGIN"),
    "login",
    "",
    "size=30 maxlength=12",
    3,
    _("Insert the username")
);

$HD_Form->AddEditElement(
    _("PASSWORD"),
    "pwd_encoded",
    "",
    "type=password size=30 maxlength=30",
    3,
    _("Insert the Reseller password")
);

if ($subadmin) {
    $rights = [
        _("CUSTOMERS"),
        _("BILLING"),
        _("RATECARD"),
        _("TRUNK"),
        _("CALL REPORT"),
        _("CRONT SERVICE"),
        _("AGENT / ADMIN"),
        _("MAINTENANCE"),
        _("MAIL"),
        _("DID"),
        _("CALL BACK"),
        _("OUTBOUND CID"),
        _("PACKAGE OFFER"),
        _("PREDICTIVE DIALER"),
        _("INVOICES"),
        _("SUPPORT"),
        _("DASHBOARD"),
        _("SYSTEM SETTINGS"),
        _("EDIT/DELETE REFILLS"),
        _("EDIT/DELETE PAYMENTS"),
        _("EDIT/DELETE CUSTOMERS"),
        _("DELETE NOTIFICATIONS"),
        _("DELETE CDR"),
        _("EDIT/DELETE ADMIN"),
        _("EDIT/DELETE AGENT"),
    ];
    foreach ($rights as $k => $v) {
        $right_list[] = [$v, pow(2, $k)];
    }

    $len_right_list = count($right_list);

    $HD_Form->AddEditSelect(
        "perms",
        $right_list,
        _("PERMISSION"),
        _("To select several rights in the above ListBox, hold the Control/⌘ key down while clicking the items"),
        "",
        "size='$len_right_list' multiple='multiple'"
    );
}

$HD_Form->AddEditElement(
    _("NAME"),
    "name",
    "",
    "size=60 maxlength=50",
    null,
    _("Insert the name")
);

$HD_Form->AddEditElement(
    _("ADDRESS"),
    "direction",
    "",
    "size=60 maxlength=250",
    null,
    _("Insert the direction")
);

$HD_Form->AddEditElement(
    _("CITY"),
    "city",
    "",
    "size=30 maxlength=30",
    null,
    _("Insert the country")
);

$HD_Form->AddEditElement(
    _("ZIPCODE"),
    "zipcode",
    "",
    "size=10 maxlength=7",
    null,
    _("Insert the zip code")
);

$HD_Form->AddEditElement(
    _("STATE"),
    "state",
    "",
    "size=30 maxlength=30",
    null,
    _("Insert the state")
);

$HD_Form->AddEditElement(
    _("COUNTRY"),
    "country",
    "",
    "size=30 maxlength=30",
    null,
    _("Insert the country")
);

$HD_Form->AddEditElement(
    _("PHONE"),
    "phone",
    "",
    "size=30 maxlength=20",
    null,
    _("Insert the telephone number")
);

$HD_Form->AddEditElement(
    _("FAX"),
    "fax",
    "",
    "size=30 maxlength=20",
    null,
    _("Insert the fax number")
);

$HD_Form->AddEditElement(
    _("EMAIL"),
    "email",
    "",
    "size=30 maxlength=70",
    null,
    _("Insert the email"));

$FG_QUERY_EDITION = "";
if ($form_action !== "ask-add" && $form_action !== "add") {
        $FG_QUERY_EDITION = 'userid, ';
}

$FG_QUERY_EDITION .='login, pwd_encoded,  ';

if ($groupID) {
    $FG_QUERY_EDITION .= 'perms, ';
}

$FG_QUERY_EDITION .= 'name, direction,city, zipcode, state,country, phone, fax,email';

$HD_Form->FieldEditElement($FG_QUERY_EDITION);

$HD_Form->FG_EDIT_QUERY_CONDITION = " userid='%id'";

$HD_Form->FG_ADD_QUERY_HIDDEN_INPUTS["groupID"] = $groupID;
$HD_Form->FG_EDIT_QUERY_HIDDEN_INPUTS["groupID"] = $groupID;

if (!$subadmin) {
    $HD_Form->FG_ADD_QUERY_HIDDEN_INPUTS["perms"] = pow(2, 32) - 1;
    $HD_Form->FG_EDIT_QUERY_HIDDEN_INPUTS["perms"] = pow(2, 32) - 1;
}

$HD_Form->FG_INTRO_TEXT_ADITION = '';

$HD_Form->FG_LOCATION_AFTER_ADD = "?atmenu=user&groupID=$groupID&stitle=Administrator+management&id=";
$HD_Form->FG_LOCATION_AFTER_EDIT = "?atmenu=user&groupID=$groupID&stitle=Administrator+management&id=";
$HD_Form->FG_LOCATION_AFTER_DELETE = "?atmenu=user&groupID=$groupID&stitle=Administrator+management&id=";
