<?php
class ADD_Field_DrugDropdown extends ADD_Field_List_Dropdown {
	const VERSION="1.00";
	const DATE="12/08/2013";
	function finaliseField($page) {
		$add = ADD::sys();	
		if (!$this->getSetting('dictionary_encounter')) throw new ADD_FieldException("No 'dictionary_encounter' defined for field '{$this->getName()}'");
		$edb = ADD::db('encounter');	
		$encs = $edb->getEncountersWithPatientAndType($add->getPatientId(),$this->getSetting('dictionary_encounter'));
		$encObsList = $this->getSetting('encounter_obs',null);
		if (!$encObsList) throw new ADD_FieldException("'encounter_obs' was not defined for field '{$this->getName()}'");
		$encObs = explode(";",$encObsList);
		$encOptions = array();
		foreach($encs as $enc) {
			$obs = $edb->getObservationsInEncounter($enc);
			$item = "";
			foreach($encObs as $encOb) {
				$item.=ADD_Util::getArrayValue($encOb,$obs,"")." ";
			}
			$currStat = ADD_Util::getArrayValue('current_status',$obs);
			$item=trim($item);
			if ($currStat==2) {
				$item="INACTIVE (".$item.")";
			}
			$encOptions[$enc->getId()]=$item;
		}
		$this->setOptions($encOptions);
		$this->isFinalised(true);
	}
	
	function addMarkup($page) {
		$options = $this->getOptions();
		if (count($options)>0) return parent::addMarkup($page);
		$html=$page->html();
		$html->addTextElement("label",$this->getTitle());
		$html->addTextElement("p","Please add one or more drugs to the Drugs list in the Investigations module",array("class"=>"text text-error"));
	}			
}
?>