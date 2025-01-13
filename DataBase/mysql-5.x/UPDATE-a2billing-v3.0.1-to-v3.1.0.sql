/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
/**
* This file is part of A2Billing (http://www.a2billing.net/)
*
* A2Billing, Commercial Open Source Telecom Billing platform,
* powered by Star2billing S.L. <http://www.star2billing.com/>
*
* @copyright Copyright (C) 2004-2012 - Star2billing S.L.
* @author Belaid Arezqui <areski@gmail.com>
* @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
* @package A2Billing
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
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU Affero General Public License for more details.
*
* You should have received a copy of the GNU Affero General Public License
* along with this program. If not, see <http://www.gnu.org/licenses/>.
*
**/

UPDATE cc_version SET version = '3.1.0' LIMIT 1;

-- move invoice config into standard config table
INSERT IGNORE INTO cc_config_group VALUES (null, 'invoice', 'Configuration for invoices and receipts');
SELECT LAST_INSERT_ID() INTO @groupid;

INSERT INTO cc_config SELECT null, 'Company name', key_val, value, 'The company name', 0, null, @groupid FROM cc_invoice_conf WHERE key_val = 'company_name';
INSERT INTO cc_config SELECT null, 'Company address', key_val, value, 'The company address', 0, null, @groupid FROM cc_invoice_conf WHERE key_val = 'address';
INSERT INTO cc_config SELECT null, 'Company city', key_val, value, 'The company city', 0, null, @groupid FROM cc_invoice_conf WHERE key_val = 'city';
INSERT INTO cc_config VALUES(null, 'Company state', 'state', '', 'The company state or region', 0, null, @groupid);
INSERT INTO cc_config SELECT null, 'Company postcode', key_val, value, 'The company postal code', 0, null, @groupid FROM cc_invoice_conf WHERE key_val = 'zipcode';
INSERT INTO cc_config SELECT null, 'Company country', key_val, value, 'The company country', 0, null, @groupid FROM cc_invoice_conf WHERE key_val = 'country';
INSERT INTO cc_config SELECT null, 'Company phone', key_val, value, 'The company phone number', 0, null, @groupid FROM cc_invoice_conf WHERE key_val = 'phone';
INSERT INTO cc_config SELECT null, 'Company fax', key_val, value, 'The company fax number', 0, null, @groupid FROM cc_invoice_conf WHERE key_val = 'fax';
INSERT INTO cc_config SELECT null, 'Company email', key_val, value, 'The company contact email', 0, null, @groupid FROM cc_invoice_conf WHERE key_val = 'email';
INSERT INTO cc_config SELECT null, 'Company website', key_val, value, 'The company website', 0, null, @groupid FROM cc_invoice_conf WHERE key_val = 'web';
INSERT INTO cc_config SELECT null, 'Company tax number', key_val, value, 'The company tax registration number', 0, null, @groupid FROM cc_invoice_conf WHERE key_val = 'vat';
INSERT INTO cc_config SELECT null, 'Display card number', key_val, value, 'Whether to include the card number on the invoice', 1, 'yes,no', @groupid FROM cc_invoice_conf WHERE key_val = 'display_account';

SELECT value INTO @next_num FROM cc_invoice_conf WHERE key_val = CONCAT('count_', DATE_FORMAT(CURRENT_TIMESTAMP, '%Y'));
INSERT INTO cc_config VALUES(
    null,
    'Next invoice number',
    'next_number',
    CONCAT(DATE_FORMAT(CURRENT_TIMESTAMP, '%Y'), LPAD(IFNULL(@next_num, 0) + 1, 8, '0')),
    'The next invoice number; the first invoice of the year resets to 1',
    0,
    null,
    @groupid
);

DROP TABLE cc_invoice_conf;

-- this column used to correspond to activated column in cc_card, now gone
ALTER TABLE cc_card_archive DROP COLUMN `activatedbyuser`;

-- remove remaining float columns to maintain precision with money
ALTER TABLE cc_alarm CHANGE COLUMN `minvalue` `minvalue` decimal(15,5) NOT NULL;
ALTER TABLE cc_alarm CHANGE COLUMN `maxvalue` `maxvalue` decimal(15,5) NOT NULL DEFAULT -1;
ALTER TABLE cc_alarm_report CHANGE COLUMN `calculatedvalue` `calculatedvalue` decimal(15,5) NOT NULL DEFAULT -1;
ALTER TABLE cc_call CHANGE COLUMN sessionbill sessionbill decimal(15,5) DEFAULT NULL;
ALTER TABLE cc_call_archive CHANGE COLUMN sessionbill sessionbill decimal(15,5) DEFAULT NULL;
ALTER TABLE cc_card CHANGE COLUMN vat vat decimal(15,5) NOT NULL DEFAULT 0;
ALTER TABLE cc_card_archive CHANGE COLUMN vat vat decimal(15,5) NOT NULL DEFAULT 0;
ALTER TABLE cc_charge CHANGE COLUMN amount amount decimal(15,5) NOT NULL DEFAULT 0;
ALTER TABLE cc_did CHANGE COLUMN fixrate fixrate decimal(15,5) NOT NULL DEFAULT 0;
ALTER TABLE cc_epayment_log CHANGE COLUMN vat vat decimal(15,5) NOT NULL DEFAULT 0;
ALTER TABLE cc_epayment_log_agent CHANGE COLUMN vat vat decimal(15,5) NOT NULL DEFAULT 0;
ALTER TABLE cc_ratecard CHANGE COLUMN stepchargec stepchargec decimal(15,5) NOT NULL DEFAULT 0;
ALTER TABLE cc_ratecard CHANGE COLUMN chargec chargec decimal(15,5) NOT NULL DEFAULT 0;
ALTER TABLE cc_service CHANGE COLUMN amount amount decimal(15,5) NOT NULL;
ALTER TABLE cc_service CHANGE COLUMN totalcredit totalcredit decimal(15,5) NOT NULL DEFAULT 0;
ALTER TABLE cc_service_report CHANGE COLUMN totalcredit totalcredit decimal(15,5) DEFAULT NULL;
ALTER TABLE cc_subscription_service CHANGE COLUMN fee fee decimal(15,5) NOT NULL DEFAULT 0;
ALTER TABLE cc_subscription_service CHANGE COLUMN totalcredit totalcredit decimal(15,5) NOT NULL DEFAULT 0;
ALTER TABLE cc_voucher CHANGE COLUMN credit credit decimal(15,5) NOT NULL DEFAULT 0;

-- setting to show/hide invalid rates
INSERT INTO cc_config (`config_title`, `config_key`, `config_value`, `config_description`, `config_valuetype`, `config_listvalues`, `config_group_id`)
    VALUES ('Hide Expired Rates','hide_expired_rates','0','Hide expired and future rates when viewing the rate list',1,'yes,no', (SELECT id FROM cc_config_group WHERE group_title = 'webui'));