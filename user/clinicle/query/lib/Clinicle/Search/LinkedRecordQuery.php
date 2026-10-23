<?php
namespace Clinicle\Search;
use ADD;
class LinkedRecordQuery extends Query {
	private $linkEncType;
	function __construct($patientId,$linkEncType) {
		$this->addPatientFilter(array("id","=",$patientId));
		$this->addPatientJoin($linkEncType);
		$this->linkEncType = $linkEncType;	
	}
	function filterBy($filterField,$filterValue) {
		$this->addObservationFilter($this->linkEncType,array($filterField,"=",$filterValue));
	}
	
	function fetchSingle($fieldName) {
		$this->addObservationFields($this->linkEncType,array($fieldName));
		
		$data = $this->fetchData();
		if(count($data)==0) return null;
		return $data[0][$fieldName];
	}
}