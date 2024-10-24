/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of A2Billing (http://www.a2billing.net/)
 *
 * A2Billing, Commercial Open Source Telecom Billing platform,   
 * powered by Star2billing S.L. <http://www.star2billing.com/>
 * 
 * @copyright   Copyright (C) 2004-2012 - Star2billing S.L. 
 * @author      Hironobu Suzuki <hironobu@interdb.jp> / Belaid Arezqui <areski@gmail.com>
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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * 
**/

--
-- A2Billing database script - Update database for Postgres
-- 
--

\set ON_ERROR_STOP ON;

-- Wrap the whole update in a transaction so everything is reverted upon failure
BEGIN;


CREATE INDEX idtariffplan_index ON cc_ratecard (idtariffplan);

UPDATE cc_config SET config_title='DID Billing Days to pay', config_description='Define the amount of days you want to give to the user before releasing its DIDs' WHERE config_key='didbilling_daytopay ';


-- Add new field for VT provisioning
ALTER TABLE cc_card_group ADD COLUMN provisioning VARCHAR(200) NULL;


-- New setting for Base_country and Base_language
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_listvalues, config_group_title) VALUES('Base Country', 'base_country', 'USA', 'Define the country code in 3 letters where you are located (ISO 3166-1 : "USA" for United States)', 0, '', 'global');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_listvalues, config_group_title) VALUES('Base Language', 'base_language', 'en', 'Define your language code in 2 letters (ISO 639 : "en" for English)', 0, '', 'global');



-- Change length of field for provisioning system
ALTER TABLE cc_card_group ALTER COLUMN name TYPE varchar(50);
ALTER TABLE cc_trunk ALTER COLUMN trunkcode TYPE varchar(50);


-- change length on Notification
ALTER TABLE cc_notification ALTER COLUMN key_value TYPE VARCHAR(255);


-- IAX Friends update

CREATE INDEX iax_friend_nh_index ON cc_iax_buddies (name, host);
CREATE INDEX iax_friend_nip_index ON cc_iax_buddies (name, ipaddr, port);
CREATE INDEX iax_friend_ip_index ON cc_iax_buddies (ipaddr, port);
CREATE INDEX iax_friend_hp_index ON cc_iax_buddies (host, port);


ALTER TABLE cc_iax_buddies DROP COLUMN callgroup;
ALTER TABLE cc_iax_buddies DROP COLUMN canreinvite;
ALTER TABLE cc_iax_buddies DROP COLUMN dtmfmode;
ALTER TABLE cc_iax_buddies DROP COLUMN fromuser;
ALTER TABLE cc_iax_buddies DROP COLUMN fromdomain;
ALTER TABLE cc_iax_buddies DROP COLUMN insecure;
ALTER TABLE cc_iax_buddies DROP COLUMN mailbox;
ALTER TABLE cc_iax_buddies DROP COLUMN md5secret;
ALTER TABLE cc_iax_buddies DROP COLUMN nat;
ALTER TABLE cc_iax_buddies DROP COLUMN pickupgroup;
ALTER TABLE cc_iax_buddies DROP COLUMN restrictcid;
ALTER TABLE cc_iax_buddies DROP COLUMN rtptimeout;
ALTER TABLE cc_iax_buddies DROP COLUMN rtpholdtimeout;
ALTER TABLE cc_iax_buddies DROP COLUMN musiconhold;
ALTER TABLE cc_iax_buddies DROP COLUMN cancallforward;

