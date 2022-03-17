<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of A2Billing (http://www.a2billing.net/)
 *
 * A2Billing, Commercial Open Source Telecom Billing platform,
 * powered by Star2billing S.L. <http://www.star2billing.com/>
 *
 * @copyright   Copyright © 2004-2015 - Star2billing S.L.
 * @copyright   Copyright © 2022 RadiusOne Inc.
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

$private_key = "Ae87v56zzl34v";
$private_key_md5 = md5($private_key);

$api_url = "http://localhost/webservice/display_ratecard.php?" .
            "key=$private_key_md5" .
            "&page_url=http://localhost/~areski/svn/asterisk2billing/trunk/webservice/sample_display_ratecard.php" .
            "&field_to_display=t1.destination,t1.dialprefix,t1.rateinitial" .
            "&column_name=Destination,Prefix,Rate/Min&field_type=,,money" .
            "&fullhtmlpage=1&filter=countryname,prefix".
            "&".$_SERVER['QUERY_STRING'];

// ----------------- USAGE -------------------------

#usage:
$content = file_get_contents ($api_url);
print ($content);
