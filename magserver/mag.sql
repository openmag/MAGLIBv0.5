drop table if exists account_tbl;

create table account_tbl (
	vc_module    varchar(64) CHARACTER SET ascii,
	vc_account   varchar(32) CHARACTER SET ascii,
	dt_whencreated datetime,
	vc_pin      varchar(64) CHARACTER SET ascii,
	ti_lockpin  tinyint unsigned,
	bl_config   blob,
	primary key (vc_module, vc_account),
	index(vc_pin)
);

drop table if exists device_tbl;

create table device_tbl (
	vc_module    varchar(64) CHARACTER SET ascii,
	vc_account   varchar(32) CHARACTER SET ascii,
	vc_password  varchar(32) CHARACTER SET ascii,
	vc_pin       varchar(64) CHARACTER SET ascii,
	vc_device    varchar(16) CHARACTER SET ascii,
	vc_software  varchar(16),
	vc_platform  varchar(16),
	vc_mdsserver varchar(32) CHARACTER SET ascii,
	vc_pushproto varchar(8) CHARACTER SET ascii,
	vc_capacity  varchar(256) CHARACTER SET ascii,
	dt_create    datetime,
	dt_lastvisit datetime,
	primary key (vc_module, vc_account),
	index(vc_pin)
);

drop table if exists cache_tbl;

# alter table device_tbl add column vc_imsi varchar(32);
# alter table device_tbl add column vc_pushproto varchar(16) default 'MDS';
# alter table device_tbl add column vc_capacity  varchar(256);

create table cache_tbl (
	id          bigint unsigned auto_increment,
	vc_url      varchar(935) CHARACTER SET ascii,
	vc_module   varchar(64) CHARACTER SET ascii,
	vc_pin      varchar(64) CHARACTER SET ascii,
	vc_title    varchar(128) CHARACTER SET utf8,
	iu_expire   bigint unsigned,
	dt_expire   datetime,
	bl_output   MEDIUMBLOB,
	dt_change   datetime,
	dt_lastvisit datetime,
	iu_tries     int unsigned default 0,
	tiu_state    tinyint unsigned default 0,
	primary key(id),
	index(vc_url, vc_pin),
	index(vc_pin, vc_module)
);

# alter table cache_tbl add column vc_imsi varchar(32);
# alter table cache_tbl add column dt_lastvisit datetime;
# alter table cache_tbl add column tiu_state    tinyint unsigned default 0;
# alter table cache_tbl modify column bl_output MEDIUMBLOB;
# alter table cache_tbl modify column vc_url varchar(16384);
# alter table cache_tbl add column iu_tries int unsigned default 0;
# alter table cache_tbl add column vc_title    varchar(128) CHARACTER SET utf8;

# 0 INIT
# 1 PUSHSUCC
# 2 PUSHFAIL
# 3 CONFIRMED

drop table if exists pushlog_tbl;

create table pushlog_tbl (
	id         bigint unsigned auto_increment not null,
	cache_id   bigint unsigned not null,
	dt_push    datetime not null,
	itu_state  tinyint unsigned not null,
	vc_reason  varchar(16) CHARACTER SET ascii,
	bl_notify  MEDIUMBLOB,
	dt_notify  datetime,
	bl_pushcontent MEDIUMBLOB NOT NULL,
	primary key (id),
	index(cache_id)
);

# alter table pushlog_tbl add column bl_pushcontent blob not null default '';
# alter table pushlog_tbl modify column bl_notify MEDIUMBLOB;
# alter table pushlog_tbl modify column bl_pushcontent MEDIUMBLOB NOT NULL;
# alter table pushlog_tbl add column vc_reason varchar(16);

drop table if exists push_config_tbl;

create table push_config_tbl (
	id       int unsigned auto_increment,
	vc_mdsserver varchar(32) CHARACTER SET ascii,
	vc_protocol  varchar(8) CHARACTER SET ascii,
	iu_mdsport   int unsigned,
	itu_state    tinyint unsigned,
	iu_interval  int unsigned,
	dt_create    datetime,
	primary key (id)
);

drop table if exists local_var_tbl;

create table local_var_tbl (
	vc_module    varchar(64) CHARACTER SET ascii,
	vc_account   varchar(32) CHARACTER SET ascii,
	vc_varname   varchar(32) CHARACTER SET ascii,
	vc_value     varchar(255) CHARACTER SET ascii,
	primary key(vc_module, vc_account, vc_varname)
);

drop table if exists user_tbl;

# alter table push_config_tbl add column vc_protocol  varchar(16) default 'MDS';

create table user_tbl (
	id		int unsigned auto_increment,
	vc_user		varchar(32) CHARACTER SET ascii,
	vc_password	varchar(64) CHARACTER SET ascii,
	vc_roles	varchar(255) CHARACTER SET ascii,
	iu_group	int unsigned,
	vc_groups	varchar(255) CHARACTER SET ascii,
	vc_name		varchar(64),
	primary key (id),
	index (vc_user)
);

drop table if exists role_tbl;

create table role_tbl (
	vc_name	varchar(64),
	vc_desc blob,
	primary key (vc_name)
);

drop table if exists group_tbl;

create table group_tbl (
	id	int unsigned auto_increment,
	vc_name varchar(64) CHARACTER SET ascii,
	vc_parent varchar(64) CHARACTER SET ascii,
	vc_desc blob,
	primary key (id),
	index (vc_name),
	index (vc_name, vc_parent)
);

# SLOW POLL INTERVAL 30 minutes
# FAST POLL INTERVAL 15 minutes
# SLOW POLL THRESHOLD 1 DAY
# NO POLL THRESHOLD 60 MINUTES

