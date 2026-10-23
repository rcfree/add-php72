<?php

class ADD_ResearchSearch_StudyQuery {
	private $_studyFilter;
	private $_studyStatusFilter;
	private $_statusFilter;
	private $_studyNoFilter;
	private $_researchIdFilter;
	private $_includeStudies = false;
	function __construct($pageNo=1, $rowCount=null) {
		$this->_pageNo = $pageNo;
		$this->_rowCount = $rowCount;
		
		$edb = ADD::db('encounter');
		$this->_researchForm = $edb->getEncounterFormWithType("res");
	}
	function setIncludeStudies($includeStudies) {
		$this->_includeStudies = $includeStudies;
	}
	function doesIncludeStudies() {
		return $this->_includeStudies;
	}
	function filterByStudy($study) {
		$this->_studyFilter = $study;
	}
	function filterByResearchId($researchId) {
		$this->_researchIdFilter = $researchId;
	}
	function filterByStudyNo($studyNo) {
		$this->_studyNoFilter = $studyNo;
	}
	function filterByStudyStatus($studyStatus) {
		$this->_studyStatusFilter = $studyStatus;
	}
	function filterByStatus($status) {
		$this->_statusFilter = $status;
	}
	function createFrom() {
		$joins = array(
			"res-contact-details"=>"patient"
		);
		if($this->_includeStudies) {
			$joins["res-study"]="section";
			$joins["res-study-details"]="res-study";
		}
		$froms = array("patient p");
		$edb = ADD::db('encounter');
		foreach($joins as $child => $parent) {
			$typeId = $this->getEntityTypeId($child,$parent);
			
			if($parent=="patient") {
				$froms[]="left join encounter `$child` on `$child`.patient_id = p.id and `$child`.encounter_form_id=".$typeId." and `$child`.is_deleted!=1";	
			}
			elseif($parent=="section") {
				$froms[]="left join section `$child` on `$child`.patient_id = p.id and `$child`.section_type_id=".$typeId." and `$child`.is_deleted!=1";
			}
			else {
				$froms[]="left join encounter `$child` on `$parent`.id = `$child`.parent_encounter_id and `$child`.encounter_form_id=".$typeId." and `$child`.is_deleted!=1";
			}
		}
		
		return implode(" ",$froms);
	}
	function getEntityTypeId($child,$parent) {
		$edb = ADD::db('encounter');
		if($parent=="section") {
			$sectionId = $edb->fetchSingleValue("select id from section_type where name=?",$child);
			if(!$sectionId) {
				throw new Exception("Section type '$child' does not exist");
			}
			return $sectionId;
		}
		$form = $edb->getEncounterFormWithType($child);
		if(!$form) {
			throw new Exception("Form '$child' does not exist");
		}
		return $form['id'];
	}
	function createWhere() {
		$where = array();
		$params = array();
		if($this->_studyFilter) {
			$where[]=$this->createFieldSubQuery("res-study-details","study")." = ?";
			$params[]=$this->_studyFilter;
		}
		if($this->_studyStatusFilter) {
			$where[]=$this->createFieldSubQuery("res-study-details","current_status")." = ?";
			$params[]=$this->_studyStatusFilter;
		}
		if($this->_statusFilter) {
			$where[]=$this->createFieldSubQuery("res-contact-details","current_status")." = ?";
			$params[]=$this->_statusFilter;
		}
		if($this->_studyNoFilter) {
			$where[]=$this->createFieldSubQuery("res-study-details","study_no")." = ?";
			$params[]=$this->_studyNoFilter;
		}
		if($this->_researchIdFilter) {
			$where[]=$this->createFieldSubQuery("res-contact-details","ilh_identifier")." = ?";
			$params[]=$this->_researchIdFilter;
		}
        $where[]="`res-contact-details`.parent_encounter_id is not null";
		return array($where,$params);
	}
	function createFieldSubQuery($joinAlias,$fieldName) {
		return "(select o.value from observation o left join observation_field of on o.observation_field_id=of.id where o.encounter_id=`$joinAlias`.id and of.name='$fieldName')";
	}
	function createSelectFields() {
		$selectFields = array(
			"p.id as patient_id",
			"p.first_name as first_name",
			"p.last_name as last_name",
			"p.date_of_birth as date_of_birth",
			"p.identifier as identifier"
		);
		if($this->_includeStudies) {
			$selectFields[]="`res-study`.id as study_id";
			foreach(array("study_no","study") as $field) {
				$selectFields[]=$this->createFieldSubQuery("res-study-details",$field)." as $field";
			}
			$selectFields[]=$this->createFieldSubQuery("res-study-details","current_status")." as study_status";
		}
		
		$selectFields[]="`res-contact-details`.parent_encounter_id as contact_id";
		
		$selectFields[]=$this->createFieldSubQuery("res-contact-details","ilh_identifier")." as ilh_identifier";
		$selectFields[]=$this->createFieldSubQuery("res-contact-details","current_status")." as current_status";
		
		return $selectFields;
	}
	function getRows() {
		$edb = ADD::db('encounter');
		
		list($sql,$params) = $this->generateSQL();
		$rows = $edb->fetchAll($sql,$params);
		return $rows;
	}
	function generateSQL() {
        $selectFields = $this->createSelectFields();
        $from = $this->createFrom();
        list($where,$params) = $this->createWhere();
        $sql = "select ".implode(",",$selectFields)." from ".$from;
        if(count($where)>0) {
            $sql.=" where ".implode(" and ",$where);
        }
        if($this->_rowCount) {
            $this->_startRow = ($this->_pageNo-1) * $this->_rowCount;
            $this->_endRow = $this->_startRow + $this->_rowCount;
            $sql.=" limit ".$this->_startRow.",".$this->_rowCount;
        }
        return array($sql,$params);
    }
	function countRows() {
		$edb = ADD::db('encounter');
		$from = $this->createFrom();
		list($where,$params) = $this->createWhere();
		$sql = "select count(*) from ".$from;
		if(count($where)>0) {
			$sql.=" where ".implode(" and ",$where);
		}
		//CLE::info("total:".)
		return $edb->fetchSingleValue($sql,$params);
	}
}
