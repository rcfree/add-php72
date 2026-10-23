<?php

abstract class ADD_Field_Decorator extends ADD_Field_Base {
	private $decoratorField;
	abstract function getDecoratorType();

	function finaliseField($page) {
		$fieldType = $this->getDecoratorType();
		$this->decoratorField = new $fieldType($this->getFieldData());
		$this->decoratorField->finaliseField($page);
	}
	
	function createMarkup($page) {
		$this->decoratorField->createMarkup($page);
	}
	
	function populateValue($values, $page, $ignoreNew=false) {
		$this->decoratorField->populateValue($values, $page, $ignoreNew);
	}
	
	function getDecoratorField() {
		return $this->decoratorField;
	}
	
	
	function getValue() {
		return $this->decoratorField->getValue();
	}
	
	function setValue($value) {
		return $this->decoratorField->setValue($value);
	}
}