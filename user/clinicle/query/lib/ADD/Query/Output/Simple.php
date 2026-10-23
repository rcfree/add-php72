<?php
;
class ADD_Query_Output_Simple extends ADD_Query_Output {
	private $_resultItems;
	function outputResultItem(ADD_Query_ResultItem $resultItem, ADD_Query_Result $result) {
		return $resultItem;
	}
	function getResultItems() {
		return $this->_resultItems;
	}
}