<?php

class ADD_Database_Audit extends ADD_Database {

    function createAudit($audData) {
    	$audData['timestamp']=$this->now();
		if(!isset($audData['user_id'])) $audData['user_id']=$this->currentUser();
        return $this->insert("audit",$audData);	
    }

	function auditEncounter($action,$enc, $createdObs=array(), $updatedObs=array()) {
		$add = ADD::sys();
		if (!$add->config('audit')) return;
                $am = $add->getAccessManager();
		$encForm = $this->selectById("encounter_form",$enc['encounter_form_id']);
		$actionInfo = array('patient_id'=>$enc['patient_id'],'parent'=>ADD_Util::val('parent_encounter_id',$enc));
		$actionInfo['create']=$createdObs;
		$actionInfo['update']=$updatedObs;
		$audData = array(
			'record_id'=>$enc['id'],
			'record_type'=>"encounter",
			'record_name'=>$encForm['type'],
			'revision_no'=>$enc['revision_no'],
			'action'=>$action,
			'action_info'=>ADD_Util::serialize($actionInfo),
		);
		$audit = $this->createAudit($audData);
	}
	
	function auditSection($action,$section) {
		$add = ADD::sys();
		if (!$add->config('audit')) return;
                $am = $add->getAccessManager();
		$audData = array(
			'record_id'=>$section->getId(),
			'record_type'=>"section",
			'record_name'=>$section->getType(),
			'revision_no'=>$section->getEncData("revision_no"),
			'action'=>$action,
			'action_info'=>ADD_Util::serialize(array('patient_id'=>$section->getPatient())),
		);
		$audit = $this->createAudit($audData);
	}
	function auditDomain($action,$domain) {
		$add = ADD::sys();
		if (!$add->config('audit')) return;
                $am = $add->getAccessManager();
		$audData = array(
			'record_id'=>$domain->getId(),
			'record_type'=>"domain",
			'record_name'=>$domain->getType(),
			'revision_no'=>$domain->getEncData("revision_no"),
			'action'=>$action,
			'action_info'=>ADD_Util::serialize(array('patient_id'=>$domain->getPatient())),
		);
		$audit = $this->createAudit($audData);
	}
	
	function auditDictItem($action,$dictItem,$prevDictItem=null) {
		$add = ADD::sys();
        $ddb = ADD::db('dictionary');
		if (!$add->config('audit')) return;
        $dict = $ddb->getDictionary($dictItem['dictionary_id']);
		$dictName = $dict['name'];
		
                $group = ADD_Util::val('dictionary_group_id',$dictItem) ? $ddb->getDictionaryGroup($dictItem['dictionary_group_id']) : null;
                $data = array("item" => $dictItem, "dictionary" => $dictName, "group" => $group);
				if ($action=="update") $data['previousItem']=$prevDictItem;
		$audData = array(
			'record_id'=>$dictItem['id'],
			'record_type'=>"dict_item",
			'action'=>$action,
			'action_info'=>ADD_Util::serialize($data)
		);
		$audit = $this->createAudit($audData);
	}
        
        function auditDictionary($action,$dictionary) {
		$add = ADD::sys();
                $ddb = ADD::db('dictionary');
		if (!$add->config('audit')) return;
                
                $audData = array(
			'record_id'=>$dictionary['id'],
			'record_type'=>"dictionary",
			'action'=>$action,
			'action_info'=>ADD_Util::serialize($dictionary)
		);
		$audit = $this->createAudit($audData);
	}
        
       function auditDictGroup($action,$dictGroup) {
		$add = ADD::sys();
                $ddb = ADD::db('dictionary');
		if (!$add->config('audit')) return;
                
                $audData = array(
			'record_id'=>$dictGroup['id'],
			'record_type'=>"dict_group",
			'action'=>$action,
			'action_info'=>ADD_Util::serialize($dictGroup)
		);
		$audit = $this->createAudit($audData);
	}
	
	function auditObservations($changeType,$audit, $obsList=array()) {
		CLE::info("auditObservations");
		return;
		$add = ADD::sys();
		if (!$add->config('audit')) return;
		
		if (!$obsList) return;
		
		foreach($obsList as $obs) {
			$audData = array(
				'observation_id'=>$obs['id'],
				'audit_id'=>$audit['id'],
				'change_type'=>$changeType,
				'value'=>isset($obs['note']) ? $obs['note'] : $obs['value']
			);
            $this->insert("observation_audit",$audData);
		}
	}
	function auditEncounterForm($action,$encForm) {
		$add = ADD::sys();
		if (!$add->config('audit')) return;
		$audData = array(
			'record_id'=>$encForm['id'],
			'record_type'=>"form",
			'record_name'=>$encForm['type'],
			'revision_no'=>$encForm['revision_no'],
			'action'=>$action,
			'action_info'=>null
		);
		$aud = $this->createAudit($audData);
		return $aud;
	}
	
