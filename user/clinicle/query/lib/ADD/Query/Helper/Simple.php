<?php
;
;
;
class ADD_Query_Helper_Simple extends ADD_Query_Helper_Base {
	
	function outputResults() {
		$resultItems=array();
		//$this->_encounterLookup = new ADD_Query_EncounterLookup(ADD::sys()->getModules());
		$output = new ADD_Query_Output_Simple();	
		$this->_result = new ADD_Query_Result($output,$this->_run);
		//$this->_run->run($this->_result,$this->_output);
		while($resultItem = $this->_result->nextResultItem()) {
			$resultItems[]=$resultItem;
		}
		return $resultItems;
	}
}