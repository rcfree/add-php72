<?php

class ADD_Field_Hidden extends ADD_Field_Base {
	function addMarkup($page) {
		$opts = array(
			"type"=>"hidden",
			"name"=>$this->getName(),
			"value"=>$this->getValue(),
			"id"=>"edit-".str_replace("_","-",$this->getName())
		);
		$page->html()->addElement("input",$opts);
	}
	function afterPopulateFields($page) {
		$this->_setHiddenFields($page);
	}
	function beforeSubmit(&$values,$page) {
		$this->_setHiddenFields($page);
	}
}
?>