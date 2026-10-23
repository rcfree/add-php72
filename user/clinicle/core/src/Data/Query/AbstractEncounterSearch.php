<?php
namespace ClinicLE\Data\Query;
use ClinicLE\System\Service\CoreServices;
use CLE;

abstract class AbstractEncounterSearch {
	private $pageNo;
	private $rowCount;
	private $query;
	function __construct($pageNo=1, $rowCount=50) {
		$this->pageNo = $pageNo;
		$this->rowCount = $rowCount;
		$this->query = $this->createQuery();
	}
	function countRows() {
		$this->setupQuery($this->query);
	
		list($sql,$params) = $this->query->generateCountSQL();
		$count = CLE::db('encounter')->fetchSingleValue($sql,$params);
		return $count;
	}
	
	function createQuery() {
		$repo = CLE::svc(CoreServices::ENCOUNTER_REPOSITORY);
		$query = $repo->createQuery();
		return $query;
	}
	abstract function setupQuery($query);
	function getRows() {
		$edb = CLE::db('encounter');
		
		$this->setupQuery($this->query);
		         
		$this->startRow = ($this->pageNo-1) * $this->rowCount;
		$this->endRow = $this->startRow + $this->rowCount;
		$this->query->limitRows($this->startRow,$this->rowCount);
		
		list($sql,$params) = $this->query->generateFullSQL();
		\ADD::log($this)->info("sql:".$sql." params:".print_r($params,1));
		$rows = $edb->fetchAll($sql,$params);
		return $rows;
	}
	function getQuery() {
		return $this->query;
	}
}
