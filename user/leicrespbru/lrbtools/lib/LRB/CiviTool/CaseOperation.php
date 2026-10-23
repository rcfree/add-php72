<?php
require_once ("LRB/CiviTool/Entity.php");
require_once ("LRB/CiviTool/Entity/ActivityType.php");
require_once("LRB/CiviTool/Operation.php");
require_once ("LRB/Util.php");
class LRB_CiviTool_CaseOperation {
	
	private $_contactId = null;
	private $_caseId = null;
	private $_activityId = null;
	static function useCase($caseId) {
		require_once "api/v3/utils.php";
		require_once "api/v3/Case.php";
				
		$params=array(
			'case_id' => $caseId,
			'version' => 3,
			//'sequential'=>1,
		);
		$case = civicrm_api('Case','get',$params);
		
		$co = new LRB_CiviTool_CaseOperation();
		
		$co->_caseId=$caseId;
		$co->_contactId = $case['values'][$co->_caseId]['contacts'][0]['contact_id'];
		return $co;
	}
	static function useContact($contactId) {
		$co = new LRB_CiviTool_CaseOperation();
		$co->_contactId=$contactId;
		return $co;
	}
	static function useContactAndCase($contactId,$caseType) {
		$co = new LRB_CiviTool_CaseOperation();
		$co->_contactId=$contactId;
		if (!$co->populateMatchingCase($caseType)) throw new LRB_CiviToolException("No '$caseType' case found for contact '$contactId'");
		return $co;
	}
	
	function populateMatchingCase($caseType) {
		require_once "CRM/Case/BAO/Case.php";
		require_once "api/v3/utils.php";
		require_once "api/v3/Case.php";
		
		if (!$this->_contactId) throw new LRB_CiviToolException("Contact ID not defined in CaseOperation");
		
		#get case types associated with contact
		$cases = CRM_Case_BAO_Case::getContactCases($this->_contactId);
		#loop through and return false if already added
		$cos = array();
		
		foreach ($cases as $key => $case) {
			if ($case['case_type'] == $caseType) {
				$cos[]=$case['case_id'];
			}
		}
		if (count($cos)==0) {
			$this->_caseId=null;
			return false;
		}
		if (count($cos)>1) {
			$this->_caseId=null;
			throw new LRB_CiviToolException("Multiple '$caseType' cases for contact ({$this->_contactId})");
		}
		$this->_caseId = $cos[0];
		return $cos[0];
	}
	
	function addActivity($activityType,$subject="Added automatically") {
		require_once "api/v3/utils.php";
		require_once "api/v3/Activity.php";
		$atValue = LRB_CiviTool_Operation::getActivityTypeValue($activityType);
		$params=array(
			'case_id' => $this->_caseId,
			'activity_type_id' => $atValue,
			'source_contact_id' => 1,
			'version' => 3,
			'subject' => $subject,
			'details' => ""
		);
		
		#create the activity
		$act = civicrm_api3_activity_create($params);
		return $act['id'];
	}
	function updateActivity($status) {
		$as = LRB_CiviTool_Entity::factory(array(
				LRB_CiviTool_Entity::TYPE=>"ActivityStatus",
				LRB_CiviTool_Entity::NAME=>$status
			));
		$params=array(
			'case_id' => $this->_caseId,
			'activity_type_id' => $this->_activityId,
			'source_contact_id' => 1,
			'version' => 3,
			'activity_status_id'=>$status,
		);
		$act = civicrm_api3_activity_create($params);
	}
	
	function updateActivityStatus($activityType,$status) {
		require_once "api/v3/utils.php";
		require_once "api/v3/Activity.php";
		
		$as = LRB_CiviTool_Entity::factory(array(
			LRB_CiviTool_Entity::TYPE=>"ActivityStatus",
			LRB_CiviTool_Entity::NAME=>$status
		));
		$asValue = $as->getValue();
		$atValue = LRB_CiviTool_Operation::getActivityTypeValue($activityType);
		$params = array('activity_type_id' => $atValue);
		$acts = CRM_Case_BAO_Case::getCaseActivity($this->_caseId,$params,$this->_contactId);
		$actIds = array_keys($acts);
		
		$params=array();
		foreach($actIds as $actId) {
			$params['activity_id']=$actId;
			$params['activity_status_id']=$asValue;
			$params['version']=3;

			civicrm_api3('Activity','update',$params);
		}
		return $actIds;
	}
	
	function containsActivity($activityType) {
		require_once "api/v3/utils.php";
		require_once "api/v3/Activity.php";
		$atValue = LRB_CiviTool_Operation::getActivityTypeValue($activityType);
		$params=array(
			'case_id' => $this->_caseId,
			'activity_type_id' => $atValue,
			'version' => 3,
		);
		
		#create the activity
		$act = civicrm_api3_activity_get($params);
		return $act['count']>0 ? true : false;
	}

	function getActivities($activityType) {
		require_once "api/v3/utils.php";
		require_once "api/v3/Activity.php";
		$atValue = LRB_CiviTool_Operation::getActivityTypeValue($activityType);
		$params=array(
			'contact_id'=>$this->_contactId,
			'case_id' => $this->_caseId,
			'activity_type_id' => $atValue,
			'version' => 3,
		);
		
		#create the activity
		$acts = civicrm_api3_activity_get($params);
		print_r($acts);
	}
	
	function getContact() {
		require_once "api/v3/utils.php";
		require_once "api/v3/Contact.php";
		$params=array(
			'contact_id'=>$this->_contactId,
			'version' => 3,
		);
		
		#create the activity
		$contact = civicrm_api3_contact_get($params);
		if ($contact['count']==0) return null;
		if ($contact['is_error']) throw new LRB_CiviToolException($contact['error_message']);
		return array_shift($contact['values']);
	}
	
	static function casesWithActivities($activityType,$activityStatus, $noDups) {
		require_once("CRM/Case/BAO/Case.php");
	
		$as = LRB_CiviTool_Entity::factory(array(
			LRB_CiviTool_Entity::TYPE=>"ActivityStatus",
			LRB_CiviTool_Entity::NAME=>$activityStatus
		));
		$asValue = $as->getValue();
		
		$atValue = LRB_CiviTool_Operation::getActivityTypeValue($activityType);
		
		
		$params = array('activity_type_id'=>$atValue, 'activity_status_id'=>$asValue, 'is_deleted' => '0','version'=>3);
		$actData = civicrm_api('Activity','get',$params);
		if ($actData['count']==0) {
			return array();
		}
		$acts = $actData['values'];
		$acs = array();
		foreach ($acts as $id => $act) {
			$ac = LRB_CiviTool_CaseOperation::useActivity($id);	
			$caseId = CRM_Case_BAO_Case::getCaseIdByActivityId($id);
			$acs[$caseId."-".$ac->_contactId]=$ac;
		}
		return array_values($acs); 
	}
	
	static function useActivity($activityId) {
		$caseId = CRM_Case_BAO_Case::getCaseIdByActivityId($activityId);
		$co = LRB_CiviTool_CaseOperation::useCase($caseId);
		$co->_activityId=$activityId;		
		return $co;
	}
}
?>
