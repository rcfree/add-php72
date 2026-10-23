<?php


class ADD_Pathology_DataSource_Warehouse_Haem extends ADD_Pathology_DataSource_Warehouse_Base {
	function _getFileSuffix() {
		return "haem";
	}
	
	public function nextTestItem() {
		return $this->_fileSource->nextTestItem();
	}
	
	function _initQuery() {
	    CLE::info("init haem query");
		$testcodes = ADD::db('dictionary')->getDictionaryItems("testcodes");
		$testcodes = array_map(function($testcode) { return $testcode['value']; },$testcodes);
		$pholders = ADD_Query_Util::makePlaceholders($testcodes);
		if ($this->_singlePatient) {
            return array($this->prepare("select * from LRBRU.LRBRU_PATIENT_PATHOLOGY_HAEM_RESULTS(?) order by [SAMPLE_COLLECTION_DATE_TIME]"), $this->_patients);
        }
        elseif ($this->_sinceStatusAchieved) {
		    CLE::info("Retrieve patients since:" . $this->_sinceStatusAchieved);
            return array($this->prepare("select * from LRBRU.LRBRU_DATE_STATUS_ACHIEVED_PATHOLOGY_HAEM_RESULTS(?) order by [SAMPLE_COLLECTION_DATE_TIME]"), array($this->_sinceStatusAchieved));
        }
        else {
            CLE::info("Update all patients:" . $this->_sinceStatusAchieved);
		    $sql = "select * from LRBRU.UVW_LRBRU_PATHOLOGY_HAEM_RESULTS order by [SAMPLE_COLLECTION_DATE_TIME]";
            return array($this->prepare($sql),array());
        }
	}
}
