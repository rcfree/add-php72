<?php
namespace ClinicLE\Data\Query;
use CLE;

class QueryBatch {
	private $currentBatchNo=0;
	private $batchSize = 100;
	private $query;
	
	function __construct(QueryInterface $query) {
		$this->query = $query;
	}
	
	function countRows() {
		list($sql,$params) = $this->query->generateCountSQL();
		$count = CLE::db('encounter')->fetchSingleValue($sql,$params);
		return $count;
	}
	function nextBatch() {
		$edb = CLE::db('encounter');
		$this->currentBatchNo++;
		
		$this->startRow = ($this->currentBatchNo * $this->batchSize) - $this->batchSize;
		
		list($sql,$params) = $this->query->generateFullSQL();
		$sql.=" limit ".$this->startRow.", ".$this->batchSize;
		
		$rows = $edb->fetchAll($sql,$params);
		if(count($rows)==0) {
			 return false;
		}
		return $rows;
	}
	function setBatchSize($batchSize) {
		$this->batchSize = $batchSize;
	}
	function getCurrentBatchNo() {
		return $this->currentBatchNo;
	}
	function getTotalBatchCount() {
		return ceil($this->countRows() / $this->batchSize);
	}
}
