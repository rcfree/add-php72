<?php
;
class ADD_Query_Output_Table_Record extends ADD_Query_Output_Table {
	protected $_rows=array();
	function getRows() {
		return $this->_rows;
	}
	function _outputRow($row) {
		$data = array();
		$rcounter=0;
		foreach($this->_fields as $fieldName) {
			$data[$fieldName]=$row[$rcounter];
			$rcounter++;
		}
	}
	
}