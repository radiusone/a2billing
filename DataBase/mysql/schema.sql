SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT, CHARACTER_SET_CLIENT=utf8mb4;
SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS, CHARACTER_SET_RESULTS=utf8mb4;
SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION, COLLATION_CONNECTION=utf8mb4_unicode_ci;
SET @OLD_TIME_ZONE=@@TIME_ZONE, TIME_ZONE='+00:00' ;
SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 ;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 ;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' ;
SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 ;

--
-- Table structure for table `cc_agent`
--

DROP TABLE IF EXISTS `cc_agent`;
CREATE TABLE `cc_agent` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `datecreation` datetime NOT NULL DEFAULT current_timestamp(),
    `active` varchar(1) NOT NULL DEFAULT 'f',
    `login` varchar(20) NOT NULL,
    `passwd` varchar(40) DEFAULT NULL,
    `location` text DEFAULT NULL,
    `language` varchar(5) DEFAULT 'en',
    `id_tariffgroup` bigint DEFAULT NULL,
    `options` int NOT NULL DEFAULT 0,
    `credit` decimal(15,5) NOT NULL DEFAULT 0.00000,
    `currency` varchar(3) DEFAULT 'USD',
    `locale` varchar(10) DEFAULT 'C',
    `commission` decimal(10,4) NOT NULL DEFAULT 0.0000,
    `vat` decimal(10,4) NOT NULL DEFAULT 0.0000,
    `banner` text DEFAULT NULL,
    `perms` int DEFAULT NULL,
    `lastname` varchar(50) DEFAULT NULL,
    `firstname` varchar(50) DEFAULT NULL,
    `address` varchar(100) DEFAULT NULL,
    `city` varchar(40) DEFAULT NULL,
    `state` varchar(40) DEFAULT NULL,
    `country` varchar(40) DEFAULT NULL,
    `zipcode` varchar(20) DEFAULT NULL,
    `phone` varchar(20) DEFAULT NULL,
    `email` varchar(70) DEFAULT NULL,
    `fax` varchar(20) DEFAULT NULL,
    `company` varchar(50) DEFAULT NULL,
    `com_balance` decimal(15,5) NOT NULL,
    `threshold_remittance` decimal(15,5) NOT NULL,
    `bank_info` mediumtext DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY (`login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_agent_commission`
--

DROP TABLE IF EXISTS `cc_agent_commission`;
CREATE TABLE `cc_agent_commission` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `id_payment` bigint DEFAULT NULL,
    `id_card` bigint DEFAULT NULL,
    `date` datetime NOT NULL DEFAULT current_timestamp(),
    `amount` decimal(15,5) NOT NULL,
    `description` mediumtext DEFAULT NULL,
    `id_agent` bigint DEFAULT NULL,
    `commission_type` tinyint NOT NULL,
    `commission_percent` decimal(10,4) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_agent_signup`
--

DROP TABLE IF EXISTS `cc_agent_signup`;
CREATE TABLE `cc_agent_signup` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `id_agent` bigint DEFAULT NULL,
    `code` varchar(30) NOT NULL,
    `id_tariffgroup` bigint DEFAULT NULL,
    `id_group` bigint DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_agent_tariffgroup`
--

DROP TABLE IF EXISTS `cc_agent_tariffgroup`;
CREATE TABLE `cc_agent_tariffgroup` (
    `id_agent` bigint NOT NULL,
    `id_tariffgroup` bigint NOT NULL,
    PRIMARY KEY (`id_agent`,`id_tariffgroup`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_alarm`
--

DROP TABLE IF EXISTS `cc_alarm`;
CREATE TABLE `cc_alarm` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `name` text NOT NULL,
    `periode` int NOT NULL DEFAULT 1,
    `type` int NOT NULL DEFAULT 1,
    `maxvalue` float NOT NULL,
    `minvalue` float NOT NULL DEFAULT -1,
    `id_trunk` bigint DEFAULT NULL,
    `status` int NOT NULL DEFAULT 0,
    `numberofrun` int NOT NULL DEFAULT 0,
    `numberofalarm` int NOT NULL DEFAULT 0,
    `datecreate` datetime NOT NULL DEFAULT current_timestamp(),
    `datelastrun` datetime DEFAULT NULL,
    `emailreport` varchar(50) DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_alarm_report`
--

DROP TABLE IF EXISTS `cc_alarm_report`;
CREATE TABLE `cc_alarm_report` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `cc_alarm_id` bigint DEFAULT NULL,
    `calculatedvalue` float NOT NULL,
    `daterun` datetime NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_autorefill_report`
--

DROP TABLE IF EXISTS `cc_autorefill_report`;
CREATE TABLE `cc_autorefill_report` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `daterun` datetime NOT NULL DEFAULT current_timestamp(),
    `totalcardperform` int DEFAULT NULL,
    `totalcredit` decimal(15,5) DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_backup`
--

DROP TABLE IF EXISTS `cc_backup`;
CREATE TABLE `cc_backup` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `path` varchar(255) NOT NULL,
    `creationdate` datetime NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_billing_customer`
--

DROP TABLE IF EXISTS `cc_billing_customer`;
CREATE TABLE `cc_billing_customer` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `id_card` bigint DEFAULT NULL,
    `date` datetime NOT NULL DEFAULT current_timestamp(),
    `id_invoice` bigint DEFAULT NULL,
    `start_date` datetime DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_call`
--

DROP TABLE IF EXISTS `cc_call`;
CREATE TABLE `cc_call` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `sessionid` varchar(40) NOT NULL,
    `uniqueid` varchar(30) NOT NULL,
    `card_id` bigint DEFAULT NULL,
    `nasipaddress` varchar(30) NOT NULL DEFAULT '',
    `starttime` datetime NOT NULL DEFAULT current_timestamp(),
    `stoptime` datetime DEFAULT NULL,
    `sessiontime` int DEFAULT NULL,
    `calledstation` varchar(100) NOT NULL DEFAULT '',
    `sessionbill` float DEFAULT NULL,
    `id_tariffgroup` bigint DEFAULT NULL,
    `id_tariffplan` bigint DEFAULT NULL,
    `id_ratecard` bigint DEFAULT NULL,
    `id_trunk` bigint DEFAULT NULL,
    `sipiax` int DEFAULT 0,
    `src` varchar(40) NOT NULL DEFAULT '',
    `id_did` bigint DEFAULT NULL,
    `buycost` decimal(15,5) DEFAULT 0.00000,
    `id_card_package_offer` bigint DEFAULT NULL,
    `real_sessiontime` int DEFAULT NULL,
    `dnid` varchar(40) NOT NULL DEFAULT '',
    `terminatecauseid` int DEFAULT 1,
    `destination` int DEFAULT 0,
    `a2b_custom1` varchar(20) DEFAULT NULL,
    `a2b_custom2` varchar(20) DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY (`starttime`),
    KEY (`calledstation`),
    KEY (`terminatecauseid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_call_archive`
--

DROP TABLE IF EXISTS `cc_call_archive`;
CREATE TABLE `cc_call_archive` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `sessionid` varchar(40) NOT NULL,
    `uniqueid` varchar(30) NOT NULL,
    `card_id` bigint DEFAULT NULL,
    `nasipaddress` varchar(30) NOT NULL,
    `starttime` datetime NOT NULL DEFAULT current_timestamp(),
    `stoptime` datetime DEFAULT NULL,
    `sessiontime` int DEFAULT NULL,
    `calledstation` varchar(100) NOT NULL,
    `sessionbill` float DEFAULT NULL,
    `id_tariffgroup` bigint DEFAULT NULL,
    `id_tariffplan` bigint DEFAULT NULL,
    `id_ratecard` bigint DEFAULT NULL,
    `id_trunk` bigint DEFAULT NULL,
    `sipiax` int DEFAULT 0,
    `src` varchar(40) NOT NULL,
    `id_did` bigint DEFAULT NULL,
    `buycost` decimal(15,5) DEFAULT 0.00000,
    `id_card_package_offer` bigint DEFAULT NULL,
    `real_sessiontime` int DEFAULT NULL,
    `dnid` varchar(40) NOT NULL,
    `terminatecauseid` int DEFAULT 1,
    `destination` int DEFAULT 0,
    `a2b_custom1` varchar(20) DEFAULT NULL,
    `a2b_custom2` varchar(20) DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY (`starttime`),
    KEY (`calledstation`),
    KEY (`terminatecauseid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_callback_spool`
--

DROP TABLE IF EXISTS `cc_callback_spool`;
CREATE TABLE `cc_callback_spool` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `uniqueid` varchar(40) DEFAULT NULL,
    `entry_time` datetime NOT NULL DEFAULT current_timestamp(),
    `status` varchar(80) DEFAULT NULL,
    `server_ip` varchar(40) DEFAULT NULL,
    `num_attempt` int NOT NULL DEFAULT 0,
    `last_attempt_time` datetime DEFAULT NULL,
    `manager_result` varchar(60) DEFAULT NULL,
    `agi_result` varchar(60) DEFAULT NULL,
    `callback_time` datetime DEFAULT NULL,
    `channel` varchar(60) DEFAULT NULL,
    `exten` varchar(60) DEFAULT NULL,
    `context` varchar(60) DEFAULT NULL,
    `priority` varchar(60) DEFAULT NULL,
    `application` varchar(60) DEFAULT NULL,
    `data` varchar(60) DEFAULT NULL,
    `timeout` varchar(60) DEFAULT NULL,
    `callerid` varchar(60) DEFAULT NULL,
    `variable` varchar(2000) DEFAULT NULL,
    `account` varchar(60) DEFAULT NULL,
    `async` varchar(60) DEFAULT NULL,
    `actionid` varchar(60) DEFAULT NULL,
    `id_server` bigint DEFAULT NULL,
    `id_server_group` bigint DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY (`uniqueid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_callerid`
--

DROP TABLE IF EXISTS `cc_callerid`;
CREATE TABLE `cc_callerid` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `cid` varchar(100) NOT NULL,
    `id_cc_card` bigint DEFAULT NULL,
    `activated` varchar(1) NOT NULL DEFAULT 't',
    PRIMARY KEY (`id`),
    UNIQUE KEY (`cid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_campaign`
--

DROP TABLE IF EXISTS `cc_campaign`;
CREATE TABLE `cc_campaign` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `name` varchar(50) NOT NULL,
    `creationdate` datetime NOT NULL DEFAULT current_timestamp(),
    `startingdate` datetime DEFAULT NULL,
    `expirationdate` datetime DEFAULT NULL,
    `description` mediumtext DEFAULT NULL,
    `id_card` bigint DEFAULT NULL,
    `secondusedreal` int DEFAULT 0,
    `nb_callmade` int DEFAULT 0,
    `status` int NOT NULL DEFAULT 1,
    `frequency` int NOT NULL DEFAULT 20,
    `forward_number` varchar(50) DEFAULT NULL,
    `daily_start_time` time NOT NULL DEFAULT '10:00:00',
    `daily_stop_time` time NOT NULL DEFAULT '18:00:00',
    `monday` tinyint NOT NULL DEFAULT 1,
    `tuesday` tinyint NOT NULL DEFAULT 1,
    `wednesday` tinyint NOT NULL DEFAULT 1,
    `thursday` tinyint NOT NULL DEFAULT 1,
    `friday` tinyint NOT NULL DEFAULT 1,
    `saturday` tinyint NOT NULL DEFAULT 0,
    `sunday` tinyint NOT NULL DEFAULT 0,
    `id_cid_group` bigint DEFAULT NULL,
    `id_campaign_config` bigint DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_campaign_config`
--

DROP TABLE IF EXISTS `cc_campaign_config`;
CREATE TABLE `cc_campaign_config` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `name` varchar(40) NOT NULL,
    `flatrate` decimal(15,5) NOT NULL DEFAULT 0.00000,
    `context` varchar(40) NOT NULL,
    `description` mediumtext DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_campaign_phonebook`
--

DROP TABLE IF EXISTS `cc_campaign_phonebook`;
CREATE TABLE `cc_campaign_phonebook` (
    `id_campaign` bigint NOT NULL,
    `id_phonebook` bigint NOT NULL,
    PRIMARY KEY (`id_campaign`,`id_phonebook`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_campaign_phonestatus`
--

DROP TABLE IF EXISTS `cc_campaign_phonestatus`;
CREATE TABLE `cc_campaign_phonestatus` (
    `id_phonenumber` bigint NOT NULL,
    `id_campaign` bigint NOT NULL,
    `id_callback` bigint DEFAULT NULL,
    `status` int NOT NULL DEFAULT 0,
    `lastuse` datetime NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id_phonenumber`,`id_campaign`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_campaignconf_cardgroup`
--

DROP TABLE IF EXISTS `cc_campaignconf_cardgroup`;
CREATE TABLE `cc_campaignconf_cardgroup` (
    `id_campaign_config` bigint NOT NULL,
    `id_card_group` bigint NOT NULL,
    PRIMARY KEY (`id_campaign_config`,`id_card_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_card`
--

DROP TABLE IF EXISTS `cc_card`;
CREATE TABLE `cc_card` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `creationdate` datetime NOT NULL DEFAULT current_timestamp(),
    `firstusedate` datetime DEFAULT NULL,
    `expirationdate` datetime DEFAULT NULL,
    `enableexpire` int DEFAULT 0,
    `expiredays` int DEFAULT 0,
    `username` varchar(50) NOT NULL,
    `useralias` varchar(50) NOT NULL,
    `uipass` varchar(50) NOT NULL,
    `credit` decimal(15,5) NOT NULL DEFAULT 0.00000,
    `tariff` bigint DEFAULT NULL,
    `id_didgroup` int DEFAULT 0,
    `status` int NOT NULL DEFAULT 1,
    `lastname` varchar(50) NOT NULL DEFAULT '',
    `firstname` varchar(50) NOT NULL DEFAULT '',
    `address` varchar(100) NOT NULL DEFAULT '',
    `city` varchar(40) NOT NULL DEFAULT '',
    `state` varchar(40) NOT NULL DEFAULT '',
    `country` varchar(40) NOT NULL DEFAULT '',
    `zipcode` varchar(20) NOT NULL DEFAULT '',
    `phone` varchar(20) NOT NULL DEFAULT '',
    `email` varchar(70) NOT NULL DEFAULT '',
    `fax` varchar(20) NOT NULL DEFAULT '',
    `inuse` int DEFAULT 0,
    `simultaccess` int DEFAULT 0,
    `currency` varchar(3) DEFAULT 'USD',
    `lastuse` datetime DEFAULT NULL,
    `nbused` int DEFAULT 0,
    `typepaid` int DEFAULT 0,
    `creditlimit` int DEFAULT 0,
    `voipcall` int DEFAULT 0,
    `sip_buddy` int DEFAULT 0,
    `iax_buddy` int DEFAULT 0,
    `language` varchar(5) DEFAULT 'en',
    `redial` varchar(50) NOT NULL DEFAULT '',
    `runservice` int DEFAULT 0,
    `nbservice` int DEFAULT 0,
    `id_campaign` bigint DEFAULT NULL,
    `num_trials_done` bigint DEFAULT 0,
    `vat` float NOT NULL DEFAULT 0,
    `servicelastrun` datetime DEFAULT NULL,
    `initialbalance` decimal(15,5) NOT NULL DEFAULT 0.00000,
    `invoiceday` int DEFAULT 1,
    `autorefill` int DEFAULT 0,
    `loginkey` varchar(40) NOT NULL DEFAULT '',
    `mac_addr` varchar(17) NOT NULL DEFAULT '00-00-00-00-00-00',
    `id_timezone` bigint DEFAULT NULL,
    `tag` varchar(50) NOT NULL DEFAULT '',
    `voicemail_permitted` int NOT NULL DEFAULT 0,
    `voicemail_activated` smallint NOT NULL DEFAULT 0,
    `last_notification` datetime DEFAULT NULL,
    `email_notification` varchar(70) NOT NULL DEFAULT '',
    `notify_email` smallint NOT NULL DEFAULT 0,
    `credit_notification` int NOT NULL DEFAULT -1,
    `id_group` bigint DEFAULT NULL,
    `company_name` varchar(50) NOT NULL DEFAULT '',
    `company_website` varchar(60) NOT NULL DEFAULT '',
    `vat_rn` varchar(40) DEFAULT NULL,
    `traffic` bigint DEFAULT NULL,
    `traffic_target` varchar(300) NOT NULL DEFAULT '',
    `discount` decimal(5,2) NOT NULL DEFAULT 0.00,
    `restriction` tinyint NOT NULL DEFAULT 0,
    `id_seria` bigint DEFAULT NULL,
    `serial` bigint DEFAULT NULL,
    `block` tinyint NOT NULL DEFAULT 0,
    `lock_pin` varchar(15) DEFAULT NULL,
    `lock_date` datetime DEFAULT NULL,
    `max_concurrent` int NOT NULL DEFAULT 10,
    PRIMARY KEY (`id`),
    UNIQUE KEY (`username`),
    UNIQUE KEY (`useralias`),
    KEY (`creationdate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DELIMITER ;;
CREATE TRIGGER `cc_card_serial_set` BEFORE INSERT ON `cc_card`
    FOR EACH ROW BEGIN
    UPDATE cc_card_seria set value=value+1  where id=NEW.id_seria ;
    SELECT value INTO @serial from cc_card_seria where id=NEW.id_seria ;
    SET NEW.serial=@serial;
END;;
DELIMITER ;

DELIMITER ;;
CREATE TRIGGER `cc_card_serial_update` BEFORE UPDATE ON `cc_card`
    FOR EACH ROW BEGIN
    IF NEW.id_seria<>OLD.id_seria OR OLD.id_seria IS NULL THEN
        UPDATE cc_card_seria set value=value+1  where id=NEW.id_seria ;
        SELECT value INTO @serial from cc_card_seria where id=NEW.id_seria ;
        SET NEW.serial=@serial;
    END IF;
END;;
DELIMITER ;

--
-- Table structure for table `cc_card_archive`
--

DROP TABLE IF EXISTS `cc_card_archive`;
CREATE TABLE `cc_card_archive` (
    `id` bigint NOT NULL,
    `creationdate` datetime NOT NULL DEFAULT current_timestamp(),
    `firstusedate` datetime DEFAULT NULL,
    `expirationdate` datetime DEFAULT NULL,
    `enableexpire` int DEFAULT 0,
    `expiredays` int DEFAULT 0,
    `username` varchar(50) NOT NULL,
    `useralias` varchar(50) NOT NULL,
    `uipass` varchar(50) DEFAULT NULL,
    `credit` decimal(15,5) NOT NULL DEFAULT 0.00000,
    `tariff` int DEFAULT 0,
    `id_didgroup` int DEFAULT 0,
    `status` int DEFAULT 1,
    `lastname` varchar(50) DEFAULT NULL,
    `firstname` varchar(50) DEFAULT NULL,
    `address` varchar(100) DEFAULT NULL,
    `city` varchar(40) DEFAULT NULL,
    `state` varchar(40) DEFAULT NULL,
    `country` varchar(40) DEFAULT NULL,
    `zipcode` varchar(20) DEFAULT NULL,
    `phone` varchar(20) DEFAULT NULL,
    `email` varchar(70) DEFAULT NULL,
    `fax` varchar(20) DEFAULT NULL,
    `inuse` int DEFAULT 0,
    `simultaccess` int DEFAULT 0,
    `currency` varchar(3) DEFAULT 'USD',
    `lastuse` datetime DEFAULT NULL,
    `nbused` int DEFAULT 0,
    `typepaid` int DEFAULT 0,
    `creditlimit` int DEFAULT 0,
    `voipcall` int DEFAULT 0,
    `sip_buddy` int DEFAULT 0,
    `iax_buddy` int DEFAULT 0,
    `language` varchar(5) DEFAULT 'en',
    `redial` varchar(50) DEFAULT NULL,
    `runservice` int DEFAULT 0,
    `nbservice` int DEFAULT 0,
    `id_campaign` int DEFAULT 0,
    `num_trials_done` bigint DEFAULT 0,
    `vat` float NOT NULL DEFAULT 0,
    `servicelastrun` datetime DEFAULT NULL,
    `initialbalance` decimal(15,5) NOT NULL DEFAULT 0.00000,
    `invoiceday` int DEFAULT 1,
    `autorefill` int DEFAULT 0,
    `loginkey` varchar(40) DEFAULT NULL,
    `activatedbyuser` varchar(1) NOT NULL DEFAULT 't',
    `id_timezone` int DEFAULT 0,
    `tag` varchar(50) DEFAULT NULL,
    `voicemail_permitted` int NOT NULL DEFAULT 0,
    `voicemail_activated` smallint NOT NULL DEFAULT 0,
    `last_notification` datetime DEFAULT NULL,
    `email_notification` varchar(70) DEFAULT NULL,
    `notify_email` smallint NOT NULL DEFAULT 0,
    `credit_notification` int NOT NULL DEFAULT -1,
    `id_group` int NOT NULL DEFAULT 1,
    `company_name` varchar(50) DEFAULT NULL,
    `company_website` varchar(60) DEFAULT NULL,
    `VAT_RN` varchar(40) DEFAULT NULL,
    `traffic` bigint DEFAULT NULL,
    `traffic_target` mediumtext DEFAULT NULL,
    `discount` decimal(5,2) NOT NULL DEFAULT 0.00,
    `restriction` tinyint NOT NULL DEFAULT 0,
    `mac_addr` varchar(17) NOT NULL DEFAULT '00-00-00-00-00-00',
    PRIMARY KEY (`id`),
    KEY (`creationdate`),
    KEY (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_card_group`
--

DROP TABLE IF EXISTS `cc_card_group`;
CREATE TABLE `cc_card_group` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `name` varchar(50) DEFAULT NULL,
    `description` varchar(400) DEFAULT NULL,
    `users_perms` int NOT NULL DEFAULT 0,
    `id_agent` bigint DEFAULT NULL,
    `provisioning` varchar(200) DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_card_history`
--

DROP TABLE IF EXISTS `cc_card_history`;
CREATE TABLE `cc_card_history` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `id_cc_card` bigint DEFAULT NULL,
    `datecreated` datetime NOT NULL DEFAULT current_timestamp(),
    `description` text DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_card_package_offer`
--

DROP TABLE IF EXISTS `cc_card_package_offer`;
CREATE TABLE `cc_card_package_offer` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `id_cc_card` bigint DEFAULT NULL,
    `id_cc_package_offer` bigint DEFAULT NULL,
    `date_consumption` datetime NOT NULL DEFAULT current_timestamp(),
    `used_secondes` bigint NOT NULL,
    PRIMARY KEY (`id`),
    KEY (`id_cc_card`),
    KEY (`id_cc_package_offer`),
    KEY (`date_consumption`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_card_seria`
--

DROP TABLE IF EXISTS `cc_card_seria`;
CREATE TABLE `cc_card_seria` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `name` varchar(30) NOT NULL,
    `description` mediumtext DEFAULT NULL,
    `value` bigint NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_card_subscription`
--

DROP TABLE IF EXISTS `cc_card_subscription`;
CREATE TABLE `cc_card_subscription` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `id_cc_card` bigint DEFAULT NULL,
    `id_subscription_fee` bigint DEFAULT NULL,
    `startdate` datetime NOT NULL DEFAULT current_timestamp(),
    `stopdate` datetime DEFAULT NULL,
    `product_id` varchar(100) DEFAULT NULL,
    `product_name` varchar(100) DEFAULT NULL,
    `paid_status` tinyint NOT NULL DEFAULT 0,
    `last_run` datetime DEFAULT NULL,
    `next_billing_date` datetime DEFAULT NULL,
    `limit_pay_date` datetime DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_cardgroup_service`
--

DROP TABLE IF EXISTS `cc_cardgroup_service`;
CREATE TABLE `cc_cardgroup_service` (
    `id_card_group` bigint NOT NULL,
    `id_service` bigint NOT NULL,
    PRIMARY KEY (`id_card_group`,`id_service`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_charge`
--

DROP TABLE IF EXISTS `cc_charge`;
CREATE TABLE `cc_charge` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `id_cc_card` bigint DEFAULT NULL,
    `iduser` bigint DEFAULT NULL,
    `creationdate` datetime NOT NULL DEFAULT current_timestamp(),
    `amount` float NOT NULL DEFAULT 0,
    `chargetype` int DEFAULT 0,
    `description` mediumtext DEFAULT NULL,
    `id_cc_did` bigint DEFAULT NULL,
    `id_cc_card_subscription` bigint DEFAULT NULL,
    `cover_from` date DEFAULT NULL,
    `cover_to` date DEFAULT NULL,
    `charged_status` tinyint NOT NULL DEFAULT 0,
    `invoiced_status` tinyint NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    KEY (`id_cc_card`),
    KEY (`creationdate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_config`
--

DROP TABLE IF EXISTS `cc_config`;
CREATE TABLE `cc_config` (
    `id` int NOT NULL AUTO_INCREMENT,
    `config_title` varchar(100) DEFAULT NULL,
    `config_key` varchar(100) DEFAULT NULL,
    `config_value` varchar(200) DEFAULT NULL,
    `config_description` varchar(500) DEFAULT NULL,
    `config_valuetype` int NOT NULL DEFAULT 0,
    `config_listvalues` varchar(100) DEFAULT NULL,
    `config_group_id` bigint DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_config_group`
--

DROP TABLE IF EXISTS `cc_config_group`;
CREATE TABLE `cc_config_group` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `group_title` varchar(64) NOT NULL,
    `group_description` varchar(255) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY (`group_title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_configuration`
--

DROP TABLE IF EXISTS `cc_configuration`;
CREATE TABLE `cc_configuration` (
    `configuration_id` int NOT NULL AUTO_INCREMENT,
    `configuration_title` varchar(64) NOT NULL,
    `configuration_key` varchar(64) NOT NULL,
    `configuration_value` varchar(255) NOT NULL,
    `configuration_description` varchar(255) NOT NULL,
    `configuration_type` int NOT NULL DEFAULT 0,
    `use_function` varchar(255) DEFAULT NULL,
    `set_function` varchar(255) DEFAULT NULL,
    PRIMARY KEY (`configuration_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_country`
--

DROP TABLE IF EXISTS `cc_country`;
CREATE TABLE `cc_country` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `countrycode` varchar(80) NOT NULL,
    `countryprefix` varchar(80) NOT NULL,
    `countryname` varchar(80) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_currencies`
--

DROP TABLE IF EXISTS `cc_currencies`;
CREATE TABLE `cc_currencies` (
    `id` smallint unsigned NOT NULL AUTO_INCREMENT,
    `currency` varchar(3) NOT NULL DEFAULT '',
    `name` varchar(30) NOT NULL DEFAULT '',
    `value` decimal(12,5) unsigned NOT NULL DEFAULT 0.00000,
    `lastupdate` datetime NOT NULL DEFAULT current_timestamp(),
    `basecurrency` varchar(3) NOT NULL DEFAULT 'USD',
    PRIMARY KEY (`id`),
    UNIQUE KEY (`currency`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_did`
--

DROP TABLE IF EXISTS `cc_did`;
CREATE TABLE `cc_did` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `id_cc_didgroup` bigint DEFAULT NULL,
    `id_cc_country` bigint DEFAULT NULL,
    `activated` int NOT NULL DEFAULT 1,
    `reserved` int DEFAULT 0,
    `iduser` bigint NOT NULL DEFAULT 0,
    `did` varchar(50) NOT NULL,
    `creationdate` datetime NOT NULL DEFAULT current_timestamp(),
    `startingdate` datetime DEFAULT NULL,
    `expirationdate` datetime DEFAULT NULL,
    `description` mediumtext DEFAULT NULL,
    `secondusedreal` int DEFAULT 0,
    `billingtype` int DEFAULT 0,
    `fixrate` float NOT NULL DEFAULT 0,
    `connection_charge` decimal(15,5) NOT NULL DEFAULT 0.00000,
    `selling_rate` decimal(15,5) NOT NULL DEFAULT 0.00000,
    `aleg_carrier_connect_charge` decimal(15,5) NOT NULL DEFAULT 0.00000,
    `aleg_carrier_cost_min` decimal(15,5) NOT NULL DEFAULT 0.00000,
    `aleg_retail_connect_charge` decimal(15,5) NOT NULL DEFAULT 0.00000,
    `aleg_retail_cost_min` decimal(15,5) NOT NULL DEFAULT 0.00000,
    `aleg_carrier_initblock` int NOT NULL DEFAULT 0,
    `aleg_carrier_increment` int NOT NULL DEFAULT 0,
    `aleg_retail_initblock` int NOT NULL DEFAULT 0,
    `aleg_retail_increment` int NOT NULL DEFAULT 0,
    `aleg_timeinterval` text DEFAULT NULL,
    `aleg_carrier_connect_charge_offp` decimal(15,5) NOT NULL DEFAULT 0.00000,
    `aleg_carrier_cost_min_offp` decimal(15,5) NOT NULL DEFAULT 0.00000,
    `aleg_retail_connect_charge_offp` decimal(15,5) NOT NULL DEFAULT 0.00000,
    `aleg_retail_cost_min_offp` decimal(15,5) NOT NULL DEFAULT 0.00000,
    `aleg_carrier_initblock_offp` int NOT NULL DEFAULT 0,
    `aleg_carrier_increment_offp` int NOT NULL DEFAULT 0,
    `aleg_retail_initblock_offp` int NOT NULL DEFAULT 0,
    `aleg_retail_increment_offp` int NOT NULL DEFAULT 0,
    `max_concurrent` int NOT NULL DEFAULT 10,
    PRIMARY KEY (`id`),
    UNIQUE KEY (`did`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_did_destination`
--

DROP TABLE IF EXISTS `cc_did_destination`;
CREATE TABLE `cc_did_destination` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `destination` varchar(100) DEFAULT NULL,
    `priority` int NOT NULL DEFAULT 0,
    `id_cc_card` bigint DEFAULT NULL,
    `id_cc_did` bigint DEFAULT NULL,
    `creationdate` datetime NOT NULL DEFAULT current_timestamp(),
    `activated` int NOT NULL DEFAULT 1,
    `secondusedreal` int DEFAULT 0,
    `voip_call` int DEFAULT 0,
    `validated` int DEFAULT 0,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_did_use`
--

DROP TABLE IF EXISTS `cc_did_use`;
CREATE TABLE `cc_did_use` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `id_cc_card` bigint DEFAULT NULL,
    `id_did` bigint DEFAULT NULL,
    `reservationdate` datetime NOT NULL DEFAULT current_timestamp(),
    `releasedate` datetime DEFAULT NULL,
    `activated` int DEFAULT 0,
    `month_payed` int DEFAULT 0,
    `reminded` tinyint NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_didgroup`
--

DROP TABLE IF EXISTS `cc_didgroup`;
CREATE TABLE `cc_didgroup` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `didgroupname` varchar(50) NOT NULL,
    `creationdate` datetime NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_epayment_log`
--

DROP TABLE IF EXISTS `cc_epayment_log`;
CREATE TABLE `cc_epayment_log` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `cardid` bigint NOT NULL DEFAULT 0,
    `amount` varchar(50) NOT NULL DEFAULT '0',
    `vat` float NOT NULL DEFAULT 0,
    `paymentmethod` varchar(50) NOT NULL,
    `cc_owner` varchar(64) DEFAULT NULL,
    `cc_number` varchar(32) DEFAULT NULL,
    `cc_expires` varchar(7) DEFAULT NULL,
    `creationdate` datetime NOT NULL DEFAULT current_timestamp(),
    `status` int NOT NULL DEFAULT 0,
    `cvv` varchar(4) DEFAULT NULL,
    `credit_card_type` varchar(20) DEFAULT NULL,
    `currency` varchar(4) DEFAULT NULL,
    `transaction_detail` longtext DEFAULT NULL,
    `item_type` varchar(30) DEFAULT NULL,
    `item_id` bigint DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_epayment_log_agent`
--

DROP TABLE IF EXISTS `cc_epayment_log_agent`;
CREATE TABLE `cc_epayment_log_agent` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `agent_id` bigint NOT NULL DEFAULT 0,
    `amount` varchar(50) NOT NULL DEFAULT '0',
    `vat` float NOT NULL DEFAULT 0,
    `paymentmethod` varchar(50) NOT NULL,
    `cc_owner` varchar(64) DEFAULT NULL,
    `cc_number` varchar(32) DEFAULT NULL,
    `cc_expires` varchar(7) DEFAULT NULL,
    `creationdate` datetime NOT NULL DEFAULT current_timestamp(),
    `status` int NOT NULL DEFAULT 0,
    `cvv` varchar(4) DEFAULT NULL,
    `credit_card_type` varchar(20) DEFAULT NULL,
    `currency` varchar(4) DEFAULT NULL,
    `transaction_detail` longtext DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_iax_buddies`
--

DROP TABLE IF EXISTS `cc_iax_buddies`;
CREATE TABLE `cc_iax_buddies` (
    `id` int NOT NULL AUTO_INCREMENT,
    `id_cc_card` bigint DEFAULT NULL,
    `name` varchar(80) NOT NULL,
    `accountcode` varchar(20) NOT NULL,
    `regexten` varchar(20) NOT NULL,
    `amaflags` varchar(7) DEFAULT NULL,
    `callerid` varchar(80) NOT NULL,
    `context` varchar(80) NOT NULL,
    `DEFAULTip` varchar(50) DEFAULT NULL,
    `host` varchar(50) NOT NULL,
    `language` varchar(2) DEFAULT NULL,
    `deny` varchar(95) NOT NULL,
    `permit` varchar(95) DEFAULT NULL,
    `mask` varchar(95) NOT NULL,
    `port` varchar(5) NOT NULL DEFAULT '',
    `qualify` varchar(7) DEFAULT 'yes',
    `secret` varchar(80) NOT NULL,
    `type` varchar(6) NOT NULL DEFAULT 'friend',
    `username` varchar(80) NOT NULL,
    `disallow` varchar(100) NOT NULL,
    `allow` varchar(100) NOT NULL,
    `regseconds` int NOT NULL DEFAULT 0,
    `ipaddr` varchar(50) NOT NULL DEFAULT '',
    `trunk` varchar(3) DEFAULT 'no',
    `dbsecret` varchar(40) NOT NULL DEFAULT '',
    `regcontext` varchar(40) NOT NULL DEFAULT '',
    `sourceaddress` varchar(50) NOT NULL DEFAULT '',
    `mohinterpret` varchar(20) NOT NULL DEFAULT '',
    `mohsuggest` varchar(20) NOT NULL DEFAULT '',
    `inkeys` varchar(40) NOT NULL DEFAULT '',
    `outkey` varchar(40) NOT NULL DEFAULT '',
    `cid_number` varchar(40) NOT NULL DEFAULT '',
    `sendani` varchar(10) NOT NULL DEFAULT '',
    `fullname` varchar(40) NOT NULL DEFAULT '',
    `auth` varchar(20) NOT NULL DEFAULT '',
    `maxauthreq` varchar(15) NOT NULL DEFAULT '',
    `encryption` varchar(20) NOT NULL DEFAULT '',
    `transfer` varchar(10) NOT NULL DEFAULT '',
    `jitterbuffer` varchar(10) NOT NULL DEFAULT '',
    `forcejitterbuffer` varchar(10) NOT NULL DEFAULT '',
    `codecpriority` varchar(40) NOT NULL DEFAULT '',
    `qualifysmoothing` varchar(10) NOT NULL DEFAULT '',
    `qualifyfreqok` varchar(10) NOT NULL DEFAULT '',
    `qualifyfreqnotok` varchar(10) NOT NULL DEFAULT '',
    `timezone` varchar(20) NOT NULL DEFAULT '',
    `adsi` varchar(10) NOT NULL DEFAULT '',
    `setvar` varchar(200) NOT NULL DEFAULT '',
    `requirecalltoken` varchar(20) NOT NULL DEFAULT '',
    `maxcallnumbers` varchar(10) NOT NULL DEFAULT '',
    `maxcallnumbers_nonvalidated` varchar(10) NOT NULL DEFAULT '',
    PRIMARY KEY (`id`),
    UNIQUE KEY (`name`),
    KEY (`host`),
    KEY (`ipaddr`),
    KEY (`port`),
    KEY (`name`,`host`),
    KEY (`name`,`ipaddr`,`port`),
    KEY (`ipaddr`,`port`),
    KEY (`host`,`port`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_invoice`
--

DROP TABLE IF EXISTS `cc_invoice`;
CREATE TABLE `cc_invoice` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `reference` varchar(30) DEFAULT NULL,
    `id_card` bigint DEFAULT NULL,
    `date` datetime NOT NULL DEFAULT current_timestamp(),
    `paid_status` tinyint NOT NULL DEFAULT 0,
    `status` tinyint NOT NULL DEFAULT 0,
    `title` varchar(50) NOT NULL,
    `description` mediumtext NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY (`reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_invoice_conf`
--

DROP TABLE IF EXISTS `cc_invoice_conf`;
CREATE TABLE `cc_invoice_conf` (
    `id` int NOT NULL AUTO_INCREMENT,
    `key_val` varchar(50) NOT NULL,
    `value` varchar(50) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY (`key_val`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_invoice_item`
--

DROP TABLE IF EXISTS `cc_invoice_item`;
CREATE TABLE `cc_invoice_item` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `id_invoice` bigint DEFAULT NULL,
    `date` datetime NOT NULL DEFAULT current_timestamp(),
    `price` decimal(15,5) NOT NULL DEFAULT 0.00000,
    `VAT` decimal(4,2) NOT NULL DEFAULT 0.00,
    `description` mediumtext NOT NULL,
    `id_ext` bigint DEFAULT NULL,
    `type_ext` varchar(10) DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_invoice_payment`
--

DROP TABLE IF EXISTS `cc_invoice_payment`;
CREATE TABLE `cc_invoice_payment` (
    `id_invoice` bigint NOT NULL,
    `id_payment` bigint NOT NULL,
    PRIMARY KEY (`id_invoice`,`id_payment`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_iso639`
--

DROP TABLE IF EXISTS `cc_iso639`;
CREATE TABLE `cc_iso639` (
    `code` varchar(2) NOT NULL,
    `name` varchar(16) NOT NULL,
    `lname` varchar(16) DEFAULT NULL,
    `charset` varchar(16) NOT NULL DEFAULT 'ISO-8859-1',
    PRIMARY KEY (`code`),
    UNIQUE KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_logpayment`
--

DROP TABLE IF EXISTS `cc_logpayment`;
CREATE TABLE `cc_logpayment` (
    `id` int NOT NULL AUTO_INCREMENT,
    `date` datetime NOT NULL DEFAULT current_timestamp(),
    `payment` decimal(15,5) NOT NULL,
    `card_id` bigint DEFAULT NULL,
    `id_logrefill` bigint DEFAULT NULL,
    `description` mediumtext DEFAULT NULL,
    `added_refill` smallint NOT NULL DEFAULT 0,
    `payment_type` tinyint NOT NULL DEFAULT 0,
    `added_commission` tinyint NOT NULL DEFAULT 0,
    `agent_id` bigint DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_logpayment_agent`
--

DROP TABLE IF EXISTS `cc_logpayment_agent`;
CREATE TABLE `cc_logpayment_agent` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `date` datetime NOT NULL DEFAULT current_timestamp(),
    `payment` decimal(15,5) NOT NULL,
    `agent_id` bigint DEFAULT NULL,
    `id_logrefill` bigint DEFAULT NULL,
    `description` mediumtext DEFAULT NULL,
    `added_refill` tinyint NOT NULL DEFAULT 0,
    `payment_type` tinyint NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_logrefill`
--

DROP TABLE IF EXISTS `cc_logrefill`;
CREATE TABLE `cc_logrefill` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `date` datetime NOT NULL DEFAULT current_timestamp(),
    `credit` decimal(15,5) NOT NULL,
    `card_id` bigint DEFAULT NULL,
    `description` mediumtext DEFAULT NULL,
    `refill_type` tinyint NOT NULL DEFAULT 0,
    `added_invoice` tinyint NOT NULL DEFAULT 0,
    `agent_id` bigint DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_logrefill_agent`
--

DROP TABLE IF EXISTS `cc_logrefill_agent`;
CREATE TABLE `cc_logrefill_agent` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `date` datetime NOT NULL DEFAULT current_timestamp(),
    `credit` decimal(15,5) NOT NULL,
    `agent_id` bigint DEFAULT NULL,
    `description` mediumtext DEFAULT NULL,
    `refill_type` tinyint NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_message_agent`
--

DROP TABLE IF EXISTS `cc_message_agent`;
CREATE TABLE `cc_message_agent` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `id_agent` bigint DEFAULT NULL,
    `message` longtext DEFAULT NULL,
    `type` tinyint NOT NULL DEFAULT 0,
    `logo` tinyint NOT NULL DEFAULT 1,
    `order_display` int NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_monitor`
--

DROP TABLE IF EXISTS `cc_monitor`;
CREATE TABLE `cc_monitor` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `label` varchar(50) NOT NULL,
    `dial_code` int DEFAULT NULL,
    `description` varchar(250) DEFAULT NULL,
    `text_intro` varchar(250) DEFAULT NULL,
    `query_type` tinyint NOT NULL DEFAULT 1,
    `query` varchar(1000) DEFAULT NULL,
    `result_type` tinyint NOT NULL DEFAULT 1,
    `enable` tinyint NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_notification`
--

DROP TABLE IF EXISTS `cc_notification`;
CREATE TABLE `cc_notification` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `key_value` varchar(255) DEFAULT NULL,
    `date` datetime NOT NULL DEFAULT current_timestamp(),
    `priority` tinyint NOT NULL DEFAULT 0,
    `from_type` tinyint NOT NULL,
    `from_id` bigint DEFAULT 0,
    `link_id` bigint DEFAULT NULL,
    `link_type` varchar(20) DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_notification_admin`
--

DROP TABLE IF EXISTS `cc_notification_admin`;
CREATE TABLE `cc_notification_admin` (
    `id_notification` bigint NOT NULL,
    `id_admin` bigint NOT NULL,
    `viewed` tinyint NOT NULL DEFAULT 0,
    PRIMARY KEY (`id_notification`,`id_admin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_outbound_cid_group`
--

DROP TABLE IF EXISTS `cc_outbound_cid_group`;
CREATE TABLE `cc_outbound_cid_group` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `creationdate` datetime NOT NULL DEFAULT current_timestamp(),
    `group_name` varchar(70) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_outbound_cid_list`
--

DROP TABLE IF EXISTS `cc_outbound_cid_list`;
CREATE TABLE `cc_outbound_cid_list` (
    `id` int NOT NULL AUTO_INCREMENT,
    `outbound_cid_group` bigint DEFAULT NULL,
    `cid` varchar(100) DEFAULT NULL,
    `activated` int NOT NULL DEFAULT 0,
    `creationdate` datetime NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_package_group`
--

DROP TABLE IF EXISTS `cc_package_group`;
CREATE TABLE `cc_package_group` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `name` varchar(30) NOT NULL,
    `description` mediumtext DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_package_offer`
--

DROP TABLE IF EXISTS `cc_package_offer`;
CREATE TABLE `cc_package_offer` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `creationdate` datetime NOT NULL DEFAULT current_timestamp(),
    `label` varchar(70) NOT NULL,
    `packagetype` int NOT NULL,
    `billingtype` int NOT NULL,
    `startday` int NOT NULL,
    `freetimetocall` int NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_package_rate`
--

DROP TABLE IF EXISTS `cc_package_rate`;
CREATE TABLE `cc_package_rate` (
    `package_id` int NOT NULL,
    `rate_id` int NOT NULL,
    PRIMARY KEY (`package_id`,`rate_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_packgroup_package`
--

DROP TABLE IF EXISTS `cc_packgroup_package`;
CREATE TABLE `cc_packgroup_package` (
    `packagegroup_id` bigint NOT NULL,
    `package_id` bigint NOT NULL,
    PRIMARY KEY (`packagegroup_id`,`package_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_payment_methods`
--

DROP TABLE IF EXISTS `cc_payment_methods`;
CREATE TABLE `cc_payment_methods` (
    `id` int NOT NULL AUTO_INCREMENT,
    `payment_method` varchar(100) NOT NULL,
    `payment_filename` varchar(200) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_payments`
--

DROP TABLE IF EXISTS `cc_payments`;
CREATE TABLE `cc_payments` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `customers_id` bigint NOT NULL DEFAULT 0,
    `customers_name` varchar(200) NOT NULL,
    `customers_email_address` varchar(96) NOT NULL,
    `item_name` varchar(127) DEFAULT NULL,
    `item_id` varchar(127) DEFAULT NULL,
    `item_quantity` int NOT NULL DEFAULT 0,
    `payment_method` varchar(32) NOT NULL,
    `cc_type` varchar(20) DEFAULT NULL,
    `cc_owner` varchar(64) DEFAULT NULL,
    `cc_number` varchar(32) DEFAULT NULL,
    `cc_expires` varchar(4) DEFAULT NULL,
    `orders_status` int NOT NULL,
    `orders_amount` decimal(14,6) DEFAULT NULL,
    `last_modified` datetime DEFAULT NULL,
    `date_purchased` datetime DEFAULT NULL,
    `orders_date_finished` datetime DEFAULT NULL,
    `currency` varchar(3) DEFAULT NULL,
    `currency_value` decimal(14,6) DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_payments_agent`
--

DROP TABLE IF EXISTS `cc_payments_agent`;
CREATE TABLE `cc_payments_agent` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `agent_id` bigint NOT NULL,
    `agent_name` varchar(200) NOT NULL,
    `agent_email_address` varchar(96) NOT NULL,
    `item_name` varchar(127) DEFAULT NULL,
    `item_id` varchar(127) DEFAULT NULL,
    `item_quantity` int NOT NULL DEFAULT 0,
    `payment_method` varchar(32) NOT NULL,
    `cc_type` varchar(20) DEFAULT NULL,
    `cc_owner` varchar(64) DEFAULT NULL,
    `cc_number` varchar(32) DEFAULT NULL,
    `cc_expires` varchar(4) DEFAULT NULL,
    `orders_status` int NOT NULL,
    `orders_amount` decimal(14,6) DEFAULT NULL,
    `last_modified` datetime DEFAULT NULL,
    `date_purchased` datetime DEFAULT NULL,
    `orders_date_finished` datetime DEFAULT NULL,
    `currency` varchar(3) DEFAULT NULL,
    `currency_value` decimal(14,6) DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_payments_status`
--

DROP TABLE IF EXISTS `cc_payments_status`;
CREATE TABLE `cc_payments_status` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `status_id` bigint DEFAULT NULL,
    `status_name` varchar(200) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_paypal`
--

DROP TABLE IF EXISTS `cc_paypal`;
CREATE TABLE `cc_paypal` (
    `id` int NOT NULL AUTO_INCREMENT,
    `payer_id` varchar(50) DEFAULT NULL,
    `payment_date` varchar(30) DEFAULT NULL,
    `txn_id` varchar(30) DEFAULT NULL,
    `first_name` varchar(40) DEFAULT NULL,
    `last_name` varchar(40) DEFAULT NULL,
    `payer_email` varchar(55) DEFAULT NULL,
    `payer_status` varchar(30) DEFAULT NULL,
    `payment_type` varchar(30) DEFAULT NULL,
    `memo` tinytext DEFAULT NULL,
    `item_name` varchar(70) DEFAULT NULL,
    `item_number` varchar(70) DEFAULT NULL,
    `quantity` int NOT NULL DEFAULT 0,
    `mc_gross` decimal(9,2) DEFAULT NULL,
    `mc_fee` decimal(9,2) DEFAULT NULL,
    `tax` decimal(9,2) DEFAULT NULL,
    `mc_currency` varchar(3) DEFAULT NULL,
    `address_name` varchar(50) NOT NULL DEFAULT '',
    `address_street` varchar(80) NOT NULL DEFAULT '',
    `address_city` varchar(40) NOT NULL DEFAULT '',
    `address_state` varchar(40) NOT NULL DEFAULT '',
    `address_zip` varchar(20) NOT NULL DEFAULT '',
    `address_country` varchar(30) NOT NULL DEFAULT '',
    `address_status` varchar(30) NOT NULL DEFAULT '',
    `payer_business_name` varchar(40) NOT NULL DEFAULT '',
    `payment_status` varchar(30) NOT NULL DEFAULT '',
    `pending_reason` varchar(50) NOT NULL DEFAULT '',
    `reason_code` varchar(30) NOT NULL DEFAULT '',
    `txn_type` varchar(30) NOT NULL DEFAULT '',
    PRIMARY KEY (`id`),
    UNIQUE KEY (`txn_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_phonebook`
--

DROP TABLE IF EXISTS `cc_phonebook`;
CREATE TABLE `cc_phonebook` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `name` varchar(30) NOT NULL,
    `description` mediumtext DEFAULT NULL,
    `id_card` bigint DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_phonenumber`
--

DROP TABLE IF EXISTS `cc_phonenumber`;
CREATE TABLE `cc_phonenumber` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `id_phonebook` bigint DEFAULT NULL,
    `number` varchar(30) NOT NULL,
    `name` varchar(40) DEFAULT NULL,
    `creationdate` datetime NOT NULL DEFAULT current_timestamp(),
    `status` smallint NOT NULL DEFAULT 1,
    `info` mediumtext DEFAULT NULL,
    `amount` int NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_prefix`
--

DROP TABLE IF EXISTS `cc_prefix`;
CREATE TABLE `cc_prefix` (
    `prefix` bigint NOT NULL AUTO_INCREMENT,
    `destination` varchar(60) NOT NULL,
    PRIMARY KEY (`prefix`),
    KEY (`destination`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_provider`
--

DROP TABLE IF EXISTS `cc_provider`;
CREATE TABLE `cc_provider` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `provider_name` varchar(30) NOT NULL,
    `creationdate` datetime NOT NULL DEFAULT current_timestamp(),
    `description` mediumtext DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY (`provider_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_ratecard`
--

DROP TABLE IF EXISTS `cc_ratecard`;
CREATE TABLE `cc_ratecard` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `idtariffplan` bigint DEFAULT NULL,
    `dialprefix` varchar(30) NOT NULL,
    `buyrate` decimal(15,5) NOT NULL DEFAULT 0.00000,
    `buyrateinitblock` int NOT NULL DEFAULT 0,
    `buyrateincrement` int NOT NULL DEFAULT 0,
    `rateinitial` decimal(15,5) NOT NULL DEFAULT 0.00000,
    `initblock` int NOT NULL DEFAULT 0,
    `billingblock` int NOT NULL DEFAULT 0,
    `connectcharge` decimal(15,5) NOT NULL DEFAULT 0.00000,
    `disconnectcharge` decimal(15,5) NOT NULL DEFAULT 0.00000,
    `stepchargea` decimal(15,5) NOT NULL DEFAULT 0.00000,
    `chargea` decimal(15,5) NOT NULL DEFAULT 0.00000,
    `timechargea` int NOT NULL DEFAULT 0,
    `billingblocka` int NOT NULL DEFAULT 0,
    `stepchargeb` decimal(15,5) NOT NULL DEFAULT 0.00000,
    `chargeb` decimal(15,5) NOT NULL DEFAULT 0.00000,
    `timechargeb` int NOT NULL DEFAULT 0,
    `billingblockb` int NOT NULL DEFAULT 0,
    `stepchargec` float NOT NULL DEFAULT 0,
    `chargec` float NOT NULL DEFAULT 0,
    `timechargec` int NOT NULL DEFAULT 0,
    `billingblockc` int NOT NULL DEFAULT 0,
    `startdate` datetime NOT NULL DEFAULT current_timestamp(),
    `stopdate` datetime DEFAULT NULL,
    `starttime` smallint unsigned DEFAULT 0,
    `endtime` smallint unsigned DEFAULT 10079,
    `id_trunk` bigint DEFAULT NULL,
    `musiconhold` varchar(100) NOT NULL,
    `id_outbound_cidgroup` bigint DEFAULT NULL,
    `rounding_calltime` int NOT NULL DEFAULT 0,
    `rounding_threshold` int NOT NULL DEFAULT 0,
    `additional_block_charge` decimal(15,5) NOT NULL DEFAULT 0.00000,
    `additional_block_charge_time` int NOT NULL DEFAULT 0,
    `tag` varchar(50) DEFAULT NULL,
    `disconnectcharge_after` int NOT NULL DEFAULT 0,
    `is_merged` int DEFAULT 0,
    `additional_grace` int NOT NULL DEFAULT 0,
    `minimal_cost` decimal(15,5) NOT NULL DEFAULT 0.00000,
    `announce_time_correction` decimal(5,3) NOT NULL DEFAULT 1.000,
    `destination` bigint NOT NULL,
    PRIMARY KEY (`id`),
    KEY (`dialprefix`),
    KEY (`idtariffplan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DELIMITER ;;
CREATE TRIGGER `cc_ratecard_validate_regex_ins` BEFORE INSERT ON `cc_ratecard`
    FOR EACH ROW BEGIN
    DECLARE valid INTEGER;
    SELECT '0' REGEXP REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(CONCAT('^', NEW.dialprefix, '$'), 'X', '[0-9]'), 'Z', '[1-9]'), 'N', '[2-9]'), '.', '.+'), '_', '') INTO valid;
END;;
DELIMITER ;

DELIMITER ;;
CREATE TRIGGER `cc_ratecard_validate_regex_upd` BEFORE UPDATE ON `cc_ratecard`
    FOR EACH ROW BEGIN
    DECLARE valid INTEGER;
    SELECT '0' REGEXP REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(CONCAT('^', NEW.dialprefix, '$'), 'X', '[0-9]'), 'Z', '[1-9]'), 'N', '[2-9]'), '.', '.+'), '_', '') INTO valid;
END;;
DELIMITER ;

--
-- Table structure for table `cc_receipt`
--

DROP TABLE IF EXISTS `cc_receipt`;
CREATE TABLE `cc_receipt` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `id_card` bigint DEFAULT NULL,
    `date` datetime NOT NULL DEFAULT current_timestamp(),
    `title` varchar(50) NOT NULL,
    `description` mediumtext NOT NULL,
    `status` tinyint NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_receipt_item`
--

DROP TABLE IF EXISTS `cc_receipt_item`;
CREATE TABLE `cc_receipt_item` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `id_receipt` bigint DEFAULT NULL,
    `date` datetime NOT NULL DEFAULT current_timestamp(),
    `price` decimal(15,5) NOT NULL DEFAULT 0.00000,
    `description` mediumtext NOT NULL,
    `id_ext` bigint DEFAULT NULL,
    `type_ext` varchar(10) DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_remittance_request`
--

DROP TABLE IF EXISTS `cc_remittance_request`;
CREATE TABLE `cc_remittance_request` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `id_agent` bigint DEFAULT NULL,
    `amount` decimal(15,5) NOT NULL,
    `type` tinyint NOT NULL,
    `date` datetime NOT NULL DEFAULT current_timestamp(),
    `status` tinyint NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_restricted_phonenumber`
--

DROP TABLE IF EXISTS `cc_restricted_phonenumber`;
CREATE TABLE `cc_restricted_phonenumber` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `number` varchar(50) NOT NULL,
    `id_card` bigint DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_server_group`
--

DROP TABLE IF EXISTS `cc_server_group`;
CREATE TABLE `cc_server_group` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `name` varchar(60) DEFAULT NULL,
    `description` mediumtext DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_server_manager`
--

DROP TABLE IF EXISTS `cc_server_manager`;
CREATE TABLE `cc_server_manager` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `id_group` bigint DEFAULT NULL,
    `server_ip` varchar(40) DEFAULT NULL,
    `manager_host` varchar(50) DEFAULT NULL,
    `manager_username` varchar(50) DEFAULT NULL,
    `manager_secret` varchar(50) DEFAULT NULL,
    `lasttime_used` datetime NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_service`
--

DROP TABLE IF EXISTS `cc_service`;
CREATE TABLE `cc_service` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `amount` float NOT NULL,
    `period` int NOT NULL DEFAULT 1,
    `rule` int NOT NULL DEFAULT 0,
    `daynumber` int NOT NULL DEFAULT 0,
    `stopmode` int NOT NULL DEFAULT 0,
    `maxnumbercycle` int NOT NULL DEFAULT 0,
    `status` int NOT NULL DEFAULT 0,
    `numberofrun` int NOT NULL DEFAULT 0,
    `datecreate` datetime NOT NULL DEFAULT current_timestamp(),
    `datelastrun` datetime DEFAULT NULL,
    `emailreport` varchar(100) NOT NULL,
    `totalcredit` float NOT NULL DEFAULT 0,
    `totalcardperform` int NOT NULL DEFAULT 0,
    `operate_mode` tinyint DEFAULT 0,
    `dialplan` int DEFAULT 0,
    `use_group` tinyint DEFAULT 0,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_service_report`
--

DROP TABLE IF EXISTS `cc_service_report`;
CREATE TABLE `cc_service_report` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `cc_service_id` bigint DEFAULT NULL,
    `daterun` datetime NOT NULL DEFAULT current_timestamp(),
    `totalcardperform` int DEFAULT NULL,
    `totalcredit` float DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_sip_buddies`
--

DROP TABLE IF EXISTS `cc_sip_buddies`;
CREATE TABLE `cc_sip_buddies` (
    `id` int NOT NULL AUTO_INCREMENT,
    `id_cc_card` bigint DEFAULT NULL,
    `name` varchar(80) NOT NULL,
    `accountcode` varchar(20) NOT NULL,
    `regexten` varchar(20) NOT NULL,
    `amaflags` varchar(7) DEFAULT NULL,
    `callgroup` varchar(10) DEFAULT NULL,
    `callerid` varchar(80) NOT NULL,
    `canreinvite` varchar(20) NOT NULL DEFAULT 'YES',
    `context` varchar(80) NOT NULL,
    `DEFAULTip` varchar(50) DEFAULT NULL,
    `dtmfmode` varchar(7) NOT NULL DEFAULT 'RFC2833',
    `fromuser` varchar(80) NOT NULL,
    `fromdomain` varchar(80) NOT NULL,
    `host` varchar(50) NOT NULL,
    `insecure` varchar(20) NOT NULL,
    `language` varchar(2) DEFAULT NULL,
    `mailbox` varchar(50) NOT NULL,
    `md5secret` varchar(80) NOT NULL,
    `nat` varchar(30) DEFAULT 'yes',
    `deny` varchar(95) NOT NULL,
    `permit` varchar(95) DEFAULT NULL,
    `mask` varchar(95) NOT NULL,
    `pickupgroup` varchar(10) DEFAULT NULL,
    `port` varchar(5) NOT NULL DEFAULT '',
    `qualify` varchar(7) DEFAULT 'yes',
    `restrictcid` varchar(1) DEFAULT NULL,
    `rtptimeout` varchar(3) DEFAULT NULL,
    `rtpholdtimeout` varchar(3) DEFAULT NULL,
    `secret` varchar(80) NOT NULL,
    `type` varchar(6) NOT NULL DEFAULT 'friend',
    `username` varchar(80) NOT NULL,
    `disallow` varchar(100) NOT NULL DEFAULT 'ALL',
    `allow` varchar(100) NOT NULL,
    `musiconhold` varchar(100) NOT NULL,
    `regseconds` int NOT NULL DEFAULT 0,
    `ipaddr` varchar(50) NOT NULL DEFAULT '',
    `cancallforward` varchar(3) DEFAULT 'yes',
    `fullcontact` varchar(80) NOT NULL,
    `setvar` varchar(100) NOT NULL,
    `regserver` varchar(20) DEFAULT NULL,
    `lastms` varchar(11) DEFAULT NULL,
    `defaultuser` varchar(40) NOT NULL DEFAULT '',
    `auth` varchar(10) NOT NULL DEFAULT '',
    `subscribemwi` varchar(10) NOT NULL DEFAULT '',
    `vmexten` varchar(20) NOT NULL DEFAULT '',
    `cid_number` varchar(40) NOT NULL DEFAULT '',
    `callingpres` varchar(20) NOT NULL DEFAULT '',
    `usereqphone` varchar(10) NOT NULL DEFAULT '',
    `incominglimit` varchar(10) NOT NULL DEFAULT '',
    `subscribecontext` varchar(40) NOT NULL DEFAULT '',
    `musicclass` varchar(20) NOT NULL DEFAULT '',
    `mohsuggest` varchar(20) NOT NULL DEFAULT '',
    `allowtransfer` varchar(20) NOT NULL DEFAULT '',
    `autoframing` varchar(10) NOT NULL DEFAULT '',
    `maxcallbitrate` varchar(15) NOT NULL DEFAULT '',
    `outboundproxy` varchar(40) NOT NULL DEFAULT '',
    `rtpkeepalive` varchar(15) NOT NULL DEFAULT '0',
    `useragent` varchar(80) DEFAULT NULL,
    `callbackextension` varchar(40) DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY (`name`),
    KEY (`host`),
    KEY (`ipaddr`),
    KEY (`port`),
    KEY (`host`,`port`),
    KEY (`ipaddr`,`port`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_speeddial`
--

DROP TABLE IF EXISTS `cc_speeddial`;
CREATE TABLE `cc_speeddial` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `id_cc_card` bigint DEFAULT NULL,
    `phone` varchar(100) NOT NULL,
    `name` varchar(100) NOT NULL,
    `speeddial` int DEFAULT 0,
    `creationdate` datetime NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY (`id_cc_card`,`speeddial`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_status_log`
--

DROP TABLE IF EXISTS `cc_status_log`;
CREATE TABLE `cc_status_log` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `status` int NOT NULL,
    `id_cc_card` bigint DEFAULT NULL,
    `updated_date` datetime NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_subscription_service`
--

DROP TABLE IF EXISTS `cc_subscription_service`;
CREATE TABLE `cc_subscription_service` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `label` varchar(200) NOT NULL,
    `fee` float NOT NULL DEFAULT 0,
    `status` int NOT NULL DEFAULT 0,
    `numberofrun` int NOT NULL DEFAULT 0,
    `datecreate` datetime NOT NULL DEFAULT current_timestamp(),
    `datelastrun` datetime DEFAULT NULL,
    `emailreport` varchar(100) NOT NULL,
    `totalcredit` float NOT NULL DEFAULT 0,
    `totalcardperform` int NOT NULL DEFAULT 0,
    `startdate` datetime DEFAULT NULL,
    `stopdate` datetime DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_subscription_signup`
--

DROP TABLE IF EXISTS `cc_subscription_signup`;
CREATE TABLE `cc_subscription_signup` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `label` varchar(50) NOT NULL,
    `id_subscription` bigint DEFAULT NULL,
    `description` varchar(500) DEFAULT NULL,
    `enable` tinyint NOT NULL DEFAULT 1,
    `id_callplan` bigint DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_support`
--

DROP TABLE IF EXISTS `cc_support`;
CREATE TABLE `cc_support` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `name` varchar(50) NOT NULL,
    `email` varchar(70) DEFAULT NULL,
    `language` varchar(5) NOT NULL DEFAULT 'en',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_support_component`
--

DROP TABLE IF EXISTS `cc_support_component`;
CREATE TABLE `cc_support_component` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `id_support` bigint DEFAULT NULL,
    `name` varchar(50) NOT NULL,
    `activated` smallint NOT NULL DEFAULT 1,
    `type_user` tinyint NOT NULL DEFAULT 2,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_system_log`
--

DROP TABLE IF EXISTS `cc_system_log`;
CREATE TABLE `cc_system_log` (
    `id` int NOT NULL AUTO_INCREMENT,
    `iduser` int NOT NULL DEFAULT 0,
    `loglevel` int NOT NULL DEFAULT 0,
    `action` text NOT NULL,
    `description` mediumtext DEFAULT NULL,
    `data` blob DEFAULT NULL,
    `tablename` varchar(255) DEFAULT NULL,
    `pagename` varchar(255) DEFAULT NULL,
    `ipaddress` varchar(255) DEFAULT NULL,
    `creationdate` datetime NOT NULL DEFAULT current_timestamp(),
    `agent` tinyint DEFAULT 0,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_tariffgroup`
--

DROP TABLE IF EXISTS `cc_tariffgroup`;
CREATE TABLE `cc_tariffgroup` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `iduser` int NOT NULL DEFAULT 0,
    `idtariffplan` bigint DEFAULT NULL,
    `tariffgroupname` varchar(50) NOT NULL,
    `lcrtype` int NOT NULL DEFAULT 0,
    `creationdate` datetime NOT NULL DEFAULT current_timestamp(),
    `removeinterprefix` int NOT NULL DEFAULT 0,
    `id_cc_package_offer` bigint DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_tariffgroup_plan`
--

DROP TABLE IF EXISTS `cc_tariffgroup_plan`;
CREATE TABLE `cc_tariffgroup_plan` (
    `idtariffgroup` bigint NOT NULL,
    `idtariffplan` bigint NOT NULL,
    PRIMARY KEY (`idtariffgroup`,`idtariffplan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_tariffplan`
--

DROP TABLE IF EXISTS `cc_tariffplan`;
CREATE TABLE `cc_tariffplan` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `iduser` bigint DEFAULT NULL,
    `tariffname` varchar(50) NOT NULL,
    `creationdate` datetime NOT NULL DEFAULT current_timestamp(),
    `startingdate` datetime DEFAULT NULL,
    `expirationdate` datetime DEFAULT NULL,
    `description` mediumtext DEFAULT NULL,
    `id_trunk` bigint DEFAULT NULL,
    `secondusedreal` int DEFAULT 0,
    `secondusedcarrier` int DEFAULT 0,
    `secondusedratecard` int DEFAULT 0,
    `reftariffplan` int DEFAULT 0,
    `idowner` int DEFAULT 0,
    `dnidprefix` varchar(30) NOT NULL DEFAULT 'all',
    `calleridprefix` varchar(30) NOT NULL DEFAULT 'all',
    PRIMARY KEY (`id`),
    UNIQUE KEY (`iduser`,`tariffname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_templatemail`
--

DROP TABLE IF EXISTS `cc_templatemail`;
CREATE TABLE `cc_templatemail` (
    `id` int NOT NULL AUTO_INCREMENT,
    `id_language` varchar(20) NOT NULL DEFAULT 'en',
    `mailtype` varchar(50) DEFAULT NULL,
    `fromemail` varchar(70) DEFAULT NULL,
    `fromname` varchar(70) DEFAULT NULL,
    `subject` varchar(130) DEFAULT NULL,
    `messagetext` varchar(3000) DEFAULT NULL,
    `messagehtml` varchar(3000) DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY (`mailtype`,`id_language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_ticket`
--

DROP TABLE IF EXISTS `cc_ticket`;
CREATE TABLE `cc_ticket` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `id_component` bigint DEFAULT NULL,
    `title` varchar(100) NOT NULL,
    `description` text DEFAULT NULL,
    `priority` smallint NOT NULL DEFAULT 0,
    `creationdate` datetime NOT NULL DEFAULT current_timestamp(),
    `creator` bigint NOT NULL,
    `status` smallint NOT NULL DEFAULT 0,
    `creator_type` tinyint NOT NULL DEFAULT 0,
    `viewed_cust` tinyint NOT NULL DEFAULT 1,
    `viewed_agent` tinyint NOT NULL DEFAULT 1,
    `viewed_admin` tinyint NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_ticket_comment`
--

DROP TABLE IF EXISTS `cc_ticket_comment`;
CREATE TABLE `cc_ticket_comment` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `date` datetime NOT NULL DEFAULT current_timestamp(),
    `id_ticket` bigint DEFAULT NULL,
    `description` text DEFAULT NULL,
    `creator` bigint NOT NULL,
    `creator_type` tinyint NOT NULL DEFAULT 0,
    `viewed_cust` tinyint NOT NULL DEFAULT 1,
    `viewed_agent` tinyint NOT NULL DEFAULT 1,
    `viewed_admin` tinyint NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_timezone`
--

DROP TABLE IF EXISTS `cc_timezone`;
CREATE TABLE `cc_timezone` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `gmtzone` varchar(255) DEFAULT NULL,
    `gmttime` varchar(255) DEFAULT NULL,
    `gmtoffset` bigint NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_trunk`
--

DROP TABLE IF EXISTS `cc_trunk`;
CREATE TABLE `cc_trunk` (
    `id_trunk` bigint NOT NULL AUTO_INCREMENT,
    `trunkcode` varchar(50) DEFAULT NULL,
    `trunkprefix` varchar(20) DEFAULT NULL,
    `providertech` varchar(20) NOT NULL,
    `providerip` varchar(80) NOT NULL,
    `removeprefix` varchar(20) DEFAULT NULL,
    `secondusedreal` int DEFAULT 0,
    `secondusedcarrier` int DEFAULT 0,
    `secondusedratecard` int DEFAULT 0,
    `creationdate` datetime NOT NULL DEFAULT current_timestamp(),
    `failover_trunk` int DEFAULT NULL,
    `addparameter` varchar(120) DEFAULT NULL,
    `id_provider` bigint DEFAULT NULL,
    `inuse` int DEFAULT 0,
    `maxuse` int DEFAULT -1,
    `status` int DEFAULT 1,
    `if_max_use` int DEFAULT 0,
    PRIMARY KEY (`id_trunk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_ui_authen`
--

DROP TABLE IF EXISTS `cc_ui_authen`;
CREATE TABLE `cc_ui_authen` (
    `userid` bigint NOT NULL AUTO_INCREMENT,
    `login` varchar(50) NOT NULL,
    `pwd_encoded` varchar(250) NOT NULL,
    `groupid` int DEFAULT NULL,
    `perms` int unsigned DEFAULT NULL,
    `confaddcust` int DEFAULT NULL,
    `name` varchar(50) DEFAULT NULL,
    `direction` varchar(80) DEFAULT NULL,
    `zipcode` varchar(20) DEFAULT NULL,
    `state` varchar(20) DEFAULT NULL,
    `phone` varchar(30) DEFAULT NULL,
    `fax` varchar(30) DEFAULT NULL,
    `datecreation` datetime NOT NULL DEFAULT current_timestamp(),
    `email` varchar(70) DEFAULT NULL,
    `country` varchar(40) DEFAULT NULL,
    `city` varchar(40) DEFAULT NULL,
    PRIMARY KEY (`userid`),
    UNIQUE KEY (`login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_version`
--

DROP TABLE IF EXISTS `cc_version`;
CREATE TABLE `cc_version` (
    `version` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cc_voucher`
--

DROP TABLE IF EXISTS `cc_voucher`;
CREATE TABLE `cc_voucher` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `creationdate` datetime NOT NULL DEFAULT current_timestamp(),
    `usedate` datetime DEFAULT NULL,
    `expirationdate` datetime DEFAULT NULL,
    `voucher` varchar(50) NOT NULL,
    `usedcardnumber` varchar(50) DEFAULT NULL,
    `tag` varchar(50) DEFAULT NULL,
    `credit` float NOT NULL DEFAULT 0,
    `activated` varchar(1) NOT NULL DEFAULT 'f',
    `used` int DEFAULT 0,
    `currency` varchar(3) DEFAULT 'USD',
    PRIMARY KEY (`id`),
    UNIQUE KEY (`voucher`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Final view structure for view `cc_callplan_lcr`
--

DROP VIEW IF EXISTS `cc_callplan_lcr`;
CREATE VIEW `cc_callplan_lcr` AS
    select `cc_ratecard`.`id` AS `id`,`cc_prefix`.`destination` AS `destination`,`cc_ratecard`.`dialprefix` AS `dialprefix`,`cc_ratecard`.`buyrate` AS `buyrate`,`cc_ratecard`.`rateinitial` AS `rateinitial`,`cc_ratecard`.`startdate` AS `startdate`,`cc_ratecard`.`stopdate` AS `stopdate`,`cc_ratecard`.`initblock` AS `initblock`,`cc_ratecard`.`connectcharge` AS `connectcharge`,`cc_ratecard`.`id_trunk` AS `id_trunk`,`cc_ratecard`.`idtariffplan` AS `idtariffplan`,`cc_ratecard`.`id` AS `ratecard_id`,`cc_tariffgroup`.`id` AS `tariffgroup_id` from ((((`cc_tariffgroup_plan` left join `cc_tariffgroup` on(`cc_tariffgroup_plan`.`idtariffgroup` = `cc_tariffgroup`.`id`)) join `cc_tariffplan` on(`cc_tariffplan`.`id` = `cc_tariffgroup_plan`.`idtariffplan`)) left join `cc_ratecard` on(`cc_ratecard`.`idtariffplan` = `cc_tariffplan`.`id`)) left join `cc_prefix` on(`cc_prefix`.`prefix` = `cc_ratecard`.`destination`)) where `cc_ratecard`.`id` is not null ;

--
-- Final view structure for view `cc_sip_buddies_empty`
--

DROP VIEW IF EXISTS `cc_sip_buddies_empty`;
CREATE VIEW `cc_sip_buddies_empty` AS
    select `cc_sip_buddies`.`id` AS `id`,`cc_sip_buddies`.`id_cc_card` AS `id_cc_card`,`cc_sip_buddies`.`name` AS `name`,`cc_sip_buddies`.`accountcode` AS `accountcode`,`cc_sip_buddies`.`regexten` AS `regexten`,`cc_sip_buddies`.`amaflags` AS `amaflags`,`cc_sip_buddies`.`callgroup` AS `callgroup`,`cc_sip_buddies`.`callerid` AS `callerid`,`cc_sip_buddies`.`canreinvite` AS `canreinvite`,`cc_sip_buddies`.`context` AS `context`,`cc_sip_buddies`.`DEFAULTip` AS `DEFAULTip`,`cc_sip_buddies`.`dtmfmode` AS `dtmfmode`,`cc_sip_buddies`.`fromuser` AS `fromuser`,`cc_sip_buddies`.`fromdomain` AS `fromdomain`,`cc_sip_buddies`.`host` AS `host`,`cc_sip_buddies`.`insecure` AS `insecure`,`cc_sip_buddies`.`language` AS `language`,`cc_sip_buddies`.`mailbox` AS `mailbox`,'' AS `md5secret`,`cc_sip_buddies`.`nat` AS `nat`,`cc_sip_buddies`.`deny` AS `deny`,`cc_sip_buddies`.`permit` AS `permit`,`cc_sip_buddies`.`mask` AS `mask`,`cc_sip_buddies`.`pickupgroup` AS `pickupgroup`,`cc_sip_buddies`.`port` AS `port`,`cc_sip_buddies`.`qualify` AS `qualify`,`cc_sip_buddies`.`restrictcid` AS `restrictcid`,`cc_sip_buddies`.`rtptimeout` AS `rtptimeout`,`cc_sip_buddies`.`rtpholdtimeout` AS `rtpholdtimeout`,'' AS `secret`,`cc_sip_buddies`.`type` AS `type`,`cc_sip_buddies`.`username` AS `username`,`cc_sip_buddies`.`disallow` AS `disallow`,`cc_sip_buddies`.`allow` AS `allow`,`cc_sip_buddies`.`musiconhold` AS `musiconhold`,`cc_sip_buddies`.`regseconds` AS `regseconds`,`cc_sip_buddies`.`ipaddr` AS `ipaddr`,`cc_sip_buddies`.`cancallforward` AS `cancallforward`,`cc_sip_buddies`.`fullcontact` AS `fullcontact`,`cc_sip_buddies`.`setvar` AS `setvar`,`cc_sip_buddies`.`regserver` AS `regserver`,`cc_sip_buddies`.`lastms` AS `lastms`,`cc_sip_buddies`.`defaultuser` AS `defaultuser`,`cc_sip_buddies`.`auth` AS `auth`,`cc_sip_buddies`.`subscribemwi` AS `subscribemwi`,`cc_sip_buddies`.`vmexten` AS `vmexten`,`cc_sip_buddies`.`cid_number` AS `cid_number`,`cc_sip_buddies`.`callingpres` AS `callingpres`,`cc_sip_buddies`.`usereqphone` AS `usereqphone`,`cc_sip_buddies`.`incominglimit` AS `incominglimit`,`cc_sip_buddies`.`subscribecontext` AS `subscribecontext`,`cc_sip_buddies`.`musicclass` AS `musicclass`,`cc_sip_buddies`.`mohsuggest` AS `mohsuggest`,`cc_sip_buddies`.`allowtransfer` AS `allowtransfer`,`cc_sip_buddies`.`autoframing` AS `autoframing`,`cc_sip_buddies`.`maxcallbitrate` AS `maxcallbitrate`,`cc_sip_buddies`.`outboundproxy` AS `outboundproxy`,`cc_sip_buddies`.`rtpkeepalive` AS `rtpkeepalive`,`cc_sip_buddies`.`useragent` AS `useragent`,`cc_sip_buddies`.`callbackextension` AS `callbackextension` from `cc_sip_buddies` ;

SET TIME_ZONE=@OLD_TIME_ZONE ;
SET SQL_MODE=@OLD_SQL_MODE ;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS ;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS ;
SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT ;
SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS ;
SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION ;
SET SQL_NOTES=@OLD_SQL_NOTES ;
