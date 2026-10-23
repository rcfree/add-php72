
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- address
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `address`;

CREATE TABLE `address`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `address_line_1` VARCHAR(50),
    `address_line_2` VARCHAR(50),
    `address_line_3` VARCHAR(50),
    `address_line_4` VARCHAR(50),
    `postcode` VARCHAR(8),
    `home_phone` VARCHAR(30),
    `work_phone` VARCHAR(30),
    `patient_id` INTEGER,
    `date_created` DATETIME,
    `date_updated` DATETIME,
    PRIMARY KEY (`id`),
    INDEX `address_FI_1` (`patient_id`),
    CONSTRAINT `address_FK_1`
        FOREIGN KEY (`patient_id`)
        REFERENCES `patient` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET='utf8';

-- ---------------------------------------------------------------------
-- patient
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `patient`;

CREATE TABLE `patient`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `identifier` VARCHAR(10) NOT NULL,
    `title` VARCHAR(12),
    `first_name` VARCHAR(50) NOT NULL,
    `middle_name` VARCHAR(50),
    `last_name` VARCHAR(50) NOT NULL,
    `date_of_birth` DATE NOT NULL,
    `hospital_no` VARCHAR(10),
    `nhs_no` VARCHAR(12),
    `born_in_uk` VARCHAR(1),
    `country_of_birth` VARCHAR(2),
    `gender` VARCHAR(1),
    `marital_status` VARCHAR(1),
    `ethnic_group` VARCHAR(1),
    `deceased` TINYINT(1),
    `date_of_death` DATE,
    `address_id` INTEGER,
    `gp_id` INTEGER,
    `is_deleted` TINYINT(1) DEFAULT 0,
    `date_created` DATETIME,
    `date_updated` DATETIME,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `patient_U_1` (`identifier`),
    INDEX `patient_I_1` (`first_name`, `last_name`, `date_of_birth`),
    INDEX `patient_FI_1` (`address_id`),
    INDEX `patient_FI_2` (`gp_id`),
    CONSTRAINT `patient_FK_1`
        FOREIGN KEY (`address_id`)
        REFERENCES `address` (`id`)
        ON DELETE CASCADE,
    CONSTRAINT `patient_FK_2`
        FOREIGN KEY (`gp_id`)
        REFERENCES `gp` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET='utf8';

-- ---------------------------------------------------------------------
-- gp
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `gp`;

CREATE TABLE `gp`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `local_code` VARCHAR(5),
    `doh_code` VARCHAR(8),
    `practice_code` VARCHAR(6),
    `gp_title` VARCHAR(10),
    `gp_forename` VARCHAR(50),
    `gp_surname` VARCHAR(50),
    `gp_address1` VARCHAR(50),
    `gp_address2` VARCHAR(50),
    `gp_address3` VARCHAR(50),
    `gp_postcode` VARCHAR(8),
    `gp_workphone` VARCHAR(30),
    `date_created` DATETIME,
    `date_updated` DATETIME,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB CHARACTER SET='utf8';

-- ---------------------------------------------------------------------
-- encounter
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `encounter`;

CREATE TABLE `encounter`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `encounter_form_id` INTEGER,
    `patient_id` INTEGER NOT NULL,
    `parent_encounter_id` INTEGER,
    `identifier` VARCHAR(20) DEFAULT NULL,
    `is_complete` TINYINT(0) DEFAULT 0,
    `is_locked` TINYINT DEFAULT 0,
    `is_deleted` TINYINT DEFAULT 0,
    `is_invalid` TINYINT DEFAULT 0,
    `is_autosave` TINYINT DEFAULT 0,
    `is_verified` TINYINT DEFAULT 0,
    `revision_no` INTEGER NOT NULL,
    `date_created` DATETIME,
    `date_updated` DATETIME,
    PRIMARY KEY (`id`),
    INDEX `encounter_FI_1` (`patient_id`),
    INDEX `encounter_FI_2` (`parent_encounter_id`),
    INDEX `encounter_FI_3` (`encounter_form_id`),
    CONSTRAINT `encounter_FK_1`
        FOREIGN KEY (`patient_id`)
        REFERENCES `patient` (`id`),
    CONSTRAINT `encounter_FK_2`
        FOREIGN KEY (`parent_encounter_id`)
        REFERENCES `encounter` (`id`),
    CONSTRAINT `encounter_FK_3`
        FOREIGN KEY (`encounter_form_id`)
        REFERENCES `encounter_form` (`id`)
) ENGINE=InnoDB CHARACTER SET='utf8';

-- ---------------------------------------------------------------------
-- observation
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `observation`;

