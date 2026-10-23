<?php

class ADD_Field_Text_Date extends ADD_Field_Text {
	function finaliseField($page) {
		$this->setSetting('size',10);
	}
	function createPdfElement($rptField,$page,$currentX,$currentY) {
		$this->setSetting('size',15);
		return parent::createPdfElement($rptField,$page,$currentX,$currentY);
	}
	
	function beforeAddMarkup($page) {
		parent::beforeAddMarkup($page);
		$add = ADD::sys();
		
		if (!$add->hasState('readOnly') && !$this->isReadOnly() && !$this->getSetting('readonly')) $this->setSetting('datepicker',true);
		
		$this->addJsValidation("ValidateDate",$page);
	}
	
	function addMarkup($page) {
		parent::addMarkup($page);
		if(!$this->getSetting('datepicker')) return;
		$page->html()->addTextElement("button","today",array("class"=>"btn btn-small btn-info","style"=>"vertical-align:top","type"=>"button","onclick"=>"set('".$this->getName()."',today())"));
	}
}
?>