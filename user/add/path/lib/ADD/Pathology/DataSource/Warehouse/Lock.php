<?php
use ClinicLE\Common\Config\AbstractConfig;

class ADD_Pathology_DataSource_Warehouse_Lock extends ADD_Pathology_DataSource_Warehouse_Base {
	 function __construct(AbstractConfig $config) {
                $this->config = $config;
                $this->connect();
        }

	function _getFileSuffix() {
		return "haem";
	}
	
	public function nextTestItem() {
	}
	
	function _initQuery() {
		$testcodes = ADD::db('dictionary')->getDictionaryItems("testcodes");
		$testcodes = array_map(function($testcode) { return $testcode['value']; },$testcodes);
		$pholders = ADD_Query_Util::makePlaceholders($testcodes);
		return array($this->prepare("select * from LRBRU.UVW_LRBRU_PATHOLOGY_HAEM_RESULTS order by [SAMPLE_COLLECTION_DATE_TIME]"),$testcodes);
	}
	
	function getLock() {
		$stmt = $this->query("select * from LRBRU.LRBRU_PATHOLOGY_LOCK");
		
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		return $row;
		
	}
}
