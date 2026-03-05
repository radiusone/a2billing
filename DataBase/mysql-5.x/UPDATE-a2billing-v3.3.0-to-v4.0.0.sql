SET FOREIGN_KEY_CHECKS = 0;

UPDATE cc_version SET version = '4.0.0' LIMIT 1;

DELETE FROM cc_config WHERE config_key = 'cache_enabled' OR config_key = 'cache_path';

-- now unused since cc_payment was dropped in 3.3.0
ALTER TABLE cc_agent_commission DROP COLUMN IF EXISTS id_payment;
DROP TABLE IF EXISTS cc_invoice_payment;

-- the page said "campaigns" are unused, so lets remove them
ALTER TABLE cc_card DROP COLUMN IF EXISTS id_campaign;
ALTER TABLE cc_card_archive DROP COLUMN IF EXISTS id_campaign;
DROP TABLE IF EXISTS cc_campaign, cc_campaign_config, cc_campaign_phonebook, cc_campaign_phonestatus, cc_campaignconf_cardgroup;
DELETE FROM cc_config WHERE config_key = 'context_campaign_callback' OR config_key = 'default_context_campaign';

-- unused (never used?)
ALTER TABLE cc_card DROP COLUMN IF EXISTS traffic, DROP COLUMN IF EXISTS traffic_target;
ALTER TABLE cc_card_archive DROP COLUMN IF EXISTS traffic, DROP COLUMN IF EXISTS traffic_target;
DELETE FROM cc_config WHERE config_key = 'field_traffic' OR config_key = 'field_traffic_target';

-- this should be a 0/1 not t/f/'0'/'1'/???
UPDATE cc_agent SET active = IF(active = 't' OR active = 1, 1, 0);
ALTER TABLE cc_agent MODIFY COLUMN active tinyint NOT NULL DEFAULT 0;
UPDATE cc_callerid SET activated = IF(activated = 't' OR activated = 1, 1, 0);
ALTER TABLE cc_callerid MODIFY COLUMN activated tinyint NOT NULL DEFAULT 1;

-- use country code as pk, it allows for easier updates
ALTER TABLE cc_did ADD IF NOT EXISTS country varchar(3) AFTER id_cc_country;
UPDATE cc_did SET country = (SELECT countrycode FROM cc_country WHERE id = id_cc_country);
ALTER TABLE cc_did DROP COLUMN IF EXISTS id_cc_country;
ALTER TABLE cc_country
    DROP PRIMARY KEY,
    DROP COLUMN IF EXISTS id,
    MODIFY COLUMN countrycode varchar(3) NOT NULL PRIMARY KEY;

-- a database structure that isn't from 2002‽

-- primary keys
ALTER TABLE cc_agent_tariffgroup
    DROP PRIMARY KEY,
    ADD UNIQUE INDEX (id_agent, id_tariffgroup),
    MODIFY id_agent BIGINT NULL DEFAULT NULL,
    MODIFY id_tariffgroup BIGINT NULL DEFAULT NULL;

ALTER TABLE cc_cardgroup_service
    DROP PRIMARY KEY,
    ADD UNIQUE INDEX (id_card_group, id_service),
    MODIFY id_card_group BIGINT NULL DEFAULT NULL,
    MODIFY id_service BIGINT NULL DEFAULT NULL;

ALTER TABLE cc_notification_admin
    DROP PRIMARY KEY,
    ADD UNIQUE INDEX (id_admin, id_notification),
    MODIFY id_admin BIGINT NULL DEFAULT NULL,
    MODIFY id_notification BIGINT NULL DEFAULT NULL;

ALTER TABLE cc_packgroup_package
    DROP PRIMARY KEY,
    ADD UNIQUE INDEX (packagegroup_id, package_id),
    MODIFY packagegroup_id BIGINT NULL DEFAULT NULL,
    MODIFY package_id BIGINT NULL DEFAULT NULL;

ALTER TABLE cc_tariffgroup_plan
    DROP PRIMARY KEY,
    ADD UNIQUE INDEX (idtariffgroup, idtariffplan),
    MODIFY idtariffgroup BIGINT NULL DEFAULT NULL,
    MODIFY idtariffplan BIGINT NULL DEFAULT NULL;

