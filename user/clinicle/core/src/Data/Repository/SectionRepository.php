<?php
namespace ClinicLE\Data\Repository;
use ADD;
use ClinicLE\Data\Exception\DataTypeUndefinedException;

class SectionRepository {
    function retrieveSection($section,$force=false) {
        if(!$force && $section->isRetrieved()) return;
		$db = ADD::db('BASE');
        if ($section->isNew()) {
            $section-> setEncounter(null);
            $section-> setId(null);
            return;
        }
		if($section->getId()) {
			$params = array($section->getId());
			$encounter = $db->fetchFirst("select s.*,st.name as `section_type` from `section` s left join `section_type` st on s.section_type_id = st.id where s.id=?",$params);
			$section->setType($encounter['section_type']);
			unset($encounter['section_type']);
		}
		elseif($section->getType() && $section->getPatient()) {
			$params = array($section->getType(),$section->getPatient());
			$encounter = $db->fetchFirst("select s.* from `section` s left join `section_type` st on s.section_type_id = st.id where st.name=? and s.patient_id=?",$params);
		}
		else {
			throw new \ClinicLE\Data\Exception\RecordNotFoundException("Type (".$section->getType().") and/or patient (".$section->getPatient().") not supplied");
		}
		if($encounter) {
			$section->setEncounter($encounter);
			$section->setRetrieved(true);
		}
	}
        function deleteSection($section) {
                ADD::db('encounter')->update("section",array("id"=>$section->getId()),array("is_deleted"=>1),array("date_updated"));
		ADD::db('encounter')->execute("update `encounter` set `is_deleted`=1,`date_updated`=? where `parent_encounter_id`=?",array(ADD::db('encounter')->now(),$section->getId())); 
		$section->setEncData('is_deleted',1);
                $this->storeSection($section);
		ADD::db('audit') -> auditSection("delete", $section);
	}
        public function storeSection($section) {
		$db = ADD::db('BASE');
		$adb = ADD::db('audit');
		if(!$section->isNew() && $section->exists()) {
			$db->update("section",$section->getId(),$section->getEncounter(),array("date_updated"));
			$adb -> auditSection("update", $section);
			return;
		}
		
		if(!$section->getEncData('section_type_id')) {	
			$stId = $db->fetchSingleValue("select id from section_type where name=?",array($section->getType()));
			if(!$stId) {
				throw new DataTypeUndefinedException("Section type '".$section->getType()."' does not exist");
			}
			$section->setEncData('section_type_id',$stId);
		}
		$section->setEncData("patient_id", $section->getPatient());
		$sectionData = $db->insert("section", $section->getEncounter(),array("date_created","date_updated"));
		$section->setEncounter($sectionData);
		$section->setNew(false);
		$adb -> auditSection("create", $section);
	}
        
}
