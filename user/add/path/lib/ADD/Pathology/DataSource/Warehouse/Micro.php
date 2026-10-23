<?php


class ADD_Pathology_DataSource_Warehouse_Micro extends ADD_Pathology_DataSource_Warehouse_Base {
	function _getFileSuffix() {
		return "micro";
	}
	
	public function nextTestItem() {
		return $this->_fileSource->nextTestItem();
	}

    function _initQuery() {
        if ($this->_singlePatient) {
            return array($this->prepare("select * from LRBRU.LRBRU_PATIENT_PATHOLOGY_MICRO_RESULTS(?) order by [UNIQUE_TEST_ID],[Organism_Display_Line]"), $this->_patients);
        }
        elseif ($this->_sinceStatusAchieved) {
            CLE::info("Retrieve patients since:" . $this->_sinceStatusAchieved);
            return array($this->prepare("select * from LRBRU.LRBRU_DATE_STATUS_ACHIEVED_PATHOLOGY_MICRO_RESULTS(?) order by [UNIQUE_TEST_ID],[Organism_Display_Line]"), array($this->_sinceStatusAchieved));

        }
        else {
            return array($this->prepare("select * from LRBRU.UVW_LRBRU_PATHOLOGY_MICRO_RESULTS order by [UNIQUE_TEST_ID],[Organism_Display_Line]"),array());
        }
    }

}
