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
UPDATE cc_version SET version = '3.0.0';

ALTER TABLE cc_call_archive ADD a2b_custom1 VARCHAR(20) DEFAULT NULL, ADD a2b_custom2 VARCHAR(20) DEFAULT NULL;
ALTER TABLE cc_call_archive MODIFY calledstation VARCHAR(100) NOT NULL;

DELETE FROM cc_config WHERE config_key IN ('asterisk_version', 'cront_currency_update', 'didx_id', 'didx_pass', 'didx_min_rating', 'didx_ring_to');

-- stop using timestamps
ALTER TABLE cc_agent MODIFY datecreation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_agent_commission MODIFY `date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_alarm
    MODIFY datecreate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    MODIFY datelastrun DATETIME NOT NULL DEFAULT '0000-00-00',
;
ALTER TABLE cc_alarm_report MODIFY daterun DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_autofill_report MODIFY daterun DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_backup MODIFY creationdate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_billing_customer
    MODIFY `date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    MODIFY start_date DATETIME NULL
;
ALTER TABLE cc_call
    MODIFY starttime DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    MODIFY stoptime DATETIME NOT NULL DEFAULT '0000-00-00'
;
ALTER TABLE cc_call_archive
    MODIFY starttime DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    MODIFY stoptime DATETIME NOT NULL DEFAULT '0000-00-00'
;
ALTER TABLE cc_callback_spool
    MODIFY entry_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    MODIFY last_attempt_time DATETIME NOT NULL DEFAULT '0000-00-00'
    MODIFY callback_time DATETIME NOT NULL DEFAULT '0000-00-00'
;
ALTER TABLE cc_campaign
    MODIFY creationdate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    MODIFY startingdate DATETIME NOT NULL DEFAULT '0000-00-00',
    MODIFY expirationdate DATETIME NOT NULL DEFAULT '0000-00-00',
;
ALTER TABLE cc_campaign_phonestatus MODIFY lastuse DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_card
    DROP activated,
    MODIFY creationdate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    MODIFY firstusedate DATETIME NOT NULL DEFAULT '0000-00-00',
    MODIFY expirationdate DATETIME NOT NULL DEFAULT '0000-00-00',
    MODIFY lastuse DATETIME NOT NULL DEFAULT '0000-00-00',
    MODIFY servicelastrun DATETIME NOT NULL DEFAULT '0000-00-00',
    MODIFY last_notification DATETIME NULL,
    MODIFY lock_date DATETIME NULL
;
ALTER TABLE cc_card_archive
    DROP activated,
    MODIFY creationdate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    MODIFY firstusedate DATETIME NOT NULL DEFAULT '0000-00-00',
    MODIFY expirationdate DATETIME NOT NULL DEFAULT '0000-00-00',
    MODIFY lastuse DATETIME NOT NULL DEFAULT '0000-00-00',
    MODIFY servicelastrun DATETIME NOT NULL DEFAULT '0000-00-00',
    MODIFY last_notification DATETIME NULL,
;
ALTER TABLE cc_card_history MODIFY datecreated DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_card_package_offer MODIFY date_consumption DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_card_subscription
    MODIFY startdate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    MODIFY stopdate DATETIME NOT NULL DEFAULT '0000-00-00'
    MODIFY last_run DATETIME NOT NULL DEFAULT '0000-00-00'
    MODIFY next_billing_date DATETIME NOT NULL DEFAULT '0000-00-00'
    MODIFY limit_pay_date DATETIME NOT NULL DEFAULT '0000-00-00'
;
ALTER TABLE cc_charge MODIFY creationdate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_currencies MODIFY lastupdate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_did
    MODIFY creationdate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    MODIFY startingdate DATETIME NOT NULL DEFAULT '0000-00-00',
    MODIFY expirationdate DATETIME NOT NULL DEFAULT '0000-00-00',
;
ALTER TABLE cc_did_destination MODIFY creationdate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_did_use
    MODIFY reservationdate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    MODIFY releasedate DATETIME NOT NULL DEFAULT '0000-00-00'
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
    MODIFY stopdate DATETIME NOT NULL DEFAULT '0000-00-00'
;
ALTER TABLE cc_receipt MODIFY `date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_receipt_item MODIFY `date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_remittance_request MODIFY `date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_server_manager MODIFY lasttime_used DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_service
    MODIFY datecreate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    MODIFY datelastrun DATETIME NOT NULL DEFAULT '0000-00-00'
;
ALTER TABLE cc_service_report MODIFY daterun DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_speeddial MODIFY creationdate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_status_log MODIFY updated_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_subscription_service
    MODIFY datecreate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    MODIFY datelastrun DATETIME NOT NULL DEFAULT '0000-00-00',
    MODIFY startdate DATETIME NOT NULL DEFAULT '0000-00-00',
    MODIFY stopdate DATETIME NOT NULL DEFAULT '0000-00-00'
;
ALTER TABLE cc_system_log MODIFY creationdate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_tariffgroup MODIFY creationdate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_tariffplan
    MODIFY creationdate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    MODIFY startingdate DATETIME NOT NULL DEFAULT '0000-00-00',
    MODIFY expirationdate DATETIME NOT NULL DEFAULT '0000-00-00'
;
ALTER TABLE cc_ticket MODIFY creationdate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_ticket_comment MODIFY `date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_trunk MODIFY creationdate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_ui_authen MODIFY datecreation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE cc_voucher
    MODIFY creationdate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    MODIFY usedate DATETIME NOT NULL DEFAULT '0000-00-00',
    MODIFY expirationdate DATETIME NOT NULL DEFAULT '0000-00-00'
;
