<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ClinicLE\Data\Query;
use ClinicLE\Common\Exception\NullValueException;
use CLE;

abstract class AbstractEncounterQuery implements QueryInterface {

    protected $orderBy=array();
    protected $filters = array();
    protected $joinTables = array();
    protected $typeFilter;
    protected $fields=array();
	protected $limitStart = false;
	protected $limitRows=50;
	protected $disabled = false;

    /**
     * @return boolean
     */
    public function isDisabled()
    {
        return $this->disabled;
    }

    /**
     * @param boolean $disabled
     */
    public function setDisabled($disabled)
    {
        $this->disabled = $disabled;
    }

    public function __construct($type=null) {
        if(!$type) {
            return;
        }
        $this->filterByType($type);
    }
    public function filterByType($type) {
	$this->typeFilter = $type;
	return $this;
    }
    
    public function filterById($id,$operator="=") {
        $encAlias = $this->determineEncAlias();
	$this->addFilter("$encAlias.id",$operator,$id);
	return $this;
    }
    public function filterBySectionId($parentId, $operator="=") {
        $encAlias = $this->determineEncAlias();
	$this->addFilter("$encAlias.".EncounterRecord::SECTION_FIELD,$operator,$parentId);
	return $this;
    }
    public function filterByPatientId($patientId,$operator="=") {
        $encAlias = $this->determineEncAlias();
	$this->addFilter("{$encAlias}.patient_id",$operator,$patientId);
	return $this;
    }
    
    public function filterByPatientField($field,$value,$operator="=") {
        $encAlias = $this->determineEncAlias();
	$this->addJoinTable("patient p", "$encAlias.patient_id = p.id");
	$this->addFilter("p.".$field,$operator,$value);
    }
    public function filterByEncounterField($field,$value,$operator="=", $encType="e") {
        $encAlias = $this->determineEncAlias($encType);
	$this->addFilter($encAlias.".".$field,$operator,$value);
	return $this;
    }
	public function filterByObservationField($field,$value,$operator="=", $encType="e") {
        $encAlias = $this->determineEncAlias($encType);
	$this->addFilter($encAlias.".".$field,$operator,$value);
	return $this;
    }
	public function addPatientFields($patientFields) {
		foreach($patientFields as $patientField) {
			$this->addPatientField($patientField);
		}
	}
	public function addEncounterFields($encFields, $encType=null) {
        if(!is_array($encFields)) {
			$encFields = array($encFields);
		}
		foreach($encFields as $encField) {
			$this->addEncounterField($encField, $encType);
		}
	}
	public function addObservationFieldsFromForm($form) {
        if(is_array($form)){
            foreach($form as $field) {
                $this->addObservationField($field['field']);
            }
            return;
        }
		foreach($form->getFields() as $field) {
			if(!$field->isStored()) {
				continue;
			}
			$this->addObservationField($field->getName());
		}
	}
    public function orderBy($field,$order="asc") {
	$this->orderBy[]=array($field,$order);
    }
    public function getTypeFilter() {
	return $this->typeFilter;
    }
   
    public function generateFullSQL() {
		$fieldList = $this->fields;
		if(count($fieldList)==0) {
			throw new \InvalidArgumentException("No fields added to query");
		}
		$sqlAction="select ".implode(",",$fieldList);
		list($sql,$params) = $this->generateFromAndWhere($sqlAction);
		if($this->limitStart !== false) {
			$sql .= " limit ".$this->limitStart.",".$this->limitRows;
		}
//CLE::info("sql:".$sql,$params);
		return array($sql,$params);
	}
    public function generateDeleteSQL() {
	$sqlAction="delete";
	return $this->generateFromAndWhere($sqlAction);
    }
	public function generateFrom() {
		return "from encounter ".$this->determineEncAlias();
	}
    private function generateFromAndWhere($sqlAction) {
	    if(!$this->getTypeFilter()) {
		    throw new NullValueException("Type filter not defined in EncounterQuery");
	    }
	    $from = " ".$this->generateFrom();
	    if(count($this->joinTables)) {
			foreach($this->joinTables as $table => $info) {
			    list($on,$type)=$info;
				$from.=" $type join ".$table." on ".$on;
			}
	    }
	    list($where,$params) = $this->determineWhere();
	    
	    $orderBy = $this->generateOrderBy();
	    $sql = $sqlAction." ".$from." ".$where." ".$orderBy;
		return array(trim($sql),$params);
    }
    private function generateOrderBy() {
	if(count($this->orderBy)==0) {
	    return;
	}
	$orderBys = array();
	foreach($this->orderBy as $orderBy) {
	    $orderBys[]=$orderBy[0]." ".$orderBy[1];
	}
	return "order by ".implode(",",$orderBys);
    }
    public function generateCountSQL() {
	   $sqlAction="select count(*)";
	   return $this->generateFromAndWhere($sqlAction);
    }
    
