<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 22/04/2014
 * Time: 10:28
 */

class ADD_ComponentCollectionTest extends ADD_BaseTestFixture {
    private $SUT;
    private $page;

    public function setUp() {
        $this->page = $this->getMockBuilder("ADD_Page")->disableOriginalConstructor()->getMock();
        $this->SUT = new ADD_ComponentCollection($this->page);
    }

    /**
     * @test
     */
    public function it_should_add_a_component_object() {
        $acComponent = $this->getMockComponentOfType("AccessControl");

        $this->SUT->add($acComponent);
        $this->assertCount(1,$this->SUT);

        $this->assertTrue(is_a($this->SUT->get('AccessControl'),"ADD_Component_AccessControl"));
    }

    /**
     * @test
     */
    public function it_should_add_a_component_by_name() {
        $this->SUT->add("AccessControl");
        $this->assertCount(1,$this->SUT);

        $this->assertTrue(is_a($this->SUT->get('AccessControl'),"ADD_Component_AccessControl"));
    }

    /**
     * @test
     */
    public function it_should_add_multiple_components_by_name() {
        $this->SUT->add("AccessControl");
        $this->SUT->add("Form_DB");
        $this->assertCount(2,$this->SUT);

        $this->assertTrue(is_a($this->SUT->get('AccessControl'),"ADD_Component_AccessControl"));
        $this->assertTrue(is_a($this->SUT->get('Form'),"ADD_Component_Form_DB"));
    }
    
    /**
     * @test
     */
    public function it_should_identify_whether_the_component_exists_already() {
        $this->SUT->add("AccessControl");

        $this->assertTrue($this->SUT->has('AccessControl'));
        $this->assertFalse($this->SUT->has('AccessNoControl'));
    }
    
    /**
     * @test
     */
    public function it_should_replace_an_existing_component_object_of_specific_type() {
        $formComponent = $this->getMockComponentOfType("Form_DB");

        $this->SUT->add($formComponent);
        $this->assertCount(1,$this->SUT);

        $formComponent = $this->getMockComponentOfType("Form_File");

        $this->SUT->add($formComponent);
        $this->assertCount(1,$this->SUT);

        $this->assertInstanceOf("ADD_Component_Form_File",$this->SUT->get('Form'));
    }

    /**
     * @test
     */
    public function it_should_add_multiple_component_objects() {
        $this->addTwoComponentsToCollection();

        $this->assertCount(2,$this->SUT);
        $this->assertInstanceOf("ADD_Component_AccessControl",$this->SUT->get('AccessControl'));
        $this->assertInstanceOf("ADD_Component_Form_File",$this->SUT->get('Form'));
    }

    /**
     * @test
     */
    public function it_should_iterate_through_multiple_component_objects() {
        $this->addTwoComponentsToCollection();
        $count = 0;
        foreach($this->SUT as $name => $component) {
            if ($count==0) {
                $this->assertInstanceOf("ADD_Component_Form_File",$this->SUT->get('Form'));
            }
            else {
                $this->assertInstanceOf("ADD_Component_AccessControl",$this->SUT->get('AccessControl'));
            }
            $count++;
        }
    }
    
    /**
     * @test
     */
    public function it_should_use_the_components_page_setting_to_initialise() {
        $this->page->expects($this->once())->method("getSettingList")->will($this->returnValue(array()));
        $this->SUT->initialise();
        $this->assertCount(0,$this->SUT);
    }
    
    /**
     * @test
     */
    public function it_should_initialise_all_components_in_the_page_setting() {
        $this->page->expects($this->once())->method("getSettingList")->will($this->returnValue(array("AccessControl","Form_File")));
        $this->SUT->initialise();
        $this->assertCount(2,$this->SUT);
    }
    
    /**
     * @test
     */
    public function it_should_initialise_all_components_even_sub_components() {
        $this->page->expects($this->once())->method("getSettingList")->will($this->returnValue(array("Content_DictionaryEntry")));
        $this->SUT->initialise();
        $this->assertCount(4,$this->SUT);
        $this->assertInstanceOf("ADD_Component_Content_DictionaryEntry",$this->SUT->get("Content"));
        $this->assertInstanceOf("ADD_Component_AccessControl",$this->SUT->get("AccessControl"));
        $this->assertInstanceOf("ADD_Component_Form_File",$this->SUT->get("Form"));
    }
    
    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockComponentOfType($componentType)
    {
        $component = ADD_Util::loadClass("ADD_Component_".$componentType);
        return $component;
    }

    private function addTwoComponentsToCollection()
    {
        $acComponent = $this->getMockComponentOfType("AccessControl");
        $this->SUT->add($acComponent);
        $formComponent = $this->getMockComponentOfType("Form_File");
        $this->SUT->add($formComponent);
    }

}
 