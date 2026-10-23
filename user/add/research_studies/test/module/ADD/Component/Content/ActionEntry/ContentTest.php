<?php
$home = getenv("ADD_PATH");
set_include_path(get_include_path().":$home/core/test/module");
require_once ("ADD/Component/Content/EncounterEntry/ContentTest.php");

class ADD_Component_Content_ActionEntry_ContentTest extends ADD_Component_Content_EncounterEntry_ContentTest {
	private $_st;
	protected $_params=array("a"=>"contactpat");
	
	public function setUp() {
		parent::setUp();
		
		$this->_page->setupComponents();
		
		
		$this->_st = $this->getMock("ADD_Tool_StudyTitle",array("getStudy","getParentId"),array($this->_page));
		
		$this->_st->expects($this->any())->method("getStudy")->will($this->returnValue("TEST"));
		$this->_st->expects($this->any())->method("getParentId")->will($this->returnValue(999));
		
		$this->_page->getComponents()->get("Content")->setStudyTitle($this->_st);
	}
	
	public function testFormTitleIsCorrect() {
		$this->_page->setupComponents();
		ADD::db('dictionary')->expects($this->once())->method("getDictionaryItemWithValue")->will($this->returnValue(array("title"=>"contact patient")));
		$this->assertEquals("Study 'TEST' action: contact patient",$this->_page->createTitle());
	}
	
	
	/**
	 * @test
	 * @expectedException ADD_DictionaryItemNotFoundException
	 * @expectedExceptionMessage Action 'contactpat' has not been defined in the system
	 */
	public function fails_if_dictionary_definition_of_action_does_not_exist() {
		ADD::sys()->setParam('a','contactpat');
		ADD::db('dictionary')->expects($this->once())->method("getDictionaryItemWithValue")->will($this->returnValue(null));
		$this->_page->createTitle();
	}
	
	public function testEditedContentContainsCorrectData() {
		$this->markTestSkipped("Does not work with 'ActionEntry'");
	}
}

?>
