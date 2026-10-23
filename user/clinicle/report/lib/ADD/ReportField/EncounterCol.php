<?php

class ADD_ReportField_EncounterCol extends ADD_ReportField {
	private $_field = null;
	function finaliseField($report) {
		$add = ADD::sys();
		$db = ADD::db('encounter');
		
		$parent = $report->getParentField($this->getName());
		if (get_class($parent)=="ADD_ReportField_EncounterColCell") $parent = $report->getParentField($parent->getName());
		$name = preg_replace("/\#.+/","",$this->getName());
		$dbField = $db->getFieldFromEncounterForm($parent->getSetting('encounter'),$name);
		if (!$dbField) throw new ADD_FieldException("Field with name '".$this->getName()."' was not defined in encounter type ".$parent->getSetting('encounter'));
		$ff = new ADD_FieldFactory_Database();
		$field = $ff->newInstance($dbField);
                
		$this->_field = $field;
	}
	function getDisplayText($page, $id) {
		$this->_field->finaliseField($page);
		return $this->_field->getDisplayText($page, $id);
	}
	protected function getField() {
		return $this->_field;
	}
	function populateValue($values, $page, $clearFirst=false) {
		$fieldName = $name = preg_replace("/\#.+/","",$this->getName());
		if ($clearFirst) $this->_field->setValue(null);
		
		if(isset($values[$fieldName])) {
			$this->_field->setValue($values[$fieldName]);
		}
	}
	function clearValue() {
		$this->_field->clearValue();
	}
}
?>