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
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU Affero General Public License for more details.
*
* You should have received a copy of the GNU Affero General Public License
* along with this program. If not, see <http://www.gnu.org/licenses/>.
*
**/

-- Update Version
UPDATE cc_version SET version = '3.0.0' WHERE version != '3.0.0';

-- match archive with live calls table
ALTER TABLE cc_call_archive ADD a2b_custom1 VARCHAR(20) DEFAULT NULL, ADD a2b_custom2 VARCHAR(20) DEFAULT NULL;
ALTER TABLE cc_call_archive MODIFY calledstation VARCHAR(100) NOT NULL;

-- remove unused settings
DELETE FROM cc_config WHERE config_key IN ('asterisk_version', 'cront_currency_update', 'didx_id', 'didx_pass', 'didx_min_rating', 'didx_ring_to');

-- increase permissions size
ALTER TABLE cc_ui_authen MODIFY perms INT UNSIGNED DEFAULT NULL;

-- stop using timestamps
ALTER TABLE cc_agent MODIFY datecreation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_agent_commission MODIFY `date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_alarm
    MODIFY datecreate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    MODIFY datelastrun DATETIME DEFAULT NULL
;
ALTER TABLE cc_alarm_report MODIFY daterun DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_autorefill_report MODIFY daterun DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_backup MODIFY creationdate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_billing_customer
    MODIFY `date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    MODIFY start_date DATETIME NULL
;
ALTER TABLE cc_call
    MODIFY starttime DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    MODIFY stoptime DATETIME DEFAULT NULL
;
ALTER TABLE cc_call_archive
    MODIFY starttime DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    MODIFY stoptime DATETIME DEFAULT NULL
;
ALTER TABLE cc_callback_spool
    MODIFY entry_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    MODIFY last_attempt_time DATETIME DEFAULT NULL,
    MODIFY callback_time DATETIME DEFAULT NULL
;
ALTER TABLE cc_campaign
    MODIFY creationdate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    MODIFY startingdate DATETIME DEFAULT NULL,
    MODIFY expirationdate DATETIME DEFAULT NULL
;
ALTER TABLE cc_campaign_phonestatus MODIFY lastuse DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_card
    DROP activated,
    MODIFY creationdate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    MODIFY firstusedate DATETIME DEFAULT NULL,
    MODIFY expirationdate DATETIME DEFAULT NULL,
    MODIFY lastuse DATETIME DEFAULT NULL,
    MODIFY servicelastrun DATETIME DEFAULT NULL,
    MODIFY last_notification DATETIME NULL,
    MODIFY lock_date DATETIME NULL
;
ALTER TABLE cc_card_archive
    DROP activated,
    MODIFY creationdate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    MODIFY firstusedate DATETIME DEFAULT NULL,
    MODIFY expirationdate DATETIME DEFAULT NULL,
    MODIFY lastuse DATETIME DEFAULT NULL,
    MODIFY servicelastrun DATETIME DEFAULT NULL,
    MODIFY last_notification DATETIME NULL
;
ALTER TABLE cc_card_history MODIFY datecreated DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_card_package_offer MODIFY date_consumption DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_card_subscription
    MODIFY startdate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    MODIFY stopdate DATETIME DEFAULT NULL,
    MODIFY last_run DATETIME DEFAULT NULL,
    MODIFY next_billing_date DATETIME DEFAULT NULL,
    MODIFY limit_pay_date DATETIME DEFAULT NULL
;
ALTER TABLE cc_charge MODIFY creationdate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_currencies MODIFY lastupdate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_did
    MODIFY creationdate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    MODIFY startingdate DATETIME DEFAULT NULL,
    MODIFY expirationdate DATETIME DEFAULT NULL
;
ALTER TABLE cc_did_destination MODIFY creationdate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_did_use
    MODIFY reservationdate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    MODIFY releasedate DATETIME DEFAULT NULL
;
ALTER TABLE cc_didgroup MODIFY creationdate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_epayment_log MODIFY creationdate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_epayment_log_agent MODIFY creationdate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_invoice MODIFY `date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_invoice_item MODIFY `date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_logpayment MODIFY `date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_logpayment_agent MODIFY `date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_logrefill MODIFY `date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_logrefill_agent MODIFY `date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_notification MODIFY `date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_outbound_cid_group MODIFY creationdate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_outbound_cid_list MODIFY creationdate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_package_offer MODIFY creationdate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_phonenumber MODIFY creationdate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_provider MODIFY creationdate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_ratecard
    MODIFY startdate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    MODIFY stopdate DATETIME DEFAULT NULL
;
ALTER TABLE cc_receipt MODIFY `date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_receipt_item MODIFY `date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_remittance_request MODIFY `date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_server_manager MODIFY lasttime_used DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_service
    MODIFY datecreate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    MODIFY datelastrun DATETIME DEFAULT NULL
;
ALTER TABLE cc_service_report MODIFY daterun DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_speeddial MODIFY creationdate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_status_log MODIFY updated_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_subscription_service
    MODIFY datecreate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    MODIFY datelastrun DATETIME DEFAULT NULL,
    MODIFY startdate DATETIME DEFAULT NULL,
    MODIFY stopdate DATETIME DEFAULT NULL
;
ALTER TABLE cc_system_log MODIFY creationdate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_tariffgroup MODIFY creationdate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_tariffplan
    MODIFY creationdate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    MODIFY startingdate DATETIME DEFAULT NULL,
    MODIFY expirationdate DATETIME DEFAULT NULL
