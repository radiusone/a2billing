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

-- remove old help text
UPDATE cc_config SET config_description = 'Fields to show in Customer. Order is important.<br/>You can use:<br/> id,username, useralias, lastname, id_group, id_agent, credit, tariff, status, language, inuse, currency, sip_buddy, iax_buddy, nbused, firstname, email, discount, callerid, id_seria, serial' WHERE config_key = 'card_show_field_list';

-- match cc_card table columns
ALTER TABLE cc_card_archive ADD COLUMN IF NOT EXISTS `id_seria` bigint(20) DEFAULT NULL;
ALTER TABLE cc_card_archive ADD COLUMN IF NOT EXISTS `serial` bigint(20) DEFAULT NULL;
ALTER TABLE cc_card_archive ADD COLUMN IF NOT EXISTS `block` tinyint(4) NOT NULL DEFAULT 0;
ALTER TABLE cc_card_archive ADD COLUMN IF NOT EXISTS `lock_pin` varchar(15) DEFAULT NULL;
ALTER TABLE cc_card_archive ADD COLUMN IF NOT EXISTS `lock_date` datetime DEFAULT NULL;
ALTER TABLE cc_card_archive ADD COLUMN IF NOT EXISTS `max_concurrent` int(11) NOT NULL DEFAULT 10;

-- rely on a properly configured PHP installation
DELETE FROM cc_config WHERE config_key = 'server_GMT';

