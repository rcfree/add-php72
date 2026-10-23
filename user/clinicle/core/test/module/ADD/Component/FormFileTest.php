<?php

global $home;
$home = getenv("ADD_PATH");
set_include_path("$home/lib");
include_once ("add.php");
include_once ("ADD/Module.php");
ADD::init("/Users/rcf8/Projects/ADD_V2");
include_once ("ADD/ComponentFactory.php");

class ADD_Component_FormFileTest extends PHPUnit_Framework_TestCase {
	static $comp=null;
	static $page = null;
	static $mod = null;
	static function setUpBeforeClass() {
		self::resetComponent();
	}
	
	function resetComponent() {
		$add = ADD::init();
		$mod = new ADD_Module("tst",array());
		$page = new ADD_Page("subsection1", array('title' => 'Test encounter', 'label' => 'Test enc', 'encounter' => 'dac-base-details', 'parent' => 'section', 'form'=>'base-details' ), null);
		$mod->setPath("/Users/rcf8/Projects/ADD_V2/test/data/modules/tst");
		
		$mod -> addPage($page);
		
		$comp = $page->loadComponent('Form_File');
		$page->addComponent($comp);
		
		self::$comp = $comp;
		self::$page = $page;
		
		self::$mod = $mod;
	}
	
	function testFormFileInitialisesOkWithoutCache() {
		$add = ADD::init();
		$add->clearCache();
		$page = self::$page;	
		$comp = self::$comp;
		$comp->setup(self::$page);
		
		$element = array();
		$comp->generate($element,self::$page);
		$this->assertArrayHasKey("date_first_seen",$element);
	}
	
	function testFormFileInitialisesFromCache() {
		self::resetComponent();
		$add = ADD::init();
		$comp = self::$page->loadComponent('Form_File');
		$element = array();
		$comp->generate($element,self::$page);
		$this->assertArrayHasKey("date_first_seen",$element);
	}


}

function l($title,$link,$opts) {
	return "l:".$title.":".$link;
}
?>