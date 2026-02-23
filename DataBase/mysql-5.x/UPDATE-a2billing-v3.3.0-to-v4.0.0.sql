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

-- a database structure that isn't from 2002‽
SET FOREIGN_KEY_CHECKS = 0;

-- primary keys
ALTER TABLE cc_agent_tariffgroup DROP PRIMARY KEY;
ALTER TABLE cc_agent_tariffgroup ADD UNIQUE INDEX (id_agent, id_tariffgroup);
ALTER TABLE cc_agent_tariffgroup MODIFY id_agent BIGINT NULL DEFAULT NULL;
ALTER TABLE cc_agent_tariffgroup MODIFY id_tariffgroup BIGINT NULL DEFAULT NULL;

ALTER TABLE cc_cardgroup_service DROP PRIMARY KEY;
ALTER TABLE cc_cardgroup_service ADD UNIQUE INDEX (id_card_group, id_service);
ALTER TABLE cc_cardgroup_service MODIFY id_card_group BIGINT NULL DEFAULT NULL;
ALTER TABLE cc_cardgroup_service MODIFY id_service BIGINT NULL DEFAULT NULL;

ALTER TABLE cc_notification_admin DROP PRIMARY KEY;
ALTER TABLE cc_notification_admin ADD UNIQUE INDEX (id_admin, id_notification);
ALTER TABLE cc_notification_admin MODIFY id_admin BIGINT NULL DEFAULT NULL;
ALTER TABLE cc_notification_admin MODIFY id_notification BIGINT NULL DEFAULT NULL;

ALTER TABLE cc_packgroup_package DROP PRIMARY KEY;
ALTER TABLE cc_packgroup_package ADD UNIQUE INDEX (packagegroup_id, package_id);
ALTER TABLE cc_packgroup_package MODIFY packagegroup_id BIGINT NULL DEFAULT NULL;
ALTER TABLE cc_packgroup_package MODIFY package_id BIGINT NULL DEFAULT NULL;

ALTER TABLE cc_tariffgroup_plan DROP PRIMARY KEY;
ALTER TABLE cc_tariffgroup_plan ADD UNIQUE INDEX (idtariffgroup, idtariffplan);
ALTER TABLE cc_tariffgroup_plan MODIFY idtariffgroup BIGINT NULL DEFAULT NULL;
ALTER TABLE cc_tariffgroup_plan MODIFY idtariffplan BIGINT NULL DEFAULT NULL;

