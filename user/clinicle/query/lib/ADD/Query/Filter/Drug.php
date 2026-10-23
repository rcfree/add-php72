<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
;

date_default_timezone_set("GB");
class ADD_Query_Filter_Drug extends ADD_Query_Filter {
	protected $_encType;
	protected $_obsField;
	protected $_ignoreValues;
	function __construct($attrs) {
        parent::__construct($attrs);
        
        $edb = ADD::db('encounter');
		$this->_encType = $this->assertAttr("encounter");
        $this->_obsField = $edb->getFieldFromEncounterForm($this->_encType,$this->assertAttr("field"));
        $this->_filterType = $this->getAttr("type",ADD_Query_Filter::PATIENT);
		if(!$this->_obsField) throw new Exception("Observation field '".$this->getAttr('field')."' in encounter type '".$this->getAttr('encounter')."' does not exist");
    }
    
	function _filterPatientsEqual(ADD_Query_Result $result) {
		if($this->_notFlag) {
			$prevPatientIds = $result->getPatientIds();
		}
        $result->updateMatchingPatients("e.patient_id","encounter e left join observation o on o.encounter_id=e.id left join observation_field of on o.observation_field_id = of.id","(of.name='drug_name' and o.value = 'SERETIDE 250') and (of.name='frequency' and o.value='6')");
    	
    	if($this->_notFlag) {
    		$newPatientIds = array();
    		$currPatientIds = $result->getPatientIds();
			foreach($prevPatientIds as $prevPatientId) {
				if(in_array($prevPatientId,$currPatientIds)) continue;
				$newPatientIds[]=$prevPatientId;
			}
			$result->setPatientIds($newPatientIds);
		}
    }
	
	function _filterPatientsContains(ADD_Query_Result $result) {
        $result->updateMatchingPatients("e.patient_id","encounter e left join observation o on o.encounter_id=e.id","(o.observation_field_id=? and o.value like ?)",array($this->_obsField['id'],"%".$this->_expValues."%"));
    }
	
	function _filterPatientsGreaterThan(ADD_Query_Result $result) {
        $result->updateMatchingPatients("e.patient_id","encounter e left join observation o on o.encounter_id=e.id","(o.observation_field_id=? and CAST(o.value AS SIGNED)>?)",array($this->_obsField['id'],$this->_expValues));
    }
	
	function _filterPatientsAny(ADD_Query_Result $result) {
        $result->updateMatchingPatients("e.patient_id","encounter e left join observation o on o.encounter_id=e.id","(o.observation_field_id=?)",array($this->_obsField['id']));
    }
	
	function _filterPatientsIn(ADD_Query_Result $result) {
		$inClause = ADD_Query_Util::makePlaceholders($this->_expValues);
		$params = $this->_expValues;
		array_unshift($params,$this->_obsField['id']);
        $result->updateMatchingPatients("e.patient_id","encounter e left join observation o on o.encounter_id=e.id","(o.observation_field_id=? and o.value in ($inClause) )",$params);
    }
}