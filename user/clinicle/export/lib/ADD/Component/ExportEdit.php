<?php

class ADD_Component_ExportEdit extends ADD_Component {
	public function title(&$title) {
		$title = "Export edit";
	}
	
	function getComponentList() {
		$cl = array(
			'PatientCheck',
			'Encounter_SingleRecord', 
			'AccessControl',
			'OptionContainer',
			'SaveButton',
            'HistoryOption',
		);
		return $cl;
	}
	
	function setup() {
		$er = $this->_page->getEncounterRecord();
		$er->retrieve();
		if(!$er->getType()) return;
		$encForm = ADD::db('encounter')->getEncounterFormWithType($er->getType());
		$obsFields = ADD::db('encounter')->getObservationFieldsInEncounterForm($encForm['id']);
		$ff = new ADD_FieldFactory_Database();
		$form = new ADD_Component_Form();
		$this->_page->getComponents()->add($form);
		ADD::sys()->setState('readOnly',false);
		foreach($obsFields as $obsField) {
			$obsField['level']=1;
			$field = $ff->newInstance($obsField);
			if(is_a($field,"ADD_Field_Button")) {
				continue;
			}
			
			if(is_a($field,"ADD_Field_Container_AutoComplete")) {
				$obsField['type']="Text";
				$field = $ff->newInstance($obsField);
			}
			 
			 
			if($obsField['type']!='AutoComplete' && is_a($field,"ADD_Field_Container")) {
				$obsField['type']="Container";
				$field = $ff->newInstance($obsField);
			}
			if(is_a($field,"ADD_Field_Expression")) {
				$obsField['type']="Text";
				$field = $ff->newInstance($obsField);
			}
			
			$field->setSetting('viewonly',false);
			
			if(is_a($field,"ADD_Field_Hidden")) {
				$obsField['type']="Text";
				$field = $ff->newInstance($obsField);
				$field->setSetting('viewonly',true);
			}
			
			$form->addField($field);
			$field->finaliseField($this->_page);
		}
		$form->_prepareChildrenAndTopLevelFields();
	}
	
	function afterGenerate() {
		$web = ADD::web();
		if (method_exists($web,"clearJavascript")) $web->clearJavascript();
		//$web->addJavascript("jQuery('[type=\"hidden\"]').attr(\"type\",\"text\");");
	}
	public function expectedPermissions(&$expPerms) {
		$expPerms=array("edit");
	}
	public function menuPath(&$menuPath) {
		$menuPath="export/edit";
	}
}
