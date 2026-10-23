<?php

namespace ClinicLE\Options\HomeTool;
use CLE;
use ADD_Content_Html;

class RecentPatientsHomeTool extends AbstractHomeTool {
	public function createTool() {
		$add = CLE::sys();
		if($add->config('RecentPatientsHomeTool.disable')) return false;
		
		$html = new ADD_Content_Html();
		$adb = CLE::db('audit');
		$pdb = CLE::db('patient');
		$am = CLE::sys()->getAccessManager();
		$pid = CLE::sys()->getParam('pid');
		if($pid) {
			CLE::db('audit')->insert("recent_patient",array("patient_id"=>$pid,"username"=>$am->getUser()->username),array("timestamp"));
		}
		$auditPage = $adb->fetchPage("select distinct patient_id from recent_patient where username=? order by timestamp desc",array($am->getUser()->username),1,10);
		if(count($auditPage)==0) {
			$html->addTextElement("p","Patients you access from now on will appear here so you can quickly choose them.",array("class"=>"muted"));
			return $html;
		}
		else {
			$html->addTextElement("p","Below are the patients you have recently accessed. Click on a name below to select them.");
		}
		$html->startElement("ul",array("class"=>"nav nav-pills"));
		
		foreach($auditPage as $auditRow) {
			$patient = $pdb->getPatient($auditRow['patient_id']);
			if(!$patient) continue;
			$pattrs = array("href"=>"/?pid=".$patient['id']);
			$lattrs = array();
			if($patient['id']==CLE::sys()->getPatientId()) $lattrs['class']="active";
			$html->startElement("li",$lattrs);
			$html->addTextElement("a",$patient['last_name'].", ".$patient['first_name']." (".$patient['identifier'].")",$pattrs);
			$html->endElement("li");
		}
		
		$html->endElement("ul");
		return $html;
	}
	public function toolTitle() {
		return "Recent Patients";
	}
	public function toolWeight() {
		return 1;
	}
	public function isAvailable() {
		if(!CLE::sys()->getAccessManager()->isLoggedIn()) return false;
		return true;
	}
	public function toolClass() {}
}