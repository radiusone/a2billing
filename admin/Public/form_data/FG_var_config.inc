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

if (! has_rights (ACX_ADMINISTRATOR)) {
    Header ("HTTP/1.0 401 Unauthorized");
    Header ("Location: PP_error.php?c=accessdenied");
    die();
}

getpost_ifset(array('id', 'config_title', 'config_key', 'config_value', 'config_description', 'config_group_title', 'groupselect', 'filterKey', 'rbKey', 'filterDescription', 'rbDesc', 'filterValue', 'rbValue', 'posted','agi_conf'));

// This will generate agi-confx
// agi-confx is duplication of Global configuration and list of configurations of : agi-conf1
if ($agi_conf) {
	$groupselect = add_agi_confx();
	unset($agi_conf);
}

$setbool = false;
$handle = DbConnect();
$instance_table = new Table();

if ($form_action == "ask-edit") {
	$QUERY =  "SELECT config_value, config_listvalues FROM cc_config where id = $id";
	$result = $instance_table -> SQLExec ($handle, $QUERY);
	if ($result[0][1] == "yes,no" || $result[0][1] == "no,yes") {
		$setbool = true;
	}
}

$value_list = array();
$value_list["1"] = array( gettext("yes"), 1);
$value_list["2"] = array( gettext("no"), 0);

$yesno = array();

$instance_config_table = new Table("cc_config", "config_value, config_valuetype, id");
$config_list = $instance_config_table -> get_list ($handle, "", ["id"]);

foreach($config_list as $key => $cur_value) {
	if($cur_value[1] != '1') {
		$yesno[$cur_value[2]] = array($cur_value[0],$cur_value[2]);
	} else {
		if($cur_value[0] == '1') {
			$yesno[$cur_value[2]] = array("Yes",$cur_value[2]);
		} else if($cur_value[0] == '0') {
			$yesno[$cur_value[2]] = array("No",$cur_value[2]);
		} else {
			$yesno[$cur_value[2]] = array($cur_value[0],$cur_value[2]);
		}
	}
}
$HD_Form = new FormHandler("cc_config", gettext("Configuration"));
$HD_Form->no_debug();
$HD_Form -> FG_TABLE_DEFAULT_ORDER = "config_group_title";
$HD_Form -> FG_TABLE_DEFAULT_SENS = "ASC";

if ($groupselect != "-1" && $groupselect != "") {
		$HD_Form -> FG_QUERY_WHERE_CLAUSE = " config_group_title = '$groupselect'";
	$_SESSION['ss_groupselect'] = $HD_Form -> FG_QUERY_WHERE_CLAUSE;
	$_SESSION['grpselect'] = $groupselect;
}

if (!empty($filterKey)) {
	switch ($rbKey) {
		case 1 : $filterKey = " = '$filterKey'"; break;
		case 2 : $filterKey = " LIKE '$filterKey%'"; break;
		case 3 : $filterKey = " LIKE '%$filterKey%'"; break;
		case 4 : $filterKey = " LIKE '%$filterKey'"; break;
	}

	$OPL = '';
	if ( ($groupselect != "-1") ) {
		$OPL = 'AND';
	}
	$HD_Form -> FG_QUERY_WHERE_CLAUSE .= " $OPL config_key $filterKey";
}
if (!empty($filterDescription)) {
	switch ($rbDesc) {
		case 1 : $filterDescription = " = '$filterDescription'"; break;
		case 2 : $filterDescription = " LIKE '$filterDescription%'"; break;
		case 3 : $filterDescription = " LIKE '%$filterDescription%'"; break;
		case 4 : $filterDescription = " LIKE '%$filterDescription'"; break;
	}

	$OPL = '';
	if ( ($groupselect != "-1") || !empty($filterKey) ) {
		$OPL = 'AND';
	}
	$HD_Form -> FG_QUERY_WHERE_CLAUSE .= " $OPL config_description $filterDescription";
}