UPDATE cc_callerid SET id_cc_card = NULL WHERE id_cc_card = -1;
ALTER TABLE cc_callerid ADD CONSTRAINT fk_cc_callerid_cc_card FOREIGN KEY (id_cc_card) REFERENCES cc_card(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_card SET id_group = NULL WHERE id_group = -1;
UPDATE cc_card SET id_seria = NULL WHERE id_seria = -1;
UPDATE cc_card SET tariff = NULL WHERE tariff = -1;
ALTER TABLE cc_card ADD CONSTRAINT fk_cc_card_cc_card_group FOREIGN KEY (id_group) REFERENCES cc_card_group(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_card_cc_card_seria FOREIGN KEY (id_seria) REFERENCES cc_card_seria(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_card_cc_tariffgroup FOREIGN KEY (tariff) REFERENCES cc_tariffgroup(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_card_history SET id_cc_card = NULL WHERE id_cc_card = -1;
ALTER TABLE cc_card_history ADD CONSTRAINT fk_cc_card_history_cc_card FOREIGN KEY (id_cc_card) REFERENCES cc_card(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_iax_buddies SET id_cc_card = NULL WHERE id_cc_card = -1;
ALTER TABLE cc_iax_buddies ADD CONSTRAINT fk_cc_iax_buddies_cc_card FOREIGN KEY (id_cc_card) REFERENCES cc_card(id) ON DELETE SET NULL ON UPDATE CASCADE;

DELETE FROM cc_invoice_item WHERE id_invoice = -1;
ALTER TABLE cc_invoice_item ADD CONSTRAINT fk_cc_invoice_item_cc_invoice FOREIGN KEY (id_invoice) REFERENCES cc_invoice(id) ON DELETE CASCADE ON UPDATE CASCADE;

UPDATE cc_outbound_cid_list SET outbound_cid_group = NULL WHERE outbound_cid_group = -1;
ALTER TABLE cc_outbound_cid_list ADD CONSTRAINT fk_cc_outbound_cid_list_cc_outbound_cid_group FOREIGN KEY (outbound_cid_group) REFERENCES cc_outbound_cid_group(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_ratecard SET id_trunk = NULL WHERE id_trunk = -1;
UPDATE cc_ratecard SET idtariffplan = NULL WHERE idtariffplan = -1;
ALTER TABLE cc_ratecard ADD CONSTRAINT fk_cc_ratecard_cc_tariffplan FOREIGN KEY (idtariffplan) REFERENCES cc_tariffplan(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_ratecard_cc_trunk FOREIGN KEY (id_trunk) REFERENCES cc_trunk(id_trunk) ON DELETE SET NULL ON UPDATE CASCADE;

DELETE FROM cc_receipt_item WHERE id_receipt = -1;
ALTER TABLE cc_receipt_item ADD CONSTRAINT fk_cc_receipt_item_cc_receipt FOREIGN KEY (id_receipt) REFERENCES cc_receipt(id) ON DELETE CASCADE ON UPDATE CASCADE;

UPDATE cc_sip_buddies SET id_cc_card = NULL WHERE id_cc_card = -1;
ALTER TABLE cc_sip_buddies ADD CONSTRAINT fk_cc_sip_buddies_cc_card FOREIGN KEY (id_cc_card) REFERENCES cc_card(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_status_log SET id_cc_card = NULL WHERE id_cc_card = -1;
ALTER TABLE cc_status_log ADD CONSTRAINT fk_cc_status_log_cc_card FOREIGN KEY (id_cc_card) REFERENCES cc_card(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_support_component SET id_support = NULL WHERE id_support = -1;
ALTER TABLE cc_support_component ADD CONSTRAINT fk_cc_support_component_cc_support FOREIGN KEY (id_support) REFERENCES cc_support(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_ticket SET id_component = NULL WHERE id_component = -1;
ALTER TABLE cc_ticket ADD CONSTRAINT fk_cc_ticket_cc_support_component FOREIGN KEY (id_component) REFERENCES cc_support_component(id) ON DELETE SET NULL ON UPDATE CASCADE;

DELETE FROM cc_ticket_comment WHERE id_ticket = -1;
ALTER TABLE cc_ticket_comment ADD CONSTRAINT fk_cc_ticket_comment_cc_ticket FOREIGN KEY (id_ticket) REFERENCES cc_ticket(id) ON DELETE CASCADE ON UPDATE CASCADE;



UPDATE cc_agent SET id_tariffgroup = NULL WHERE id_tariffgroup = -1;
ALTER TABLE cc_agent ADD CONSTRAINT fk_cc_agent_cc_tariffgroup FOREIGN KEY (id_tariffgroup) REFERENCES cc_tariffgroup(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_agent_commission SET id_agent = NULL WHERE id_agent = -1;
UPDATE cc_agent_commission SET id_card = NULL WHERE id_card = -1;
ALTER TABLE cc_agent_commission ADD CONSTRAINT fk_cc_agent_commission_cc_agent FOREIGN KEY (id_agent) REFERENCES cc_agent(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_agent_commission_cc_card FOREIGN KEY (id_card) REFERENCES cc_card(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_agent_signup SET id_agent = NULL WHERE id_agent = -1;
UPDATE cc_agent_signup SET id_group = NULL WHERE id_group = -1;
UPDATE cc_agent_signup SET id_tariffgroup = NULL WHERE id_tariffgroup = -1;
ALTER TABLE cc_agent_signup ADD CONSTRAINT fk_cc_agent_signup_cc_agent FOREIGN KEY (id_agent) REFERENCES cc_agent(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_agent_signup_cc_card_group FOREIGN KEY (id_group) REFERENCES cc_card_group(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_agent_signup_cc_tariffgroup FOREIGN KEY (id_tariffgroup) REFERENCES cc_tariffgroup(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_agent_tariffgroup SET id_agent = NULL WHERE id_agent = -1;
UPDATE cc_agent_tariffgroup SET id_tariffgroup = NULL WHERE id_tariffgroup = -1;
ALTER TABLE cc_agent_tariffgroup ADD CONSTRAINT fk_cc_agent_tariffgroup_cc_agent FOREIGN KEY (id_agent) REFERENCES cc_agent(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_agent_tariffgroup_cc_tariffgroup FOREIGN KEY (id_tariffgroup) REFERENCES cc_tariffgroup(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_alarm SET id_trunk = NULL WHERE id_trunk = -1;
ALTER TABLE cc_alarm ADD CONSTRAINT fk_cc_alarm_cc_trunk FOREIGN KEY (id_trunk) REFERENCES cc_trunk(id_trunk) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_alarm_report SET cc_alarm_id = NULL WHERE cc_alarm_id = -1;
ALTER TABLE cc_alarm_report ADD CONSTRAINT fk_cc_alarm_report_cc_alarm FOREIGN KEY (cc_alarm_id) REFERENCES cc_alarm(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_billing_customer SET id_card = NULL WHERE id_card = -1;
UPDATE cc_billing_customer SET id_invoice = NULL WHERE id_invoice = -1;
ALTER TABLE cc_billing_customer ADD CONSTRAINT fk_cc_billing_customer_cc_card FOREIGN KEY (id_card) REFERENCES cc_card(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_billing_customer_cc_invoice FOREIGN KEY (id_invoice) REFERENCES cc_invoice(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_call SET card_id = NULL WHERE card_id = -1;
UPDATE cc_call SET id_card_package_offer = NULL WHERE id_card_package_offer = -1;
UPDATE cc_call SET id_did = NULL WHERE id_did = -1;
UPDATE cc_call SET id_ratecard = NULL WHERE id_ratecard = -1;
UPDATE cc_call SET id_tariffgroup = NULL WHERE id_tariffgroup = -1;
UPDATE cc_call SET id_tariffplan = NULL WHERE id_tariffplan = -1;
UPDATE cc_call SET id_trunk = NULL WHERE id_trunk = -1;
ALTER TABLE cc_call ADD CONSTRAINT fk_cc_call_cc_card FOREIGN KEY (card_id) REFERENCES cc_card(id) ON DELETE SET NULL ON UPDATE CASCADE,
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
ALTER TABLE cc_call_archive ADD CONSTRAINT fk_cc_call_archive_cc_card FOREIGN KEY (card_id) REFERENCES cc_card(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_call_archive_cc_card_package_offer FOREIGN KEY (id_card_package_offer) REFERENCES cc_card_package_offer(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_call_archive_cc_did FOREIGN KEY (id_did) REFERENCES cc_did(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_call_archive_cc_ratecard FOREIGN KEY (id_ratecard) REFERENCES cc_ratecard(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_call_archive_cc_tariffgroup FOREIGN KEY (id_tariffgroup) REFERENCES cc_tariffgroup(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_call_archive_cc_tariffplan FOREIGN KEY (id_tariffplan) REFERENCES cc_tariffplan(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_call_archive_cc_trunk FOREIGN KEY (id_trunk) REFERENCES cc_trunk(id_trunk) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_callback_spool SET id_server = NULL WHERE id_server = -1;
UPDATE cc_callback_spool SET id_server_group = NULL WHERE id_server_group = -1;
ALTER TABLE cc_callback_spool ADD CONSTRAINT fk_cc_callback_spool_cc_server FOREIGN KEY (id_server) REFERENCES cc_server_manager(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_callback_spool_cc_server_group FOREIGN KEY (id_server_group) REFERENCES cc_server_group(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_card SET id_timezone = NULL WHERE id_timezone = -1;
ALTER TABLE cc_card ADD CONSTRAINT fk_cc_card_cc_timezone FOREIGN KEY (id_timezone) REFERENCES cc_timezone(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_card_group SET id_agent = NULL WHERE id_agent = -1;
ALTER TABLE cc_card_group ADD CONSTRAINT fk_cc_card_group_cc_agent FOREIGN KEY (id_agent) REFERENCES cc_agent(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_card_package_offer SET id_cc_card = NULL WHERE id_cc_card = -1;
UPDATE cc_card_package_offer SET id_cc_package_offer = NULL WHERE id_cc_package_offer = -1;
ALTER TABLE cc_card_package_offer ADD CONSTRAINT fk_cc_card_package_offer_cc_card FOREIGN KEY (id_cc_card) REFERENCES cc_card(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_card_package_offer_cc_package_offer FOREIGN KEY (id_cc_package_offer) REFERENCES cc_package_offer(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_card_subscription SET id_cc_card = NULL WHERE id_cc_card = -1;
UPDATE cc_card_subscription SET id_subscription_fee = NULL WHERE id_subscription_fee = -1;
ALTER TABLE cc_card_subscription ADD CONSTRAINT fk_cc_card_subscription_cc_card FOREIGN KEY (id_cc_card) REFERENCES cc_card(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_card_subscription_cc_subscription_service FOREIGN KEY (id_subscription_fee) REFERENCES cc_subscription_service(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_cardgroup_service SET id_card_group = NULL WHERE id_card_group = -1;
UPDATE cc_cardgroup_service SET id_service = NULL WHERE id_service = -1;
ALTER TABLE cc_cardgroup_service ADD CONSTRAINT fk_cc_cardgroup_service_cc_card_group FOREIGN KEY (id_card_group) REFERENCES cc_card_group(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_cardgroup_service_cc_service FOREIGN KEY (id_service) REFERENCES cc_service(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_charge SET id_cc_card = NULL WHERE id_cc_card = -1;
UPDATE cc_charge SET id_cc_card_subscription = NULL WHERE id_cc_card_subscription = -1;
UPDATE cc_charge SET id_cc_did = NULL WHERE id_cc_did = -1;
UPDATE cc_charge SET iduser = NULL WHERE iduser = -1;
ALTER TABLE cc_charge ADD CONSTRAINT fk_cc_charge_cc_card FOREIGN KEY (id_cc_card) REFERENCES cc_card(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_charge_cc_card FOREIGN KEY (iduser) REFERENCES cc_ui_authen(userid) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_charge_cc_card_subscription FOREIGN KEY (id_cc_card_subscription) REFERENCES cc_card_subscription(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_charge_cc_did FOREIGN KEY (id_cc_did) REFERENCES cc_did(id) ON DELETE SET NULL ON UPDATE CASCADE;

DELETE FROM cc_config WHERE config_group_id = -1;
ALTER TABLE cc_config ADD CONSTRAINT fk_cc_config_cc_config_group FOREIGN KEY (config_group_id) REFERENCES cc_config_group(id) ON DELETE CASCADE ON UPDATE CASCADE;

UPDATE cc_did SET id_cc_country = NULL WHERE id_cc_country = -1;
UPDATE cc_did SET id_cc_didgroup = NULL WHERE id_cc_didgroup = -1;
ALTER TABLE cc_did ADD CONSTRAINT fk_cc_did_cc_country FOREIGN KEY (id_cc_country) REFERENCES cc_country(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_did_cc_didgroup FOREIGN KEY (id_cc_didgroup) REFERENCES cc_didgroup(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_did_destination SET id_cc_card = NULL WHERE id_cc_card = -1;
UPDATE cc_did_destination SET id_cc_did = NULL WHERE id_cc_did = -1;
ALTER TABLE cc_did_destination ADD CONSTRAINT fk_cc_did_destination_cc_card FOREIGN KEY (id_cc_card) REFERENCES cc_card(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_did_destination_cc_did FOREIGN KEY (id_cc_did) REFERENCES cc_did(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_did_use SET id_cc_card = NULL WHERE id_cc_card = -1;
UPDATE cc_did_use SET id_did = NULL WHERE id_did = -1;
ALTER TABLE cc_did_use ADD CONSTRAINT fk_cc_did_use_cc_card FOREIGN KEY (id_cc_card) REFERENCES cc_card(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_did_use_cc_did FOREIGN KEY (id_did) REFERENCES cc_did(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_invoice SET id_card = NULL WHERE id_card = -1;
ALTER TABLE cc_invoice ADD CONSTRAINT fk_cc_invoice_cc_card FOREIGN KEY (id_card) REFERENCES cc_card(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_invoice_item SET id_ext = NULL WHERE id_ext = -1;
ALTER TABLE cc_invoice_item ADD CONSTRAINT fk_cc_invoice_item_cc_billing_customer FOREIGN KEY (id_ext) REFERENCES cc_billing_customer(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_logpayment SET agent_id = NULL WHERE agent_id = -1;
UPDATE cc_logpayment SET card_id = NULL WHERE card_id = -1;
UPDATE cc_logpayment SET id_logrefill = NULL WHERE id_logrefill = -1;
ALTER TABLE cc_logpayment ADD CONSTRAINT fk_cc_logpayment_cc_agent FOREIGN KEY (agent_id) REFERENCES cc_agent(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_logpayment_cc_card FOREIGN KEY (card_id) REFERENCES cc_card(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_logpayment_cc_logrefill FOREIGN KEY (id_logrefill) REFERENCES cc_logrefill(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_logpayment_agent SET agent_id = NULL WHERE agent_id = -1;
UPDATE cc_logpayment_agent SET id_logrefill = NULL WHERE id_logrefill = -1;
ALTER TABLE cc_logpayment_agent ADD CONSTRAINT fk_cc_logpayment_agent_cc_agent FOREIGN KEY (agent_id) REFERENCES cc_agent(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_logpayment_agent_cc_logrefill FOREIGN KEY (id_logrefill) REFERENCES cc_logrefill(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_logrefill SET agent_id = NULL WHERE agent_id = -1;
UPDATE cc_logrefill SET card_id = NULL WHERE card_id = -1;
ALTER TABLE cc_logrefill ADD CONSTRAINT fk_cc_logrefill_cc_agent FOREIGN KEY (agent_id) REFERENCES cc_agent(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_logrefill_cc_card FOREIGN KEY (card_id) REFERENCES cc_card(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_logrefill_agent SET agent_id = NULL WHERE agent_id = -1;
ALTER TABLE cc_logrefill_agent ADD CONSTRAINT fk_cc_logrefill_agent_cc_agent FOREIGN KEY (agent_id) REFERENCES cc_agent(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_message_agent SET id_agent = NULL WHERE id_agent = -1;
ALTER TABLE cc_message_agent ADD CONSTRAINT fk_cc_message_agent_cc_agent FOREIGN KEY (id_agent) REFERENCES cc_agent(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_notification_admin SET id_admin = NULL WHERE id_admin = -1;
UPDATE cc_notification_admin SET id_notification = NULL WHERE id_notification = -1;
ALTER TABLE cc_notification_admin ADD CONSTRAINT fk_cc_notification_admin_cc_notification FOREIGN KEY (id_notification) REFERENCES cc_notification(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_notification_admin_cc_ui_authen FOREIGN KEY (id_admin) REFERENCES cc_ui_authen(userid) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_packgroup_package SET package_id = NULL WHERE package_id = -1;
UPDATE cc_packgroup_package SET packagegroup_id = NULL WHERE packagegroup_id = -1;
ALTER TABLE cc_packgroup_package ADD CONSTRAINT fk_cc_packgroup_package_cc_package_group FOREIGN KEY (packagegroup_id) REFERENCES cc_package_group(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_packgroup_package_cc_package_offer FOREIGN KEY (package_id) REFERENCES cc_package_offer(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_phonebook SET id_card = NULL WHERE id_card = -1;
ALTER TABLE cc_phonebook ADD CONSTRAINT fk_cc_phonebook_cc_card FOREIGN KEY (id_card) REFERENCES cc_card(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_phonenumber SET id_phonebook = NULL WHERE id_phonebook = -1;
ALTER TABLE cc_phonenumber ADD CONSTRAINT fk_cc_phonenumber_cc_phonebook FOREIGN KEY (id_phonebook) REFERENCES cc_phonebook(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_ratecard SET id_outbound_cidgroup = NULL WHERE id_outbound_cidgroup = -1;
ALTER TABLE cc_ratecard ADD CONSTRAINT fk_cc_ratecard_cc_outbound_cid_group FOREIGN KEY (id_outbound_cidgroup) REFERENCES cc_outbound_cid_group(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_receipt SET id_card = NULL WHERE id_card = -1;
ALTER TABLE cc_receipt ADD CONSTRAINT fk_cc_receipt_cc_card FOREIGN KEY (id_card) REFERENCES cc_card(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_remittance_request SET id_agent = NULL WHERE id_agent = -1;
ALTER TABLE cc_remittance_request ADD CONSTRAINT fk_cc_remittance_request_cc_agent FOREIGN KEY (id_agent) REFERENCES cc_agent(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_restricted_phonenumber SET id_card = NULL WHERE id_card = -1;
ALTER TABLE cc_restricted_phonenumber ADD CONSTRAINT fk_cc_restricted_phonenumber_cc_card FOREIGN KEY (id_card) REFERENCES cc_card(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_server_manager SET id_group = NULL WHERE id_group = -1;
ALTER TABLE cc_server_manager ADD CONSTRAINT fk_cc_server_manager_cc_server_group FOREIGN KEY (id_group) REFERENCES cc_server_group(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_service_report SET cc_service_id = NULL WHERE cc_service_id = -1;
ALTER TABLE cc_service_report ADD CONSTRAINT fk_cc_service_report_cc_service FOREIGN KEY (cc_service_id) REFERENCES cc_service(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_speeddial SET id_cc_card = NULL WHERE id_cc_card = -1;
ALTER TABLE cc_speeddial ADD CONSTRAINT fk_cc_speeddial_cc_card FOREIGN KEY (id_cc_card) REFERENCES cc_card(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_subscription_signup SET id_callplan = NULL WHERE id_callplan = -1;
UPDATE cc_subscription_signup SET id_subscription = NULL WHERE id_subscription = -1;
ALTER TABLE cc_subscription_signup ADD CONSTRAINT fk_cc_subscription_signup_cc_callplan FOREIGN KEY (id_callplan) REFERENCES cc_tariffgroup(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_subscription_signup_cc_subscription FOREIGN KEY (id_subscription) REFERENCES cc_subscription_service(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_tariffgroup SET id_cc_package_offer = NULL WHERE id_cc_package_offer = -1;
UPDATE cc_tariffgroup SET idtariffplan = NULL WHERE idtariffplan = -1;
ALTER TABLE cc_tariffgroup ADD CONSTRAINT fk_cc_tariffgroup_cc_package_offer FOREIGN KEY (id_cc_package_offer) REFERENCES cc_package_offer(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_tariffgroup_cc_tariffplan FOREIGN KEY (idtariffplan) REFERENCES cc_tariffplan(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_tariffgroup_plan SET idtariffgroup = NULL WHERE idtariffgroup = -1;
UPDATE cc_tariffgroup_plan SET idtariffplan = NULL WHERE idtariffplan = -1;
ALTER TABLE cc_tariffgroup_plan ADD CONSTRAINT fk_cc_tariffgroup_plan_cc_tariffgroup FOREIGN KEY (idtariffgroup) REFERENCES cc_tariffgroup(id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_tariffgroup_plan_cc_tariffplan FOREIGN KEY (idtariffplan) REFERENCES cc_tariffplan(id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_tariffplan SET id_trunk = NULL WHERE id_trunk = -1;
UPDATE cc_tariffplan SET iduser = NULL WHERE iduser = -1;
ALTER TABLE cc_tariffplan ADD CONSTRAINT fk_cc_tariffplan_cc_card FOREIGN KEY (iduser) REFERENCES cc_ui_authen(userid) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_cc_tariffplan_cc_trunk FOREIGN KEY (id_trunk) REFERENCES cc_trunk(id_trunk) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE cc_trunk SET id_provider = NULL WHERE id_provider = -1;
ALTER TABLE cc_trunk ADD CONSTRAINT fk_cc_trunk_cc_provider FOREIGN KEY (id_provider) REFERENCES cc_provider(id) ON DELETE SET NULL ON UPDATE CASCADE;

SET FOREIGN_KEY_CHECKS = 1;
