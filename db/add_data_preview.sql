-- MySQL dump 10.15  Distrib 10.0.38-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: add_data_preview
-- ------------------------------------------------------
-- Server version	10.0.38-MariaDB-0ubuntu0.16.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `address`
--

DROP TABLE IF EXISTS `address`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `address` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `address_line_1` varchar(50) DEFAULT NULL,
  `address_line_2` varchar(50) DEFAULT NULL,
  `address_line_3` varchar(50) DEFAULT NULL,
  `address_line_4` varchar(50) DEFAULT NULL,
  `postcode` varchar(10) DEFAULT NULL,
  `home_phone` varchar(30) DEFAULT NULL,
  `work_phone` varchar(30) DEFAULT NULL,
  `patient_id` int(11) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `address_FI_1` (`patient_id`),
  CONSTRAINT `address_FK_1` FOREIGN KEY (`patient_id`) REFERENCES `patient` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=63982 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `audit`
--

DROP TABLE IF EXISTS `audit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `record_id` int(11) DEFAULT NULL,
  `record_type` varchar(20) DEFAULT NULL,
  `record_name` varchar(50) DEFAULT NULL,
  `action` varchar(10) DEFAULT NULL,
  `action_info` text,
  `revision_no` int(11) DEFAULT NULL,
  `user_id` varchar(20) NOT NULL,
  `timestamp` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `consent`
--

DROP TABLE IF EXISTS `consent`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `consent` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `consent_date` date DEFAULT NULL,
  `consent_version` smallint(6) DEFAULT NULL,
  `patient_filled_in` char(1) DEFAULT NULL,
  `consent_given` char(1) DEFAULT NULL,
  `reason_not_given` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `consent_FI_1` (`patient_id`),
  CONSTRAINT `consent_FK_1` FOREIGN KEY (`patient_id`) REFERENCES `patient` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dictionary`
--

DROP TABLE IF EXISTS `dictionary`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dictionary` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `title` varchar(80) DEFAULT NULL,
  `ontology` varchar(20) DEFAULT NULL,
  `is_deleted` tinyint(4) DEFAULT '0',
  `file_hash` varchar(32) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dictionary_U_1` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=250 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dictionary_group`
--

DROP TABLE IF EXISTS `dictionary_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dictionary_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dictionary_id` int(11) NOT NULL,
  `name` varchar(30) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_updated` datetime DEFAULT NULL,
  `is_disabled` tinyint(4) DEFAULT '0',
  `weight` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dictionary_group_U_1` (`name`,`dictionary_id`),
  KEY `dictionary_group_FI_1` (`dictionary_id`),
  CONSTRAINT `dictionary_group_FK_1` FOREIGN KEY (`dictionary_id`) REFERENCES `dictionary` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2157 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dictionary_item`
--

DROP TABLE IF EXISTS `dictionary_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dictionary_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dictionary_id` int(11) NOT NULL,
  `dictionary_group_id` int(11) DEFAULT NULL,
  `value` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `title` varchar(128) DEFAULT NULL,
  `synonyms` text,
  `concept` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `data` text,
  `parent_item_id` int(11) DEFAULT NULL,
  `is_disabled` tinyint(4) DEFAULT '0',
  `is_deleted` tinyint(4) DEFAULT '0',
  `date_created` datetime DEFAULT NULL,
  `date_updated` datetime DEFAULT NULL,
  `weight` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dictionary_item_U_1` (`value`,`dictionary_id`),
  KEY `dictionary_item_I_1` (`title`),
  KEY `dictionary_item_FI_1` (`dictionary_id`),
  KEY `dictionary_item_FI_2` (`dictionary_group_id`),
  KEY `dictionary_item_FI_3` (`synonyms`(50)),
  CONSTRAINT `dictionary_item_FK_1` FOREIGN KEY (`dictionary_id`) REFERENCES `dictionary` (`id`),
  CONSTRAINT `dictionary_item_FK_2` FOREIGN KEY (`dictionary_group_id`) REFERENCES `dictionary_group` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=117687 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `domain`
--

DROP TABLE IF EXISTS `domain`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `domain` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) DEFAULT NULL,
  `domain_type_id` int(11) DEFAULT NULL,
  `is_deleted` int(11) DEFAULT '0',
  `date_created` datetime DEFAULT NULL,
  `date_updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `domain_info` (`patient_id`,`domain_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=683506 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `domain_type`
--

DROP TABLE IF EXISTS `domain_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `domain_type` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `encounter`
--

DROP TABLE IF EXISTS `encounter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `encounter` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `encounter_form_id` int(11) DEFAULT NULL,
  `patient_id` int(11) NOT NULL,
  `parent_encounter_id` int(11) DEFAULT NULL,
  `badge` varchar(20) DEFAULT NULL,
  `is_complete` tinyint(4) DEFAULT '0',
  `is_locked` tinyint(4) DEFAULT '0',
  `is_deleted` tinyint(4) DEFAULT '0',
  `is_invalid` tinyint(4) DEFAULT '0',
  `is_autosave` tinyint(4) DEFAULT '0',
  `is_verified` tinyint(4) DEFAULT '0',
  `revision_no` int(11) NOT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_updated` datetime DEFAULT NULL,
  `data_hash` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `encounter_FI_1` (`patient_id`),
  KEY `encounter_FI_2` (`parent_encounter_id`),
  KEY `encounter_FI_3` (`encounter_form_id`),
  KEY `encounter_FI_4` (`data_hash`),
  CONSTRAINT `encounter_FK_1` FOREIGN KEY (`patient_id`) REFERENCES `patient` (`id`),
  CONSTRAINT `encounter_FK_3` FOREIGN KEY (`encounter_form_id`) REFERENCES `encounter_form` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6906510 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `encounter_form`
--

DROP TABLE IF EXISTS `encounter_form`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `encounter_form` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(128) NOT NULL,
  `revision_no` int(11) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `encounter_form_U_2` (`type`),
  UNIQUE KEY `encounter_form_U_1` (`type`)
) ENGINE=InnoDB AUTO_INCREMENT=289 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `episode`
--

