<?php




class ADD_Pathology_Record_Haem extends ADD_Pathology_Record_Base {
	protected $_mapping=array (
	  'UNIQUE_TEST_ID' => 'unique_test_id',
	  'SPECIMEN_NUMBER' => 'specimen_no',
	  'SAMPLE_COLLECTION_DATE_TIME' => 'coll_date_time',
	  'LOCAL_TEST_CODE' => 'test_code',
	  'SNOMED_CT_CODE' => 'snomed_ct_code',
	  'RESULT' => 'result',
	  'REFERENCE_RANGE_LOW' => 'normal_low',
	  'REFERENCE_RANGE_HIGH' => 'normal_high',
	  'UNITS' => 'units',
	);
	
	function updateFromRowData($rowData) {
		$this->_updateRowData($rowData);
	}
}