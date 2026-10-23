<?php
namespace ClinicLE\Test\Migrate;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use ClinicLE\Migrate\SqlParser;
use ClinicLE\Migrate\TableFactory;
use ClinicLE\Migrate\DatabaseSchemaPopulator;

class DatabaseSchemaPopulatorTest extends \PHPUnit_Framework_TestCase {
	const DB_NAME="add_data_test";
	private $schemaStructure;
	
	function setUp() {
		$this->connection = $this->getMock("ClinicLE\Common\Connection\AbstractConnection");
		$this->SUT = new DatabaseSchemaPopulator($this->connection);
		$this->schemaStructure = new \ClinicLE\Migrate\Schema($this->connection);
		$this->sqlParser = new SqlParser();
	}
	
	/**
	 * @test
	 */
	function it_should_retrieve_the_table_list_from_the_connection() {
		$tables = array(
			array("table_name"=>"address"),
			array("table_name"=>"patient")
		);
		$patientSql = "CREATE TABLE `patient` (
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
  `date_created` datetime DEFAULT NULL,
  `date_updated` datetime DEFAULT NULL,
  `special_status` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `patient_U_1` (`identifier`),
  KEY `patient_I_1` (`first_name`,`last_name`,`date_of_birth`),
  KEY `patient_FI_1` (`address_id`),
  KEY `patient_FI_2` (`gp_id`),
  CONSTRAINT `patient_FK_1` FOREIGN KEY (`address_id`) REFERENCES `address` (`id`) ON DELETE CASCADE,
  CONSTRAINT `patient_FK_2` FOREIGN KEY (`gp_id`) REFERENCES `gp` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8162 DEFAULT CHARSET=utf8";
$addressSql = "CREATE TABLE `address` (
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
) ENGINE=InnoDB AUTO_INCREMENT=7918 DEFAULT CHARSET=utf8";

		$this->connection->expects($this->once())->method("getDbName")->will($this->returnValue(self::DB_NAME));
		$this->connection->expects($this->once())->method("fetchAll")->with("SELECT table_name
	    FROM information_schema.tables 
	    WHERE table_schema = ?",self::DB_NAME)->will($this->returnValue($tables));
		$this->connection->expects($this->at(2))->method("fetchFirst")->with("show create table `address`")->will($this->returnValue(array("table_name"=>"address","Create Table"=>$addressSql)));
		$this->connection->expects($this->at(3))->method("fetchFirst")->with("show create table `patient`")->will($this->returnValue(array("table_name"=>"patient","Create Table"=>$patientSql)));
		
		$this->SUT->populate($this->schemaStructure,$this->sqlParser);
		$patientTable = $this->schemaStructure->getTable("patient");
		$this->assertInstanceOf("ClinicLE\Migrate\Table",$patientTable);
		$this->assertEquals("patient",$patientTable->getTableName());
		$this->assertCount(22,$patientTable->getFields());
		$this->assertEquals(array("UNIQUE KEY","(`identifier`)"),$patientTable->getKey("patient_U_1"));
		
		
		$addressTable = $this->schemaStructure->getTable("address");
		$this->assertInstanceOf("ClinicLE\Migrate\Table",$addressTable);
		$this->assertEquals("address",$addressTable->getTableName());
		$this->assertCount(11,$addressTable->getFields());
		$this->assertEquals("(`id`)",$addressTable->getPrimaryKey());
	}
}