CREATE TABLE `temp_data` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `token` varchar(32) DEFAULT NULL,
  `data` text,
  `encounter_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2202 DEFAULT CHARSET=latin1;
alter TABLE `add_users`.`users` add column `apikey` varchar(32) default null;
alter table `observation` add column `value` varchar(255) DEFAULT '';
update `version` set `version`='2.0.5';