UPDATE cc_callerid SET id_cc_card = NULL WHERE id_cc_card = -1;
ALTER TABLE cc_callerid
    ADD CONSTRAINT fk_cc_callerid_cc_card FOREIGN KEY (id_cc_card) REFERENCES cc_card(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_card SET id_group = NULL WHERE id_group = -1;
UPDATE cc_card SET id_seria = NULL WHERE id_seria = -1;
UPDATE cc_card SET tariff = NULL WHERE tariff = -1;
UPDATE cc_card SET country = NULL WHERE country = '';
ALTER TABLE cc_card
    ADD CONSTRAINT fk_cc_card_cc_card_group FOREIGN KEY (id_group) REFERENCES cc_card_group(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_card_cc_card_seria FOREIGN KEY (id_seria) REFERENCES cc_card_seria(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_card_cc_tariffgroup FOREIGN KEY (tariff) REFERENCES cc_tariffgroup(id) ON DELETE SET NULL ON UPDATE CASCADE,
    MODIFY COLUMN country varchar(3),
    ADD CONSTRAINT fk_cc_card_cc_country FOREIGN KEY (country) REFERENCES cc_country(countrycode) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_card_history SET id_cc_card = NULL WHERE id_cc_card = -1;
ALTER TABLE cc_card_history
    ADD CONSTRAINT fk_cc_card_history_cc_card FOREIGN KEY (id_cc_card) REFERENCES cc_card(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_iax_buddies SET id_cc_card = NULL WHERE id_cc_card = -1;
ALTER TABLE cc_iax_buddies
    ADD CONSTRAINT fk_cc_iax_buddies_cc_card FOREIGN KEY (id_cc_card) REFERENCES cc_card(id) ON DELETE SET NULL ON UPDATE CASCADE;

DELETE FROM cc_invoice_item WHERE id_invoice = -1;
ALTER TABLE cc_invoice_item
    ADD CONSTRAINT fk_cc_invoice_item_cc_invoice FOREIGN KEY (id_invoice) REFERENCES cc_invoice(id) ON DELETE CASCADE ON UPDATE CASCADE;

UPDATE cc_outbound_cid_list SET outbound_cid_group = NULL WHERE outbound_cid_group = -1;
ALTER TABLE cc_outbound_cid_list
    ADD CONSTRAINT fk_cc_outbound_cid_list_cc_outbound_cid_group FOREIGN KEY (outbound_cid_group) REFERENCES cc_outbound_cid_group(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_ratecard SET id_trunk = NULL WHERE id_trunk = -1;
UPDATE cc_ratecard SET idtariffplan = NULL WHERE idtariffplan = -1;
ALTER TABLE cc_ratecard
    ADD CONSTRAINT fk_cc_ratecard_cc_tariffplan FOREIGN KEY (idtariffplan) REFERENCES cc_tariffplan(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_ratecard_cc_trunk FOREIGN KEY (id_trunk) REFERENCES cc_trunk(id_trunk) ON DELETE SET NULL ON UPDATE CASCADE;

DELETE FROM cc_receipt_item WHERE id_receipt = -1;
ALTER TABLE cc_receipt_item
    ADD CONSTRAINT fk_cc_receipt_item_cc_receipt FOREIGN KEY (id_receipt) REFERENCES cc_receipt(id) ON DELETE CASCADE ON UPDATE CASCADE;

UPDATE cc_sip_buddies SET id_cc_card = NULL WHERE id_cc_card = -1;
ALTER TABLE cc_sip_buddies
    ADD CONSTRAINT fk_cc_sip_buddies_cc_card FOREIGN KEY (id_cc_card) REFERENCES cc_card(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_status_log SET id_cc_card = NULL WHERE id_cc_card = -1;
ALTER TABLE cc_status_log
    ADD CONSTRAINT fk_cc_status_log_cc_card FOREIGN KEY (id_cc_card) REFERENCES cc_card(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_support_component SET id_support = NULL WHERE id_support = -1;
ALTER TABLE cc_support_component
    ADD CONSTRAINT fk_cc_support_component_cc_support FOREIGN KEY (id_support) REFERENCES cc_support(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_ticket SET id_component = NULL WHERE id_component = -1;
ALTER TABLE cc_ticket
    ADD CONSTRAINT fk_cc_ticket_cc_support_component FOREIGN KEY (id_component) REFERENCES cc_support_component(id) ON DELETE SET NULL ON UPDATE CASCADE;

DELETE FROM cc_ticket_comment WHERE id_ticket = -1;
ALTER TABLE cc_ticket_comment
    ADD CONSTRAINT fk_cc_ticket_comment_cc_ticket FOREIGN KEY (id_ticket) REFERENCES cc_ticket(id) ON DELETE CASCADE ON UPDATE CASCADE;

UPDATE cc_agent SET id_tariffgroup = NULL WHERE id_tariffgroup = -1;
ALTER TABLE cc_agent
    ADD CONSTRAINT fk_cc_agent_cc_tariffgroup FOREIGN KEY (id_tariffgroup) REFERENCES cc_tariffgroup(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_agent_commission SET id_agent = NULL WHERE id_agent = -1;
UPDATE cc_agent_commission SET id_card = NULL WHERE id_card = -1;
ALTER TABLE cc_agent_commission
    ADD CONSTRAINT fk_cc_agent_commission_cc_agent FOREIGN KEY (id_agent) REFERENCES cc_agent(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_agent_commission_cc_card FOREIGN KEY (id_card) REFERENCES cc_card(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_agent_signup SET id_agent = NULL WHERE id_agent = -1;
UPDATE cc_agent_signup SET id_group = NULL WHERE id_group = -1;
UPDATE cc_agent_signup SET id_tariffgroup = NULL WHERE id_tariffgroup = -1;
ALTER TABLE cc_agent_signup
    ADD CONSTRAINT fk_cc_agent_signup_cc_agent FOREIGN KEY (id_agent) REFERENCES cc_agent(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_agent_signup_cc_card_group FOREIGN KEY (id_group) REFERENCES cc_card_group(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_agent_signup_cc_tariffgroup FOREIGN KEY (id_tariffgroup) REFERENCES cc_tariffgroup(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_agent_tariffgroup SET id_agent = NULL WHERE id_agent = -1;
UPDATE cc_agent_tariffgroup SET id_tariffgroup = NULL WHERE id_tariffgroup = -1;
ALTER TABLE cc_agent_tariffgroup
    ADD CONSTRAINT fk_cc_agent_tariffgroup_cc_agent FOREIGN KEY (id_agent) REFERENCES cc_agent(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_agent_tariffgroup_cc_tariffgroup FOREIGN KEY (id_tariffgroup) REFERENCES cc_tariffgroup(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_alarm SET id_trunk = NULL WHERE id_trunk = -1;
ALTER TABLE cc_alarm
    ADD CONSTRAINT fk_cc_alarm_cc_trunk FOREIGN KEY (id_trunk) REFERENCES cc_trunk(id_trunk) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_alarm_report SET cc_alarm_id = NULL WHERE cc_alarm_id = -1;
ALTER TABLE cc_alarm_report
    ADD CONSTRAINT fk_cc_alarm_report_cc_alarm FOREIGN KEY (cc_alarm_id) REFERENCES cc_alarm(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_billing_customer SET id_card = NULL WHERE id_card = -1;
UPDATE cc_billing_customer SET id_invoice = NULL WHERE id_invoice = -1;
ALTER TABLE cc_billing_customer
    ADD CONSTRAINT fk_cc_billing_customer_cc_card FOREIGN KEY (id_card) REFERENCES cc_card(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_billing_customer_cc_invoice FOREIGN KEY (id_invoice) REFERENCES cc_invoice(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_call SET card_id = NULL WHERE card_id = -1;
UPDATE cc_call SET id_card_package_offer = NULL WHERE id_card_package_offer = -1;
UPDATE cc_call SET id_did = NULL WHERE id_did = -1;
UPDATE cc_call SET id_ratecard = NULL WHERE id_ratecard = -1;
UPDATE cc_call SET id_tariffgroup = NULL WHERE id_tariffgroup = -1;
UPDATE cc_call SET id_tariffplan = NULL WHERE id_tariffplan = -1;
UPDATE cc_call SET id_trunk = NULL WHERE id_trunk = -1;
ALTER TABLE cc_call
    ADD CONSTRAINT fk_cc_call_cc_card FOREIGN KEY (card_id) REFERENCES cc_card(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_call_cc_card_package_offer FOREIGN KEY (id_card_package_offer) REFERENCES cc_card_package_offer(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_call_cc_did FOREIGN KEY (id_did) REFERENCES cc_did(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_call_cc_ratecard FOREIGN KEY (id_ratecard) REFERENCES cc_ratecard(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_call_cc_tariffgroup FOREIGN KEY (id_tariffgroup) REFERENCES cc_tariffgroup(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_call_cc_tariffplan FOREIGN KEY (id_tariffplan) REFERENCES cc_tariffplan(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_call_cc_trunk FOREIGN KEY (id_trunk) REFERENCES cc_trunk(id_trunk) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_call_archive SET card_id = NULL WHERE card_id = -1;
UPDATE cc_call_archive SET id_card_package_offer = NULL WHERE id_card_package_offer = -1;
UPDATE cc_call_archive SET id_did = NULL WHERE id_did = -1;
UPDATE cc_call_archive SET id_ratecard = NULL WHERE id_ratecard = -1;
UPDATE cc_call_archive SET id_tariffgroup = NULL WHERE id_tariffgroup = -1;
UPDATE cc_call_archive SET id_tariffplan = NULL WHERE id_tariffplan = -1;
UPDATE cc_call_archive SET id_trunk = NULL WHERE id_trunk = -1;
ALTER TABLE cc_call_archive
    ADD CONSTRAINT fk_cc_call_archive_cc_card FOREIGN KEY (card_id) REFERENCES cc_card(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_call_archive_cc_card_package_offer FOREIGN KEY (id_card_package_offer) REFERENCES cc_card_package_offer(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_call_archive_cc_did FOREIGN KEY (id_did) REFERENCES cc_did(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_call_archive_cc_ratecard FOREIGN KEY (id_ratecard) REFERENCES cc_ratecard(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_call_archive_cc_tariffgroup FOREIGN KEY (id_tariffgroup) REFERENCES cc_tariffgroup(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_call_archive_cc_tariffplan FOREIGN KEY (id_tariffplan) REFERENCES cc_tariffplan(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_call_archive_cc_trunk FOREIGN KEY (id_trunk) REFERENCES cc_trunk(id_trunk) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_callback_spool SET id_server = NULL WHERE id_server = -1;
UPDATE cc_callback_spool SET id_server_group = NULL WHERE id_server_group = -1;
ALTER TABLE cc_callback_spool
    ADD CONSTRAINT fk_cc_callback_spool_cc_server FOREIGN KEY (id_server) REFERENCES cc_server_manager(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_callback_spool_cc_server_group FOREIGN KEY (id_server_group) REFERENCES cc_server_group(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_card SET id_timezone = NULL WHERE id_timezone = -1;
ALTER TABLE cc_card
    ADD CONSTRAINT fk_cc_card_cc_timezone FOREIGN KEY (id_timezone) REFERENCES cc_timezone(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_card_group SET id_agent = NULL WHERE id_agent = -1;
ALTER TABLE cc_card_group
    ADD CONSTRAINT fk_cc_card_group_cc_agent FOREIGN KEY (id_agent) REFERENCES cc_agent(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_card_package_offer SET id_cc_card = NULL WHERE id_cc_card = -1;
UPDATE cc_card_package_offer SET id_cc_package_offer = NULL WHERE id_cc_package_offer = -1;
ALTER TABLE cc_card_package_offer
    ADD CONSTRAINT fk_cc_card_package_offer_cc_card FOREIGN KEY (id_cc_card) REFERENCES cc_card(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_card_package_offer_cc_package_offer FOREIGN KEY (id_cc_package_offer) REFERENCES cc_package_offer(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_card_subscription SET id_cc_card = NULL WHERE id_cc_card = -1;
UPDATE cc_card_subscription SET id_subscription_fee = NULL WHERE id_subscription_fee = -1;
ALTER TABLE cc_card_subscription
    ADD CONSTRAINT fk_cc_card_subscription_cc_card FOREIGN KEY (id_cc_card) REFERENCES cc_card(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_card_subscription_cc_subscription_service FOREIGN KEY (id_subscription_fee) REFERENCES cc_subscription_service(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_cardgroup_service SET id_card_group = NULL WHERE id_card_group = -1;
UPDATE cc_cardgroup_service SET id_service = NULL WHERE id_service = -1;
ALTER TABLE cc_cardgroup_service
    ADD CONSTRAINT fk_cc_cardgroup_service_cc_card_group FOREIGN KEY (id_card_group) REFERENCES cc_card_group(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_cardgroup_service_cc_service FOREIGN KEY (id_service) REFERENCES cc_service(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_charge SET id_cc_card = NULL WHERE id_cc_card = -1;
UPDATE cc_charge SET id_cc_card_subscription = NULL WHERE id_cc_card_subscription = -1;
UPDATE cc_charge SET id_cc_did = NULL WHERE id_cc_did = -1;
UPDATE cc_charge SET iduser = NULL WHERE iduser = -1;
ALTER TABLE cc_charge
    ADD CONSTRAINT fk_cc_charge_cc_card FOREIGN KEY (id_cc_card) REFERENCES cc_card(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_charge_cc_ui_authen FOREIGN KEY (iduser) REFERENCES cc_ui_authen(userid) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_charge_cc_card_subscription FOREIGN KEY (id_cc_card_subscription) REFERENCES cc_card_subscription(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_charge_cc_did FOREIGN KEY (id_cc_did) REFERENCES cc_did(id) ON DELETE SET NULL ON UPDATE CASCADE;

DELETE FROM cc_config WHERE config_group_id = -1;
ALTER TABLE cc_config
    ADD CONSTRAINT fk_cc_config_cc_config_group FOREIGN KEY (config_group_id) REFERENCES cc_config_group(id) ON DELETE CASCADE ON UPDATE CASCADE;

UPDATE cc_did SET id_cc_didgroup = NULL WHERE id_cc_didgroup = -1;
ALTER TABLE cc_did
    ADD CONSTRAINT fk_cc_did_cc_country FOREIGN KEY (country) REFERENCES cc_country(countrycode) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_did_cc_didgroup FOREIGN KEY (id_cc_didgroup) REFERENCES cc_didgroup(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_did_destination SET id_cc_card = NULL WHERE id_cc_card = -1;
UPDATE cc_did_destination SET id_cc_did = NULL WHERE id_cc_did = -1;
ALTER TABLE cc_did_destination
    ADD CONSTRAINT fk_cc_did_destination_cc_card FOREIGN KEY (id_cc_card) REFERENCES cc_card(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_did_destination_cc_did FOREIGN KEY (id_cc_did) REFERENCES cc_did(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_did_use SET id_cc_card = NULL WHERE id_cc_card = -1;
UPDATE cc_did_use SET id_did = NULL WHERE id_did = -1;
ALTER TABLE cc_did_use
    ADD CONSTRAINT fk_cc_did_use_cc_card FOREIGN KEY (id_cc_card) REFERENCES cc_card(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_did_use_cc_did FOREIGN KEY (id_did) REFERENCES cc_did(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_invoice SET id_card = NULL WHERE id_card = -1;
ALTER TABLE cc_invoice
    ADD CONSTRAINT fk_cc_invoice_cc_card FOREIGN KEY (id_card) REFERENCES cc_card(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_invoice_item SET id_ext = NULL WHERE id_ext = -1;
ALTER TABLE cc_invoice_item
    ADD CONSTRAINT fk_cc_invoice_item_cc_billing_customer FOREIGN KEY (id_ext) REFERENCES cc_billing_customer(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_logpayment SET agent_id = NULL WHERE agent_id = -1;
UPDATE cc_logpayment SET card_id = NULL WHERE card_id = -1;
UPDATE cc_logpayment SET id_logrefill = NULL WHERE id_logrefill = -1;
ALTER TABLE cc_logpayment
    ADD CONSTRAINT fk_cc_logpayment_cc_agent FOREIGN KEY (agent_id) REFERENCES cc_agent(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_logpayment_cc_card FOREIGN KEY (card_id) REFERENCES cc_card(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_logpayment_cc_logrefill FOREIGN KEY (id_logrefill) REFERENCES cc_logrefill(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_logpayment_agent SET agent_id = NULL WHERE agent_id = -1;
UPDATE cc_logpayment_agent SET id_logrefill = NULL WHERE id_logrefill = -1;
ALTER TABLE cc_logpayment_agent
    ADD CONSTRAINT fk_cc_logpayment_agent_cc_agent FOREIGN KEY (agent_id) REFERENCES cc_agent(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_logpayment_agent_cc_logrefill FOREIGN KEY (id_logrefill) REFERENCES cc_logrefill(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_logrefill SET agent_id = NULL WHERE agent_id = -1;
UPDATE cc_logrefill SET card_id = NULL WHERE card_id = -1;
ALTER TABLE cc_logrefill
    ADD CONSTRAINT fk_cc_logrefill_cc_agent FOREIGN KEY (agent_id) REFERENCES cc_agent(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_logrefill_cc_card FOREIGN KEY (card_id) REFERENCES cc_card(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_logrefill_agent SET agent_id = NULL WHERE agent_id = -1;
ALTER TABLE cc_logrefill_agent
    ADD CONSTRAINT fk_cc_logrefill_agent_cc_agent FOREIGN KEY (agent_id) REFERENCES cc_agent(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_message_agent SET id_agent = NULL WHERE id_agent = -1;
ALTER TABLE cc_message_agent
    ADD CONSTRAINT fk_cc_message_agent_cc_agent FOREIGN KEY (id_agent) REFERENCES cc_agent(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_notification_admin SET id_admin = NULL WHERE id_admin = -1;
UPDATE cc_notification_admin SET id_notification = NULL WHERE id_notification = -1;
ALTER TABLE cc_notification_admin
    ADD CONSTRAINT fk_cc_notification_admin_cc_notification FOREIGN KEY (id_notification) REFERENCES cc_notification(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_notification_admin_cc_ui_authen FOREIGN KEY (id_admin) REFERENCES cc_ui_authen(userid) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_packgroup_package SET package_id = NULL WHERE package_id = -1;
UPDATE cc_packgroup_package SET packagegroup_id = NULL WHERE packagegroup_id = -1;
ALTER TABLE cc_packgroup_package
    ADD CONSTRAINT fk_cc_packgroup_package_cc_package_group FOREIGN KEY (packagegroup_id) REFERENCES cc_package_group(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_packgroup_package_cc_package_offer FOREIGN KEY (package_id) REFERENCES cc_package_offer(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_phonebook SET id_card = NULL WHERE id_card = -1;
ALTER TABLE cc_phonebook
    ADD CONSTRAINT fk_cc_phonebook_cc_card FOREIGN KEY (id_card) REFERENCES cc_card(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_phonenumber SET id_phonebook = NULL WHERE id_phonebook = -1;
ALTER TABLE cc_phonenumber
    ADD CONSTRAINT fk_cc_phonenumber_cc_phonebook FOREIGN KEY (id_phonebook) REFERENCES cc_phonebook(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_ratecard SET id_outbound_cidgroup = NULL WHERE id_outbound_cidgroup = -1;
ALTER TABLE cc_ratecard
    ADD CONSTRAINT fk_cc_ratecard_cc_outbound_cid_group FOREIGN KEY (id_outbound_cidgroup) REFERENCES cc_outbound_cid_group(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_receipt SET id_card = NULL WHERE id_card = -1;
ALTER TABLE cc_receipt
    ADD CONSTRAINT fk_cc_receipt_cc_card FOREIGN KEY (id_card) REFERENCES cc_card(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_remittance_request SET id_agent = NULL WHERE id_agent = -1;
ALTER TABLE cc_remittance_request
    ADD CONSTRAINT fk_cc_remittance_request_cc_agent FOREIGN KEY (id_agent) REFERENCES cc_agent(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_restricted_phonenumber SET id_card = NULL WHERE id_card = -1;
ALTER TABLE cc_restricted_phonenumber
    ADD CONSTRAINT fk_cc_restricted_phonenumber_cc_card FOREIGN KEY (id_card) REFERENCES cc_card(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_server_manager SET id_group = NULL WHERE id_group = -1;
ALTER TABLE cc_server_manager
    ADD CONSTRAINT fk_cc_server_manager_cc_server_group FOREIGN KEY (id_group) REFERENCES cc_server_group(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_service_report SET cc_service_id = NULL WHERE cc_service_id = -1;
ALTER TABLE cc_service_report
    ADD CONSTRAINT fk_cc_service_report_cc_service FOREIGN KEY (cc_service_id) REFERENCES cc_service(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_speeddial SET id_cc_card = NULL WHERE id_cc_card = -1;
ALTER TABLE cc_speeddial
    ADD CONSTRAINT fk_cc_speeddial_cc_card FOREIGN KEY (id_cc_card) REFERENCES cc_card(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_subscription_signup SET id_callplan = NULL WHERE id_callplan = -1;
UPDATE cc_subscription_signup SET id_subscription = NULL WHERE id_subscription = -1;
ALTER TABLE cc_subscription_signup
    ADD CONSTRAINT fk_cc_subscription_signup_cc_callplan FOREIGN KEY (id_callplan) REFERENCES cc_tariffgroup(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_subscription_signup_cc_subscription FOREIGN KEY (id_subscription) REFERENCES cc_subscription_service(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_tariffgroup SET id_cc_package_offer = NULL WHERE id_cc_package_offer = -1;
UPDATE cc_tariffgroup SET idtariffplan = NULL WHERE idtariffplan = -1;
ALTER TABLE cc_tariffgroup
    ADD CONSTRAINT fk_cc_tariffgroup_cc_package_offer FOREIGN KEY (id_cc_package_offer) REFERENCES cc_package_offer(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_tariffgroup_cc_tariffplan FOREIGN KEY (idtariffplan) REFERENCES cc_tariffplan(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_tariffgroup_plan SET idtariffgroup = NULL WHERE idtariffgroup = -1;
UPDATE cc_tariffgroup_plan SET idtariffplan = NULL WHERE idtariffplan = -1;
ALTER TABLE cc_tariffgroup_plan
    ADD CONSTRAINT fk_cc_tariffgroup_plan_cc_tariffgroup FOREIGN KEY (idtariffgroup) REFERENCES cc_tariffgroup(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_tariffgroup_plan_cc_tariffplan FOREIGN KEY (idtariffplan) REFERENCES cc_tariffplan(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_tariffplan SET id_trunk = NULL WHERE id_trunk = -1;
UPDATE cc_tariffplan SET iduser = NULL WHERE iduser = -1;
ALTER TABLE cc_tariffplan
    ADD CONSTRAINT fk_cc_tariffplan_cc_card FOREIGN KEY (iduser) REFERENCES cc_ui_authen(userid) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_tariffplan_cc_trunk FOREIGN KEY (id_trunk) REFERENCES cc_trunk(id_trunk) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_trunk SET id_provider = NULL WHERE id_provider = -1;
ALTER TABLE cc_trunk
    ADD CONSTRAINT fk_cc_trunk_cc_provider FOREIGN KEY (id_provider) REFERENCES cc_provider(id) ON DELETE SET NULL ON UPDATE CASCADE;

SET FOREIGN_KEY_CHECKS = 1;

-- refresh data
INSERT INTO cc_country VALUES
    ('ABW', 297, 'Aruba'), ('AFG', 93, 'Afghanistan'), ('AGO', 244, 'Angola'), ('AIA', 1264, 'Anguilla'), ('ALA', 358, 'Åland Islands'),
    ('ALB', 355, 'Albania'), ('AND', 376, 'Andorra'), ('ARE', 971, 'United Arab Emirates'), ('ARG', 54, 'Argentina'), ('ARM', 374, 'Armenia'),
    ('ASM', 1684, 'American Samoa'), ('ATA', 672, 'Antarctica'), ('ATF', 262, 'French Southern Territories'), ('ATG', 1268, 'Antigua and Barbuda'), ('AUS', 61, 'Australia'),
    ('AUT', 43, 'Austria'), ('AZE', 994, 'Azerbaijan'), ('BDI', 257, 'Burundi'), ('BEL', 32, 'Belgium'), ('BEN', 229, 'Benin'),
    ('BES', 599, 'Bonaire, Sint Eustatius and Saba'), ('BFA', 226, 'Burkina Faso'), ('BGD', 880, 'Bangladesh'), ('BGR', 359, 'Bulgaria'), ('BHR', 973, 'Bahrain'),
    ('BHS', 1242, 'Bahamas'), ('BIH', 387, 'Bosnia and Herzegovina'), ('BLM', 590, 'Saint Barthélemy'), ('BLR', 375, 'Belarus'), ('BLZ', 501, 'Belize'),
    ('BMU', 1441, 'Bermuda'), ('BOL', 591, 'Bolivia'), ('BRA', 55, 'Brazil'), ('BRB', 1246, 'Barbados'), ('BRN', 673, 'Brunei Darussalam'),
    ('BTN', 975, 'Bhutan'), ('BVT', 47, 'Bouvet Island'), ('BWA', 267, 'Botswana'), ('CAF', 236, 'Central African Republic'), ('CAN', 1, 'Canada'),
    ('CCK', 61, 'Cocos Islands'), ('CHE', 41, 'Switzerland'), ('CHL', 56, 'Chile'), ('CHN', 86, 'China'), ('CIV', 225, 'Ivory Coast'),
    ('CMR', 237, 'Cameroon'), ('COD', 243, 'Democratic Republic of the Congo'), ('COG', 242, 'Congo'), ('COK', 682, 'Cook Islands'), ('COL', 57, 'Colombia'),
    ('COM', 269, 'Comoros'), ('CPV', 238, 'Cabo Verde'), ('CRI', 506, 'Costa Rica'), ('CUB', 53, 'Cuba'), ('CUW', 599, 'Curaçao'),
    ('CXR', 61, 'Christmas Island'), ('CYM', 1345, 'Cayman Islands'), ('CYP', 357, 'Cyprus'), ('CZE', 420, 'Czechia'), ('DEU', 49, 'Germany'),
    ('DJI', 253, 'Djibouti'), ('DMA', 1767, 'Dominica'), ('DNK', 45, 'Denmark'), ('DOM', 1809, 'Dominican Republic'), ('DZA', 213, 'Algeria'),
    ('ECU', 593, 'Ecuador'), ('EGY', 20, 'Egypt'), ('ERI', 291, 'Eritrea'), ('ESH', 212, 'Western Sahara'), ('ESP', 34, 'Spain'),
    ('EST', 372, 'Estonia'), ('ETH', 251, 'Ethiopia'), ('FIN', 358, 'Finland'), ('FJI', 679, 'Fiji'), ('FLK', 500, 'Falkland Islands'),
    ('FRA', 33, 'France'), ('FRO', 298, 'Faroe Islands'), ('FSM', 691, 'Federated States of Micronesia'), ('GAB', 241, 'Gabon'), ('GBR', 44, 'United Kingdom'),
    ('GEO', 995, 'Georgia'), ('GGY', 44, 'Guernsey'), ('GHA', 233, 'Ghana'), ('GIB', 350, 'Gibraltar'), ('GIN', 224, 'Guinea'),
    ('GLP', 590, 'Guadeloupe'), ('GMB', 220, 'Gambia'), ('GNB', 245, 'Guinea-Bissau'), ('GNQ', 240, 'Equatorial Guinea'), ('GRC', 30, 'Greece'),
    ('GRD', 1473, 'Grenada'), ('GRL', 299, 'Greenland'), ('GTM', 502, 'Guatemala'), ('GUF', 594, 'French Guiana'), ('GUM', 1671, 'Guam'),
    ('GUY', 592, 'Guyana'), ('HKG', 852, 'Hong Kong'), ('HMD', 672, 'Heard and McDonald Islands'), ('HND', 504, 'Honduras'), ('HRV', 385, 'Croatia'),
    ('HTI', 509, 'Haiti'), ('HUN', 36, 'Hungary'), ('IDN', 62, 'Indonesia'), ('IMN', 44, 'Isle of Man'), ('IND', 91, 'India'),
    ('IOT', 246, 'British Indian Ocean Territory'), ('IRL', 353, 'Ireland'), ('IRN', 98, 'Iran'), ('IRQ', 964, 'Iraq'), ('ISL', 354, 'Iceland'),
    ('ISR', 972, 'Israel'), ('ITA', 39, 'Italy'), ('JAM', 1876, 'Jamaica'), ('JEY', 44, 'Jersey'), ('JOR', 962, 'Jordan'),
    ('JPN', 81, 'Japan'), ('KAZ', 7, 'Kazakhstan'), ('KEN', 254, 'Kenya'), ('KGZ', 996, 'Kyrgyzstan'), ('KHM', 855, 'Cambodia'),
    ('KIR', 686, 'Kiribati'), ('KNA', 1869, 'Saint Kitts and Nevis'), ('KOR', 82, 'Republic of Korea'), ('KWT', 965, 'Kuwait'), ('LAO', 856, 'Laos'),
    ('LBN', 961, 'Lebanon'), ('LBR', 231, 'Liberia'), ('LBY', 218, 'Libya'), ('LCA', 1758, 'Saint Lucia'), ('LIE', 423, 'Liechtenstein'),
    ('LKA', 94, 'Sri Lanka'), ('LSO', 266, 'Lesotho'), ('LTU', 370, 'Lithuania'), ('LUX', 352, 'Luxembourg'), ('LVA', 371, 'Latvia'),
    ('MAC', 853, 'Macao'), ('MAF', 590, 'Saint Martin'), ('MAR', 212, 'Morocco'), ('MCO', 377, 'Monaco'), ('MDA', 373, 'Moldova'),
    ('MDG', 261, 'Madagascar'), ('MDV', 960, 'Maldives'), ('MEX', 52, 'Mexico'), ('MHL', 692, 'Marshall Islands'), ('MKD', 389, 'North Macedonia'),
    ('MLI', 223, 'Mali'), ('MLT', 356, 'Malta'), ('MMR', 95, 'Myanmar'), ('MNE', 382, 'Montenegro'), ('MNG', 976, 'Mongolia'),
    ('MNP', 1670, 'Northern Mariana Islands'), ('MOZ', 258, 'Mozambique'), ('MRT', 222, 'Mauritania'), ('MSR', 1664, 'Montserrat'), ('MTQ', 596, 'Martinique'),
    ('MUS', 230, 'Mauritius'), ('MWI', 265, 'Malawi'), ('MYS', 60, 'Malaysia'), ('MYT', 262, 'Mayotte'), ('NAM', 264, 'Namibia'),
    ('NCL', 687, 'New Caledonia'), ('NER', 227, 'Niger'), ('NFK', 672, 'Norfolk Island'), ('NGA', 234, 'Nigeria'), ('NIC', 505, 'Nicaragua'),
    ('NIU', 683, 'Niue'), ('NLD', 31, 'Netherlands'), ('NOR', 47, 'Norway'), ('NPL', 977, 'Nepal'), ('NRU', 674, 'Nauru'),
    ('NZL', 64, 'New Zealand Aotearoa'), ('OMN', 968, 'Oman'), ('PAK', 92, 'Pakistan'), ('PAN', 507, 'Panama'), ('PCN', 870, 'Pitcairn'),
    ('PER', 51, 'Peru'), ('PHL', 63, 'Philippines'), ('PLW', 680, 'Palau'), ('PNG', 675, 'Papua New Guinea'), ('POL', 48, 'Poland'),
    ('PRI', 1, 'Puerto Rico'), ('PRK', 850, 'Democratic People\'s Republic of Korea'), ('PRT', 351, 'Portugal'), ('PRY', 595, 'Paraguay'), ('PSE', 970, 'Palestine'),
    ('PYF', 689, 'French Polynesia'), ('QAT', 974, 'Qatar'), ('REU', 262, 'Réunion'), ('ROU', 40, 'Romania'), ('RUS', 7, 'Russian Federation'),
    ('RWA', 250, 'Rwanda'), ('SAU', 966, 'Saudi Arabia'), ('SDN', 249, 'Sudan'), ('SEN', 221, 'Senegal'), ('SGP', 65, 'Singapore'),
    ('SGS', 500, 'South Georgia and the South Sandwich Islands'), ('SHN', 290, 'Saint Helena, Ascension and Tristan de Cunha'), ('SJM', 47, 'Svalbard and Jan Mayen'), ('SLB', 677, 'Solomon Islands'), ('SLE', 232, 'Sierra Leone'),
    ('SLV', 503, 'El Salvador'), ('SMR', 378, 'San Marino'), ('SOM', 252, 'Somalia'), ('SPM', 508, 'Saint Pierre and Miquelon'), ('SRB', 381, 'Serbia'),
    ('SSD', 211, 'South Sudan'), ('STP', 239, 'Sao Tome and Principe'), ('SUR', 597, 'Suriname'), ('SVK', 421, 'Slovakia'), ('SVN', 386, 'Slovenia'),
    ('SWE', 46, 'Sweden'), ('SWZ', 268, 'Eswatini'), ('SXM', 1721, 'Sint Maarten'), ('SYC', 248, 'Seychelles'), ('SYR', 963, 'Syria'),
    ('TCA', 1649, 'Turks and Caicos Islands'), ('TCD', 235, 'Chad'), ('TGO', 228, 'Togo'), ('THA', 66, 'Thailand'), ('TJK', 992, 'Tajikistan'),
    ('TKL', 690, 'Tokelau'), ('TKM', 993, 'Turkmenistan'), ('TLS', 670, 'Timor-Leste'), ('TON', 676, 'Tonga'), ('TTO', 1868, 'Trinidad and Tobago'),
    ('TUN', 216, 'Tunisia'), ('TUR', 90, 'Turkey'), ('TUV', 688, 'Tuvalu'), ('TWN', 886, 'Taiwan'), ('TZA', 255, 'Tanzania'),
    ('UGA', 256, 'Uganda'), ('UKR', 380, 'Ukraine'), ('UMI', 1, 'United States Minor Outlying Islands'), ('URY', 598, 'Uruguay'), ('USA', 1, 'United States of America'),
    ('UZB', 998, 'Uzbekistan'), ('VAT', 3906, 'Vatican City'), ('VCT', 1784, 'Saint Vincent and the Grenadines'), ('VEN', 58, 'Venezuela'), ('VGB', 1284, 'British Virgin Islands'),
    ('VIR', 1340, 'United States Virgin Islands'), ('VNM', 84, 'Viet Nam'), ('VUT', 678, 'Vanuatu'), ('WLF', 681, 'Wallis and Futuna Islands'), ('WSM', 685, 'Samoa'),
    ('XKX', 383, 'Kosovo'), ('YEM', 967, 'Yemen'), ('ZAF', 27, 'South Africa'), ('ZMB', 260, 'Zambia'), ('ZWE', 263, 'Zimbabwe')
ON DUPLICATE KEY UPDATE countryprefix = VALUES(countryprefix), countryname = VALUES(countryname);

-- invalid entries, left here for old records
INSERT INTO cc_country VALUES
    ('ANT', 599, 'Netherlands Antilles (obsolete)'),
    ('ASC', 247, 'Ascenscion Island (obsolete)'),
    ('CPT', 0, 'Clipperton Island (obsolete)'),
    ('DGA', 246, 'Diego Garcia (obsolete)'),
    ('TAA', 290, 'Tristan da Cunha (obsolete)'),
    ('TMP', 670, 'East Timor (obsolete)'),
    ('UNK', 383, 'Kosovo (obsolete)'),
    ('XNM', 870, 'Inmarsat (obsolete)')
ON DUPLICATE KEY UPDATE countryprefix = VALUES(countryprefix), countryname = VALUES(countryname);

-- unused
ALTER TABLE cc_iso639 DROP COLUMN IF EXISTS lname, DROP COLUMN IF EXISTS charset, MODIFY COLUMN name varchar(64);
-- refresh data
INSERT INTO cc_iso639 VALUES
    ('aa', 'Afar'), ('ab', 'Abkhazian'), ('ae', 'Avestan'), ('af', 'Afrikaans'), ('ak', 'Akan'),
    ('am', 'Amharic'), ('an', 'Aragonese'), ('ar', 'Arabic'), ('as', 'Assamese'), ('av', 'Avaric'),
    ('ay', 'Aymara'), ('az', 'Azerbaijani'), ('ba', 'Bashkir'), ('be', 'Belarusian'), ('bg', 'Bulgarian'),
    ('bi', 'Bislama'), ('bm', 'Bambara'), ('bn', 'Bengali'), ('bo', 'Tibetan'), ('br', 'Breton'),
    ('bs', 'Bosnian'), ('ca', 'Catalan, Valencian'), ('ce', 'Chechen'), ('ch', 'Chamorro'), ('co', 'Corsican'),
    ('cr', 'Cree'), ('cs', 'Czech'), ('cu', 'Church Slavonic, Old Slavonic, Old Church Slavonic'), ('cv', 'Chuvash'), ('cy', 'Welsh'),
    ('da', 'Danish'), ('de', 'German'), ('dv', 'Divehi, Dhivehi, Maldivian'), ('dz', 'Dzongkha'), ('ee', 'Ewe'),
    ('el', 'Greek, Modern (1453–)'), ('en', 'English'), ('eo', 'Esperanto'), ('es', 'Spanish, Castilian'), ('et', 'Estonian'),
    ('eu', 'Basque'), ('fa', 'Persian'), ('ff', 'Fulah'), ('fi', 'Finnish'), ('fj', 'Fijian'),
    ('fo', 'Faroese'), ('fr', 'French'), ('fy', 'Western Frisian'), ('ga', 'Irish'), ('gd', 'Gaelic, Scottish Gaelic'),
    ('gl', 'Galician'), ('gn', 'Guarani'), ('gu', 'Gujarati'), ('gv', 'Manx'), ('ha', 'Hausa'),
    ('he', 'Hebrew'), ('hi', 'Hindi'), ('ho', 'Hiri Motu'), ('hr', 'Croatian'), ('ht', 'Haitian, Haitian Creole'),
    ('hu', 'Hungarian'), ('hw', 'Hawaiian'), ('hy', 'Armenian'), ('hz', 'Herero'), ('ia', 'Interlingua (IALA)'),
    ('id', 'Indonesian'), ('ie', 'Interlingue, Occidental'), ('ig', 'Igbo'), ('ii', 'Sichuan Yi, Nuosu'), ('ik', 'Inupiaq'),
    ('io', 'Ido'), ('is', 'Icelandic'), ('it', 'Italian'), ('iu', 'Inuktitut'), ('ja', 'Japanese'),
    ('jv', 'Javanese'), ('ka', 'Georgian'), ('kg', 'Kongo'), ('ki', 'Kikuyu, Gikuyu'), ('kj', 'Kuanyama, Kwanyama'),
    ('kk', 'Kazakh'), ('kl', 'Kalaallisut, Greenlandic'), ('km', 'Central Khmer'), ('kn', 'Kannada'), ('ko', 'Korean'),
    ('kr', 'Kanuri'), ('ks', 'Kashmiri'), ('ku', 'Kurdish'), ('kv', 'Komi'), ('kw', 'Cornish'),
    ('ky', 'Kyrgyz, Kirghiz'), ('la', 'Latin'), ('lb', 'Luxembourgish, Letzeburgesch'), ('lg', 'Ganda'), ('li', 'Limburgan, Limburger, Limburgish'),
    ('ln', 'Lingala'), ('lo', 'Lao'), ('lt', 'Lithuanian'), ('lu', 'Luba-Katanga'), ('lv', 'Latvian'),
    ('mg', 'Malagasy'), ('mh', 'Marshallese'), ('mi', 'Maori'), ('mk', 'Macedonian'), ('ml', 'Malayalam'),
    ('mn', 'Mongolian'), ('mr', 'Marathi'), ('ms', 'Malay'), ('mt', 'Maltese'), ('my', 'Burmese'),
    ('na', 'Nauru'), ('nb', 'Norwegian Bokmål'), ('nd', 'North Ndebele'), ('ne', 'Nepali'), ('ng', 'Ndonga'),
    ('nl', 'Dutch, Flemish'), ('nn', 'Norwegian Nynorsk'), ('no', 'Norwegian'), ('nr', 'South Ndebele'), ('nv', 'Navajo, Navaho'),
    ('ny', 'Chichewa, Chewa, Nyanja'), ('oc', 'Occitan'), ('oj', 'Ojibwa'), ('om', 'Oromo'), ('or', 'Oriya'),
    ('os', 'Ossetian, Ossetic'), ('pa', 'Punjabi, Panjabi'), ('pi', 'Pali'), ('pl', 'Polish'), ('ps', 'Pashto, Pushto'),
    ('pt', 'Portuguese'), ('qu', 'Quechua'), ('rm', 'Romansh'), ('rn', 'Rundi'), ('ro', 'Romanian, Moldavian, Moldovan'),
    ('ru', 'Russian'), ('rw', 'Kinyarwanda'), ('sa', 'Sanskrit'), ('sc', 'Sardinian'), ('sd', 'Sindhi'),
    ('se', 'Northern Sami'), ('sg', 'Sango'), ('si', 'Sinhala, Sinhalese'), ('sk', 'Slovak'), ('sl', 'Slovenian'),
    ('sm', 'Samoan'), ('sn', 'Shona'), ('so', 'Somali'), ('sq', 'Albanian'), ('sr', 'Serbian'),
    ('ss', 'Swati'), ('st', 'Southern Sotho'), ('su', 'Sundanese'), ('sv', 'Swedish'), ('sw', 'Swahili'),
    ('ta', 'Tamil'), ('te', 'Telugu'), ('tg', 'Tajik'), ('th', 'Thai'), ('ti', 'Tigrinya'),
    ('tk', 'Turkmen'), ('tl', 'Tagalog'), ('tn', 'Tswana'), ('to', 'Tonga (Tonga Islands)'), ('tr', 'Turkish'),
    ('ts', 'Tsonga'), ('tt', 'Tatar'), ('tw', 'Twi'), ('ty', 'Tahitian'), ('ug', 'Uighur, Uyghur'),
    ('uk', 'Ukrainian'), ('ur', 'Urdu'), ('uz', 'Uzbek'), ('ve', 'Venda'), ('vi', 'Vietnamese'),
    ('vo', 'Volapük'), ('wa', 'Walloon'), ('wo', 'Wolof'), ('xh', 'Xhosa'), ('yi', 'Yiddish'),
    ('yo', 'Yoruba'), ('za', 'Zhuang, Chuang'), ('zh', 'Chinese'), ('zu', 'Zulu')
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- invalid entries, left here for old records
INSERT INTO cc_iso639 VALUES
    ('bh', 'Bihari (obsolete)'),
    ('mo', 'Moldovan (obsolete)'),
    ('sh', 'Serbo-Croatian (obsolete)')
ON DUPLICATE KEY UPDATE name = VALUES(name);