CREATE TABLE `observation`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `observation_field_id` INTEGER,
    `observation_note_id` INTEGER,
    `value` VARCHAR(255) NOT NULL,
    `encounter_id` INTEGER NOT NULL,
    `dictionary_item_id` INTEGER,
    `is_serialized` TINYINT DEFAULT 0,
    `is_deleted` TINYINT DEFAULT 0,
    `is_note` TINYINT DEFAULT 0,
    `date_created` DATETIME,
    `date_updated` DATETIME,
    PRIMARY KEY (`id`),
    INDEX `observation_FI_1` (`encounter_id`),
    INDEX `observation_FI_2` (`observation_field_id`),
    INDEX `observation_FI_3` (`observation_note_id`),
    INDEX `observation_FI_4` (`dictionary_item_id`),
    CONSTRAINT `observation_FK_1`
        FOREIGN KEY (`encounter_id`)
        REFERENCES `encounter` (`id`),
    CONSTRAINT `observation_FK_2`
        FOREIGN KEY (`observation_field_id`)
        REFERENCES `observation_field` (`id`),
    CONSTRAINT `observation_FK_3`
        FOREIGN KEY (`observation_note_id`)
        REFERENCES `observation_note` (`id`),
    CONSTRAINT `observation_FK_4`
        FOREIGN KEY (`dictionary_item_id`)
        REFERENCES `dictionary_item` (`id`)
) ENGINE=InnoDB CHARACTER SET='utf8';

-- ---------------------------------------------------------------------
-- observation_note
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `observation_note`;

CREATE TABLE `observation_note`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `observation_id` INTEGER,
    `text` TEXT NOT NULL,
    `date_created` DATETIME,
    `date_updated` DATETIME,
    PRIMARY KEY (`id`),
    INDEX `observation_note_FI_1` (`observation_id`),
    CONSTRAINT `observation_note_FK_1`
        FOREIGN KEY (`observation_id`)
        REFERENCES `observation` (`id`)
) ENGINE=InnoDB CHARACTER SET='utf8';

-- ---------------------------------------------------------------------
-- dictionary
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `dictionary`;

CREATE TABLE `dictionary`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(50) NOT NULL,
    `title` VARCHAR(80),
    `ontology` VARCHAR(20),
    `is_deleted` TINYINT DEFAULT 0,
    `file_hash` VARCHAR(32),
    `date_created` DATETIME,
    `date_updated` DATETIME,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `dictionary_U_1` (`name`)
) ENGINE=InnoDB CHARACTER SET='utf8';

-- ---------------------------------------------------------------------
-- dictionary_group
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `dictionary_group`;

CREATE TABLE `dictionary_group`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `dictionary_id` INTEGER NOT NULL,
    `name` VARCHAR(30) NOT NULL,
    `title` VARCHAR(128),
    `date_created` DATETIME,
    `date_updated` DATETIME,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `dictionary_group_U_1` (`name`, `dictionary_id`),
    INDEX `dictionary_group_FI_1` (`dictionary_id`),
    CONSTRAINT `dictionary_group_FK_1`
        FOREIGN KEY (`dictionary_id`)
        REFERENCES `dictionary` (`id`)
) ENGINE=InnoDB CHARACTER SET='utf8';

-- ---------------------------------------------------------------------
-- dictionary_item
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `dictionary_item`;

CREATE TABLE `dictionary_item`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `dictionary_id` INTEGER NOT NULL,
    `dictionary_group_id` INTEGER,
    `value` VARCHAR(20) COLLATE 'utf8_bin' NOT NULL,
    `title` VARCHAR(128),
    `concept` VARCHAR(20) COLLATE 'utf8_bin',
    `data` TEXT,
    `parent_item_id` INTEGER,
    `is_disabled` TINYINT DEFAULT 0,
    `is_deleted` TINYINT DEFAULT 0,
    `date_created` DATETIME,
    `date_updated` DATETIME,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `dictionary_item_U_1` (`value`, `dictionary_id`),
    INDEX `dictionary_item_I_1` (`title`),
    INDEX `dictionary_item_FI_1` (`dictionary_id`),
    INDEX `dictionary_item_FI_2` (`dictionary_group_id`),
    CONSTRAINT `dictionary_item_FK_1`
        FOREIGN KEY (`dictionary_id`)
        REFERENCES `dictionary` (`id`),
    CONSTRAINT `dictionary_item_FK_2`
        FOREIGN KEY (`dictionary_group_id`)
        REFERENCES `dictionary_group` (`id`)
        ON DELETE SET NULL
) ENGINE=InnoDB CHARACTER SET='utf8';

-- ---------------------------------------------------------------------
-- identifier
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `identifier`;

CREATE TABLE `identifier`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(20) NOT NULL,
    `value` INTEGER NOT NULL,
    `date_created` DATETIME,
    `date_updated` DATETIME,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB CHARACTER SET='utf8';

-- ---------------------------------------------------------------------
-- audit
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `audit`;

CREATE TABLE `audit`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `record_id` INTEGER,
    `record_type` VARCHAR(20),
    `record_name` VARCHAR(50),
    `action` VARCHAR(10),
    `action_info` TEXT,
    `revision_no` INTEGER,
    `user_id` VARCHAR(20) NOT NULL,
    `timestamp` DATETIME,
    `updated_at` DATETIME,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB CHARACTER SET='utf8';

