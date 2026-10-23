<?php

class ADD_Client_Action_LinkToPatientButton implements ADD_Client_Action_GridRowActionInterface {
	function __construct(ADD_Page $rowPage) {
		$this->_rowPage = $rowPage;
	}
	function isAvailable() {
		return $this->_rowPage->getPermission('move');
	}
	function outputMarkup() {
		$html = new ADD_Content_Html();
		$html->addTextElement("a", "<i class=\"icon-search icon-white\"></i>&nbsp;Match patient..", array("class"=>"btn btn-small btn-info","href" => "javascript:;", "ng-click"=>\ClinicLE\Client\Util\AngularJS::createCustomCall("showLinkModal",array("row.id"))));
		return $html;
	}
	function getColumnHeading() {
		return "";
	}
	function afterOutputMarkup(ADD_Content_Html $html) {
		$modal = new ADD_Content_Html_ModalHelper("Match patient to hospital database","holding-link",array("class"=>"holding-link","style"=>"width:700px"),array("Close"=>array("ng-click"=>\ClinicLE\Client\Util\AngularJS::createCustomCall("closeLinkModal",array()))));
		$mbody = $modal->getModalBody();
		
		$mbody->startElement("div",array("class"=>"well"));
		$mbody->addTextElement("p","<b>Name:</b>{{control.data.linkFirstName}}&nbsp;{{control.data.linkLastName}}&nbsp;&nbsp;
		<b>DOB:</b>{{control.data.linkDob}}&nbsp;&nbsp;<b>S number:</b>{{control.data.linkId}}");
		$mbody->addTextElement("div","Change identifier/name and date of birth to find this patient. Then click the 'Choose patient' button");
		$mbody->endElement("div");
		
		$mbody->startElement("div");
		$mbody->addTextElement("label","Search patients using:",array("class"=>"control-label"));
		$mbody->startElement("label",array("class"=>"radio inline"));
		$mbody->addHtml("Identifier");
		
		$mbody->addElement("input",array("value"=>1,"type"=>"radio","ng-model"=>"control.fields.searchType","name"=>"search_type"));
		$mbody->endElement("label");
		$mbody->startElement("label",array("class"=>"radio inline"));
		$mbody->addHtml("Name/DOB");
		
		$mbody->addElement("input",array("value"=>2,"type"=>"radio","ng-model"=>"control.fields.searchType","name"=>"search_type"));
		$mbody->endElement("label");
		$mbody->endElement("div");
		
		
		$mbody->startElement("div",array("ng-show"=>"control.fields.searchType==1"));
		$mbody->addTextElement("label","S number/Hospital number");
		$mbody->addElement("input",array("type"=>"text","ng-model"=>"control.fields.searchId"));
		$mbody->endElement("div");
		
		$mbody->startElement("form",array("ng-show"=>"control.fields.searchType==2","class"=>"form-inline"));
		$mbody->startElement("label",array("radio class"));
		$mbody->addHtml("First name:");
		$mbody->addElement("input",array("type"=>"text","ng-model"=>"control.fields.searchFirstName","class"=>"span3"));
		$mbody->endElement("label");
		$mbody->startElement("label",array("radio class"));
		$mbody->addHtml("&nbsp;Last name:");
		$mbody->addElement("input",array("type"=>"text","ng-model"=>"control.fields.searchLastName","class"=>"span3"));
		$mbody->endElement("label");
		$mbody->addHtml("<br/>");
		$mbody->startElement("label",array("radio class"));
		$mbody->addHtml("&nbsp;Date of Birth:");
		$mbody->addElement("input",array("type"=>"text","ng-model"=>"control.fields.searchDob","class"=>"span2"));
		$mbody->endElement("label");
		
		$mbody->endElement("form");
		$mbody->addTextElement("button","<i class='icon-search'></i>&nbspSearch",array("class"=>"btn",
            "type"=>"button","ng-click"=>\ClinicLE\Client\Util\AngularJS::createCustomCall("searchPatients",array())));
		$mbody->addTextElement("div","",array("class"=>"alert alert-error","ng-show"=>"control.data.patientError",
            "ng-bind-html"=>"control.data.patientError"));
		
		$ajaxLoader = new ADD_Content_Html();
		$ajaxLoader->startElement("p",array("ng-show"=>"loadingPatients==true","class"=>"muted"));
		$ajaxLoader->addHtml("Searching patients&nbsp;");
		$ajaxLoader->addElement("img",array("src"=>"/module/clinicle/client/images/ajax-loader.gif"));
		$ajaxLoader->endElement("p");
		$mbody->addHtml($ajaxLoader);
			
		$psTable = new ADD_Content_Html_TableHelper(null,array("ng-show"=>"control.data.patients.length>0 && control.data.loadingPatients==false"));
		$psTable->setHeadings(array("Patient ID","Gender","Last name","First name","Date of Birth","Address line 1","Postcode"));
		$patientLink = new ADD_Content_Html();
        $patientLink->addTextElement("button","Choose patient..",array("class"=>'btn btn-mini',"type"=>'button', "ng-click"=>\ClinicLE\Client\Util\AngularJS::createCustomCall("linkToPatient",array("patient.main_pat_id"))));
        $psTable->addRow(array(
			"data"=>array("{{patient.main_pat_id}}","{{patient.gender}}","{{patient.last_name}}","{{patient.first_name}}","{{patient.dob}}","{{patient.pat_addr1}}","{{patient.postcode}}",$patientLink->output()),
			"attrs"=>array("ng-repeat"=>"patient in control.data.patients")
		));
		$mbody->addHtml($psTable->create());
		
		$html->addHtml($modal->create());
		//$html->endElement("div");
		//$html->endElement("div");
	}
}
?>