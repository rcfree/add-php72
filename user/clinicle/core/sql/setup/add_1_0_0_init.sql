-- MySQL dump 10.13  Distrib 5.5.27, for osx10.6 (i386)
--
-- Host: localhost    Database: add_1_0_data
-- ------------------------------------------------------
-- Server version	5.5.27-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `audit`
--

DROP TABLE IF EXISTS `audit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `record_id` int(11) NOT NULL,
  `record_type` varchar(20) DEFAULT NULL,
  `record_name` varchar(20) DEFAULT NULL,
  `action` varchar(10) DEFAULT NULL,
  `action_info` text,
  `revision_no` int(11) DEFAULT NULL,
  `user_id` varchar(20) NOT NULL,
  `timestamp` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=165 DEFAULT CHARSET=utf8;
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
-- Table structure for table `current_record`
--

DROP TABLE IF EXISTS `current_record`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `current_record` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uri` varchar(50) DEFAULT NULL,
  `user_id` varchar(255) DEFAULT NULL,
  `session_id` varchar(50) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
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
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8;
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
  `title` varchar(128) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dictionary_group_U_1` (`name`,`dictionary_id`),
  KEY `dictionary_group_FI_1` (`dictionary_id`),
  CONSTRAINT `dictionary_group_FK_1` FOREIGN KEY (`dictionary_id`) REFERENCES `dictionary` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
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
  `concept` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `data` text,
  `parent_item_id` int(11) DEFAULT NULL,
  `is_disabled` tinyint(4) DEFAULT '0',
  `is_deleted` tinyint(4) DEFAULT '0',
  `date_created` datetime DEFAULT NULL,
  `date_updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dictionary_item_U_1` (`value`,`dictionary_id`),
  KEY `dictionary_item_I_1` (`title`),
  KEY `dictionary_item_FI_1` (`dictionary_id`),
  KEY `dictionary_item_FI_2` (`dictionary_group_id`),
  CONSTRAINT `dictionary_item_FK_1` FOREIGN KEY (`dictionary_id`) REFERENCES `dictionary` (`id`),
  CONSTRAINT `dictionary_item_FK_2` FOREIGN KEY (`dictionary_group_id`) REFERENCES `dictionary_group` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=18485 DEFAULT CHARSET=utf8;
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
  `identifier` varchar(20) NOT NULL,
  `is_complete` tinyint(4) NOT NULL,
  `is_locked` tinyint(4) DEFAULT '0',
  `is_deleted` tinyint(4) DEFAULT '0',
  `is_invalid` tinyint(4) DEFAULT '0',
  `is_autosave` tinyint(4) DEFAULT '0',
  `is_verified` tinyint(4) DEFAULT '0',
  `revision_no` int(11) NOT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `encounter_FI_1` (`patient_id`),
  KEY `encounter_FI_2` (`parent_encounter_id`),
  KEY `encounter_FI_3` (`encounter_form_id`),
  CONSTRAINT `encounter_FK_1` FOREIGN KEY (`patient_id`) REFERENCES `patient` (`id`),
  CONSTRAINT `encounter_FK_2` FOREIGN KEY (`parent_encounter_id`) REFERENCES `encounter` (`id`),
  CONSTRAINT `encounter_FK_3` FOREIGN KEY (`encounter_form_id`) REFERENCES `encounter_form` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8;
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
  UNIQUE KEY `encounter_form_U_1` (`type`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gp`
--

DROP TABLE IF EXISTS `gp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `local_code` varchar(5) DEFAULT NULL,
  `doh_code` varchar(8) DEFAULT NULL,
  `practice_code` varchar(6) DEFAULT NULL,
  `gp_title` varchar(10) DEFAULT NULL,
  `gp_forename` varchar(50) DEFAULT NULL,
  `gp_surname` varchar(50) DEFAULT NULL,
  `gp_address1` varchar(50) DEFAULT NULL,
  `gp_address2` varchar(50) DEFAULT NULL,
  `gp_address3` varchar(50) DEFAULT NULL,
  `gp_postcode` varchar(10) DEFAULT NULL,
  `gp_workphone` varchar(10) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
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
  `observation_note_id` int(11) DEFAULT NULL,
  `value` varchar(255) NOT NULL,
  `encounter_id` int(11) NOT NULL,
  `dictionary_item_id` int(11) DEFAULT NULL,
  `is_serialized` tinyint(4) DEFAULT '0',
  `is_deleted` tinyint(4) DEFAULT '0',
  `is_note` tinyint(4) DEFAULT '0',
  `date_created` datetime DEFAULT NULL,
  `date_updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `observation_FI_1` (`encounter_id`),
  KEY `observation_FI_2` (`observation_field_id`),
  KEY `observation_FI_3` (`observation_note_id`),
  CONSTRAINT `observation_FK_1` FOREIGN KEY (`encounter_id`) REFERENCES `encounter` (`id`),
  CONSTRAINT `observation_FK_2` FOREIGN KEY (`observation_field_id`) REFERENCES `observation_field` (`id`),
  CONSTRAINT `observation_FK_3` FOREIGN KEY (`observation_note_id`) REFERENCES `observation_note` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=87 DEFAULT CHARSET=utf8;
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
) ENGINE=InnoDB AUTO_INCREMENT=92 DEFAULT CHARSET=utf8;
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
  `default_value` varchar(100) NOT NULL,
  `options` text,
  `settings` text,
  `concepts` text,
  `is_deleted` tinyint(4) DEFAULT '0',
  `date_created` datetime DEFAULT NULL,
  `date_updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `observation_field_U_1` (`encounter_form_id`,`name`),
  CONSTRAINT `observation_field_FK_1` FOREIGN KEY (`encounter_form_id`) REFERENCES `encounter_form` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=948 DEFAULT CHARSET=utf8;
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
) ENGINE=InnoDB AUTO_INCREMENT=959 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `observation_note`
--

DROP TABLE IF EXISTS `observation_note`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `observation_note` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `observation_id` int(11) DEFAULT NULL,
  `text` text NOT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `observation_note_FI_1` (`observation_id`),
  CONSTRAINT `observation_note_FK_1` FOREIGN KEY (`observation_id`) REFERENCES `observation` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
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
  `ethnic_group` varchar(1) DEFAULT NULL,
  `deceased` tinyint(1) DEFAULT NULL,
  `date_of_death` date DEFAULT NULL,
  `address_id` int(11) DEFAULT NULL,
  `gp_id` int(11) DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT '0',
  `date_created` datetime DEFAULT NULL,
  `date_updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `patient_U_1` (`identifier`),
  KEY `patient_I_1` (`first_name`,`last_name`,`date_of_birth`),
  KEY `patient_FI_1` (`address_id`),
  KEY `patient_FI_2` (`gp_id`),
  CONSTRAINT `patient_FK_1` FOREIGN KEY (`address_id`) REFERENCES `address` (`id`) ON DELETE CASCADE,
  CONSTRAINT `patient_FK_2` FOREIGN KEY (`gp_id`) REFERENCES `gp` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-01-16 10:26:08
