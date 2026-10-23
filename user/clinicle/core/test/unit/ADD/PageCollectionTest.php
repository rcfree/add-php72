<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 22/04/2014
 * Time: 10:28
 */

class ADD_PageCollectionTest extends ADD_BaseTestFixture {
    private $SUT;
    private $module;

    public function setUp() {
        $this->module = $this->getMockBuilder("ADD_Module")->disableOriginalConstructor()->getMock();
        $this->SUT = new ADD_PageCollection($this->module);
        
    }

    /**
     * @test
     */
    public function it_should_add_a_page_object_and_set_the_module() {
        $page = $this->getMockPageNamed("test");
        $this->SUT->add($page);
        $this->assertCount(1,$this->SUT);
        $this->assertInstanceOf("ADD_Page",$this->SUT->get('test'));
    }
    
    /**
     * @test
     */
    public function it_should_identify_whether_the_page_exists_already() {
        $page = $this->getMockPageNamed("test");
        $this->SUT->add($page);

        $this->assertTrue($this->SUT->has('test'));
        $this->assertFalse($this->SUT->has('not'));
    }

    /**
     * @test
     */
    public function it_should_add_multiple_different_page_objects() {
        $page1 = $this->getMockPageNamed("test1");
        $this->SUT->add($page1);
        $page2 = $this->getMockPageNamed("test2");
        $this->SUT->add($page2);

        $this->assertCount(2,$this->SUT);
        $this->assertEquals($page1,$this->SUT->get('test1'));
        $this->assertEquals($page2,$this->SUT->get('test2'));
    }

    /**
     * @test
     */
    public function it_should_iterate_through_multiple_page_objects() {
        $page1 = $this->getMockPageNamed("test1");
        $this->SUT->add($page1);
        $page2 = $this->getMockPageNamed("test2");
        $this->SUT->add($page2);
        
        $count = 0;
        foreach($this->SUT as $name => $page) {
            if ($count==0) {
                $this->assertEquals($page1,$page);
                $this->assertEquals("test1",$name);
            }
            else {
                $this->assertEquals($page2,$page);
                $this->assertEquals("test2",$name);
            }
            $count++;
        }
    }
    
    public function it_should_combine_with_pages_from_another_collection() {
        $page1 = $this->getMockPageNamed("test1");
        $this->SUT->add($page1);
        $page2 = $this->getMockPageNamed("test2");
        $this->SUT->add($page2);
        
        $page3 = $this->getMockPageNamed("test3");
        $pc = new ADD_PageCollection($this->module);
        $pc->add($page2);
        $pc->add($page3);
        
        $this->SUT->combineWith($pc);
        $this->assertCount(3,$this->SUT);
        $this->assertEquals($page1,$this->SUT->get('test1'));
        $this->assertEquals($page2,$this->SUT->get('test2'));
        $this->assertEquals($page3,$this->SUT->get('test3'));
    }
    
    public function it_should_not_add_a_page_with_no_name() {
        $this->setExpectedException("ADD_NullValueProvided");
        $page = $this->getMockPageNamed(null);
        $this->SUT->add($page1);
    }
    
   public function getMockPageNamed($name) {
        $page = $this->getMockBuilder("ADD_Page")->disableOriginalConstructor()->getMock();
        $page->expects($this->once())->method("getName")->will($this->returnValue($name));
        return $page;
   }
        
}
 