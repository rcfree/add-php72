<?php
require_once ("LRB/CiviTool/Entity.php");
require_once ("LRB/CiviTool/Entity/ActivityType.php");
require_once("LRB/CiviTool/Operation.php");
require_once ("LRB/Util.php");
class LRB_CiviTool_ContactOperation {
	
	private $_contactId = null;
	
	static function useId($contactId) {
		$co = new LRB_CiviTool_ContactOperation();
		$co->_contactId=$contactId;
		return $co;
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
	
	function updateActivityStatus($activityType,$status) {
		require_once "api/v3/utils.php";
		//require_once "api/v3/Activity.php";
		
		$as = LRB_CiviTool_Entity::factory(array(
			LRB_CiviTool_Entity::TYPE=>"ActivityStatus",
			LRB_CiviTool_Entity::NAME=>$status
		));
		$asValue = $as->getValue();
		$params = array('version'=>'3','contact_id' => $this->_contactId);
		$actData = civicrm_api('Activity','get',$params);
		
		$atValue = LRB_CiviTool_Operation::getActivityTypeValue($activityType);
		if ($actData['count']==0) return;
		$actVals = $actData['values'];
		
		$actIds = array();
		$params=array('activity_status_id'=>$asValue,'version'=>3);
		foreach($actVals as $actVal) {
			if ($actVal['activity_type_id']!=$atValue) continue;
			
			$params['activity_id']=$actVal['id'];
			
			civicrm_api3('Activity','update',$params);
			$actIds[]=$actVal['id'];
		}
		return $actIds;
	}
	
}
?>
