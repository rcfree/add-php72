<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ADD_Field_ClearFieldButton extends ADD_Field_Base {
	function addMarkup($page) {
		if(ADD::sys()->hasState(ADD_System::READONLY)) return;
		$html = ADD::web()->getContent();
		$html->addTextElement("label","&nbsp;",array(),$this->getName()."_label");
		$clearField = $this->getSetting('field');
		$html->startElement("button",array("type"=>"button","class"=>"btn btn-mini","onclick"=>"set('__{$clearField}_ac','');set('{$clearField}','');"));
		$html->addHtml("<i class='icon-remove'></i>");
		$html->endElement("button");
	}
	function afterAllGenerate($page) {
	}
	function isTitleHidden() {
		return false;
	}
}