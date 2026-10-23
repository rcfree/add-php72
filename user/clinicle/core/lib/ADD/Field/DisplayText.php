<?php

class ADD_Field_DisplayText extends ADD_Field_Hidden {
	private $dispField = null;
	private $valueField = null;
	function isReadOnly() { return true; }
	function addMarkup($page) {
		$html = $page->html();
		$dispTitle = $this->getTitle();
		$html->addTextElement("label",$this->getTitle(),array(),$this->getName()."_label");
		$value = $this->getValue();
		if (ADD_Util::isEmpty($value)) $value="-not entered-";
		$html->addTextElement("p",$value,array("class"=>"muted"));
	}
}
?>