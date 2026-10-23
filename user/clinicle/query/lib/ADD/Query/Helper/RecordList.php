<?php
;
class ADD_Query_Helper_RecordList extends ADD_Query_Helper_Base {
	
	function outputResults($encounterType, $obsFields,$patientFields=null) {
		$this->_encounterLookup = new ADD_Export_EncounterLookup(ADD::sys()->getModules());
			
		$this->_output = new ADD_Query_Output_Table_Record($this->_run,$this->_result);
		$this->_result = new ADD_Query_Result($this->_encounterLookup);
		if($this->_options->getSetting('patientId')) $this->_result->setPatientIds(array($this->_options->getSetting('patientId')));
		
		$this->_run->addValue("EncData",array("fields"=>array("is_deleted","id"),"encounter"=>$encounterType));
		$this->_run->addValue("ObsData",array("fields"=>$obsFields,"encounter"=>$encounterType));
		
		$this->_run->run($this->_result,$this->_output);
		
		return $this->_output->getRows();
	}
}