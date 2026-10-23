<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of StudyModalEntry
 *
 * @author rcf8
 */

class ADD_Component_Content_StudyModalEntry extends ADD_Component {

	static $completedStatuses=array("completed","withdrawn","not interested","left due to problem","study complete","fails to meet study criteria");
	
	static $enrolledStatuses=array("enrolled");

	function title(&$title) {
        return $this->_st->title($title);
	}
	
	function breadcrumb(&$breadcrumb) {
        return $this->_st->breadcrumb($breadcrumb);
	}
	function setPage(ADD_Page $page) {
		$this->_st = new ADD_Tool_StudyTitle($page);
		parent::setPage($page);
	}
	function beforeSubmit(&$values) {
		if ($values['action_type']!="updateno" && $values['action_type']!="studyenrol") return;
		if(ADD_Util::isEmpty($values['action_study_no'])) return;
		$values['description'] = ADD_Util::isEmpty($values['description']) ? "" : $values['description']."\n";
		$values['description'].="Study number updated to '".$values['action_study_no']."'";
	}
	function submit($values) {
		$output = parent::submit($values);
		$add = ADD::sys();
		$sdetailsPage = $this->_page->getSiblingPage('sdetails');
		$sdetails = $sdetailsPage->getEncounterRecord();
		$sdetails->retrieve();
		if ($values['action_type']=="updateno") {
			$updateDetails = array('study_no'=>$values['action_study_no']);
			$message="Study number updated to ".$updateDetails['study_no'];
			ADD::web()->addMessage($message);
			$sdetails->store($updateDetails);
			return $output;
		}
		
		$actionMod = $add->getModule('research_action');
		$actionPage= $actionMod->getPage($values['action_type']);
		if (!$actionPage) return $output;
		$updateStatuses = $actionPage->getSetting('update_status');
		if (!$updateStatuses) return $output;
		if(!is_array($updateStatuses)) $updateStatuses=array($updateStatuses);
		
		foreach($updateStatuses as $updateStatus) {
			list($actionStatusTrigger,$changeToStudyStatus) = explode(":",$updateStatus);
			$selActStatus = ADD_Util::val('action_status',$values);
			$updateDetails = $this->autoUpdateStatus($values,$actionStatusTrigger,$changeToStudyStatus,$selActStatus, $sdetails);
			if(!$updateDetails) continue;
			$this->checkStatusWillNotChange($changeToStudyStatus, $sdetails);
			$sdetails->store($updateDetails);
		}
		return $output;
	}
	function autoUpdateStatus($values,$actionStatusTrigger,$changeToStudyStatus,$selActStatus,$sdetails) {
		$ddb = ADD::db('dictionary');
		$asStatus = $ddb->getDictionaryItemByTitle("actionstatus",$actionStatusTrigger);
		if ($asStatus['value']!=$selActStatus) return;
		
		$ssStatus = $ddb->getDictionaryItemByTitle("studystatus",$changeToStudyStatus);
		if(!$ssStatus) {
			ADD::web()->addWarning("Study status '".ucfirst($changeToStudyStatus)."' is not defined in ADD");
			return;
		}
		if (strtolower($sdetails->getObsValue('current_status'))==strtolower($ssStatus['value'])) {
			ADD::web()->addMessage("Patient's study status already changed to '".ucfirst($changeToStudyStatus)."'");
			return;
		}
		$updateDetails = array("current_status"=>$ssStatus['value']);
		
		$message = "Patient's study status automatically changed by action to '".$ssStatus['title']."'";
		
		if (in_array($changeToStudyStatus,self::$completedStatuses)) {
			$updateDetails['end_date']=$values['action_end_date'];
			$message.=" and end date set to ".$updateDetails['end_date'];
		}
		
		
		if (in_array($changeToStudyStatus,self::$enrolledStatuses)) {
			$form = $this->_page->getComponent('Form');
			$updateDetails['enrol_date']=$values['action_end_date'];
			$message.=", enrol date is ".$updateDetails['enrol_date'];
			$updateDetails['study_no']=$values['action_study_no'];
			$message.=" and study number is ".$updateDetails['study_no'];
		}
		ADD::web()->addMessage($message);
		return $updateDetails;
	}
	function checkStatusWillNotChange($changeToStudyStatus, $sdetails) {
		$log = ADD::log();
		$ddb = ADD::db('dictionary');
		$studyDict = $ddb->getDictionaryByName("studylist");
		$studyDictItem = $ddb->getDictionaryItemWithValue($studyDict, $sdetails->getObsValue('study'));
		$studyData = ADD_Util::unserialize($studyDictItem['data']);
		$changeToStudyStatus = strtolower($changeToStudyStatus);
		
		$enrolled = in_array($changeToStudyStatus,self::$enrolledStatuses) ?  true : false;
		$completed = in_array($changeToStudyStatus,self::$completedStatuses) ? true : false;
		if(!$enrolled && !$completed) return;
		
		if (ADD_Util::val('allow_recruitment',$studyData)==1) {
			ADD::web()->addMessage("Study allows enrolment in other studies. Recruitment status has not been changed");
			return;
		}
		
		$detailsPage = ADD::sys()->getPageInModule('research','details');
		$details = $detailsPage->getEncounterRecord();
		$details->retrieve();
		
		$statusComment = "Automatically set: Status changed in study '".$studyDictItem['title']."'";
		$unavailReason=null;
		
		if ($enrolled && $details->getObsValue('current_status')!=5) {
			$currentStatus = 5;
			$statusComment = "Automatically set: Patient enrolled in study ".$studyDictItem['title'];
			$unavailReason = 1;
			ADD::web()->addWarning("Study does not allow enrolment in other studies. Recruitment status has been changed to 'Not currently available'");
		}
		if ($completed && $details->getObsValue('current_status')!=3) {
			$currentStatus = 3;
			$statusComment = "Automatically set: Patient left study ".$studyDictItem['title'];
			ADD::web()->addWarning("Study did not allow enrolment in other studies. Recruitment status has been changed to 'Available'");
		}
		if(!isset($currentStatus)) return;
		
		$details->store(array("current_status"=>$currentStatus));
		$contact = $details->getParent();
		$em = ADD::sys()->getEncounterManager();
		$statusLog = $em->newChild($contact,"res-contact-status");
		
		$statusLog->store(array(
			"recruit_status"=>$currentStatus,
			"change_comments"=>$statusComment,
			"reason_unavail"=>$unavailReason,
			"log_type"=>1,
			"date_changed"=>ADD_Field_Util::today(),
			"user_changed"=>ADD_Field_Util::current_user()
		));
	}
	
}
