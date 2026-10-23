<?php
namespace ClinicLE\Test\Migrate;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use ClinicLE\Migrate\SqlParser;
use ClinicLE\Migrate\TableFactory;
use ClinicLE\Migrate\TableCompare;

class TableCompareTest extends \PHPUnit_Framework_TestCase {
    private $sqlParser;
    private $SUT;

	function setUp() {
		$this->SUT = new TableCompare();
        $this->sqlParser = new SqlParser();

        $this->sql1 = $sql1 = 'CREATE TABLE `address` (
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
) ENGINE=InnoDB AUTO_INCREMENT=7931 DEFAULT CHARSET=utf8;';
$this->sql2 = $sql2 = 'CREATE TABLE `address` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `address_line_1` varchar(50) DEFAULT NULL,
  `address_line_2` varchar(50) DEFAULT NULL,
  `address_line_3` varchar(50) DEFAULT NULL,
  `address_line_4` varchar(50) DEFAULT NULL,
  `postcode` varchar(8) DEFAULT NULL,
  `home_phone` varchar(30) DEFAULT NULL,
  `work_phone` varchar(30) DEFAULT NULL,
  `email` varchar(30) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `address_FI_1` (`patient_id`),
) ENGINE=InnoDB AUTO_INCREMENT=7931 DEFAULT CHARSET=utf8;';
    }
	
	/**
	 * @test
	 */
	function it_should_correctly_determine_changed_fields_with_include_drop_and_modify_fields() {
		$origTable = $this->sqlParser->createTableFromSql($this->sql1);
		$newTable = $this->sqlParser->createTableFromSql($this->sql2);
        $this->SUT->setIncludeDropFields(true);
        $this->SUT->setIncludeModifyFields(true);
        $statement = $this->SUT->createModifyStatement($origTable,$newTable);
		$this->assertEquals("alter table `address` drop column `patient_id`, add column `email` varchar(30) DEFAULT NULL, modify column `postcode` varchar(8) DEFAULT NULL",$statement);
	}

    /**
     * @test
     */
    function it_should_correctly_determine_changed_fields_without_include_drop_and_with_modify_fields() {
        $origTable = $this->sqlParser->createTableFromSql($this->sql1);
        $newTable = $this->sqlParser->createTableFromSql($this->sql2);
        $this->SUT->setIncludeDropFields(false);
        $this->SUT->setIncludeModifyFields(true);
        $statement = $this->SUT->createModifyStatement($origTable,$newTable);
        $this->assertEquals("alter table `address` add column `email` varchar(30) DEFAULT NULL, modify column `postcode` varchar(8) DEFAULT NULL",$statement);
    }

    /**
     * @test
     */
    function it_should_correctly_determine_changed_fields_without_include_drop_and_modify_fields() {
        $origTable = $this->sqlParser->createTableFromSql($this->sql1);

        $newTable = $this->sqlParser->createTableFromSql($this->sql2);
        $this->SUT->setIncludeDropFields(false);
        $this->SUT->setIncludeModifyFields(false);
        $statement = $this->SUT->createModifyStatement($origTable,$newTable);
        $this->assertEquals("alter table `address` add column `email` varchar(30) DEFAULT NULL",$statement);
    }

    /**
     * @test
     */
    function it_should_correctly_ignore_lack_of_primary_key_if_given_empty_array_of_changes() {
        $origTable = $this->sqlParser->createTableFromSql($this->sql1);

        $statement = $this->SUT->createModifyStatement($origTable,array());
        $this->assertNull($statement);
    }

    /**
     * @test
     */
    function it_should_correctly_create_key_if_given_array_containing_key() {
        $origTable = $this->sqlParser->createTableFromSql($this->sql1);
        $statement = $this->SUT->createModifyStatement($origTable,array("KEY `test_idx` (id,patient_id)"));
        $this->assertEquals("alter table `address` add KEY `test_idx` (id,patient_id)",$statement);
    }

    /**
     * @test
     */
    function it_should_correctly_modify_key_if_given_array_contains_changed_key() {
        $origTable = $this->sqlParser->createTableFromSql($this->sql1);
        $origTable->setKey("test_idx",array("KEY","(id)"));
        $statement = $this->SUT->createModifyStatement($origTable,array("KEY `test_idx` (id,patient_id)"));
        $this->assertEquals("alter table `address` modify KEY `test_idx` (id,patient_id)",$statement);
    }

    /**
     * @test
     */
    function it_should_not_drop_key_if_given_array_does_not_contain_key_without_drop_keys() {
        $origTable = $this->sqlParser->createTableFromSql($this->sql1);
        $origTable->setKey("test_idx",array("KEY","(id)"));
        $this->SUT->setIncludeDropKeys(false);
        $statement = $this->SUT->createModifyStatement($origTable,array());
        $this->assertEquals(null,$statement);
    }

    /**
     * @test
     */
    function it_should_drop_key_if_given_array_does_not_contain_key_with_drop_keys() {
        $origTable = $this->sqlParser->createTableFromSql($this->sql1);
        $origTable->setKey("test_idx",array("KEY","(id)"));
        $this->SUT->setIncludeDropKeys(true);
        $statement = $this->SUT->createModifyStatement($origTable,array());
        $this->assertEquals("alter table `address` drop KEY `address_FI_1`, drop CONSTRAINT `address_FK_1`, drop KEY `test_idx`",
            $statement);
    }
}