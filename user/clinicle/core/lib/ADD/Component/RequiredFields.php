<?php
class ADD_Component_RequiredFields extends ADD_Component {
	private $_requiredFields = array();
	private $_incompleteFields = array();
	
	function setIncompleteFields($incompleteFields) {
		$this->_incompleteFields = $incompleteFields;
	}
	
	function findIncompleteFields($values=null) {
        $page = $this->_page;

        if ($this->_incompleteFields && !$values) return $this->_incompleteFields;
		$fields = $this->getRequiredFields();
		$incomplete = array();
		foreach($fields as $field) {
			if ($field->isHidden()) continue;
		    if (isset($values[$field->getName()]) && is_array($values[$field->getName()])) {

		        if (ADD_Util::isEmpty($values[$field->getName()])) {
                    $incomplete[]=$field->getName();
                }
		    }
            else {
                if($field->getType()=="Checkbox") {

                    $value = \ClinicLE\Common\Util::val($field->getName(),$values);
                    if ($value==='0' ||
                        $value === null) {
                        $incomplete[]=$field->getName();
                        continue;
                    }
                }
    			if (!isset($values[$field->getName()]) || (isset($values[$field->getName()]) && ADD_Util::isEmpty($values[$field->getName()]))) {
    				$incomplete[]=$field->getName();
    			}
            }
		}
        $this->_incompleteFields = $incomplete;
		return $incomplete;
	}
	
	function getRequiredFields() {
        $page = $this->_page;

        if ($this->_requiredFields) return $this->_requiredFields;
		
		$form = $page->getComponent('Form');
		$fields = $form->getFields();
		$required = array();
		foreach($fields as $field) {
			if (is_subclass_of($field,"ADD_Field_Container")) continue;
			if ($field->getSetting('required')) $required[]=$field;
		}
		$this->_requiredFields = $required;
		return $required;
	}

}
