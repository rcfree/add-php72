<?php
set_include_path(getenv('ADD_MODULE_PATH')."/lib:".get_include_path());
require_once("ADD/Pathology/TestManager.php");
require_once("ADD/Pathology/DataSourceInterface.php");
require_once("ADD/Data/PathologyService.php");
require_once("ADD/Pathology/RecordSet.php");

class ADD_Data_PathologyServiceTest extends ADD_BaseTestFixture {
    private $SUT;
	function setUp() {
		$this->initSystemAccess(false,array("encounter"));
	}
	function testThis() {
		$er = new ADD_EncounterRecord();
		$data = unserialize('a:9:{s:14:"unique_test_id";s:34:"6357469361||T||GFR||20150122204958";s:11:"specimen_no";s:9:"CP260144W";s:14:"coll_date_time";s:16:"22/01/2015 00:00";s:9:"test_code";s:3:"GFR";s:14:"snomed_ct_code";s:0:"";s:6:"result";s:2:"47";s:10:"normal_low";s:0:"";s:11:"normal_high";s:0:"";s:5:"units";s:6:"mL/min";}');
		$er->setType("path-tests-haemresult");
		ADD::db('encounter')->expects($this->once())->method("getObservationFieldsByIdAndName")->will($this->returnValue(array(
			"unique_test_id"=>array(),
			"specimen_no"=>array(),
			"coll_date_time"=>array(),
			"test_code"=>array(),
			"snomed_ct_code"=>array(),
			"result"=>array(),
			"normal_low"=>array(),
			"normal_high"=>array(),
			"units"=>array()
		)));
		$er->setObsValues($data);
		print_r($er);
	}
}
	