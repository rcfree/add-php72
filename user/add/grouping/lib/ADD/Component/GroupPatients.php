<?php

class ADD_Component_GroupPatients extends ADD_Component {
	public function generate() {
		$content = ADD::web()->getContent();
		$patients = ADD::db('group')->getPatientsInGroup(ADD::sys()->getParam('id'));
		$content->startElement("table",array("class"=>"table table-condensed table-striped"));
		$content->addTextElement("caption",count($patients)." ".ADD_Util::pluralize($patients,"patient"));
		$content->startElement("thead");
		$content->startElement("tr");
		$content->addTextElement("th","Identifier");
		$content->addTextElement("th","First name");
		$content->addTextElement("th","Last name");
		$content->addTextElement("th","Date of birth");
		$content->addTextElement("th","Recruitment status");
		$content->addTextElement("th","Sent consent form?");
		$content->addTextElement("th","Date sent");
		$content->endElement("tr");
		$content->endElement("thead");
		$content->startElement("tbody");
		foreach($patients as $patient) {
			$sentConsent = ADD_Util::val('pat_sent_db_consent',$patient);
			if($sentConsent=="Y") $sentConsent="Yes";
			if($sentConsent=="N") $sentConsent="Not interested";
			$content->startElement("tr");
			$content->startElement("td");
			$content->addTextElement("a",$patient['identifier'],array("href"=>ADD_Util::uri("",array("pid"=>$patient['id']))));
			$content->endElement("td");
			$content->addTextElement("td",$patient['first_name']);
			$content->addTextElement("td",$patient['last_name']);
			$content->addTextElement("td",$patient['date_of_birth']);
			$content->addTextElement("td",$patient['current_status']);
			$content->addTextElement("td",$sentConsent);
			$content->addTextElement("td",ADD_Util::val('date_sent',$patient));
			$content->endElement("tr");
		}
		$content->endElement("tbody");
		$content->endElement("table");
	}
	public function title(&$title) {
		$group = ADD::db('group')->getGroup(ADD::sys()->getParam('id'));
        $title="Patients in group '".$group['name'];
    }
   
	function expectedPermissions(&$expPerms) {
		$expPerms[]="view";
	}
	function menuPath(&$menuPath) {
		$menuPath="grouping/patients";
	}
	function menuParams(&$menuParams) {
	}
} 