<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DrugTimepointTest
 *
 * @author rcf8
 */
namespace ADD\Test\Med\ActiveDrugs;

use ADD\Med\DrugConstants;

class ActiveDrugsTest extends \PHPUnit_Framework_TestCase {
	private $SUT;
	function setUp() {
		$this->SUT = new ActiveDrugsImpl();
	}
	
	/**
	 * @test
	 */
	function it_returns_all_active_drug_records_using_the_implementation_of_wasRecordActive() {
		$drugs = array(
			array("status"=>DrugConstants::STATUS_ACTIVE, "drug_name"=>"PARACETAMOL"),
			array("status"=>DrugConstants::STATUS_ACTIVE, "drug_name"=>"AMOXYCILLIN"),
			array("status"=>DrugConstants::STATUS_INACTIVE, "drug_name"=>"FOSTAIR"),
			array("status"=>DrugConstants::STATUS_ACTIVE, "drug_name"=>"SALBUTAMOL"),
			array("status"=>DrugConstants::STATUS_INACTIVE, "drug_name"=>"CANNABIS")
		);
		$obsRecords = $this->SUT->extractActiveRecords($drugs);
		$expRecords = array($drugs[0],$drugs[1],$drugs[3]);
		$this->assertEquals($obsRecords,$expRecords);
	}
}
class ActiveDrugsImpl extends \ADD\Med\ActiveDrugs\AbstractActiveDrugs {
	public function wasRecordActive($record) {
		if($record['status'] == DrugConstants::STATUS_ACTIVE) {
			return true;
		}
		return false;
	}
}