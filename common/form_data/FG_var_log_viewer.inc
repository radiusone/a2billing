<?php

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

$HD_Form = new FormHandler(
    "cc_system_log",
    _("Log"),
    "cc_system_log.id",
    ["cc_ui_authen" => ["cc_system_log.iduser", "cc_ui_authen.userid"]]
);

$HD_Form->list_query_order_columns = ["creationdate"];
$HD_Form->list_query_order_direction = "DESC";

$HD_Form->help_text = create_help(_("The system log helps you track all events on your application. Log levels are the Importance Levels for the events - 1 is lowest level and 3 is highest level. 1 is used for Login, Logout and Page Visit. 2 is used for Add, Import, Export. 3 is for Update and Delete."));

$HD_Form->AddListValue(_("Login"), "login");
$HD_Form->AddListMapping(_("Level"), "loglevel", getLogLevel_List());
$HD_Form->AddListValue(_("Action"), "action");
$HD_Form->AddListValue(_("Description"), "description");
$HD_Form->AddListValue(_("Table"), "tablename");
$HD_Form->AddListValue(_("Page"), "pagename");
$HD_Form->AddListValue(_("IP address"), "ipaddress");
$HD_Form->AddListValue(_("Date"), "creationdate");

$HD_Form->search_form_enabled = true;
$HD_Form->search_session_key = "var_log_viewer";
$HD_Form->search_delete_enabled = false;
$HD_Form->AddSearchDateInput(_("Date"), "creationdate");
$HD_Form->AddSearchPopupInput(_("Administrator"), "iduser", "A2B_entity_user.php");
$HD_Form->AddSearchSelectInput(_("Log level"),"loglevel", [[1, _("Info")], [2, _("Notice")], [3, _("Warning")]]);

$HD_Form->CV_NO_FIELDS = _("No log entries found");
$HD_Form->FG_ENABLE_EDIT_BUTTON = false;
$HD_Form->FG_ENABLE_DELETE_BUTTON = false;
$HD_Form->FG_ENABLE_ADD_BUTTON = false;
$HD_Form->FG_ENABLE_INFO_BUTTON = true;
$HD_Form->FG_INFO_BUTTON_LINK = "A2B_info_log_viewer.php?id=";
