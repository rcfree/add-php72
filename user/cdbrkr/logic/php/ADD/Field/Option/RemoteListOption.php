<?php

class ADD_Field_Option_RemoteListOption extends ADD_Field_Option_Standard {
	private $disabled=false;
    function isInitAfterPopulate() {
        return true;
    }
	function initOptions($field,$page) {
		$system = ADD::sys();
		$config = $system->getConfig()->assertSection("Codebreaker");
		if(!$config->getSetting('remote_disabled')) {
			return;
		}
		$this->disabled = true;
		ADD::web()->addWarning("Remote link to Codebreaker is not available");
		$field->setSetting('readonly',1);
		ADD::web()->addJavascript("jQuery('.content-codebreaker_transfer').hide();");
		ADD::web()->addJavascript("jQuery('#tabs-diag_tabs a[href=\"#manual_entry\"]').tab('show');");
    }
}