-- ---------------------------------------------------------------------
-- observation_field_audit
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `observation_field_audit`;

CREATE TABLE `observation_field_audit`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `observation_field_id` INTEGER NOT NULL,
    `audit_id` INTEGER NOT NULL,
    `change_type` VARCHAR(15),
    `change_data` TEXT,
    PRIMARY KEY (`id`),
    INDEX `observation_field_audit_FI_1` (`observation_field_id`),
    INDEX `observation_field_audit_FI_2` (`audit_id`),
    CONSTRAINT `observation_field_audit_FK_1`
        FOREIGN KEY (`observation_field_id`)
        REFERENCES `observation_field` (`id`),
    CONSTRAINT `observation_field_audit_FK_2`
        FOREIGN KEY (`audit_id`)
        REFERENCES `audit` (`id`)
) ENGINE=InnoDB CHARACTER SET='utf8';

-- ---------------------------------------------------------------------
-- observation_audit
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `observation_audit`;

CREATE TABLE `observation_audit`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `observation_id` INTEGER NOT NULL,
    `audit_id` INTEGER NOT NULL,
    `change_type` VARCHAR(15),
    `value` TEXT,
    `is_serialized` TINYINT DEFAULT 0,
    PRIMARY KEY (`id`),
    INDEX `observation_audit_FI_1` (`observation_id`),
    INDEX `observation_audit_FI_2` (`audit_id`),
    CONSTRAINT `observation_audit_FK_1`
        FOREIGN KEY (`observation_id`)
        REFERENCES `observation` (`id`),
    CONSTRAINT `observation_audit_FK_2`
        FOREIGN KEY (`audit_id`)
        REFERENCES `audit` (`id`)
) ENGINE=InnoDB CHARACTER SET='utf8';

-- ---------------------------------------------------------------------
-- encounter_form
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `encounter_form`;

CREATE TABLE `encounter_form`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `type` VARCHAR(128) NOT NULL,
    `revision_no` INTEGER,
    `date_created` DATETIME,
    `date_updated` DATETIME,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `encounter_form_U_1` (`type`)
) ENGINE=InnoDB CHARACTER SET='utf8';

-- ---------------------------------------------------------------------
-- observation_field
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `observation_field`;

CREATE TABLE `observation_field` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `encounter_form_id` int(11) DEFAULT NULL,
  `dictionary_id` int(11) DEFAULT NULL,
  `level` int(11) NOT NULL,
  `weight` int(11) NOT NULL,
  `type` varchar(30) NOT NULL,
  `name` varchar(50) NOT NULL,
  `title` text,
  `default_value` varchar(100) DEFAULT '',
  `options` text,
  `settings` text,
  `concepts` text,
  `is_deleted` tinyint(4) DEFAULT '0',
  `date_created` datetime DEFAULT NULL,
  `date_updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `observation_field_U_1` (`encounter_form_id`,`name`),
  KEY `observation_field_FI_2` (`dictionary_id`),
  CONSTRAINT `observation_field_FK_1` FOREIGN KEY (`encounter_form_id`) REFERENCES `encounter_form` (`id`),
  CONSTRAINT `observation_field_FK_2` FOREIGN KEY (`dictionary_id`) REFERENCES `dictionary` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ---------------------------------------------------------------------
-- submit_token
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `submit_token`;

CREATE TABLE `submit_token`
(
    `uuid` VARCHAR(255) NOT NULL,
    `timestamp` INTEGER,
    PRIMARY KEY (`uuid`)
) ENGINE=InnoDB CHARACTER SET='utf8';

-- ---------------------------------------------------------------------
-- search
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `search`;

CREATE TABLE `search`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `description` VARCHAR(100),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB CHARACTER SET='utf8';

-- ---------------------------------------------------------------------
-- search_item
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `search_item`;

CREATE TABLE `search_item`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `search_id` INTEGER NOT NULL,
    `module` VARCHAR(20),
    `section` VARCHAR(20),
    `page` VARCHAR(20),
    `field` VARCHAR(50),
    `filter` TINYINT,
    `comparison` VARCHAR(10),
    `comparison_text` TEXT,
    `value1` VARCHAR(100),
    `value2` VARCHAR(100),
    `date_created` DATETIME,
    `date_updated` DATETIME,
    PRIMARY KEY (`id`),
    INDEX `search_item_FI_1` (`search_id`),
    CONSTRAINT `search_item_FK_1`
        FOREIGN KEY (`search_id`)
        REFERENCES `search` (`id`)
) ENGINE=InnoDB CHARACTER SET='utf8';

DROP TABLE IF EXISTS `version`;

CREATE TABLE `version` (
  `version` varchar(8) NOT NULL DEFAULT '',
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `version` values('2.0.0');

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;

