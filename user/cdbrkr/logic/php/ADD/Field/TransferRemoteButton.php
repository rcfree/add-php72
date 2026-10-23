<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ADD_Field_TransferRemoteButton extends ADD_Field_AbstractGridAdd {
	function initAttrs() {
		$attrs = parent::initAttrs();
		$patient = ADD::db('patient')->getPatient(ADD::sys()->getPatientId());
		$attrs['cle-patientident']=$patient['identifier'];
		return $attrs;
	}
	function addGridAddHtml($html) {
		$html->startElement("button",array("type"=>"button","class"=>"btn","ng-click"=>"transferItems(get('select_ward_spell'))"));
		$html->addHtml("<i class='icon-share-alt'></i>&nbsp;".$this->getTitle());
		$html->endElement("button");
		
		$ajaxLoader = new ADD_Content_Html();
		$ajaxLoader->startElement("p",array("ng-show"=>"loadingButton==true","class"=>"muted"));
		$ajaxLoader->addHtml($this->getSetting("transfer_message","Transferring spell.."));
		$ajaxLoader->addElement("img",array("src"=>"/module/clinicle/client/images/ajax-loader.gif"));
		$ajaxLoader->endElement("p");
		$html->addHtml($ajaxLoader);
			
		$html->addTextElement("div","{{buttonError}}",array("ng-show"=>"buttonError","ng-cloak"=>"","class"=>"alert alert-error"));
		$html->addTextElement("div","{{buttonWarning}}",array("ng-show"=>"buttonWarning","ng-cloak"=>"","class"=>"alert alert-warning"));
		$html->addTextElement("div","{{buttonMessage}}",array("ng-show"=>"buttonMessage","ng-cloak"=>"","class"=>"alert alert-success"));
		
	}
	public function isAvailable($page) {
		if(ADD::sys()->hasState(ADD_System::READONLY)) return false;
		if(ADD::sys()->hasState('locked')) return false;
		
		return true;
	}
	function getHtmlClass() {
		return "add-button";
	}
}