DROP TABLE IF EXISTS `episode`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=566 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `episode_type`
--

DROP TABLE IF EXISTS `episode_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `episode_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gp`
--

DROP TABLE IF EXISTS `gp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `local_code` varchar(6) DEFAULT NULL,
  `doh_code` varchar(8) DEFAULT NULL,
  `practice_code` varchar(6) DEFAULT NULL,
  `gp_title` varchar(10) DEFAULT NULL,
  `gp_forename` varchar(50) DEFAULT NULL,
  `gp_surname` varchar(50) DEFAULT NULL,
  `gp_address1` varchar(50) DEFAULT NULL,
  `gp_address2` varchar(50) DEFAULT NULL,
  `gp_address3` varchar(50) DEFAULT NULL,
  `gp_postcode` varchar(10) DEFAULT NULL,
  `gp_workphone` varchar(30) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_updated` datetime DEFAULT NULL,
  `gp_address4` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=63982 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `group`
--

DROP TABLE IF EXISTS `group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `icd10_area`
--

DROP TABLE IF EXISTS `icd10_area`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `icd10_area` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `value` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `title` varchar(128) DEFAULT NULL,
  `is_disabled` tinyint(4) DEFAULT '0',
  `is_deleted` tinyint(4) DEFAULT '0',
  `date_created` datetime DEFAULT NULL,
  `date_updated` datetime DEFAULT NULL,
  `weight` int(11) DEFAULT NULL,
  `icd10_site_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `icd10_area_title` (`title`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `icd10_code`
--

DROP TABLE IF EXISTS `icd10_code`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `icd10_code` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `value` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `title` varchar(128) DEFAULT NULL,
  `is_disabled` tinyint(4) DEFAULT '0',
  `is_deleted` tinyint(4) DEFAULT '0',
  `date_created` datetime DEFAULT NULL,
  `date_updated` datetime DEFAULT NULL,
  `weight` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `icd10_code_title` (`title`)
) ENGINE=InnoDB AUTO_INCREMENT=17875 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `icd10_group`
--

DROP TABLE IF EXISTS `icd10_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `icd10_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `value` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `title` varchar(128) DEFAULT NULL,
  `is_disabled` tinyint(4) DEFAULT '0',
  `is_deleted` tinyint(4) DEFAULT '0',
  `date_created` datetime DEFAULT NULL,
  `date_updated` datetime DEFAULT NULL,
  `weight` int(11) DEFAULT NULL,
  `icd10_area_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `icd10_group_title` (`title`),
  KEY `area_idx` (`icd10_area_id`),
  KEY `area_val` (`value`),
  CONSTRAINT `area` FOREIGN KEY (`icd10_area_id`) REFERENCES `icd10_area` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=116 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `icd10_group_code`
--

