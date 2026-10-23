<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ADD_Field_AddSelDiagButton extends ADD_Field_Base {
	function addMarkup($page) {
		if(ADD::sys()->hasState(ADD_System::READONLY)) return;
		$html = ADD::web()->getContent();
		$html->addTextElement("button",$this->getTitle(),array("ng-click"=>"addSelected()","class"=>"btn","type"=>"button"));
	}
	function isStored() {
		return false;
	}
}