<?php

class ADD_Field_Expression extends ADD_Field_Text {
	protected $_readOnly=true;
	public function beforeAddMarkup($page) {
		$this->defaultSetting("tooltip","calculated, so cannot be edited");
	}

	
}
?>