<?php



class ADD_Component_Encounter_StudyRepeatSection extends ADD_Component_Encounter_RepeatSection {
	protected $noContact;
	
	public function menuSectionLabel($sectionEr) {
		$encLabel = null;
		$add = ADD::sys();
		
		$labelEr = ADD::sys()->getEncounterManager()->child($sectionEr,$this->labelPage->getSetting('encounter'));
		$labelEr->retrieve();
		$studyName = $labelEr->getObsText("study",$this->labelPage);
		$encLabel = $studyName;
		if($labelEr->getObsValue("study_no")) {
			$encLabel.= " (" . $labelEr->getObsValue("study_no").")";
		}
		$status = $labelEr->getObsText(ADD_Constants_Research::CURRENT_STATUS_FIELD,$this->labelPage);
		$statusVal =  $labelEr->getObsValue(ADD_Constants_Research::CURRENT_STATUS_FIELD);
		$smallLabel = "";
		if($status) { 
			$smallLabel.= $status;
			$startDate="";
			if($statusVal==3) {
				$startDate = $labelEr->getObsValue("enrol_date");
			}
			else {
				$startDate = $labelEr->getObsValue("end_date");
			}
			if($startDate) $smallLabel.=" ($startDate)";
		}
		else {
			$smallLabel.="no study status";
		}
		$encLabel.="<p style='padding-left:0.5em;margin:0;color:black'>$smallLabel</p>";

		return $encLabel;
	}
	
	public function menu($options) {
		$add = ADD::sys();
		$this->messages = array(
			ADD_Constants_Research::PATIENT_REQUEST_NO_CONTACT=>array("Patient requests no contact",true),
			ADD_Constants_Research::PATIENT_UNAVAILABLE_RECRUIT=>array("Patient not available",false),
			ADD_Constants_Research::PATIENT_UNSUITABLE_RECRUIT=>array("Patient unsuitable for research",false),
			ADD_Constants_Research::PATIENT_ENROL_BUT_NOT_INTERESTED=>array("Patient enrolled but not interested",false),
			ADD_Constants_Research::PATIENT_DECEASED=>array("Patient is deceased",true)
		);
		$em = $add->getEncounterManager();
		$pid = $add->getPatientId();
		
		$this->noContact = ADD::sys()->getPageInModule('research','general')->getComponent('Encounter')->doesPatientRequestNoContact();
		$researchPage = ADD::sys()->getPageInModule('research','research');
		$researchComp = $add->getPageInModule('research','research')->getComponent('Encounter');
		$research = $em->domain("res",$pid);
		
		$dbConsents = $em->sectionsByPatientAndType($pid,"res-general");
		
		if($researchComp->hasNoConsents($dbConsents,$options)) {
			if ($this->noContact!=ADD_Constants_Research::PATIENT_REQUEST_NO_CONTACT) {
				$options->addTextElement("p","<i class='icon icon-warning-sign'></i>&nbsp;<b>Not able to manage studies</b><br/>Patient has no database consents",array("class"=>"text text-warning"));
			}
			return;
		}
		$latestConsent = $researchComp->getLatestConsentRecord($dbConsents);
		if($latestConsent->getObsValue(ADD_Constants_Research::ADD_TO_RECRUIT_DB_FIELD)!="Y") {
			$options->addTextElement("p","<i class='icon icon-warning-sign'></i>&nbsp;<b>Not able to manage studies</b><br/>No consent to be added to database",array("class"=>"text text-warning"));
			return;
		}
		$message = ADD_Util::val($this->noContact,$this->messages);
		$this->noLinks = $message[1];
		
		parent::menu($options);
	}
	
