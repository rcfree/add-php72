<?php
use ClinicLE\Common\Util;
class ADD_Page_StandardForm extends ADD_Page_AbstractContent {
	function isWebContent() {
		return false;
	}
	function createMenuPath() {
		return "client/standard-form";
	}

    function getPanelName() {
        return "form_main";
    }
}
