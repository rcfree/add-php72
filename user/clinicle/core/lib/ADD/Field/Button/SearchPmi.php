<?php

class ADD_Field_Button_SearchPmi extends ADD_Field_Button {
	protected function getOnClick($page) {
		return "patientLookup()";
	}
	
	function afterAddFields($page) {
		$html = $page->html();
		$html->startElement("div",array("id"=>"patient_dialog","class"=>"modal hide fade","style"=>"width:800px"));
		$html->startElement("div",array("class"=>"modal-header"));
		$html->addTextElement("button","&times;",array("type"=>"button","class"=>"close","data-dismiss"=>"modal","aria-hidden"=>"true"));
		$html->addTextElement("h3","Search hospital patients");
		$html->endElement("div");
		$html->startElement("div", array("class"=>"modal-body"));
		$html->endElement("div");
		$html->startElement("div",array("class"=>"modal-footer"));
		$html->addTextElement("a","Close",array("href"=>"javascript:;","onclick"=>"jQuery('#patient_dialog').modal('hide')","class"=>"btn","id"=>"close-button"));
		
		$html->endElement("div");
		$html->endElement("div");
	}
	
	
}
?>