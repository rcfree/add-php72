CREATE TABLE `episode_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `episode` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `episode_type_id` int(11) NOT NULL,
  `is_deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `episode_FI_1` (`episode_type_id`,`patient_id`),
  KEY `episode_FI_2` (`patient_id`),
  CONSTRAINT `episode_FK_1` FOREIGN KEY (`patient_id`) REFERENCES `patient` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `episode_FK_2` FOREIGN KEY (`episode_type_id`) REFERENCES `episode_type` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `section` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `section_type_id` int(11) unsigned DEFAULT NULL,
  `patient_id` int(11) DEFAULT NULL,
  `label` varchar(100) DEFAULT NULL,
  `number` int(11) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_updated` datetime DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT '0',
  `episode_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `section_FI_1` (`section_type_id`,`patient_id`),
  KEY `section_FI_3` (`episode_id`),
  CONSTRAINT `section_FK_1` FOREIGN KEY (`patient_id`) REFERENCES `patient` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `section_FK_2` FOREIGN KEY (`section_type_id`) REFERENCES `section_type` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `section_FK_3` FOREIGN KEY (`episode_id`) REFERENCES `episode` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=586469 DEFAULT CHARSET=latin1;