if (!empty($filterValue)) {
	switch ($rbValue) {
		case 1 : $filterValue = " = '$filterValue'"; break;
		case 2 : $filterValue = " LIKE '$filterValue%'"; break;
		case 3 : $filterValue = " LIKE '%$filterValue%'"; break;
		case 4 : $filterValue = " LIKE '%$filterValue'"; break;
	}

	$OPL = '';
	if ( ($groupselect != "-1") || !empty($filterKey) || !empty($filterDescription) ) {
		$OPL = 'AND';
	}
	$HD_Form -> FG_QUERY_WHERE_CLAUSE .= " $OPL config_value $filterValue";
}

if (isset($posted)) {
	$_SESSION['ss_groupselect'] = '';
	$_SESSION['groupselect'] = '';
	$_SESSION['ss_groupselect'] = $HD_Form -> FG_QUERY_WHERE_CLAUSE;
}

if($form_action == "list") {
	$_SESSION['ss_groupselect'] = '';
	$_SESSION['groupselect'] = '';
} else {
	$HD_Form -> FG_QUERY_WHERE_CLAUSE = $_SESSION['ss_groupselect'];
}

$HD_Form -> FG_EDIT_QUERY_CONDITION = "id = %id";
$HD_Form -> AddViewElement(gettext("TITLE"), "config_title");
$HD_Form -> AddViewElement(gettext("KEY"), "config_key");
$HD_Form -> AddViewElement(gettext("VALUE"), "config_value", false, "", null, "list-conf", $yesno);
$HD_Form -> AddViewElement(gettext("DESCRIPTION"), "config_description");
$HD_Form -> AddViewElement(gettext("GROUP"), "config_group_title");

$HD_Form -> FieldViewElement ('config_title, config_key, config_value, config_description, config_group_title');

$HD_Form -> FG_LIST_VIEW_PAGE_SIZE = 25;

$HD_Form -> FG_ENABLE_EDIT_BUTTON = true;
$HD_Form -> FG_ENABLE_DELETE_BUTTON = false;
$HD_Form -> FG_ENABLE_ADD_BUTTON = false;
if(!$setbool) {
	$HD_Form -> FG_DISPLAY_SELECT = true;
	$HD_Form -> FG_SELECT_FIELDNAME = "config_listvalues";
	$HD_Form -> FG_CONF_VALUE_FIELDNAME = "config_value";
}

$HD_Form -> AddEditElement (gettext("GROUP"),
	"config_group_title",
	'$value',
	"INPUT",
	"size=30 readonly",
	"",
	gettext("This is the group of the configuration. Not editable!"),
	"" , "", "", "", "", "", "", gettext("Group Name of the configuration variable"));


$HD_Form -> AddEditElement (gettext("TITLE"),
	"config_title",
	'$value',
	"INPUT",
	"size=40 maxlength=100 readonly",
	"",
	gettext("Insert the TITLE"),
	"" , "", "", "", "" , "", "" ,gettext("Title of the configuration variable"));

$HD_Form -> AddEditElement (gettext("KEY"),
	"config_key",
	'$value',
	"INPUT",
	"size=60 maxlength=100 readonly",
	"",
	gettext("Insert the TITLE"),
	"" , "", "", "", "" , "", "" ,gettext("Key name of the configuration variable"));

if ($setbool) {
	$HD_Form -> AddEditElement (gettext("VALUE"),
	   "config_value",
	   "",
	   "SELECT",
	   "","","",
	   "list" , "", "",	"", $value_list, "%1", "", "" );

} else {
	$HD_Form -> AddEditElement (gettext("VALUE"),
		"config_value",
		'$value',
		"INPUT",
		"size=100 maxlength=200",
		"","",
		"" , "", "", "", "", "", "", gettext("Insert the Value"));
}

	$HD_Form -> AddEditElement (gettext("LIST OF VALUES"),
		"config_listvalues",
		'$value',
		"INPUT",
		"size=40 ",
		"",
		"",
		"" , "", "", "", "", "", "", gettext("Insert the List of values, comma separated"));

$HD_Form -> AddEditElement (gettext("DESCRIPTION"),
	"config_description",
	'',
	"SPAN",
	"cols=80 rows=10",
	"",
	gettext("Insert the Description"),
	"" , "", "", "", "" , "", "", "");


