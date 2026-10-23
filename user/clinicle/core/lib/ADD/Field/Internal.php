<?php

class ADD_Field_Internal extends ADD_Field_Base {
	public $isValidated=false;
	protected $stored = false;
        function ignoreContainerTest() {
            return true;
        }
}
?>