<?php

class ADD_Field_RadioItem extends ADD_Field_List {
	function finaliseField($page) { }
    function beforePopulateFields($page) {
		$form = $page->getComponent('Form');
		$parentField = $form->getParentField($this);
		if(!$parentField) throw new ADD_FieldException("No parent RadioGroup found for field '".$this->getName()."'");
		$this->setOptions($parentField->getOptions());
		$this->setFinalised(true);
	} 
	function createMarkup($page) {
		$add = ADD::sys();
		$options = $this->getOptions();
		
		$html = $page->html();
		$html->startElement("tr");
		$question = $this->getSetting('question');
		$attrs = $question ? array("style"=>"width:2em") : array("style"=>"width:0px");
		$html->addTextElement("td",$question,$attrs);
                $html->startElement("td");
                $cname = $this->getName();
                $html->startElement("div",array("class"=>"control-group"),$cname."_start");
                $this->addTitle($page);
                $html->addTextElement("label",  $this->getTitle(),array("class"=>"control-label","style"=>"font-weight:normal"),$cname."_label");
                $html->endElement("div");
                $html->endElement("td");
		foreach($options->getOptionList() as $value => $label) {
			$attrs = array("type"=>"radio","value"=>$value,"name"=>$this->getName());
			if (strlen($this->getValue())>0 && $this->getValue() ==$value) $attrs['checked']="checked";
            if ($add->hasState('readOnly')) $attrs['readonly']='readonly'; 
			$html->startElement("td",array("style"=>"text-align:center;width:6em"));
			$html->addElement("input",$attrs);
			if ($this->getSetting('show_value')) $html->addHtml("&nbsp;$value");
			$html->endElement("td");
		}
		$html->endElement("tr");
	}
	function addTitle($page) {
		$cname = $this->getName();
		$html = $page->html();
		
		$html->addTextElement("label",$this->getTitle(),array("class"=>"control-label"),$cname."_label");
	}
		
}