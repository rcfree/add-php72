<?php
namespace ClinicLE\Data;
use ClinicLE\Data\Record\EncounterRecord;
use ClinicLE\Data\Record\SectionRecord;
use ClinicLE\Data\Record\DomainRecord;
use ClinicLE\System\Service\CoreServices;
use ClinicLE\Data\Query\EncounterGroup;
use ClinicLE\Data\Record\AbstractRecord;

use ADD;
class RecordManager {
    private $encounterGroup;
    function __construct() {
        $this->encounterGroup = ADD::sys()->getService(CoreServices::ENCOUNTER_GROUP);
    }
    	function record($type, $patientId) {
            $er = new EncounterRecord();
            if (!$patientId) throw new \InvalidArgumentException("A patient ID was not supplied to the EncounterManager record function (for type '$type')");
            $er->setType($type);
            $er->setPatient($patientId);
            return $er;
	}
	function domain($type, $patientId) {
            $er = new DomainRecord();
			$er->setType($type);
			$er->setPatient($patientId);
			return $er;
	}
    function recordById($id) {
        $er = new EncounterRecord();
        $er->setId($id);
        return $er;
    }
	function newInstance() {
            return new DataManager();
	}
	function sectionById($id) {
        $er = new SectionRecord();
        $er->setId($id);
        return $er;
    }
	function newChild(AbstractRecord $er, $type, $badge=null) {
            if (!$er->getEncounter()) throw new Exception\RecordNotFoundException("Child cannot be returned as parent record has not been retrieved");
            
            $cer = $this->record($type,$er->getPatient());
            $cer -> setSection($er);
            $cer -> setNew(true);
			$cer->setBadge($badge);

			return $cer;
	}
	function newSection($patientId, $type) {
            if (!$patientId) throw new \InvalidArgumentException("A patient ID was not supplied to the EncounterManager record function (for type '$type')");
            if (!$type) throw new \InvalidArgumentException("A type was not supplied to the EncounterManager record function (for patient '$patientId')");
            	
            $cer = new SectionRecord();
            $cer->setType($type);
            $cer->setPatient($patientId);
			$cer -> setNew(true);
			return $cer;
	}
	
	function sectionsByPatientAndType($patientId, $type) {
                $edb = ADD::db('encounter');	
		$params = array($patientId,$type);
		$cencs = $edb -> fetchAll("select s.* from `section` s left join `section_type` st on s.section_type_id = st.id where s.patient_id=? and st.name=? and (s.is_deleted != 1  or s.is_deleted is null)",$params);
		if (!$cencs || count($cencs)==0) return array();
		foreach ($cencs as $cenc) {
			$cer = new SectionRecord();
			$cer -> setEncounter($cenc);
			$cer->setType($type);
			$cers[] = $cer;
		}
		
		return $cers;
	}
	
	function sectionByPatientAndType($patientId, $type) {
		$edb = ADD::db('encounter');	
		$params = array($patientId,$type);
		$cenc = $edb -> fetchFirst("select s.* from `section` s left join `section_type` st on s.section_type_id = st.id where s.patient_id=? and st.name=? and (s.is_deleted != 1  or s.is_deleted is null)",$params);
		
		$cer = new SectionRecord();
		if($cenc) {
			$cer -> setEncounter($cenc);
		}
		$cer->setType($type);
		$cer->setPatient($patientId);
		return $cer;
	}
	
	function child(AbstractRecord $er, $type, $id = null, $badge=null) {		
		$children = $this->children($er, $type, $id, $badge);
		if (count($children) == 0) {
			return $this-> newChild($er,$type, $badge);
			
		}
		
		if ($id) {
            if (count($children) == 0) throw new Exception\RecordNotFoundException("There was no '$type' child present with parent ID $id");
		} 
		else {
			if (count($children) > 1) {
				throw new Exception\MultipleRecordsException("You must specify an ID as there is more than one '$type' child record (".count($children)." found) matching the parent");
			}
		}
		return $children[0];
	}
	
	function childById($patientId, $type, $id = null, $badge=null) {
        $edb = ADD::db('encounter');
		
        $em = ADD::sys()->getEncounterManager();
			
		$children = $em->children($er, $type, $id, $badge);
		
		if(count($children)==0) {
			if(is_a($er,"\ClinicLE\Data\Record\DomainRecord") && count($children) == 0) {
				return $em -> newSection($er->getPatient(),$type);
			}
			
			return $em -> newChild($er,$type, $badge);
		}
		
		if ($id) {
            if (count($children) == 0) throw new Exception\RecordNotFoundException("There was no '$type' child present with parent ID $id");
		} 
		else {
			if (count($children) > 1) {
				throw new Exception\MultipleRecordsException("You must specify an ID as there is more than one '$type' child record (".count($children)." found) matching the parent");
			}
		}

		return $children[0];
	}
	function children(AbstractRecord $er, $type, $id = null, $badge=null) {
		if(is_a($er,"\ClinicLE\Data\Record\DomainRecord")) {
			return $this->sectionsByPatientAndType($er->getPatient(), $type);
		}
		ADD::sys()->getService(CoreServices::SECTION_REPOSITORY)->retrieveSection($er);
        
       	$cers = array();
		if (!$er -> getEncounter())
			throw new Exception\RecordNotFoundException("Supplied record (ID:".$er->getId().") does not exist, therefore there are no '$type' child records.<pre>".print_r($er,1)."</pre>");

		$edb = ADD::db('encounter');
                
		if ($id) {
			if ($id==$er->getId()) throw new ADD_IdenticalEncounterException("Parent and child ID cannot be the same (".$id.")");
			$encounter = $this->encounterGroup->getSingleEncounterWithPatientTypeAndId($er->getPatient(), $type, $id,
                $badge);
                        return array($encounter);
		}
		$encounters = $this->encounterGroup->getSectionEncountersWithPatientAndType($er->getPatient(), $type, $er,
            $badge);
		
		return $encounters->all();
	}
        
        

}
