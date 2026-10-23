<?php


class ADD_Component_LegacyDataPopulated extends ADD_Component {
	function beforeGenerate() {
		$form = $this->_page->getComponent('Form');
		$fieldsToCheck = array("diagnosis","biopsy","comments","mdt_outcome");
		
		$populated=false;
		foreach($fieldsToCheck as $fieldToCheck) {
			$field = $form->getField($fieldToCheck);
			if(ADD_Util::isEmpty($field->getValue())) continue;
			$populated=true;
			break;
		}
		if(!$populated) return;
		ADD::web()->getContent()->addTextElement("span","'Legacy import' tab contains data",array("class"=>"label label-important pull-right"));
	}
}
?>