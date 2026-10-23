<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 21/04/2014
 * Time: 11:34
 */
require_once("module/ajax/lib/ADD/Component/AjaxExecute.php");
require_once("module/api/lib/ADD/Content/Json.php");
class ADD_Component_AjaxExecuteTest extends ADD_BaseTestFixture {

    private $page;
    private $SUT;
    static $FUNCNAME="TestFunction";
    private $pageWithAjax;
    public function setUp() {
        $this->initSystemAccess(true);
        $this->pageWithAjax = $this->getMockBuilder("ADD_Page")->disableOriginalConstructor()->getMock();
        
        $this->page = $this->getMockBuilder("ADD_Page")->disableOriginalConstructor()->getMock();
        
        $this->components = $this->getMockBuilder("ADD_ComponentCollection")->disableOriginalConstructor()->disableArgumentCloning()->getMock();
        $this->pageWithAjax->expects($this->once())->method("getComponents")->will($this->returnValue($this->components));
		
		$this->module = $this->getMockBuilder("ADD_Module")->disableOriginalConstructor()->getMock();
		$this->module->expects($this->once())->method("getPages")->will($this->returnValue(array($this->pageWithAjax)));
        $modules = new ADD_ModuleCollection();
		$modules->add($this->module);
		
		$this->system->expects($this->once())->method("getModules")->will($this->returnValue($modules));
        $this->SUT = new ADD_Component_AjaxExecute($this->page);
    }

    /**
     * @test
     */
    public function it_should_get_ajax_component_and_create_inputs() {
        $ajaxComp = $this->getMock("ADD_Component_AjaxInterface",array("ajaxInputs"));
        $this->components->expects($this->once())->method("has")->with(self::$FUNCNAME)->will($this->returnValue(true));
        $this->components->expects($this->once())->method("get")->with(self::$FUNCNAME)->will($this->returnValue($ajaxComp));
        $ajaxComp->expects($this->once())->method("ajaxInputs")->will($this->returnValue(array("field1")));
        $this->pageWithAjax->expects($this->once())->method("getComponents")->will($this->returnValue($this->components));
        
        $inputs = $this->SUT->createAjaxInputs($this->pageWithAjax,self::$FUNCNAME);
        $this->assertEquals(array("field1"),$inputs);
    }
    
    /**
     * @test
     */
    public function it_should_get_ajax_component_and_create_function_results() {
        $ajaxComp = $this->getMock("ADD_Component_AjaxInterface",array("ajaxFunction"));
        $this->components->expects($this->once())->method("has")->with(self::$FUNCNAME)->will($this->returnValue(true));
        $values = array("output_field"=>1);
        $this->components->expects($this->once())->method("get")->with(self::$FUNCNAME)->will($this->returnValue($ajaxComp));
        $ajaxComp->expects($this->once())->method("ajaxFunction")->with($values)->will($this->returnValue($values));
        $this->pageWithAjax->expects($this->once())->method("getComponents")->will($this->returnValue($this->components));
        
        $results = $this->SUT->createAjaxFunction($this->pageWithAjax,self::$FUNCNAME,$values);
        $this->assertEquals($values,$results);
    }
    
    /**
     * @test
     */
    public function it_should_fail_if_component_does_not_implement_ajax_interface() {
        $notAjaxComp = $this->getMock("ADD_Component");
        $this->components->expects($this->once())->method("has")->with(self::$FUNCNAME)->will($this->returnValue(true));
        $this->components->expects($this->once())->method("get")->with(self::$FUNCNAME)->will($this->returnValue($notAjaxComp));
        $this->pageWithAjax->expects($this->once())->method("getComponents")->will($this->returnValue($this->components));
        $this->setExpectedException("ADD_NotAjaxComponentException");
        $inputs = $this->SUT->createAjaxInputs($this->pageWithAjax,self::$FUNCNAME);
    }
    
    /**
     * @test
     */
    public function it_should_fail_if_component_does_not_exist() {
        $notAjaxComp = $this->getMock("ADD_Component");
        $this->components->expects($this->once())->method("has")->with(self::$FUNCNAME)->will($this->returnValue(false));
        $this->pageWithAjax->expects($this->once())->method("getComponents")->will($this->returnValue($this->components));
        $this->setExpectedException("ADD_ComponentNotInCollectionException");
        $inputs = $this->SUT->createAjaxInputs($this->pageWithAjax,self::$FUNCNAME);
    }

}
 