# drop view if exists cache_update_view;
# create view cache_update_view as 
#  SELECT cache_tbl.id, cache_tbl.vc_url, 
#         cache_tbl.vc_module, cache_tbl.vc_pin, 
#         cache_tbl.bl_output, cache_tbl.iu_expire, 
#         cache_tbl.dt_change, 
#         IF(
#           DATE_ADD(cache_tbl.dt_change, INTERVAL %%SLOW_POLL_THRESHOLD%% DAY) > NOW(),
#           DATE_ADD(cache_tbl.dt_lastvisit, INTERVAL %%FAST_POLL_INTERVAL%% MINUTE), 
#           DATE_ADD(cache_tbl.dt_lastvisit, INTERVAL %%SLOW_POLL_INTERVAL%% MINUTE)
#         ) as dt_deadline, 
#         device_tbl.vc_account, device_tbl.vc_password, 
#         device_tbl.vc_device, device_tbl.vc_software, 
#         device_tbl.vc_platform 
#  FROM cache_tbl LEFT JOIN device_tbl on 
#       (cache_tbl.vc_pin=device_tbl.vc_pin) AND 
#       cache_tbl.vc_module=device_tbl.vc_module 
#  WHERE cache_tbl.tiu_state=0 
#        OR DATE_ADD(cache_tbl.dt_lastvisit, INTERVAL %%DEAD_POLL_THRESHOLD%% MINUTE) < NOW();

# SYNC_START 0
# SYNC_SUCC  1
# SYNC_FAIL  2

drop table if exists syncdb_tbl;
create table syncdb_tbl (
	id   INT UNSIGNED AUTO_INCREMENT,
	vc_name VARCHAR(64) CHARACTER SET ascii NOT NULL,
	vc_dataurl VARCHAR(255) CHARACTER SET ascii NOT NULL,
	vc_timestamp VARCHAR(40) CHARACTER SET ascii NOT NULL DEFAULT '',
	dt_firstsync DATETIME,
	ui_updateintv INT UNSIGNED NOT NULL,
	bl_description BLOB,
	dt_lastsync DATETIME,
	ti_status TINYINT UNSIGNED,
	ti_enable TINYINT UNSIGNED,
	ti_isdirty TINYINT UNSIGNED,
	PRIMARY KEY (id),
	INDEX(vc_name)
);

# alter table syncdb_tbl add column vc_timestamp DATETIME;
# alter table syncdb_tbl modify column vc_timestamp VARCHAR(40) NOT NULL DEFAULT '';
# alter table syncdb_tbl add column dt_firstsync DATETIME;
# alter table syncdb_tbl add column ti_isdirty TINYINT UNSIGNED;

drop table if exists syncdb_column_tbl;
create table syncdb_column_tbl (
	tbl_id INT UNSIGNED,
	vc_name VARCHAR(64) CHARACTER SET ascii NOT NULL,
	vc_datatype VARCHAR(128) CHARACTER SET ascii NOT NULL,
	vc_dtparam  VARCHAR(32) CHARACTER SET ascii NOT NULL,
	ti_isnull TINYINT UNSIGNED,
	ti_isprimary TINYINT UNSIGNED,
	dt_whenadd DATETIME,
	PRIMARY KEY (tbl_id, vc_name)
);

drop table if exists syncdb_index_tbl;
create table syncdb_index_tbl (
	id INT      UNSIGNED AUTO_INCREMENT,
	tbl_id      INT UNSIGNED,
	vc_colnames VARCHAR(256) CHARACTER SET ascii,
	dt_whenadd  DATETIME,
	PRIMARY KEY (id)
);

drop view if exists syncdb_view;
create view syncdb_view as select id, vc_name, NOW() AS current, 
IF(dt_lastsync IS NULL, 
   IF(dt_firstsync < NOW(),
      NOW(),
      dt_firstsync
   ),
   IF(ti_status=0, 
      IF(dt_firstsync > DATE_ADD(dt_lastsync, INTERVAL 2 HOUR),
         dt_firstsync,
         DATE_ADD(dt_lastsync, INTERVAL 2 HOUR)
      ),
      IF(ti_status=1,
         IF(dt_firstsync > DATE_ADD(dt_lastsync, INTERVAL ui_updateintv SECOND),
            dt_firstsync,
            IF(dt_firstsync > dt_lastsync,
               dt_firstsync,
               DATE_ADD(dt_lastsync, INTERVAL ui_updateintv SECOND)
            )
         ),
         IF(ti_status=2,
            IF(dt_firstsync > DATE_ADD(dt_lastsync, INTERVAL 2*ui_updateintv SECOND),
               dt_firstsync,
               IF(dt_firstsync > dt_lastsync,
                  dt_firstsync,
                  DATE_ADD(dt_lastsync, INTERVAL 2*ui_updateintv SECOND)
               )
            ),
            IF(dt_firstsync > DATE_ADD(dt_lastsync, INTERVAL 5 MINUTE),
               dt_firstsync,
               DATE_ADD(dt_lastsync, INTERVAL 5 MINUTE)
            )
         )
      )
   )
) AS next_sync FROM syncdb_tbl WHERE ti_enable=1;


drop view if exists account_count_view;

create view account_count_view AS
select vc_module, count(*) AS account_count from account_tbl where vc_module!='*' AND vc_account!='*' group by vc_module UNION select '*' AS vc_module, count(*) from account_tbl where vc_module!='*' AND vc_account!='*';

drop view if exists device_count_view;
create view device_count_view AS
select vc_module, count(*) AS device_count from device_tbl group by vc_module UNION select '*' AS vc_module, count(*) from device_tbl;
