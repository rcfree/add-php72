<?php

class ADD_Field_Text_DateTime extends ADD_Field_Text {
	function finaliseField($page) {
		$this->setSetting('size',16);
	}
			
	function afterAddMarkup($page) {
		$this->addJQuery($page,'mask',"'99/99/9999 99:99'");
	}
}
?>