	function auditObservationField($changeType,$audit,$obsField,$data) {
		$add = ADD::sys();
		if (!$add->config('audit')) return;
		$actionInfo = array(
			'audit_id'=>$audit['id'],
			'change_data'=>ADD_Util::serialize($data)
		);
		$audData = array(
			'record_id'=>$obsField['id'],
			'record_type'=>"obs_field",
			'record_name'=>$obsField['name'],
			'action'=>$changeType,
			'action_info'=>ADD_Util::serialize($actionInfo)
		);
        $this->createAudit($audData);
	}
	
	function auditOverview($action,$enc, $logicName) {
		$add = ADD::sys();
		if (!$add->config('audit')) return;
		$obsData = $db->getObservationsInEncounter($enc);
		$audData = array(
			'record_id'=>$enc->getId(),
			'record_type'=>"overview",
			'record_name'=>$logicName,
			'action'=>$action,
			'action_info'=>ADD_Util::serialize(array('obs'=>$obsData,'patient_id'=>$enc->getPatientId(),'parent'=>$enc->getParentEncounterID()))
		);
		$this->createAudit($audData);
	}
	
	function auditUser($action,$user) {
		$add = ADD::sys();
		$audData = array(
				'record_id'=>null,
				'record_type'=>"user",
				'record_name'=>"",
				'action'=>$action,
		);
		$this->createAudit($audData);
	}
	
	function auditSearch($params) {
		$add = ADD::sys();
                $user = $this->currentUser();
		$audData = array(
				'record_id'=>null,
				'record_type'=>"patient",
				'record_name'=>"",
				'action'=>"search",
				'action_info'=>ADD_Util::serialize($params)
		);
		$this->createAudit($audData);
	}
	
    function currentUser($user=null) {
    	if($user && is_object($user)) {
    		return $user->username;
		}
        $am = ADD::sys()->getAccessManager();
        $userName = $am->getUser() ? $am->getUser()->username : get_current_user();
        return $userName;
    }
        
	function auditSearchItem($params) {
		$add = ADD::sys();
		$audData = array(
				'record_id'=>null,
				'record_type'=>"search_item",
				'record_name'=>"",
				'action'=>"",
				'action_info'=>ADD_Util::serialize($params)
		);
		$this->createAudit($audData);
	}

	function auditReport($reportName, $reportPath, $ts, $encId=null) {
		$add = ADD::sys();
		if (!$add->config('audit')) return;
		$repData = array(
			'record_id'=>$add->getPatientId(),
			'record_type'=>"report",
			'record_name'=>$reportName,
			'action'=>"print",
		);
		$actionInfo = array();
		
		if($encId) $actionInfo['encounter_id']=$encId;
		
		if ($reportPath) {
			$actionInfo['internal_timestamp']=$ts;
			$actionInfo['file']=$reportPath;
		}
		else {
			$actionInfo['blank']=true;
		}
		$repData['action_info']=ADD_Util::serialize($actionInfo);
		$this->createAudit($repData);
	}
	
	function auditPatient($action,$patient,$address=null,$gp=null) {
		$add = ADD::sys();
		if (!$add->config('audit')) return;
		$patData = array();

		if ($patient) {
			if (is_array($patient)) {
				$patData = $patient;
			}
			else {
				$patData = $patient->toArray(BasePeer::TYPE_FIELDNAME);
			}
		}
		
		$addData = array();
		
		if ($address) {
			if (is_array($address)) {
				$addData = $address;
			}
			else {
				$addData = $address->toArray(BasePeer::TYPE_FIELDNAME);
			}
		}
		
		$gpData = array();
		if ($gp) {
			if (is_array($gp)) {
				$gpData = $gp;
			}
			else {
				$gpData = $gp->toArray(BasePeer::TYPE_FIELDNAME);
			}
		}
		
		$audData = array(
				'record_id'=>$patData['id'],
				'record_type'=>"patient",
				'record_name'=>"",
				'action'=>$action,
				'action_info'=>ADD_Util::serialize(array('patient'=>$patData,'address'=>$addData, 'gp'=>$gpData))
		);
		$this->createAudit($audData);
	}
    function getAuditsForEncounter($enc) {
        $sql = "select * from audit where record_type=? and record_id=? and action='verify'";
        return $this->fetchAll($sql,array('encounter',$enc['id']));
    }

}
