<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 22/04/2014
 * Time: 10:28
 */

class ADD_Field_Option_DictionaryItemsTest extends ADD_BaseTestFixture {
    private $SUT;
    private $field;
    private $dbDictionary;
    private $page;
    
    public $initialiser;
    const DICTIONARYNAME="test_dictionary";
    
    public function setUp() {
        $this->SUT = new ADD_Field_Option_DictionaryItems();
        $this->dbDictionary = $this->getMock("ADD_Database_Dictionary");
        $dbm = $this->getMock("ADD_DatabaseManager");
        $dbm->expects($this->any())
                ->method("get")
                ->will($this->returnValueMap(array(
                    array("dictionary",$this->dbDictionary)
                )));
        ADD::dbm($dbm);
        $this->field = $this->getMockBuilder("ADD_Field_List")->disableOriginalConstructor()->getMock();
        $this->page = $this->getMockBuilder("ADD_Page")->disableOriginalConstructor()->getMock();
        //$this->page->expects($this->once())->method("getSetting")->with("dictionary")->will($this->returnValue(self::DICTIONARYNAME));
        $this->field->expects($this->once())->method("assertSetting")->with("dictionary")->will($this->returnValue(self::DICTIONARYNAME));

    }
    
    /**
     * @test
     */
    public function it_should_create_options_from_a_csv_list_of_alphabetically_keyed_items() {
        $this->dbDictionary->expects($this->once())
                ->method("getDictionaryItems")
                ->with("test_dictionary")
                ->will($this->returnValue(array(
                    array("id"=>1,"value"=>"Y","title"=>"Yes"),
                    array("id"=>2,"value"=>"N","title"=>"No"),
                    array("id"=>3,"value"=>"U","title"=>"Unknown")
                )));
                    
        $this->SUT->initOptions($this->field,$this->page);
        $this->assertEquals(array("Y"=>"Yes","N"=>"No","U"=>"Unknown"),$this->SUT->getOptionList());
    }
    
    /**
     * @test
     */
    public function it_should_create_options_from_a_csv_mixed_list_of_numbered_and_alphabetically_keyed_items() {
       $this->dbDictionary->expects($this->once())
        ->method("getDictionaryItems")
        ->with("test_dictionary")
        ->will($this->returnValue(array(
            array("id"=>1,"value"=>"1","title"=>"Complete"),
            array("id"=>2,"value"=>"2","title"=>"Incomplete"),
            array("id"=>3,"value"=>"U","title"=>"Unknown")
        )));
        $this->SUT->initOptions($this->field,$this->page);
        $this->assertEquals(array("1"=>"Complete","2"=>"Incomplete","U"=>"Unknown"),$this->SUT->getOptionList());
    }
    
    /**
     * @test
     */
    public function it_should_create_options_from_a_csv_list_of_numbered_keyed_items() {
        $this->dbDictionary->expects($this->once())
        ->method("getDictionaryItems")
        ->with("test_dictionary")
        ->will($this->returnValue(array(
            array("id"=>1,"value"=>"1","title"=>"Complete"),
            array("id"=>2,"value"=>"2","title"=>"Incomplete"),
            array("id"=>3,"value"=>"3","title"=>"Unknown")
        )));
        $this->SUT->initOptions($this->field,$this->page);
        $this->assertEquals(array("1"=>"Complete","2"=>"Incomplete","3"=>"Unknown"),$this->SUT->getOptionList());
    }

}
 