	public function menuSectionAdd($options) {
		$startText = "<b>Only manage existing studies</b>";
		
		if ($this->noContact) {
			$addMessage = ADD_Util::val($this->noContact,$this->messages);
			if($addMessage[1]) $startText="<b>Not able to manage studies</b>";
			if($addMessage) {
				$options->addTextElement("p","<i class='icon icon-warning-sign'></i>&nbsp;$startText<br/> ".$addMessage[0],array("class"=>"text text-warning"));			
			}
			return;
		}
		$add = ADD::sys();
		$page = $this->_page;
		$ppage = $page -> getParentPage();
		$form = $this->labelPage->getComponent('Form');
		$option = new ADD_Field_Option_Studies();
		
		$field = new ADD_Field_List_Dropdown(array("Field"=>"study"));
		$field->setOptions($option);
		
		$option->initOptions($field, $page);
		if($option->countOptions()==1) {
			$options->addTextElement("span","Patient added to ALL studies",array("class"=>"label label-warning"));
			return;
		}
	
		$field->setValue(null);
		$label = $page -> getSetting('label');
		
		$basePath = $add -> config('site_path') . "/" . $ppage -> getName() . "/" . $page -> getName();
		
		$field->setTitle(null);
		$field->setSetting('size',10);
		$form->setup();
		
		$options->startElement("form",array("action"=>"/".$page->createMenuPath(),"class"=>"form-inline","method"=>"GET","id"=>"study-form","style"=>"padding-top:0.5em;margin-bottom:0px","onsubmit"=>"return false","ng-submit"=>"save
		(\$event)"));
		$html = new ADD_Content_Html();
		$options->addHtml($field->createHtml($html,$page));
		$options->addElement("input",array("name"=>"pid","value"=>$add->getPatientId(),"type"=>"hidden"));
		$options->addElement("input",array("name"=>"t","value"=>ADD_Util::token(),"type"=>"hidden"));
		$options->addTextElement("button","Study management..",array("class"=>"btn","cle-nong"=>""));
		$options->endElement("form");
	}
	
	public function generate() {
		$add = ADD::sys();
		$page = $this->_page;
		if($add->hasState('action')=='delete') {
			$eid = $add->getParam('eid');
			$cer = $add->getEncounterManager()->sectionById($add->getParam('eid'));
			if(!$cer->exists()) throw new ADD_EncounterNotFoundException($page -> getSetting('title')." with ID '$eid' does not exist");
			$cer->retrieve();
			$cer->delete();
			ADD::web() -> addMessage($page -> getSetting('title') . " record was deleted successfully");
			return;
		}
		
		if(strlen($add->getParam('study'))==0) {
			throw new \Exception("Sorry unable to add patient to study. You did not select one from the list");
			return;
		}
		
		parent::generate();
		
		$em = $add->getEncounterManager();
		$enrol = $em->sectionById($add->hasState('eid'));
		$enrol->retrieve();
		$detail = $em->child($enrol,"res-study-details");
		$detail->setObsValues(array(
			"study"=>$add->getParam('study'),
			"created_by"=>ADD::db('audit')->currentUser(),
			ADD_Constants_Research::CURRENT_STATUS_FIELD=>""
		));
		$detail -> store();
		
		$web = ADD::web();
		$this->labelPage = $this->_page->getChildPage('sdetails');
		$studyName = $detail->getObsText("study",$this->labelPage);
		$web->addMessage("Patient now being managed in study $studyName");
		$add->setState('locked',false);
	}
	
	public function activeMenu(&$isActive, $currPage, $currentSectionER) {
		if($currPage->getName()!='action') {
			return parent::activeMenu($isActive,$currPage,$currentSectionER);
		}
		
		$add = ADD::sys();
		$currPPage = $currPage -> getParentPage();
		if (!$currPPage) return;
		$eid = $add->getParam('eid');
		$em = $add->getEncounterManager();
		
		$currPER=$em->recordById($eid);
		$page = $this->_page;
		$currPER->retrieve();
		
		if ($currentSectionER -> getId() == $currPER -> getParent()-> getId()) {
			$isActive=true;
			return;
		}
	}
	
	public function breadcrumb(&$breadcrumb) {
		$breadcrumb="";
	}
}
?>
