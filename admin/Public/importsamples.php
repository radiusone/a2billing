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

$cardSample_Simple = <<< CSV
    # username, useralias, uipass, credit, lastname, firstname, status
    1321321321, 12312323325, Churchill, 12, Churchill, Winston, 1
    1435345345, 12312323444, Raumon, 12, Raumon, Carrette, 1
    1321387788, 12312323555, VonDutch, 12, VonDutch, Jaycon, 1
    CSV;

$cardSample_Complex = <<< CSV
    # username, useralias, uipass, credit, lastname, firstname, status, expirationdate, enableexpire, expiredays, tariff, id_didgroup, id_group, address, city, state, country, zipcode, phone, email, fax, simultaccess, currency, typepaid, creditlimit, voipcall, sip_buddy, iax_buddy, language, id_campaign, vat, initialbalance, invoiceday, autorefill, loginkey
    1321321321, wchurch, "Churchill,332", 12, Churchill, Winston, 1, 2006-07-03 23:27:01, 0, 0, 1, -1, -1, 150 10th Street NW, Washington, DC , USA, 54000, 4592300610, winston@example.com, , 0, USD, 0, 0, 0, 1, 1, en, 0, 21, 0, 1, 1, asd234asd3
    6434353300, rcarrette, "Raumon""852", 12, Raumon, Carrette, 1, , 1, 31, 1, -1, -1, 150 14th NW, New York, NY , USA, 54000, 4592300613, Raumon@example.com, 4257009990, 0, USD, 0, 0, 0, 1, 1, en, 0, 0, 0, 1, 1, asd98866
    CSV;

$ratecardSample_Simple = <<< CSV
    1, US, 0.70, 0.50, 2008-03-07 21:21:38, tag1, 0, 0, 0, 0
    34, Spain Fix, 1.56, 1.16, 2008-03-07 21:21:38, tag2, 360, 240, 0.5, 5
    34650, Spain Mobile Movistar, 1.56, 1.18, tag3, 720, 480, 1.0, 10
    32, Belgium Fix, 1.20, 1.11, tag4, 1080, 720, 1.5, 15
    32473, Belgium Mobile Proximus, 1.70, 1.44, tag5, 1440, 960, 2.0, 20
    CSV;

$ratecardSample_Complex = <<< CSV
    33, France, 1.01, 30, 6, 1.23, 30, 6, 0.12, 0, 0.12, 1.34, 120, 20, 0,0,0,0,  0,0,0,0, 0, 0, 0,10079,tag1,0,0,0,0
    32, Belgium, 1.30, 30, 6, 1.43, 30, 6, 0.12, 0, 0.12, 1.54, 180, 20, 0,0,0,0,  0,0,0,0, 0, 0, 0,10079,tag2,360,240,0.5,5
    34, Spain, 1.00, 30, 6, 1.10, 30, 6, 0.12, 0, 0.12, 1.14, 120, 0, 0,0,0,0,  0,0,0,0, 0, 0, 0,9079,tag3,720,480,1.0,10
    44, UK, 0.54, 30, 10, 0.78, 30, 6, 0.06, 0, 0.06, 0.85, 120, 0, 0,0,0,0,  0,0,0,0, 2005-02-10 21:23:55, 2005-04-15 10:00:00, 1,10079,tag4,1080,720,1.5,15
    44, UK, 0.54, 30, 10, 0.89, 30, 6, 0.10, 0, 0.06, 0.94, 120, 0, 0,0,0,0,  0,0,0,0, 2005-04-15 10:00:00, 0, 1,2000,tag5,1440,960,2.0,20
    CSV;

$didSample_Simple = <<< CSV
    2001, 103
    2002, 104
    2003, 108
    2004, 105
    CSV;

$didSample_Complex = <<< CSV
    200, 12, 1, 2006-07-17 19:48:07, 2031-07-17 19:48:07, 1
    300, 12, 1, 2006-07-17 19:48:07, 2031-07-17 19:48:07, 1
    400, 12, 1, 2006-07-17 19:48:07, 2031-07-17 19:48:07, 1
    500, 12, 1, 2006-07-17 19:48:07, 2031-07-17 19:48:07, 1
    CSV;

$phonebookSample_Simple = <<< CSV
    003247354343
    003247354563
    003247354356
    CSV;

$phonebookSample_Complex = <<< CSV
    003247354343, Jean Pest, advertissing
    003247354563, Ale Beignard, Debt
    003247354356, James Bon, Debt
    CSV;

$texts = [
    0 => _("No sample defined!"),
    "Card_Simple" => $cardSample_Simple,
    "Card_Complex" => $cardSample_Complex,
    "RateCard_Simple" => $ratecardSample_Simple,
    "RateCard_Complex" => $ratecardSample_Complex,
    "did_Simple" => $didSample_Simple,
    "did_Complex" => $didSample_Complex,
    "Phonebook_Simple" => $phonebookSample_Simple,
    "Phonebook_Complex" => $phonebookSample_Complex,
];
$index = $_GET["sample"] ?? 0;
?>
<!doctype html>
<html lang="en">
    <head><title>Sample</title></head>
    <body><pre style="font-size:75%"><?= $texts[$index] ?? $texts[0] ?></pre></body>
</html>
