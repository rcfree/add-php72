<?php

class ADD_Field_Text_Number extends ADD_Field_Text {
	function finaliseField($page) {
		$this->defaultSetting('size',6);
	}
	function afterAddMarkup($page) {
		parent::afterAddMarkup($page);
		$this->addJsValidation("ValidateNumber",$page);
		$name = $this->getName();
	}
	function getInputType() {
		return "text";
	}
}
?>