DROP TABLE IF EXISTS `icd10_group_code`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `icd10_group_code` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `icd10_group_id` int(11) DEFAULT NULL,
  `icd10_code_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `group_gc` (`icd10_group_id`),
  KEY `code_gc` (`icd10_code_id`),
  CONSTRAINT `code_gc` FOREIGN KEY (`icd10_code_id`) REFERENCES `icd10_code` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `group_gc` FOREIGN KEY (`icd10_group_id`) REFERENCES `icd10_group` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=1579 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `icd10_qualifier`
--

DROP TABLE IF EXISTS `icd10_qualifier`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `icd10_qualifier` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `value` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `title` varchar(128) DEFAULT NULL,
  `is_disabled` tinyint(4) DEFAULT '0',
  `is_deleted` tinyint(4) DEFAULT '0',
  `date_created` datetime DEFAULT NULL,
  `date_updated` datetime DEFAULT NULL,
  `weight` int(11) DEFAULT NULL,
  `icd10_group_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `icd10_qualifier_title` (`title`),
  KEY `group_idx` (`icd10_group_id`),
  CONSTRAINT `group` FOREIGN KEY (`icd10_group_id`) REFERENCES `icd10_group` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `identifier`
--

DROP TABLE IF EXISTS `identifier`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `identifier` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `value` int(11) NOT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ilh_existing_id`
--

DROP TABLE IF EXISTS `ilh_existing_id`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ilh_existing_id` (
  `id` varchar(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `observation`
--

DROP TABLE IF EXISTS `observation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `observation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `observation_field_id` int(11) DEFAULT NULL,
  `value` varchar(255) NOT NULL,
  `encounter_id` int(11) NOT NULL,
  `dictionary_item_id` int(11) DEFAULT NULL,
  `is_serialized` tinyint(4) DEFAULT '0',
  `is_deleted` tinyint(4) DEFAULT '0',
  `is_note` tinyint(4) DEFAULT '0',
  `date_created` datetime DEFAULT NULL,
  `date_updated` datetime DEFAULT NULL,
  `note` text,
  PRIMARY KEY (`id`),
  KEY `observation_FI_1` (`encounter_id`),
  KEY `observation_FI_2` (`observation_field_id`),
  KEY `observation_I_1` (`value`),
  KEY `observation_FK_3` (`dictionary_item_id`),
  CONSTRAINT `observation_FK_1` FOREIGN KEY (`encounter_id`) REFERENCES `encounter` (`id`),
  CONSTRAINT `observation_FK_2` FOREIGN KEY (`observation_field_id`) REFERENCES `observation_field` (`id`),
  CONSTRAINT `observation_FK_3` FOREIGN KEY (`dictionary_item_id`) REFERENCES `dictionary_item` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=59142442 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `observation_audit`
--

DROP TABLE IF EXISTS `observation_audit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `observation_audit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `observation_id` int(11) NOT NULL,
  `audit_id` int(11) NOT NULL,
  `change_type` varchar(15) DEFAULT NULL,
  `value` text,
  `is_serialized` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `observation_audit_FI_1` (`observation_id`),
  KEY `observation_audit_FI_2` (`audit_id`),
  CONSTRAINT `observation_audit_FK_1` FOREIGN KEY (`observation_id`) REFERENCES `observation` (`id`),
  CONSTRAINT `observation_audit_FK_2` FOREIGN KEY (`audit_id`) REFERENCES `audit` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `observation_field`
--

DROP TABLE IF EXISTS `observation_field`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `observation_field` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `encounter_form_id` int(11) DEFAULT NULL,
  `level` int(11) NOT NULL,
  `weight` int(11) NOT NULL,
  `type` varchar(30) NOT NULL,
  `name` varchar(50) NOT NULL,
  `title` text,
  `default_value` varchar(100) DEFAULT NULL,
  `options` text,
  `settings` text,
  `concepts` text,
  `is_deleted` tinyint(4) DEFAULT '0',
  `date_created` datetime DEFAULT NULL,
  `date_updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `observation_field_U_1` (`encounter_form_id`,`name`),
  CONSTRAINT `observation_field_FK_1` FOREIGN KEY (`encounter_form_id`) REFERENCES `encounter_form` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9647 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `observation_field_audit`
--

DROP TABLE IF EXISTS `observation_field_audit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `observation_field_audit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `observation_field_id` int(11) NOT NULL,
  `audit_id` int(11) NOT NULL,
  `change_type` varchar(15) DEFAULT NULL,
  `change_data` text,
  PRIMARY KEY (`id`),
  KEY `observation_field_audit_FI_1` (`observation_field_id`),
  KEY `observation_field_audit_FI_2` (`audit_id`),
  CONSTRAINT `observation_field_audit_FK_1` FOREIGN KEY (`observation_field_id`) REFERENCES `observation_field` (`id`),
  CONSTRAINT `observation_field_audit_FK_2` FOREIGN KEY (`audit_id`) REFERENCES `audit` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `patient`
--

DROP TABLE IF EXISTS `patient`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patient` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `identifier` varchar(10) NOT NULL,
  `title` varchar(12) DEFAULT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `date_of_birth` date NOT NULL,
  `hospital_no` varchar(10) DEFAULT NULL,
  `nhs_no` varchar(12) DEFAULT NULL,
  `born_in_uk` varchar(1) DEFAULT NULL,
  `country_of_birth` varchar(2) DEFAULT NULL,
  `gender` varchar(1) DEFAULT NULL,
  `marital_status` varchar(1) DEFAULT NULL,
  `ethnic_group` varchar(2) DEFAULT NULL,
  `deceased` tinyint(1) DEFAULT NULL,
  `date_of_death` date DEFAULT NULL,
  `address_id` int(11) DEFAULT NULL,
  `gp_id` int(11) DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT '0',
  `special_status` enum('held','test') DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `patient_U_1` (`identifier`),
  KEY `patient_I_1` (`first_name`,`last_name`,`date_of_birth`),
  KEY `patient_FI_1` (`address_id`),
  KEY `patient_FI_2` (`gp_id`),
  CONSTRAINT `patient_FK_1` FOREIGN KEY (`address_id`) REFERENCES `address` (`id`) ON DELETE CASCADE,
  CONSTRAINT `patient_FK_2` FOREIGN KEY (`gp_id`) REFERENCES `gp` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=64190 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `patient_group`
--

DROP TABLE IF EXISTS `patient_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patient_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `patient_idx` (`patient_id`),
  KEY `group_idx` (`group_id`),
  CONSTRAINT `group_fky` FOREIGN KEY (`group_id`) REFERENCES `group` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `patient_fky` FOREIGN KEY (`patient_id`) REFERENCES `patient` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `propel_migration`
--

DROP TABLE IF EXISTS `propel_migration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `propel_migration` (
  `version` int(11) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `recent_patient`
--

DROP TABLE IF EXISTS `recent_patient`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `recent_patient` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `timestamp` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `timestamp` (`timestamp`,`username`)
) ENGINE=InnoDB AUTO_INCREMENT=271223 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `section`
--

DROP TABLE IF EXISTS `section`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  KEY `section_FI_2` (`patient_id`),
  KEY `section_FI_3` (`episode_id`),
  CONSTRAINT `section_FK_1` FOREIGN KEY (`patient_id`) REFERENCES `patient` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `section_FK_2` FOREIGN KEY (`section_type_id`) REFERENCES `section_type` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `section_FK_3` FOREIGN KEY (`episode_id`) REFERENCES `episode` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=950862 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `section_type`
--

DROP TABLE IF EXISTS `section_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `section_type` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `section_type_FI_1` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `study_breakdown`
--

DROP TABLE IF EXISTS `study_breakdown`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `study_breakdown` (
  `patient_id` int(11) NOT NULL DEFAULT '0',
  `first_name` varchar(50) CHARACTER SET utf8 NOT NULL,
  `last_name` varchar(50) CHARACTER SET utf8 NOT NULL,
  `date_of_birth` date NOT NULL,
  `identifier` varchar(10) CHARACTER SET utf8 NOT NULL,
  `study_id` int(11) unsigned DEFAULT '0',
  `study_no` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `study` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `study_status` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `contact_id` int(11) DEFAULT NULL,
  `ilh_identifier` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `current_status` varchar(255) CHARACTER SET utf8 DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `submit_token`
--

DROP TABLE IF EXISTS `submit_token`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `submit_token` (
  `uuid` varchar(255) NOT NULL,
  `timestamp` int(11) DEFAULT NULL,
  PRIMARY KEY (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_record`
--

DROP TABLE IF EXISTS `user_record`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_record` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL DEFAULT '',
  `record_id` int(11) DEFAULT NULL,
  `record_type` varchar(50) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `logged_in` timestamp NULL DEFAULT NULL,
  `logged_out` timestamp NULL DEFAULT NULL,
  `session_id` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_id` (`session_id`)
) ENGINE=InnoDB AUTO_INCREMENT=53204 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `version`
--

DROP TABLE IF EXISTS `version`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `version` (
  `version` varchar(8) NOT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-23 15:16:33
