UPDATE cc_version SET version = '4.0.0' LIMIT 1;

DELETE FROM cc_config WHERE config_key = 'cache_enabled' OR config_key = 'cache_path';