;
ALTER TABLE cc_ticket MODIFY creationdate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_ticket_comment MODIFY `date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_trunk MODIFY creationdate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_ui_authen MODIFY datecreation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_voucher
    MODIFY creationdate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    MODIFY usedate DATETIME DEFAULT NULL,
    MODIFY expirationdate DATETIME DEFAULT NULL
;

-- set some common defaults
ALTER TABLE cc_call
    MODIFY nasipaddress VARCHAR(30) NOT NULL DEFAULT '',
    MODIFY calledstation VARCHAR(100) NOT NULL DEFAULT '',
    MODIFY src VARCHAR(40) NOT NULL DEFAULT '',
    MODIFY dnid VARCHAR(40) NOT NULL DEFAULT ''
;

ALTER TABLE cc_card
    MODIFY email VARCHAR(70) NOT NULL DEFAULT '',
    MODIFY address VARCHAR(100) NOT NULL DEFAULT '',
    MODIFY company_website VARCHAR(60) NOT NULL DEFAULT '',
    MODIFY company_name VARCHAR(50) NOT NULL DEFAULT '',
    MODIFY loginkey VARCHAR(40) NOT NULL DEFAULT '',
    MODIFY traffic_target VARCHAR(300) NOT NULL DEFAULT '',
    MODIFY city VARCHAR(40) NOT NULL DEFAULT '',
    MODIFY fax VARCHAR(20) NOT NULL DEFAULT '',
    MODIFY tag VARCHAR(50) NOT NULL DEFAULT '',
    MODIFY email_notification VARCHAR(70) NOT NULL DEFAULT '',
    MODIFY country VARCHAR(40) NOT NULL DEFAULT '',
    MODIFY redial VARCHAR(50) NOT NULL DEFAULT '',
    MODIFY phone VARCHAR(20) NOT NULL DEFAULT '',
    MODIFY zipcode VARCHAR(20) NOT NULL DEFAULT '',
    MODIFY lastname VARCHAR(50) NOT NULL DEFAULT '',
    MODIFY firstname VARCHAR(50) NOT NULL DEFAULT '',
    MODIFY state VARCHAR(40) NOT NULL DEFAULT ''
;

-- why is this using text as a foreign key???
ALTER TABLE cc_config ADD COLUMN config_group_id BIGINT NOT NULL;
DELETE FROM cc_config WHERE config_group_title NOT IN (SELECT group_title FROM cc_config_group);
UPDATE cc_config SET cc_config.config_group_id = (SELECT id FROM cc_config_group WHERE group_title=config_group_title) WHERE config_group_title != '';
ALTER TABLE cc_config DROP config_group_title;
UPDATE cc_config SET config_valuetype = 2 WHERE config_valuetype = 0 AND config_listvalues != '' AND config_listvalues IS NOT NULL;

-- primary keys
ALTER TABLE cc_agent MODIFY id bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE cc_alarm MODIFY id bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE cc_billing_customer MODIFY id bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE cc_callback_spool MODIFY id bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE cc_campaign MODIFY id bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE cc_campaign_config MODIFY id bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE cc_card MODIFY id bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE cc_card_group MODIFY id bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE cc_card_package_offer MODIFY id bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE cc_card_seria MODIFY id bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE cc_card_subscription MODIFY id bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE cc_config_group MODIFY id bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE cc_country MODIFY id bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE cc_did MODIFY id bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE cc_didgroup MODIFY id bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE cc_invoice MODIFY id bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE cc_logrefill MODIFY id bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE cc_notification MODIFY id bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE cc_outbound_cid_group MODIFY id bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE cc_package_group MODIFY id bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE cc_package_offer MODIFY id bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE cc_payments MODIFY id bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE cc_payments_status MODIFY id bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE cc_phonebook MODIFY id bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE cc_phonenumber MODIFY id bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE cc_provider MODIFY id bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE cc_ratecard MODIFY id bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE cc_receipt MODIFY id bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE cc_server_group MODIFY id bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE cc_server_manager MODIFY id bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE cc_service MODIFY id bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE cc_subscription_service MODIFY id bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE cc_support MODIFY id bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE cc_support_component MODIFY id bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE cc_tariffgroup MODIFY id bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE cc_tariffplan MODIFY id bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE cc_ticket MODIFY id bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE cc_timezone MODIFY id bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE cc_trunk MODIFY id_trunk bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE cc_ui_authen MODIFY userid bigint(20) NOT NULL AUTO_INCREMENT;

-- foreign keys
ALTER TABLE cc_agent MODIFY id_tariffgroup BIGINT DEFAULT NULL;
ALTER TABLE cc_agent_commission MODIFY id_agent BIGINT DEFAULT NULL;
ALTER TABLE cc_agent_commission MODIFY id_card BIGINT DEFAULT NULL;
ALTER TABLE cc_agent_commission MODIFY id_payment BIGINT DEFAULT NULL;
ALTER TABLE cc_agent_signup MODIFY id_agent BIGINT DEFAULT NULL;
ALTER TABLE cc_agent_signup MODIFY id_group BIGINT DEFAULT NULL;
ALTER TABLE cc_agent_signup MODIFY id_tariffgroup BIGINT DEFAULT NULL;
ALTER TABLE cc_agent_tariffgroup MODIFY id_agent BIGINT DEFAULT NULL;
ALTER TABLE cc_agent_tariffgroup MODIFY id_tariffgroup BIGINT DEFAULT NULL;
ALTER TABLE cc_alarm MODIFY id_trunk BIGINT DEFAULT NULL;
ALTER TABLE cc_alarm_report MODIFY cc_alarm_id BIGINT DEFAULT NULL;
ALTER TABLE cc_billing_customer MODIFY id_card BIGINT DEFAULT NULL;
ALTER TABLE cc_billing_customer MODIFY id_invoice BIGINT DEFAULT NULL;
ALTER TABLE cc_call MODIFY card_id BIGINT DEFAULT NULL;
ALTER TABLE cc_call MODIFY id_card_package_offer BIGINT DEFAULT NULL;
ALTER TABLE cc_call MODIFY id_did BIGINT DEFAULT NULL;
ALTER TABLE cc_call MODIFY id_ratecard BIGINT DEFAULT NULL;
ALTER TABLE cc_call MODIFY id_tariffgroup BIGINT DEFAULT NULL;
ALTER TABLE cc_call MODIFY id_tariffplan BIGINT DEFAULT NULL;
ALTER TABLE cc_call MODIFY id_trunk BIGINT DEFAULT NULL;
ALTER TABLE cc_call_archive MODIFY card_id BIGINT DEFAULT NULL;
ALTER TABLE cc_call_archive MODIFY id_card_package_offer BIGINT DEFAULT NULL;
ALTER TABLE cc_call_archive MODIFY id_did BIGINT DEFAULT NULL;
ALTER TABLE cc_call_archive MODIFY id_ratecard BIGINT DEFAULT NULL;
ALTER TABLE cc_call_archive MODIFY id_tariffgroup BIGINT DEFAULT NULL;
ALTER TABLE cc_call_archive MODIFY id_tariffplan BIGINT DEFAULT NULL;
ALTER TABLE cc_call_archive MODIFY id_trunk BIGINT DEFAULT NULL;
ALTER TABLE cc_callback_spool MODIFY id_server BIGINT DEFAULT NULL;
ALTER TABLE cc_callback_spool MODIFY id_server_group BIGINT DEFAULT NULL;
ALTER TABLE cc_callerid MODIFY id_cc_card BIGINT DEFAULT NULL;
ALTER TABLE cc_campaign MODIFY id_campaign_config BIGINT DEFAULT NULL;
ALTER TABLE cc_campaign MODIFY id_card BIGINT DEFAULT NULL;
ALTER TABLE cc_campaign MODIFY id_cid_group BIGINT DEFAULT NULL;
ALTER TABLE cc_campaign_phonebook MODIFY id_campaign BIGINT DEFAULT NULL;
ALTER TABLE cc_campaign_phonebook MODIFY id_phonebook BIGINT DEFAULT NULL;
ALTER TABLE cc_campaign_phonestatus MODIFY id_callback BIGINT DEFAULT NULL;
ALTER TABLE cc_campaign_phonestatus MODIFY id_campaign BIGINT DEFAULT NULL;
ALTER TABLE cc_campaign_phonestatus MODIFY id_phonenumber BIGINT DEFAULT NULL;
ALTER TABLE cc_campaignconf_cardgroup MODIFY id_campaign_config BIGINT DEFAULT NULL;
ALTER TABLE cc_campaignconf_cardgroup MODIFY id_card_group BIGINT DEFAULT NULL;
ALTER TABLE cc_card MODIFY id_campaign BIGINT DEFAULT NULL;
ALTER TABLE cc_card MODIFY id_group BIGINT DEFAULT NULL;
ALTER TABLE cc_card MODIFY id_seria BIGINT DEFAULT NULL;
ALTER TABLE cc_card MODIFY id_timezone BIGINT DEFAULT NULL;
ALTER TABLE cc_card MODIFY tariff BIGINT DEFAULT NULL;
ALTER TABLE cc_card_group MODIFY id_agent BIGINT DEFAULT NULL;
ALTER TABLE cc_card_history MODIFY id_cc_card BIGINT DEFAULT NULL;
ALTER TABLE cc_card_package_offer MODIFY id_cc_card BIGINT DEFAULT NULL;
ALTER TABLE cc_card_package_offer MODIFY id_cc_package_offer BIGINT DEFAULT NULL;
ALTER TABLE cc_card_subscription MODIFY id_cc_card BIGINT DEFAULT NULL;
ALTER TABLE cc_card_subscription MODIFY id_subscription_fee BIGINT DEFAULT NULL;
ALTER TABLE cc_cardgroup_service MODIFY id_card_group BIGINT DEFAULT NULL;
ALTER TABLE cc_cardgroup_service MODIFY id_service BIGINT DEFAULT NULL;
ALTER TABLE cc_charge MODIFY id_cc_card BIGINT DEFAULT NULL;
ALTER TABLE cc_charge MODIFY id_cc_card_subscription BIGINT DEFAULT NULL;
ALTER TABLE cc_charge MODIFY id_cc_did BIGINT DEFAULT NULL;
ALTER TABLE cc_charge MODIFY iduser BIGINT DEFAULT NULL;
ALTER TABLE cc_config MODIFY config_group_id BIGINT DEFAULT NULL;
ALTER TABLE cc_did MODIFY id_cc_country BIGINT DEFAULT NULL;
ALTER TABLE cc_did MODIFY id_cc_didgroup BIGINT DEFAULT NULL;
ALTER TABLE cc_did_destination MODIFY id_cc_card BIGINT DEFAULT NULL;
ALTER TABLE cc_did_destination MODIFY id_cc_did BIGINT DEFAULT NULL;
ALTER TABLE cc_did_use MODIFY id_cc_card BIGINT DEFAULT NULL;
ALTER TABLE cc_did_use MODIFY id_did BIGINT DEFAULT NULL;
ALTER TABLE cc_iax_buddies MODIFY id_cc_card BIGINT DEFAULT NULL;
ALTER TABLE cc_invoice MODIFY id_card BIGINT DEFAULT NULL;
ALTER TABLE cc_invoice_item MODIFY id_ext BIGINT DEFAULT NULL;
ALTER TABLE cc_invoice_item MODIFY id_invoice BIGINT DEFAULT NULL;
ALTER TABLE cc_invoice_payment MODIFY id_invoice BIGINT DEFAULT NULL;
ALTER TABLE cc_invoice_payment MODIFY id_payment BIGINT DEFAULT NULL;
ALTER TABLE cc_logpayment MODIFY agent_id BIGINT DEFAULT NULL;
ALTER TABLE cc_logpayment MODIFY card_id BIGINT DEFAULT NULL;
ALTER TABLE cc_logpayment MODIFY id_logrefill BIGINT DEFAULT NULL;
ALTER TABLE cc_logpayment_agent MODIFY agent_id BIGINT DEFAULT NULL;
ALTER TABLE cc_logpayment_agent MODIFY id_logrefill BIGINT DEFAULT NULL;
ALTER TABLE cc_logrefill MODIFY agent_id BIGINT DEFAULT NULL;
ALTER TABLE cc_logrefill MODIFY card_id BIGINT DEFAULT NULL;
ALTER TABLE cc_logrefill_agent MODIFY agent_id BIGINT DEFAULT NULL;
ALTER TABLE cc_message_agent MODIFY id_agent BIGINT DEFAULT NULL;
ALTER TABLE cc_notification_admin MODIFY id_admin BIGINT DEFAULT NULL;
ALTER TABLE cc_notification_admin MODIFY id_notification BIGINT DEFAULT NULL;
ALTER TABLE cc_outbound_cid_list MODIFY outbound_cid_group BIGINT DEFAULT NULL;
ALTER TABLE cc_packgroup_package MODIFY package_id BIGINT DEFAULT NULL;
ALTER TABLE cc_packgroup_package MODIFY packagegroup_id BIGINT DEFAULT NULL;
ALTER TABLE cc_payments_status MODIFY status_id BIGINT DEFAULT NULL;
ALTER TABLE cc_phonebook MODIFY id_card BIGINT DEFAULT NULL;
ALTER TABLE cc_phonenumber MODIFY id_phonebook BIGINT DEFAULT NULL;
ALTER TABLE cc_ratecard MODIFY id_outbound_cidgroup BIGINT DEFAULT NULL;
ALTER TABLE cc_ratecard MODIFY id_trunk BIGINT DEFAULT NULL;
ALTER TABLE cc_ratecard MODIFY idtariffplan BIGINT DEFAULT NULL;
ALTER TABLE cc_receipt MODIFY id_card BIGINT DEFAULT NULL;
ALTER TABLE cc_receipt_item MODIFY id_receipt BIGINT DEFAULT NULL;
ALTER TABLE cc_remittance_request MODIFY id_agent BIGINT DEFAULT NULL;
ALTER TABLE cc_restricted_phonenumber MODIFY id_card BIGINT DEFAULT NULL;
ALTER TABLE cc_server_manager MODIFY id_group BIGINT DEFAULT NULL;
ALTER TABLE cc_service_report MODIFY cc_service_id BIGINT DEFAULT NULL;
ALTER TABLE cc_sip_buddies MODIFY id_cc_card BIGINT DEFAULT NULL;
ALTER TABLE cc_speeddial MODIFY id_cc_card BIGINT DEFAULT NULL;
ALTER TABLE cc_status_log MODIFY id_cc_card BIGINT DEFAULT NULL;
ALTER TABLE cc_subscription_signup MODIFY id_callplan BIGINT DEFAULT NULL;
ALTER TABLE cc_subscription_signup MODIFY id_subscription BIGINT DEFAULT NULL;
ALTER TABLE cc_support_component MODIFY id_support BIGINT DEFAULT NULL;
ALTER TABLE cc_tariffgroup MODIFY id_cc_package_offer BIGINT DEFAULT NULL;
ALTER TABLE cc_tariffgroup MODIFY idtariffplan BIGINT DEFAULT NULL;
ALTER TABLE cc_tariffgroup_plan MODIFY idtariffgroup BIGINT DEFAULT NULL;
ALTER TABLE cc_tariffgroup_plan MODIFY idtariffplan BIGINT DEFAULT NULL;
ALTER TABLE cc_tariffplan MODIFY id_trunk BIGINT DEFAULT NULL;
ALTER TABLE cc_tariffplan MODIFY iduser BIGINT DEFAULT NULL;
ALTER TABLE cc_ticket MODIFY id_component BIGINT DEFAULT NULL;
ALTER TABLE cc_ticket_comment MODIFY id_ticket BIGINT DEFAULT NULL;
ALTER TABLE cc_trunk MODIFY id_provider BIGINT DEFAULT NULL;

-- update engine to something from the 21st century
ALTER TABLE cc_agent_commission ENGINE=InnoDB;
ALTER TABLE cc_agent_signup ENGINE=InnoDB;
ALTER TABLE cc_agent_tariffgroup ENGINE=InnoDB;
ALTER TABLE cc_agent ENGINE=InnoDB;
ALTER TABLE cc_alarm_report ENGINE=InnoDB;
ALTER TABLE cc_alarm ENGINE=InnoDB;
ALTER TABLE cc_autorefill_report ENGINE=InnoDB;
ALTER TABLE cc_backup ENGINE=InnoDB;
ALTER TABLE cc_billing_customer ENGINE=InnoDB;
ALTER TABLE cc_call_archive ENGINE=InnoDB;
ALTER TABLE cc_call ENGINE=InnoDB;
ALTER TABLE cc_callback_spool ENGINE=InnoDB;
ALTER TABLE cc_callerid ENGINE=InnoDB;
ALTER TABLE cc_campaign_config ENGINE=InnoDB;
ALTER TABLE cc_campaign_phonebook ENGINE=InnoDB;
ALTER TABLE cc_campaign_phonestatus ENGINE=InnoDB;
ALTER TABLE cc_campaign ENGINE=InnoDB;
ALTER TABLE cc_campaignconf_cardgroup ENGINE=InnoDB;
ALTER TABLE cc_card_archive ENGINE=InnoDB;
ALTER TABLE cc_card_group ENGINE=InnoDB;
ALTER TABLE cc_card_history ENGINE=InnoDB;
ALTER TABLE cc_card_package_offer ENGINE=InnoDB;
ALTER TABLE cc_card_seria ENGINE=InnoDB;
ALTER TABLE cc_card_subscription ENGINE=InnoDB;
ALTER TABLE cc_card ENGINE=InnoDB;
ALTER TABLE cc_cardgroup_service ENGINE=InnoDB;
ALTER TABLE cc_charge ENGINE=InnoDB;
ALTER TABLE cc_config_group ENGINE=InnoDB;
ALTER TABLE cc_config ENGINE=InnoDB;
ALTER TABLE cc_configuration ENGINE=InnoDB;
ALTER TABLE cc_country ENGINE=InnoDB;
ALTER TABLE cc_currencies ENGINE=InnoDB;
ALTER TABLE cc_did_destination ENGINE=InnoDB;
ALTER TABLE cc_did_use ENGINE=InnoDB;
ALTER TABLE cc_did ENGINE=InnoDB;
ALTER TABLE cc_didgroup ENGINE=InnoDB;
ALTER TABLE cc_epayment_log_agent ENGINE=InnoDB;
ALTER TABLE cc_epayment_log ENGINE=InnoDB;
ALTER TABLE cc_iax_buddies ENGINE=InnoDB;
ALTER TABLE cc_invoice_conf ENGINE=InnoDB;
ALTER TABLE cc_invoice_item ENGINE=InnoDB;
ALTER TABLE cc_invoice_payment ENGINE=InnoDB;
ALTER TABLE cc_invoice ENGINE=InnoDB;
ALTER TABLE cc_iso639 ENGINE=InnoDB;
ALTER TABLE cc_logpayment_agent ENGINE=InnoDB;
ALTER TABLE cc_logpayment ENGINE=InnoDB;
ALTER TABLE cc_logrefill_agent ENGINE=InnoDB;
ALTER TABLE cc_logrefill ENGINE=InnoDB;
ALTER TABLE cc_message_agent ENGINE=InnoDB;
ALTER TABLE cc_monitor ENGINE=InnoDB;
ALTER TABLE cc_notification_admin ENGINE=InnoDB;
ALTER TABLE cc_notification ENGINE=InnoDB;
ALTER TABLE cc_outbound_cid_group ENGINE=InnoDB;
ALTER TABLE cc_outbound_cid_list ENGINE=InnoDB;
ALTER TABLE cc_package_group ENGINE=InnoDB;
ALTER TABLE cc_package_offer ENGINE=InnoDB;
ALTER TABLE cc_package_rate ENGINE=InnoDB;
ALTER TABLE cc_packgroup_package ENGINE=InnoDB;
ALTER TABLE cc_payment_methods ENGINE=InnoDB;
ALTER TABLE cc_payments_agent ENGINE=InnoDB;
ALTER TABLE cc_payments_status ENGINE=InnoDB;
ALTER TABLE cc_payments ENGINE=InnoDB;
ALTER TABLE cc_paypal ENGINE=InnoDB;
ALTER TABLE cc_phonebook ENGINE=InnoDB;
ALTER TABLE cc_phonenumber ENGINE=InnoDB;
ALTER TABLE cc_prefix ENGINE=InnoDB;
ALTER TABLE cc_provider ENGINE=InnoDB;
ALTER TABLE cc_ratecard ENGINE=InnoDB;
ALTER TABLE cc_receipt_item ENGINE=InnoDB;
ALTER TABLE cc_receipt ENGINE=InnoDB;
ALTER TABLE cc_remittance_request ENGINE=InnoDB;
ALTER TABLE cc_restricted_phonenumber ENGINE=InnoDB;
ALTER TABLE cc_server_group ENGINE=InnoDB;
ALTER TABLE cc_server_manager ENGINE=InnoDB;
ALTER TABLE cc_service_report ENGINE=InnoDB;
ALTER TABLE cc_service ENGINE=InnoDB;
ALTER TABLE cc_sip_buddies ENGINE=InnoDB;
ALTER TABLE cc_speeddial ENGINE=InnoDB;
ALTER TABLE cc_status_log ENGINE=InnoDB;
ALTER TABLE cc_subscription_service ENGINE=InnoDB;
ALTER TABLE cc_subscription_signup ENGINE=InnoDB;
ALTER TABLE cc_support_component ENGINE=InnoDB;
ALTER TABLE cc_support ENGINE=InnoDB;
ALTER TABLE cc_system_log ENGINE=InnoDB;
ALTER TABLE cc_tariffgroup_plan ENGINE=InnoDB;
ALTER TABLE cc_tariffgroup ENGINE=InnoDB;
ALTER TABLE cc_tariffplan ENGINE=InnoDB;
ALTER TABLE cc_templatemail ENGINE=InnoDB;
ALTER TABLE cc_ticket_comment ENGINE=InnoDB;
ALTER TABLE cc_ticket ENGINE=InnoDB;
ALTER TABLE cc_timezone ENGINE=InnoDB;
ALTER TABLE cc_trunk ENGINE=InnoDB;
ALTER TABLE cc_ui_authen ENGINE=InnoDB;
ALTER TABLE cc_version ENGINE=InnoDB;
ALTER TABLE cc_voucher ENGINE=InnoDB;

/**
-- a database structure that isn't from 2002?!
-- these foreign keys are explicitly mentioned in code
UPDATE cc_callerid SET id_cc_card = NULL WHERE id_cc_card = -1;
UPDATE cc_card SET id_group = NULL WHERE id_group = -1;
UPDATE cc_card SET id_seria = NULL WHERE id_seria = -1;
UPDATE cc_card SET tariff = NULL WHERE tariff = -1;
UPDATE cc_card_history SET id_cc_card = NULL WHERE id_cc_card = -1;
UPDATE cc_iax_buddies SET id_cc_card = NULL WHERE id_cc_card = -1;
UPDATE cc_invoice_item SET id_invoice = NULL WHERE id_invoice = -1;
UPDATE cc_outbound_cid_list SET outbound_cid_group = NULL WHERE outbound_cid_group = -1;
UPDATE cc_ratecard SET id_trunk = NULL WHERE id_trunk = -1;
UPDATE cc_ratecard SET idtariffplan = NULL WHERE idtariffplan = -1;
UPDATE cc_receipt_item SET id_receipt = NULL WHERE id_receipt = -1;
UPDATE cc_sip_buddies SET id_cc_card = NULL WHERE id_cc_card = -1;
UPDATE cc_status_log SET id_cc_card = NULL WHERE id_cc_card = -1;
UPDATE cc_support_component SET id_support = NULL WHERE id_support = -1;
UPDATE cc_ticket SET id_component = NULL WHERE id_component = -1;
UPDATE cc_ticket_comment SET id_ticket = NULL WHERE id_ticket = -1;

ALTER TABLE cc_callerid ADD CONSTRAINT fk_cc_card FOREIGN KEY (id_cc_card) REFERENCES cc_card(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_card ADD CONSTRAINT fk_cc_card_group FOREIGN KEY (id_group) REFERENCES cc_card_group(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_card ADD CONSTRAINT fk_cc_card_seria FOREIGN KEY (id_seria) REFERENCES cc_card_seria(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_card ADD CONSTRAINT fk_cc_tariffgroup FOREIGN KEY (tariff) REFERENCES cc_tariffgroup(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_card_history ADD CONSTRAINT fk_cc_card FOREIGN KEY (id_cc_card) REFERENCES cc_card(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_iax_buddies ADD CONSTRAINT fk_cc_card FOREIGN KEY (id_cc_card) REFERENCES cc_card(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_invoice_item ADD CONSTRAINT fk_cc_invoice FOREIGN KEY (id_invoice) REFERENCES cc_invoice(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_outbound_cid_list ADD CONSTRAINT fk_cc_outbound_cid_group FOREIGN KEY (outbound_cid_group) REFERENCES cc_outbound_cid_group(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_ratecard ADD CONSTRAINT fk_cc_tariffplan FOREIGN KEY (idtariffplan) REFERENCES cc_tariffplan(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_ratecard ADD CONSTRAINT fk_cc_trunk FOREIGN KEY (id_trunk) REFERENCES cc_trunk(id_trunk) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_receipt_item ADD CONSTRAINT fk_cc_receipt FOREIGN KEY (id_receipt) REFERENCES cc_receipt(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_sip_buddies ADD CONSTRAINT fk_cc_card FOREIGN KEY (id_cc_card) REFERENCES cc_card(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_status_log ADD CONSTRAINT fk_cc_card FOREIGN KEY (id_cc_card) REFERENCES cc_card(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_support_component ADD CONSTRAINT fk_cc_support FOREIGN KEY (id_support) REFERENCES cc_support(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_ticket ADD CONSTRAINT fk_cc_support_component FOREIGN KEY (id_component) REFERENCES cc_support_component(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_ticket_comment ADD CONSTRAINT fk_cc_ticket FOREIGN KEY (id_ticket) REFERENCES cc_ticket(id) ON DELETE CASCADE ON UPDATE CASCADE;

-- these foreign keys are best guesses based on name, todo: confirm in code
UPDATE cc_agent SET id_tariffgroup = NULL WHERE id_tariffgroup = -1;
UPDATE cc_agent_commission SET id_agent = NULL WHERE id_agent = -1;
UPDATE cc_agent_commission SET id_card = NULL WHERE id_card = -1;
UPDATE cc_agent_commission SET id_payment = NULL WHERE id_payment = -1;
UPDATE cc_agent_signup SET id_agent = NULL WHERE id_agent = -1;
UPDATE cc_agent_signup SET id_group = NULL WHERE id_group = -1;
UPDATE cc_agent_signup SET id_tariffgroup = NULL WHERE id_tariffgroup = -1;
UPDATE cc_agent_tariffgroup SET id_agent = NULL WHERE id_agent = -1;
UPDATE cc_agent_tariffgroup SET id_tariffgroup = NULL WHERE id_tariffgroup = -1;
UPDATE cc_alarm SET id_trunk = NULL WHERE id_trunk = -1;
UPDATE cc_alarm_report SET cc_alarm_id = NULL WHERE cc_alarm_id = -1;
UPDATE cc_billing_customer SET id_card = NULL WHERE id_card = -1;
UPDATE cc_billing_customer SET id_invoice = NULL WHERE id_invoice = -1;
UPDATE cc_call SET card_id = NULL WHERE card_id = -1;
UPDATE cc_call SET id_card_package_offer = NULL WHERE id_card_package_offer = -1;
UPDATE cc_call SET id_did = NULL WHERE id_did = -1;
UPDATE cc_call SET id_ratecard = NULL WHERE id_ratecard = -1;
UPDATE cc_call SET id_tariffgroup = NULL WHERE id_tariffgroup = -1;
UPDATE cc_call SET id_tariffplan = NULL WHERE id_tariffplan = -1;
UPDATE cc_call SET id_trunk = NULL WHERE id_trunk = -1;
UPDATE cc_call_archive SET card_id = NULL WHERE card_id = -1;
UPDATE cc_call_archive SET id_card_package_offer = NULL WHERE id_card_package_offer = -1;
UPDATE cc_call_archive SET id_did = NULL WHERE id_did = -1;
UPDATE cc_call_archive SET id_ratecard = NULL WHERE id_ratecard = -1;
UPDATE cc_call_archive SET id_tariffgroup = NULL WHERE id_tariffgroup = -1;
UPDATE cc_call_archive SET id_tariffplan = NULL WHERE id_tariffplan = -1;
UPDATE cc_call_archive SET id_trunk = NULL WHERE id_trunk = -1;
UPDATE cc_callback_spool SET id_server = NULL WHERE id_server = -1;
UPDATE cc_callback_spool SET id_server_group = NULL WHERE id_server_group = -1;
UPDATE cc_campaign SET id_campaign_config = NULL WHERE id_campaign_config = -1;
UPDATE cc_campaign SET id_card = NULL WHERE id_card = -1;
UPDATE cc_campaign SET id_cid_group = NULL WHERE id_cid_group = -1;
UPDATE cc_campaign_phonebook SET id_campaign = NULL WHERE id_campaign = -1;
UPDATE cc_campaign_phonebook SET id_phonebook = NULL WHERE id_phonebook = -1;
UPDATE cc_campaign_phonestatus SET id_callback = NULL WHERE id_callback = -1;
UPDATE cc_campaign_phonestatus SET id_campaign = NULL WHERE id_campaign = -1;
UPDATE cc_campaign_phonestatus SET id_phonenumber = NULL WHERE id_phonenumber = -1;
UPDATE cc_campaignconf_cardgroup SET id_campaign_config = NULL WHERE id_campaign_config = -1;
UPDATE cc_campaignconf_cardgroup SET id_card_group = NULL WHERE id_card_group = -1;
UPDATE cc_card SET id_campaign = NULL WHERE id_campaign = -1;
UPDATE cc_card SET id_timezone = NULL WHERE id_timezone = -1;
UPDATE cc_card_group SET id_agent = NULL WHERE id_agent = -1;
UPDATE cc_card_package_offer SET id_cc_card = NULL WHERE id_cc_card = -1;
UPDATE cc_card_package_offer SET id_cc_package_offer = NULL WHERE id_cc_package_offer = -1;
UPDATE cc_card_subscription SET id_cc_card = NULL WHERE id_cc_card = -1;
UPDATE cc_card_subscription SET id_subscription_fee = NULL WHERE id_subscription_fee = -1;
UPDATE cc_cardgroup_service SET id_card_group = NULL WHERE id_card_group = -1;
UPDATE cc_cardgroup_service SET id_service = NULL WHERE id_service = -1;
UPDATE cc_charge SET id_cc_card = NULL WHERE id_cc_card = -1;
UPDATE cc_charge SET id_cc_card_subscription = NULL WHERE id_cc_card_subscription = -1;
UPDATE cc_charge SET id_cc_did = NULL WHERE id_cc_did = -1;
UPDATE cc_charge SET iduser = NULL WHERE iduser = -1;
UPDATE cc_config SET config_group_id = NULL WHERE config_group_id = -1;
UPDATE cc_did SET id_cc_country = NULL WHERE id_cc_country = -1;
UPDATE cc_did SET id_cc_didgroup = NULL WHERE id_cc_didgroup = -1;
UPDATE cc_did_destination SET id_cc_did = NULL WHERE id_cc_did = -1;
UPDATE cc_did_use SET id_cc_card = NULL WHERE id_cc_card = -1;
UPDATE cc_did_use SET id_did = NULL WHERE id_did = -1;
UPDATE cc_invoice SET id_card = NULL WHERE id_card = -1;
UPDATE cc_invoice_item SET id_ext = NULL WHERE id_ext = -1;
UPDATE cc_invoice_payment SET id_invoice = NULL WHERE id_invoice = -1;
UPDATE cc_invoice_payment SET id_payment = NULL WHERE id_payment = -1;
UPDATE cc_logpayment SET agent_id = NULL WHERE agent_id = -1;
UPDATE cc_logpayment SET card_id = NULL WHERE card_id = -1;
UPDATE cc_logpayment SET id_logrefill = NULL WHERE id_logrefill = -1;
UPDATE cc_logpayment_agent SET agent_id = NULL WHERE agent_id = -1;
UPDATE cc_logpayment_agent SET id_logrefill = NULL WHERE id_logrefill = -1;
UPDATE cc_logrefill SET agent_id = NULL WHERE agent_id = -1;
UPDATE cc_logrefill SET card_id = NULL WHERE card_id = -1;
UPDATE cc_logrefill_agent SET agent_id = NULL WHERE agent_id = -1;
UPDATE cc_message_agent SET id_agent = NULL WHERE id_agent = -1;
UPDATE cc_notification_admin SET id_admin = NULL WHERE id_admin = -1;
UPDATE cc_notification_admin SET id_notification = NULL WHERE id_notification = -1;
UPDATE cc_packgroup_package SET package_id = NULL WHERE package_id = -1;
UPDATE cc_packgroup_package SET packagegroup_id = NULL WHERE packagegroup_id = -1;
UPDATE cc_payments_status SET status_id = NULL WHERE status_id = -1;
UPDATE cc_phonebook SET id_card = NULL WHERE id_card = -1;
UPDATE cc_phonenumber SET id_phonebook = NULL WHERE id_phonebook = -1;
UPDATE cc_ratecard SET id_outbound_cidgroup = NULL WHERE id_outbound_cidgroup = -1;
UPDATE cc_receipt SET id_card = NULL WHERE id_card = -1;
UPDATE cc_remittance_request SET id_agent = NULL WHERE id_agent = -1;
UPDATE cc_restricted_phonenumber SET id_card = NULL WHERE id_card = -1;
UPDATE cc_server_manager SET id_group = NULL WHERE id_group = -1;
UPDATE cc_service_report SET cc_service_id = NULL WHERE cc_service_id = -1;
UPDATE cc_speeddial SET id_cc_card = NULL WHERE id_cc_card = -1;
UPDATE cc_subscription_signup SET id_callplan = NULL WHERE id_callplan = -1;
UPDATE cc_subscription_signup SET id_subscription = NULL WHERE id_subscription = -1;
UPDATE cc_tariffgroup SET id_cc_package_offer = NULL WHERE id_cc_package_offer = -1;
UPDATE cc_tariffgroup SET idtariffplan = NULL WHERE idtariffplan = -1;
UPDATE cc_tariffgroup_plan SET idtariffgroup = NULL WHERE idtariffgroup = -1;
UPDATE cc_tariffgroup_plan SET idtariffplan = NULL WHERE idtariffplan = -1;
UPDATE cc_tariffplan SET id_trunk = NULL WHERE id_trunk = -1;
UPDATE cc_tariffplan SET iduser = NULL WHERE iduser = -1;
UPDATE cc_trunk SET id_provider = NULL WHERE id_provider = -1;

-- todo: cc_payments, cc_payments_agent, cc_paypal
ALTER TABLE cc_agent ADD CONSTRAINT fk_cc_tariffgroup FOREIGN KEY (id_tariffgroup) REFERENCES cc_tariffgroup(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_agent_commission ADD CONSTRAINT fk_cc_agent FOREIGN KEY (id_agent) REFERENCES cc_agent(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_agent_commission ADD CONSTRAINT fk_cc_card FOREIGN KEY (id_card) REFERENCES cc_card(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_agent_commission ADD CONSTRAINT fk_cc_payment FOREIGN KEY (id_payment) REFERENCES cc_payments(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_agent_signup ADD CONSTRAINT fk_cc_agent FOREIGN KEY (id_agent) REFERENCES cc_agent(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_agent_signup ADD CONSTRAINT fk_cc_card_group FOREIGN KEY (id_group) REFERENCES cc_card_group(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_agent_signup ADD CONSTRAINT fk_cc_tariffgroup FOREIGN KEY (id_tariffgroup) REFERENCES cc_tariffgroup(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_agent_tariffgroup ADD CONSTRAINT fk_cc_agent FOREIGN KEY (id_agent) REFERENCES cc_agent(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_agent_tariffgroup ADD CONSTRAINT fk_cc_tariffgroup FOREIGN KEY (id_tariffgroup) REFERENCES cc_tariffgroup(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_alarm ADD CONSTRAINT fk_cc_trunk FOREIGN KEY (id_trunk) REFERENCES cc_trunk(id_trunk) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_alarm_report ADD CONSTRAINT fk_cc_alarm FOREIGN KEY (cc_alarm_id) REFERENCES cc_alarm(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_billing_customer ADD CONSTRAINT fk_cc_card FOREIGN KEY (id_card) REFERENCES cc_card(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_billing_customer ADD CONSTRAINT fk_cc_invoice FOREIGN KEY (id_invoice) REFERENCES cc_invoice(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_call ADD CONSTRAINT fk_cc_card FOREIGN KEY (card_id) REFERENCES cc_card(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_call ADD CONSTRAINT fk_cc_card_package_offer FOREIGN KEY (id_card_package_offer) REFERENCES cc_card_package_offer(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_call ADD CONSTRAINT fk_cc_did FOREIGN KEY (id_did) REFERENCES cc_did(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_call ADD CONSTRAINT fk_cc_ratecard FOREIGN KEY (id_ratecard) REFERENCES cc_ratecard(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_call ADD CONSTRAINT fk_cc_tariffgroup FOREIGN KEY (id_tariffgroup) REFERENCES cc_tariffgroup(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_call ADD CONSTRAINT fk_cc_tariffplan FOREIGN KEY (id_tariffplan) REFERENCES cc_tariffplan(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_call ADD CONSTRAINT fk_cc_trunk FOREIGN KEY (id_trunk) REFERENCES cc_trunk(id_trunk) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_call_archive ADD CONSTRAINT fk_cc_card FOREIGN KEY (card_id) REFERENCES cc_card(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_call_archive ADD CONSTRAINT fk_cc_card_package_offer FOREIGN KEY (id_card_package_offer) REFERENCES cc_card_package_offer(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_call_archive ADD CONSTRAINT fk_cc_did FOREIGN KEY (id_did) REFERENCES cc_did(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_call_archive ADD CONSTRAINT fk_cc_ratecard FOREIGN KEY (id_ratecard) REFERENCES cc_ratecard(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_call_archive ADD CONSTRAINT fk_cc_tariffgroup FOREIGN KEY (id_tariffgroup) REFERENCES cc_tariffgroup(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_call_archive ADD CONSTRAINT fk_cc_tariffplan FOREIGN KEY (id_tariffplan) REFERENCES cc_tariffplan(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_call_archive ADD CONSTRAINT fk_cc_trunk FOREIGN KEY (id_trunk) REFERENCES cc_trunk(id_trunk) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_callback_spool ADD CONSTRAINT fk_cc_server FOREIGN KEY (id_server) REFERENCES cc_server_manager(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_callback_spool ADD CONSTRAINT fk_cc_server_group FOREIGN KEY (id_server_group) REFERENCES cc_server_group(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_campaign ADD CONSTRAINT fk_cc_campaign_config FOREIGN KEY (id_campaign_config) REFERENCES cc_campaign_config(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_campaign ADD CONSTRAINT fk_cc_card FOREIGN KEY (id_card) REFERENCES cc_card(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_campaign ADD CONSTRAINT fk_cc_outbound_cid_group FOREIGN KEY (id_cid_group) REFERENCES cc_outbound_cid_group(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_campaign_phonebook ADD CONSTRAINT fk_cc_campaign FOREIGN KEY (id_campaign) REFERENCES cc_campaign(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_campaign_phonebook ADD CONSTRAINT fk_cc_phonebook FOREIGN KEY (id_phonebook) REFERENCES cc_phonebook(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_campaign_phonestatus ADD CONSTRAINT fk_cc_callback_spool FOREIGN KEY (id_callback) REFERENCES cc_callback_spool(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_campaign_phonestatus ADD CONSTRAINT fk_cc_campaign FOREIGN KEY (id_campaign) REFERENCES cc_campaign(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_campaign_phonestatus ADD CONSTRAINT fk_cc_phonenumber FOREIGN KEY (id_phonenumber) REFERENCES cc_phonenumber(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_campaignconf_cardgroup ADD CONSTRAINT fk_cc_campaign_config FOREIGN KEY (id_campaign_config) REFERENCES cc_campaign_config(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_campaignconf_cardgroup ADD CONSTRAINT fk_cc_card_group FOREIGN KEY (id_card_group) REFERENCES cc_card_group(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_card ADD CONSTRAINT fk_cc_campaign FOREIGN KEY (id_campaign) REFERENCES cc_campaign(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_card ADD CONSTRAINT fk_cc_timezone FOREIGN KEY (id_timezone) REFERENCES cc_timezone(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_card_group ADD CONSTRAINT fk_cc_agent FOREIGN KEY (id_agent) REFERENCES cc_agent(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_card_package_offer ADD CONSTRAINT fk_cc_card FOREIGN KEY (id_cc_card) REFERENCES cc_card(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_card_package_offer ADD CONSTRAINT fk_cc_package_offer FOREIGN KEY (id_cc_package_offer) REFERENCES cc_package_offer(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_card_subscription ADD CONSTRAINT fk_cc_card FOREIGN KEY (id_cc_card) REFERENCES cc_card(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_card_subscription ADD CONSTRAINT fk_cc_subscription_service FOREIGN KEY (id_subscription_fee) REFERENCES cc_subscription_service(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_cardgroup_service ADD CONSTRAINT fk_cc_card_group FOREIGN KEY (id_card_group) REFERENCES cc_card_group(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_cardgroup_service ADD CONSTRAINT fk_cc_service FOREIGN KEY (id_service) REFERENCES cc_service(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_charge ADD CONSTRAINT fk_cc_card FOREIGN KEY (id_cc_card) REFERENCES cc_card(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_charge ADD CONSTRAINT fk_cc_card FOREIGN KEY (iduser) REFERENCES cc_ui_authen(userid) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_charge ADD CONSTRAINT fk_cc_card_subscription FOREIGN KEY (id_cc_card_subscription) REFERENCES cc_card_subscription(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_charge ADD CONSTRAINT fk_cc_did FOREIGN KEY (id_cc_did) REFERENCES cc_did(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_config ADD CONSTRAINT fk_cc_config_group FOREIGN KEY (config_group_id) REFERENCES cc_config_group(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_did ADD CONSTRAINT fk_cc_country FOREIGN KEY (id_cc_country) REFERENCES cc_country(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_did ADD CONSTRAINT fk_cc_didgroup FOREIGN KEY (id_cc_didgroup) REFERENCES cc_didgroup(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_did_destination ADD CONSTRAINT fk_cc_card FOREIGN KEY (id_cc_card) REFERENCES cc_card(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_did_destination ADD CONSTRAINT fk_cc_did FOREIGN KEY (id_cc_did) REFERENCES cc_did(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_did_use ADD CONSTRAINT fk_cc_card FOREIGN KEY (id_cc_card) REFERENCES cc_card(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_did_use ADD CONSTRAINT fk_cc_did FOREIGN KEY (id_did) REFERENCES cc_did(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_invoice ADD CONSTRAINT fk_cc_card FOREIGN KEY (id_card) REFERENCES cc_card(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_invoice_item ADD CONSTRAINT fk_cc_billing_customer FOREIGN KEY (id_ext) REFERENCES cc_billing_customer(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_invoice_payment ADD CONSTRAINT fk_cc_invoice FOREIGN KEY (id_invoice) REFERENCES cc_invoice(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_invoice_payment ADD CONSTRAINT fk_cc_payments FOREIGN KEY (id_payment) REFERENCES cc_payments(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_logpayment ADD CONSTRAINT fk_cc_agent FOREIGN KEY (agent_id) REFERENCES cc_agent(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_logpayment ADD CONSTRAINT fk_cc_card FOREIGN KEY (card_id) REFERENCES cc_card(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_logpayment ADD CONSTRAINT fk_cc_logrefill FOREIGN KEY (id_logrefill) REFERENCES cc_logrefill(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_logpayment_agent ADD CONSTRAINT fk_cc_agent FOREIGN KEY (agent_id) REFERENCES cc_agent(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_logpayment_agent ADD CONSTRAINT fk_cc_logrefill FOREIGN KEY (id_logrefill) REFERENCES cc_logrefill(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_logrefill ADD CONSTRAINT fk_cc_agent FOREIGN KEY (agent_id) REFERENCES cc_agent(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_logrefill ADD CONSTRAINT fk_cc_card FOREIGN KEY (card_id) REFERENCES cc_card(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_logrefill_agent ADD CONSTRAINT fk_cc_agent FOREIGN KEY (agent_id) REFERENCES cc_agent(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_message_agent ADD CONSTRAINT fk_cc_agent FOREIGN KEY (id_agent) REFERENCES cc_agent(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_notification_admin ADD CONSTRAINT fk_cc_notification FOREIGN KEY (id_notification) REFERENCES cc_notification(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_notification_admin ADD CONSTRAINT fk_cc_ui_authen FOREIGN KEY (id_admin) REFERENCES cc_ui_authen(userid) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_packgroup_package ADD CONSTRAINT fk_cc_package_group FOREIGN KEY (packagegroup_id) REFERENCES cc_package_group(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_packgroup_package ADD CONSTRAINT fk_cc_package_offer FOREIGN KEY (package_id) REFERENCES cc_package_offer(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_payments_status ADD CONSTRAINT fk_cc_status FOREIGN KEY (status_id) REFERENCES cc_payments_status(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_phonebook ADD CONSTRAINT fk_cc_card FOREIGN KEY (id_card) REFERENCES cc_card(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_phonenumber ADD CONSTRAINT fk_cc_phonebook FOREIGN KEY (id_phonebook) REFERENCES cc_phonebook(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_ratecard ADD CONSTRAINT fk_cc_outbound_cid_group FOREIGN KEY (id_outbound_cidgroup) REFERENCES cc_outbound_cid_group(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_receipt ADD CONSTRAINT fk_cc_card FOREIGN KEY (id_card) REFERENCES cc_card(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_remittance_request ADD CONSTRAINT fk_cc_agent FOREIGN KEY (id_agent) REFERENCES cc_agent(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_restricted_phonenumber ADD CONSTRAINT fk_cc_card FOREIGN KEY (id_card) REFERENCES cc_card(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_server_manager ADD CONSTRAINT fk_cc_server_group FOREIGN KEY (id_group) REFERENCES cc_server_group(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_service_report ADD CONSTRAINT fk_cc_service FOREIGN KEY (cc_service_id) REFERENCES cc_service(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_speeddial ADD CONSTRAINT fk_cc_card FOREIGN KEY (id_cc_card) REFERENCES cc_card(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_subscription_signup ADD CONSTRAINT fk_cc_callplan FOREIGN KEY (id_callplan) REFERENCES cc_tariffgroup(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_subscription_signup ADD CONSTRAINT fk_cc_subscription FOREIGN KEY (id_subscription) REFERENCES cc_subscription_service(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_tariffgroup ADD CONSTRAINT fk_cc_package_offer FOREIGN KEY (id_cc_package_offer) REFERENCES cc_package_offer(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_tariffgroup ADD CONSTRAINT fk_cc_tariffplan FOREIGN KEY (idtariffplan) REFERENCES cc_tariffplan(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_tariffgroup_plan ADD CONSTRAINT fk_cc_tariffgroup FOREIGN KEY (idtariffgroup) REFERENCES cc_tariffgroup(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_tariffgroup_plan ADD CONSTRAINT fk_cc_tariffplan FOREIGN KEY (idtariffplan) REFERENCES cc_tariffplan(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_tariffplan ADD CONSTRAINT fk_cc_card FOREIGN KEY (iduser) REFERENCES cc_ui_authen(userid) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_tariffplan ADD CONSTRAINT fk_cc_trunk FOREIGN KEY (id_trunk) REFERENCES cc_trunk(id_trunk) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE cc_trunk ADD CONSTRAINT fk_cc_provider FOREIGN KEY (id_provider) REFERENCES cc_provider(id) ON DELETE CASCADE ON UPDATE CASCADE;
*/
