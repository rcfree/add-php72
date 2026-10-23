<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class ADD_Component_ConditionalTopSection_ContentTest extends ADD_Test_EncounterBase {
	public function setUp() {
		parent::setUp();
		
		
	}
	 
	/**
	 * @test
	 */
	public function section_based_content_should_be_empty() {
		$page = $this->_page;
		ADD::sys()->setParam('eid',5);
		$page->setPermission('create',true);
		$this->setAllPermissionsForChildPages();
		$this->givenMockPatientIsSelected();
		
		$page->createBaseContent();
		$tmContent = ADD::web()->getContent('topMenu');
		
		
		$this->assertEquals("",$tmContent->toText(true));
	}
	
	/**
	 * @test
	 */
	public function if_condition_is_not_met_only_display_page_menu_options_if_always_show_flag_set() {
		$page = $this->_page;
		$this->setAllPermissionsForChildPages();
		$this->givenMockPatientIsSelected();
		$rs = new ADD_Test_RecordSet($this->_module,$this);
		$rs->expectTopDomain(null);
		$rs->expectSection($this->_page->getName(), array("id"=>self::SECTION_ID,"old"=>true,"repeat"=>true));
		$rs->expectData("referral", array("id"=>99,"parent"=>self::SECTION_ID,"startData"=>array("enrol_in_treatment"=>"0"),"old"=>true));
		$rs->expectData("referral", array("id"=>100,"parent"=>self::SECTION_ID,"startData"=>array("enrol_in_treatment"=>"0"),"old"=>true));
		
		$em = ADD::sys()->getEncounterManager();
		$rs->populateEncounterManager($em);
		
		$expOptList = array();
		
		$cpages = $page->getChildPages();
		foreach($cpages as $cpage) {
			if ($cpage->getSetting('ConditionalTopMenu.always_show')) {
				$expOptList[]=$cpage->createMenuLabel();
			}
		}
		ADD::sys()->getAccessManager()->setCurrentPage($cpages[0]);
		
		$page->createBaseContent();
		$cpages[0]->createContent();
		$tmContent = ADD::web()->getContent('topMenu');
		
		$expOpts=implode("",$expOptList);
		$this->assertEquals($expOpts,$tmContent->toText(true));
	}
	
	const SECTION_ID=1;
	/**
	 * @test
	 */
	public function if_condition_is_met_display_all_page_menu_options_apart_from_those_with_never_show_flag_set() {
		$page = $this->_page;
		ADD::sys()->setParam('eid',1);
		$this->setAllPermissionsForChildPages();
		$rs = new ADD_Test_RecordSet($this->_module,$this);
		$em = ADD::sys()->getEncounterManager();
		$rs->expectTopDomain(null);
		$rs->expectSection($page->getName(), array("id"=>self::SECTION_ID,"old"=>true,"exists"=>true));
		$startRecord = $rs->expectData("referral", array("parent"=>self::SECTION_ID,"startData"=>array("enrol_in_treatment"=>"1"),"old"=>true,"exists"=>true));
		$rs->populateEncounterManager($em);
		
		$this->givenMockPatientIsSelected();
		$expOptList = array();
		$cpages = $page->getChildPages();
		$condPageName = $this->_page->getSetting('ConditionalTopMenu.condition_page');
		
		foreach($cpages as $cpage) {
			if ($cpage->getSetting('ConditionalTopMenu.never_show')) continue;
			$expOptList[]=$cpage->createMenuLabel();
		}
		ADD::sys()->getAccessManager()->setCurrentPage($cpages[0]);
		
		$page->createBaseContent();
		
		$cpages[0]->createContent();
		$tmContent = ADD::web()->getContent('topMenu');
		
		$expOpts=implode("",$expOptList);
		$this->assertEquals($expOpts,$tmContent->toText(true));
	}
	
	private function setAllPermissionsForChildPages() {
		foreach($this->_page->getChildPages() as $cpage) {
			$cpage->setPermission('create',true);
			$cpage->setPermission('edit',true);
			$cpage->setPermission('view',true);
		}
	}
}