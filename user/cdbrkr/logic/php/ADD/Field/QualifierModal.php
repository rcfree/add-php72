<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ADD_Field_QualifierModal extends ADD_Field_Base {
	function addMarkup($page) {
		$html = ADD::web()->getContent();
		$html->startElement("div",array("class"=>"modal","cle-refresh"=>"comorb_results_grid;diagnoses_matrix_set","cle-pid"=>ADD::sys()->getPatientId(),"cle-parentid"=>ADD::sys()->getParam('eid'),"id"=>"qualifier-modal","style"=>"display:none","ng-controller"=>"QualController","cle-pim"=>$this->assertSetting('pim')));
		$html->addTextElement("label","Select condition severity");
		$html->startElement("select",array("name"=>"uhl_qualifier","ng-model"=>"control.fields.uhl_qualifier","ng-change"=>"addDiagnosis(qualifier.value)"));
		$html->addTextElement("option","- select qualifier -",array("value"=>""));
		$html->addTextElement("option","{{qualifier.title}}",array("ng-value"=>"qualifier.value","ng-repeat"=>"qualifier in control.data.qualifiers"));
		$html->endElement("select");
		$html->endElement("div");
	}
	function isStored() {
		return false;
	}
}
