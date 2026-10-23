<?php

class ADD_Field_Text_AutoNumber extends ADD_Field_Text {
	protected $_readOnly=true;
	function populateValue($values, $page, $ignoreNew=false) {
		$add = ADD::sys();	
		if ($add->hasState('new')) {
			$edb = ADD::db('encounter');
			$id = $edb->incrementId($page->getName()."-".$this->getName());
			$this->setValue($id);
		}
		else {
			parent::populateValue($values,$page,$ignoreNew);
		}
	}
	
	public function beforeAddMarkup($page) {
		$this->defaultSetting("tooltip","is automatically generated");
		$this->defaultSetting("size",3);
	}
}
?>