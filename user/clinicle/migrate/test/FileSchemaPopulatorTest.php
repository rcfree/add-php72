<?php
namespace ClinicLE\Test\Migrate;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use ClinicLE\Migrate\SqlParser;
use ClinicLE\Migrate\TableFactory;
use ClinicLE\Migrate\FileSchemaPopulator;

class FileSchemaPopulatorTest extends \PHPUnit_Framework_TestCase {
	private $schemaStructure;
	
	function setUp() {
		$file = new \SplFileObject(__DIR__."/data/schema.sql","r");
		
		$this->SUT = new FileSchemaPopulator($file);
		$this->schemaStructure = new \ClinicLE\Migrate\Schema($this->connection);
		$this->factory = new SqlParser();
	}
	
	/**
	 * @test
	 */
	function it_should_retrieve_the_table_list_from_the_file() {
		$tables = array(
			array("table_name"=>"address"),
			array("table_name"=>"patient")
		);

		$this->SUT->populate($this->schemaStructure,$this->factory);
		
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