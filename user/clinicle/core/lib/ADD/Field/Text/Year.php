<?php

class ADD_Field_Text_Year extends ADD_Field_Text {
	
    function beforeAddMarkup($page) {
        $add = ADD::sys();
        $this->setSetting('size',4);
		$this->addJsValidation("ValidateYear",$page);
    }
}
?>