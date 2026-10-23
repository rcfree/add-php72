<?php

class ADD_Field_EmbeddedForm extends ADD_Field_Base {
	function isStored() {
		return false;
	}
	function addMarkup($page) {
		$page->html()->startElement("div",array("id"=>$this->getName(),"ng-controller"=>"EmbeddedFormController","cle-parentid"=>ADD::sys()->getParam('eid'),"cle-pid"=>ADD::sys()->getPatientId(),"cle-eid"=>ADD::sys()->getParam('eid'),"cle-readonly"=>$this->getSetting('readonly'),"cle-pim"=>$this->getSetting('use_page'),"cle-refresh"=>$this->getSetting('refresh')));
		$page->html()->addTextElement("div","",array("class"=>"form-content"));
		$page->html()->endElement("div");
		//$page->addJavascript("angular.bootstrap($('#".$this->getName()."-app'), ['addFramework']);");
	}
}
?>