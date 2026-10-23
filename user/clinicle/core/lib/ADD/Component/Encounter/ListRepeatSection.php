<?php

class ADD_Component_Encounter_ListRepeatSection extends ADD_Component_Encounter_RepeatSection {
	public function menuSectionOpen($options) {
		$options->startElement("li");
		$options->startElement("select",array("name"=>$this->_page->getName()."-options","onchange"=>"window.location=get('".$this->_page->getName()."-options')"));
		$options->addTextElement("option","-select ".$this->_page->getSetting('label')."-",array("value"=>""));
	}
	public function menuSectionClose($options) {
		$options->endElement("select");
		$options->endElement("li");
	} 
	public function menuSectionOption($cer,$options) {
		$encLabel = $this->menuSectionLabel($cer);
		$eid = $cer->getId();
		$patientId = ADD::sys()->getPatientId();
		
		$uri = ADD_Util::uri($this->_basePath . "/" . $this->_availPages[0]->getName(),array("pid"=> $patientId,"eid"=> $eid));
		$attrs = array("value"=>htmlentities($uri));
		if(ADD::sys()->getParam('eid')==$eid) $attrs['selected']='selected';
		$options->addTextElement("option",$encLabel,$attrs);
	}
}