ALTER TABLE cc_iax_buddies ADD COLUMN dbsecret varchar(40) NOT NULL default '';
ALTER TABLE cc_iax_buddies ADD COLUMN regcontext varchar(40) NOT NULL default '';
ALTER TABLE cc_iax_buddies ADD COLUMN sourceaddress varchar(20) NOT NULL default '';
ALTER TABLE cc_iax_buddies ADD COLUMN mohinterpret varchar(20) NOT NULL default '';
ALTER TABLE cc_iax_buddies ADD COLUMN mohsuggest varchar(20) NOT NULL default '';
ALTER TABLE cc_iax_buddies ADD COLUMN inkeys varchar(40) NOT NULL default '';
ALTER TABLE cc_iax_buddies ADD COLUMN outkey varchar(40) NOT NULL default ''; 
ALTER TABLE cc_iax_buddies ADD COLUMN cid_number varchar(40) NOT NULL default '';
ALTER TABLE cc_iax_buddies ADD COLUMN sendani varchar(10) NOT NULL default '';
ALTER TABLE cc_iax_buddies ADD COLUMN fullname varchar(40) NOT NULL default ''; 
ALTER TABLE cc_iax_buddies ADD COLUMN auth varchar(20) NOT NULL default '';
ALTER TABLE cc_iax_buddies ADD COLUMN maxauthreq varchar(15) NOT NULL default ''; 
ALTER TABLE cc_iax_buddies ADD COLUMN encryption varchar(20) NOT NULL default ''; 
ALTER TABLE cc_iax_buddies ADD COLUMN transfer varchar(10) NOT NULL default '';
ALTER TABLE cc_iax_buddies ADD COLUMN jitterbuffer varchar(10) NOT NULL default ''; 
ALTER TABLE cc_iax_buddies ADD COLUMN forcejitterbuffer varchar(10) NOT NULL default ''; 
ALTER TABLE cc_iax_buddies ADD COLUMN codecpriority varchar(40) NOT NULL default '';
ALTER TABLE cc_iax_buddies ADD COLUMN qualifysmoothing varchar(10) NOT NULL default ''; 
ALTER TABLE cc_iax_buddies ADD COLUMN qualifyfreqok varchar(10) NOT NULL default '';
ALTER TABLE cc_iax_buddies ADD COLUMN qualifyfreqnotok varchar(10) NOT NULL default ''; 
ALTER TABLE cc_iax_buddies ADD COLUMN timezone varchar(20) NOT NULL default '';
ALTER TABLE cc_iax_buddies ADD COLUMN adsi varchar(10) NOT NULL default '';
ALTER TABLE cc_iax_buddies ADD COLUMN setvar varchar(200) NOT NULL default '';

-- Add IAX security settings / not support by realtime
ALTER TABLE cc_iax_buddies ADD COLUMN requirecalltoken varchar(20) NOT NULL default '';
ALTER TABLE cc_iax_buddies ADD COLUMN maxcallnumbers varchar(10) NOT NULL default '';
ALTER TABLE cc_iax_buddies ADD COLUMN maxcallnumbers_nonvalidated varchar(10) NOT NULL default '';


-- SIP Friends update

CREATE INDEX sip_friend_hp_index ON cc_sip_buddies (host, port);
CREATE INDEX sip_friend_ip_index ON cc_sip_buddies (ipaddr, port);

ALTER TABLE cc_sip_buddies	ADD COLUMN defaultuser varchar(40) NOT NULL default '';
ALTER TABLE cc_sip_buddies	ADD COLUMN auth varchar(10) NOT NULL default '';
ALTER TABLE cc_sip_buddies	ADD COLUMN subscribemwi varchar(10) NOT NULL default ''; -- yes/no
ALTER TABLE cc_sip_buddies	ADD COLUMN vmexten varchar(20) NOT NULL default '';
ALTER TABLE cc_sip_buddies	ADD COLUMN cid_number varchar(40) NOT NULL default '';
ALTER TABLE cc_sip_buddies	ADD COLUMN callingpres varchar(20) NOT NULL default '';
ALTER TABLE cc_sip_buddies	ADD COLUMN usereqphone varchar(10) NOT NULL default '';
ALTER TABLE cc_sip_buddies	ADD COLUMN incominglimit varchar(10) NOT NULL default '';
ALTER TABLE cc_sip_buddies	ADD COLUMN subscribecontext varchar(40) NOT NULL default '';
ALTER TABLE cc_sip_buddies	ADD COLUMN musicclass varchar(20) NOT NULL default '';
ALTER TABLE cc_sip_buddies	ADD COLUMN mohsuggest varchar(20) NOT NULL default '';
ALTER TABLE cc_sip_buddies	ADD COLUMN allowtransfer varchar(20) NOT NULL default '';
ALTER TABLE cc_sip_buddies	ADD COLUMN autoframing varchar(10) NOT NULL default ''; -- yes/no
ALTER TABLE cc_sip_buddies	ADD COLUMN maxcallbitrate varchar(15) NOT NULL default '';
ALTER TABLE cc_sip_buddies	ADD COLUMN outboundproxy varchar(40) NOT NULL default '';
--  ADD regserver varchar(20) NOT NULL default '',
ALTER TABLE cc_sip_buddies	ADD COLUMN rtpkeepalive varchar(15) NOT NULL default '';



-- ADD A2Billing Version into the Database 
CREATE TABLE cc_version (
    version varchar(30) NOT NULL
);

INSERT INTO cc_version (version) VALUES ('1.4.3');

UPDATE cc_version SET version = '1.4.3';


-- Commit the whole update;  psql will automatically rollback if we failed at any point
COMMIT;
