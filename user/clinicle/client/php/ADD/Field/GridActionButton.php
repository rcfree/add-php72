<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ADD_Field_GridActionButton extends ADD_Field_Button {
	
	protected function getLinkTitle($page) {
		$icon = $this->getSetting('icon');
		return ($icon ? "<i class='icon-".$icon." icon-white'></i>&nbsp;" : "").$this->getTitle();
	}
	
	public function isAvailable($page) {
		if(ADD::sys()->hasState(ADD_System::READONLY)) return false;
		if(ADD::sys()->hasState('locked')) return false;
		
		return true;
	}
	
	protected function getNgClick($page) {
		if (ADD::sys()->hasState('locked')) return null;
		if (ADD::sys()->hasState('readOnly')) return null;
		return "javascript:;";
	}
	
	protected function getLinkAttrs($page) {
		return array(
			"class"=>"btn btn-info",
			"type"=>"button"
		);
	}
}