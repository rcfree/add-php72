<?php
require_once ("LRB/CiviTool/Entity.php");
require_once ("LRB/Util.php");
class LRB_CiviTool_Operation {
	#create case for contact based on specified name
	#but prevent creation of the same case type multiple times
	function addParticipantToStudy($contactID, $studyName, $date = null, $message = "Added to %s study on import") {
		require_once "CRM/Case/BAO/Case.php";
		require_once "api/v3/utils.php";
		require_once "api/v3/Case.php";

		if (!$date)
			$date = date("d/m/Y");
		$startDate = LRB_Util::dmyToYMD($date);

		#get case types associated with contact
		$cases = CRM_Case_BAO_Case::getContactCases($contactID);

		#loop through and return false if already added
		foreach ($cases as $key => $case) {
			if ($case['case_type'] == $studyName) {
				error_log("Contact already added to study '$studyName' no need to add again");
				return false;
			}
		}

		#get case type ID from study name
		$ctEnt = LRB_CiviTool_Entity::factory(array("Type" => "CaseType", "Name" => $studyName));
		$caseTypeID = $ctEnt -> getId();

		$case_vars = array("case_type_id" => $caseTypeID, "subject" => sprintf($message, $studyName), "status_id" => 1, "contact_id" => $contactID, "start_date" => $startDate, "version" => 1, "creator_id" => 1);

		#create case for contact
		$study = civicrm_api3_case_create($case_vars);

		#return case information
		return $study['values'];
	}

	function createRelationship($relNameAB, $contactIdA, $contactIdB) {
		$params['version'] = '3';
		$params['name_a_b'] = $relNameAB;
		$rt = civicrm_api('RelationshipType', 'getsingle', $params);
		if (isset($rt['is_error'])) {
			throw new LRB_CiviToolException("Error looking for relationship type: '" . $params['name_a_b'] . "': " . $rt['error_message']);
		}
		$id = $rt['id'];
		$relationship = civicrm_api('Relationship', 'get', array('version' => '3', 'contact_id_a' => $contactIdA, 'contact_id_b' => $contactIdB, 'relationship_type_id' => $id, 'is_active' => '1'));
		if (isset($relationship['is_error']) && $relationship['is_error']==1) {
			throw new LRB_CiviToolException("Error looking for the relationship: " . $relationship['error_message']);
		}
		if ($relationship['count'] == 0) {
			$relationship = civicrm_api('Relationship', 'create', array('version' => '3', 'contact_id_a' => $contactIdA, 'contact_id_b' => $contactIdB, 'relationship_type_id' => $id, 'is_active' => '1'));
			if (isset($relationship['is_error']) && $relationship['is_error']==1) {
				throw new LRB_CiviToolException("Error creating relationship: " . $relationship['error_message']);
			}
			return true;
		}
		return false;
	}

	function getContactWithFieldValue($fieldName, $groupName, $valueToSearch, $notFoundError="A contact with field value '%s' was not found") {
		$ent = LRB_CiviTool_Entity::factory(array("Name" => $fieldName,"Type"=>"CustomField","Parent"=>$groupName));
		$cfId = $ent->getId();
		$params = array("version"=>"3","custom_".$cfId=>$valueToSearch);
		$ct = civicrm_api('Contact', 'getsingle', $params);
		if(isset($ct['is_error'])) {
			if (isset($ct['count']) && $ct['count']==0) return null;
			if (isset($ct['count']) && $ct['count']>1) throw new LRB_CiviToolException("Multiple ({$ct['count']}) contacts have a ".str_replace("_"," ",$fieldName)." equal to '$valueToSearch'");
			else throw new LRB_CiviToolException("Problem getting contact with ".str_replace("_"," ",$fieldName)." equal '$valueToSearch'. Error message:".isset($ct['error_message']) ? $ct['error_message'] : "");
		}
		return $ct;
	}
	
	function getContact($fieldName, $valueToSearch,$contactType="Individual") {
		$params = array("version"=>"3",$fieldName=>$valueToSearch,"contact_type"=>$contactType);
		$ct = civicrm_api('Contact', 'getsingle', $params);
		if(isset($ct['is_error'])) {
			if (isset($ct['count']) && $ct['count']==0) return null;
			else throw new LRB_CiviToolException("Problem getting contact with '$fieldName' equal '$valueToSearch'");
		}
		return $ct;
	}
	
	static function getActivityTypeValue($activityType) {
		$at = LRB_CiviTool_Entity::factory(array(
			LRB_CiviTool_Entity::TYPE=>"ActivityType",
			LRB_CiviTool_Entity::NAME=>$activityType
		));
		return $at->getValue();
	}
	
}
?>
