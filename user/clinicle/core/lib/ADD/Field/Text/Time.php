<?php

class ADD_Field_Text_Time extends ADD_Field_Text {
	function finaliseField($page) {
		$this->setSetting('size',5);
	}
		
	function beforeAddMarkup($page) {
		parent::beforeAddMarkup($page);
		$add = ADD::sys();
		
		$this->addJQuery($page,'mask',"'99:99'");
		//$this->addJsValidation("validateDate",$page);
		if ($add->hasState('readOnly')) return;
		if ($this->isReadOnly() || $this->getSetting('viewonly')) return;
		$this->addJsValidation("ValidateTime",$page);
	}

}
?>