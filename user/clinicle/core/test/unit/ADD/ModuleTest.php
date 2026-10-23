<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 22/04/2014
 * Time: 10:28
 */

class ADD_ModuleTest extends ADD_BaseTestFixture {

    private $pageCollection;
    private $SUT;

    const OTHERPAGE = "otherpage";

    public function setUp() {
        $this->pageCollection = $this->getMockBuilder("ADD_PageCollection")->disableOriginalConstructor()->getMock();
    }

    /**
     * @test
     */
    public function it_should_construct_if_directory_exists() {
        $this->setupSUT();
        $this->assertInstanceOf("ADD_Config",$this->SUT->getTemplate());
        $this->assertEquals("patient",$this->SUT->getName());
        $this->assertFalse($this->SUT->isInit());
    }

    /**
     * @test
     */
    public function it_should_not_construct_if_directory_does_not_exist() {
        $this->setExpectedException("ADD_DirectoryDoesNotExistException","Cannot init module 'patient' as dir");
        $this->SUT = new ADD_Module("patient", __DIR__."/log");
    }

    /**
     * @test
     */
    public function it_should_initialise_module_pages() {
        $this->setupSUT();
        $this->pageCollection->expects($this->once())->method("initialise")->with($this->SUT->getTemplate());
        $this->SUT->initPages($this->pageCollection);
        $this->assertTrue($this->SUT->isInit());
    }

    /**
     * @test
     */
    public function it_should_not_init_sub_modules_if_pages_not_init() {
        $this->setupSUT();
        $this->SUT->initSubModules();
        $this->assertEmpty($this->SUT->getSubModules());
    }

    /**
     * @test
     */
    public function it_should_init_sub_modules_if_pages_init() {
        $this->initSystemAccess();

        $this->setupSUT();
        $this->SUT->initPages($this->pageCollection);
        $this->SUT->initSubModules();
        $this->assertEmpty($this->SUT->getSubModules());
    }

    /**
     * @test
     */
    public function it_should_fail_to_init_if_extends_sub_modules_do_not_exist() {
        $this->initSystemAccess();
        $this->template = $this->getMock("ADD_Config");

       /* $this->SUT->setTemplate($this->template);
        $this->template->expects($this->at(1))
            ->method("getSetting")
            ->with("extends")
            ->will($this->returnValue("test"));
	    * 
	    */
		$this->setupSUT(array("extends"=>"test"));
        $this->setExpectedException("ADD_ModuleDoesNotExistException");
        $this->SUT->initPages($this->pageCollection);
        $this->SUT->initSubModules();
    }

    /**
     * @test
     */
    public function it_should_init_other_module_and_be_added_to_the_other_module_if_extends_config_present() {
        $this->initSystemAccess();
        $this->template = $this->getMock("ADD_Config");

        $this->setupSUT(array("extends"=>"test"));
        $this->SUT->setTemplate($this->template);
        
        $extMod = $this->getMockBuilder("ADD_Module")->disableOriginalConstructor()->getMock();
        $extMod->expects($this->once())
            ->method("addSubModule")
            ->with("patient");

        $this->system->expects($this->once())
            ->method("getModule")
            ->with("test")
            ->will($this->returnValue($extMod));

        $this->SUT->initPages($this->pageCollection);
        $this->SUT->initSubModules();

        $this->assertEquals("test",$this->SUT->getExtends());

    }

    /**
     * @test
     */
    public function it_should_add_single_sub_module_to_list() {
        $this->setupSUT();
        $this->SUT->addSubModule("test1");
        $this->assertEquals(array("test1"),$this->SUT->getSubModules());
    }

    /**
     * @test
     */
    public function it_should_add_multiple_sub_modules_to_list() {
        $this->setupSUT();
        $this->SUT->addSubModule("test1");
        $this->SUT->addSubModule("test2");
        $this->assertEquals(array("test1","test2"),$this->SUT->getSubModules());
    }
    
    /**
     * @test
     */
    public function it_should_only_get_pages_from_this_module_if_flag_is_true() {
        $this->setupSUT();
        $this->SUT->initPages($this->pageCollection);
        
        $this->SUT->addSubModule("test1");
        $this->assertCount(0,$this->SUT->getPages(true));
    }
    
    /**
     * @test
     */
    public function it_should_get_pages_from_only_this_module() {
        $this->setupSUT();
        $this->initSystemAccess();
        
        $this->SUT->addSubModule("test");
        
        $page1 = $this->getMockBuilder("ADD_Page")->disableOriginalConstructor()->getMock();
        $page1->expects($this->never())->method("getName")->will($this->returnValue("page1"));
        $page2 = $this->getMockBuilder("ADD_Page")->disableOriginalConstructor()->getMock();
        $page2->expects($this->never())->method("getName")->will($this->returnValue("page2"));
        
        $sutPageCol = $this->getMockBuilder("ADD_PageCollection")->disableOriginalConstructor()->getMock();
        $this->mockIterator($sutPageCol,array($page1,$page2));
        $this->SUT->initPages($sutPageCol);
        
        $testModule = $this->expectTestModuleWithTwoPagesToNotBeAccessed();
        
        $this->system->expects($this->any())->method("getModule")->with("test")->will($this->returnValue($testModule));
        
        $this->assertCount(2,$this->SUT->getPages());
    }
    