-- add proper timezones, make a few corrections
ALTER TABLE cc_timezone DROP COLUMN gmttime;
ALTER TABLE cc_timezone DROP COLUMN gmtoffset;
ALTER TABLE cc_timezone ADD COLUMN zone VARCHAR(64);
UPDATE cc_timezone SET zone = 'Etc/GMT+12' WHERE gmtzone = '(GMT-12:00) International Date Line West';
UPDATE cc_timezone SET zone = 'Pacific/Samoa' WHERE gmtzone = '(GMT-11:00) Midway Island, Samoa';
UPDATE cc_timezone SET zone = 'Pacific/Honolulu' WHERE gmtzone = '(GMT-10:00) Hawaii';
UPDATE cc_timezone SET zone = 'America/Anchorage' WHERE gmtzone = '(GMT-09:00) Alaska';
UPDATE cc_timezone SET zone = 'America/Los_Angeles' WHERE gmtzone = '(GMT-08:00) Pacific Time (US & Canada) Tijuana';
UPDATE cc_timezone SET zone = 'America/Phoenix' WHERE gmtzone = '(GMT-07:00) Arizona';
UPDATE cc_timezone SET zone = 'America/Mazatlan' WHERE gmtzone = '(GMT-07:00) Chihuahua, La Paz, Mazatlan';
UPDATE cc_timezone SET zone = 'America/Denver' WHERE gmtzone = '(GMT-07:00) Mountain Time(US & Canada)';
UPDATE cc_timezone SET zone = 'America/Guatemala' WHERE gmtzone = '(GMT-06:00) Central America';
UPDATE cc_timezone SET zone = 'America/Chicago' WHERE gmtzone = '(GMT-06:00) Central Time (US & Canada)';
UPDATE cc_timezone SET zone = 'America/Mexico_City' WHERE gmtzone = '(GMT-06:00) Guadalajara, Mexico City, Monterrey';
UPDATE cc_timezone SET zone = 'America/Regina' WHERE gmtzone = '(GMT-06:00) Saskatchewan';
UPDATE cc_timezone SET zone = 'America/Bogota' WHERE gmtzone = '(GMT-05:00) Bogota, Lima, Quito';
UPDATE cc_timezone SET zone = 'America/New_York' WHERE gmtzone = '(GMT-05:00) Eastern Time (US & Canada)';
UPDATE cc_timezone SET zone = 'America/Indiana/Indianapolis' WHERE gmtzone = '(GMT-05:00) Indiana (East)';
UPDATE cc_timezone SET zone = 'America/Halifax' WHERE gmtzone = '(GMT-04:00) Atlantic Time (Canada)';
UPDATE cc_timezone SET zone = 'America/Caracas' WHERE gmtzone = '(GMT-04:00) Caracas, La Paz';
UPDATE cc_timezone SET zone = 'America/Santiago' WHERE gmtzone = '(GMT-04:00) Santiago';
UPDATE cc_timezone SET zone = 'America/St_Johns', gmtzone='(GMT-03:30) Newfoundland' WHERE gmtzone = '(GMT-03:30) NewFoundland';
UPDATE cc_timezone SET zone = 'America/Sao_Paulo' WHERE gmtzone = '(GMT-03:00) Brasillia';
UPDATE cc_timezone SET zone = 'America/Buenos_Aires' WHERE gmtzone = '(GMT-03:00) Buenos Aires, Georgetown';
UPDATE cc_timezone SET zone = 'America/Nuuk', gmtzone = '(GMT-02:00) Greenland' WHERE gmtzone = '(GMT-03:00) Greenland';
UPDATE cc_timezone SET zone = 'Atlantic/South_Georgia', gmtzone = '(GMT-02:00) Mid-Atlantic' WHERE gmtzone = '(GMT-03:00) Mid-Atlantic';
UPDATE cc_timezone SET zone = 'Atlantic/Azores' WHERE gmtzone = '(GMT-01:00) Azores';
UPDATE cc_timezone SET zone = 'Atlantic/Cape_Verde', gmtzone = '(GMT-01:00) Cabo Verde' WHERE gmtzone = '(GMT-01:00) Cape Verd Is.';
UPDATE cc_timezone SET zone = 'Africa/Monrovia' WHERE gmtzone = '(GMT) Casablanca, Monrovia';
UPDATE cc_timezone SET zone = 'Europe/London', gmtzone = '(GMT) Dublin, Edinburgh, Lisbon, London' WHERE gmtzone = '(GMT) Greenwich Mean Time : Dublin, Edinburgh, Lisbon,  London';
UPDATE cc_timezone SET zone = 'Europe/Berlin' WHERE gmtzone = '(GMT+01:00) Amsterdam, Berlin, Bern, Rome, Stockholm, Vienna';
UPDATE cc_timezone SET zone = 'Europe/Budapest' WHERE gmtzone = '(GMT+01:00) Belgrade, Bratislava, Budapest, Ljubljana, Prague';
UPDATE cc_timezone SET zone = 'Europe/Paris' WHERE gmtzone = '(GMT+01:00) Brussels, Copenhagen, Madrid, Paris';
UPDATE cc_timezone SET zone = 'Europe/Warsaw' WHERE gmtzone = '(GMT+01:00) Sarajevo, Skopje, Warsaw, Zagreb';
UPDATE cc_timezone SET zone = 'Africa/Lagos' WHERE gmtzone = '(GMT+01:00) West Central Africa';
UPDATE cc_timezone SET zone = 'Europe/Athens' WHERE gmtzone = '(GMT+02:00) Athens, Istanbul, Minsk';
UPDATE cc_timezone SET zone = 'Europe/Bucharest' WHERE gmtzone = '(GMT+02:00) Bucharest';
UPDATE cc_timezone SET zone = 'Africa/Cairo' WHERE gmtzone = '(GMT+02:00) Cairo';
UPDATE cc_timezone SET zone = 'Africa/Johannesburg', gmtzone = '(GMT+02:00) Harare, Pretoria' WHERE gmtzone = '(GMT+02:00) Harere, Pretoria';
UPDATE cc_timezone SET zone = 'Europe/Riga' WHERE gmtzone = '(GMT+02:00) Helsinki, Kyiv, Riga, Sofia, Tallinn, Vilnius';
UPDATE cc_timezone SET zone = 'Asia/Gaza' WHERE gmtzone = '(GMT+02:00) Jeruasalem';
UPDATE cc_timezone SET zone = 'Asia/Baghdad' WHERE gmtzone = '(GMT+03:00) Baghdad';
UPDATE cc_timezone SET zone = 'Asia/Kuwait' WHERE gmtzone = '(GMT+03:00) Kuwait, Riyadh';
UPDATE cc_timezone SET zone = 'Europe/Moscow' WHERE gmtzone = '(GMT+03:00) Moscow, St.Petersburg, Volgograd';
UPDATE cc_timezone SET zone = 'Africa/Nairobi' WHERE gmtzone = '(GMT+03:00) Nairobi';
UPDATE cc_timezone SET zone = 'Asia/Tehran' WHERE gmtzone = '(GMT+03:30) Tehran';
UPDATE cc_timezone SET zone = 'Asia/Dubai' WHERE gmtzone = '(GMT+04:00) Abu Dhabi, Muscat';
UPDATE cc_timezone SET zone = 'Asia/Tbilisi' WHERE gmtzone = '(GMT+04:00) Baku, Tbillisi, Yerevan';
UPDATE cc_timezone SET zone = 'Asia/Kabul' WHERE gmtzone = '(GMT+04:30) Kabul';
UPDATE cc_timezone SET zone = 'Asia/Yekaterinburg' WHERE gmtzone = '(GMT+05:00) Ekaterinburg';
UPDATE cc_timezone SET zone = 'Asia/Karachi' WHERE gmtzone = '(GMT+05:00) Islamabad, Karachi, Tashkent';
UPDATE cc_timezone SET zone = 'Asia/Kolkata' WHERE gmtzone = '(GMT+05:30) Chennai, Kolkata, Mumbai, New Delhi';
UPDATE cc_timezone SET zone = 'Asia/Kathmandu' WHERE gmtzone = '(GMT+05:45) Kathmandu';
UPDATE cc_timezone SET zone = 'Asia/Omsk' WHERE gmtzone = '(GMT+06:00) Almaty, Novosibirsk';
UPDATE cc_timezone SET zone = 'Asia/Dhaka' WHERE gmtzone = '(GMT+06:00) Astana, Dhaka';
UPDATE cc_timezone SET zone = 'Asia/Colombo', gmtzone = '(GMT+05:30) Sri Jayawardenepura' WHERE gmtzone = '(GMT+06:00) Sri Jayawardenepura';
UPDATE cc_timezone SET zone = 'Asia/Yangon', gmtzone = '(GMT+06:30) Yangon' WHERE gmtzone = '(GMT+06:30) Rangoon';
UPDATE cc_timezone SET zone = 'Asia/Bangkok' WHERE gmtzone = '(GMT+07:00) Bangkok, Hanoi, Jakarta';
UPDATE cc_timezone SET zone = 'Asia/Krasnoyarsk' WHERE gmtzone = '(GMT+07:00) Krasnoyarsk';
UPDATE cc_timezone SET zone = 'Asia/Hong_Kong' WHERE gmtzone = '(GMT+08:00) Beijiing, Chongging, Hong Kong, Urumqi';
UPDATE cc_timezone SET zone = 'Asia/Ulaanbaatar' WHERE gmtzone = '(GMT+08:00) Irkutsk, Ulaan Bataar';
UPDATE cc_timezone SET zone = 'Asia/Singapore' WHERE gmtzone = '(GMT+08:00) Kuala Lumpur, Singapore';
UPDATE cc_timezone SET zone = 'Australia/Perth' WHERE gmtzone = '(GMT+08:00) Perth';
UPDATE cc_timezone SET zone = 'Asia/Taipei' WHERE gmtzone = '(GMT+08:00) Taipei';
UPDATE cc_timezone SET zone = 'Asia/Tokyo' WHERE gmtzone = '(GMT+09:00) Osaka, Sapporo, Tokyo';
UPDATE cc_timezone SET zone = 'Asia/Seoul' WHERE gmtzone = '(GMT+09:00) Seoul';
UPDATE cc_timezone SET zone = 'Asia/Yakutsk' WHERE gmtzone = '(GMT+09:00) Yakutsk';
UPDATE cc_timezone SET zone = 'Australia/Adelaide' WHERE gmtzone = '(GMT+09:00) Adelaide';
UPDATE cc_timezone SET zone = 'Australia/Darwin' WHERE gmtzone = '(GMT+09:30) Darwin';
UPDATE cc_timezone SET zone = 'Australia/Brisbane' WHERE gmtzone = '(GMT+10:00) Brisbane';
UPDATE cc_timezone SET zone = 'Australia/Sydney' WHERE gmtzone = '(GMT+10:00) Canberra, Melbourne, Sydney';
UPDATE cc_timezone SET zone = 'Pacific/Port_Moresby' WHERE gmtzone = '(GMT+10:00) Guam, Port Moresby';
UPDATE cc_timezone SET zone = 'Australia/Hobart' WHERE gmtzone = '(GMT+10:00) Hobart';
UPDATE cc_timezone SET zone = 'Asia/Vladivostok' WHERE gmtzone = '(GMT+10:00) Vladivostok';
UPDATE cc_timezone SET zone = 'Pacific/Norfolk' WHERE gmtzone = '(GMT+11:00) Magadan, Solomon Is., New Caledonia';
UPDATE cc_timezone SET zone = 'Pacific/Auckland' WHERE gmtzone = '(GMT+12:00) Auckland, Wellington';
UPDATE cc_timezone SET zone = 'Pacific/Fiji' WHERE gmtzone = '(GMT+12:00) Fiji, Kamchatka, Marshall Is.';
UPDATE cc_timezone SET zone = 'Pacific/Tongatapu', gmtzone = '(GMT+13:00) Nukuʻalofa' WHERE gmtzone = '(GMT+13:00) Nuku alofa';
