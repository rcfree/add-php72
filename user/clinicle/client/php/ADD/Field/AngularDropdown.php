<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ADD_Field_AngularDropdown extends ADD_Field_List_Dropdown {
	function startFieldHtml($page) {
		$page->html()->startElement("div",array("id"=>$this->getName(),"ng-controller"=>"FieldController","cle-pid"=>ADD::sys()->getPatientId(),"cle-pim"=>$this->assertSetting('pim'),"cle-field"=>$this->getName()));
	}
	function getDropdownAttributes() {
		$attrs = parent::getDropdownAttributes();
		$attrs["ng-init"]=$this->getName()."='".$this->getValue()."'";
		return $attrs;
	}
	function endFieldHtml($page) {
		$page->html()->endElement("div");
	}
}
