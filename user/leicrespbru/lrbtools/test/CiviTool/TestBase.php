<?php
set_include_path(get_include_path().":".getcwd()."/lib");

require_once("PHPUnit/Framework/TestCase.php");
require_once("LRB/InitCivi.php");

class CiviTool_TestBase extends PHPUnit_Framework_TestCase {
	protected static $config = null;
	static function setUpBeforeClass() {
		self::$config = LRB_InitCivi::init(getcwd(),"/var/www/htdocs/civicrm");
	}
}
?>