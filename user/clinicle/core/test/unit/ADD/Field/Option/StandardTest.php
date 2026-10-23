<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 22/04/2014
 * Time: 10:28
 */

//class to test inherited methods in abstract class
class ADD_Field_Option_StandardImpl extends ADD_Field_Option_Standard { }

class ADD_Field_Option_StandardTest extends ADD_BaseTestFixture {
    private $SUT;
    public function setUp() {
        $this->SUT = new ADD_Field_Option_StandardImpl();
    }

    /**
     * @test
     */
    public function it_should_prepend_an_option_to_a_list_of_alphabetically_keyed_items() {
        $this->SUT->setOptionList(array("Y"=>"Yes","N"=>"No","U"=>"Unknown"));
        $this->SUT->prependOption("","-select item-");
        $this->assertEquals(array(""=>"-select item-","Y"=>"Yes","N"=>"No","U"=>"Unknown"),$this->SUT->getOptionList());
    }
    
    /**
     * @test
     */
    public function it_should_prepend_an_option_to_a_mixed_list_of_numbered_and_alphabetically_keyed_items() {
        $this->SUT->setOptionList(array("1"=>"Complete","2"=>"Incomplete","U"=>"Unknown"));
        $this->SUT->prependOption("","-select item-");
        $this->assertEquals(array(""=>"-select item-","1"=>"Complete","2"=>"Incomplete","U"=>"Unknown"),$this->SUT->getOptionList());
    }
    
    /**
     * @test
     */
    public function it_should_create_options_from_a_csv_list_of_numbered_keyed_items() {
        $this->SUT->setOptionList(array("1"=>"Complete","2"=>"Incomplete","3"=>"Unknown"));
        $this->SUT->prependOption("","-select item-");
        $this->assertEquals(array(""=>"-select item-","1"=>"Complete","2"=>"Incomplete","3"=>"Unknown"),$this->SUT->getOptionList());
    }
    
    /**
     * @test
     */
    public function it_should_say_it_has_an_option_with_a_value() {
        $this->SUT->setOptionList(array("1"=>"Complete","2"=>"Incomplete","3"=>"Unknown"));
        $this->assertTrue($this->SUT->hasOptionWithValue("1"));
    }
    
    /**
     * @test
     */
    public function it_should_say_it_has_an_option_with_a_zero_value() {
        $this->SUT->setOptionList(array("0"=>"Other","1"=>"Complete","2"=>"Incomplete","3"=>"Unknown"));
        $this->assertTrue($this->SUT->hasOptionWithValue("0"));
    }
    
    /**
     * @test
     */
    public function it_should_say_it_has_an_option_with_a_value_if_the_label_is_null() {
        $this->SUT->setOptionList(array("1"=>null,"2"=>"Incomplete","3"=>"Unknown"));
        $this->assertTrue($this->SUT->hasOptionWithValue("1"));
    }
    
    /**
     * @test
     */
    public function it_should_say_it_does_not_have_an_option_with_a_value() {
        $this->SUT->setOptionList(array("1"=>"Complete","2"=>"Incomplete","3"=>"Unknown"));
        $this->assertFalse($this->SUT->hasOptionWithValue("4"));
    }
    
    /**
     * @test
     */
    public function it_should_say_it_has_an_option_with_a_label() {
        $this->SUT->setOptionList(array("1"=>"Complete","2"=>"Incomplete","3"=>"Unknown"));
        $this->assertTrue($this->SUT->hasOptionWithLabel("Incomplete"));
    }
    
    /**
     * @test
     */
    public function it_should_say_it_does_not_have_an_option_with_a_label() {
        $this->SUT->setOptionList(array("1"=>"Complete","2"=>"Incomplete","3"=>"Unknown"));
        $this->assertFalse($this->SUT->hasOptionWithLabel("Not answered"));
    }
    
    /**
     * @test
     */
    public function it_should_get_the_value_for_a_label_in_the_list() {
        $this->SUT->setOptionList(array("1"=>"Complete","2"=>"Incomplete","3"=>"Unknown"));
        $this->assertEquals("Incomplete",$this->SUT->getLabelWithValue("2"));
    }
    
    /**
     * @test
     */
    public function it_should_get_a_zero_value_for_a_label_in_the_list() {
        $this->SUT->setOptionList(array("0"=>"Other","1"=>"Complete","2"=>"Incomplete","3"=>"Unknown"));
        $this->assertEquals("Other",$this->SUT->getLabelWithValue("0"));
    }
    
    /**
     * @test
     */
    public function it_should_get_the_label_for_a_value_in_the_list() {
        $this->SUT->setOptionList(array("1"=>"Complete","2"=>"Incomplete","3"=>"Unknown"));
        $this->assertEquals("3",$this->SUT->getValueWithLabel("Unknown"));
    }
    
    /**
     * @test
     */
    public function it_should_count_the_options_in_the_list() {
        $this->SUT->setOptionList(array("0"=>"Other","1"=>"Complete","2"=>"Incomplete","3"=>"Unknown"));
        $this->assertEquals(4,$this->SUT->countOptions());
    }

}
