<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ADD_Field_GridAddSelection extends ADD_Field_AbstractGridAdd {
	function addGridAddHtml($html) {
		$html->addTextElement("label",$this->getTitle());
		$html->startElement("select",array("style"=>"width:".$this->getSetting('select_size','12em'),"ng-model"=>$this->getName()));
		$html->addTextElement("option",$this->getSetting('empty_option'),array("value"=>""));
		foreach($this->getFieldData('Options') as $value => $label) {
			$html->addTextElement("option",$label,array("value"=>$value));
		}
		$html->endElement("select");
		$html->startElement("button",array("type"=>"button","class"=>"btn","ng-click"=>"addItem(".$this->getName().")"));
		$html->addHtml("<i class='icon-plus'></i>&nbsp;Add selected..");
		$html->endElement("button");
	}
}