<?php

class ADD_Field_RadioButtonsHoriz extends ADD_Field_List {
    function startFieldHtml($page)
    {
    }

    function addMarkup($page) {
		$add = ADD::sys();
		$html = $page->html();
		$radioClass = "radio";
		$ocount = 1;
		foreach($this->getOptions()->getOptionList() as $value => $label) {
			$html->startElement("td",array("style"=>"vertical-align:middle;text-align:center"));
		
			$attrs = array(
				"name" =>$this->getName(),
				"type" =>"radio",
				"value"=>$value,
				"autocomplete"=>"no",
			);
			if ($add->hasState('readOnly') || $this->getSetting('viewonly')) $attrs['disabled']='disabled';
			if (count($this->getOptions())==$ocount) $radioClass=$radioClass." last";
			
			if (strlen($this->getValue())>0 && $this->getValue() ==$value) $attrs['checked']="checked";
			$html->addElement("input",$attrs);
			$html->endElement("td");
			$ocount++;
		}
	}
    function endFieldHtml($page)
    {
    }
}
?>