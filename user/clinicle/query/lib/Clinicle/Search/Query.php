<?php
namespace Clinicle\Search;
use ADD;
class Query {
	private $pageNo;
	private $rowCount;
	private $patientFilters = array();
	private $encounterFilters = array();
	private $observationFilters = array();
	private $encounterJoins = array();
	private $patientJoins = array();
	private $patientFields=array();
	private $observationFields = array();
	private $encounterFields = array();
	private $encounterForms = array();
	private $aliasPrefix;
	
	/**
	 * 
	 * @param type $pageNo
	 * @return \Clinicle\Search\Query
	 */
	function setPageNo($pageNo) {
		$this->pageNo = $pageNo;
		return $this;
	}
	/**
	 * 
	 * @param type $rowCount
	 * @return \Clinicle\Search\Query
	 */
	function setRowCount($rowCount) {
		$this->rowCount = $rowCount;
		return $this;
	}
	/**
	 * 
	 * @param type $filter
	 */
	function addPatientFilter($filter) {
		$this->patientFilters[]=$filter;
	}
	/**
	 * 
	 * @param type $encType
	 * @param type $filter
	 */
	function addEncounterFilter($encType,$filter) {
		if(!isset($this->encounterFilters[$encType])) {
			$this->encounterFilters[$encType]=array();
		}
		$this->encounterFilters[$encType][] = $filter;
	}
	/**
	 * 
	 * @param type $encType
	 * @param type $filter
	 */
	function addObservationFilter($encType,$filter) {
		if(!isset($this->observationFilters[$encType])) {
			$this->observationFilters[$encType]=array();
		}
		$this->observationFilters[$encType][] = $filter;
	}
	/**
	 * 
	 * @param type $encType
	 * @param type $parentType
	 * @param type $alwaysShow
	 */
	function addEncounterJoin($encType, $parentType,$includeFields=false,$alwaysShow=true) {
		$this->encounterJoins[]=array($encType,$parentType,$includeFields, $alwaysShow);
	}
	/**
	 * 
	 * @param type $encType
	 * @param type $alwaysShow
	 */
	function addPatientJoin($encType,$includeFields=false,$alwaysShow=true) {
		$this->patientJoins[]=array($encType,$includeFields,$alwaysShow);
	}
	/**
	 * 
	 * @param type $encType
	 * @param type $fields
	 */
	function addObservationFields($encType,$fields=null) {
		$this->observationFields[$encType]=$fields;
	}
	
	/**
	 * 
	 * @param type $encType
	 * @param type $fields
	 */
	function addEncounterFields($encType,$fields=null) {
		$this->encounterFields[$encType]=$fields;
	}
	
	/**
	 * 
	 * @param type $fields
	 */
	function setPatientFields($fields=null) {
		$this->patientFields=$fields;
	}
	/**
	 * 
	 * @return type
	 * @throws Exception
	 */
	function createFrom() {
		$froms = array("patient p");
		foreach($this->patientJoins as $join) {
			list($child,$includeFields,$alwaysShow) = $join;
			$form = $this->encounterForms[$child];
			$froms[]=($alwaysShow ? "left join " : "inner join ")."encounter `$child` on p.id = `$child`.patient_id and `$child`.encounter_form_id=".$form['id']." and `$child`.is_deleted!=1";	
		}
		
		foreach($this->encounterJoins as $join) {
			list($child,$parent,$includeFields,$alwaysShow) = $join;
			$form = $this->encounterForms[$child];
			$froms[]=($alwaysShow ? "left join " : "inner join ")."encounter `$child` on `$parent`.id = `$child`.parent_encounter_id and `$child`.encounter_form_id=".$form['id']." and `$child`.is_deleted!=1";
		}
		
		
		return implode(" ",$froms);
	}
	