    private function determineWhere() {
	$where=array();
	
	list($params,$whereClauses) = $this->generateParamsAndWhereClauses();
	
	foreach($whereClauses as $whereClause) {
	    if(is_array($whereClause)) {
		$where[]="(".implode(" or ",$whereClause).")";
	    }
	    else {
		$where[]=$whereClause;
	    }
	}
	if(count($where)==0) return array("",array());
	return array("where ".implode(" and ",$where),$params);
    }
    
    private function generateParamsAndWhereClauses() {
	$whereClauses=array();
	$params = array();
	foreach($this->filters as $filter) {
		if(!is_array($filter)) {
			$whereClauses[]=$filter;
			continue;
		}
		
		$ffield = $filter['field'];
		$fvalue = $filter['value'];
	    $fop = $filter['op'];
		if(is_array($fvalue)) {
		$subClauses = array();
		foreach($fvalue as $filterValueItem) {
		    if(is_array($filterValueItem)) {
			list($svalue,$sop) = $filterValueItem;
		    }
		    else {
			$svalue = $filterValueItem;
			$sop = $fop;
		    }
		    $subClauses[]="$ffield $sop ?";
		    $params[]=$svalue;
		}
		$whereClauses[]=$subClauses;
	    }
	    else {
		$whereClauses[]="$ffield $fop ?";
		$params[]=$fvalue;
	    }
	}
	return array($params,$whereClauses);
    }
    function addFilter($field,$op=null,$value=null) {
		if(!$value & !$op) {
			$this->filters[]=$field;
			return;
		}
	$this->filters[]=array("field"=>$field,"op"=>$op,"value"=>$value);
    }
    function addJoinTable($table,$on, $type="left") {
	if(isset($this->joinTables[$table])) return;
	$this->joinTables[$table]=array($on,$type);
    }
	abstract function getFieldSubQuery($fieldName, $encType="e");
	abstract function joinSection($sectionAlias,$sectionTypeId);
	
	abstract function joinSectionEncounter($encounterType,$encounterFormId);
	
	public function addExpressionField($expression,$alias=null) {
		$this->fields[]=$expression.($alias ? " as '$alias'" : "");
	}
	public function addObservationFields($obsFields, $encType="e") {
		if($obsFields=="*") {
			$formFields = CLE::db('encounter')->getObservationFieldsInEncounterForm($this->typeFilter);
			$obsFields = array_map(function($formField) { return $formField['name'];},$formFields);
		}
		foreach($obsFields as $obsFieldAlias=>$obsField) {
			$this->addObservationField($obsField, is_numeric($obsFieldAlias) ? $obsField :$obsFieldAlias, $encType);
		}
	}
	public function addPatientField($patientField, $alias=null) {
	    $encAlias = $this->determineEncAlias();
		$this->addJoinTable("patient p", "{$encAlias}.patient_id = p.id");
		$this->fields[]="p.".$patientField.($alias ? " as '$alias'" : "");
	}
	public function addObservationField($obsField, $alias = null, $encType = "e") {
        $this->fields[]=$this->getFieldSubQuery($obsField, $encType)." as '".($alias ? : $obsField)."'";
	}
	
	public function addEncounterField($encField, $alias=null, $encType="e") {
        $encAlias = $this->determineEncAlias($encType);
		$this->fields[]=$encAlias.".".$encField.($alias ? " as '$alias'" : "");
	}
	
	public function limitRows($start, $rows) {
		$this->limitStart = $start;
		$this->limitRows = $rows;
	}
	function determineEncAlias($encType=null) {
	    if($encType===null || $encType==="e") {
	        return "e";
        }
        if($encType==$this->getTypeFilter()) {
	        return "e";
        }
        return "`".$encType."`";
    }
}