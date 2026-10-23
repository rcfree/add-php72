<?php

class ADD_Field_UserEmail extends ADD_Field_Text {
	function beforePopulateFields($page) {
		if(!ADD::sys()->hasState("new")) {
			return;
		}
		$this->setDefault("\"".ADD::sys()->getAccessManager()->getUser()->email."\"");
	}
}