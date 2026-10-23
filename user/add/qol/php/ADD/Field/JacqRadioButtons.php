<?php

class ADD_Field_JacqRadioButtons extends ADD_Field_List {
	function addMarkup($page) {
		$add = ADD::sys();
		$html = $page->html();
		$radioClass = "radio";
		$ocount = 1;
		$html->startElement("div",array("style"=>"width:100%"));
		$html->addTextElement("div",$this->getSetting('question'),array("style"=>"float:left;width:2em;clear:left"));
		$html->addTextElement("div",$this->getTitle().'<span class="required">*</span>',array("style"=>"float:left;width:40%;margin-right:3em;font-size:14px"));
		$html->startElement("div",array("style"=>"float:left;margin-bottom:1.5em"));
		foreach($this->getOptions()->getOptionList() as $value => $label) {
			
			$attrs = array(
				"name" =>$this->getName(),
				"type" =>"radio",
				"value"=>$value,
				"autocomplete"=>"no",
			);
			if ($add->hasState('readOnly') || $this->getSetting('viewonly')) $attrs['disabled']='disabled';
			if ($this->getOptions()->countOptions()==$ocount) $radioClass=$radioClass." last";
			
			if (strlen($this->getValue())>0 && $this->getValue() ==$value) $attrs['checked']="checked";
			$html->startElement("label",array("class"=>$radioClass));
			
			$html->addElement("input",$attrs);
			$html->addTextElement("span","{$value}&nbsp;&nbsp;&nbsp;&nbsp;");
			
			$html->addHtml($label);
			$html->endElement("label");
			$ocount++;
		}
		$html->endElement("div");
		$html->endElement("div");
	}
}
?>