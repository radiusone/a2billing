<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of A2Billing (http://www.a2billing.net/)
 *
 * A2Billing, Commercial Open Source Telecom Billing platform,
 * powered by Star2billing S.L. <http://www.star2billing.com/>
 *
 * @copyright   Copyright © 2004-2015 - Star2billing S.L.
 * @copyright   Copyright © 2022-2025 RadiusOne Inc.
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
    # status types:
    # 0 - Cancelled
    # 1 - Active
    # 2 - New
    # 3 - Pending confirmation
    # 4 - Reserved
    # 5 - Expired
    # 6 - Suspended (payment)
    # 7 - Suspended (litigation)
    # 8 - Pending payment
    # username, useralias, uipass, credit, lastname, firstname, status, expirationdate, enableexpire, expiredays, tariff, id_didgroup, id_group, address, city, state, country, zipcode, phone, email, fax, simultaccess, currency, typepaid, creditlimit, voipcall, sip_buddy, iax_buddy, language, id_campaign, vat, initialbalance, invoiceday, autorefill, loginkey
    1321321321, wchurch, "Churchill,332", 12, Churchill, Winston, 1, 2006-07-03 23:27:01, 0, 0, 1, -1, -1, 150 10th Street NW, Washington, DC , USA, 54000, 4592300610, winston@example.com, , 0, USD, 0, 0, 0, 1, 1, en, 0, 21, 0, 1, 1, asd234asd3
    6434353300, rcarrette, "Raumon""852", 12, Raumon, Carrette, 1, , 1, 31, 1, -1, -1, 150 14th NW, New York, NY , USA, 54000, 4592300613, Raumon@example.com, 4257009990, 0, USD, 0, 0, 0, 1, 1, en, 0, 0, 0, 1, 1, asd98866
    CSV;

$ratecardSample_Simple = <<< CSV
    # dialprefix, destination, selling rate, buyrate
    1212, "New York, NY", 0.70, 0.50
    32473, Belgium Mobile, 1.70, 1.44
    34650, Spain Mobile, 1.56, 1.18
    CSV;

$ratecardSample_Complex = <<< CSV
    # dialprefix, destination, selling rate, sellrate min duration, sellrate billing block, buyrate, buyrate min duration, buyrate billing block, connect charge, disconnect charge, start time, stop time, tag
    33, France, 1.01, 30, 6, 0.75, 30, 6, 0.12, 0, 0, 10079, tag1
    32, Belgium, 1.30, 30, 6, 1.04, 30, 6, 0.12, 0, 0, 10079, tag1
    34, Spain, 1.20, 30, 6, 1.00, 30, 6, 0.12, 0, 0, 10079, tag2
    44, UK, 0.54, 30, 10, 0.78, 30, 6, 0.06, 0, 10079, tag2
    CSV;

$didSample_Simple = <<< CSV
    # did, fixrate
    2001, 10
    2002, 10
    2003, 10
    2004, 10
    CSV;

$didSample_Complex = <<< CSV
    # billing types:
    # 0 - monthly fee plus per-minute fee
    # 1 - monthly fee only
    # 2 - per-minute fee only
    # 3 - free
    # did, fixrate, expirationdate, billingtype, selling_rate
    200, 10, 2029-07-17 19:48:07, 0, 0.03
    300, 15, 2029-07-17 19:48:07, 1, 0
    400, 0, 2029-07-17 19:48:07, 2, 0.25
    500, 12, 2029-07-17 19:48:07, 3, 0
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
