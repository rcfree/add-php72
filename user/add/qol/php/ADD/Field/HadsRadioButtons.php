<?php

class ADD_Field_HadsRadioButtons extends ADD_Field_List {
	function addMarkup($page) {
		$add = ADD::sys();
		$html = $page->html();
		$radioClass = "radio";
		$ocount = 1;
		
		$scoreColAttrs = array("style"=>"width:2.5em");
		
		if ($this->getSetting('rtl')) {
			$html->startElement("table",array("style"=>"width:100%;margin-bottom:1em"));
		}
		else {
			$html->startElement("table",array("style"=>"width:100%;margin-bottom:1em;margin-right:2em"));
		}
		$html->startElement("thead");
		$html->startElement("tr");
		if (!$this->getSetting('rtl')) {
			$html->addTextElement("td","",$scoreColAttrs);
			$html->addTextElement("td","",$scoreColAttrs);
		}
		$html->addTextElement("td",$this->getTitle(),array("style"=>"font-weight:bold"));
		if ($this->getSetting('rtl')) {
			$html->addTextElement("td","",$scoreColAttrs);
			$html->addTextElement("td","",$scoreColAttrs);
		}
		$html->endElement("tr");
		$html->endElement("thead");
		
		$html->startElement("tbody");
                
		foreach($this->getOptions()->getOptionList() as $value => $label) {
			$html->startElement("tr");
			if ($this->getSetting('rtl')) {
				$html->addTextElement("td",$label,array("style"=>"padding-left:1em"));
			}
			if ($this->getSetting('d_value')) {
				$html->addTextElement("td","",$scoreColAttrs);
			}
			
			$html->startElement("td",$scoreColAttrs + array("style"=>"vertical-align:middle;text-align:center"));
		
			$attrs = array(
				"name" =>$this->getName(),
				"type" =>"radio",
				"value"=>$value,
				"autocomplete"=>"no",
			);
			if ($add->hasState('readOnly') || $this->getSetting('viewonly')) $attrs['disabled']='disabled';
			if ($this->getOptions()->countOptions()==$ocount) $radioClass=$radioClass." last";
			
			if (strlen($this->getValue())>0 && $this->getValue() ==$value) $attrs['checked']="checked";
			$html->addTextElement("span","{$value}&nbsp;");
			$html->addElement("input",$attrs);
			$html->endElement("td");
			if (!$this->getSetting('d_value')) {
				$html->addTextElement("td","",$scoreColAttrs);
			}
			if (!$this->getSetting('rtl')) {
				$html->addTextElement("td",$label,array("style"=>"padding-left:1em"));
			}
			$html->endElement("tr");
			$ocount++;
		}
		$html->endElement("tbody");
		$html->endElement("table");
		
		$html->startElement("div",array("style"=>"display:none"));
		$emptyAttrs = array("type"=>"radio","value"=>"","name"=>$this->getName());
		if(strlen($this->getValue())==0) $emptyAttrs['checked']='checked';
		$html->addElement("input",$emptyAttrs);
		$html->endElement("div");
	}
}
?>