<?php
class ADD_Page_Api_GroupItems extends ADD_Page_Api {
	function createMenuPath() {
       return "api/groupitems";
    }
	
	function apiOutput() {
		$add =  ADD::sys();
		$groupName = $add->getParam('group');
		$dictName = $add->getParam('dict');
		$ddb = ADD::db('dictionary');
		$groupItems = $ddb->getDictionaryItemsInGroup($dictName,$groupName);
		return $groupItems;
	}
}
?>
