<?php

class ADD_Field_Container_TabContainer extends ADD_Field_Container {
	function startFieldHtml($page) {
		$form = $page->getComponent('Form');
		$childFields = $form->getChildFields($this);
		$html = $page->html();
		$html->startElement("div",array("class"=>"tabbable content-".$this->getName()),$this->getName()."_start");
		$html->startElement("ul",array("class"=>"nav nav-tabs","id"=>"tabs-".$this->getName()));
		$firstTab = true;
		
		$tabFields = array();
		foreach($childFields as $childField) {
			if (get_class($childField)!="ADD_Field_Container_Tab") continue;
			$tabFields[]=$childField;
		}
		
		$tabCount = 0;
	
		foreach($tabFields as $tabField) {
			$tabField->setPrevTab($tabCount>0 ? $childFields[$tabCount-1]->getName() : null);
			$tabField->setNextTab($tabCount<count($childFields)-1 ? $childFields[$tabCount+1]->getName() : null);
			
			$liAttrs = array('class'=>"content-".$tabField->getName()." no-print");
			if ($firstTab) {
				$liAttrs['class'].=" active";
				$this->setSetting('active-tab',$tabFields[0]->getName());
			}
			
			$html->startElement("li",$liAttrs);
			$html->addTextElement("a",$tabField->getTitle(),array("href"=>"#".$tabField->getName(),"data-toggle"=>"tab"));
			$html->endElement("li");
			$firstTab = false;
			$tabCount++;
		}
		$html->endElement("ul");
		$html->startElement("div",array("class"=>"tab-content"));	
	}
	function endFieldHtml($page) {
		$html = $page->html();
		$html->endElement("div");
		$html->endElement("div");
		
		if ($page->hasComponent('RequiredFields')) {
			$formEle = $html->get('form_start');
			$formEle['attrs']['onsubmit']="return ADD.Form.isValid(null,true);";
			$html->set('form_start',$formEle);
		}
	}
}
?>