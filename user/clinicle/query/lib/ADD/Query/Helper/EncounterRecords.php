<?php
;
;
class ADD_Query_Helper_EncounterRecords extends ADD_Query_Helper_Base {
	private $_output;
	private $_encounterLookup;
	private $_result;
	
	function outputResults() {
		$this->_encounterLookup = new ADD_Query_EncounterLookup(ADD::sys()->getModules());
			
		$this->_output = new ADD_Query_Output_Table_Record($this->_run,$this->_result);
		$this->_result = new ADD_Query_Result($this->_encounterLookup);
		$patientId = $this->_options->getSetting('patientId');
		if($patientId) $this->_result->setPatientIds(array($patientId));
		$parentId = $this->_options->getSetting('parentId');
		
		$encType = $this->_options->assertSetting('encounter');
		
		if($parentId) {
			$this->_run->addFilter("EncValue",array("encounter"=>$encType,"field"=>"parent_encounter_id","value"=>$parentId,"op"=>"equal","type"=>ADD_Query_Filter::ENCOUNTER));
		}
		$this->_run->addValue("EncData",array("encounter"=>$encType,"fields"=>array("id","parent_encounter_id")));
		$this->_run->addValue("ObsData",array("encounter"=>$encType));
		
		$this->_run->run($this->_result,$this->_output);
		
		$rows = $this->_output->getRows();
		
		$ers = array();
		$em = ADD::sys()->getEncounterManager();
		foreach($rows as $row) {
			$er = $em->recordById($row['id']);
			$er->setType($encType);
			unset($row['id']);
			unset($row['parent_encounter_id']);
			$er->setObsValues($row);
			$ers[]=$er;
		}
		
		return $ers;
	}
}