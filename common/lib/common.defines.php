<?php

use A2billing\Connection;

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of A2Billing (http://www.a2billing.net/)
 *
 * A2Billing, Commercial Open Source Telecom Billing platform,
 * powered by Star2billing S.L. <http://www.star2billing.com/>
 *
 * @copyright   Copyright (C) 2004-2015 - Star2billing S.L.
 * @author      Belaid Arezqui <areski@gmail.com>
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

// SETTINGS FOR DATABASE CONNECTION
define ("HOST", $A2B->config['database']['hostname'] ?? null);
define ("PORT", $A2B->config['database']['port'] ?? null);
define ("USER", $A2B->config['database']['user'] ?? null);
define ("PASS", $A2B->config['database']['password'] ?? null);
define ("DBNAME", $A2B->config['database']['dbname'] ?? null);
define ("DB_TYPE", $A2B->config['database']['dbtype'] ?? null);
define ("CSRF_SALT", $A2B->config['csrf']['csrf_token_salt'] ?? 'YOURSALT');

// SETTINGS FOR SMTP
define ("SMTP_SERVER", $A2B->config['global']['smtp_server'] ?? null);
define ("SMTP_HOST", $A2B->config['global']['smtp_host'] ?? null);
define ("SMTP_USERNAME", $A2B->config['global']['smtp_username'] ?? null);
define ("SMTP_PASSWORD", $A2B->config['global']['smtp_password'] ?? null);
define ("SMTP_PORT", $A2B->config['global']['smtp_port'] ?? '25');
define ("SMTP_SECURE", $A2B->config['global']['smtp_secure'] ?? null);

// SETTING FOR REALTIME
define ("USE_REALTIME", $A2B->config['global']['use_realtime'] ?? 0);

// SIP IAX FRIEND CREATION
define ("FRIEND_TYPE", $A2B->config['peer_friend']['type'] ?? null);
define ("FRIEND_ALLOW", $A2B->config['peer_friend']['allow'] ?? null);
define ("FRIEND_CONTEXT", $A2B->config['peer_friend']['context'] ?? null);
define ("FRIEND_NAT", $A2B->config['peer_friend']['nat'] ?? null);
define ("FRIEND_AMAFLAGS", $A2B->config['peer_friend']['amaflags'] ?? null);
define ("FRIEND_QUALIFY", $A2B->config['peer_friend']['qualify'] ?? null);
define ("FRIEND_HOST", $A2B->config['peer_friend']['host'] ?? null);
define ("FRIEND_DTMFMODE", $A2B->config['peer_friend']['dtmfmode'] ?? null);

//DIDX.NET API
define ("DIDX_ID", $A2B->config['webui']['didx_id'] ?? null);
define ("DIDX_PASS", $A2B->config['webui']['didx_pass'] ?? null);
define ("DIDX_MIN_RATING", $A2B->config['webui']['didx_min_rating'] ?? null);
const DIDX_SITE = "api.didx.net";
define ("DIDX_RING_TO", $A2B->config['webui']['didx_ring_to'] ?? null);

define ("API_LOGFILE", $A2B->config['webui']['api_logfile'] ?? "/var/log/a2billing/");

// BUDDY ASTERISK FILES
define ("BUDDY_SIP_FILE", $A2B->config['webui']['buddy_sip_file'] ?? null);
define ("BUDDY_IAX_FILE", $A2B->config['webui']['buddy_iax_file'] ?? null);

// VOICEMAIL
const ACT_VOICEMAIL = false;

// SHOW DONATION
const SHOW_DONATION = true;

// AGI
define ("ASTERISK_VERSION", $A2B->config['agi-conf1']['asterisk_version'] ?? '1_4');

# define the amount of emails you want to send per period. If 0, batch processing
# is disabled and messages are sent out as fast as possible
const MAILQUEUE_BATCH_SIZE = 0;

# define the length of one batch processing period, in seconds (3600 is an hour)
const MAILQUEUE_BATCH_PERIOD = 3600;

# to avoid overloading the server that sends your email, you can add a little delay
# between messages that will spread the load of sending
# you will need to find a good value for your own server
# value is in seconds (or you can play with the autothrottle below)
const MAILQUEUE_THROTTLE = 0;

/*
 *		GLOBAL USED VARIABLE
 */
$PHP_SELF = $_SERVER["PHP_SELF"];

$CURRENT_DATETIME = date("Y-m-d H:i:s");

// Store script start time
$_START_TIME = time();

// A2BILLING COPYRIGHT & CONTACT
define ("TEXTCONTACT", gettext("This software has been created by Areski Belaid under AGPL licence. For futher information, feel free to contact me:"));
const EMAILCONTACT = "sales@star2billing.com";

// A2BILLING INFO
const COPYRIGHT = "A2Billing v2.2.0 is a " . '<a href="http://www.star2billing.com/solutions/voip-billing/" target="_blank">voip billing software</a>' . " licensed under the " . '<a href="http://www.fsf.org/licensing/licenses/agpl-3.0.html" target="_blank">AGPL 3</a>' . ". <br/>" . "Copyright (C) 2004-2015 - Star2billing S.L. <a href=\"http://www.star2billing.com\" target=\"_blank\">http://www.star2billing.com/</a>";

define ("CCMAINTITLE", gettext("A2Billing Portal"));

/*
 *		CONNECT / DISCONNECT DATABASE
 */
function DbConnect(): ADOConnection
{
    return Connection::GetDBHandler();
}
