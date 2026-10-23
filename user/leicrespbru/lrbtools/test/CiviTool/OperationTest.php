<?php
set_include_path(get_include_path().":".getcwd()."/lib:".getcwd()."/test");

require_once("CiviTool/TestBase.php");
require_once("LRB/CiviTool/Entity.php");
require_once("LRB/CiviTool/ContactOperation.php");
class CiviTool_OperationTest extends CiviTool_TestBase {
	function testSomething() {
		$contactId = 11;
		$co = LRB_CiviTool_ContactOperation::useId($contactId);
		$co->updateActivityStatus("Reminder invitation","Cancelled");
	}
}
?>