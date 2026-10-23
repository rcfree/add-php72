<?php

class ADD_Field_DrugCheckboxes extends ADD_Field_List_Checkboxes {
	function finaliseField($page) {
		

		$this->_options = new ADD_Field_Option_LinkedEncounters();
		$this->_options->initOptions($this,$page);
	}
	function addMarkup($page) {
		$options = $this->getOptions();
		if ($options->countOptions()>0) return parent::addMarkup($page);
		$html=$page->html();
		$html->addTextElement("label",$this->getTitle());
		$html->addTextElement("p","Please add one or more drugs to the Drugs list in the Medications module",array("class"=>"text text-error"));
	}
        function updateExistingData($dbField) {
            return true;
	}
}
?>