	function retrieveFormAndIncludeFields($includeFields,$child) {
		$edb = ADD::db('encounter');
		$form = $edb->getEncounterFormWithType($child);
		if(!$form) {
			throw new Exception("No encounter join form '$form'");
		}
		if(!$includeFields) {
			return $form;
		}
		$obsFields = $edb->getObservationFieldsInEncounterForm($form['id']);
		$fields = array_map(function($obsField) { return $obsField['name']; },$obsFields);
		$this->addObservationFields($form['type'],$fields);
		return $form;
	}
	/**
	 * 
	 * @return type
	 */
	function createWhere() {
		$where = array();
		$params = array();
		foreach($this->patientFilters as $patientFilter) {
			list($field,$op,$value) = $patientFilter;
			$where[]="p.$field $op ?";
			$params[]=$value;
		}
			
		foreach($this->encounterFilters as $encType => $encFilters) {
			foreach($encFilters as $encFilter) {
				list($field,$op,$value) = $encFilter;
				$where[]="`$encType`.$field $op ?";
				$params[]=$value;
			}
		}
		
		foreach($this->observationFilters as $encType => $encFilters) {
			foreach($encFilters as $encFilter) {
				list($field,$op,$value) = $encFilter;
				$where[]=$this->createFieldSubQuery($encType,$field)." $op ?";
				$params[]=$value;
			}
		}
		
		return array($where,$params);
	}
	/**
	 * 
	 * @param type $joinAlias
	 * @param type $fieldName
	 * @return type
	 */
	function createFieldSubQuery($joinAlias,$fieldName) {
		return "(select o.value from observation o left join observation_field of on o.observation_field_id=of.id where o.encounter_id=`$joinAlias`.id and of.name='$fieldName')";
	}
	/**
	 * 
	 * @return string
	 */
	function createSelectFields() {
		$selectFields = array();
		foreach($this->patientJoins as $join) {
			list($child,$includeFields,$alwaysShow) = $join;
			$this->encounterForms[$child] = $this->retrieveFormAndIncludeFields($includeFields,$child);
		}
		
		foreach($this->encounterJoins as $join) {
			list($child,$parent,$includeFields,$alwaysShow) = $join;
			$this->encounterForms[$child] = $this->retrieveFormAndIncludeFields($includeFields,$child);
		}
		
		foreach($this->patientFields as $field) {
			$selectFields[]="p.$field as `".($this->hasAliasPrefix() ? "patient." : "").$field."`";
		}
		foreach($this->encounterFields as $encType => $fields) {
			foreach($fields as $field) {
				$selectFields[]="`".$encType."`.$field as `".($this->hasAliasPrefix() ? "$encType." : "").$field."`";
			}
		}
		foreach($this->observationFields as $encType => $fields) {
			foreach($fields as $field) {
				$selectFields[]=$this->createFieldSubQuery($encType,$field)." as `".($this->hasAliasPrefix() ? "$encType." : "")."$field`";
			}
		}
		if(count($selectFields)==0) {
			throw new \Exception("No fields included in Query output");
		}
		return $selectFields;
	}
	/**
	 * 
	 * @return type
	 */
	function fetchData() {
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
		if($this->rowCount) {
			$this->startRow = ($this->pageNo-1) * $this->rowCount;
			$this->endRow = $this->startRow + $this->rowCount;
			$sql.=" limit ".$this->startRow.",".$this->rowCount;
		}
		return array($sql,$params);
	}
	
	/**
	 * 
	 * @return type
	 */
	function fetchCount() {
		$edb = ADD::db('encounter');
		$from = $this->createFrom();
		list($where,$params) = $this->createWhere();
		$sql = "select count(*) from ".$from;
		if(count($where)>0) {
			$sql.=" and ".implode(" and ",$where);
		}
		return $edb->fetchSingleValue($sql,$params);
	}
	
	function setAliasPrefix($aliasPrefix) {
		$this->aliasPrefix = $aliasPrefix;
	}
	function hasAliasPrefix() {
		return $this->aliasPrefix;
	}
}
