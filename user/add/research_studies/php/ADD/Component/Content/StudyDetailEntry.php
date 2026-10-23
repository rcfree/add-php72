<?php


class ADD_Component_Content_StudyDetailEntry extends ADD_Component {
	protected $_st;
	private $differentStatus = false;
	function getComponentList() {
		$cl = array(
			'PatientCheck',
			'Encounter_Single', 
            'Autosave',
			'Form_DB',
			'AccessControl',
			'MenuItem_Single',
			'RequiredFields_Notify',
			'ConfirmSave',
            'HistoryOption',
		);
		return $cl;
	}
	function setPage(ADD_Page $page) {
		$this->_st = new ADD_Tool_StudyTitle($page);
		parent::setPage($page);
	}
	function title(&$title) {
        return $this->_st->title($title);
	}
	
	function breadcrumb(&$breadcrumb) {
        return $this->_st->breadcrumb($breadcrumb);
	}
	
	function expectedPermissions(&$expPerms) {
        $page = $this->_page;
		$expPerms = $expPerms + array('create','edit','view');
	}
	function beforeGenerate() {
		$html = ADD::web()->getContent();
		$studyDetails = $this->_page->getEncounterRecord(ADD::sys()->getParam('eid'));
		if(!$studyDetails->exists()) return;
		
		$studyDetails->retrieve();
		$studyValue = $studyDetails->getObsValue("study");
		$studyItem = ADD::db('dictionary')->getDictionaryItemWithValue("studylist",$studyValue);
		$studyData = ADD_Util::unserialize($studyItem['data']);
		$endDate = "<b>Expected end date:</b>&nbsp;".ADD_Util::val('study_end_date',$studyData,"none specified");
		$allowRecruit = ADD_Util::val('allow_recruitment',$studyData) ? "Yes" : "No";
		$obsStudy = "<b>Observational study:</b>&nbsp;".$allowRecruit;
		$html->addTextElement("p",$endDate." | ".$obsStudy,array("class"=>"muted"));
	}
	function beforeSubmit(&$values) {
		$studyDetails = $this->_page->getEncounterRecord(ADD::sys()->getParam('eid'));
		if(!$studyDetails->exists()) {
			$this->differentStatus = true;
			return;
		}
		$studyDetails->retrieve();
		if ($values['current_status']==$studyDetails->getObsValue('current_status')) return;
		$this->differentStatus = true;
	}
	function afterSubmit(ADD_Page $submitPage) {
		if($submitPage!=$this->_page) return;
		if(!$this->differentStatus) return;
		
		$studyDetails = $this->_page->getEncounterRecord(ADD::sys()->getParam('eid'));
		$studyDetails->retrieve();
		$currentStudyStatus = strtolower($studyDetails->getObsText('current_status',$this->getPage()));
		$actionsPage = ADD::sys()->getPageInModule('research_studies','actions');
		$actionsPage->getComponent('Content')->checkStatusWillNotChange($currentStudyStatus, $studyDetails);
		
		$actionMod = ADD::sys()->getModule('research_action');
		foreach($actionMod->getPages() as $apage) {
			if(!$apage->getSetting('auto_add_action')) continue;
			
			$updateStatuses = $apage->getSetting('update_status');
			if(!is_array($updateStatuses)) $updateStatuses=array($updateStatuses);
			foreach($updateStatuses as $updateStatus) {
				list($actionStatus,$matchStudyStatus)=explode(":",$updateStatus);
				$endDateField = $apage->getSetting('study_date_to_use','end_date');

				if($currentStudyStatus!=$matchStudyStatus) continue;
				
				$this->addCompleteActionToStudy($apage->getName(),array("action_end_date"=>$studyDetails->getObsValue($endDateField)),$currentStudyStatus, $actionStatus);
				return;
			}
		}
	}
	function addCompleteActionToStudy($actionValue,$params, $studyStatus, $actionStatus) {
		$ddb = ADD::db('dictionary');
		$actionDict = $ddb->getDictionaryByName("actionlist");
		$actionItem = $ddb->getDictionaryItemWithValue($actionDict,$actionValue);
		
		if(!$actionItem) throw new ADD_NullValueProvidedException("No action '$actionValue' found");
		
		$actionStatItem = $ddb->getDictionaryItemByTitle("actionstatus",$actionStatus);
		
		if(!$actionStatItem) throw new ADD_NullValueProvidedException("No action status '$actionStatus' found");
		
		$params['action_type']=$actionValue;
		$params['action_status']=$actionStatItem['value'];
		$params['date_recorded']=ADD_Field_Util::today();
		$params['action_created_by']=ADD_Field_Util::current_user();
		$actionsPage = $this->_page->getSiblingPage('actions');
		ADD::sys()->setState('action','create');
		$actionsPage->getComponent('Encounter')->submit($params);
		$messages = ADD::web()->getMessages();
		$messages = array_slice($messages,0,-1);
		$messages[]="Automatically added the '".$actionItem['title']."' action due to the change in study status to '".ucfirst($studyStatus)."'";
		ADD::web()->setMessages($messages);
	}
}
?>