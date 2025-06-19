-- remove old ecommerce stuff
UPDATE cc_version SET version = '3.3.0' LIMIT 1;

DROP TABLE IF EXISTS cc_configuration;
DROP TABLE IF EXISTS cc_epayment_log;
DROP TABLE IF EXISTS cc_epayment_log_agent;
DROP TABLE IF EXISTS cc_payment_methods;
DROP TABLE IF EXISTS cc_payments;
DROP TABLE IF EXISTS cc_payments_agent;
DROP TABLE IF EXISTS cc_payments_status;
DROP TABLE IF EXISTS cc_paypal;

DELETE FROM cc_config WHERE config_group_id = (SELECT id FROM cc_config_group WHERE group_title = 'epayment_method');
DELETE FROM cc_config_group WHERE group_title = 'epayment_method';
DELETE FROM cc_config WHERE config_key LIKE 'paypal%' OR config_key LIKE 'api_%' OR config_key = 'epayment';

-- match cc_card table columns
ALTER TABLE cc_card_archive ADD COLUMN IF NOT EXISTS `id_seria` bigint(20) DEFAULT NULL;
ALTER TABLE cc_card_archive ADD COLUMN IF NOT EXISTS `serial` bigint(20) DEFAULT NULL;
ALTER TABLE cc_card_archive ADD COLUMN IF NOT EXISTS `block` tinyint(4) NOT NULL DEFAULT 0;
ALTER TABLE cc_card_archive ADD COLUMN IF NOT EXISTS `lock_pin` varchar(15) DEFAULT NULL;
ALTER TABLE cc_card_archive ADD COLUMN IF NOT EXISTS `lock_date` datetime DEFAULT NULL;
ALTER TABLE cc_card_archive ADD COLUMN IF NOT EXISTS `max_concurrent` int(11) NOT NULL DEFAULT 10;
