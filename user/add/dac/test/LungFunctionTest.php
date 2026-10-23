<?php
set_include_path(getenv('ADD_PATH')."/lib");
include_once("add.php");
$add = ADD::init();
require_once("ADD/Field/Util.php");
class LungFunctionTest extends PHPUnit_Framework_TestCase {
	function testAgeWithDOBAfterToday() {
		$age = ADD_Field_Util::age("05/11/1953","05/09/2013");
		$this->assertEquals(59,$age);
	}
	
	function testAgeWithDOBBeforeToday() {
		$age = ADD_Field_Util::age("05/11/1953","05/12/2013");
		$this->assertEquals(60,$age);
	}
	
	function testAgeWithDOBToday() {
		$age = ADD_Field_Util::age("05/11/1953","05/11/2013");
		$this->assertEquals(60,$age);
	}
	
	function testJustBorn() {
		$age = ADD_Field_Util::age("12/10/2013","12/10/2013");
		$this->assertEquals(0,$age);
	}
	
	function testAgeLessThanOne() {
		$age = ADD_Field_Util::age("05/12/2012","05/11/2013");
		$this->assertEquals(0,$age);
	}
	
	function testAgeMoreThanOne() {
		$age = ADD_Field_Util::age("05/08/2012","05/09/2013");
		$this->assertEquals(1,$age);
	}
	function testAjaxData() {
		$add = ADD::sys();
		$add->initModules();
		$add->setPatientId(249);
		$page = $add->getPageInModule('dac','lungfunction');
		$ajax = $page->createAjaxFunctions();
		print_r($ajax['lungFunction'](array('spirometry_date'=>'02/10/2013')));
		print_r($page->getWarnings());
	}
	
}
?>