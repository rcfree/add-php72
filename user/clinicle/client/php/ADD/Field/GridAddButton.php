<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


class ADD_Field_GridAddButton extends ADD_Field_AbstractGridAdd {
	function addGridAddHtml($html) {
		$html->startElement("button",array("type"=>"button","class"=>"btn","ng-click"=>$this->getNgClick()));
		$html->addHtml("<i class='".$this->getSetting('icon','icon-plus')."'></i>&nbsp;".$this->getTitle());
		$html->endElement("button");
	}
	public function isAvailable($page) {
		if(ADD::sys()->hasState(ADD_System::READONLY)) return false;
		if(ADD::sys()->hasState('locked')) return false;
		if($this->getSetting('use_page')) {
			$pim = ADD::sys()->getPageFromPim($this->getSetting('use_page'));
			return $pim->getPermission("create");
		}
		return true;
	}
	function getHtmlClass() {
		return "add-button content-".$this->getName();
	}
    function getNgClick() {
        return "addItem()";
    }
}