<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 22/04/2014
 * Time: 10:28
 */

class ADD_Field_Option_DataTest extends ADD_BaseTestFixture {
    private $SUT;
    private $field;
    private $page;
    
    public function setUp() {
        $this->SUT = new ADD_Field_Option_Data();
        $this->page = $this->getMockBuilder("ADD_Page")->disableOriginalConstructor()->getMock();
    }

    /**
     * @test
     */
    public function it_should_create_options_from_a_csv_list_of_alphabetically_keyed_items() {
        $this->field = $this->getMockBuilder("ADD_Field_List")->disableOriginalConstructor()->getMock();
        $this->field->expects($this->at(0))->method("getFieldData")->with("Options")->will($this->returnValue("Y=Yes,N=No,U=Unknown"));
        $this->field->expects($this->at(1))->method("getFieldData")->with("Concept")->will($this->returnValue(null));
        
        $this->SUT->initOptions($this->field,$this->page);
        $this->assertEquals(array("Y"=>"Yes","N"=>"No","U"=>"Unknown"),$this->SUT->getOptionList());
    }
    
    /**
     * @test
     */
    public function it_should_create_options_from_a_csv_mixed_list_of_numbered_and_alphabetically_keyed_items() {
        $this->field = $this->getMockBuilder("ADD_Field_List")->disableOriginalConstructor()->getMock();
        $this->field->expects($this->at(0))->method("getFieldData")->with("Options")->will($this->returnValue("1=Complete,2=Incomplete,U=Unknown"));
        $this->field->expects($this->at(1))->method("getFieldData")->with("Concept")->will($this->returnValue(null));
        
        $this->SUT->initOptions($this->field,$this->page);
        $this->assertEquals(array("1"=>"Complete","2"=>"Incomplete","U"=>"Unknown"),$this->SUT->getOptionList());
    }
    
    /**
     * @test
     */
    public function it_should_create_options_from_a_csv_list_of_numbered_keyed_items() {
        $this->field = $this->getMockBuilder("ADD_Field_List")->disableOriginalConstructor()->getMock();
        $this->field->expects($this->at(0))->method("getFieldData")->with("Options")->will($this->returnValue("1=Complete,2=Incomplete,3=Unknown"));
        $this->field->expects($this->at(1))->method("getFieldData")->with("Concept")->will($this->returnValue(null));
        
        $this->SUT->initOptions($this->field,$this->page);
        $this->assertEquals(array("1"=>"Complete","2"=>"Incomplete","3"=>"Unknown"),$this->SUT->getOptionList());
    }

}
 