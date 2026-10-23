<?php

class ADD_Client_Action_PrimaryDiagnosis implements ADD_Client_Action_GridRowActionInterface {
	function __construct(ADD_Page $rowPage) {
		$this->_rowPage = $rowPage;
	}
	function isAvailable() {
		return $this->_rowPage->getPermission('edit');
	}
	
	function outputMarkup() {
		$html = new ADD_Content_Html();
		if(ADD::sys()->hasState(ADD_System::READONLY)) {
			$html->addTextElement("a", "{{ row.primary_diagnosis.value == 1 ? '1' : '2' }}&ordm;&nbsp;&Delta;", array("class"=>"ignoredirty","ng-class"=>"row.primary_diagnosis.value == 1 ? 'btn btn-danger disabled' : 'btn btn-success disabled'"));
		}
		else {
			$html->addTextElement("a", "{{ row.primary_diagnosis.value == 1 ? '1' : '2' }}&ordm;&nbsp;&Delta;", array("class"=>"ignoredirty","ng-class"=>"row.primary_diagnosis.value == 1 ? 'btn btn-danger' : 'btn btn-success'","href" => "javascript:;","ng-click"=>$this->getNgClick()));
		}	
		return $html;
	}
	function getColumnHeading() {
		return "";
	}
	function afterOutputMarkup(ADD_Content_Html $html) {
		
	}
	function getNgClick() {
		return \ClinicLE\Client\Util\AngularJS::createCustomCall("primary",array("row.id","row.primary_diagnosis
		.value"));
	}
}
?>