    /**
     * @test
     */
    public function it_should_get_pages_from_this_module_and_submodule_if_required() {
        $this->setupSUT();
        $this->initSystemAccess();
        
        $this->SUT->addSubModule("test");
        
        $page1 = $this->getMockBuilder("ADD_Page")->disableOriginalConstructor()->getMock();
        $page1->expects($this->once())->method("getName")->will($this->returnValue("page1"));
        $page2 = $this->getMockBuilder("ADD_Page")->disableOriginalConstructor()->getMock();
        $page2->expects($this->once())->method("getName")->will($this->returnValue("page2"));
        
        $sutPageCol = $this->getMockBuilder("ADD_PageCollection")->disableOriginalConstructor()->getMock();
        $this->mockIterator($sutPageCol,array($page1,$page2));
        $this->SUT->initPages($sutPageCol);
        
        $testModule = $this->expectTestModuleWithTwoPages();
        
        $this->system->expects($this->once())->method("getModule")->with("test")->will($this->returnValue($testModule));
        
        $this->assertCount(3,$this->SUT->getPagesInStructure());
    }
    
    private function setupSUT($settings=array())
    {
		$config = new ADD_Config($settings);
        $this->SUT = new ADD_Module("patient", $config);
        $this->pageCollection = $this->getMockBuilder("ADD_PageCollection")->disableOriginalConstructor()->getMock(); 
    }
    
    private function expectTestModuleWithTwoPages() {
        $testModule = $this->getMockBuilder("ADD_Module")->disableOriginalConstructor()->getMock();
        $page2 = $this->getMockBuilder("ADD_Page")->disableOriginalConstructor()->getMock();
        $page2->expects($this->once())->method("getName")->will($this->returnValue("page2"));
        
        $page3 = $this->getMockBuilder("ADD_Page")->disableOriginalConstructor()->getMock();
        $page3->expects($this->once())->method("getName")->will($this->returnValue("page3"));
         
        $testPageCol = $this->getMockBuilder("ADD_PageCollection")->disableOriginalConstructor()->getMock();
        $this->mockIterator($testPageCol,array($page2,$page3));
        
        $testModule->expects($this->once())->method("getPages")->will($this->returnValue($testPageCol));
        return $testModule;
    }
    
    private function expectTestModuleWithTwoPagesToNotBeAccessed() {
        $testModule = $this->getMockBuilder("ADD_Module")->disableOriginalConstructor()->getMock();
        $page2 = $this->getMockBuilder("ADD_Page")->disableOriginalConstructor()->getMock();
        $page2->expects($this->never())->method("getName")->will($this->returnValue("page2"));
        
        $page3 = $this->getMockBuilder("ADD_Page")->disableOriginalConstructor()->getMock();
        $page3->expects($this->never())->method("getName")->will($this->returnValue("page3"));
         
        $testPageCol = $this->getMockBuilder("ADD_PageCollection")->disableOriginalConstructor()->getMock();
        $this->mockIterator($testPageCol,array($page2,$page3));
        
        $testModule->expects($this->never())->method("getPages")->will($this->returnValue($testPageCol));
        return $testModule;
    }
    
 /**
 * Setup methods required to mock an iterator
 *
 * @param PHPUnit_Framework_MockObject_MockObject $iteratorMock The mock to attach the iterator methods to
 * @param array $items The mock data we're going to use with the iterator
 * @return PHPUnit_Framework_MockObject_MockObject The iterator mock
 */
public function mockIterator(PHPUnit_Framework_MockObject_MockObject $iteratorMock, array $items)
{
    $iteratorData = new \stdClass();
    $iteratorData->array = $items;
    $iteratorData->position = 0;

    $iteratorMock->expects($this->any())
                 ->method('rewind')
                 ->will(
                     $this->returnCallback(
                         function() use ($iteratorData) {
                             $iteratorData->position = 0;
                         }
                     )
                 );

    $iteratorMock->expects($this->any())
                 ->method('current')
                 ->will(
                     $this->returnCallback(
                         function() use ($iteratorData) {
                             return $iteratorData->array[$iteratorData->position];
                         }
                     )
                 );

    $iteratorMock->expects($this->any())
                 ->method('key')
                 ->will(
                     $this->returnCallback(
                         function() use ($iteratorData) {
                             return $iteratorData->position;
                         }
                     )
                 );

    $iteratorMock->expects($this->any())
                 ->method('next')
                 ->will(
                     $this->returnCallback(
                         function() use ($iteratorData) {
                             $iteratorData->position++;
                         }
                     )
                 );

    $iteratorMock->expects($this->any())
                 ->method('valid')
                 ->will(
                     $this->returnCallback(
                         function() use ($iteratorData) {
                             return isset($iteratorData->array[$iteratorData->position]);
                         }
                     )
                 );

    $iteratorMock->expects($this->any())
                 ->method('count')
                 ->will(
                     $this->returnCallback(
                         function() use ($iteratorData) {
                             return sizeof($iteratorData->array);
                         }
                     )
                 );

    return $iteratorMock;
}
}
 