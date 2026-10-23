<?php
abstract class ADD_Query_Output {
	function beforeOutput(ADD_Query_Result $result) {
	} 
    function outputResultItem(ADD_Query_ResultItem $resultItem, ADD_Query_Result $result) {
	}
	function afterOutput(ADD_Query_Result $result) {
	}
	function outputAllResultItems(ADD_Query_Result $result) {
		$log = ADD::log();
		$rcount=0;
		while($resultItem = $result->nextResultItem()) {
			$eof = $this->outputResultItem($resultItem, $result);
			if($eof) break;
			$rcount++;
		}
	}
}