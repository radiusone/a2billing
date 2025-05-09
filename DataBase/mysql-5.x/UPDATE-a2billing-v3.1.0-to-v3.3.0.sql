-- remove old ecommerce stuff
UPDATE cc_version SET version = '3.3.0' LIMIT 1;

DROP TABLE cc_configuration;
DROP TABLE cc_epayment_log;
DROP TABLE cc_epayment_log_agent;
DROP TABLE cc_payment_methods;
DROP TABLE cc_payments;
DROP TABLE cc_payments_agent;
DROP TABLE cc_payments_status;
DROP TABLE cc_paypal;

DELETE FROM cc_config WHERE config_group_id = (SELECT id FROM cc_config_group WHERE group_title = 'epayment_method');
DELETE FROM cc_config_group WHERE group_title = 'epayment_method';
DELETE FROM cc_config WHERE config_key IN ('paypal', 'epayment', 'api_ecommerce') AND config_group_id = (SELECT id FROM cc_config_group WHERE group_title = 'log-files');
