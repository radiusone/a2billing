UPDATE cc_version SET version = '3.3.2' LIMIT 1;

-- western Canada no longer uses same time zones as US
UPDATE cc_timezone SET gmtzone = '(GMT-08:00) Pacific Time (US), Tijuana' WHERE zone = 'America/Los_Angeles';
INSERT IGNORE INTO cc_timezone(gmtzone, zone) VALUES ('(GMT-07:00) Pacific Time (Canada)', 'America/Vancouver');
INSERT IGNORE INTO cc_timezone(gmtzone, zone) VALUES ('(GMT-07:00) Yukon Standard Time', 'America/Whitehorse');
UPDATE cc_timezone SET gmtzone = '(GMT-07:00) Mountain Time (US)' WHERE zone = 'America/Denver';
INSERT IGNORE INTO cc_timezone(gmtzone, zone) VALUES ('(GMT-06:00) Alberta Time', 'America/Edmonton');

-- separate these for changes that are likely in the near future
UPDATE cc_timezone SET gmtzone = '(GMT-06:00) Central Time (US)' WHERE gmtzone = 'America/Chicago';
INSERT IGNORE INTO cc_timezone(gmtzone, zone) VALUES ('(GMT-06:00) Central Time (Canada)', 'America/Winnipeg');
UPDATE cc_timezone SET gmtzone = '(GMT-05:00) Eastern Time (US)' WHERE gmtzone = 'America/New_York';
INSERT IGNORE INTO cc_timezone(gmtzone, zone) VALUES ('(GMT-05:00) Eastern Time (Canada)', 'America/Toronto');

-- missing time zone
INSERT IGNORE INTO cc_timezone(gmtzone, zone) VALUES ('(GMT) Iceland', 'Atlantic/Reykjavik');