$HD_Form -> FieldEditElement ('config_group_title, config_title, config_key, config_value ,config_listvalues, config_description');


$HD_Form -> FG_INTRO_TEXT_EDITION= '';

$HD_Form -> FG_LOCATION_AFTER_EDIT = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL)."?atmenu=document&stitle=Document&wh=AC&id=";


/*
 * Following fuctions return the latest title to add as
 * agi-conf(title_number) for Global configurations and List of configurations
 * Tables : cc_confi_group
 * Operations : SELECT
 *
 * It will browse the agi-conf% existing find the new one to add (last or missing agi-conf)
 * return config_group_id of the first agi-conf% existing
 *
 */

function agi_confx_title($handle=null)
{
    if (empty($handle)) {
        $handle = DbConnect();
    }
    $instance_table = new Table();

    $QUERY = "SELECT id, group_title, group_description, SUBSTR(group_title,9,3)+0 AS idorder FROM cc_config_group WHERE group_title like '%agi-conf%' ORDER BY idorder";
    $result = $instance_table -> SQLExec ($handle, $QUERY);

    if (is_array($result)) {
        $num_cur = count($result);
        for ($i=0;$i<$num_cur;$i++) {
            $config_group_id = $result[0][0];
            $group_title[] = $result[$i][1];
            $description = $result[0][2];
        }
    }
    foreach ($group_title as $value) {
        $agi_number[] = (int) substr($value, 8);
    }

    $len_agi_array = sizeof($agi_number);
    $agi_conf_number = $len_agi_array + 1;
    for ($i=1; $i <= $len_agi_array; $i++) {
        if ($i != $agi_number[$i - 1]) {
            $agi_conf_number = $i;
            break;
        }
    }
    $config_group = array();
    $config_group[0] = "agi-conf".$agi_conf_number;
    $config_group[1] = $config_group_id;
    $config_group[2] = $description;
    $config_group[3] = $agi_number[0];

    return $config_group;
}

/*
 * Following function will generate agi-confx,
 * Duplicate all the configurations of agi-conf1 and produce agi-confx
 * Subquery is also used in this function to improve functional response.
 * Operations : SELECT , INSERT
 * Tables : cc_config, cc_config_group
 */

function add_agi_confx($handle = null)
{
    if (empty($handle)) {
        $handle = DbConnect();
    }
    $instance_table = new Table();
    $config_group = array();
    $config_group  = agi_confx_title(); // calling function  to generate agi-conf(title_number)
    $group_title = $config_group[0];
    $config_group_id = $config_group[1];
    $description = $config_group[2];
    $base_group_title = 'agi-conf'.$config_group[3];

    $value = "'$group_title', '$description'";
    $func_fields = "group_title, group_description";
    $func_table = 'cc_config_group';
    $id_name = "id";
    $inserted_id = $instance_table -> Add_table ($handle, $value, $func_fields, $func_table, $id_name);

    $value = "SELECT config_title, config_key, config_value, config_description, config_valuetype, '$group_title', config_listvalues FROM cc_config WHERE config_group_title = '$base_group_title'";
    $func_fields = "config_title, config_key, config_value, config_description, config_valuetype, config_group_title, config_listvalues";
    $func_table = 'cc_config';
    $id_name = "";
    $subquery = true;
    $result = $instance_table -> Add_table ($handle, $value, $func_fields, $func_table, $id_name, $subquery);

    return $group_title;
}

/*
 * This function delete agi-confx, all its global configurations and list of configurations
 * Operations : DELETE
 * Tables : cc_config, cc_config_group
 */
function delete_agi_confx($agi_conf)
{
    if (empty($handle)) {
        $handle = DbConnect();
    }
    $instance_table = new Table();

    $clause = "group_title = '$agi_conf'";
    $fun_table = "cc_config_group";
    $result = $instance_table -> Delete_table ($handle, $clause, $fun_table);

    $clause = "config_group_title = '$agi_conf'";
    $fun_table = "cc_config";
    $result = $instance_table -> Delete_table ($handle, $clause, $fun_table);

    return $result;
}
