<?php

use A2billing\Forms\FormHandler;
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
if (!has_rights(ACX_ACXSETTING)) {
    header("HTTP/1.0 401 Unauthorized");
    header("Location: PP_error.php?c=accessdenied");
    die();
}

getpost_ifset(['id', 'group_title', 'group_description','agi_conf', 'groupdelete', 'agi_conf', 'from_conf']);
/**
 * @var string $id
 * @var string $group_title
 * @var string $group_description
 * @var string $agi_conf
 * @var string $groupdelete
 * @var string $agi_conf
 * @var string $from_conf
 */

$setbool = false;
$handle = DbConnect();
$instance_table = new Table();

if ( !empty($groupdelete) && substr($groupdelete,0,8) == "agi-conf" && $groupdelete != "agi-conf1" && strlen($groupdelete) < 11) {
	$QUERY =  "DELETE FROM cc_config_group WHERE group_title='$groupdelete'";
	$result = $instance_table -> SQLExec ($handle, $QUERY);
	$QUERY =  "DELETE FROM cc_config WHERE config_group_title='$groupdelete'";
	$result = $instance_table -> SQLExec ($handle, $QUERY);

	$msg = $groupdelete.' : '."Group deleted !";
	$error_msg = '<font face="Arial, Helvetica, sans-serif" size="2" color="green"><b>'.$msg.'</b></font><br>';
} elseif (!empty($agi_conf)) {
    add_agi_confx($agi_conf, $from_conf);
}

$HD_Form = new FormHandler("cc_config_group", "configuration group");

$HD_Form->no_debug();
$HD_Form -> FG_TABLE_DEFAULT_ORDER = "id";
$HD_Form -> FG_TABLE_DEFAULT_SENS = "ASC";

$HD_Form -> AddViewElement(gettext("TITLE"), "group_title");
$HD_Form -> AddViewElement(gettext("DESCRIPTION"), "group_description");

$HD_Form -> FieldViewElement ('group_title, group_description');

$HD_Form -> FG_LIST_VIEW_PAGE_SIZE = 15;

$HD_Form -> FG_ENABLE_EDIT_BUTTON = false;
$HD_Form -> FG_ENABLE_ADD_BUTTON = false;
$HD_Form -> FG_ENABLE_DELETE_BUTTON = true;
$HD_Form -> FG_DELETE_BUTTON_CONDITION = 'substr("|col0|",0,8) == "agi-conf" && "|col0|" != "agi-conf1"';

$HD_Form -> FG_OTHER_BUTTON1 = true;
$HD_Form -> FG_OTHER_BUTTON1_LINK = "A2B_entity_config.php?groupselect=|col0|";
$HD_Form -> FG_OTHER_BUTTON1_ALT = gettext('DETAILS');

$HD_Form -> FG_OTHER_BUTTON2 = true;
$HD_Form -> FG_OTHER_BUTTON2_LINK = "A2B_entity_config_group.php?groupdelete=|col0|";
$HD_Form -> FG_OTHER_BUTTON2_IMG = Images_Path."/delete.png";
$HD_Form -> FG_OTHER_BUTTON2_HTML_ID = "|col0|";
$HD_Form -> FG_OTHER_BUTTON2_CONDITION = 'substr("|col0|",0,8) == "agi-conf" && "|col0|" != "agi-conf1"';
$HD_Form -> FG_OTHER_BUTTON2_ALT = gettext('DELETE THIS AGI CONF');

$HD_Form -> FG_INTRO_TEXT_EDITION = '';
$HD_Form -> FG_INTRO_TEXT_ADITION = '';

$HD_Form -> FG_LOCATION_AFTER_ADD = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?atmenu=document&stitle=Document&wh=AC&id=";
$HD_Form -> FG_LOCATION_AFTER_EDIT = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?atmenu=document&stitle=Document&wh=AC&id=";
$HD_Form -> FG_LOCATION_AFTER_DELETE = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?atmenu=document&stitle=Document&wh=AC&id=";

function add_agi_confx(string $group_title, ?string $from_conf): void
{
    $group_desc = 'This configuration group handles the AGI Configuration.';
    $handle = DbConnect();
    $handle->Execute("INSERT INTO cc_config_group (group_title, group_description) VALUES (?, ?)", [$group_title, $group_desc]);
    if (!empty($from_conf)) {
        $handle->Execute(
            "INSERT INTO cc_config_group (config_title, config_key, config_value, config_description, config_valuetype, config_group_title, config_listvalues)
            SELECT config_title, config_key, config_value, config_description, config_valuetype, ?, config_listvalues FROM cc_config WHERE config_group_title = ?",
            [$group_title, $from_conf]
        );
    }
}

function agi_confx_title(): array
{
    $handle = DbConnect();

    $QUERY = "SELECT id, group_title, group_description FROM cc_config_group WHERE group_title like 'agi-conf%' ORDER BY group_title";
    $result = $handle->Execute($QUERY);
    $result = $result ? $result->GetAll() : [];

    $agi_number = [];
    foreach ($result as $row) {
        $agi_number[] = (int)str_replace("agi-conf", "", $row["group_title"]);
    }

    $first_group_title = $result[0]["group_title"];
    $agi_conf_number = max($agi_number) + 1;

    return ["agi-conf$agi_conf_number", $first_group_title];
}
