<?php
class ADD_Field_List_MultiSelect extends ADD_Field_List {
	public function getDisplayText($form,$id) {
		$options = $this->getOptions();
		$keyedValues = $this->getValue();
		
		$output = array();
		if ($keyedValues) {
			if (!is_array($keyedValues)) {
				return "ERROR WITH DATA";
			}
			foreach($keyedValues as $value => $label) {
				if (empty($label)) continue;
				$valueWithKey = $options->findLabelWithValue($value);
				$output[] = $valueWithKey ? $valueWithKey : "Option '$value' not present";
			}
			if (count($output)==0) {
				return "- Nothing -";
			}
			else {
				return implode(", ",$output);
			}
		}
		else {
			return $this->getSetting("blank_no_options") ? "" : "- No options selected -";
		}
	}
	
	function addValue($newValue) {
		return $this->_setKeyValue($newValue,true);
	}
	function removeValue($newValue) {
		return $this->_setKeyValue($newValue,false);
	}
	
	private function _setKeyValue($obsName,$isChecked) {
		$newValue = $isChecked ? 1 : null;
		$currentValue = $this->getValue();
		$options = $this->getOptions();
		if (!isset($options[$obsName])) throw new ADD_FieldException("Toggle option '$newValue' supplied to checkbox '".$this->getName()."' not defined"); 
		if (!$currentValue) $currentValue=array();
		if (isset($currentValue[$obsName]) && $currentValue[$obsName]==$newValue) return false;
		if (!isset($currentValue[$obsName]) && !$isChecked) return false;
		$currentValue[$obsName]=$newValue;
		$this->setValue($currentValue);
		return true;
	}
	function beforeSubmit(&$values,$page) {
		if ($this->getLevel()=="DEL") return;
                parent::beforeSubmit($values,$page);
                if (isset($values[$this->getName()])) return;
		$values[$this->getName()]=array();
	}
	
	public function generateJQueryFieldSel() {
		return "[name^=\"".$this->getName()."[\"]";
	}
	function valueEqual($testValues) {
		$currValues = $this->getValue();
		if (!$currValues) return false;
		if(!is_array($currValues)) $currValues=array($currValues=>$currValues);
		foreach($currValues as $key => $val) {
			if (in_array($val,$testValues)) return true;
		}
		return false;
	}
	function hasValue($value) {
		$currValues = $this->getValue();
		if (!$currValues) return false;
		if($value===0) $value='0';
		if (ADD_Util::getArrayValue($value,$currValues)==$value) return true;
	}
	
	function updateExistingData($dbField) {
		if (!parent::updateExistingData($dbField)) return false;
		$edb = ADD::db('encounter');
		
		$obs = $edb->getObservationsUsingField($dbField);
		
		$newValues = array();
		foreach($obs as $ob) {
			$oldValue = $ob['value'];
			if (is_array($oldValue)) {
				continue;
			}
			
			$newValues[$ob['id']]=strlen($oldValue)==0 ? array() : array($oldValue => $oldValue);
		}
		$this->saveChangedObservations($newValues,$obs);		
		return true;
	}	
}

?>