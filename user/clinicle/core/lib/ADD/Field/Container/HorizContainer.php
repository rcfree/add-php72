<?php

class ADD_Field_Container_HorizContainer extends ADD_Field_Container {
	function startFieldHtml($page) {
		$name = $this->getName();
		$add = ADD::sys();
		$classType = $this->getClassType();
		$contentClass = substr($this->generateJQueryBlockSel($page),1);
		$attrs = array(
			"id" => $name,
			"name" => $name,
			"style" => "display:inline-block;width:100%;".$this->getSetting('style'),
			"class"=>"field $classType $contentClass"
		);
		if($this->getSetting('class')) {
			$attrs['class'].=" ".$this->getSetting('class');
		}
		$page->html()->startElement("div",$attrs,$name."_start");
	}
	function beforeAddChildHtml($childField,$page) {
		$marginRight = $childField->getSetting('margin_right','1em');
		$width = $childField->getSetting('width','auto');
		if ($this->getSetting('segments')) {
			$width = ((100/$this->getSetting('segments'))-2)."%";
		}
		
		$style = "display:inline-block;margin-right:$marginRight;float:left";
		$page->html()->startElement('div',array('style'=>$style.';width:'.$width));
		if ($this->getSetting('arrow_prefix')) {
			$page->html()->addTextElement("div","&rarr;",array("style"=>$style.";width:auto"));
		}
	}
	function afterAddChildHtml($childField,$page) {
		$page->html()->endElement('div');
	}	
}
?>
