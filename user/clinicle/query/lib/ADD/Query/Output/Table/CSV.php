<?php
;
class ADD_Query_Output_Table_CSV extends ADD_Query_Output_Table {
	const COMMA=",";
	const TAB="\t";
	
	private $_separator = self::COMMA;
	
	protected function _outputRow($row) {
		fputcsv($this->_handle, $row,$this->_separator,'"');
	}
	
	function _outputHeadings($handle) {
		fputcsv($handle,$this->_fields,$this->_separator,'"');
	}
	
	function setSeparator($separator) {
		$this->_separator = $separator;
	}
}