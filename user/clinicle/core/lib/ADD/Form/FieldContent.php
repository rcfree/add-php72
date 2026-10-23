<?php
class ADD_Form_FieldContent implements ADD_Form_FieldContainerInterface {
	private $_fieldsByName = array();
	private $_fieldPosByName = array();
	private $_childFieldsByName = null;
	private $_parentFieldsByName = null;
	private $_fields = array();
	private $_fieldsInit = false;
	private $_topLevelFields = array();
	private $_noFinalise;

	function setNotFinalise($noFinalise) {
		$this->_noFinalise = $noFinalise;
	}
	function doNotFinalise() {
		return $this->_noFinalise;
	}

	function getTopLevelFields() {
		return $this->_topLevelFields;
	}

	public function getNextField($searchField) {
		$fields = $this->getFields();
		$counter=0;
		foreach($fields as $field) {
			if ($field->getName() == $searchField->getName()) {
				return isset($fields[$counter + 1]) ? $fields[$counter + 1] : null;
			}
			$counter++;
		}
	}

	public function getLastChildFieldPosition($field) {
		$childFields = $this->getChildFields($field->getName());
		$fieldPos = $this->_fieldPosByName[$field->getName()];
		if(!$childFields || count($childFields)==0) {
			return $fieldPos;
		}
		$fields = $this->_fields;

		$fieldPos++;
		$descendentField = $fields[$fieldPos];

		while($descendentField->getLevel()>$field->getLevel()) {
			if(!isset($fields[$fieldPos])) {
				break;
			}
			$descendentField = $fields[$fieldPos];

			$fieldPos++;
		}
		$fieldPos-=2;
		return $fieldPos;
	}

	public function insertChildFieldAfterParentDescendents($parentField,$childField) {
		if($parentField->getLevel()!="DEL") {
			$childField->setLevel($parentField->getLevel()+1);
        }

		$fieldPos = $this->getLastChildFieldPosition($parentField);

		$firstSlice = array_slice($this->_fields,0,$fieldPos+1);
		$lastSlice = array_slice($this->_fields,$fieldPos+1);

		$mergedFields = $firstSlice;
		$mergedFields[]=$childField;
		$mergedFields = array_merge($mergedFields,$lastSlice);

		$this->_fields=$mergedFields;

		$this->refreshFieldPosByName();
	}

	public function refreshFieldPosByName() {
		$fcount=0;
		foreach($this->getFields() as $field) {
			$fieldPosByName[$field->getName()]=$fcount;
			$fcount++;
		}
		$this->_fieldPosByName=$fieldPosByName;
	}
	public function addChildField($parentField, $childField) {
		if (!is_object($parentField)) $parentField = $this->getField($parentField);

		$this->insertChildFieldAfterParentDescendents($parentField,$childField);
		$this->_fieldsByName[$childField->getName()]=$childField;
		$this->_childFieldsByName[$parentField->getName()][]=$childField;
		$fieldPosByName=array();
	}

	public function setChildFieldsByName($childFieldsByName) {
		$this->_childFieldsByName=$childFieldsByName;
	}

	function setParentFieldsByName($parentFieldsByName) {
		$this->_parentFieldsByName=$parentFieldsByName;
	}

	function getFields() {
		return $this->_fields;
	}

	public function getChildFieldsByName() {
		return $this->_childFieldsByName;
	}

	function getParentFieldsByName() {
		return $this->_childFieldsByName;
	}

	public function getParentField($name) {
		if (is_object($name)) $name = $name->getName();
		if (isset($this->_parentFieldsByName[$name])) {
			return $this->getField($this->_parentFieldsByName[$name]);
		}
	}

	public function getChildFields($fieldName) {
		if (is_object($fieldName)) $fieldName=$fieldName->getName();
		if (isset($this->_childFieldsByName[$fieldName])) {
			return $this->_childFieldsByName[$fieldName];
		}
		return null;
	}

	function getFieldsByName() {
		return $this->_fieldsByName;
	}

	function getField($fieldName) {
		if (isset($this->_fieldsByName[$fieldName])) {
			return $this->_fieldsByName[$fieldName];
		}
		else {
			return null;
		}
	}

	function addField($field) {
		if (!$field) return;
		if (isset($this->_fieldsByName[$field->getName()]) && strlen($field->getName())>0) throw new ADD_FormException("Field name '".$field->getName()."' is specified multiple times in this form");
		$this->_fieldsByName[$field->getName()]=$field;
		$this->_fieldPosByName[$field->getName()]=count($this->_fields);

		$this->_fields[]=$field;
		if ($field->getLevel()==1) {
			$this->_topLevelFields[]=$field->getName();
		}
	}
	function replaceField($field) {
		unsset($this->_fieldsByName[$field->getName()]);
		$this->addField($field);
	}
	function unshiftField($field) {
		if (isset($this->_fieldsByName[$field->getName()])) throw new ADD_FormException("Field name '".$field->getName()."' is specified multiple times in this form");
		$this->_fieldsByName[$field->getName()]=$field;
		$fields = $this->_fields;
		$this->_fields=$fields;
		if ($field->getLevel()==1) {
			array_unshift($this->_topLevelFields,$field->getName());
		}
	}

	function areFieldsInit() {
		return $this->_fieldsInit;
	}
	function setFieldsInit($fieldsInit) {
		$this->_fieldsInit = $fieldsInit;
	}

	function getChildFieldAtPosition($position) {
		$fields = $this->getFields();
		$field = $fields[$position];
		$startLevel = $field->getLevel();
        if($startLevel=="DEL") {
            return array();
        }
		$currPosition = $position;
		$children = array();
		$slice = array_slice($fields,$position+1);
		foreach($slice as $currField) {
			$level = $currField->getLevel();

			if ($level == $startLevel) {
				break;
			}
			else if ($level < $startLevel) {
				break;
			}
			else if ($level == $startLevel+1) {
				$children[]=$currField;
			}

			$currPosition++;
		}
		return $children;
	}

	function prepareChildrenAndTopLevelFields() {
		$position = 0;
		$childFieldsByName = array();
		$topLevelFields=array();
		$parentFieldsByName = array();
		foreach($this->getFields() as $field) {
			$childFields = $this->getChildFieldAtPosition($position);
			foreach($childFields as $childField) {
                            $childName = $childField->getName();
				if (!isset($parentFieldsByName[$childName])) $parentFieldsByName[$childName]=$field->getName();
			}

			$childFieldsByName[$field->getName()]=$childFields;

			$position++;
		}
		$this->setChildFieldsByName($childFieldsByName);
		$this->setParentFieldsByName($parentFieldsByName);
	}


}
?>
