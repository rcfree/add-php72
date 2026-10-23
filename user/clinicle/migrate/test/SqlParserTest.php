<?php
namespace ClinicLE\Test\Migrate;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use ClinicLE\Migrate\SqlParser;
use ClinicLE\Migrate\TableFactory;

class SqlParserTest extends \PHPUnit_Framework_TestCase {
	function setUp() {
		$this->SUT = new SqlParser();
	}
	
	/**
	 * @test
	 */
	function it_should_read_a_create_table_statement_correctly() {
		$sql = 'CREATE TABLE `address` (
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
  UNIQUE KEY `patient_U_1` (`identifier`),
  KEY `address_FI_1` (`patient_id`),
  CONSTRAINT `address_FK_1` FOREIGN KEY (`patient_id`) REFERENCES `patient` (`id`) ON DELETE CASCADE, CONSTRAINT `address_FK_2` FOREIGN KEY (`address_line1`,`address_line2`) REFERENCES `patient` (`id`) ON DELETE CASCADE, CONSTRAINT `address_FK_3` FOREIGN KEY (`address_line1`,`address_line2`,`address_line3`) REFERENCES `patient` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7931 DEFAULT CHARSET=utf8;';
			
		$tableStructure = $this->SUT->createTableFromSql($sql);
		$this->assertInstanceOf("ClinicLE\Migrate\Table",$tableStructure);
		$this->assertEquals("address",$tableStructure->getTableName());
		$this->assertEquals("(`id`)",$tableStructure->getPrimaryKey());
		$this->assertEquals(array("KEY","(`patient_id`)"),$tableStructure->getKey("address_FI_1"));
		$this->assertEquals(array("CONSTRAINT","FOREIGN KEY (`patient_id`) REFERENCES `patient` (`id`) ON DELETE CASCADE"),$tableStructure->getKey("address_FK_1"));
        $this->assertEquals(array("CONSTRAINT","FOREIGN KEY (`address_line1`,`address_line2`) REFERENCES `patient` (`id`) ON DELETE CASCADE"),$tableStructure->getKey("address_FK_2"));
        $this->assertEquals(array("CONSTRAINT","FOREIGN KEY (`address_line1`,`address_line2`,`address_line3`) REFERENCES `patient` (`id`) ON DELETE CASCADE"),$tableStructure->getKey("address_FK_3"));
		$this->assertEquals(array("UNIQUE KEY","(`identifier`)"),$tableStructure->getKey("patient_U_1"));
		$this->assertEquals("varchar(50) DEFAULT NULL",$tableStructure->getField("address_line_4"));
		$this->assertEquals("int(11) DEFAULT NULL",$tableStructure->getField("patient_id"));
		$this->assertEquals("ENGINE=InnoDB AUTO_INCREMENT=7931 DEFAULT CHARSET=utf8",$tableStructure->getTableInfo());
	}
}