<?php
class ADD_Page_UnlockRecord extends ADD_Page_Api {
	function createMenuPath() {
		return "record/unlock";
	}
	function apiOutput() {
		$id = ADD::sys()->getParam('id');
		$ul = ADD_UserLogin::instanceFromAccessManager(ADD::sys()->getAccessManager());
		$ul->unlockRecordWithId($id);
		return array("success"=>1);
	}
}