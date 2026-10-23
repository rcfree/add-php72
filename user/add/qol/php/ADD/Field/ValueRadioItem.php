<?php

class ADD_Field_ValueRadioItem extends ADD_Field_List {
    function isStored() {
        return false;
    }
	function finaliseField($page) { }
    function beforePopulateFields($page) {
		$form = $page->getComponent('Form');
		$parentField = $form->getParentField($this);
		if(!$parentField) throw new ADD_FieldException("No parent RadioGroup found for field '".$this->getName()."'");
		$this->setOptions($parentField->getOptions());
		$this->setFinalised(true);
	} 
	function createMarkup($page) {
		$options = $this->getOptions();
		
		$html = $page->html();
		$html->startElement("tr");
		$attrs = array("style"=>"width:0px");
        $html->addTextElement("td","",$attrs);
		$html->addTextElement("td","",$attrs);

		foreach($options->getOptionList() as $value => $label) {
			$html->addTextElement("td",$value,array("style"=>"text-align:center;width:6em"));
		}
		$html->endElement("tr");
	}
	function addTitle($page) {
		
	}
		
}