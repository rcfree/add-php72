<?php
namespace ClinicLE\Options\HomeTool;

class InfoHomeTool extends AbstractHomeTool {
	
	public function createTool() {
		if(!ADD::sys()->getAccessManager()->isLoggedIn()) return;
		
		$html = ADD::web()->newContent();
		$pid = ADD::sys()->getPatientId();
		$pdb = ADD::db('patient');
		if(!$pid) {
			return null;
		}
		if (!$pdb->doesPatientExist($pid)) throw new ADD_PatientException("A patient with id ".$pid." was not found. Please contact the administrator.");
		$person = $pdb->getPatient($pid);
		$pname = $person['first_name']." ".$person['last_name'];
				
		$html->startElement("ul");
		$html->addTextElement("li","You have currently selected a patient (<b>$pname</b>)");
		$html->addTextElement("li","Basic patient details and options to view Demographics are displayed above.");
		$html->addTextElement("li","Clinical research data and other patient data is available in the left hand menu.");
		$html->endElement("ul");
		return $html;
	}
	public function toolTitle() {
		return "Information";
	}
	public function toolWeight() {
		return 1;
	}
	public function isAvailable() {
		$pid = ADD::sys()->getPatientId();
		$pdb = ADD::db('patient');
		if(!$pid) {
			return false;
		}
		if(!ADD::sys()->getAccessManager()->isLoggedIn()) return false;
		return true;
	}
	public function toolClass() {}
}