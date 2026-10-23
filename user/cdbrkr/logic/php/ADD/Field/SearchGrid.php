<?php
require_once("ADD/Query/Helper/RecordList.php");
require_once("ADD/Field/WebClient.php");
class ADD_Field_SearchGrid extends ADD_Field_WebClient {
	private $_existingItems = array();
	function populateValue($values,$page, $ignoreNew=false) {
		$encType="cdbrkr-wspl-comorbitem";
		$qhelper = new ADD_Query_Helper_RecordList();
		
		$run = $qhelper->createRun(array("patientId"=>ADD::sys()->getPatientId()));
		$run->addFilter("EncValue",array("encounter"=>$encType,"field"=>"parent_encounter_id","op"=>"equal","value"=>ADD::sys()->getParam('eid'),"type"=>ADD_Query_Filter::ENCOUNTER));
		$run->addFilter("EncValue",array("encounter"=>$encType,"field"=>"is_deleted","op"=>"not_equal","value"=>"1","type"=>ADD_Query_Filter::ENCOUNTER));
		$run->addFilter("ObsValue",array("encounter"=>$encType,"field"=>"search_item","op"=>"equal","value"=>"1","type"=>ADD_Query_Filter::ENCOUNTER));
		
		$this->_existingItems = $qhelper->outputResults($encType,null);
	}
	function getExistingItems() {
		return $this->_existingItems;
	}
}