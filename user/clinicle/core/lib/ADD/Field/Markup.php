<?php

class ADD_Field_Markup extends ADD_Field_Base {
	protected $stored = false;
	function addMarkup($page) {
	    $action = ADD::sys()->getParam("a");
        if($this->getSetting("show_on_edit") && $action != "edit") {
            return;
        }
		$page->html()->addHtml($this->getTitle());
	}
	function createPdfElement($rptField,$page,$currentX,$currentY) {
		return array(0,0);
	}
	function createPdfLabel($rptField,$page,$currentX,$currentY) {
		return 0;
	}
}
?>