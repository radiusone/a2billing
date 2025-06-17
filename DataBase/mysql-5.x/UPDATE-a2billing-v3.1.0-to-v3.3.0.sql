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
