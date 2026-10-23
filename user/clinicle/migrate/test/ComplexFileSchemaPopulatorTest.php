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

class ComplexFileSchemaPopulatorTest extends \PHPUnit_Framework_TestCase {
	private $schema;
	private $sqlParser;
	function setUp() {
		$file = new \SplFileObject(__DIR__."/data/complexSchema.sql","r");
		
		$this->SUT = new FileSchemaPopulator($file);
		$this->schema = new \ClinicLE\Migrate\Schema($this->connection);
		$this->sqlParser = new SqlParser();
	}

	/**
	 * @test
	 */
	function it_reads_the_file() {
        $this->SUT->populate($this->schema,$this->sqlParser);
        $this->assertTrue(true);
    }

    /**
     * @test
     */
    function it_deals_with_enums() {
        $this->SUT->populate($this->schema,$this->sqlParser);

        $patientTable = $this->schema->getTable("patient");
        $this->assertEquals("enum('held','test') DEFAULT NULL",$patientTable->getField("special_status"));
    }

}