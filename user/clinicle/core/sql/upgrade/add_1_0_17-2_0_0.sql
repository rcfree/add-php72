alter TABLE `gp` modify column
  `gp_workphone` varchar(30) DEFAULT NULL;

alter TABLE `dictionary_group` modify column
  `title` varchar(255) DEFAULT NULL,
add column `is_disabled` tinyint(0) default 0;

alter table `audit` modify column `record_id` int(11) default null,
modify column `record_name` VARCHAR(50) DEFAULT NULL,
drop `updated_at`;
alter table `observation_field` modify column `default_value` varchar(100) default null;
alter table `encounter` modify column `identifier` VARCHAR(20) DEFAULT NULL,
    modify column `is_complete` TINYINT(0) DEFAULT 0;

CREATE TABLE `version` (
  `version` varchar(8) NOT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
insert into `version` VALUES('2.0.0');

CREATE TABLE if not exists `ilh_existing_id`  (
  `id` varchar(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

