alter table `encounter`
change `identifier` `badge` varchar(20) default null;

CREATE TABLE `user_record` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL DEFAULT '',
  `record_id` int(11) DEFAULT NULL,
  `record_type` varchar(20) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `logged_in` timestamp NULL DEFAULT NULL,
  `logged_out` timestamp NULL DEFAULT NULL,
  `session_id` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_id` (`session_id`)
) ENGINE=InnoDB AUTO_INCREMENT=96 DEFAULT CHARSET=utf8;

